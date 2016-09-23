<?php

namespace RocketChat;

/**
 * Simple PHP RocketChat client.
 *
 * @author Fogarasi Ferenc <ffogarasi at gmail dot com>
 * Website: http://github.com/ffogarasi/rocket-chat-api
 *
 * @property Api\User $user
 * @property Api\Channel $channel
 */
class Client
{
    /**
     * Value for CURLOPT_SSL_VERIFYHOST.
     *
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
     */
    const SSL_VERIFYHOST = 2;

    /**
     * @var array
     */
    private static $defaultPorts = array(
        'http' => 80,
        'https' => 443,
    );

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $authToken;

    /**
     * @var string|null
     */
    private $authUserId;

    /**
     * @var bool
     */
    private $checkSslCertificate = false;

    /**
     * @var bool
     */
    private $checkSslHost = false;

    /**
     * @var int
     */
    private $sslVersion = 0;

    /**
     * @var array APIs
     */
    private $apis = array();

    /**
     * @var int|null response code, null if request is not still completed
     */
    private $responseCode = null;

    /**
     * @var array cURL options
     */
    private $curlOptions = array();

    /**
     * Error strings if json is invalid.
     */
    private static $jsonErrors = array(
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
    );

    private $classes = array(
        'user' => 'User',
        'chanell' => 'Channel',
    );

    /**
     * @param string $url
     * @param string $authToken
     * @param string|null $authUserId
     */
    public function __construct($url, $authToken = null, $authUserId = null)
    {
        $this->url = $url;
        $this->getPort();
        $this->authToken = $authToken;
        $this->authUserId = $authUserId;
    }

    /**
     * PHP getter magic method.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Api\AbstractApi
     */
    public function __get($name)
    {
        return $this->api($name);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Api\AbstractApi
     */
    public function api($name)
    {
        if (!isset($this->classes[$name])) {
            throw new \InvalidArgumentException();
        }
        if (isset($this->apis[$name])) {
            return $this->apis[$name];
        }
        $class = 'RocketChat\Api\\'.$this->classes[$name];
        $this->apis[$name] = new $class($this);

        return $this->apis[$name];
    }

    /**
     * Returns Url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * HTTP GETs a json $path and tries to decode it.
     *
     * @param string $path
     * @param bool   $decode
     *
     * @return array
     */
    public function get($path, $decode = true, $version = 1)
    {
        if (false === $json = $this->runRequest($path, $version, 'GET')) {
            return false;
        }

        if (!$decode) {
            return $json;
        }

        return $this->decode($json);
    }

    /**
     * Decodes json response.
     *
     * Returns $json if no error occured during decoding but decoded value is
     * null.
     *
     * @param string $json
     *
     * @return array|string
     */
    public function decode($json)
    {
        $decoded = json_decode($json, true);
        if (null !== $decoded) {
            return $decoded;
        }
        if (JSON_ERROR_NONE === json_last_error()) {
            return $json;
        }

        return self::$jsonErrors[json_last_error()];
    }

    /**
     * HTTP POSTs $params to $path.
     *
     * @param string $path
     * @param string $data
     *
     * @return mixed
     */
    public function post($path, $data, $version = 1)
    {
        return $this->runRequest($path, $version, 'POST', $data);
    }

    /**
     * HTTP PUTs $params to $path.
     *
     * @param string $path
     * @param string $data
     *
     * @return array
     */
    public function put($path, $data, $version = 1)
    {
        return $this->runRequest($path, $version, 'PUT', $data);
    }

    /**
     * HTTP PUTs $params to $path.
     *
     * @param string $path
     *
     * @return array
     */
    public function delete($path, $version = 1)
    {
        return $this->runRequest($path, $version, 'DELETE');
    }

    /**
     * Turns on/off ssl certificate check.
     *
     * @param bool $check
     *
     * @return Client
     */
    public function setCheckSslCertificate($check = false)
    {
        $this->checkSslCertificate = $check;

        return $this;
    }

    /**
     * Get the on/off flag for ssl certificate check.
     *
     * @return bool
     */
    public function getCheckSslCertificate()
    {
        return $this->checkSslCertificate;
    }

    /**
     * Turns on/off ssl host certificate check.
     *
     * @param bool $check
     *
     * @return Client
     */
    public function setCheckSslHost($check = false)
    {
        // Make sure verify value is set to "2" for boolean argument
        // @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
        if (true === $check) {
            $check = self::SSL_VERIFYHOST;
        }
        $this->checkSslHost = $check;

        return $this;
    }

    /**
     * Get the on/off flag for ssl host certificate check.
     *
     * @return bool
     */
    public function getCheckSslHost()
    {
        return $this->checkSslHost;
    }

    /**
     * Forces the SSL/TLS version to use.
     *
     * @see http://curl.haxx.se/libcurl/c/CURLOPT_SSLVERSION.html
     *
     * @param int $sslVersion
     *
     * @return Client
     */
    public function setSslVersion($sslVersion = 0)
    {
        $this->sslVersion = $sslVersion;

        return $this;
    }

    /**
     * Returns the SSL Version used.
     *
     * @return int
     */
    public function getSslVersion()
    {
        return $this->sslVersion;
    }

    /**
     * Turns on/off http auth.
     *
     * @param bool $use
     *
     * @return Client
     */
    public function setUseHttpAuth($use = true)
    {
        $this->useHttpAuth = $use;

        return $this;
    }

    /**
     * Get the on/off flag for http auth.
     *
     * @return bool
     */
    public function getUseHttpAuth()
    {
        return $this->useHttpAuth;
    }

    /**
     * Set the port of the connection.
     *
     * @param int $port
     *
     * @return Client
     */
    public function setPort($port = null)
    {
        if (null !== $port) {
            $this->port = (int) $port;
        }

        return $this;
    }

    /**
     * Returns response code.
     *
     * @return int
     */
    public function getResponseCode()
    {
        return (int) $this->responseCode;
    }

    /**
     * Returns the port of the current connection,
     * if not set, it will try to guess the port
     * from the url of the client.
     *
     * @return int the port number
     */
    public function getPort()
    {
        if (null !== $this->port) {
            return $this->port;
        }

        $tmp = parse_url($this->getUrl());
        if (isset($tmp['port'])) {
            $this->setPort($tmp['port']);
        } elseif (isset($tmp['scheme'])) {
            $this->setPort(self::$defaultPorts[$tmp['scheme']]);
        }

        return $this->port;
    }
    /**
     * Set a cURL option.
     *
     * @param int   $option The CURLOPT_XXX option to set
     * @param mixed $value  The value to be set on option
     *
     * @return Client
     */
    public function setCurlOption($option, $value)
    {
        $this->curlOptions[$option] = $value;

        return $this;
    }

    /**
     * Get all set cURL options.
     *
     * @return array
     */
    public function getCurlOptions()
    {
        return $this->curlOptions;
    }

    /**
     * Prepare the request by setting the cURL options.
     *
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @return resource a cURL handle on success, <b>FALSE</b> on errors.
     */
    public function prepareRequest($path, $method = 'GET', $data = '', $version = 1)
    {
        $this->responseCode = null;
        $this->curlOptions = array();
        $curl = curl_init();

        // General cURL options
        $this->setCurlOption(CURLOPT_VERBOSE, 0);
        $this->setCurlOption(CURLOPT_HEADER, 0);
        $this->setCurlOption(CURLOPT_RETURNTRANSFER, 1);

        // Host and request options
        $this->setCurlOption(CURLOPT_URL, $this->url."api/v{$version}".$path);
        $this->setCurlOption(CURLOPT_PORT, $this->getPort());
        if (80 !== $this->getPort()) {
            $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $this->checkSslCertificate);
            $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, $this->checkSslHost);
            $this->setCurlOption(CURLOPT_SSLVERSION, $this->sslVersion);
        }

