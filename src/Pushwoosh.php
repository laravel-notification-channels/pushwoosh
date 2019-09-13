<?php

namespace NotificationChannels\Pushwoosh;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class Pushwoosh
{
    protected $application;
    protected $client;
    protected $token;

    /**
     * Create a new Pushwoosh API client.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param string $application
     * @param string $token
     * @return void
     */
    public function __construct(ClientInterface $client, $application, $token)
    {
        $this->application = $application;
        $this->client = $client;
        $this->token = $token;
    }

    /**
     * Create the given message in the Pushwoosh API.
     *
     * @param \NotificationChannels\Pushwoosh\PushwooshPendingMessage $message
     * @return string[]
     */
    public function createMessage(PushwooshPendingMessage $message)
    {
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = \GuzzleHttp\json_encode(['request' => $message]);
        $request = new Request('POST', 'https://cp.pushwoosh.com/json/1.3/createMessage', $headers, $payload);

        try {
            $response = $this->client->send($request);
        } catch (GuzzleException $exception) {
            throw PushwooshException::failedTransmission($exception);
        }

        $response = \GuzzleHttp\json_decode($response->getBody()->getContents());

        if (isset($response->status_code) && $response->status_code !== 200) {
            throw PushwooshException::apiError($response);
        }

        if (isset($response->response->UnknownDevices)) {
            throw PushwooshException::unknownDevices($response);
        }

        $message->wasSent();

        if (isset($response->response->Messages)) {
            return $response->response->Messages;
        }

        return [];
    }

    /**
     * Get the Pushwoosh API token.
     *
     * @return string
     */
    public function getApiToken()
    {
        return $this->token;
    }

    /**
     * Get the Pushwoosh application code.
     *
     * @return string
     */
    public function getApplicationCode()
    {
        return $this->application;
    }

    /**
     * Send the message.
     *
     * @param \NotificationChannels\Pushwoosh\PushwooshMessage $message
     * @return \NotificationChannels\Pushwoosh\PushwooshPendingMessage
     */
    public function send(PushwooshMessage $message)
    {
        return (new PushwooshPendingMessage($this))->queue($message);
    }
}
