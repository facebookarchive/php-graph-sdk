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

use Facebook\GraphNodes\GraphObjectFactory;
use Facebook\FacebookApp;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;

/**
 * @todo v6: Remove this test
 */
class GraphObjectFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Facebook\FacebookRequest
     */
    protected $request;

    public function setUp()
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

    public function testAGraphNodeWillBeCastAsAGraphNode()
    {
        $data = json_encode([
            'id' => '123',
            'name' => 'Foo McBar',
            'link' => 'http://facebook/foo',
        ]);
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphObjectFactory($res);
        $graphObject = $factory->makeGraphObject();
        $graphData = $graphObject->asArray();

        $this->assertInstanceOf('\Facebook\GraphNodes\GraphObject', $graphObject);
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
        $res = new FacebookResponse($this->request, $data);

        $factory = new GraphObjectFactory($res);
        $graphList = $factory->makeGraphList();
        $graphData = $graphList->asArray();

        $this->assertInstanceOf('\Facebook\GraphNodes\GraphList', $graphList);
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