        // Additional request headers
        $httpHeader = array(
            'Expect: ',
        );

        // Content type headers
	$httpHeader[] = 'Content-Type: application/json';

        if ($this->authToken && $this->authUserId) {
            $httpHeader[] = 'X-Auth-Token: '.$this->authToken;
            $httpHeader[] = 'X-User-Id: '.$this->authUserId;
        }

        // Set the HTTP request headers
        $this->setCurlOption(CURLOPT_HTTPHEADER, $httpHeader);

        switch ($method) {
            case 'POST':
                $this->setCurlOption(CURLOPT_POST, 1);
                if (isset($data)) {
                    $this->setCurlOption(CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'PUT':
                $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'PUT');
                if (isset($data)) {
                    $this->setCurlOption(CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'DELETE':
                $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default: // GET
                break;
        }

        // Set all cURL options to the current cURL resource
        curl_setopt_array($curl, $this->getCurlOptions());

        return $curl;
    }

    /**
     * Process the cURL response.
     *
     * @param string $response
     * @param string $contentType
     *
     * @throws \Exception If anything goes wrong on curl request
     *
     * @return bool|SimpleXMLElement|string
     */
    public function processCurlResponse($response, $contentType)
    {
        if ($response) {
            return $response;
        }

        return false;
    }

    /**
     * @codeCoverageIgnore Ignore due to untestable curl_* function calls.
     *
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @throws \Exception If anything goes wrong on curl request
     *
     * @return bool|SimpleXMLElement|string
     */
    protected function runRequest($path, $version, $method = 'GET', $data = '')
    {
        $curl = $this->prepareRequest($path, $method, $data, $version);
        
        $response = trim(curl_exec($curl));
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        if (curl_errno($curl)) {
            $e = new \Exception(curl_error($curl), curl_errno($curl));
            curl_close($curl);
            throw $e;
        }
        curl_close($curl);

        return $this->processCurlResponse($response, $contentType);
    }
}
