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
namespace Facebook\GraphNodes;

/**
 * Class GraphAlbum
 *
 * @package Facebook
 */
class GraphAlbum extends GraphNode
{
    /**
     * @var array Maps object key names to GraphNode types.
     */
    protected static $graphObjectMap = [
        'event' => '\Facebook\GraphNodes\GraphEvent',
        'place' => '\Facebook\GraphNodes\GraphPage',
        'picture' => '\Facebook\GraphNodes\GraphPicture',
    ];

    /**
     * The album ID
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getField('id');
    }

    /**
     * Whether the viewer can upload photos to this album
     *
     * @return boolean|null
     */
    public function getCanUpload()
    {
        return $this->getField('can_upload');
    }

    /**
     * Number of photos in this album
     *
     * @return int|null
     */
    public function getCount()
    {
        return $this->getField('count');
    }

    /**
     * Album cover photo id
     *
     * @return string|null
     */
    public function getCoverPhoto()
    {
        return $this->getField('cover_photo');
    }

    /**
     * The time the album was initially created
     *
     * @return \DateTime|null
     */
    public function getCreatedTime()
    {
        return $this->getField('created_time');
    }

    /**
     * The description of the album
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getField('description');
    }

    /**
     * The profile that created the album
     *
     * @return GraphNode|null
     */
    public function getFrom()
    {
        return $this->getField('from');
    }

    /**
     * A link to this album on Facebook
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->getField('link');
    }

    /**
     * The textual location of the album
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->getField('location');
    }

    /**
     * The title of the album
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getField('name');
    }

    /**
     * The privacy settings for the album
     *
     * @return string|null
     */
    public function getPrivacy()
    {
        return $this->getField('privacy');
    }

    /**
     * The type of the album: profile, mobile, wall, normal or album
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->getField('type');
    }

    /**
     * The last time the album was updated
     *
     * @return \DateTime|null
     */
    public function getUpdatedTime()
    {
        return $this->getField('updated_time');
    }

    /**
     * If this object has a place, the event associated with the place
     *
     * @return GraphEvent|null
     */
    public function getEvent()
    {
        return $this->getField('event');
    }

    /**
     * The place associated with this album
     *
     * @return GraphPage|null
     */
    public function getPlace()
    {
        return $this->getField('place');
    }

    /**
     * A user-specified time for when this object was created
     *
     * @return \DateTime|null
     */
    public function getBackdatedTime()
    {
        return $this->getField('backdated_time');
    }

    /**
     * How accurate the backdated time is
     *
     * @return string|null
     */
    public function getBackdatedTimeGranularity()
    {
        return $this->getField('backdated_time_granularity');
    }

    /**
     * The cover photo of this album.
     *
     * @return GraphPicture|null
     */
    public function getPicture()
    {
        return $this->getField('picture');
    }

}
