<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter;

use Spryker\Client\Redis\Compressor\CompressorInterface;

class RedisCompressionAdapter implements RedisAdapterInterface
{
    public function __construct(protected RedisAdapterInterface $redisAdapter, protected CompressorInterface $compressor)
    {
    }

    public function get(string $key): ?string
    {
        return $this->prepareSingleValueForGet($this->redisAdapter->get($key));
    }

    public function setex(string $key, int $seconds, string $value): bool
    {
        return $this->redisAdapter->setex($key, $seconds, $this->prepareSingleValueForSet($value));
    }

    public function set(string $key, string $value, ?string $expireResolution = null, ?int $expireTTL = null, ?string $flag = null): bool
    {
        return $this->redisAdapter->set($key, $this->prepareSingleValueForSet($value), $expireResolution, $expireTTL, $flag);
    }

    public function del(array $keys): int
    {
        return $this->redisAdapter->del($keys);
    }

    public function eval(string $script, int $numKeys, array $keysOrArgs): bool
    {
        return $this->redisAdapter->eval($script, $numKeys, $keysOrArgs);
    }

    public function connect(): void
    {
        $this->redisAdapter->connect();
    }

    public function disconnect(): void
    {
        $this->redisAdapter->disconnect();
    }

    public function isConnected(): bool
    {
        return $this->redisAdapter->isConnected();
    }

    public function mget(array $keys): array
    {
        return $this->prepereMultiValueForGet($this->redisAdapter->mget($keys));
    }

    public function mset(array $dictionary): bool
    {
        return $this->redisAdapter->mset($this->prepereMultiValueForSet($dictionary));
    }

    public function info(?string $section = null): array
    {
        return $this->redisAdapter->info($section);
    }

    /**
     * @param string $pattern
     *
     * @return array<string>
     */
    public function keys(string $pattern): array
    {
        return $this->redisAdapter->keys($pattern);
    }

    /**
     * @param int $cursor
     * @param array<string, mixed> $options
     *
     * @return array [string, string[]]
     */
    public function scan(int $cursor, array $options): array
    {
        return $this->redisAdapter->scan($cursor, $options);
    }

    public function dbSize(): int
    {
        return $this->redisAdapter->dbSize();
    }

    public function flushDb(): void
    {
        $this->redisAdapter->flushDb();
    }

    public function incr(string $key): int
    {
        return $this->redisAdapter->incr($key);
    }

    protected function prepareSingleValueForSet(string $data): string
    {
        if (!$this->compressor->canBeCompressed($data)) {
            return $data;
        }

        return $this->compressor->compress($data);
    }

    protected function prepereMultiValueForSet(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($this->compressor->canBeCompressed($value)) {
                $data[$key] = $this->compressor->compress($value);
            }
        }

        return $data;
    }

    protected function prepareSingleValueForGet(?string $data): ?string
    {
        if ($data === null || !$this->compressor->isCompressed($data)) {
            return $data;
        }

        return $this->compressor->decompress($data);
    }

    protected function prepereMultiValueForGet(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value !== null && $this->compressor->isCompressed($value)) {
                $data[$key] = $this->compressor->decompress($value);
            }
        }

        return $data;
    }
}
