<?php
namespace Ndm\JsonRpc2\Client\Transport;
use \Ndm\JsonRpc2\Client\Exception as Exception;
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerInterface;
use \Psr\Log\NullLogger;
use \Ndm\OAuth as OAuth;

/**
 * A configurable transport based on stream_context_create.
 *
 */
class OAuthHttpStreamTransport implements TransportInterface, LoggerAwareInterface {

    /**
     * @var string
     */
    private $url;

    /**
     * @var OAuth\Consumer
     */
    private $consumer;
    /**
     * @var OAuth\Token
     */
    private $token;
    /**
     * @var string
     */
    private $realm;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $httpContextOptions = array(
        'follow_location' => 0,
        'max_redirects' => 0,
    );

    /**
     * @var array
     */
    private $sslContextOptions = array();

    /**
     * @param string $url
     * @param OAuth\Consumer $consumer
     * @param OAuth\Token|null $token
     * @param string $realm
     *
     * @throws Exception\TransportException
     */
    public function __construct($url, OAuth\Consumer $consumer, OAuth\Token $token = null, $realm=""){
        // check that the URL is valid
        if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)){
            throw new Exception\TransportException("The URL provided is not valid. Must be in the format (http[s]://domain[/path/][file])");
        }
        // check that they are using HTTP or HTTPS
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (strcasecmp($scheme, 'http') != 0 && strcasecmp($scheme, 'https') != 0){
            throw new Exception\TransportException("The URL must be HTTP or HTTPS");
        }
        // set the URL to use
        $this->url = $url;
        // set the consumer & token
        $this->consumer = $consumer;
        $this->token = $token;
        $this->realm = $realm;
        // set the logger
        $this->setLogger(new NullLogger());

    }

    /**
     * @param string $request
     *
     * @throws \Ndm\JsonRpc2\Client\Exception\TransportException
     * @throws \Ndm\JsonRpc2\Client\Exception\HttpTransportException
     *
     * @return string
     */
    public function send($request)
    {
        $defaults = array(
            'method' => 'POST',
            'header' => array(
                'Content-Type: application/json',
                'Connection: close'
            ),
            'content' => $request,
            'protocol_version' => 1.0,
            'ignore_errors' => true
        );
        // create an OAuth Request
        $request = OAuth\Request::fromConsumerAndToken($this->consumer, $this->token, $defaults['method'], $this->url);
        $request->signRequest($this->consumer, $this->token);
        // obtain the Authorisation header
        $header = $request->toHeader($this->realm);
        // set the header in the request
        $defaults['header'] = array_merge($defaults['header'], array($header));
        // add additional options
        $options = $this->getContextOptions($defaults);
        $this->logger->info('Sending Request', array('url'=>$this->url, 'context_options'=>$options));
        // create the context
        $context = stream_context_create($options);
        // connect and open the stream
        $stream = fopen($this->url, 'r', false, $context);
        // get the response headers etc.
        $headers = stream_get_meta_data($stream);
        // actual data at $url
        $content = stream_get_contents($stream);
        fclose($stream);
        $this->logger->info('Received Reply', array('headers'=>$headers, 'content'=>$content));
        if (!isset($headers['wrapper_data'])){
            throw new Exception\TransportException("Failed to connect to URL {$this->url}");
        }
        // check the status code of the response
        list ($successful, $statusCode, $statusMessage) = $this->checkStatus($headers['wrapper_data']);
        if (!$successful){
            $this->logger->error('Request was not successful',array('url'=>$this->url, 'context_options'=>$options, 'headers'=>$headers, 'content'=>$content));
            throw new Exception\HttpTransportException($statusCode, $statusMessage, $content);
        }
        return $content;
    }

    /**
     * @param $override
     * @return array
     */
    private function getContextOptions($override){
        return array(
            'http' => array_merge_recursive($this->httpContextOptions, $override),
            'ssl'  => $this->sslContextOptions
        );
    }

    /**
     * @param $headers
     * @return array
     */
    private function checkStatus($headers){
        if (isset($headers[0]) && count($parts = explode(' ', $headers[0], 3)) == 3) {
            $statusCode = (integer) $parts[1];
            $statusMessage = $parts[2];
            $success = (200 <= $statusCode  && $statusCode < 300);
            return array($success, $statusCode, $statusMessage);
        } else {
            return array(false, 0, "Unknown");
        }
    }

    /**
     * Note: When setting 'header' option, it must be an array or it will be overwritten
     * @param string $optionName
     * @param mixed $value
     */
    public function setHttpContextOption($optionName, $value){
        $this->httpContextOptions[$optionName] = $value;
    }

    /**
     * @param string $optionName
     * @return mixed
     */
    public function getHttpContextOption($optionName){
        if (!isset($this->httpContextOptions, $optionName)){
            return null;
        }
        return $this->httpContextOptions[$optionName];
    }

    /**
     * @param string $optionName
     */
    public function unsetHttpContextOption($optionName){
        if (array_key_exists($this->httpContextOptions, $optionName)){
            unset ($this->httpContextOptions[$optionName]);
        }
    }

    /**
     * @param string $optionName
     * @param mixed $value
     */
    public function setSslContextOption($optionName, $value){
        $this->sslContextOptions[$optionName] = $value;
    }

    /**
     * @param string $optionName
     * @return mixed
     */
    public function getSslContextOption($optionName){
        if (!isset($this->sslContextOptions, $optionName)){
            return null;
        }
        return $this->sslContextOptions[$optionName];
    }

    /**
     * @param string $optionName
     */
    public function unsetSslContextOption($optionName){
        if (array_key_exists($this->sslContextOptions, $optionName)){
            unset ($this->sslContextOptions[$optionName]);
        }
    }


    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
