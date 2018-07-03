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

use Facebook\GraphNode\Birthday;
use Facebook\GraphNode\GraphNode;
use PHPUnit\Framework\TestCase;

class GraphNodeTest extends TestCase
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

    /**
     * @dataProvider provideDateTimeFieldNames
     */
    public function testCastDateTimeFieldsToDateTime($fieldName)
    {
        $graphNode = new GraphNode([$fieldName => '1989-11-02']);

        $this->assertInstanceOf(\DateTime::class, $graphNode->getField($fieldName));
    }

    public static function provideDateTimeFieldNames()
    {
        yield ['created_time'];
        yield ['updated_time'];
        yield ['start_time'];
        yield ['stop_time'];
        yield ['end_time'];
        yield ['backdated_time'];
        yield ['issued_at'];
        yield ['expires_at'];
        yield ['publish_time'];
    }

    /**
     * @dataProvider provideValidDateTimeFieldValues
     */
    public function testCastDateTimeFieldValueToDateTime($value, $message, $prettyDate = null)
    {
        $graphNode = new GraphNode(['created_time' => $value]);

        $this->assertInstanceOf(\DateTime::class, $graphNode->getField('created_time'), $message);

        if ($prettyDate !== null) {
            $this->assertEquals($prettyDate, $graphNode->getField('created_time')->format(\DateTime::RFC1036));
        }
    }

    public static function provideValidDateTimeFieldValues()
    {
        yield ['1985-10-26T01:21:00+0000', 'Expected the valid ISO 8601 formatted date from Back To The Future to pass.'];
        yield ['2014-07-15T03:44:53+0000', 'Expected the valid ISO 8601 formatted date to pass.', 'Tue, 15 Jul 14 03:44:53 +0000'];
        yield ['1999-12-31', 'Expected the valid ISO 8601 formatted date to party like it\'s 1999.'];
        yield ['2009-05-19T14:39Z', 'Expected the valid ISO 8601 formatted date to pass.'];
        yield ['2014-W36', 'Expected the valid ISO 8601 formatted date to pass.'];
        yield [1405547020, 'Expected the valid timestamp to pass.', 'Wed, 16 Jul 14 23:43:40 +0200'];
    }

    /**
     * @dataProvider provideInvalidDateTimeFieldValues
     */
    public function testNotCastDateTimeFieldValueToDateTime($value, $message)
    {
        $graphNode = new GraphNode(['created_time' => $value]);

        $this->assertNotInstanceOf(\DateTime::class, $graphNode->getField('created_time'), $message);
    }

    public static function provideInvalidDateTimeFieldValues()
    {
        yield ['2009-05-19T14a39r', 'Expected the invalid ISO 8601 format to fail.'];
        yield ['foo_time', 'Expected the invalid ISO 8601 format to fail.'];
    }

    public function testCastBirthdayFieldValueToBirthday()
    {
        $graphNode = new GraphNode(['birthday' => '11/02/1989']);

        $this->assertInstanceOf(Birthday::class, $graphNode->getField('birthday'));
    }

    public function testGettingGraphNodeAsAnArrayWillNotUncastTheDateTimeObject()
    {
        $graphNode = new GraphNode([
            'id' => '123',
            'created_time' => '2014-07-15T03:44:53+0000',
        ]);

        $graphNodeAsArray = $graphNode->asArray();

        $this->assertInstanceOf(\DateTime::class, $graphNodeAsArray['created_time']);
    }

    public function testGettingAGraphNodeAsAStringWillSafelyRepresentDateTimes()
    {
        $graphNode = new GraphNode([
            'id' => '123',
            'created_time' => '2014-07-15T03:44:53+0000',
        ]);

        $graphNodeAsString = (string) $graphNode;

        $this->assertEquals('{"id":"123","created_time":"2014-07-15T03:44:53+0000"}', $graphNodeAsString);
    }

    public function testAnExistingFieldCanBeAccessed()
    {
        $graphNode = new GraphNode(['foo' => 'bar']);

        $field = $graphNode->getField('foo');
        $this->assertEquals('bar', $field);
    }

    public function testAMissingFieldWillReturnNull()
    {
        $graphNode = new GraphNode(['foo' => 'bar']);
        $field = $graphNode->getField('baz');

        $this->assertNull($field, 'Expected the property to return null.');
    }

    public function testAMissingFieldWillReturnTheDefault()
    {
        $graphNode = new GraphNode(['foo' => 'bar']);

        $field = $graphNode->getField('baz', 'faz');
        $this->assertEquals('faz', $field);
    }

    public function testFalseDefaultsWillReturnSameType()
    {
        $graphNode = new GraphNode(['foo' => 'bar']);

        $field = $graphNode->getField('baz', '');
        $this->assertSame('', $field);

        $field = $graphNode->getField('baz', 0);
        $this->assertSame(0, $field);

        $field = $graphNode->getField('baz', false);
        $this->assertFalse($field);
    }

    public function testTheFieldsFromTheGraphNodeCanBeReturned()
    {
        $graphNode = new GraphNode([
            'field1' => 'foo',
            'field2' => 'bar',
            'field3' => 'baz',
        ]);

        $fieldNames = $graphNode->getFieldNames();
        $this->assertEquals(['field1', 'field2', 'field3'], $fieldNames);
    }

    public function testAGraphNodeCanBeConvertedToAString()
    {
        $graphNode = new GraphNode(['foo', 'bar', 123]);

        $graphNodeAsString = (string) $graphNode;

        $this->assertEquals('["foo","bar",123]', $graphNodeAsString);
    }
}
