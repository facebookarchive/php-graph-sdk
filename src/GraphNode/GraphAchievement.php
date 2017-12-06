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
namespace Facebook\GraphNode;

/**
 * @package Facebook
 */
class GraphAchievement extends GraphNode
{
    /**
     * @var array maps object key names to Graph object types
     */
    protected static $graphNodeMap = [
        'from' => GraphUser::class,
        'application' => GraphApplication::class,
    ];

    /**
     * Returns the ID for the achievement.
     *
     * @return null|string
     */
    public function getId()
    {
        return $this->getField('id');
    }

    /**
     * Returns the user who achieved this.
     *
     * @return null|GraphUser
     */
    public function getFrom()
    {
        return $this->getField('from');
    }

    /**
     * Returns the time at which this was achieved.
     *
     * @return null|\DateTime
     */
    public function getPublishTime()
    {
        return $this->getField('publish_time');
    }

    /**
     * Returns the app in which the user achieved this.
     *
     * @return null|GraphApplication
     */
    public function getApplication()
    {
        return $this->getField('application');
    }

    /**
     * Returns information about the achievement type this instance is connected with.
     *
     * @return null|array
     */
    public function getData()
    {
        return $this->getField('data');
    }

    /**
     * Returns the type of achievement.
     *
     * @see https://developers.facebook.com/docs/graph-api/reference/achievement
     *
     * @return string
     */
    public function getType()
    {
        return 'game.achievement';
    }

    /**
     * Indicates whether gaining the achievement published a feed story for the user.
     *
     * @return null|bool
     */
    public function isNoFeedStory()
    {
        return $this->getField('no_feed_story');
    }
}
