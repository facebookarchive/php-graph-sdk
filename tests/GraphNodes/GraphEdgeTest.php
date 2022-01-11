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

use Facebook\Application;
use Facebook\Request;
use Facebook\GraphNodes\GraphEdge;
use Facebook\GraphNodes\GraphNode;
use PHPUnit\Framework\TestCase;

/**
 * Class GraphEdgeTest
 */
class GraphEdgeTest extends TestCase
{

    /**
     * @var Request
     */
    protected Request $request;

    protected array $pagination = [
        'next' => 'https://graph.facebook.com/v7.12/998899/photos?pretty=0&limit=25&after=foo_after_cursor',
        'previous' => 'https://graph.facebook.com/v7.12/998899/photos?pretty=0&limit=25&before=foo_before_cursor',
    ];

    protected function setUp(): void
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

    public function testNonGetRequestsWillThrow(): void
    {
        $this->expectException(\Facebook\Exceptions\FacebookSDKException::class);
        $this->request->setMethod('POST');
        $graphEdge = new GraphEdge($this->request);
        $graphEdge->validateForPagination();
    }

    public function testCanReturnGraphGeneratedPaginationEndpoints(): void
    {
        $graphEdge = new GraphEdge(
            $this->request,
            [],
            ['paging' => $this->pagination]
        );
        $nextPage = $graphEdge->getPaginationUrl('next');
        $prevPage = $graphEdge->getPaginationUrl('previous');

        static::assertEquals('/998899/photos?pretty=0&limit=25&after=foo_after_cursor', $nextPage);
        static::assertEquals('/998899/photos?pretty=0&limit=25&before=foo_before_cursor', $prevPage);
    }

    public function testCanInstantiateNewPaginationRequest(): void
    {
        $graphEdge = new GraphEdge(
            $this->request,
            [],
            ['paging' => $this->pagination],
            '/1234567890/likes'
        );
        $nextPage = $graphEdge->getNextPageRequest();
        $prevPage = $graphEdge->getPreviousPageRequest();

        static::assertInstanceOf(Request::class, $nextPage);
        static::assertInstanceOf(Request::class, $prevPage);
        static::assertNotSame($this->request, $nextPage);
        static::assertNotSame($this->request, $prevPage);
        static::assertEquals('/v1337/998899/photos?access_token=foo_token&after=foo_after_cursor&appsecret_proof=857d5f035a894f16b4180f19966e055cdeab92d4d53017b13dccd6d43b6497af&foo=bar&limit=25&pretty=0', $nextPage->getUrl());
        static::assertEquals('/v1337/998899/photos?access_token=foo_token&appsecret_proof=857d5f035a894f16b4180f19966e055cdeab92d4d53017b13dccd6d43b6497af&before=foo_before_cursor&foo=bar&limit=25&pretty=0', $prevPage->getUrl());
    }

    public function testCanMapOverNodes(): void
    {
        $graphEdge = new GraphEdge(
            $this->request,
            [
                new GraphNode(['name' => 'dummy1']),
                new GraphNode(['name' => 'dummy2']),
            ],
            ['paging' => $this->pagination],
            '/1234567890/likes',
        );

        $output = '';

        $graphEdge->map(function (GraphNode $node) use (&$output) {
            $output .= $node->getField('name');
        });

        static::assertEquals('dummy1dummy2', $output);
    }


    public function testAnExistingPropertyCanBeAccessed(): void
    {
        $graphEdge = new GraphEdge($this->request, ['foo' => 'bar']);

        $field = $graphEdge->getField('foo');
        static::assertEquals('bar', $field);
    }


    public function testAMissingPropertyWillReturnNull(): void
    {
        $graphEdge = new GraphEdge($this->request, ['foo' => 'bar']);
        $field = $graphEdge->getField('baz');

        static::assertNull($field, 'Expected the property to return null.');
    }


    public function testAMissingPropertyWillReturnTheDefault(): void
    {
        $graphEdge = new GraphEdge($this->request, ['foo' => 'bar']);

        $field = $graphEdge->getField('baz', 'faz');
        static::assertEquals('faz', $field);
    }


    public function testFalseDefaultsWillReturnSameType(): void
    {
        $graphEdge = new GraphEdge($this->request, ['foo' => 'bar']);

        $field = $graphEdge->getField('baz', '');
        static::assertSame('', $field);

        $field = $graphEdge->getField('baz', 0);
        static::assertSame(0, $field);

        $field = $graphEdge->getField('baz', false);
        static::assertFalse($field);
    }


    public function testTheKeysFromTheCollectionCanBeReturned(): void
    {
        $graphEdge = new GraphEdge(
            $this->request,
            [
                'key1' => 'foo',
                'key2' => 'bar',
                'key3' => 'baz',
            ],
        );

        $fieldNames = $graphEdge->getFieldNames();
        static::assertEquals(['key1', 'key2', 'key3'], $fieldNames);
    }


    public function testAnArrayCanBeInjectedViaTheConstructor(): void
    {
        $graphEdge = new GraphEdge($this->request, ['foo', 'bar']);
        static::assertEquals(['foo', 'bar'], $graphEdge->asArray());
    }


    public function testACollectionCanBeConvertedToProperJson(): void
    {
        $graphEdge = new GraphEdge($this->request, ['foo', 'bar', 123]);

        $graphEdgeAsString = $graphEdge->asJson();

        static::assertEquals('["foo","bar",123]', $graphEdgeAsString);
    }


    public function testACollectionCanBeCounted(): void
    {
        $graphEdge = new GraphEdge($this->request, ['foo', 'bar', 'baz']);

        $graphEdgeCount = count($graphEdge);

        static::assertEquals(3, $graphEdgeCount);
    }


    public function testACollectionCanBeAccessedAsAnArray(): void
    {
        $graphEdge = new GraphEdge($this->request, ['foo' => 'bar', 'faz' => 'baz']);

        static::assertEquals('bar', $graphEdge['foo']);
        static::assertEquals('baz', $graphEdge['faz']);
    }


    public function testACollectionCanBeIteratedOver(): void
    {
        $graphEdge = new GraphEdge($this->request, ['foo' => 'bar', 'faz' => 'baz']);

        static::assertInstanceOf(\IteratorAggregate::class, $graphEdge);

        $newArray = [];

        foreach ($graphEdge as $k => $v) {
            $newArray[$k] = $v;
        }

        static::assertEquals(['foo' => 'bar', 'faz' => 'baz'], $newArray);
    }
}
