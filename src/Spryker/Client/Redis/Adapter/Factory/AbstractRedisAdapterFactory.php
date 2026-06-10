<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter\Factory;

use Generated\Shared\Transfer\RedisConfigurationTransfer;
use Generated\Shared\Transfer\RedisCredentialsTransfer;
use Spryker\Client\Redis\Adapter\KeyPrefixRedisAdapter;
use Spryker\Client\Redis\Adapter\LoggableRedisAdapter;
use Spryker\Client\Redis\Adapter\RedisAdapterInterface;
use Spryker\Client\Redis\Adapter\RedisCompressionAdapter;
use Spryker\Client\Redis\Compressor\CompressorInterface;
use Spryker\Client\Redis\Exception\ConnectionConfigurationException;
use Spryker\Client\Redis\RedisConfig;
use Spryker\Shared\Redis\Dependency\Service\RedisToUtilEncodingServiceInterface;
use Spryker\Shared\Redis\Logger\RedisInMemoryLogger;
use Spryker\Shared\Redis\Logger\RedisLoggerInterface;

abstract class AbstractRedisAdapterFactory implements RedisAdapterFactoryInterface
{
    protected const string SCHEME_TLS = 'tls';

    protected const string SSL_OPTION_CA_FILE = 'cafile';

    protected const string SSL_OPTION_VERIFY_PEER = 'verify_peer';

    protected const string SSL_OPTION_VERIFY_PEER_NAME = 'verify_peer_name';

    /**
     * @var string
     */
    protected const CONNECTION_PARAMETERS = 'CONNECTION_PARAMETERS';

    /**
     * @var string
     */
    protected const CONNECTION_OPTIONS = 'CONNECTION_OPTIONS';

    // RedisCredentialsTransfer::IS_PERSISTENT (isPersistent)
    protected const string CREDENTIALS_KEY_IS_PERSISTENT = 'is_persistent';

    // RedisCredentialsTransfer::SSL_CA_FILE_PATH (sslCaFilePath)
    protected const string CREDENTIALS_KEY_SSL_CA_FILE_PATH = 'ssl_ca_file_path';

    // No direct transfer property — retained for safety in case downstream code injects this key.
    protected const string CREDENTIALS_KEY_IS_TLS = 'is_tls';

    public function __construct(
        protected RedisConfig $redisConfig,
        protected RedisToUtilEncodingServiceInterface $utilEncodingService,
        protected CompressorInterface $compressor
    ) {
    }

    public function create(RedisConfigurationTransfer $redisConfigurationTransfer): RedisAdapterInterface
    {
        $this->normalizeCredentialsScheme($redisConfigurationTransfer->getConnectionCredentials());

        $adapter = $this->redisConfig->isDevelopmentMode()
            ? $this->createLoggableRedisAdapter($redisConfigurationTransfer)
            : $this->createRedisCompressionAdapter($redisConfigurationTransfer);

        return $this->createKeyPrefixAdapter($adapter);
    }

    public function createKeyPrefixAdapter(RedisAdapterInterface $adapter): RedisAdapterInterface
    {
        $keyPrefix = $this->redisConfig->getKeyPrefix();
        if (!$keyPrefix) {
            return $adapter;
        }

        return new KeyPrefixRedisAdapter($adapter, $keyPrefix);
    }

    abstract protected function createVersionAgnosticAdapter(RedisConfigurationTransfer $redisConfigurationTransfer): RedisAdapterInterface;

    protected function createRedisCompressionAdapter(RedisConfigurationTransfer $redisConfigurationTransfer): RedisAdapterInterface
    {
        return new RedisCompressionAdapter(
            $this->createVersionAgnosticAdapter($redisConfigurationTransfer),
            $this->compressor,
        );
    }

    protected function createLoggableRedisAdapter(RedisConfigurationTransfer $redisConfigurationTransfer): RedisAdapterInterface
    {
        return new LoggableRedisAdapter(
            $this->createRedisCompressionAdapter($redisConfigurationTransfer),
            $this->createRedisLogger($redisConfigurationTransfer),
        );
    }

