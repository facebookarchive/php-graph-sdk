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
namespace Facebook\FileUpload;

/**
 * Class FacebookTransferChunk
 *
 * @package Facebook
 */
class FacebookTransferChunk extends FacebookVideo
{
    /**
     * @var int ID of the upload session
     */
    protected $uploadSessionId;

    /**
     * @var int Start byte position of the next file chunk
     */
    protected $startOffset;

    /**
     * @var int The maximum bytes to read
     */
    protected $maxLen = -1;

    /**
     * @var int ID of the video
     */
    protected $videoId;

    /**
     * @param string $filePath
     * @param int $uploadSessionId
     * @param int $videoId
     * @param int $startOffset
     * @param int $maxLen
     */
    public function __construct($filePath, $uploadSessionId, $videoId, $startOffset, $maxLen)
    {
        parent::__construct($filePath);
        $this->uploadSessionId = $uploadSessionId;
        $this->startOffset = $startOffset;
        $this->maxLen = $maxLen;
        $this->videoId = $videoId;
    }

    /**
     * Return the contents of the file.
     *
     * @return string
     */
    public function getContents()
    {
        return stream_get_contents($this->stream, $this->maxLen, $this->startOffset);
    }

    /**
     * Return upload session Id
     *
     * @return int
     */
    public function getUploadSessionId()
    {
        return $this->uploadSessionId;
    }

    /**
     * Check whether is the last chunk
     *
     * @return bool
     */
    public function isLastChunk()
    {
        return $this->maxLen === 0;
    }

    /**
     * @return int
     */
    public function getStartOffset()
    {
        return $this->startOffset;
    }

    /**
     * Get uploaded video Id
     *
     * @return int
     */
    public function getVideoId()
    {
        return $this->videoId;
    }
}
