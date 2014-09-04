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

use Mockery as m;
use Facebook\GraphNodes\GraphObjectFactory;
use Facebook\GraphNodes\GraphObject;

class MyFooSubClassGraphObject extends GraphObject {}

class MyFooGraphObject extends GraphObject {
  protected static $graphObjectMap = [
    'foo_object' => '\\Facebook\\Tests\\GraphNodes\\MyFooSubClassGraphObject',
  ];
}

class GraphObjectFactoryTest extends \PHPUnit_Framework_TestCase
{

  /**
   * @var \Facebook\Entities\FacebookResponse
   */
  protected $responseMock;

  public function setUp()
  {
    $this->responseMock = m::mock('\\Facebook\\Entities\\FacebookResponse');
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testADecodedResponseThatIsNotAnArrayWillThrow()
  {
    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn('foo');

    $factory = new GraphObjectFactory($this->responseMock);
    $factory->validateResponseAsArray();
  }

  public function testAValidGraphObjectResponseWillNotThrow()
  {
    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn(['id' => '123', 'name' => 'foo']);

    $factory = new GraphObjectFactory($this->responseMock);
    $factory->validateResponseCastableAsGraphObject();
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testANonGraphObjectResponseWillThrow()
  {
    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn([
          'data' => [
            ['id' => '123', 'name' => 'foo'],
            ['id' => '1337', 'name' => 'bar'],
          ]
        ]);

    $factory = new GraphObjectFactory($this->responseMock);
    $factory->validateResponseCastableAsGraphObject();
  }

  public function testAValidGraphListResponseWillNotThrow()
  {
    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn([
          'data' => [
            ['id' => '123', 'name' => 'foo'],
            ['id' => '1337', 'name' => 'bar'],
          ]
        ]);

    $factory = new GraphObjectFactory($this->responseMock);
    $factory->validateResponseCastableAsGraphList();
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testANonGraphListResponseWillThrow()
  {
    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn(['id' => '123', 'name' => 'foo']);

    $factory = new GraphObjectFactory($this->responseMock);
    $factory->validateResponseCastableAsGraphList();
  }

  public function testOnlyNumericArraysAreCastableAsAGraphList()
  {
    $shouldPassOne = GraphObjectFactory::isCastableAsGraphList([]);
    $shouldPassTwo = GraphObjectFactory::isCastableAsGraphList(['foo', 'bar']);
    $shouldFail = GraphObjectFactory::isCastableAsGraphList(['faz' => 'baz']);

    $this->assertTrue($shouldPassOne, 'Expected the given array to be castable as a GraphList.');
    $this->assertTrue($shouldPassTwo, 'Expected the given array to be castable as a GraphList.');
    $this->assertFalse($shouldFail, 'Expected the given array to not be castable as a GraphList.');
  }

  /**
   * @expectedException \Facebook\Exceptions\FacebookSDKException
   */
  public function testInvalidSubClassesWillThrow()
  {
    GraphObjectFactory::validateSubclass('FooSubClass');
  }

  public function testValidSubClassesWillNotThrow()
  {
    GraphObjectFactory::validateSubclass('\\Facebook\\GraphNodes\\GraphObject');
    GraphObjectFactory::validateSubclass('\\Facebook\\GraphNodes\\GraphAlbum');
    GraphObjectFactory::validateSubclass('\\Facebook\\Tests\\GraphNodes\\MyFooGraphObject');
  }

  public function testCastingAsASubClassObjectWillInstantiateTheSubClass()
  {
    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn(['id' => '123', 'name' => 'foo']);

    $factory = new GraphObjectFactory($this->responseMock);
    $mySubClassObject = $factory->makeGraphObject('\\Facebook\\Tests\\GraphNodes\\MyFooGraphObject');

    $this->assertInstanceOf('\\Facebook\\Tests\\GraphNodes\\MyFooGraphObject', $mySubClassObject);
  }

  public function testASubClassMappingWillAutomaticallyInstantiateSubClass()
  {
    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn([
          'id' => '123',
          'name' => 'Foo Name',
          'foo_object' => [
            'id' => '1337',
            'name' => 'Should be sub classed!',
          ],
        ]);

    $factory = new GraphObjectFactory($this->responseMock);
    $mySubClassObject = $factory->makeGraphObject('\\Facebook\\Tests\\GraphNodes\\MyFooGraphObject');
    $fooObject = $mySubClassObject->getProperty('foo_object');

    $this->assertInstanceOf('\\Facebook\\Tests\\GraphNodes\\MyFooGraphObject', $mySubClassObject);
    $this->assertInstanceOf('\\Facebook\\Tests\\GraphNodes\\MyFooSubClassGraphObject', $fooObject);
  }

  public function testAnUnknownGraphObjectWillBeCastAsAGenericGraphObject()
  {
    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn([
        'id' => '123',
        'name' => 'Foo Name',
        'unknown_object' => [
          'id' => '1337',
          'name' => 'Should be generic!',
        ],
      ]);

    $factory = new GraphObjectFactory($this->responseMock);

    $mySubClassObject = $factory->makeGraphObject('\\Facebook\\Tests\\GraphNodes\\MyFooGraphObject');
    $unknownObject = $mySubClassObject->getProperty('unknown_object');

    $this->assertInstanceOf('\\Facebook\\Tests\\GraphNodes\\MyFooGraphObject', $mySubClassObject);
    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphObject', $unknownObject);
    $this->assertNotInstanceOf('\\Facebook\\Tests\\GraphNodes\\MyFooGraphObject', $unknownObject);
  }

  public function testAListFromGraphWillBeCastAsAGraphList()
  {
    $dataFromGraph = [
      'data' => [
        [
          'id' => '123',
          'name' => 'Foo McBar',
          'link' => 'http://facebook/foo',
        ],
        [
          'id' => '1337',
          'name' => 'Bar McBaz',
          'link' => 'http://facebook/bar',
        ],
      ],
      'paging' => [
        'next' => 'http://facebook/next',
        'previous' => 'http://facebook/prev',
      ],
    ];

    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn($dataFromGraph);

    $factory = new GraphObjectFactory($this->responseMock);

    $graphList = $factory->makeGraphList();
    $graphData = $graphList->asArray();

    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphList', $graphList);
    $this->assertEquals([
        'id' => '123',
        'name' => 'Foo McBar',
        'link' => 'http://facebook/foo',
      ], $graphData[0]);
    $this->assertEquals([
        'id' => '1337',
        'name' => 'Bar McBaz',
        'link' => 'http://facebook/bar',
      ], $graphData[1]);
  }

  public function testAGraphObjectWillBeCastAsAGraphObject()
  {
    $dataFromGraph = [
      'id' => '123',
      'name' => 'Foo McBar',
      'link' => 'http://facebook/foo',
    ];

    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn($dataFromGraph);

    $factory = new GraphObjectFactory($this->responseMock);

    $graphObject = $factory->makeGraphObject();
    $graphData = $graphObject->asArray();

    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphObject', $graphObject);
    $this->assertEquals([
        'id' => '123',
        'name' => 'Foo McBar',
        'link' => 'http://facebook/foo',
      ], $graphData);
  }

  public function testAGraphObjectWithARootDataKeyWillBeCastAsAGraphObject()
  {
    $dataFromGraph = [
      'data' => [
        'id' => '123',
        'name' => 'Foo McBar',
        'link' => 'http://facebook/foo',
      ],
    ];

    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn($dataFromGraph);

    $factory = new GraphObjectFactory($this->responseMock);

    $graphObject = $factory->makeGraphObject();
    $graphData = $graphObject->asArray();

    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphObject', $graphObject);
    $this->assertEquals([
        'id' => '123',
        'name' => 'Foo McBar',
        'link' => 'http://facebook/foo',
      ], $graphData);
  }

  public function testACollectionWillBeCastRecursively()
  {
    $someUser = [
      'id' => '123',
      'name' => 'Foo McBar',
    ];
    $likesCollection = [
      'data' => [
        [
          'id' => '1',
          'name' => 'Sammy Kaye Powers',
          'is_sexy' => true,
        ],
        [
          'id' => '2',
          'name' => 'Yassine Guedidi',
          'is_sexy' => true,
        ],
        [
          'id' => '3',
          'name' => 'Fosco Marotto',
          'is_sexy' => true,
        ],
        [
          'id' => '4',
          'name' => 'Foo McUgly',
          'is_sexy' => false,
        ],
      ],
      'paging' => [
        'next' => 'http://facebook/next_likes',
        'previous' => 'http://facebook/prev_likes',
      ],
    ];
    $commentsCollection = [
      'data' => [
        [
          'id' => '42_1',
          'from' => $someUser,
          'message' => 'Foo comment.',
          'created_time' => '2014-07-15T03:54:34+0000',
          'likes' => $likesCollection,
        ],
        [
          'id' => '42_2',
          'from' => $someUser,
          'message' => 'Bar comment.',
          'created_time' => '2014-07-15T04:11:24+0000',
          'likes' => $likesCollection,
        ],
      ],
      'paging' => [
        'next' => 'http://facebook/next_comments',
        'previous' => 'http://facebook/prev_comments',
      ],
    ];
    $dataFromGraph = [
      'data' => [
        [
          'id' => '1337_1',
          'from' => $someUser,
          'story' => 'Some great foo story.',
          'likes' => $likesCollection,
          'comments' => $commentsCollection,
        ],
        [
          'id' => '1337_2',
          'from' => $someUser,
          'to' => [
            'data' => [$someUser],
          ],
          'message' => 'Some great bar message.',
          'likes' => $likesCollection,
          'comments' => $commentsCollection,
        ],
      ],
      'paging' => [
        'next' => 'http://facebook/next',
        'previous' => 'http://facebook/prev',
      ],
    ];

    $this->responseMock
      ->shouldReceive('getDecodedBody')
      ->once()
      ->andReturn($dataFromGraph);

    $factory = new GraphObjectFactory($this->responseMock);

    $graphObject = $factory->makeGraphList();
    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphList', $graphObject);

    // Story
    $storyObject = $graphObject[0];
    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphObject', $storyObject['from']);
    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphList', $storyObject['likes']);
    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphList', $storyObject['comments']);

    // Story Comments
    $storyComments = $storyObject['comments'];
    $firstStoryComment = $storyComments[0];
    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphObject', $firstStoryComment['from']);

    // Message
    $messageObject = $graphObject[1];
    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphList', $messageObject['to']);
    $toUsers = $messageObject['to'];
    $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphObject', $toUsers[0]);
  }

}
