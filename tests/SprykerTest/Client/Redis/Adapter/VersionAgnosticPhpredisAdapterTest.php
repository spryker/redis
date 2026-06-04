<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\Redis\Adapter;

use Codeception\Test\Unit;
use Redis;
use Spryker\Client\Redis\Adapter\VersionAgnosticPhpredisAdapter;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group Redis
 * @group Adapter
 * @group VersionAgnosticPhpredisAdapterTest
 * Add your own group annotations below this line
 */
class VersionAgnosticPhpredisAdapterTest extends Unit
{
    public function testConnectRestoresNonZeroDatabaseAfterReconnect(): void
    {
        // Arrange
        $database = 3;
        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('getDBNum')->willReturn($database);
        $redisMock->expects($this->once())->method('select')->with($database);

        $adapter = new VersionAgnosticPhpredisAdapter($redisMock);

        // Act
        $adapter->connect();
    }

    public function testConnectRestoresZeroDatabaseAfterReconnect(): void
    {
        // Arrange
        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('getDBNum')->willReturn(0);
        $redisMock->expects($this->once())->method('select')->with(0);

        $adapter = new VersionAgnosticPhpredisAdapter($redisMock);

        // Act
        $adapter->connect();
    }
}
