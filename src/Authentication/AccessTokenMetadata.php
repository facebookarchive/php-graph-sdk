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
namespace Facebook\Authentication;

use Facebook\Exception\SDKException;

/**
 * Represents metadata from an access token.
 *
 * @package Facebook
 *
 * @see     https://developers.facebook.com/docs/graph-api/reference/debug_token
 */
class AccessTokenMetadata
{
    /**
     * The access token metadata.
     *
     * @var array
     */
    protected $metadata = [];

    /**
     * Properties that should be cast as DateTime objects.
     *
     * @var array
     */
    protected static $dateProperties = ['expires_at', 'issued_at'];

    /**
     * @param array $metadata
     *
     * @throws SDKException
     */
    public function __construct(array $metadata)
    {
        if (!isset($metadata['data'])) {
            throw new SDKException('Unexpected debug token response data.', 401);
        }

        $this->metadata = $metadata['data'];

        $this->castTimestampsToDateTime();
    }

    /**
     * Returns a value from the metadata.
     *
     * @param string $field   the property to retrieve
     * @param mixed  $default the default to return if the property doesn't exist
     *
     * @return mixed
     */
    public function getField($field, $default = null)
    {
        if (isset($this->metadata[$field])) {
            return $this->metadata[$field];
        }

        return $default;
    }

    /**
     * Returns a value from a child property in the metadata.
     *
     * @param string $parentField the parent property
     * @param string $field       the property to retrieve
     * @param mixed  $default     the default to return if the property doesn't exist
     *
     * @return mixed
     */
    public function getChildProperty($parentField, $field, $default = null)
    {
        if (!isset($this->metadata[$parentField])) {
            return $default;
        }

        if (!isset($this->metadata[$parentField][$field])) {
            return $default;
        }

        return $this->metadata[$parentField][$field];
    }

    /**
     * Returns a value from the error metadata.
     *
     * @param string $field   the property to retrieve
     * @param mixed  $default the default to return if the property doesn't exist
     *
     * @return mixed
     */
    public function getErrorProperty($field, $default = null)
    {
        return $this->getChildProperty('error', $field, $default);
    }

    /**
     * Returns a value from the "metadata" metadata. *Brain explodes*.
     *
     * @param string $field   the property to retrieve
     * @param mixed  $default the default to return if the property doesn't exist
     *
     * @return mixed
     */
    public function getMetadataProperty($field, $default = null)
    {
        return $this->getChildProperty('metadata', $field, $default);
    }

    /**
     * The ID of the application this access token is for.
     *
     * @return null|string
     */
    public function getAppId()
    {
        return $this->getField('app_id');
    }

    /**
     * Name of the application this access token is for.
     *
     * @return null|string
     */
    public function getApplication()
    {
        return $this->getField('application');
    }

    /**
     * Any error that a request to the graph api
     * would return due to the access token.
     *
     * @return null|bool
     */
    public function isError()
    {
        return $this->getField('error') !== null;
    }

    /**
     * The error code for the error.
     *
     * @return null|int
     */
    public function getErrorCode()
    {
        return $this->getErrorProperty('code');
    }

    /**
     * The error message for the error.
     *
     * @return null|string
     */
    public function getErrorMessage()
    {
        return $this->getErrorProperty('message');
    }

    /**
     * The error subcode for the error.
     *
     * @return null|int
     */
    public function getErrorSubcode()
    {
        return $this->getErrorProperty('subcode');
    }

    /**
     * DateTime when this access token expires.
     *
     * @return null|\DateTime
     */
    public function getExpiresAt()
    {
        return $this->getField('expires_at');
    }

    /**
     * Whether the access token is still valid or not.
     *
     * @return null|bool
     */
    public function getIsValid()
    {
        return $this->getField('is_valid');
    }

    /**
     * DateTime when this access token was issued.
     *
     * Note that the issued_at field is not returned
     * for short-lived access tokens.
     *
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#debug
     *
     * @return null|\DateTime
     */
    public function getIssuedAt()
    {
        return $this->getField('issued_at');
    }

    /**
     * General metadata associated with the access token.
     * Can contain data like 'sso', 'auth_type', 'auth_nonce'.
     *
     * @return null|array
     */
    public function getMetadata()
    {
        return $this->getField('metadata');
    }

    /**
     * The 'sso' child property from the 'metadata' parent property.
     *
     * @return null|string
     */
    public function getSso()
    {
        return $this->getMetadataProperty('sso');
    }

    /**
     * The 'auth_type' child property from the 'metadata' parent property.
     *
     * @return null|string
     */
    public function getAuthType()
    {
        return $this->getMetadataProperty('auth_type');
    }

    /**
     * The 'auth_nonce' child property from the 'metadata' parent property.
     *
     * @return null|string
     */
    public function getAuthNonce()
    {
        return $this->getMetadataProperty('auth_nonce');
    }

    /**
     * For impersonated access tokens, the ID of
     * the page this token contains.
     *
     * @return null|string
     */
    public function getProfileId()
    {
        return $this->getField('profile_id');
    }

    /**
     * List of permissions that the user has granted for
     * the app in this access token.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->getField('scopes');
    }

    /**
     * The ID of the user this access token is for.
     *
     * @return null|string
     */
    public function getUserId()
    {
        return $this->getField('user_id');
    }

    /**
     * Ensures the app ID from the access token
     * metadata is what we expect.
     *
     * @param string $appId
     *
     * @throws SDKException
     */
    public function validateAppId($appId)
    {
        if ($this->getAppId() !== $appId) {
            throw new SDKException('Access token metadata contains unexpected app ID.', 401);
        }
    }

    /**
     * Ensures the user ID from the access token
     * metadata is what we expect.
     *
     * @param string $userId
     *
     * @throws SDKException
     */
    public function validateUserId($userId)
    {
        if ($this->getUserId() !== $userId) {
            throw new SDKException('Access token metadata contains unexpected user ID.', 401);
        }
    }

    /**
     * Ensures the access token has not expired yet.
     *
     * @throws SDKException
     */
    public function validateExpiration()
    {
        if (!$this->getExpiresAt() instanceof \DateTime) {
            return;
        }

        if ($this->getExpiresAt()->getTimestamp() < time()) {
            throw new SDKException('Inspection of access token metadata shows that the access token has expired.', 401);
        }
    }

    /**
     * Converts a unix timestamp into a DateTime entity.
     *
     * @param int $timestamp
     *
     * @return \DateTime
     */
    private function convertTimestampToDateTime($timestamp)
    {
        $dt = new \DateTime();
        $dt->setTimestamp($timestamp);

        return $dt;
    }

    /**
     * Casts the unix timestamps as DateTime entities.
     */
    private function castTimestampsToDateTime()
    {
        foreach (static::$dateProperties as $key) {
            if (isset($this->metadata[$key]) && $this->metadata[$key] !== 0) {
                $this->metadata[$key] = $this->convertTimestampToDateTime($this->metadata[$key]);
            }
        }
    }
}
