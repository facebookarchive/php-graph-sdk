<?php
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
namespace Facebook\Tests\GraphNode;

use Facebook\GraphNode\GraphObjectFactory;
use Facebook\Application;
use Facebook\Request;
use Facebook\Response;
use Facebook\GraphNode\GraphList;
use Facebook\GraphNode\GraphObject;
use PHPUnit\Framework\TestCase;

/**
 * @todo v6: Remove this test
 */
class GraphObjectFactoryTest extends TestCase
{
    /**
     * @var \Facebook\Request
     */
    protected $request;

    protected function setUp()
    {
        $app = new Application('123', 'foo_app_secret');
        $this->request = new Request(
            $app,
            'foo_token',
            'GET',
            '/me/photos?keep=me',
            ['foo' => 'bar'],
            'foo_eTag',
            'v1337'
        );
    }

    public function testAGraphNodeWillBeCastAsAGraphNode()
    {
        $data = json_encode([
            'id' => '123',
            'name' => 'Foo McBar',
            'link' => 'http://facebook/foo',
        ]);
        $res = new Response($this->request, $data);

        $factory = new GraphObjectFactory($res);
        $graphObject = $factory->makeGraphObject();
        $graphData = $graphObject->asArray();

        $this->assertInstanceOf(GraphObject::class, $graphObject);
        $this->assertEquals([
            'id' => '123',
            'name' => 'Foo McBar',
            'link' => 'http://facebook/foo',
        ], $graphData);
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
        $res = new Response($this->request, $data);

        $factory = new GraphObjectFactory($res);
        $graphList = $factory->makeGraphList();
        $graphData = $graphList->asArray();

        $this->assertInstanceOf(GraphList::class, $graphList);
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
}
