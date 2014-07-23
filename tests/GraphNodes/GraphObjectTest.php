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

use Facebook\FacebookRequest;
use Facebook\GraphNodes\GraphObject;
use Facebook\FacebookResponse;
use Facebook\GraphNodes\GraphUser;
use Facebook\Tests\FacebookTestHelper;

class GraphObjectTest extends \PHPUnit_Framework_TestCase
{

  public function testFriends()
  {
    $response = (
    new FacebookRequest(
      FacebookTestHelper::$testSession,
      'GET',
      '/me/friends'
    ))->execute()->getGraphObjectList();
    $this->assertTrue(is_array($response));
  }

  public function testArrayProperties()
  {
    $backingData = array(
      'id' => 42,
      'friends' => array(
        'data' => array(
          array(
            'id' => 1,
            'name' => 'David'
          ),
          array(
            'id' => 2,
            'name' => 'Fosco'
          )
        ),
        'paging' => array(
          'next' => 'nexturl'
        )
      )
    );
    $obj = new GraphObject($backingData);
    $friends = $obj->getPropertyAsArray('friends');
    $this->assertEquals(2, count($friends));
    $this->assertTrue($friends[0] instanceof GraphObject);
    $this->assertTrue($friends[1] instanceof GraphObject);
    $this->assertEquals('David', $friends[0]->getProperty('name'));
    $this->assertEquals('Fosco', $friends[1]->getProperty('name'));

    $backingData = array(
      'id' => 42,
      'friends' => array(
        array(
          'id' => 1,
          'name' => 'Ilya'
        ),
        array(
          'id' => 2,
          'name' => 'Kevin'
        )
      )
    );
    $obj = new GraphObject($backingData);
    $friends = $obj->getPropertyAsArray('friends');
    $this->assertEquals(2, count($friends));
    $this->assertTrue($friends[0] instanceof GraphObject);
    $this->assertTrue($friends[1] instanceof GraphObject);
    $this->assertEquals('Ilya', $friends[0]->getProperty('name'));
    $this->assertEquals('Kevin', $friends[1]->getProperty('name'));

  }

  public function testAsList()
  {
    $backingData = array(
      'data' => array(
        array(
          'id' => 1,
          'name' => 'David'
        ),
        array(
          'id' => 2,
          'name' => 'Fosco'
        )
      )
    );
    $enc = json_encode($backingData);
    $response = new FacebookResponse(null, json_decode($enc), $enc);
    $list = $response->getGraphObjectList(GraphUser::className());
    $this->assertEquals(2, count($list));
    $this->assertTrue($list[0] instanceof GraphObject);
    $this->assertTrue($list[1] instanceof GraphObject);
    $this->assertEquals('David', $list[0]->getName());
    $this->assertEquals('Fosco', $list[1]->getName());
  }

}
