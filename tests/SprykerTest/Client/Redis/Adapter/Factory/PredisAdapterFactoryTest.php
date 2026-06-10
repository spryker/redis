<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\Redis\Adapter\Factory;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\RedisConfigurationTransfer;
use Generated\Shared\Transfer\RedisCredentialsTransfer;
use Predis\Client;
use Spryker\Client\Redis\Adapter\Factory\PredisAdapterFactory;
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
 * @group PredisAdapterFactoryTest
 * Add your own group annotations below this line
 */
class PredisAdapterFactoryTest extends Unit
{
    protected RedisClientTester $tester;

    protected const string HOST = 'redis-host';

    protected const string PORT = '6379';

    /**
     * @dataProvider provideSchemeFromCredentials
     */
    public function testCreatePredisClientUsesExpectedScheme(
        RedisCredentialsTransfer $credentialsTransfer,
        string $expectedScheme,
    ): void {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer($credentialsTransfer);

        // Act
        $predisClient = $factory->createPredisClient($configurationTransfer);

        // Assert
        $this->assertSame($expectedScheme, $this->extractSchemeFromPredisClient($predisClient));
    }

    /**
     * @return array<string, array{\Generated\Shared\Transfer\RedisCredentialsTransfer, string}>
     */
    public function provideSchemeFromCredentials(): array
    {
        return [
            'tls scheme when tls enabled' => [
                (new RedisCredentialsTransfer())->setHost(static::HOST)->setPort(static::PORT)->setScheme('tls'),
                'tls',
            ],
            'tcp scheme when tls not enabled' => [
                (new RedisCredentialsTransfer())->setHost(static::HOST)->setPort(static::PORT)->setScheme('tcp'),
                'tcp',
            ],
        ];
    }

    public function testCreatePredisClientIncludesUsernameInConnectionParametersWhenProvided(): void
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
        $predisClient = $factory->createPredisClient($configurationTransfer);

        // Assert
        $this->assertSame('alice', $predisClient->getConnection()->getParameters()->username);
        $this->assertSame('secret', $predisClient->getConnection()->getParameters()->password);
    }

    public function testCreatePredisClientIncludesUsernameWithTlsWhenBothProvided(): void
    {
        // Arrange
        $factory = $this->createFactory();
        $configurationTransfer = $this->buildConfigurationTransfer(
            (new RedisCredentialsTransfer())
                ->setHost(static::HOST)
                ->setPort(static::PORT)
                ->setScheme('tls')
                ->setUsername('alice')
                ->setPassword('secret'),
        );

        // Act
        $predisClient = $factory->createPredisClient($configurationTransfer);

        // Assert
        $this->assertSame('tls', $this->extractSchemeFromPredisClient($predisClient));
        $this->assertSame('alice', $predisClient->getConnection()->getParameters()->username);
    }

    /**
     * @dataProvider provideTlsConnectionParameters
     */
    public function testApplyTlsConnectionParameters(
        RedisCredentialsTransfer $credentialsTransfer,
        ?array $expectedSsl,
    ): void {
        // Arrange
        $factory = $this->createFactory();

        // Act
        $result = $factory->applyTlsConnectionParameters(
            ['host' => static::HOST, 'port' => static::PORT],
            $credentialsTransfer,
        );

        // Assert
        if ($expectedSsl === null) {
            $this->assertArrayNotHasKey('ssl', $result);

            return;
        }

        $this->assertSame($expectedSsl, $result['ssl']);
    }

    /**
     * @return array<string, array{\Generated\Shared\Transfer\RedisCredentialsTransfer, array<string, mixed>|null}>
     */
    public function provideTlsConnectionParameters(): array
    {
        return [
            'disables peer verification when no CA file' => [
                (new RedisCredentialsTransfer())->setScheme('tls'),
                ['verify_peer' => false, 'verify_peer_name' => false],
            ],
            'enables peer verification when CA file provided' => [
                (new RedisCredentialsTransfer())->setScheme('tls')->setSslCaFilePath('/etc/ssl/redis-ca.crt'),
                ['cafile' => '/etc/ssl/redis-ca.crt', 'verify_peer' => true, 'verify_peer_name' => true],
            ],
            'omits ssl key when tls not enabled' => [
                (new RedisCredentialsTransfer())->setScheme('tcp'),
                null,
            ],
        ];
    }

    public function testCreatePredisClientUsesTlsSchemeWhenGlobalSchemeIsTls(): void
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
        $predisClient = $factory->createPredisClient($configurationTransfer);

        // Assert
        $this->assertSame('tls', $this->extractSchemeFromPredisClient($predisClient));
    }

    protected function buildConfigurationTransfer(RedisCredentialsTransfer $credentialsTransfer): RedisConfigurationTransfer
    {
        return (new RedisConfigurationTransfer())->setConnectionCredentials($credentialsTransfer);
    }

    protected function extractSchemeFromPredisClient(Client $client): string
    {
        /** @var \Predis\Connection\StreamConnection $connection */
        $connection = $client->getConnection();

        return $connection->getParameters()->scheme;
    }

    protected function createFactory(): PredisAdapterFactory
    {
        return new PredisAdapterFactory(
            $this->tester->getFactory()->getConfig(),
            $this->tester->getFactory()->getUtilEncodingService(),
            $this->tester->getFactory()->createCompressor(),
        );
    }
}
