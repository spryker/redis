<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Redis\Adapter;

use Generated\Shared\Transfer\RedisCredentialsTransfer;
use Redis;

class VersionAgnosticPhpredisAdapter implements RedisAdapterInterface
{
    protected const string SCHEME_TLS = 'tls';

    protected const string SSL_OPTION_CA_FILE = 'cafile';

    protected const string SSL_OPTION_VERIFY_PEER = 'verify_peer';

    protected const string SSL_OPTION_VERIFY_PEER_NAME = 'verify_peer_name';

    public function __construct(
        protected Redis $client,
        protected ?RedisCredentialsTransfer $connectionCredentials = null,
    ) {
    }

    public function get(string $key): ?string
    {
        $value = $this->client->get($key);

        return $value === false ? null : $value;
    }

    public function setex(string $key, int $seconds, string $value): bool
    {
        return (bool)$this->client->setex($key, $seconds, $value);
    }

    public function set(string $key, string $value, ?string $expireResolution = null, ?int $expireTTL = null, ?string $flag = null): bool
    {
        $options = [];

        if ($expireResolution) {
            $options[$expireResolution] = $expireTTL;
        }

        if ($flag) {
            $options[] = $flag;
        }

        return (bool)$this->client->set($key, $value, $options);
    }

    public function del(array $keys): int
    {
        return $this->client->del($keys);
    }

    public function eval(string $script, int $numKeys, array $keysOrArgs): bool
    {
        $result = $this->client->eval($script, $keysOrArgs, $numKeys);

        return $result !== false;
    }

    public function connect(): void
    {
        if ($this->client->isConnected()) {
            return;
        }

        $database = $this->client->getDBNum();
        $auth = $this->client->getAuth();
        $context = $this->isTlsEnabled() ? ['ssl' => $this->buildSslContext()] : [];
        $host = $this->resolveHost();

        if ($this->connectionCredentials?->getIsPersistent() === true) {
            $this->client->pconnect(
                $host,
                $this->client->getPort(),
                (float)$this->client->getTimeout(),
                null,
                0,
                $this->client->getReadTimeout(),
                $context,
            );
        } else {
            $this->client->connect(
                $host,
                $this->client->getPort(),
                (float)$this->client->getTimeout(),
                null,
                0,
                $this->client->getReadTimeout(),
                $context,
            );
        }

        if ($auth) {
            $this->client->auth($auth);
        }

        $this->client->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
        $this->client->select($database);
    }

    protected function isTlsEnabled(): bool
    {
        return $this->connectionCredentials?->getScheme() === static::SCHEME_TLS;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSslContext(): array
    {
        $caFile = $this->connectionCredentials?->getSslCaFilePath();

        if (!$caFile) {
            return [
                static::SSL_OPTION_VERIFY_PEER => false,
                static::SSL_OPTION_VERIFY_PEER_NAME => false,
            ];
        }

        return [
            static::SSL_OPTION_CA_FILE => $caFile,
            static::SSL_OPTION_VERIFY_PEER => true,
            static::SSL_OPTION_VERIFY_PEER_NAME => true,
        ];
    }

    protected function resolveHost(): string
    {
        return $this->client->getHost();
    }

    public function disconnect(): void
    {
        if ($this->client->isConnected()) {
            $this->client->close();
        }
    }

    public function isConnected(): bool
    {
        return $this->client->isConnected();
    }

    public function mget(array $keys): array
    {
        $values = $this->client->mget($keys);

        $values = array_map(function ($v) {
            return ($v !== false) ? $v : null;
        }, $values);

        return $values;
    }

    public function info(?string $section = null): array
    {
        $info = $this->client->info($section); //@phpstan-ignore-line

        return $info !== false ? $info : [];
    }

    public function mset(array $dictionary): bool
    {
        return (bool)$this->client->mset($dictionary);
    }

    /**
     * @param string $pattern
     *
     * @return array<string>
     */
    public function keys(string $pattern): array
    {
        $keys = $this->client->keys($pattern);

        return $keys !== false ? $keys : [];
    }

    /**
     * @param int $cursor
     * @param array<string, mixed> $options
     *
     * @return array [string, string[]]
     */
    public function scan(int $cursor, array $options): array
    {
        $pattern = $options['MATCH'] ?? $options['match'] ?? null;
        $count = $options['COUNT'] ?? $options['count'] ?? null;
        if ($cursor === 0) {
            $cursor = null;
        }

        $result = $this->client->scan($cursor, $pattern, $count);

        if ($result === false) {
            return [(string)$cursor, []];
        }

        return [(string)$cursor, $result];
    }

    public function dbSize(): int
    {
        $size = $this->client->dbSize();

        return $size !== false ? $size : 0;
    }

    public function flushDb(): void
    {
        $this->client->flushDB();
    }

    public function incr(string $key): int
    {
        $result = $this->client->incr($key);

        return $result !== false ? $result : 0;
    }
}
