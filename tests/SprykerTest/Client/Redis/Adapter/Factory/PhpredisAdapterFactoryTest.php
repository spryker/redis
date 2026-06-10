<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\Redis\Adapter\Factory;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\RedisConfigurationTransfer;
use Generated\Shared\Transfer\RedisCredentialsTransfer;
use Spryker\Client\Redis\Adapter\Factory\PhpredisAdapterFactory;
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
 * @group PhpredisAdapterFactoryTest
 * Add your own group annotations below this line
 */
class PhpredisAdapterFactoryTest extends Unit
{
    protected RedisClientTester $tester;

    protected const string HOST = 'redis-host';

    protected const string PORT = '6379';

    public function testBuildPhpredisOptionsPrefixesHostWithTlsWhenTlsEnabled(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT)
                ->setScheme('tls'),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertStringStartsWith('tls://', $options['host']);
    }

    public function testBuildPhpredisOptionsDoesNotPrefixHostWhenTlsNotEnabled(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT)
                ->setScheme('tcp'),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertSame(static::HOST, $options['host']);
    }

    public function testBuildPhpredisOptionsIncludesPersistentWhenIsPersistentEnabled(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT)
                ->setIsPersistent(true),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertTrue($options['persistent']);
    }

    public function testBuildPhpredisOptionsOmitsPersistentWhenIsPersistentNotSet(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertArrayNotHasKey('persistent', $options);
    }

    public function testBuildPhpredisOptionsIncludesAuthWhenPasswordProvided(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT)
                ->setPassword('secret'),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertSame('secret', $options['auth']);
    }

    public function testBuildPhpredisOptionsIncludesUsernameAndPasswordWhenBothProvided(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT)
                ->setUsername('alice')
                ->setPassword('secret'),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertSame(['alice', 'secret'], $options['auth']);
    }

    public function testBuildPhpredisOptionsDisablesPeerVerificationWhenTlsEnabledWithoutCaFile(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT)
                ->setScheme('tls'),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertFalse($options['ssl']['verify_peer']);
        $this->assertFalse($options['ssl']['verify_peer_name']);
        $this->assertArrayNotHasKey('cafile', $options['ssl']);
    }

    public function testBuildPhpredisOptionsEnablesPeerVerificationWhenTlsEnabledWithCaFile(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT)
                ->setScheme('tls')
                ->setSslCaFilePath('/etc/ssl/redis-ca.crt'),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertSame('/etc/ssl/redis-ca.crt', $options['ssl']['cafile']);
        $this->assertTrue($options['ssl']['verify_peer']);
        $this->assertTrue($options['ssl']['verify_peer_name']);
    }

    public function testBuildPhpredisOptionsOmitsSslKeyWhenTlsNotEnabled(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT)
                ->setScheme('tcp'),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertArrayNotHasKey('ssl', $options);
    }

    public function testBuildPhpredisOptionsEnablesTlsWhenGlobalSchemeIsTls(): void
    {
        // Arrange
        $this->tester->setConfig(RedisConstants::REDIS_SCHEME, 'tls');
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT),
        );

        // Act
        $options = $factory->buildPhpredisOptions(
            $configurationTransfer,
            ['host' => static::HOST, 'port' => static::PORT],
        );

        // Assert
        $this->assertStringStartsWith('tls://', $options['host']);
        $this->assertArrayHasKey('ssl', $options);
    }

    protected function buildConfigurationTransfer(RedisCredentialsTransfer $credentialsTransfer): RedisConfigurationTransfer
    {
        return (new RedisConfigurationTransfer())->setConnectionCredentials($credentialsTransfer);
    }

    protected function createFactory(): PhpredisAdapterFactory
    {
        return new PhpredisAdapterFactory(
            $this->tester->getFactory()->getConfig(),
            $this->tester->getFactory()->getUtilEncodingService(),
            $this->tester->getFactory()->createCompressor(),
        );
    }
}
