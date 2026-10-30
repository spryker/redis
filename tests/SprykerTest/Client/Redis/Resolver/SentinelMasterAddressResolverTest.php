<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\Redis\Resolver;

use Codeception\Test\Unit;
use RedisException;
use RedisSentinel;
use Spryker\Client\Redis\Exception\ConnectionConfigurationException;
use Spryker\Client\Redis\Resolver\SentinelMasterAddressResolver;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group Redis
 * @group Resolver
 * @group SentinelMasterAddressResolverTest
 * Add your own group annotations below this line
 */
class SentinelMasterAddressResolverTest extends Unit
{
    protected const string SERVICE_NAME = 'mymaster';

    public function testResolveMasterConnectionParametersReturnsAddressFromFirstRespondingSentinel(): void
    {
        // Arrange
        $sentinelMock = $this->createMock(RedisSentinel::class);
        $sentinelMock->method('getMasterAddrByName')
            ->with(static::SERVICE_NAME)
            ->willReturn(['redis-master', '6380']);

        $resolver = $this->createResolverWithSentinels([$sentinelMock]);

        // Act
        $masterConnectionParameters = $resolver->resolveMasterConnectionParameters(
            ['tcp://sentinel-1:26379'],
            static::SERVICE_NAME,
        );

        // Assert
        $this->assertSame(['host' => 'redis-master', 'port' => 6380], $masterConnectionParameters);
    }

    public function testResolveMasterConnectionParametersFallsBackToNextSentinelWhenFirstOneIsUnreachable(): void
    {
        // Arrange
        $unreachableSentinelMock = $this->createMock(RedisSentinel::class);
        $unreachableSentinelMock->method('getMasterAddrByName')->willThrowException(new RedisException('connection refused'));

        $respondingSentinelMock = $this->createMock(RedisSentinel::class);
        $respondingSentinelMock->method('getMasterAddrByName')
            ->with(static::SERVICE_NAME)
            ->willReturn(['redis-master', '6380']);

        $resolver = $this->createResolverWithSentinels([$unreachableSentinelMock, $respondingSentinelMock]);

        // Act
        $masterConnectionParameters = $resolver->resolveMasterConnectionParameters(
            ['tcp://sentinel-1:26379', 'tcp://sentinel-2:26379'],
            static::SERVICE_NAME,
        );

        // Assert
        $this->assertSame(['host' => 'redis-master', 'port' => 6380], $masterConnectionParameters);
    }

    public function testResolveMasterConnectionParametersThrowsWhenNoSentinelCanResolveTheMaster(): void
    {
        // Arrange
        $unreachableSentinelMock = $this->createMock(RedisSentinel::class);
        $unreachableSentinelMock->method('getMasterAddrByName')->willThrowException(new RedisException('connection refused'));

        $resolver = $this->createResolverWithSentinels([$unreachableSentinelMock, $unreachableSentinelMock]);

        // Assert
        $this->expectException(ConnectionConfigurationException::class);

        // Act
        $resolver->resolveMasterConnectionParameters(
            ['tcp://sentinel-1:26379', 'tcp://sentinel-2:26379'],
            static::SERVICE_NAME,
        );
    }

    public function testCreateRedisSentinelUsesAConstructorCallShapeTheInstalledPhpredisExtensionAccepts(): void
    {
        // Arrange
        $resolver = new SentinelMasterAddressResolver();

        // Act
        $sentinel = $resolver->createRedisSentinel('127.0.0.1', 1);

        // Assert
        $this->assertInstanceOf(RedisSentinel::class, $sentinel);
    }

    public function testResolveMasterConnectionParametersThrowsRatherThanAnArgumentErrorWhenRealSentinelIsUnreachable(): void
    {
        // Arrange
        $resolver = new SentinelMasterAddressResolver();

        // Assert
        $this->expectException(ConnectionConfigurationException::class);

        // Act
        $resolver->resolveMasterConnectionParameters(['tcp://127.0.0.1:1'], static::SERVICE_NAME);
    }

    /**
     * @param array<\RedisSentinel> $sentinelMocks
     */
    protected function createResolverWithSentinels(array $sentinelMocks): SentinelMasterAddressResolver
    {
        return new class ($sentinelMocks) extends SentinelMasterAddressResolver {
            /**
             * @param array<\RedisSentinel> $sentinelMocks
             */
            public function __construct(protected array $sentinelMocks)
            {
            }

            public function createRedisSentinel(string $host, int $port): RedisSentinel
            {
                return array_shift($this->sentinelMocks);
            }
        };
    }
}
