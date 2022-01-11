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
namespace Facebook\Tests;

use Facebook\Application;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\GraphNodes\GraphNode;
use Facebook\Request;
use Facebook\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class ResponseTest
 */
class ResponseTest extends TestCase
{
    /**
     * @var \Facebook\Request
     */
    protected Request $request;

    protected function setUp(): void
    {
        $app = new Application('123', 'foo_secret');
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

    public function testAnETagCanBeProperlyAccessed(): void
    {
        $response = new Response($this->request, '', 200, ['ETag' => 'foo_tag']);

        $eTag = $response->getETag();

        static::assertEquals('foo_tag', $eTag);
    }

    public function testAProperAppSecretProofCanBeGenerated(): void
    {
        $response = new Response($this->request);

        $appSecretProof = $response->getAppSecretProof();

        static::assertEquals('df4256903ba4e23636cc142117aa632133d75c642bd2a68955be1443bd14deb9', $appSecretProof);
    }

    public function testASuccessfulJsonResponseWillBeDecodedToAGraphNode(): void
    {
        $graphResponseJson = '{"id":"123","name":"Foo"}';
        $response = new Response($this->request, $graphResponseJson, 200);

        $decodedResponse = $response->getDecodedBody();
        $graphNode = $response->getGraphNode();

        static::assertFalse($response->isError(), 'Did not expect Response to return an error.');
        static::assertEquals([
            'id' => '123',
            'name' => 'Foo',
        ], $decodedResponse);
        static::assertInstanceOf(GraphNode::class, $graphNode);
    }

    public function testASuccessfulJsonResponseWillBeDecodedToAGraphEdge(): void
    {
        $graphResponseJson = '{"data":[{"id":"123","name":"Foo"},{"id":"1337","name":"Bar"}]}';
        $response = new Response($this->request, $graphResponseJson, 200);

        $graphEdge = $response->getGraphEdge();

        static::assertFalse($response->isError(), 'Did not expect Response to return an error.');
        static::assertInstanceOf(GraphNode::class, $graphEdge[0]);
        static::assertInstanceOf(GraphNode::class, $graphEdge[1]);
    }

    public function testASuccessfulUrlEncodedKeyValuePairResponseWillBeDecoded(): void
    {
        $graphResponseKeyValuePairs = 'id=123&name=Foo';
        $response = new Response($this->request, $graphResponseKeyValuePairs, 200);

        $decodedResponse = $response->getDecodedBody();

        static::assertFalse($response->isError(), 'Did not expect Response to return an error.');
        static::assertEquals([
            'id' => '123',
            'name' => 'Foo',
        ], $decodedResponse);
    }

    public function testErrorStatusCanBeCheckedWhenAnErrorResponseIsReturned(): void
    {
        $graphResponse = '{"error":{"message":"Foo error.","type":"OAuthException","code":190,"error_subcode":463}}';
        $response = new Response($this->request, $graphResponse, 401);

        $exception = $response->getThrownException();

        static::assertTrue($response->isError(), 'Expected Response to return an error.');
        static::assertInstanceOf(FacebookResponseException::class, $exception);
    }
}
