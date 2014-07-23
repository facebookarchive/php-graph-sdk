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
use Facebook\GraphNodes\GraphAlbum;
use Facebook\Tests\FacebookTestHelper;

class GraphAlbumTest extends \PHPUnit_Framework_TestCase
{

  const ALBUM_DESCRIPTION = "Album Description";
  const ALBUM_NAME = "Album Name";

  public function testMeReturnsGraphAlbum()
  {
    $response = (
    new FacebookRequest(
        FacebookTestHelper::$testSession,
        'POST',
        '/me/albums',
        array(
            'name' => self::ALBUM_NAME,
            'message' => self::ALBUM_DESCRIPTION,
            'value' => 'everyone'
        )
    ))->execute()->getGraphObject();

    $albumId = $response->getProperty('id');

    $response = (
    new FacebookRequest(
        FacebookTestHelper::$testSession,
        'GET',
        '/'.$albumId
    ))->execute()->getGraphObject(GraphAlbum::className());

    $this->assertTrue($response instanceof GraphAlbum);
    $this->assertEquals($albumId, $response->getId());
    $this->assertTrue($response->getFrom() instanceof \Facebook\GraphNodes\GraphUser);
    $this->assertTrue($response->canUpload());
    $this->assertEquals(0, $response->getCount());
    $this->assertEquals(self::ALBUM_NAME, $response->getName());
    $this->assertEquals(self::ALBUM_DESCRIPTION, $response->getDescription());
    $this->assertNotNull($response->getLink());
    $this->assertNotNull($response->getPrivacy());

    $type = array("profile", "mobile", "wall", "normal", "album");
    $this->assertTrue(in_array($response->getType(),$type));

    date_default_timezone_set('GMT');
    $this->assertTrue($response->getCreatedTime() instanceof \DateTime);
    $this->assertTrue($response->getUpdatedTime() instanceof \DateTime);
  }

}
