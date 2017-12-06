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
class GraphPage extends GraphNode
{
    /**
     * @var array maps object key names to Graph object types
     */
    protected static $graphNodeMap = [
        'best_page' => GraphPage::class,
        'global_brand_parent_page' => GraphPage::class,
        'location' => GraphLocation::class,
        'cover' => GraphCoverPhoto::class,
        'picture' => GraphPicture::class,
    ];

    /**
     * Returns the ID for the user's page as a string if present.
     *
     * @return null|string
     */
    public function getId()
    {
        return $this->getField('id');
    }

    /**
     * Returns the Category for the user's page as a string if present.
     *
     * @return null|string
     */
    public function getCategory()
    {
        return $this->getField('category');
    }

    /**
     * Returns the Name of the user's page as a string if present.
     *
     * @return null|string
     */
    public function getName()
    {
        return $this->getField('name');
    }

    /**
     * Returns the best available Page on Facebook.
     *
     * @return null|GraphPage
     */
    public function getBestPage()
    {
        return $this->getField('best_page');
    }

    /**
     * Returns the brand's global (parent) Page.
     *
     * @return null|GraphPage
     */
    public function getGlobalBrandParentPage()
    {
        return $this->getField('global_brand_parent_page');
    }

    /**
     * Returns the location of this place.
     *
     * @return null|GraphLocation
     */
    public function getLocation()
    {
        return $this->getField('location');
    }

    /**
     * Returns CoverPhoto of the Page.
     *
     * @return null|GraphCoverPhoto
     */
    public function getCover()
    {
        return $this->getField('cover');
    }

    /**
     * Returns Picture of the Page.
     *
     * @return null|GraphPicture
     */
    public function getPicture()
    {
        return $this->getField('picture');
    }

    /**
     * Returns the page access token for the admin user.
     *
     * Only available in the `/me/accounts` context.
     *
     * @return null|string
     */
    public function getAccessToken()
    {
        return $this->getField('access_token');
    }

    /**
     * Returns the roles of the page admin user.
     *
     * Only available in the `/me/accounts` context.
     *
     * @return null|array
     */
    public function getPerms()
    {
        return $this->getField('perms');
    }
}
