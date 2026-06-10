<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter\Factory;

use Generated\Shared\Transfer\RedisConfigurationTransfer;
use Generated\Shared\Transfer\RedisCredentialsTransfer;
use Redis;
use Spryker\Client\Redis\Adapter\RedisAdapterInterface;
use Spryker\Client\Redis\Adapter\VersionAgnosticPhpredisAdapter;
use Spryker\Client\Redis\Exception\ConnectionConfigurationException;

class PhpredisAdapterFactory extends AbstractRedisAdapterFactory
{
    protected function createVersionAgnosticAdapter(RedisConfigurationTransfer $redisConfigurationTransfer): RedisAdapterInterface
    {
        return new VersionAgnosticPhpredisAdapter(
            $this->createPhpredisClient($redisConfigurationTransfer),
            $redisConfigurationTransfer->getConnectionCredentials(),
        );
    }

    public function createPhpredisClient(RedisConfigurationTransfer $redisConfigurationTransfer): Redis
    {
        $connectionParameters = $this->getConnectionParameters($redisConfigurationTransfer);
        $options = $this->buildPhpredisOptions($redisConfigurationTransfer, $connectionParameters);

        $redis = new Redis($options); //@phpstan-ignore-line

        if (isset($connectionParameters['database'])) {
            $redis->select($connectionParameters['database']);
        }

        return $redis;
    }

    /**
     * @param \Generated\Shared\Transfer\RedisConfigurationTransfer $redisConfigurationTransfer
     * @param array<string, mixed> $connectionParameters
     *
     * @return array<string, mixed>
     */
    public function buildPhpredisOptions(RedisConfigurationTransfer $redisConfigurationTransfer, array $connectionParameters): array
    {
        $connectionCredentials = $redisConfigurationTransfer->getConnectionCredentials();

        $options = [
            'host' => $this->buildHost($connectionParameters, $connectionCredentials),
            'port' => (int)$connectionParameters['port'],
        ];

        $options = $this->applyTlsOptions($options, $connectionCredentials);
        $options = $this->applyPersistentOption($options, $connectionCredentials);
        $options = $this->applyAuthOption($options, $connectionCredentials);

        return $options;
    }

    public function buildHost(array $connectionParameters, ?RedisCredentialsTransfer $credentialsTransfer): string
    {
        $host = $connectionParameters['host'];

        if ($this->isTlsEnabled($credentialsTransfer)) {
            return sprintf('tls://%s', $host);
        }

        return $host;
    }

    public function applyTlsOptions(array $options, ?RedisCredentialsTransfer $credentialsTransfer): array
    {
        if (!$this->isTlsEnabled($credentialsTransfer)) {
            return $options;
        }

        $options['ssl'] = $this->buildSslOptions($credentialsTransfer);

        return $options;
    }

    public function applyPersistentOption(array $options, ?RedisCredentialsTransfer $credentialsTransfer): array
    {
        if (!$credentialsTransfer || !$credentialsTransfer->getIsPersistent()) {
            return $options;
        }

        $options['persistent'] = true;

        return $options;
    }

    public function applyAuthOption(array $options, ?RedisCredentialsTransfer $credentialsTransfer): array
    {
        if (!$credentialsTransfer || !$credentialsTransfer->getPassword()) {
            return $options;
        }

        if ($credentialsTransfer->getUsername()) {
            $options['auth'] = [$credentialsTransfer->getUsername(), $credentialsTransfer->getPassword()];

            return $options;
        }

        $options['auth'] = $credentialsTransfer->getPassword();

        return $options;
    }

    /**
     * @param \Generated\Shared\Transfer\RedisConfigurationTransfer $redisConfigurationTransfer
     *
     * @throws \Spryker\Client\Redis\Exception\ConnectionConfigurationException
     *
     * @return array<string, mixed>
     */
    public function getConnectionParameters(RedisConfigurationTransfer $redisConfigurationTransfer): array
    {
        $connectionParameters = parent::getConnectionParameters($redisConfigurationTransfer);

        if (!isset($connectionParameters['host'])) {
            throw new ConnectionConfigurationException('Redis host is not set.');
        }

        if (!isset($connectionParameters['port'])) {
            throw new ConnectionConfigurationException('Redis port is not set.');
        }

        return $connectionParameters;
    }
}
