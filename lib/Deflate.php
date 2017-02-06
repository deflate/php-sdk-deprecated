<?php

/**
 * Class Deflate
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Deflate
{
    /**
     * The URL for the API
     */
    const API_URL = 'https://api.deflate.io/v1/';

    /**
     * The current SDK version
     */
    const SDK_VERSION = '1.0.0';

    /**
     * API Key
     *
     * @var string
     */
    private $apiKey;

    /**
     * API secret
     *
     * @var string
     */
    private $apiSecret;

    /**
     * Pass in the api credentials to create a connection with Deflate
     *
     * @param $apiKey
     * @param $apiSecret
     *
     * @throws Exception
     */
    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        // Check we can login with the details provided
        if (!$this->account()) {
            throw new Exception('Unable to connect to Deflate API using provided credentials.');
        }
    }

    /**
     * Grab the account information
     *
     * @return bool|mixed
     */
    public function account()
    {
        if ($response = $this->_request('account')) {
            if (isset($response->success) && $response->success == true) {
                return $response->account;
            }
        }

        return false;
    }

    /**
     * Make a request to the API
     *
     * @param            $action
     * @param bool|false $data
     *
     * @return mixed
     */
    private function _request($action, $data = false)
    {
        // Get cURL resource
        $curl = curl_init();

        // Start building our body
        $body = array(
            'auth' => array(
                'api_key'    => $this->apiKey,
                'api_secret' => $this->apiSecret
            )
        );

        // Merge in the data
        if ($data !== false && is_array($data)) {
            $body = array_merge($body, $data);
        }

        // Set the basic CURL requests options
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => self::API_URL . $action,
            CURLOPT_USERAGENT      => 'Deflate SDK - Version ' . self::SDK_VERSION,
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => json_encode($body)
        ));

        // Send the request & save response to $resp
        $resp = curl_exec($curl);

        // Close request to clear up some resources
        curl_close($curl);

        return json_decode($resp);
    }

    /**
     * Compress an individual image
     *
     * @param      $image
     * @param      $type
     * @param bool $id
     * @param bool $wait
     * @param bool $callback
     * @param bool $custom
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function compress($image, $type, $id = false, $wait = true, $callback = false, $custom = false)
    {
        // Build our request
        $request = array(
            'image' => $image,
            'type'  => $type,
            'wait'  => $wait
        );

        // Include an ID with the request
        if ($id !== false) {
            $request['id'] = $id;
        }

        // Verify that we have a callback
        if ($wait === false && $callback === false) {
            throw new Exception('A callback must be defined when you\'re not wanting to wait.');
        }

        // Include the fallback in the request
        if ($callback) {
            $request['callback'] = $callback;
        }

        // Pass any custom data over
        if ($custom) {
            $request['custom'] = $custom;
        }

        if ($response = $this->_request('deflate', $request)) {
            if (isset($response->success) && $response->success == true) {
                return $response;
            }
        }

        return false;
    }

    /**
     * Attempt to compress multiple images
     *
     * @param            $images
     * @param            $type
     * @param bool|true  $wait
     * @param bool|false $callback
     * @param bool|false $custom
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function compressMultiple($images, $type, $wait = true, $callback = false, $custom = false)
    {
        // Build our request
        $request = array(
            'images' => $images,
            'type'   => $type,
            'wait'   => $wait
        );

        // Verify that we have a callback
        if ($wait === false && $callback === false) {
            throw new Exception('A callback must be defined when you\'re not wanting to wait.');
        }

        // Include the fallback in the request
        if ($callback) {
            $request['callback'] = $callback;
        }

        // Pass any custom data over
        if ($custom) {
            $request['custom'] = $custom;
        }

        if ($response = $this->_request('deflate', $request)) {
            if (isset($response->success) && $response->success == true) {
                return $response;
            }
        }

        return false;
    }

    /**
     * Return the total limit of images that can be compressed at once
     *
     * @return bool
     */
    public function limit()
    {
        if ($response = $this->_request('limit')) {
            if (isset($response->limit) && $response->limit == true) {
                return $response->limit;
            }
        }

        return false;
    }

    /**
     * Return the supported image formats
     *
     * @param bool $type
     *
     * @return bool|mixed
     */
    public function supported($type = false)
    {
        if ($response = $this->_request('supported')) {
            if (isset($response->success) && $response->success == true) {
                switch ($type) {
                    case 'extensions':
                        return $response->extensions;
                        break;
                    case 'mime':
                        return $response->types;
                        break;
                    default:
                        return $response;
                        break;
                }
            }
        }

        return false;
    }

    /**
     * Return the callback response as an array
     *
     * @return mixed
     */
    public function callbackResponse()
    {
        return json_decode(file_get_contents('php://input'));
    }
}
