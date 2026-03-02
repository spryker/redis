<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter\Factory;

use Generated\Shared\Transfer\RedisConfigurationTransfer;
use Spryker\Client\Redis\Adapter\RedisAdapterInterface;

interface RedisAdapterFactoryInterface
{
    public function create(RedisConfigurationTransfer $redisConfigurationTransfer): RedisAdapterInterface;
}
