<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Resolver;

use RedisException;
use RedisSentinel;
use Spryker\Client\Redis\Exception\ConnectionConfigurationException;

class SentinelMasterAddressResolver
{
    protected const int DEFAULT_SENTINEL_PORT = 26379;

    /**
     * @param array<string> $sentinelDataSourceNames
     *
     * @throws \Spryker\Client\Redis\Exception\ConnectionConfigurationException
     *
     * @return array{host: string, port: int}
     */
    public function resolveMasterConnectionParameters(array $sentinelDataSourceNames, string $sentinelServiceName): array
    {
        foreach ($sentinelDataSourceNames as $sentinelDataSourceName) {
            $masterAddress = $this->findMasterAddress($sentinelDataSourceName, $sentinelServiceName);

            if ($masterAddress) {
                return ['host' => (string)$masterAddress[0], 'port' => (int)$masterAddress[1]];
            }
        }

        throw new ConnectionConfigurationException(
            sprintf('Unable to resolve Redis Sentinel master address for service "%s".', $sentinelServiceName),
        );
    }

    /**
     * @return array<int, mixed>|false
     */
    protected function findMasterAddress(string $sentinelDataSourceName, string $sentinelServiceName): array|false
    {
        $sentinelHost = parse_url($sentinelDataSourceName, PHP_URL_HOST);

        if (!$sentinelHost) {
            return false;
        }

        $sentinelPort = (int)(parse_url($sentinelDataSourceName, PHP_URL_PORT) ?: static::DEFAULT_SENTINEL_PORT);

        try {
            $masterAddress = $this->createRedisSentinel((string)$sentinelHost, $sentinelPort)->getMasterAddrByName($sentinelServiceName);
        } catch (RedisException) {
            return false;
        }

        return is_array($masterAddress) ? $masterAddress : false;
    }

    public function createRedisSentinel(string $host, int $port): RedisSentinel
    {
        return new RedisSentinel(['host' => $host, 'port' => $port]);
    }
}
