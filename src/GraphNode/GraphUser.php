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
class GraphUser extends GraphNode
{
    /**
     * @var array maps object key names to Graph object types
     */
    protected static $graphNodeMap = [
        'hometown' => GraphPage::class,
        'location' => GraphPage::class,
        'significant_other' => GraphUser::class,
        'picture' => GraphPicture::class,
    ];

    /**
     * Returns the ID for the user as a string if present.
     *
     * @return null|string
     */
    public function getId()
    {
        return $this->getField('id');
    }

    /**
     * Returns the name for the user as a string if present.
     *
     * @return null|string
     */
    public function getName()
    {
        return $this->getField('name');
    }

    /**
     * Returns the first name for the user as a string if present.
     *
     * @return null|string
     */
    public function getFirstName()
    {
        return $this->getField('first_name');
    }

    /**
     * Returns the middle name for the user as a string if present.
     *
     * @return null|string
     */
    public function getMiddleName()
    {
        return $this->getField('middle_name');
    }

    /**
     * Returns the last name for the user as a string if present.
     *
     * @return null|string
     */
    public function getLastName()
    {
        return $this->getField('last_name');
    }

    /**
     * Returns the email for the user as a string if present.
     *
     * @return null|string
     */
    public function getEmail()
    {
        return $this->getField('email');
    }

    /**
     * Returns the gender for the user as a string if present.
     *
     * @return null|string
     */
    public function getGender()
    {
        return $this->getField('gender');
    }

    /**
     * Returns the Facebook URL for the user as a string if available.
     *
     * @return null|string
     */
    public function getLink()
    {
        return $this->getField('link');
    }

    /**
     * Returns the users birthday, if available.
     *
     * @return null|Birthday
     */
    public function getBirthday()
    {
        return $this->getField('birthday');
    }

    /**
     * Returns the current location of the user as a GraphPage.
     *
     * @return null|GraphPage
     */
    public function getLocation()
    {
        return $this->getField('location');
    }

    /**
     * Returns the current location of the user as a GraphPage.
     *
     * @return null|GraphPage
     */
    public function getHometown()
    {
        return $this->getField('hometown');
    }

    /**
     * Returns the current location of the user as a GraphUser.
     *
     * @return null|GraphUser
     */
    public function getSignificantOther()
    {
        return $this->getField('significant_other');
    }

    /**
     * Returns the picture of the user as a GraphPicture.
     *
     * @return null|GraphPicture
     */
    public function getPicture()
    {
        return $this->getField('picture');
    }
}
