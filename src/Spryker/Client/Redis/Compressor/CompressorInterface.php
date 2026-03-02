<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Compressor;

interface CompressorInterface
{
    public function canBeCompressed(string $value): bool;

    public function isCompressed(string $value): bool;

    public function compress(string $value): string;

    public function decompress(string $value): string;
}
