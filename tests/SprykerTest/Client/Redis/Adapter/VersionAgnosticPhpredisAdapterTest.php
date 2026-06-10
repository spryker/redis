<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\Redis\Adapter;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\RedisCredentialsTransfer;
use Redis;
use Spryker\Client\Redis\Adapter\VersionAgnosticPhpredisAdapter;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group Redis
 * @group Adapter
 * @group VersionAgnosticPhpredisAdapterTest
 * Add your own group annotations below this line
 */
class VersionAgnosticPhpredisAdapterTest extends Unit
{
    public function testConnectIsSkippedWhenAlreadyConnected(): void
    {
        // Arrange
        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('isConnected')->willReturn(true);
        $redisMock->expects($this->never())->method('connect');
        $redisMock->expects($this->never())->method('pconnect');
        $redisMock->expects($this->never())->method('select');

        $adapter = new VersionAgnosticPhpredisAdapter($redisMock);

        // Act
        $adapter->connect();
    }

    public function testConnectRestoresNonZeroDatabaseAfterReconnect(): void
    {
        // Arrange
        $database = 3;
        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('isConnected')->willReturn(false);
        $redisMock->method('getDBNum')->willReturn($database);
        $redisMock->method('getHost')->willReturn('redis-host');
        $redisMock->expects($this->once())->method('select')->with($database);

        $adapter = new VersionAgnosticPhpredisAdapter($redisMock);

        // Act
        $adapter->connect();
    }

    public function testConnectRestoresZeroDatabaseAfterReconnect(): void
    {
        // Arrange
        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('isConnected')->willReturn(false);
        $redisMock->method('getDBNum')->willReturn(0);
        $redisMock->method('getHost')->willReturn('redis-host');
        $redisMock->expects($this->once())->method('select')->with(0);

        $adapter = new VersionAgnosticPhpredisAdapter($redisMock);

        // Act
        $adapter->connect();
    }

    public function testConnectSendsAuthAfterReconnectWhenCredentialsPresent(): void
    {
        // Arrange
        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('isConnected')->willReturn(false);
        $redisMock->method('getDBNum')->willReturn(0);
        $redisMock->method('getHost')->willReturn('redis-host');
        $redisMock->method('getAuth')->willReturn(['alice', 'secret']);
        $redisMock->expects($this->once())->method('auth')->with(['alice', 'secret']);

        $adapter = new VersionAgnosticPhpredisAdapter($redisMock);

        // Act
        $adapter->connect();
    }

    public function testConnectPassesSslContextWhenTlsEnabled(): void
    {
        // Arrange
        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('isConnected')->willReturn(false);
        $redisMock->method('getDBNum')->willReturn(0);
        $redisMock->method('getHost')->willReturn('tls://redis-host');
        $redisMock->expects($this->once())->method('connect')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->callback(fn (array $context): bool => isset($context['ssl'])),
        );

        $credentialsTransfer = (new RedisCredentialsTransfer())->setScheme('tls');
        $adapter = new VersionAgnosticPhpredisAdapter($redisMock, $credentialsTransfer);

        // Act
        $adapter->connect();
    }

    public function testConnectUsesPconnectWhenPersistentEnabled(): void
    {
        // Arrange
        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('isConnected')->willReturn(false);
        $redisMock->method('getDBNum')->willReturn(0);
        $redisMock->method('getHost')->willReturn('redis-host');
        $redisMock->expects($this->once())->method('pconnect');
        $redisMock->expects($this->never())->method('connect');

        $credentialsTransfer = (new RedisCredentialsTransfer())->setIsPersistent(true);
        $adapter = new VersionAgnosticPhpredisAdapter($redisMock, $credentialsTransfer);

        // Act
        $adapter->connect();
    }
}
