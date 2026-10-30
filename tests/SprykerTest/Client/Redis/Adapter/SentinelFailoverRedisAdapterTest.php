<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\Redis\Adapter;

use Codeception\Test\Unit;
use RedisException;
use Spryker\Client\Redis\Adapter\RedisAdapterInterface;
use Spryker\Client\Redis\Adapter\SentinelFailoverRedisAdapter;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group Redis
 * @group Adapter
 * @group SentinelFailoverRedisAdapterTest
 * Add your own group annotations below this line
 */
class SentinelFailoverRedisAdapterTest extends Unit
{
    public function testGetDelegatesToWrappedAdapterWhenItSucceeds(): void
    {
        // Arrange
        $adapterMock = $this->createMock(RedisAdapterInterface::class);
        $adapterMock->expects($this->once())->method('get')->with('key')->willReturn('value');

        $sentinelFailoverRedisAdapter = new SentinelFailoverRedisAdapter($adapterMock, fn (): RedisAdapterInterface => $adapterMock);

        // Act
        $result = $sentinelFailoverRedisAdapter->get('key');

        // Assert
        $this->assertSame('value', $result);
    }

    public function testGetReconnectsToNewlyDiscoveredMasterAndRetriesWhenWrappedAdapterThrows(): void
    {
        // Arrange
        $failingAdapterMock = $this->createMock(RedisAdapterInterface::class);
        $failingAdapterMock->method('get')->willThrowException(new RedisException('connection lost'));

        $recoveredAdapterMock = $this->createMock(RedisAdapterInterface::class);
        $recoveredAdapterMock->expects($this->once())->method('get')->with('key')->willReturn('value');

        $sentinelFailoverRedisAdapter = new SentinelFailoverRedisAdapter(
            $failingAdapterMock,
            fn (): RedisAdapterInterface => $recoveredAdapterMock,
        );

        // Act
        $result = $sentinelFailoverRedisAdapter->get('key');

        // Assert
        $this->assertSame('value', $result);
    }

    public function testGetPropagatesExceptionWhenRetryAfterReconnectAlsoFails(): void
    {
        // Arrange
        $failingAdapterMock = $this->createMock(RedisAdapterInterface::class);
        $failingAdapterMock->method('get')->willThrowException(new RedisException('connection lost'));

        $sentinelFailoverRedisAdapter = new SentinelFailoverRedisAdapter(
            $failingAdapterMock,
            fn (): RedisAdapterInterface => $failingAdapterMock,
        );

        // Assert
        $this->expectException(RedisException::class);

        // Act
        $sentinelFailoverRedisAdapter->get('key');
    }

    public function testDisconnectDelegatesToWrappedAdapterWithoutRetrying(): void
    {
        // Arrange
        $adapterMock = $this->createMock(RedisAdapterInterface::class);
        $adapterMock->expects($this->once())->method('disconnect');

        $sentinelFailoverRedisAdapter = new SentinelFailoverRedisAdapter($adapterMock, fn (): RedisAdapterInterface => $adapterMock);

        // Act
        $sentinelFailoverRedisAdapter->disconnect();
    }
}
