<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\Redis;

use Spryker\Glue\Kernel\AbstractFactory;
use Spryker\Glue\Redis\WebProfiler\RedisDataCollector;
use Spryker\Shared\Redis\Dependency\Service\RedisToUtilEncodingServiceInterface;
use Spryker\Shared\Redis\Logger\RedisInMemoryLogger;
use Spryker\Shared\Redis\Logger\RedisLoggerInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class RedisFactory extends AbstractFactory
{
    public function createRedisDataCollector(): DataCollector
    {
        return new RedisDataCollector(
            $this->createRedisLogger(),
        );
    }

    public function createRedisLogger(): RedisLoggerInterface
    {
        return new RedisInMemoryLogger(
            $this->getUtilEncodingService(),
        );
    }

    public function getUtilEncodingService(): RedisToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(RedisDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
