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
namespace Facebook\Tests\GraphNodes;

use Mockery as m;
use Facebook\GraphNodes\GraphNodeFactory;

class GraphSessionInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Facebook\FacebookResponse
     */
    protected $responseMock;

    protected function setUp()
    {
        $this->responseMock = m::mock('\\Facebook\\FacebookResponse');
    }

    public function testDatesGetCastToDateTime()
    {
        $dataFromGraph = [
            'expires_at' => 123,
            'issued_at' => 1337,
        ];

        $this->responseMock
            ->shouldReceive('getDecodedBody')
            ->once()
            ->andReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock);

        $graphNode = $factory->makeGraphSessionInfo();

        $expires = $graphNode->getExpiresAt();
        $issuedAt = $graphNode->getIssuedAt();

        $this->assertInstanceOf('DateTime', $expires);
        $this->assertInstanceOf('DateTime', $issuedAt);
    }
}
