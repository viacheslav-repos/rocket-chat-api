<?php

namespace RocketChat\Api;

use RocketChat\Client;

/**
 * Abstract class for Api classes.
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
abstract class AbstractApi
{
    /**
     * The client.
     *
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Returns whether or not the last api call failed.
     *
     * @return bool
     */
    public function lastCallFailed()
    {
        $code = $this->client->getResponseCode();

        return 200 !== $code && 201 !== $code;
    }

    /**
     * Perform the client get() method.
     *
     * @param string $path
     *
     * @return array
     */
    protected function get($path, $decode = true)
    {
        return $this->client->get($path, $decode);
    }

    /**
     * Perform the client post() method.
     *
     * @param string $path
     * @param string $data
     *
     * @return string|false
     */
    protected function post($path, $data)
    {
        return $this->client->post($path, $data);
    }

    /**
     * Perform the client put() method.
     *
     * @param string $path
     * @param string $data
     *
     * @return string|false
     */
    protected function put($path, $data)
    {
        return $this->client->put($path, $data);
    }

    /**
     * Perform the client delete() method.
     *
     * @param string $path
     *
     * @return array
     */
    protected function delete($path)
    {
        return $this->client->delete($path);
    }

    /**
     * Checks if the variable passed is not null.
     *
     * @param mixed $var Variable to be checked
     *
     * @return bool
     */
    protected function isNotNull($var)
    {
        return
            false !== $var &&
            null !== $var &&
            '' !== $var &&
            !((is_array($var) || is_object($var)) && empty($var));
    }

    /**
     * @param array $defaults
     * @param array $params
     *
     * @return array
     */
    protected function sanitizeParams(array $defaults, array $params)
    {
        return array_filter(
            array_merge($defaults, $params),
            array($this, 'isNotNull')
        );
    }
}
