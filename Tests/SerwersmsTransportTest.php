<?php

declare(strict_types=1);

namespace Symfony\Component\Notifier\Bridge\Serwersms\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Notifier\Bridge\Serwersms\SerwersmsTransport;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class SerwersmsTransportTest extends TestCase
{
    public function testToStringContainsProperties(): void
    {
        $host = 'testHost';
        $client = $this->createMock(HttpClientInterface::class);

        $transport = new SerwersmsTransport('demo', 'password', 'demo', $client);
        $transport->setHost($host);

        $this->assertSame(sprintf('serwersms://%s', $host), (string) $transport);
    }

    public function testSupportsChatMessage(): void
    {
        $client = $this->createMock(HttpClientInterface::class);

        $transport = new SerwersmsTransport('demo', 'password', 'demo', $client);

        $this->assertTrue($transport->supports(new SmsMessage('+48500600700', 'testChatMessage')));
        $this->assertFalse($transport->supports($this->createMock(MessageInterface::class)));
    }

    public function testSendNonChatMessageThrows(): void
    {
        $this->expectException(LogicException::class);
        $client = $this->createMock(HttpClientInterface::class);
        $transport = new SerwersmsTransport('demo', 'password', 'demo', $client);

        $transport->send($this->createMock(MessageInterface::class));
    }

    public function testSendWithErrorResponseThrows(): void
    {
        $this->expectException(TransportException::class);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(400);
        $response->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode(['description' => 'testDescription', 'error_code' => 'testErrorCode']));

        $client = new MockHttpClient(static function () use ($response): ResponseInterface {
            return $response;
        });

        $transport = new SerwersmsTransport('testToken', 'testChannel', 'demo', $client);

        $transport->send(new SmsMessage('+48500600700', 'testChatMessage'));
    }
}
