<?php

declare(strict_types=1);

namespace Symfony\Component\Notifier\Bridge\Serwersms;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

final class SerwersmsTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();
        $user = $this->getUser($dsn);
        $password = $this->getPassword($dsn);
        $from = $dsn->getOption('from') ?? '';
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        if ('serwersms' === $scheme) {
            return (new SerwersmsTransport($user, $password, $from, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
        }

        throw new UnsupportedSchemeException($dsn, 'serwersms', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['serwersms'];
    }
}
