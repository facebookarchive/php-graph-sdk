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

use Facebook\Response;
use Facebook\Exception\SDKException;

/**
 * Class GraphNodeFactory.
 *
 * @package Facebook
 *
 * ## Assumptions ##
 * GraphEdge - is ALWAYS a numeric array
 * GraphEdge - is ALWAYS an array of GraphNode types
 * GraphNode - is ALWAYS an associative array
 * GraphNode - MAY contain GraphNode's "recurrable"
 * GraphNode - MAY contain GraphEdge's "recurrable"
 * GraphNode - MAY contain DateTime's "primitives"
 * GraphNode - MAY contain string's "primitives"
 */
class GraphNodeFactory
{
    /**
     * @const string The base graph object class.
     */
    const BASE_GRAPH_NODE_CLASS = GraphNode::class;

    /**
     * @const string The base graph edge class.
     */
    const BASE_GRAPH_EDGE_CLASS = GraphEdge::class;

    /**
     * @const string The graph object prefix.
     */
    const BASE_GRAPH_OBJECT_PREFIX = '\Facebook\GraphNode\\';

    /**
     * @var Response the response entity from Graph
     */
    protected $response;

    /**
     * @var array the decoded body of the Response entity from Graph
     */
    protected $decodedBody;

    /**
     * Init this Graph object.
     *
     * @param Response $response the response entity from Graph
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
        $this->decodedBody = $response->getDecodedBody();
    }

    /**
     * Tries to convert a Response entity into a GraphNode.
     *
     * @param null|string $subclassName the GraphNode sub class to cast to
     *
     * @throws SDKException
     *
     * @return GraphNode
     */
    public function makeGraphNode($subclassName = null)
    {
        $this->validateResponseAsArray();
        $this->validateResponseCastableAsGraphNode();

        return $this->castAsGraphNodeOrGraphEdge($this->decodedBody, $subclassName);
    }

