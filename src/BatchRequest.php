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
namespace Facebook;

use ArrayIterator;
use IteratorAggregate;
use ArrayAccess;
use Facebook\Authentication\AccessToken;
use Facebook\Exception\SDKException;

/**
 * @package Facebook
 */
class BatchRequest extends Request implements IteratorAggregate, ArrayAccess
{
    /**
     * @var array an array of Request entities to send
     */
    protected $requests = [];

    /**
     * @var array an array of files to upload
     */
    protected $attachedFiles = [];

    /**
     * Creates a new Request entity.
     *
     * @param null|Application        $app
     * @param array                   $requests
     * @param null|AccessToken|string $accessToken
     * @param null|string             $graphVersion
     */
    public function __construct(Application $app = null, array $requests = [], $accessToken = null, $graphVersion = null)
    {
        parent::__construct($app, $accessToken, 'POST', '', [], null, $graphVersion);

        $this->add($requests);
    }

    /**
     * Adds a new request to the array.
     *
     * @param array|Request     $request
     * @param null|array|string $options Array of batch request options e.g. 'name', 'omit_response_on_success'.
     *                                   If a string is given, it is the value of the 'name' option.
     *
     * @throws \InvalidArgumentException
     *
     * @return BatchRequest
     * @return BatchRequest
     */
    public function add($request, $options = null)
    {
        if (is_array($request)) {
            foreach ($request as $key => $req) {
                $this->add($req, $key);
            }

            return $this;
        }

        if (!$request instanceof Request) {
            throw new \InvalidArgumentException('Argument for add() must be of type array or Request.');
        }

        if (null === $options) {
            $options = [];
        } elseif (!is_array($options)) {
            $options = ['name' => $options];
        }

        $this->addFallbackDefaults($request);

        // File uploads
        $attachedFiles = $this->extractFileAttachments($request);

        $name = $options['name'] ?? null;

        unset($options['name']);

        $requestToAdd = [
            'name' => $name,
            'request' => $request,
            'options' => $options,
            'attached_files' => $attachedFiles,
        ];

        $this->requests[] = $requestToAdd;

        return $this;
    }

    /**
     * Ensures that the Application and access token fall back when missing.
     *
     * @param Request $request
     *
     * @throws SDKException
     */
    public function addFallbackDefaults(Request $request)
    {
        if (!$request->getApplication()) {
            $app = $this->getApplication();
            if (!$app) {
                throw new SDKException('Missing Application on Request and no fallback detected on BatchRequest.');
            }
            $request->setApp($app);
        }

        if (!$request->getAccessToken()) {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                throw new SDKException('Missing access token on Request and no fallback detected on BatchRequest.');
            }
            $request->setAccessToken($accessToken);
        }
    }

    /**
     * Extracts the files from a request.
     *
     * @param Request $request
     *
     * @throws SDKException
     *
     * @return null|string
     */
    public function extractFileAttachments(Request $request)
    {
        if (!$request->containsFileUploads()) {
            return null;
        }

        $files = $request->getFiles();
        $fileNames = [];
        foreach ($files as $file) {
            $fileName = uniqid();
            $this->addFile($fileName, $file);
            $fileNames[] = $fileName;
        }

        $request->resetFiles();

        // @TODO Does Graph support multiple uploads on one endpoint?
        return implode(',', $fileNames);
    }

    /**
     * Return the Request entities.
     *
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Prepares the requests to be sent as a batch request.
     */
    public function prepareRequestsForBatch()
    {
        $this->validateBatchRequestCount();

        $params = [
            'batch' => $this->convertRequestsToJson(),
            'include_headers' => true,
        ];
        $this->setParams($params);
    }

    /**
     * Converts the requests into a JSON(P) string.
     *
     * @return string
     */
    public function convertRequestsToJson()
    {
        $requests = [];
        foreach ($this->requests as $request) {
            $options = [];

            if (null !== $request['name']) {
                $options['name'] = $request['name'];
            }

            $options += $request['options'];

            $requests[] = $this->requestEntityToBatchArray($request['request'], $options, $request['attached_files']);
        }

        return json_encode($requests);
    }

    /**
     * Validate the request count before sending them as a batch.
     *
     * @throws SDKException
     */
    public function validateBatchRequestCount()
    {
        $batchCount = count($this->requests);
        if ($batchCount === 0) {
            throw new SDKException('There are no batch requests to send.');
        } elseif ($batchCount > 50) {
            // Per: https://developers.facebook.com/docs/graph-api/making-multiple-requests#limits
            throw new SDKException('You cannot send more than 50 batch requests at a time.');
        }
    }

    /**
     * Converts a Request entity into an array that is batch-friendly.
     *
     * @param Request           $request       the request entity to convert
     * @param null|array|string $options       Array of batch request options e.g. 'name', 'omit_response_on_success'.
     *                                         If a string is given, it is the value of the 'name' option.
     * @param null|string       $attachedFiles names of files associated with the request
     *
     * @return array
     */
    public function requestEntityToBatchArray(Request $request, $options = null, $attachedFiles = null)
    {

        if (null === $options) {
            $options = [];
        } elseif (!is_array($options)) {
            $options = ['name' => $options];
        }

        $compiledHeaders = [];
        $headers = $request->getHeaders();
        foreach ($headers as $name => $value) {
            $compiledHeaders[] = $name . ': ' . $value;
        }

        $batch = [
            'headers' => $compiledHeaders,
            'method' => $request->getMethod(),
            'relative_url' => $request->getUrl(),
        ];

        // Since file uploads are moved to the root request of a batch request,
        // the child requests will always be URL-encoded.
        $body = $request->getUrlEncodedBody()->getBody();
        if ($body) {
            $batch['body'] = $body;
        }

        $batch += $options;

        if (null !== $attachedFiles) {
            $batch['attached_files'] = $attachedFiles;
        }

        return $batch;
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->requests);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->add($value, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->requests[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->requests[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->requests[$offset] ?? null;
    }
}
