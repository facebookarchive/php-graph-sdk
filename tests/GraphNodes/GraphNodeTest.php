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

use Facebook\GraphNodes\GraphNode;

class GraphNodeTest extends \PHPUnit_Framework_TestCase
{
    public function testAnEmptyBaseGraphNodeCanInstantiate()
    {
        $graphNode = new GraphNode();
        $backingData = $graphNode->asArray();

        $this->assertEquals([], $backingData);
    }

    public function testAGraphNodeCanInstantiateWithData()
    {
        $graphNode = new GraphNode(['foo' => 'bar']);
        $backingData = $graphNode->asArray();

        $this->assertEquals(['foo' => 'bar'], $backingData);
    }

    public function testDatesThatShouldBeCastAsDateTimeObjectsAreDetected()
    {
        $graphNode = new GraphNode();

        // Should pass
        $shouldPass = $graphNode->isIso8601DateString('1985-10-26T01:21:00+0000');
        $this->assertTrue($shouldPass, 'Expected the valid ISO 8601 formatted date from Back To The Future to pass.');

        $shouldPass = $graphNode->isIso8601DateString('1999-12-31');
        $this->assertTrue($shouldPass, 'Expected the valid ISO 8601 formatted date to party like it\'s 1999.');

        $shouldPass = $graphNode->isIso8601DateString('2009-05-19T14:39Z');
        $this->assertTrue($shouldPass, 'Expected the valid ISO 8601 formatted date to pass.');

        $shouldPass = $graphNode->isIso8601DateString('2014-W36');
        $this->assertTrue($shouldPass, 'Expected the valid ISO 8601 formatted date to pass.');

        // Should fail
        $shouldFail = $graphNode->isIso8601DateString('2009-05-19T14a39r');
        $this->assertFalse($shouldFail, 'Expected the invalid ISO 8601 format to fail.');

        $shouldFail = $graphNode->isIso8601DateString('foo_time');
        $this->assertFalse($shouldFail, 'Expected the invalid ISO 8601 format to fail.');
    }

    public function testATimeStampCanBeConvertedToADateTimeObject()
    {
        $someTimeStampFromGraph = 1405547020;
        $graphNode = new GraphNode();
        $dateTime = $graphNode->castToDateTime($someTimeStampFromGraph);
        $prettyDate = $dateTime->format(\DateTime::RFC1036);
        $timeStamp = $dateTime->getTimestamp();

        $this->assertInstanceOf('DateTime', $dateTime);
        $this->assertEquals('Wed, 16 Jul 14 23:43:40 +0200', $prettyDate);
        $this->assertEquals(1405547020, $timeStamp);
    }

    public function testAGraphDateStringCanBeConvertedToADateTimeObject()
    {
        $someDateStringFromGraph = '2014-07-15T03:44:53+0000';
        $graphNode = new GraphNode();
        $dateTime = $graphNode->castToDateTime($someDateStringFromGraph);
        $prettyDate = $dateTime->format(\DateTime::RFC1036);
        $timeStamp = $dateTime->getTimestamp();

        $this->assertInstanceOf('DateTime', $dateTime);
        $this->assertEquals('Tue, 15 Jul 14 03:44:53 +0000', $prettyDate);
        $this->assertEquals(1405395893, $timeStamp);
    }

    public function testUncastingAGraphNodeWillUncastTheDateTimeObject()
    {
        $collectionOne = new GraphNode(['foo', 'bar']);
        $collectionTwo = new GraphNode([
            'id' => '123',
            'date' => new \DateTime('2014-07-15T03:44:53+0000'),
            'some_collection' => $collectionOne,
        ]);

        $uncastArray = $collectionTwo->uncastItems();

        $this->assertEquals([
            'id' => '123',
            'date' => '2014-07-15T03:44:53+0000',
            'some_collection' => ['foo', 'bar'],
        ], $uncastArray);
    }

    public function testGettingGraphNodeAsAnArrayWillNotUncastTheDateTimeObject()
    {
        $collection = new GraphNode([
            'id' => '123',
            'date' => new \DateTime('2014-07-15T03:44:53+0000'),
        ]);

        $collectionAsArray = $collection->asArray();

        $this->assertInstanceOf('DateTime', $collectionAsArray['date']);
    }

    public function testReturningACollectionAsJasonWillSafelyRepresentDateTimes()
    {
        $collection = new GraphNode([
            'id' => '123',
            'date' => new \DateTime('2014-07-15T03:44:53+0000'),
        ]);

        $collectionAsString = $collection->asJson();

        $this->assertEquals('{"id":"123","date":"2014-07-15T03:44:53+0000"}', $collectionAsString);
    }
}