    protected function createRedisLogger(RedisConfigurationTransfer $redisConfigurationTransfer): RedisLoggerInterface
    {
        return new RedisInMemoryLogger($this->utilEncodingService, $redisConfigurationTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\RedisConfigurationTransfer $redisConfigurationTransfer
     *
     * @throws \Spryker\Client\Redis\Exception\ConnectionConfigurationException
     *
     * @return array|string
     */
    public function getConnectionParameters(RedisConfigurationTransfer $redisConfigurationTransfer)
    {
        $configurationParameters = $redisConfigurationTransfer->getDataSourceNames();

        if (!$configurationParameters) {
            $configurationParameters = $this->getFilteredConnectionCredentials($redisConfigurationTransfer);
        }

        if ($configurationParameters) {
            return $configurationParameters;
        }

        throw new ConnectionConfigurationException('Redis connection parameters are corrupt. Either DSN string or an array of configuration values should be provided.');
    }

    public function getFilteredConnectionCredentials(RedisConfigurationTransfer $redisConfigurationTransfer): array
    {
        $connectionCredentialsTransfer = $redisConfigurationTransfer->getConnectionCredentials();

        if (!$connectionCredentialsTransfer) {
            return [];
        }

        $connectionCredentials = $connectionCredentialsTransfer->toArray();
        $connectionCredentials = $this->clearEmptyPassword($connectionCredentials);
        $connectionCredentials = $this->clearEmptyUsername($connectionCredentials);
        $connectionCredentials = $this->clearEmptySchema($connectionCredentials);
        $connectionCredentials = $this->clearNonConnectionFields($connectionCredentials);

        return $connectionCredentials;
    }

    public function clearNonConnectionFields(array $connectionCredentials): array
    {
        unset(
            $connectionCredentials[static::CREDENTIALS_KEY_IS_TLS],
            $connectionCredentials[static::CREDENTIALS_KEY_SSL_CA_FILE_PATH],
            $connectionCredentials[static::CREDENTIALS_KEY_IS_PERSISTENT],
        );

        return $connectionCredentials;
    }

    public function normalizeCredentialsScheme(?RedisCredentialsTransfer $credentialsTransfer): ?RedisCredentialsTransfer
    {
        if ($credentialsTransfer === null || $credentialsTransfer->getScheme()) {
            return $credentialsTransfer;
        }

        return $credentialsTransfer->setScheme($this->redisConfig->getScheme());
    }

    public function isTlsEnabled(?RedisCredentialsTransfer $credentialsTransfer): bool
    {
        $scheme = $credentialsTransfer?->getScheme() ?? $this->redisConfig->getScheme();

        return $scheme === static::SCHEME_TLS;
    }

    /**
     * @return array<string, mixed>
     */

    /**
     * @return array<string, mixed>
     */
    public function buildSslOptions(?RedisCredentialsTransfer $credentialsTransfer): array
    {
        $caFile = $credentialsTransfer?->getSslCaFilePath() ?? $this->redisConfig->getSslCaFilePath();

        if (!$caFile) {
            return [
                static::SSL_OPTION_VERIFY_PEER => false,
                static::SSL_OPTION_VERIFY_PEER_NAME => false,
            ];
        }

        return [
            static::SSL_OPTION_CA_FILE => $caFile,
            static::SSL_OPTION_VERIFY_PEER => true,
            static::SSL_OPTION_VERIFY_PEER_NAME => true,
        ];
    }

    public function clearEmptyPassword(array $connectionCredentials): array
    {
        if (isset($connectionCredentials[RedisCredentialsTransfer::PASSWORD]) && !$connectionCredentials[RedisCredentialsTransfer::PASSWORD]) {
            unset($connectionCredentials[RedisCredentialsTransfer::PASSWORD]);
        }

        return $connectionCredentials;
    }

    public function clearEmptyUsername(array $connectionCredentials): array
    {
        if (!empty($connectionCredentials[RedisCredentialsTransfer::USERNAME])) {
            return $connectionCredentials;
        }

        unset($connectionCredentials[RedisCredentialsTransfer::USERNAME]);

        return $connectionCredentials;
    }

    public function clearEmptySchema(array $connectionCredentials): array
    {
        if (array_key_exists(RedisCredentialsTransfer::SCHEME, $connectionCredentials) && !$connectionCredentials[RedisCredentialsTransfer::SCHEME]) {
            unset($connectionCredentials[RedisCredentialsTransfer::SCHEME]);
        }

        return $connectionCredentials;
    }
}
