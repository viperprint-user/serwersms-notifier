<?php

declare(strict_types=1);

namespace Symfony\Component\Notifier\Bridge\Serwersms\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\Bridge\Serwersms\SerwersmsTransportFactory;
use Symfony\Component\Notifier\Exception\IncompleteDsnException;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\Dsn;

final class SerwersmsTransportFactoryTest extends TestCase
{
    public function testCreateWithDsn(): void
    {
        $factory = new SerwersmsTransportFactory();

        $dsn = 'serwersms://login:pass@default';
        $transport = $factory->create(Dsn::fromString($dsn));
        $transport->setHost('host.test');

        $this->assertSame('serwersms://host.test', (string) $transport);
    }

    public function testSupportsSerwersmsScheme(): void
    {
        $factory = new SerwersmsTransportFactory();

        $this->assertTrue($factory->supports(Dsn::fromString('serwersms://host/path')));
        $this->assertFalse($factory->supports(Dsn::fromString('somethingElse://host/path')));
    }

    public function testNonSerwersmsSchemeThrows(): void
    {
        $factory = new SerwersmsTransportFactory();

        $this->expectException(UnsupportedSchemeException::class);

        $factory->create(Dsn::fromString('somethingElse://login:pass@default'));
    }

    public function testCreateWithoutAuthorization(): void
    {
        $factory = new SerwersmsTransportFactory();

        $this->expectException(IncompleteDsnException::class);

        $factory->create(Dsn::fromString('serwersms://default'));
    }
}
