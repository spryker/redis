<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\Redis\Adapter;

use Codeception\Test\Unit;
use Spryker\Client\Redis\Adapter\KeyPrefixRedisAdapter;
use Spryker\Client\Redis\Adapter\RedisAdapterInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group Redis
 * @group Adapter
 * @group KeyPrefixRedisAdapterTest
 * Add your own group annotations below this line
 */
class KeyPrefixRedisAdapterTest extends Unit
{
    protected const PREFIX = 'tenant1:';

    /**
     * @var \Spryker\Client\Redis\Adapter\RedisAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected RedisAdapterInterface $innerAdapterMock;

    protected KeyPrefixRedisAdapter $adapter;

    protected function _setUp(): void
    {
        parent::_setUp();

        $this->innerAdapterMock = $this->createMock(RedisAdapterInterface::class);
        $this->adapter = new KeyPrefixRedisAdapter($this->innerAdapterMock, static::PREFIX);
    }

    public function testGetPrefixesKey(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('get')
            ->with(static::PREFIX . 'my:key')
            ->willReturn('value');

        $this->assertSame('value', $this->adapter->get('my:key'));
    }

    public function testSetexPrefixesKey(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('setex')
            ->with(static::PREFIX . 'my:key', 60, 'value')
            ->willReturn(true);

        $this->assertTrue($this->adapter->setex('my:key', 60, 'value'));
    }

    public function testSetPrefixesKey(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('set')
            ->with(static::PREFIX . 'my:key', 'value', 'EX', 60, 'NX')
            ->willReturn(true);

        $this->assertTrue($this->adapter->set('my:key', 'value', 'EX', 60, 'NX'));
    }

    public function testDelPrefixesAllKeys(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('del')
            ->with([static::PREFIX . 'key:1', static::PREFIX . 'key:2'])
            ->willReturn(2);

        $this->assertSame(2, $this->adapter->del(['key:1', 'key:2']));
    }

    public function testEvalPrefixesOnlyKeys(): void
    {
        // First $numKeys=2 elements are KEYS[], remaining are ARGV[]
        $this->innerAdapterMock->expects($this->once())
            ->method('eval')
            ->with(
                'script',
                2,
                [static::PREFIX . 'lock:key', static::PREFIX . 'other:key', 'arg1', 'arg2'],
            )
            ->willReturn(true);

        $this->assertTrue($this->adapter->eval('script', 2, ['lock:key', 'other:key', 'arg1', 'arg2']));
    }

    public function testMgetPrefixesAllKeys(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('mget')
            ->with([static::PREFIX . 'key:1', static::PREFIX . 'key:2'])
            ->willReturn(['val1', 'val2']);

        $this->assertSame(['val1', 'val2'], $this->adapter->mget(['key:1', 'key:2']));
    }

    public function testMsetPrefixesDictionaryKeys(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('mset')
            ->with([static::PREFIX . 'key:1' => 'one', static::PREFIX . 'key:2' => 'two'])
            ->willReturn(true);

        $this->assertTrue($this->adapter->mset(['key:1' => 'one', 'key:2' => 'two']));
    }

    public function testKeysPrefixesPatternAndStripsReturnedKeys(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('keys')
            ->with(static::PREFIX . 'product:*')
            ->willReturn([static::PREFIX . 'product:123', static::PREFIX . 'product:456']);

        $this->assertSame(['product:123', 'product:456'], $this->adapter->keys('product:*'));
    }

    public function testScanPrefixesMatchOptionAndStripsReturnedKeys(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('scan')
            ->with(0, ['MATCH' => static::PREFIX . 'product:*', 'COUNT' => 100])
            ->willReturn(['0', [static::PREFIX . 'product:123', static::PREFIX . 'product:456']]);

        [$cursor, $keys] = $this->adapter->scan(0, ['MATCH' => 'product:*', 'COUNT' => 100]);

        $this->assertSame('0', $cursor);
        $this->assertSame(['product:123', 'product:456'], $keys);
    }

    public function testScanWithLowercaseMatchOption(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('scan')
            ->with(0, ['match' => static::PREFIX . 'session:*'])
            ->willReturn(['0', [static::PREFIX . 'session:abc']]);

        [, $keys] = $this->adapter->scan(0, ['match' => 'session:*']);

        $this->assertSame(['session:abc'], $keys);
    }

    public function testIncrPrefixesKey(): void
    {
        $this->innerAdapterMock->expects($this->once())
            ->method('incr')
            ->with(static::PREFIX . 'counter')
            ->willReturn(5);

        $this->assertSame(5, $this->adapter->incr('counter'));
    }

    public function testPassthroughMethodsDelegateWithoutKeyModification(): void
    {
        $this->innerAdapterMock->expects($this->once())->method('connect');
        $this->innerAdapterMock->expects($this->once())->method('disconnect');
        $this->innerAdapterMock->expects($this->once())->method('isConnected')->willReturn(true);
        $this->innerAdapterMock->expects($this->once())->method('info')->with('server')->willReturn(['redis_version' => '7.0']);
        $this->innerAdapterMock->expects($this->once())->method('dbSize')->willReturn(42);
        $this->innerAdapterMock->expects($this->once())->method('flushDb');

        $this->adapter->connect();
        $this->adapter->disconnect();
        $this->assertTrue($this->adapter->isConnected());
        $this->assertSame(['redis_version' => '7.0'], $this->adapter->info('server'));
        $this->assertSame(42, $this->adapter->dbSize());
        $this->adapter->flushDb();
    }
}
