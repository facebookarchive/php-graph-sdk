<?php
/**
 * Copyright 2016 Facebook, Inc.
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

use Facebook\FacebookApp;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\GraphNodes\GraphNodeFactory;

class GraphNodeFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Facebook\FacebookRequest
     */
    protected $request;

    protected function setUp()
    {
        $app = new FacebookApp('123', 'foo_app_secret');
        $this->request = new FacebookRequest(
            $app,
            'foo_token',
            'GET',
            '/me/photos?keep=me',
            ['foo' => 'bar'],
            'foo_eTag',
            'v1337'
        );
    }

    public function testAValidGraphNodeResponseWillNotThrow()
    {
        $data = '{"id":"123","name":"foo"}';
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $factory->validateResponseCastableAsGraphNode();
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testANonGraphNodeResponseWillThrow()
    {
        $data = '{"data":[{"id":"123","name":"foo"},{"id":"1337","name":"bar"}]}';
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $factory->validateResponseCastableAsGraphNode();
    }

    public function testAValidGraphEdgeResponseWillNotThrow()
    {
        $data = '{"data":[{"id":"123","name":"foo"},{"id":"1337","name":"bar"}]}';
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $factory->validateResponseCastableAsGraphEdge();
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testANonGraphEdgeResponseWillThrow()
    {
        $data = '{"id":"123","name":"foo"}';
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $factory->validateResponseCastableAsGraphEdge();
    }

    public function testOnlyNumericArraysAreCastableAsAGraphEdge()
    {
        $shouldPassOne = GraphNodeFactory::isCastableAsGraphEdge([]);
        $shouldPassTwo = GraphNodeFactory::isCastableAsGraphEdge(['foo', 'bar']);
        $shouldFail = GraphNodeFactory::isCastableAsGraphEdge(['faz' => 'baz']);

        $this->assertTrue($shouldPassOne, 'Expected the given array to be castable as a GraphEdge.');
        $this->assertTrue($shouldPassTwo, 'Expected the given array to be castable as a GraphEdge.');
        $this->assertFalse($shouldFail, 'Expected the given array to not be castable as a GraphEdge.');
    }

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testInvalidSubClassesWillThrow()
    {
        GraphNodeFactory::validateSubclass('FooSubClass');
    }

    public function testValidSubClassesWillNotThrow()
    {
        GraphNodeFactory::validateSubclass('\Facebook\GraphNodes\GraphNode');
        GraphNodeFactory::validateSubclass('\Facebook\GraphNodes\GraphAlbum');
        GraphNodeFactory::validateSubclass('\Facebook\Tests\Fixtures\MyFooGraphNode');
    }

    public function testCastingAsASubClassObjectWillInstantiateTheSubClass()
    {
        $data = '{"id":"123","name":"foo"}';
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $mySubClassObject = $factory->makeGraphNode('\Facebook\Tests\Fixtures\MyFooGraphNode');

        $this->assertInstanceOf('\Facebook\Tests\Fixtures\MyFooGraphNode', $mySubClassObject);
    }

    public function testASubClassMappingWillAutomaticallyInstantiateSubClass()
    {
        $data = '{"id":"123","name":"Foo Name","foo_object":{"id":"1337","name":"Should be sub classed!"}}';
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $mySubClassObject = $factory->makeGraphNode('\Facebook\Tests\Fixtures\MyFooGraphNode');
        $fooObject = $mySubClassObject->getField('foo_object');

        $this->assertInstanceOf('\Facebook\Tests\Fixtures\MyFooGraphNode', $mySubClassObject);
        $this->assertInstanceOf('\Facebook\Tests\Fixtures\MyFooSubClassGraphNode', $fooObject);
    }

    public function testAnUnknownGraphNodeWillBeCastAsAGenericGraphNode()
    {
        $data = json_encode([
            'id' => '123',
            'name' => 'Foo Name',
            'unknown_object' => [
                'id' => '1337',
                'name' => 'Should be generic!',
            ],
        ]);
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);

        $mySubClassObject = $factory->makeGraphNode('\Facebook\Tests\Fixtures\MyFooGraphNode');
        $unknownObject = $mySubClassObject->getField('unknown_object');

        $this->assertInstanceOf('\Facebook\Tests\Fixtures\MyFooGraphNode', $mySubClassObject);
        $this->assertInstanceOf('\Facebook\GraphNodes\GraphNode', $unknownObject);
        $this->assertNotInstanceOf('\Facebook\Tests\Fixtures\MyFooGraphNode', $unknownObject);
    }

    public function testAListFromGraphWillBeCastAsAGraphEdge()
    {
        $data = json_encode([
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
        ]);
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $graphEdge = $factory->makeGraphEdge();
        $graphData = $graphEdge->asArray();

        $this->assertInstanceOf('\Facebook\GraphNodes\GraphEdge', $graphEdge);
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

    public function testAGraphNodeWillBeCastAsAGraphNode()
    {
        $data = json_encode([
            'id' => '123',
            'name' => 'Foo McBar',
            'link' => 'http://facebook/foo',
        ]);
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $graphNode = $factory->makeGraphNode();
        $graphData = $graphNode->asArray();

        $this->assertInstanceOf('\Facebook\GraphNodes\GraphNode', $graphNode);
        $this->assertEquals([
            'id' => '123',
            'name' => 'Foo McBar',
            'link' => 'http://facebook/foo',
        ], $graphData);
    }

    public function testAGraphNodeWithARootDataKeyWillBeCastAsAGraphNode()
    {
        $data = json_encode([
            'data' => [
                'id' => '123',
                'name' => 'Foo McBar',
                'link' => 'http://facebook/foo',
            ],
        ]);

        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $graphNode = $factory->makeGraphNode();
        $graphData = $graphNode->asArray();

        $this->assertInstanceOf('\Facebook\GraphNodes\GraphNode', $graphNode);
        $this->assertEquals([
            'id' => '123',
            'name' => 'Foo McBar',
            'link' => 'http://facebook/foo',
        ], $graphData);
    }

    public function testAGraphEdgeWillBeCastRecursively()
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
        $data = json_encode($dataFromGraph);
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $graphNode = $factory->makeGraphEdge();
        $this->assertInstanceOf('\Facebook\GraphNodes\GraphEdge', $graphNode);

        // Story
        $storyObject = $graphNode[0];
        $this->assertInstanceOf('\Facebook\GraphNodes\GraphNode', $storyObject['from']);
        $this->assertInstanceOf('\Facebook\GraphNodes\GraphEdge', $storyObject['likes']);
        $this->assertInstanceOf('\Facebook\GraphNodes\GraphEdge', $storyObject['comments']);

        // Story Comments
        $storyComments = $storyObject['comments'];
        $firstStoryComment = $storyComments[0];
        $this->assertInstanceOf('\Facebook\GraphNodes\GraphNode', $firstStoryComment['from']);

        // Message
        $messageObject = $graphNode[1];
        $this->assertInstanceOf('\Facebook\GraphNodes\GraphEdge', $messageObject['to']);
        $toUsers = $messageObject['to'];
        $this->assertInstanceOf('\Facebook\GraphNodes\GraphNode', $toUsers[0]);
    }

    public function testAGraphEdgeWillGenerateTheProperParentGraphEdges()
    {
        $likesList = [
            'data' => [
                [
                    'id' => '1',
                    'name' => 'Sammy Kaye Powers',
                ],
            ],
            'paging' => [
                'cursors' => [
                    'after' => 'like_after_cursor',
                    'before' => 'like_before_cursor',
                ],
            ],
        ];

        $photosList = [
            'data' => [
                [
                    'id' => '777',
                    'name' => 'Foo Photo',
                    'likes' => $likesList,
                ],
            ],
            'paging' => [
                'cursors' => [
                    'after' => 'photo_after_cursor',
                    'before' => 'photo_before_cursor',
                ],
            ],
        ];

        $data = json_encode([
            'data' => [
                [
                    'id' => '111',
                    'name' => 'Foo McBar',
                    'likes' => $likesList,
                    'photos' => $photosList,
                ],
                [
                    'id' => '222',
                    'name' => 'Bar McBaz',
                    'likes' => $likesList,
                    'photos' => $photosList,
                ],
            ],
            'paging' => [
                'next' => 'http://facebook/next',
                'previous' => 'http://facebook/prev',
            ],
        ]);
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphNodeFactory($res);
        $graphEdge = $factory->makeGraphEdge();
        $topGraphEdge = $graphEdge->getParentGraphEdge();
        $childGraphEdgeOne = $graphEdge[0]['likes']->getParentGraphEdge();
        $childGraphEdgeTwo = $graphEdge[1]['likes']->getParentGraphEdge();
        $childGraphEdgeThree = $graphEdge[1]['photos']->getParentGraphEdge();
        $childGraphEdgeFour = $graphEdge[1]['photos'][0]['likes']->getParentGraphEdge();

        $this->assertNull($topGraphEdge);
        $this->assertEquals('/111/likes', $childGraphEdgeOne);
        $this->assertEquals('/222/likes', $childGraphEdgeTwo);
        $this->assertEquals('/222/photos', $childGraphEdgeThree);
        $this->assertEquals('/777/likes', $childGraphEdgeFour);
    }
}
