<?php

use Facebook\FacebookRequest;
use Facebook\GraphAlbum;

class GraphAlbumTest extends PHPUnit_Framework_TestCase
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
    $this->assertTrue($response->getFrom() instanceof \Facebook\GraphUser);
    $this->assertTrue($response->canUpload());
    $this->assertEquals(0, $response->getCount());
    $this->assertEquals(self::ALBUM_NAME, $response->getName());
    $this->assertEquals(self::ALBUM_DESCRIPTION, $response->getDescription());
    $this->assertNotNull($response->getLink());
    $this->assertNotNull($response->getPrivacy());

    $type = array("profile", "mobile", "wall", "normal", "album");
    $this->assertTrue(in_array($response->getType(),$type));

    date_default_timezone_set('GMT');
    $this->assertTrue($response->getCreatedTime() instanceof DateTime);
    $this->assertTrue($response->getUpdatedTime() instanceof DateTime);
  }

}