    /**
     * Convenience method for creating a GraphAchievement collection.
     *
     * @throws SDKException
     *
     * @return GraphAchievement
     */
    public function makeGraphAchievement()
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphAchievement');
    }

    /**
     * Convenience method for creating a GraphAlbum collection.
     *
     * @throws SDKException
     *
     * @return GraphAlbum
     */
    public function makeGraphAlbum()
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphAlbum');
    }

    /**
     * Convenience method for creating a GraphPage collection.
     *
     * @throws SDKException
     *
     * @return GraphPage
     */
    public function makeGraphPage()
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphPage');
    }

    /**
     * Convenience method for creating a GraphSessionInfo collection.
     *
     * @throws SDKException
     *
     * @return GraphSessionInfo
     */
    public function makeGraphSessionInfo()
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphSessionInfo');
    }

    /**
     * Convenience method for creating a GraphUser collection.
     *
     * @throws SDKException
     *
     * @return GraphUser
     */
    public function makeGraphUser()
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphUser');
    }

    /**
     * Convenience method for creating a GraphEvent collection.
     *
     * @throws SDKException
     *
     * @return GraphEvent
     */
    public function makeGraphEvent()
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphEvent');
    }

    /**
     * Convenience method for creating a GraphGroup collection.
     *
     * @throws SDKException
     *
     * @return GraphGroup
     */
    public function makeGraphGroup()
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphGroup');
    }

    /**
     * Tries to convert a Response entity into a GraphEdge.
     *
     * @param null|string $subclassName the GraphNode sub class to cast the list items to
     * @param bool        $auto_prefix  toggle to auto-prefix the subclass name
     *
     * @throws SDKException
     *
     * @return GraphEdge
     */
    public function makeGraphEdge($subclassName = null, $auto_prefix = true)
    {
        $this->validateResponseAsArray();
        $this->validateResponseCastableAsGraphEdge();

        if ($subclassName && $auto_prefix) {
            $subclassName = static::BASE_GRAPH_OBJECT_PREFIX . $subclassName;
        }

        return $this->castAsGraphNodeOrGraphEdge($this->decodedBody, $subclassName);
    }

    /**
     * Validates the decoded body.
     *
     * @throws SDKException
     */
    public function validateResponseAsArray()
    {
        if (!is_array($this->decodedBody)) {
            throw new SDKException('Unable to get response from Graph as array.', 620);
        }
    }

    /**
     * Validates that the return data can be cast as a GraphNode.
     *
     * @throws SDKException
     */
    public function validateResponseCastableAsGraphNode()
    {
        if (isset($this->decodedBody['data']) && static::isCastableAsGraphEdge($this->decodedBody['data'])) {
            throw new SDKException(
                'Unable to convert response from Graph to a GraphNode because the response looks like a GraphEdge. Try using GraphNodeFactory::makeGraphEdge() instead.',
                620
            );
        }
    }

    /**
     * Validates that the return data can be cast as a GraphEdge.
     *
     * @throws SDKException
     */
    public function validateResponseCastableAsGraphEdge()
    {
        if (!(isset($this->decodedBody['data']) && static::isCastableAsGraphEdge($this->decodedBody['data']))) {
            throw new SDKException(
                'Unable to convert response from Graph to a GraphEdge because the response does not look like a GraphEdge. Try using GraphNodeFactory::makeGraphNode() instead.',
                620
            );
        }
    }

    /**
     * Safely instantiates a GraphNode of $subclassName.
     *
     * @param array       $data         the array of data to iterate over
     * @param null|string $subclassName the subclass to cast this collection to
     *
     * @throws SDKException
     *
     * @return GraphNode
     */
    public function safelyMakeGraphNode(array $data, $subclassName = null)
    {
        $subclassName = $subclassName ?: static::BASE_GRAPH_NODE_CLASS;
        static::validateSubclass($subclassName);

        // Remember the parent node ID
        $parentNodeId = $data['id'] ?? null;

        $items = [];

        foreach ($data as $k => $v) {
            // Array means could be recurable
            if (is_array($v)) {
                // Detect any smart-casting from the $graphNodeMap array.
                // This is always empty on the GraphNode collection, but subclasses can define
                // their own array of smart-casting types.
                $graphNodeMap = $subclassName::getNodeMap();
                $objectSubClass = $graphNodeMap[$k] ?? null;

                // Could be a GraphEdge or GraphNode
                $items[$k] = $this->castAsGraphNodeOrGraphEdge($v, $objectSubClass, $k, $parentNodeId);
            } else {
                $items[$k] = $v;
            }
        }

        return new $subclassName($items);
    }

    /**
     * Takes an array of values and determines how to cast each node.
     *
     * @param array       $data         the array of data to iterate over
     * @param null|string $subclassName the subclass to cast this collection to
     * @param null|string $parentKey    the key of this data (Graph edge)
     * @param null|string $parentNodeId the parent Graph node ID
     *
     * @throws SDKException
     *
     * @return GraphEdge|GraphNode
     */
    public function castAsGraphNodeOrGraphEdge(array $data, $subclassName = null, $parentKey = null, $parentNodeId = null)
    {
        if (isset($data['data'])) {
            // Create GraphEdge
            if (static::isCastableAsGraphEdge($data['data'])) {
                return $this->safelyMakeGraphEdge($data, $subclassName, $parentKey, $parentNodeId);
            }
            // Sometimes Graph is a weirdo and returns a GraphNode under the "data" key
            $data = $data['data'];
        }

        // Create GraphNode
        return $this->safelyMakeGraphNode($data, $subclassName);
    }

    /**
     * Return an array of GraphNode's.
     *
     * @param array       $data         the array of data to iterate over
     * @param null|string $subclassName the GraphNode subclass to cast each item in the list to
     * @param null|string $parentKey    the key of this data (Graph edge)
     * @param null|string $parentNodeId the parent Graph node ID
     *
     * @throws SDKException
     *
     * @return GraphEdge
     */
    public function safelyMakeGraphEdge(array $data, $subclassName = null, $parentKey = null, $parentNodeId = null)
    {
        if (!isset($data['data'])) {
            throw new SDKException('Cannot cast data to GraphEdge. Expected a "data" key.', 620);
        }

        $dataList = [];
        foreach ($data['data'] as $graphNode) {
            $dataList[] = $this->safelyMakeGraphNode($graphNode, $subclassName);
        }

        $metaData = $this->getMetaData($data);

        // We'll need to make an edge endpoint for this in case it's a GraphEdge (for cursor pagination)
        $parentGraphEdgeEndpoint = $parentNodeId && $parentKey ? '/' . $parentNodeId . '/' . $parentKey : null;
        $className = static::BASE_GRAPH_EDGE_CLASS;

        return new $className($this->response->getRequest(), $dataList, $metaData, $parentGraphEdgeEndpoint, $subclassName);
    }

    /**
     * Get the meta data from a list in a Graph response.
     *
     * @param array $data the Graph response
     *
     * @return array
     */
    public function getMetaData(array $data)
    {
        unset($data['data']);

        return $data;
    }

    /**
     * Determines whether or not the data should be cast as a GraphEdge.
     *
     * @param array $data
     *
     * @return bool
     */
    public static function isCastableAsGraphEdge(array $data)
    {
        if ($data === []) {
            return true;
        }

        // Checks for a sequential numeric array which would be a GraphEdge
        return array_keys($data) === range(0, count($data) - 1);
    }

    /**
     * Ensures that the subclass in question is valid.
     *
     * @param string $subclassName the GraphNode subclass to validate
     *
     * @throws SDKException
     */
    public static function validateSubclass($subclassName)
    {
        if ($subclassName == static::BASE_GRAPH_NODE_CLASS || is_subclass_of($subclassName, static::BASE_GRAPH_NODE_CLASS)) {
            return;
        }

        throw new SDKException('The given subclass "' . $subclassName . '" is not valid. Cannot cast to an object that is not a GraphNode subclass.', 620);
    }
}
