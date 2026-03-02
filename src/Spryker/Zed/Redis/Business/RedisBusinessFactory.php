<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Redis\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\Redis\Business\Export\RedisExporter;
use Spryker\Zed\Redis\Business\Export\RedisExporterInterface;
use Spryker\Zed\Redis\Business\Import\RedisImporter;
use Spryker\Zed\Redis\Business\Import\RedisImporterInterface;

/**
 * @method \Spryker\Zed\Redis\RedisConfig getConfig()
 */
class RedisBusinessFactory extends AbstractBusinessFactory
{
    public function createRedisImporter(): RedisImporterInterface
    {
        return new RedisImporter($this->getConfig());
    }

    public function createRedisExporter(): RedisExporterInterface
    {
        return new RedisExporter($this->getConfig());
    }
}
