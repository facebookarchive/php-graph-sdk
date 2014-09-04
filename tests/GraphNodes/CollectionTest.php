<?php
/**
 * Copyright 2014 Facebook, Inc.
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

class CollectionTest extends \PHPUnit_Framework_TestCase
{

  public function testAnExistingPropertyCanBeAccessed()
  {
    $graphObject = new Collection(['foo' => 'bar']);
    $property = $graphObject->getProperty('foo');

    $this->assertEquals('bar', $property);
  }

  public function testAMissingPropertyWillReturnNull()
  {
    $graphObject = new Collection(['foo' => 'bar']);
    $property = $graphObject->getProperty('baz');

    $this->assertNull($property, 'Expected the property to return null.');
  }

  public function testAMissingPropertyWillReturnTheDefault()
  {
    $graphObject = new Collection(['foo' => 'bar']);
    $property = $graphObject->getProperty('baz', 'faz');

    $this->assertEquals('faz', $property);
  }

  public function testTheKeysFromTheCollectionCanBeReturned()
  {
    $graphObject = new Collection([
      'key1' => 'foo',
      'key2' => 'bar',
      'key3' => 'baz',
    ]);
    $propertyKeys = $graphObject->getPropertyNames();

    $this->assertEquals(['key1', 'key2', 'key3'], $propertyKeys);
  }

  public function testAnArrayCanBeInjectedViaTheConstructor()
  {
    $collection = new Collection(['foo', 'bar']);
    $this->assertEquals(['foo', 'bar'], $collection->asArray());
  }

  public function testACollectionCanBeConvertedToProperJson()
  {
    $collection = new Collection(['foo', 'bar', 123]);

    $collectionAsString = $collection->asJson();

    $this->assertEquals('["foo","bar",123]', $collectionAsString);
  }

  public function testACollectionCanBeCounted()
  {
    $collection = new Collection(['foo', 'bar', 'baz']);

    $collectionCount = count($collection);

    $this->assertEquals(3, $collectionCount);
  }

  public function testACollectionCanBeAccessedAsAnArray()
  {
    $collection = new Collection(['foo' => 'bar', 'faz' => 'baz']);

    $this->assertEquals('bar', $collection['foo']);
    $this->assertEquals('baz', $collection['faz']);
  }

  public function testACollectionCanBeIteratedOver()
  {
    $collection = new Collection(['foo' => 'bar', 'faz' => 'baz']);

    $this->assertInstanceOf('IteratorAggregate', $collection);

    $newArray = [];

    foreach ($collection as $k => $v) {
      $newArray[$k] = $v;
    }

    $this->assertEquals(['foo' => 'bar', 'faz' => 'baz'], $newArray);
  }

}
