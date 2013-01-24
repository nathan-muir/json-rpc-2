<?php

namespace JsonRpc\Transport;

/**
 * A basic HttpTransport.
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

    /**
     * @var int
     */
    private $options;

    /**
     * @param int $options
     */
    public function __construct($options = 0)
    {
        $this->options = $options;
    }

    /**
     * Processes data from it's specific source, and initialise a request, or a group of requests
     *
     * @return \JsonRpc\Request|\JsonRpc\Request[]
     * @throws TransportException
     * @throws \JsonRpc\Exception
     */
    public function receive()
    {
        // check configuration options
        if ($this->isOptRequireHttps() && (empty($_SERVER['HTTPS']) || strcmp($_SERVER['HTTPS'], 'off') === 0)) {
            throw new TransportException("HTTPS is Required");
        }

        if (!$this->isHttpPost()) {
            throw new TransportException("Request must be a HTTP-POST");
        }

        if ($this->isOptRequireContentType() && !$this->isContentTypeJson()) {
            throw new TransportException("Content Type isn't application/json");
        }

        return $this->getRawHttpPost();
    }

    /**
     * @param string $response
     * @throws TransportException
     */
    public function reply($response)
    {
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
}