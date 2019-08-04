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

use Facebook\GraphNode\GraphAlbum;
use Facebook\GraphNode\GraphEdge;
use Facebook\GraphNode\GraphEvent;
use Facebook\GraphNode\GraphGroup;
use Facebook\GraphNode\GraphNode;
use Facebook\GraphNode\GraphNodeFactory;
use Facebook\Exception\ResponseException;
use Facebook\Exception\SDKException;
use Facebook\GraphNode\GraphPage;
use Facebook\GraphNode\GraphSessionInfo;
use Facebook\GraphNode\GraphUser;
use Facebook\Http\RequestBodyUrlEncoded;

/**
 * @package Facebook
 */
class Response
{
    /**
     * @var int the HTTP status code response from Graph
     */
    protected $httpStatusCode;

    /**
     * @var array the headers returned from Graph
     */
    protected $headers;

    /**
     * @var string the raw body of the response from Graph
     */
    protected $body;

    /**
     * @var array the decoded body of the Graph response
     */
    protected $decodedBody = [];

    /**
     * @var Request the original request that returned this response
     */
    protected $request;

    /**
     * @var SDKException the exception thrown by this request
     */
    protected $thrownException;

    /**
     * Creates a new Response entity.
     *
     * @param Request     $request
     * @param null|string $body
     * @param null|int    $httpStatusCode
     * @param array  $headers
     */
    public function __construct(
        Request $request,
        ?string $body = null,
        ?int $httpStatusCode = null,
        array $headers = []
    ) {
        $this->request = $request;
        $this->body = $body;
        $this->httpStatusCode = $httpStatusCode;
        $this->headers = $headers;

        $this->decodeBody();
    }

    /**
     * Return the original request that returned this response.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Return the Application entity used for this response.
     *
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->request->getApplication();
    }

    /**
     * Return the access token that was used for this response.
     *
     * @return null|string
     */
    public function getAccessToken(): ?string
    {
        return $this->request->getAccessToken();
    }

    /**
     * Return the HTTP status code for this response.
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * Return the HTTP headers for this response.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return the raw body response.
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Return the decoded body response.
     *
     * @return array
     */
    public function getDecodedBody(): array
    {
        return $this->decodedBody;
    }

    /**
     * Get the app secret proof that was used for this response.
     *
     * @return null|string
     */
    public function getAppSecretProof(): ?string
    {
        return $this->request->getAppSecretProof();
    }

    /**
     * Get the ETag associated with the response.
     *
     * @return null|string
     */
    public function getETag(): ?string
    {
        return $this->headers['ETag'] ?? null;
    }

    /**
     * Get the version of Graph that returned this response.
     *
     * @return null|string
     */
    public function getGraphVersion(): ?string
    {
        return $this->headers['Facebook-API-Version'] ?? null;
    }

    /**
     * Returns true if Graph returned an error message.
     *
     * @return bool
     */
    public function isError(): bool
    {
        return isset($this->decodedBody['error']);
    }

    /**
     * Throws the exception.
     *
     * @throws SDKException
     */
    public function throwException(): void
    {
        throw $this->thrownException;
    }

    /**
     * Instantiates an exception to be thrown later.
     */
    public function makeException(): void
    {
        $this->thrownException = ResponseException::create($this);
    }

    /**
     * Returns the exception that was thrown for this request.
     *
     * @return null|ResponseException
     */
    public function getThrownException(): ?ResponseException
    {
        return $this->thrownException;
    }

    /**
     * Convert the raw response into an array if possible.
     *
     * Graph will return 2 types of responses:
     * - JSON(P)
     *    Most responses from Graph are JSON(P)
     * - application/x-www-form-urlencoded key/value pairs
     *    Happens on the `/oauth/access_token` endpoint when exchanging
     *    a short-lived access token for a long-lived access token
     * - And sometimes nothing :/ but that'd be a bug.
     */
    public function decodeBody(): void
    {
        $this->decodedBody = json_decode($this->body, true);

        if ($this->decodedBody === null) {
            $this->decodedBody = [];
            parse_str($this->body, $this->decodedBody);
        } elseif (is_bool($this->decodedBody)) {
            // Backwards compatibility for Graph < 2.1.
            // Mimics 2.1 responses.
            // @TODO Remove this after Graph 2.0 is no longer supported
            $this->decodedBody = ['success' => $this->decodedBody];
        } elseif (is_numeric($this->decodedBody)) {
            $this->decodedBody = ['id' => $this->decodedBody];
        }

        if (!is_array($this->decodedBody)) {
            $this->decodedBody = [];
        }

        if ($this->isError()) {
            $this->makeException();
        }
    }

    /**
     * Instantiate a new GraphNode from response.
     *
     * @param null|string $subclassName the GraphNode subclass to cast to
     *
     * @throws SDKException
     *
     * @return \Facebook\GraphNode\GraphNode
     */
    public function getGraphNode(?string $subclassName = null): GraphNode
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphNode($subclassName);
    }

    /**
     * Convenience method for creating a GraphAlbum collection.
     *
     * @throws SDKException
     *
     * @return \Facebook\GraphNode\GraphAlbum
     */
    public function getGraphAlbum(): GraphAlbum
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphAlbum();
    }

    /**
     * Convenience method for creating a GraphPage collection.
     *
     * @throws SDKException
     *
     * @return \Facebook\GraphNode\GraphPage
     */
    public function getGraphPage(): GraphPage
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphPage();
    }

    /**
     * Convenience method for creating a GraphSessionInfo collection.
     *
     * @throws SDKException
     *
     * @return \Facebook\GraphNode\GraphSessionInfo
     */
    public function getGraphSessionInfo(): GraphSessionInfo
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphSessionInfo();
    }

    /**
     * Convenience method for creating a GraphUser collection.
     *
     * @throws SDKException
     *
     * @return \Facebook\GraphNode\GraphUser
     */
    public function getGraphUser(): GraphUser
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphUser();
    }

    /**
     * Convenience method for creating a GraphEvent collection.
     *
     * @throws SDKException
     *
     * @return \Facebook\GraphNode\GraphEvent
     */
    public function getGraphEvent(): GraphEvent
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphEvent();
    }

    /**
     * Convenience method for creating a GraphGroup collection.
     *
     * @throws SDKException
     *
     * @return \Facebook\GraphNode\GraphGroup
     */
    public function getGraphGroup(): GraphGroup
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphGroup();
    }

    /**
     * Instantiate a new GraphEdge from response.
     *
     * @param null|string $subclassName the GraphNode subclass to cast list items to
     * @param bool        $auto_prefix  toggle to auto-prefix the subclass name
     *
     * @throws SDKException
     *
     * @return \Facebook\GraphNode\GraphEdge
     */
    public function getGraphEdge(?string $subclassName = null, bool $auto_prefix = true): GraphEdge
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphEdge($subclassName, $auto_prefix);
    }
}
