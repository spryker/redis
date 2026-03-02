<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\Redis\Adapter\Factory\PhpredisAdapterFactory;
use Spryker\Client\Redis\Adapter\Factory\PredisAdapterFactory;
use Spryker\Client\Redis\Adapter\Factory\RedisAdapterFactoryInterface;
use Spryker\Client\Redis\Adapter\RedisAdapterProvider;
use Spryker\Client\Redis\Adapter\RedisAdapterProviderInterface;
use Spryker\Client\Redis\Compressor\Compressor;
use Spryker\Client\Redis\Compressor\CompressorInterface;
use Spryker\Client\Redis\Compressor\Strategy\ZlibCompressorStrategy;
use Spryker\Shared\Redis\Dependency\Service\RedisToUtilEncodingServiceInterface;

/**
 * @method \Spryker\Client\Redis\RedisConfig getConfig()
 */
class RedisFactory extends AbstractFactory
{
    public function createRedisAdapterProvider(): RedisAdapterProviderInterface
    {
        return new RedisAdapterProvider(
            $this->createRedisAdapterFactory(),
        );
    }

    public function createRedisAdapterFactory(): RedisAdapterFactoryInterface
    {
        if ($this->getConfig()->usePhpredis()) {
            return $this->createPhpredisAdapterFactory();
        }

        return $this->createPredisAdapterFactory();
    }

    public function createPredisAdapterFactory(): RedisAdapterFactoryInterface
    {
        return new PredisAdapterFactory(
            $this->getConfig(),
            $this->getUtilEncodingService(),
            $this->createCompressor(),
        );
    }

    public function createPhpredisAdapterFactory(): RedisAdapterFactoryInterface
    {
        return new PhpredisAdapterFactory(
            $this->getConfig(),
            $this->getUtilEncodingService(),
            $this->createCompressor(),
        );
    }

    public function createCompressor(): CompressorInterface
    {
        return new Compressor($this->getConfig(), $this->getKeyValueCompressorStrategies());
    }

    public function getUtilEncodingService(): RedisToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(RedisDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return array<\Spryker\Client\Redis\Compressor\Strategy\ZlibCompressorStrategy>
     */
    public function getKeyValueCompressorStrategies(): array
    {
        return [
            new ZlibCompressorStrategy(),
        ];
    }
}
