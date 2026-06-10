<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Redis;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface RedisConstants
{
    /**
     * Specification:
     * - Enables/disables compressing for Redis data.
     *
     * @api
     *
     * @var string
     */
    public const REDIS_COMPRESSION_ENABLED = 'REDIS:REDIS_COMPRESSION_ENABLED';

    /**
     * Specification:
     * - Enables/disables Redis logs.
     *
     * @api
     *
     * @var string
     */
    public const REDIS_IS_DEV_MODE = 'REDIS:REDIS_IS_DEV_MODE';

    /**
     * Specification:
     * - Connection scheme for all Redis connections (tcp or tls).
     * - When set to "tls", TLS encryption is enabled for all connections.
     * - Defaults to "tcp" when not configured.
     *
     * @api
     */
    public const string REDIS_SCHEME = 'REDIS:REDIS_SCHEME';

    /**
     * Specification:
     * - Path to the CA certificate file used to verify the Redis server TLS certificate.
     * - When empty, TLS peer verification is disabled (suitable for self-signed certificates).
     *
     * @api
     */
    public const string REDIS_SSL_CA_FILE_PATH = 'REDIS:REDIS_SSL_CA_FILE_PATH';

    /**
     * Specification:
     * - Defines the prefix that is prepended to every Redis key produced by the Redis client.
     * - Required for environments where Redis ACL restricts key access by prefix.
     * - When empty, keys are passed through unchanged.
     *
     * @api
     */
    public const string REDIS_KEY_PREFIX = 'REDIS:REDIS_KEY_PREFIX';
}
