<?php

namespace NotificationChannels\Pushwoosh;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Events\Dispatcher;
use NotificationChannels\Pushwoosh\Concerns\DetectsPushwooshErrors;
use NotificationChannels\Pushwoosh\Events\UnknownDevices;
use NotificationChannels\Pushwoosh\Exceptions\PushwooshException;
use Throwable;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class Pushwoosh
{
    use DetectsPushwooshErrors;

    protected $application;
    protected $client;
    protected $dispatcher;
    protected $enabled;
    protected $token;

    /**
     * Create a new Pushwoosh API client.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher
     * @param string|null $application
     * @param string|null $token
     * @param bool $enabled
     * @return void
     */
    public function __construct(ClientInterface $client, Dispatcher $dispatcher, ?string $application, ?string $token, bool $enabled = true)
    {
        $this->application = $application;
        $this->client = $client;
        $this->dispatcher = $dispatcher;
        $this->enabled = $enabled;
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
        if (!$this->enabled) {
            return [];
        }

        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        $payload = json_encode(['request' => $message]);
        $request = new Request('POST', 'https://cp.pushwoosh.com/json/1.3/createMessage', $headers, $payload);

        try {
            $response = $this->client->send($request);
        } catch (Throwable $e) {
            $response = $this->tryAgainIfCausedByPushwooshServerError($request, $e);
        }

        $response = json_decode($response->getBody()->getContents());

        if (isset($response->status_code) && $response->status_code !== 200) {
            throw new PushwooshException($response->status_message);
        }

        if (isset($response->response->UnknownDevices)) {
            foreach ($response->response->UnknownDevices as $identifier => $devices) {
                $this->dispatcher->dispatch(new UnknownDevices($identifier, $devices));
            }
        }

        $message->wasSent();

        if (isset($response->response->Messages)) {
            # Pushwoosh will not assign IDs to messages sent to less than 10 unique devices
            return array_map(function (string $identifier) {
                return $identifier !== 'CODE_NOT_AVAILABLE' ? $identifier : null;
            }, $response->response->Messages);
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

    /**
     * Handle a Pushwoosh communication error.
     *
     * @param \GuzzleHttp\Psr7\Request $request
     * @param \Throwable $e
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function tryAgainIfCausedByPushwooshServerError(Request $request, Throwable $e)
    {
        if ($this->causedByPushwooshServerError($e)) {
            try {
                return $this->client->send($request);
            } catch (Throwable $e) {
                // Do nothing...
            }
        }

        throw new PushwooshException('Failed to create message(s)', 0, $e);
    }
}
