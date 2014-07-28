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

use Facebook\GraphNodes\GraphAlbum;
use Facebook\GraphNodes\GraphUser;
use Facebook\GraphNodes\GraphPage;

class GraphAlbumTest extends \PHPUnit_Framework_TestCase
{
  public function testAlbum()
  {
    $createdTime = time()-60;
    $updatedTime = time();
    $data = [
      'id' => 1234,
      'can_upload' => true,
      'count' => 53,
      'cover_photo' => 'cover',
      'created_time' => date('c', $createdTime),
      'description' => 'Album Description',
      'from' => [],
      'link' => 'http://link',
      'location' => 'somewhere',
      'name' => 'Album Name',
      'place' => [],
      'privacy' => 'everybody',
      'type' => 'normal',
      'updated_time' => date('c', $updatedTime),
    ];
    $album = new GraphAlbum($data);

    $this->assertEquals(1234, $album->getId());
    $this->assertTrue($album->canUpload());
    $this->assertEquals(53, $album->getCount());
    $this->assertEquals('cover', $album->getCoverPhoto());
    $this->assertTrue($album->getCreatedTime() instanceof \DateTime);
    $this->assertEquals($createdTime, $album->getCreatedTime()->getTimestamp());
    $this->assertEquals('Album Description', $album->getDescription());
    $this->assertTrue($album->getFrom() instanceof GraphUser);
    $this->assertNotNull($album->getLink());
    $this->assertEquals('somewhere', $album->getLocation());
    $this->assertEquals('Album Name', $album->getName());
    $this->assertTrue($album->getPlace() instanceof GraphPage);
    $this->assertNotNull($album->getPrivacy());

    $type = array("profile", "mobile", "wall", "normal", "album");
    $this->assertTrue(in_array($album->getType(),$type));

    $this->assertTrue($album->getUpdatedTime() instanceof \DateTime);
    $this->assertEquals($updatedTime, $album->getUpdatedTime()->getTimestamp());
  }

}
