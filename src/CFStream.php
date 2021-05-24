<?php

namespace Fishr01\CFStream;

use GuzzleHttp\Client;
use Fishr01\CFStream\Exceptions\InvalidFileException;
use Fishr01\CFStream\Exceptions\InvalidOriginsException;
use Fishr01\CFStream\Exceptions\OperationFailedException;
use Fishr01\CFStream\Exceptions\InvalidCredentialsException;

class CFStream
{
    private $key;
    private $zone;
    private $email;

    /**
     * Initialize CFStream with authentication credentials.
     *
     * @param string $key
     * @param string $zone
     * @param string $email
     */
    public function __construct($key, $zone, $email)
    {
        if (empty($key) || empty($zone) || empty($email)) {
            throw new InvalidCredentialsException();
        }

        $this->key = $key;
        $this->zone = $zone;
        $this->email = $email;

        $this->client = new Client();
    }

    /**
     * Get the status of a video.
     *
     * @param string $resourceUrl
     *
     * @return json Response body contents
     */
    public function status($resourceUrl)
    {
        $response = $this->client->get($resourceUrl, [
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Upload a video with a given filepath.
     *
     * @param string $filepath
     *
     * @return string $resourceUrl URL to manage the video resource
     */
    public function upload($filepath)
    {
        $file = fopen($filepath, 'r');
        if (!$file) {
            throw new InvalidFileException();
        }

        $filesize = filesize($filepath);
        $filename = basename($filepath);

        $response = $this->client->post("https://api.cloudflare.com/client/v4/accounts/{$this->zone}/stream/copy", [
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
                'Content-Length' => $filesize,
                'Content-Type' => 'application/offset+octet-stream',
                'Tus-Resumable' => '1.0.0',
                'Upload-Offset' => 0,
            ],
            'body' => $file,
        ]);

        if (204 != $response->getStatusCode()) {
            throw new OperationFailedException();
        }

        return $response;
    }


    /**
     * Delete video from Cloudflare Stream.
     *
     * @param string $resourceUrl
     */
    public function delete($resourceUrl)
    {
        $response = $this->client->delete($resourceUrl, [
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
                'Content-Length' => 0,
            ],
        ]);

        if (204 != $response->getStatusCode()) {
            throw new OperationFailedException();
        }
    }

    /**
     * Get embed code for the video.
     *
     * @param string $resourceUrl
     *
     * @return string HTML embed code
     */
    public function code($resourceUrl)
    {
        $response = $this->client->get("{$resourceUrl}/embed", [
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
                'Content-Type' => 'application/json',
            ],
        ]);

        if (200 != $response->getStatusCode()) {
            throw new OperationFailedException();
        }

        return $response->getBody()->getContents();
    }

    /**
     * Set allowedOrigins on the video.
     *
     * @param string $resourceUrl
     * @param string $origins     Comma separated hostnames
     */
    public function allow($resourceUrl, $origins)
    {
        if (false !== strpos($origins, '/')) {
            throw new InvalidOriginsException();
        }

        $videoId = @end(explode('/', $resourceUrl));

        $response = $this->client->post($resourceUrl, [
            'body' => "{\"uid\": \"{$videoId}\", \"allowedOrigins\": [\"{$origins}\"]}",
            'headers' => [
                'X-Auth-Key' => $this->key,
                'X-Auth-Email' => $this->email,
            ],
        ]);

        if (200 != $response->getStatusCode()) {
            throw new OperationFailedException();
        }
    }
}
