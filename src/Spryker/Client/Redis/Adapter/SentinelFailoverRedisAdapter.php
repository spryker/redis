<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter;

use Closure;
use RedisException;

class SentinelFailoverRedisAdapter implements RedisAdapterInterface
{
    /**
     * @param \Closure(): \Spryker\Client\Redis\Adapter\RedisAdapterInterface $adapterFactory
     */
    public function __construct(
        protected RedisAdapterInterface $adapter,
        protected Closure $adapterFactory,
    ) {
    }

    public function get(string $key): ?string
    {
        return $this->retry(fn (): ?string => $this->adapter->get($key));
    }

    public function setex(string $key, int $seconds, string $value): bool
    {
        return $this->retry(fn (): bool => $this->adapter->setex($key, $seconds, $value));
    }

    public function set(string $key, string $value, ?string $expireResolution = null, ?int $expireTTL = null, ?string $flag = null): bool
    {
        return $this->retry(fn (): bool => $this->adapter->set($key, $value, $expireResolution, $expireTTL, $flag));
    }

    /**
     * @param array<string> $keys
     */
    public function del(array $keys): int
    {
        return $this->retry(fn (): int => $this->adapter->del($keys));
    }

    /**
     * @param array<int, mixed> $keysOrArgs
     */
    public function eval(string $script, int $numKeys, array $keysOrArgs): bool
    {
        return $this->retry(fn (): bool => $this->adapter->eval($script, $numKeys, $keysOrArgs));
    }

    public function connect(): void
    {
        $this->retry(function (): bool {
            $this->adapter->connect();

            return true;
        });
    }

    public function disconnect(): void
    {
        $this->adapter->disconnect();
    }

    public function isConnected(): bool
    {
        return $this->adapter->isConnected();
    }

    /**
     * @param array<string> $keys
     *
     * @return array<string|null>
     */
    public function mget(array $keys): array
    {
        return $this->retry(fn (): array => $this->adapter->mget($keys));
    }

    /**
     * @param array<string, string> $dictionary
     */
    public function mset(array $dictionary): bool
    {
        return $this->retry(fn (): bool => $this->adapter->mset($dictionary));
    }

    /**
     * @return array<string, mixed>
     */
    public function info(?string $section = null): array
    {
        return $this->retry(fn (): array => $this->adapter->info($section));
    }

    /**
     * @return array<string>
     */
    public function keys(string $pattern): array
    {
        return $this->retry(fn (): array => $this->adapter->keys($pattern));
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<int, mixed>
     */
    public function scan(int $cursor, array $options): array
    {
        return $this->retry(fn (): array => $this->adapter->scan($cursor, $options));
    }

    public function dbSize(): int
    {
        return $this->retry(fn (): int => $this->adapter->dbSize());
    }

    public function flushDb(): void
    {
        $this->retry(function (): bool {
            $this->adapter->flushDb();

            return true;
        });
    }

    public function incr(string $key): int
    {
        return $this->retry(fn (): int => $this->adapter->incr($key));
    }

    public function ttl(string $key): int
    {
        return $this->retry(fn (): int => $this->adapter->ttl($key));
    }

    public function expire(string $key, int $seconds): bool
    {
        return $this->retry(fn (): bool => $this->adapter->expire($key, $seconds));
    }

    /**
     * @template TResult
     *
     * @param \Closure(): TResult $operation
     *
     * @return TResult
     */
    protected function retry(Closure $operation): mixed
    {
        try {
            return $operation();
        } catch (RedisException) {
            $this->reconnectToCurrentMaster();

            return $operation();
        }
    }

    protected function reconnectToCurrentMaster(): void
    {
        $this->adapter = ($this->adapterFactory)();
    }
}
