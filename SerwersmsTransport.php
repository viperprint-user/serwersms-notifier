<?php

declare(strict_types=1);

namespace Symfony\Component\Notifier\Bridge\Serwersms;

use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SerwersmsTransport extends AbstractTransport
{
    protected const HOST = 'api2.serwersms.pl';

    private $username;
    private $password;
    private $from;

    public function __construct(string $username, string $password, string $from, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->from = $from;

        parent::__construct($client, $dispatcher);
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, SmsMessage::class, get_debug_type($message)));
        }

        $payload = [
            'username' => $this->username,
            'password' => $this->password,
            'phone' => $message->getPhone(),
            'text' => $message->getSubject(),
        ];
        $from = $this->from;

        if (!empty($from)) {
            $payload['from'] = $from;
        }

        $endpoint = sprintf('https://%s/messages/send_sms.json', $this->getEndpoint());
        $response = $this->client->request('POST', $endpoint, [
            'json' => $payload,
        ]);
        $body = $response->toArray(false);

        $success = filter_var(($body['success'] ?? false), FILTER_VALIDATE_BOOLEAN);

        if (!$success || 200 !== $response->getStatusCode()) {
            $contents = $body['message'] ?? '';
            $code = $body['code'] ?? null;

            throw new TransportException(sprintf('Unable to send the SMS: %s (%s).', $contents, $code), $response);
        }
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    public function __toString(): string
    {
        return sprintf('serwersms://%s', $this->getEndpoint());
    }
}
