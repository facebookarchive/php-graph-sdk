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

use Facebook\Request;
use Facebook\Url\UrlManipulator;
use Facebook\Exception\SDKException;

/**
 * @package Facebook
 */
class GraphEdge implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @var Request the original request that generated this data
     */
    protected $request;

    /**
     * @var array an array of Graph meta data like pagination, etc
     */
    protected $metaData = [];

    /**
     * @var null|string the parent Graph edge endpoint that generated the list
     */
    protected $parentEdgeEndpoint;

    /**
     * @var null|string the subclass of the child GraphNode's
     */
    protected $subclassName;

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Init this collection of GraphNode's.
     *
     * @param Request     $request            the original request that generated this data
     * @param array       $data               an array of GraphNode's
     * @param array       $metaData           an array of Graph meta data like pagination, etc
     * @param null|string $parentEdgeEndpoint the parent Graph edge endpoint that generated the list
     * @param null|string $subclassName       the subclass of the child GraphNode's
     */
    public function __construct(
        Request $request,
        array $data = [],
        array $metaData = [],
        ?string $parentEdgeEndpoint = null,
        ?string $subclassName = null
    ) {
        $this->request = $request;
        $this->metaData = $metaData;
        $this->parentEdgeEndpoint = $parentEdgeEndpoint;
        $this->subclassName = $subclassName;
        $this->items = $data;
    }

    /**
     * Gets the value of a field from the Graph node.
     *
     * @param string $name    the field to retrieve
     * @param mixed  $default the default to return if the field doesn't exist
     *
     * @return mixed
     */
    public function getField(string $name, $default = null)
    {
        if (isset($this->items[$name])) {
            return $this->items[$name];
        }

        return $default;
    }

    /**
     * Returns a list of all fields set on the object.
     *
     * @return array
     */
    public function getFieldNames(): array
    {
        return array_keys($this->items);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function asArray(): array
    {
        return array_map(function ($value) {
            if ($value instanceof GraphNode || $value instanceof GraphEdge) {
                return $value->asArray();
            }

            return $value;
        }, $this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function map(\Closure $callback)
    {
        return new static(
            $this->request,
            array_map($callback, $this->items, array_keys($this->items)),
            $this->metaData,
            $this->parentEdgeEndpoint,
            $this->subclassName
        );
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function asJson(int $options = 0): string
    {
        return json_encode($this->asArray(), $options);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param array-key $key
     *
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param array-key $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param array-key $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param array-key $key
     *
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->asJson();
    }

    /**
     * Gets the parent Graph edge endpoint that generated the list.
     *
     * @return null|string
     */
    public function getParentGraphEdge(): ?string
    {
        return $this->parentEdgeEndpoint;
    }

    /**
     * Gets the subclass name that the child GraphNode's are cast as.
     *
     * @return null|string
     */
    public function getSubClassName(): ?string
    {
        return $this->subclassName;
    }

    /**
     * Returns the raw meta data associated with this GraphEdge.
     *
     * @return array
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    /**
     * Returns the next cursor if it exists.
     *
     * @return null|string
     */
    public function getNextCursor(): ?string
    {
        return $this->getCursor('after');
    }

    /**
     * Returns the previous cursor if it exists.
     *
     * @return null|string
     */
    public function getPreviousCursor(): ?string
    {
        return $this->getCursor('before');
    }

    /**
     * Returns the cursor for a specific direction if it exists.
     *
     * @param string $direction The direction of the page: after|before
     *
     * @return null|string
     */
    public function getCursor(string $direction): ?string
    {
        if (isset($this->metaData['paging']['cursors'][$direction])) {
            return $this->metaData['paging']['cursors'][$direction];
        }

        return null;
    }

    /**
     * Generates a pagination URL based on a cursor.
     *
     * @param string $direction The direction of the page: next|previous
     *
     * @throws SDKException
     *
     * @return null|string
     */
    public function getPaginationUrl(string $direction): ?string
    {
        $this->validateForPagination();

        // Do we have a paging URL?
        if (!isset($this->metaData['paging'][$direction])) {
            return null;
        }

        $pageUrl = $this->metaData['paging'][$direction];

        return UrlManipulator::baseGraphUrlEndpoint($pageUrl);
    }

    /**
     * Validates whether or not we can paginate on this request.
     *
     * @throws SDKException
     */
    public function validateForPagination(): void
    {
        if ($this->request->getMethod() !== 'GET') {
            throw new SDKException('You can only paginate on a GET request.', 720);
        }
    }

    /**
     * Gets the request object needed to make a next|previous page request.
     *
     * @param string $direction The direction of the page: next|previous
     *
     * @throws SDKException
     *
     * @return null|Request
     */
    public function getPaginationRequest(string $direction): ?Request
    {
        $pageUrl = $this->getPaginationUrl($direction);
        if (!$pageUrl) {
            return null;
        }

        $newRequest = clone $this->request;
        $newRequest->setEndpoint($pageUrl);

        return $newRequest;
    }

    /**
     * Gets the request object needed to make a "next" page request.
     *
     * @throws SDKException
     *
     * @return null|Request
     */
    public function getNextPageRequest(): ?Request
    {
        return $this->getPaginationRequest('next');
    }

    /**
     * Gets the request object needed to make a "previous" page request.
     *
     * @throws SDKException
     *
     * @return null|Request
     */
    public function getPreviousPageRequest(): ?Request
    {
        return $this->getPaginationRequest('previous');
    }

    /**
     * The total number of results according to Graph if it exists.
     *
     * This will be returned if the summary=true modifier is present in the request.
     *
     * @return null|int
     */
    public function getTotalCount(): ?int
    {
        if (isset($this->metaData['summary']['total_count'])) {
            return $this->metaData['summary']['total_count'];
        }

        return null;
    }
}
