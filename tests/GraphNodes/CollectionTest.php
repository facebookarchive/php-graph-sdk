<?php

declare(strict_types=1);
/**
 * Copyright 2017 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */
namespace Facebook\Tests\GraphNodes;

use Facebook\GraphNodes\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionTest
 */
class CollectionTest extends TestCase
{

    public function testAnExistingPropertyCanBeAccessed()
    {
        $graphNode = new Collection(['foo' => 'bar']);

        $field = $graphNode->getField('foo');
        static::assertEquals('bar', $field);
    }

    public function testAMissingPropertyWillReturnNull()
    {
        $graphNode = new Collection(['foo' => 'bar']);
        $field = $graphNode->getField('baz');

        static::assertNull($field, 'Expected the property to return null.');
    }

    public function testAMissingPropertyWillReturnTheDefault()
    {
        $graphNode = new Collection(['foo' => 'bar']);

        $field = $graphNode->getField('baz', 'faz');
        static::assertEquals('faz', $field);
    }

    public function testFalseDefaultsWillReturnSameType()
    {
        $graphNode = new Collection(['foo' => 'bar']);

        $field = $graphNode->getField('baz', '');
        static::assertSame('', $field);

        $field = $graphNode->getField('baz', 0);
        static::assertSame(0, $field);

        $field = $graphNode->getField('baz', false);
        static::assertSame(false, $field);
    }

    public function testTheKeysFromTheCollectionCanBeReturned()
    {
        $graphNode = new Collection([
            'key1' => 'foo',
            'key2' => 'bar',
            'key3' => 'baz',
        ]);

        $fieldNames = $graphNode->getFieldNames();
        static::assertEquals(['key1', 'key2', 'key3'], $fieldNames);
    }

    public function testAnArrayCanBeInjectedViaTheConstructor()
    {
        $collection = new Collection(['foo', 'bar']);
        static::assertEquals(['foo', 'bar'], $collection->asArray());
    }

    public function testACollectionCanBeConvertedToProperJson()
    {
        $collection = new Collection(['foo', 'bar', 123]);

        $collectionAsString = $collection->asJson();

        static::assertEquals('["foo","bar",123]', $collectionAsString);
    }

    public function testACollectionCanBeCounted()
    {
        $collection = new Collection(['foo', 'bar', 'baz']);

        $collectionCount = count($collection);

        static::assertEquals(3, $collectionCount);
    }

    public function testACollectionCanBeAccessedAsAnArray()
    {
        $collection = new Collection(['foo' => 'bar', 'faz' => 'baz']);

        static::assertEquals('bar', $collection['foo']);
        static::assertEquals('baz', $collection['faz']);
    }

    public function testACollectionCanBeIteratedOver()
    {
        $collection = new Collection(['foo' => 'bar', 'faz' => 'baz']);

        static::assertInstanceOf('IteratorAggregate', $collection);

        $newArray = [];

        foreach ($collection as $k => $v) {
            $newArray[$k] = $v;
        }

        static::assertEquals(['foo' => 'bar', 'faz' => 'baz'], $newArray);
    }
}
