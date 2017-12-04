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
 */
namespace Facebook\Tests\GraphNode;

use Facebook\Response;
use Facebook\GraphNode\GraphNodeFactory;
use Facebook\GraphNode\GraphPicture;
use Facebook\GraphNode\GraphUser;
use Facebook\GraphNode\GraphPage;
use Facebook\GraphNode\Birthday;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class GraphUserTest extends TestCase
{
    /**
     * @var ObjectProphecy|Response
     */
    protected $responseMock;

    protected function setUp()
    {
        $this->responseMock = $this->prophesize(Response::class);
    }

    public function testDatesGetCastToDateTime()
    {
        $dataFromGraph = [
            'updated_time' => '2016-04-26 13:22:05',
        ];

        $this->responseMock->getDecodedBody()->willReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock->reveal());
        $graphNode = $factory->makeGraphUser();

        $updatedTime = $graphNode->getField('updated_time');

        $this->assertInstanceOf(\DateTime::class, $updatedTime);
    }

    public function testBirthdaysGetCastToBirthday()
    {
        $dataFromGraph = [
            'birthday' => '1984/01/01',
        ];

        $this->responseMock->getDecodedBody()->willReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock->reveal());
        $graphNode = $factory->makeGraphUser();

        $birthday = $graphNode->getBirthday();

        // Test to ensure BC
        $this->assertInstanceOf(\DateTime::class, $birthday);

        $this->assertInstanceOf(Birthday::class, $birthday);
        $this->assertTrue($birthday->hasDate());
        $this->assertTrue($birthday->hasYear());
        $this->assertEquals('1984/01/01', $birthday->format('Y/m/d'));
    }

    public function testBirthdayCastHandlesDateWithoutYear()
    {
        $dataFromGraph = [
            'birthday' => '03/21',
        ];

        $this->responseMock->getDecodedBody()->willReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock->reveal());
        $graphNode = $factory->makeGraphUser();

        $birthday = $graphNode->getBirthday();

        $this->assertTrue($birthday->hasDate());
        $this->assertFalse($birthday->hasYear());
        $this->assertEquals('03/21', $birthday->format('m/d'));
    }

    public function testBirthdayCastHandlesYearWithoutDate()
    {
        $dataFromGraph = [
            'birthday' => '1984',
        ];

        $this->responseMock->getDecodedBody()->willReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock->reveal());
        $graphNode = $factory->makeGraphUser();

        $birthday = $graphNode->getBirthday();

        $this->assertTrue($birthday->hasYear());
        $this->assertFalse($birthday->hasDate());
        $this->assertEquals('1984', $birthday->format('Y'));
    }

    public function testPagePropertiesWillGetCastAsGraphPageObjects()
    {
        $dataFromGraph = [
            'id' => '123',
            'name' => 'Foo User',
            'hometown' => [
                'id' => '1',
                'name' => 'Foo Place',
            ],
            'location' => [
                'id' => '2',
                'name' => 'Bar Place',
            ],
        ];

        $this->responseMock->getDecodedBody()->willReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock->reveal());
        $graphNode = $factory->makeGraphUser();

        $hometown = $graphNode->getHometown();
        $location = $graphNode->getLocation();

        $this->assertInstanceOf(GraphPage::class, $hometown);
        $this->assertInstanceOf(GraphPage::class, $location);
    }

    public function testUserPropertiesWillGetCastAsGraphUserObjects()
    {
        $dataFromGraph = [
            'id' => '123',
            'name' => 'Foo User',
            'significant_other' => [
                'id' => '1337',
                'name' => 'Bar User',
            ],
        ];

        $this->responseMock->getDecodedBody()->willReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock->reveal());
        $graphNode = $factory->makeGraphUser();

        $significantOther = $graphNode->getSignificantOther();

        $this->assertInstanceOf(GraphUser::class, $significantOther);
    }

    public function testPicturePropertiesWillGetCastAsGraphPictureObjects()
    {
        $dataFromGraph = [
            'id' => '123',
            'name' => 'Foo User',
            'picture' => [
                'is_silhouette' => true,
                'url' => 'http://foo.bar',
                'width' => 200,
                'height' => 200,
            ],
        ];

        $this->responseMock->getDecodedBody()->willReturn($dataFromGraph);
        $factory = new GraphNodeFactory($this->responseMock->reveal());
        $graphNode = $factory->makeGraphUser();

        $Picture = $graphNode->getPicture();

        $this->assertInstanceOf(GraphPicture::class, $Picture);
        $this->assertTrue($Picture->isSilhouette());
        $this->assertEquals(200, $Picture->getWidth());
        $this->assertEquals(200, $Picture->getHeight());
        $this->assertEquals('http://foo.bar', $Picture->getUrl());
    }
}
