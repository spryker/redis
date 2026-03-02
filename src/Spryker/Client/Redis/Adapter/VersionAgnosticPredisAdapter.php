<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter;

use Predis\Client;
use Predis\Response\Status;

class VersionAgnosticPredisAdapter implements RedisAdapterInterface
{
    /**
     * @var string
     */
    protected const OK_WRITE_STATUS = 'OK';

    /**
     * @var \Predis\Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function get(string $key): ?string
    {
        return $this->client->get($key);
    }

    public function setex(string $key, int $seconds, string $value): bool
    {
        $result = $this->client->setex($key, $seconds, $value);

        // @phpstan-ignore instanceof.alwaysTrue (defensive programming for type safety)
        if ($result instanceof Status) {
            return $result->getPayload() === static::OK_WRITE_STATUS;
        }

        return (bool)$result;
    }

    public function set(string $key, string $value, ?string $expireResolution = null, ?int $expireTTL = null, ?string $flag = null): bool
    {
        return $expireTTL !== null || $flag !== null
            ? (bool)$this->client->set($key, $value, $expireResolution, $expireTTL, $flag)
            : (bool)$this->client->set($key, $value);
    }

    public function del(array $keys): int
    {
        return $this->client->del($keys);
    }

    public function eval(string $script, int $numKeys, array $keysOrArgs): bool
    {
        // @phpstan-ignore argument.named (array keys are numeric indices, not named arguments)
        return (bool)$this->client->eval($script, $numKeys, ...$keysOrArgs);
    }

    public function connect(): void
    {
        $this->client->connect();
    }

    public function disconnect(): void
    {
        $this->client->disconnect();
    }

    public function isConnected(): bool
    {
        return $this->client->isConnected();
    }

    public function mget(array $keys): array
    {
        return $this->client->mget($keys);
    }

    public function mset(array $dictionary): bool
    {
        return (bool)$this->client->mset($dictionary);
    }

    public function info(?string $section = null): array
    {
        return $this->client->info($section);
    }

    /**
     * @param string $pattern
     *
     * @return array<string>
     */
    public function keys(string $pattern): array
    {
        return $this->client->keys($pattern);
    }

    /**
     * @param int $cursor
     * @param array<string, mixed> $options
     *
     * @return array [string, string[]]
     */
    public function scan(int $cursor, array $options): array
    {
        return $this->client->scan($cursor, $options);
    }

    public function dbSize(): int
    {
        return $this->client->dbsize();
    }

    public function flushDb(): void
    {
        $this->client->flushdb();
    }

    public function incr(string $key): int
    {
        return $this->client->incr($key);
    }
}
