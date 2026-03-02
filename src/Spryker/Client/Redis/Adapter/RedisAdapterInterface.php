<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter;

interface RedisAdapterInterface
{
    public function get(string $key): ?string;

    public function setex(string $key, int $seconds, string $value): bool;

    public function set(string $key, string $value, ?string $expireResolution = null, ?int $expireTTL = null, ?string $flag = null): bool;

    public function del(array $keys): int;

    public function eval(string $script, int $numKeys, array $keysOrArgs): bool;

    public function connect(): void;

    public function disconnect(): void;

    public function isConnected(): bool;

    public function mget(array $keys): array;

    public function mset(array $dictionary): bool;

    public function info(?string $section = null): array;

    /**
     * @param string $pattern
     *
     * @return array<string>
     */
    public function keys(string $pattern): array;

    /**
     * @param int $cursor
     * @param array<string, mixed> $options
     *
     * @return array [string, string[]]
     */
    public function scan(int $cursor, array $options): array;

    public function dbSize(): int;

    public function flushDb(): void;

    public function incr(string $key): int;
}
