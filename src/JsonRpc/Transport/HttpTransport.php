<?php

namespace JsonRpc\Transport;

/**
 * A basic HttpTransport class that expects a HTTP Post, with application/json content
 *
 *
 * @author Nathan Muir
 * @version 2012-12-24
 */
class HttpTransport implements TransportInterface
{

    /**
     * @const
     */
    const OPT_NONE = 0;
    /**@#+
     * @const Request / input options
     */
    const OPT_REQUIRE_HTTPS = 1;
    const OPT_REQUIRE_CONTENT_TYPE = 2; // only applicable if SOURCE_POST
    /**@#+
     * @const Render / Output Options
     */
    const OPT_SEND_OUTPUT_HEADERS = 64;

    /**@#+
     * @const Source Types
     */
    const SOURCE_POST_BODY = 1;
    const SOURCE_POST_FORM_ENCODED = 2;
    const SOURCE_GET_PARAMS = 4;
    const SOURCE_GET_QUERY_STRING = 8;

    /**
     * @var int
     */
    private $options;

    /**
     * @var int
     */
    private $source;

    /**
     * @param int $source
     * @param int $options
     */
    public function __construct($source = self::SOURCE_POST_BODY, $options = 0)
    {
        $this->source = $source;
        $this->options = $options;
    }

    /**
     * Processes data from it's specific source, and initialise a request, or a group of requests
     *
     * @return \JsonRpc\Request|\JsonRpc\Request[]
     * @throws TransportException
     * @throws \JsonRpc\Exception
     */
    public function getRequest()
    {
        // check configuration options
        if ($this->isOptRequireHttps() && (empty($_SERVER['HTTPS']) || strcmp($_SERVER['HTTPS'], 'off') === 0)) {
            throw new TransportException("HTTPS is Required");
        }

        if ($this->isSourcePost()) {
            if (!$this->isHttpPost()) {
                throw new TransportException("Request must be a HTTP-POST");
            }

            if ($this->isSourcePostBody()) {
                if ($this->isOptRequireContentType() && !$this->isContentTypeJson()) {
                    throw new TransportException("Content Type isn't application/json");
                }
                return \JsonRpc\Request::createFromString($this->getRawHttpPost());
            } else { // $this->isSourcePostFormEncoded()
                if ($this->isOptRequireContentType() && !$this->isContentTypeFormEncoded()) {
                    throw new TransportException("Content Type isn't application/x-www-form-urlencoded");
                }
                return \JsonRpc\Request::createFromAssoc($_POST);
            }
        } else { // $this->isSourceGet()
            if (!$this->isHttpGet()) {
                throw new TransportException("Request must be a HTTP-GET");
            }
            if ($this->isSourceGetParams()) {
                return \JsonRpc\Request::createFromAssoc($_GET);
            } else { // $this->isSourceGetQueryString()
                return \JsonRpc\Request::createFromString($this->getQueryString());
            }
        }
    }

    /**
     * Processes data from it's specific source, and initialise a request, or a group of requests
     *
     * @param \JsonRpc\ResponseInterface $collection
     *
     * @throws TransportException
     */
    public function render(\JsonRpc\ResponseInterface $collection = null)
    {
        if ($collection === null) {
            $response = '';
        } else {
            $response = (string)$collection;
        }

        if ($this->isOptSendOutputHeaders()) {
            if (headers_sent()) {
                throw new TransportException("Headers already sent");
            }
            header("Content-type: application/json");
            header("Content-length: " . strlen($response));
        }
        echo $response;
    }

    /**
     * @return bool
     */
    private function isHttpGet()
    {
        return strcasecmp($_SERVER['REQUEST_METHOD'], 'get') === 0;
    }

    /**
     * @return bool
     */
    private function isHttpPost()
    {
        return strcasecmp($_SERVER['REQUEST_METHOD'], 'post') === 0;
    }

    /**
     * @return string
     */
    private function getRawHttpPost()
    {
        return trim(file_get_contents('php://input'));
    }

    /**
     * @return string
     */
    private function getQueryString()
    {
        return rawurldecode($_SERVER['QUERY_STRING']);
    }

    /**
     * @param string $type
     * @return bool
     */
    private function isContentType($type)
    {
        return isset($_SERVER['CONTENT_TYPE']) && strcmp($_SERVER['CONTENT_TYPE'], $type) === 0;
    }

    /**
     * @return bool
     */
    private function isContentTypeJson()
    {
        return $this->isContentType('application/json');
    }

    /**
     * @return bool
     */
    private function isContentTypeFormEncoded()
    {
        return $this->isContentType('application/x-www-form-urlencoded');
    }

    /**
     * @return bool
     */
    private function isOptRequireHttps()
    {
        return (bool)($this->options | self::OPT_REQUIRE_HTTPS);
    }

    /**
     * @return bool
     */
    private function isOptRequireContentType()
    {
        return (bool)($this->options | self::OPT_REQUIRE_CONTENT_TYPE);
    }

    /**
     * @return bool
     */
    private function isOptSendOutputHeaders()
    {
        return (bool)($this->options | self::OPT_SEND_OUTPUT_HEADERS);
    }

    /**
     * @return bool
     */
    private function isSourcePost()
    {
        return (bool)($this->options | self::SOURCE_POST_BODY | self::SOURCE_POST_FORM_ENCODED);
    }

    /**
     * @return bool
     */
    private function isSourcePostBody()
    {
        return (bool)($this->options | self::SOURCE_POST_BODY);
    }

    /**
     * @return bool
     */
    private function isSourcePostFormEncoded()
    {
        return (bool)($this->options | self::SOURCE_POST_FORM_ENCODED);
    }

    /**
     * @return bool
     */
    private function isSourceGet()
    {
        return (bool)($this->options | self::SOURCE_GET_PARAMS | self::SOURCE_GET_QUERY_STRING);
    }

    /**
     * @return bool
     */
    private function isSourceGetParams()
    {
        return (bool)($this->options | self::SOURCE_GET_PARAMS);
    }

    /**
     * @return bool
     */
    private function isSourceGetQueryString()
    {
        return (bool)($this->options | self::SOURCE_GET_QUERY_STRING);
    }
}