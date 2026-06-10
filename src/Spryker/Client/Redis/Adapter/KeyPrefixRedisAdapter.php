<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter;

class KeyPrefixRedisAdapter implements RedisAdapterInterface
{
    public function __construct(
        protected RedisAdapterInterface $adapter,
        protected string $keyPrefix,
    ) {
    }

    public function get(string $key): ?string
    {
        return $this->adapter->get($this->prefixKey($key));
    }

    public function setex(string $key, int $seconds, string $value): bool
    {
        return $this->adapter->setex($this->prefixKey($key), $seconds, $value);
    }

    public function set(string $key, string $value, ?string $expireResolution = null, ?int $expireTTL = null, ?string $flag = null): bool
    {
        return $this->adapter->set($this->prefixKey($key), $value, $expireResolution, $expireTTL, $flag);
    }

    public function del(array $keys): int
    {
        return $this->adapter->del($this->prefixKeys($keys));
    }

    public function eval(string $script, int $numKeys, array $keysOrArgs): bool
    {
        // Only the first $numKeys elements are Redis keys (KEYS[]), the rest are arguments (ARGV[])
        $prefixedKeys = $this->prefixKeys(array_slice($keysOrArgs, 0, $numKeys));
        $args = array_slice($keysOrArgs, $numKeys);

        return $this->adapter->eval($script, $numKeys, array_merge($prefixedKeys, $args));
    }

    public function connect(): void
    {
        $this->adapter->connect();
    }

    public function disconnect(): void
    {
        $this->adapter->disconnect();
    }

    public function isConnected(): bool
    {
        return $this->adapter->isConnected();
    }

    public function mget(array $keys): array
    {
        return $this->adapter->mget($this->prefixKeys($keys));
    }

    public function mset(array $dictionary): bool
    {
        return $this->adapter->mset(
            array_combine($this->prefixKeys(array_keys($dictionary)), array_values($dictionary)),
        );
    }

    public function info(?string $section = null): array
    {
        return $this->adapter->info($section);
    }

    /**
     * @return array<string>
     */
    public function keys(string $pattern): array
    {
        return $this->stripPrefixFromKeys($this->adapter->keys($this->prefixKey($pattern)));
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array [string, string[]]
     */
    public function scan(int $cursor, array $options): array
    {
        [$cursor, $keys] = $this->adapter->scan($cursor, $this->prefixMatchOption($options));

        return [$cursor, $this->stripPrefixFromKeys($keys)];
    }

    public function dbSize(): int
    {
        return $this->adapter->dbSize();
    }

    public function flushDb(): void
    {
        $this->adapter->flushDb();
    }

    public function incr(string $key): int
    {
        return $this->adapter->incr($this->prefixKey($key));
    }

    protected function prefixKey(string $key): string
    {
        return $this->keyPrefix . $key;
    }

    /**
     * @param array<string> $keys
     *
     * @return array<string>
     */
    protected function prefixKeys(array $keys): array
    {
        return array_map(fn (string $key): string => $this->prefixKey($key), $keys);
    }

    /**
     * @param array<string> $keys
     *
     * @return array<string>
     */
    protected function stripPrefixFromKeys(array $keys): array
    {
        $prefixLength = strlen($this->keyPrefix);

        return array_map(fn (string $key): string => substr($key, $prefixLength), $keys);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function prefixMatchOption(array $options): array
    {
        foreach (['MATCH', 'match'] as $matchKey) {
            if (!isset($options[$matchKey])) {
                continue;
            }

            $options[$matchKey] = $this->prefixKey($options[$matchKey]);

            break;
        }

        return $options;
    }
}
