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

use Facebook\GraphNodes\GraphObject;

class MyFooSubClassGraphObject extends GraphObject {}

class GraphObjectTest extends \PHPUnit_Framework_TestCase
{

  public function testAnEmptyBaseGraphObjectCanInstantiate()
  {
    $graphObject = new GraphObject();
    $backingData = $graphObject->asArray();

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
    $this->assertEquals([], $backingData);
  }

  public function testAGraphObjectCanInstantiateWithData()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $backingData = $graphObject->asArray();

    $this->assertEquals(['foo' => 'bar'], $backingData);
  }

  public function testSomethingThatLooksLikeAListWillBeFlattened()
  {
    $dataFromGraph = [
      'data' => [
        [
          'id' => '123',
          'name' => 'Foo McBar',
          'link' => 'http://facebook/foo',
        ],
      ],
    ];
    $graphObject = new GraphObject($dataFromGraph);

    $this->assertInstanceOf('Facebook\GraphNodes\GraphObject', $graphObject);
  }

  public function testAnExistingPropertyCanBeAccessed()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $property = $graphObject->getProperty('foo');

    $this->assertEquals('bar', $property);
  }

  public function testAMissingPropertyWillReturnNull()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $property = $graphObject->getProperty('baz');

    $this->assertNull($property, 'Expected the property to return null.');
  }

  public function testAMissingPropertyWillReturnTheDefault()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $property = $graphObject->getProperty('baz', 'faz');

    $this->assertEquals('faz', $property);
  }

  public function testTheKeysFromTheGraphDataCanBeReturned()
  {
    $graphObject = new GraphObject([
      'key1' => 'foo',
      'key2' => 'bar',
      'key3' => 'baz',
    ]);
    $propertyKeys = $graphObject->getPropertyNames();

    $this->assertEquals(['key1', 'key2', 'key3'], $propertyKeys);
  }

  public function testAGraphObjectCanBeRecast()
  {
    $fooGraphObject = new GraphObject(['foo' => 'bar']);
    $newFooGraphObject = $fooGraphObject->cast('Facebook\Tests\GraphNodes\MyFooSubClassGraphObject');
    $this->assertInstanceOf('Facebook\Tests\GraphNodes\MyFooSubClassGraphObject', $newFooGraphObject);
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testTryingToRecastToAGraphObjectThatDoesntExistWillThrow()
  {
    $graphObject = new GraphObject(['foo' => 'bar']);
    $graphObject->cast('FooClass');
  }

}
