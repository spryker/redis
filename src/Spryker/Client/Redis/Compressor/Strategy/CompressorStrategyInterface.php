<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Compressor\Strategy;

interface CompressorStrategyInterface
{
    public function isCompressed(mixed $value): bool;

    public function compress(string $value, int $level): ?string;

    public function decompress(string $value): ?string;
}
