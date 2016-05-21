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

use Mockery as m;
use Facebook\GraphNodes\GraphNodeFactory;

class GraphAlbumTest extends \PHPUnit_Framework_TestCase
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
            'created_time' => '2014-07-15T03:54:34+0000',
            'updated_time' => '2014-07-12T01:24:09+0000',
            'id' => '123',
            'name' => 'Bar',
        ];

        $this->responseMock
            ->shouldReceive('getDecodedBody')
            ->once()
            ->andReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock);
        $graphNode = $factory->makeGraphAlbum();

        $createdTime = $graphNode->getCreatedTime();
        $updatedTime = $graphNode->getUpdatedTime();

        $this->assertInstanceOf('DateTime', $createdTime);
        $this->assertInstanceOf('DateTime', $updatedTime);
    }

    public function testFromGetsCastAsGraphUser()
    {
        $dataFromGraph = [
            'id' => '123',
            'from' => [
                'id' => '1337',
                'name' => 'Foo McBar',
            ],
        ];

        $this->responseMock
            ->shouldReceive('getDecodedBody')
            ->once()
            ->andReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock);
        $graphNode = $factory->makeGraphAlbum();

        $from = $graphNode->getFrom();

        $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphUser', $from);
    }

    public function testPlacePropertyWillGetCastAsGraphPageObject()
    {
        $dataFromGraph = [
            'id' => '123',
            'name' => 'Foo Album',
            'place' => [
                'id' => '1',
                'name' => 'For Bar Place',
            ]
        ];

        $this->responseMock
            ->shouldReceive('getDecodedBody')
            ->once()
            ->andReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock);
        $graphNode = $factory->makeGraphAlbum();

        $place = $graphNode->getPlace();

        $this->assertInstanceOf('\\Facebook\\GraphNodes\\GraphPage', $place);
    }
}
