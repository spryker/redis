<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\Redis\Adapter\Factory;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\RedisConfigurationTransfer;
use Generated\Shared\Transfer\RedisCredentialsTransfer;
use Spryker\Client\Redis\Adapter\Factory\PredisAdapterFactory;
use Spryker\Client\Redis\Adapter\KeyPrefixRedisAdapter;
use Spryker\Client\Redis\Adapter\RedisAdapterInterface;
use Spryker\Shared\Redis\RedisConstants;
use SprykerTest\Client\Redis\RedisClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group Redis
 * @group Adapter
 * @group Factory
 * @group AbstractRedisAdapterFactoryTest
 * Add your own group annotations below this line
 */
class AbstractRedisAdapterFactoryTest extends Unit
{
    protected RedisClientTester $tester;

    public function testGetConnectionParametersStripsNonConnectionFields(): void
    {
        // Arrange
        $factory = $this->createConcreteFactory();
        $configurationTransfer = (new RedisConfigurationTransfer())->setConnectionCredentials(
            (new RedisCredentialsTransfer())
                ->setHost('redis-host')
                ->setPort('6379')
                ->setScheme('tls')
                ->setIsPersistent(true)
                ->setSslCaFilePath('/etc/ssl/ca.crt'),
        );

        // Act
        $connectionParameters = $factory->getConnectionParameters($configurationTransfer);

        // Assert
        $this->assertIsArray($connectionParameters);
        $this->assertArrayNotHasKey('is_tls', $connectionParameters);
        $this->assertArrayNotHasKey('is_persistent', $connectionParameters);
        $this->assertArrayNotHasKey('ssl_ca_file_path', $connectionParameters);
    }

    public function testGetConnectionParametersPreservesStandardFields(): void
    {
        // Arrange
        $factory = $this->createConcreteFactory();
        $configurationTransfer = (new RedisConfigurationTransfer())->setConnectionCredentials(
            (new RedisCredentialsTransfer())
                ->setHost('redis-host')
                ->setPort('6379')
                ->setDatabase(1)
                ->setPassword('secret'),
        );

        // Act
        $connectionParameters = $factory->getConnectionParameters($configurationTransfer);

        // Assert
        $this->assertSame('redis-host', $connectionParameters['host']);
        $this->assertSame('6379', $connectionParameters['port']);
        $this->assertSame(1, $connectionParameters['database']);
        $this->assertSame('secret', $connectionParameters['password']);
    }

    public function testGetConnectionParametersIncludesUsernameWhenSet(): void
    {
        // Arrange
        $factory = $this->createConcreteFactory();
        $configurationTransfer = (new RedisConfigurationTransfer())->setConnectionCredentials(
            (new RedisCredentialsTransfer())
                ->setHost('redis-host')
                ->setPort('6379')
                ->setUsername('alice')
                ->setPassword('secret'),
        );

        // Act
        $connectionParameters = $factory->getConnectionParameters($configurationTransfer);

        // Assert
        $this->assertSame('alice', $connectionParameters['username']);
        $this->assertSame('secret', $connectionParameters['password']);
    }

    public function testGetConnectionParametersStripsUsernameWhenNull(): void
    {
        // Arrange
        $factory = $this->createConcreteFactory();
        $configurationTransfer = (new RedisConfigurationTransfer())->setConnectionCredentials(
            (new RedisCredentialsTransfer())
                ->setHost('redis-host')
                ->setPort('6379')
                ->setUsername(null),
        );

        // Act
        $connectionParameters = $factory->getConnectionParameters($configurationTransfer);

        // Assert
        $this->assertArrayNotHasKey('username', $connectionParameters);
    }

    public function testCreateKeyPrefixAdapterWrapsAdapterWhenPrefixIsSet(): void
    {
        // Arrange
        $this->tester->setConfig(RedisConstants::REDIS_KEY_PREFIX, 'tenant1:');
        $factory = $this->createConcreteFactory();
        $innerAdapter = $this->createMock(RedisAdapterInterface::class);

        // Act
        $result = $factory->createKeyPrefixAdapter($innerAdapter);

        // Assert
        $this->assertInstanceOf(KeyPrefixRedisAdapter::class, $result);
    }

    public function testCreateKeyPrefixAdapterReturnsOriginalAdapterWhenPrefixIsEmpty(): void
    {
        // Arrange
        $this->tester->setConfig(RedisConstants::REDIS_KEY_PREFIX, '');
        $factory = $this->createConcreteFactory();
        $innerAdapter = $this->createMock(RedisAdapterInterface::class);

        // Act
        $result = $factory->createKeyPrefixAdapter($innerAdapter);

        // Assert
        $this->assertSame($innerAdapter, $result);
    }

    public function testBuildSslOptionsDisablesPeerVerificationWhenNoCaFileConfigured(): void
    {
        // Arrange
        $this->tester->setConfig(RedisConstants::REDIS_SSL_CA_FILE_PATH, '');
        $factory = $this->createConcreteFactory();

        // Act
        $sslOptions = $factory->buildSslOptions(new RedisCredentialsTransfer());

        // Assert
        $this->assertFalse($sslOptions['verify_peer']);
        $this->assertFalse($sslOptions['verify_peer_name']);
        $this->assertArrayNotHasKey('cafile', $sslOptions);
    }

    public function testBuildSslOptionsEnablesPeerVerificationWhenCaFileSetInConfig(): void
    {
        // Arrange
        $this->tester->setConfig(RedisConstants::REDIS_SSL_CA_FILE_PATH, '/etc/ssl/redis-ca.crt');
        $factory = $this->createConcreteFactory();

        // Act
        $sslOptions = $factory->buildSslOptions(new RedisCredentialsTransfer());

        // Assert
        $this->assertSame('/etc/ssl/redis-ca.crt', $sslOptions['cafile']);
        $this->assertTrue($sslOptions['verify_peer']);
        $this->assertTrue($sslOptions['verify_peer_name']);
    }

    public function testBuildSslOptionsPrefersCaFileFromTransferOverConfig(): void
    {
        // Arrange
        $this->tester->setConfig(RedisConstants::REDIS_SSL_CA_FILE_PATH, '/etc/ssl/config-ca.crt');
        $factory = $this->createConcreteFactory();
        $credentialsTransfer = (new RedisCredentialsTransfer())->setSslCaFilePath('/etc/ssl/transfer-ca.crt');

        // Act
        $sslOptions = $factory->buildSslOptions($credentialsTransfer);

        // Assert
        $this->assertSame('/etc/ssl/transfer-ca.crt', $sslOptions['cafile']);
    }

    public function testGetConnectionParametersStripsSslCaFilePathFromCredentials(): void
    {
        // Arrange
        $factory = $this->createConcreteFactory();
        $configurationTransfer = (new RedisConfigurationTransfer())->setConnectionCredentials(
            (new RedisCredentialsTransfer())
                ->setHost('redis-host')
                ->setPort('6379')
                ->setSslCaFilePath('/etc/ssl/ca.crt'),
        );

        // Act
        $connectionParameters = $factory->getConnectionParameters($configurationTransfer);

        // Assert
        $this->assertArrayNotHasKey('ssl_ca_file_path', $connectionParameters);
    }

    protected function createConcreteFactory(): PredisAdapterFactory
    {
        return new PredisAdapterFactory(
            $this->tester->getFactory()->getConfig(),
            $this->tester->getFactory()->getUtilEncodingService(),
            $this->tester->getFactory()->createCompressor(),
        );
    }
}
