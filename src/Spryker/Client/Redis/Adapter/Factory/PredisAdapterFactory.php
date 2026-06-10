<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter\Factory;

use Generated\Shared\Transfer\RedisConfigurationTransfer;
use Generated\Shared\Transfer\RedisCredentialsTransfer;
use Predis\Client;
use Spryker\Client\Redis\Adapter\RedisAdapterInterface;
use Spryker\Client\Redis\Adapter\VersionAgnosticPredisAdapter;

class PredisAdapterFactory extends AbstractRedisAdapterFactory
{
    protected function createVersionAgnosticAdapter(RedisConfigurationTransfer $redisConfigurationTransfer): RedisAdapterInterface
    {
        return new VersionAgnosticPredisAdapter(
            $this->createPredisClient($redisConfigurationTransfer),
        );
    }

    public function createPredisClient(RedisConfigurationTransfer $redisConfigurationTransfer): Client
    {
        $connectionParameters = $this->getConnectionParameters($redisConfigurationTransfer);
        $connectionCredentials = $redisConfigurationTransfer->getConnectionCredentials();

        if (is_array($connectionParameters)) {
            $connectionParameters = $this->applyTlsConnectionParameters($connectionParameters, $connectionCredentials);
        }

        return new Client(
            $connectionParameters,
            $redisConfigurationTransfer->getClientOptions(),
        );
    }

    public function applyTlsConnectionParameters(array $connectionParameters, ?RedisCredentialsTransfer $credentialsTransfer): array
    {
        if (!$this->isTlsEnabled($credentialsTransfer)) {
            return $connectionParameters;
        }

        $connectionParameters['scheme'] = 'tls';
        $connectionParameters['ssl'] = $this->buildSslOptions($credentialsTransfer);

        if ($credentialsTransfer?->getIsPersistent()) {
            $connectionParameters['persistent'] = true;
        }

        return $connectionParameters;
    }
}
