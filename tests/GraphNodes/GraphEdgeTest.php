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
use Facebook\GraphNodes\GraphEdge;

class GraphEdgeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Facebook\FacebookRequest
     */
    protected $request;

    protected $pagination = [
        'next' => 'https://graph.facebook.com/v7.12/998899/photos?pretty=0&limit=25&after=foo_after_cursor',
        'previous' => 'https://graph.facebook.com/v7.12/998899/photos?pretty=0&limit=25&before=foo_before_cursor',
    ];

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

    /**
     * @expectedException \Facebook\Exceptions\FacebookSDKException
     */
    public function testNonGetRequestsWillThrow()
    {
        $this->request->setMethod('POST');
        $graphEdge = new GraphEdge($this->request);
        $graphEdge->validateForPagination();
    }

    public function testCanReturnGraphGeneratedPaginationEndpoints()
    {
        $graphEdge = new GraphEdge(
            $this->request,
            [],
            ['paging' => $this->pagination]
        );
        $nextPage = $graphEdge->getPaginationUrl('next');
        $prevPage = $graphEdge->getPaginationUrl('previous');

        $this->assertEquals('/998899/photos?pretty=0&limit=25&after=foo_after_cursor', $nextPage);
        $this->assertEquals('/998899/photos?pretty=0&limit=25&before=foo_before_cursor', $prevPage);
    }

    public function testCanInstantiateNewPaginationRequest()
    {
        $graphEdge = new GraphEdge(
            $this->request,
            [],
            ['paging' => $this->pagination],
            '/1234567890/likes'
        );
        $nextPage = $graphEdge->getNextPageRequest();
        $prevPage = $graphEdge->getPreviousPageRequest();

        $this->assertInstanceOf('Facebook\FacebookRequest', $nextPage);
        $this->assertInstanceOf('Facebook\FacebookRequest', $prevPage);
        $this->assertNotSame($this->request, $nextPage);
        $this->assertNotSame($this->request, $prevPage);
        $this->assertEquals('/v1337/998899/photos?access_token=foo_token&after=foo_after_cursor&appsecret_proof=857d5f035a894f16b4180f19966e055cdeab92d4d53017b13dccd6d43b6497af&foo=bar&limit=25&pretty=0', $nextPage->getUrl());
        $this->assertEquals('/v1337/998899/photos?access_token=foo_token&appsecret_proof=857d5f035a894f16b4180f19966e055cdeab92d4d53017b13dccd6d43b6497af&before=foo_before_cursor&foo=bar&limit=25&pretty=0', $prevPage->getUrl());
    }
}
