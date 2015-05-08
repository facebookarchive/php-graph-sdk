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

class GraphAchievementTest extends AbstractGraphNode
{

    public function testIdIsString()
    {
        $dataFromGraph = [
            'id' => '1337'
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $id = $graphNode->getId();

        $this->assertEquals($dataFromGraph['id'], $id);
    }

    public function testTypeIsAlwaysString()
    {
        $dataFromGraph = [
            'id' => '1337'
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $type = $graphNode->getType();

        $this->assertEquals('game.achievement', $type);
    }

    public function testNoFeedStoryIsBoolean()
    {
        $dataFromGraph = [
            'no_feed_story' => (rand(0, 1) == 1)
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $isNoFeedStory = $graphNode->isNoFeedStory();

        $this->assertTrue(is_bool($isNoFeedStory));
    }

    public function testDatesGetCastToDateTime()
    {
        $dataFromGraph = [
            'publish_time' => '2014-07-15T03:54:34+0000'
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $publishTime = $graphNode->getPublishTime();

        $this->assertInstanceOf('DateTime', $publishTime);
    }

    public function testFromGetsCastAsGraphUser()
    {
        $dataFromGraph = [
            'from' => [
                'id' => '1337',
                'name' => 'Foo McBar'
            ]
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $from = $graphNode->getFrom();

        $this->assertInstanceOf('\Facebook\GraphNodes\GraphUser', $from);
    }

    public function testApplicationGetsCastAsGraphApplication()
    {
        $dataFromGraph = [
            'application' => [
                'id' => '1337'
            ]
        ];

        $factory = $this->makeFactoryWithData($dataFromGraph);
        $graphNode = $factory->makeGraphAchievement();

        $app = $graphNode->getApplication();

        $this->assertInstanceOf('\Facebook\GraphNodes\GraphApplication', $app);
    }
}
