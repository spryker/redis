<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter;

use Spryker\Shared\Redis\Logger\RedisLoggerInterface;

class LoggableRedisAdapter implements RedisAdapterInterface
{
    /**
     * @var \Spryker\Client\Redis\Adapter\RedisAdapterInterface
     */
    protected $redisAdapter;

    /**
     * @var \Spryker\Shared\Redis\Logger\RedisLoggerInterface
     */
    protected $redisLogger;

    public function __construct(RedisAdapterInterface $redisAdapter, RedisLoggerInterface $redisLogger)
    {
        $this->redisAdapter = $redisAdapter;
        $this->redisLogger = $redisLogger;
    }

    public function get(string $key): ?string
    {
        $result = $this->redisAdapter->get($key);
        $this->redisLogger->log('GET', ['key' => $key], $result);

        return $result;
    }

    public function setex(string $key, int $seconds, string $value): bool
    {
        $result = $this->redisAdapter->setex($key, $seconds, $value);
        $this->redisLogger->log('SETEX', ['key' => $key, 'seconds' => $seconds, 'value' => $value], $result);

        return $result;
    }

    public function set(string $key, string $value, ?string $expireResolution = null, ?int $expireTTL = null, ?string $flag = null): bool
    {
        $result = $this->redisAdapter->set($key, $value, $expireResolution, $expireTTL, $flag);
        $this->redisLogger->log(
            'SET',
            [
                'key' => $key,
                'value' => $value,
                'expireResolution' => $expireResolution,
                'expireTTL' => $expireTTL,
                'flag' => $flag,
            ],
            $result,
        );

        return $result;
    }

    public function del(array $keys): int
    {
        $result = $this->redisAdapter->del($keys);
        $this->redisLogger->log('DEL', ['keys' => $keys], $result);

        return $result;
    }

    public function eval(string $script, int $numKeys, array $keysOrArgs): bool
    {
        $result = $this->redisAdapter->eval($script, $numKeys, $keysOrArgs);
        $this->redisLogger->log(
            'EVAL',
            ['script' => $script, 'numKeys' => $numKeys, 'keysOrArgs' => $keysOrArgs],
            $result,
        );

        return $result;
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
        $result = $this->redisAdapter->mget($keys);
        $this->redisLogger->log('MGET', ['keys' => $keys], $result);

        return $result;
    }

    public function mset(array $dictionary): bool
    {
        $result = $this->redisAdapter->mset($dictionary);
        $this->redisLogger->log('MSET', ['dictionary' => $dictionary], $result);

        return $result;
    }

    public function info(?string $section = null): array
    {
        $result = $this->redisAdapter->info($section);
        $this->redisLogger->log('INFO', ['section' => $section], $result);

        return $result;
    }

    /**
     * @param string $pattern
     *
     * @return array<string>
     */
    public function keys(string $pattern): array
    {
        $result = $this->redisAdapter->keys($pattern);
        $this->redisLogger->log('KEYS', ['pattern' => $pattern], $result);

        return $result;
    }

    /**
     * @param int $cursor
     * @param array<string, mixed> $options
     *
     * @return array [string, string[]]
     */
    public function scan(int $cursor, array $options): array
    {
        $result = $this->redisAdapter->scan($cursor, $options);
        $this->redisLogger->log('SCAN', ['cursor' => $cursor, 'options' => $options], $result);

        return $result;
    }

    public function dbSize(): int
    {
        $result = $this->redisAdapter->dbSize();
        $this->redisLogger->log('DBSIZE', [], $result);

        return $result;
    }

    public function flushDb(): void
    {
        $this->redisLogger->log('FLUSHDB', []);

        $this->redisAdapter->flushDb();
    }

    public function incr(string $key): int
    {
        $this->redisLogger->log('INCR', ['key' => $key]);

        return $this->redisAdapter->incr($key);
    }
}
