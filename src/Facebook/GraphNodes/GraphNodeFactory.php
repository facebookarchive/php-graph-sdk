<?php

declare(strict_types=1);
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
 *
 */

namespace Facebook\GraphNodes;

use Facebook\Response;
use Facebook\Exceptions\FacebookSDKException;
use JetBrains\PhpStorm\Pure;

/**
 * Class GraphNodeFactory
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
    const BASE_GRAPH_OBJECT_PREFIX = '\Facebook\GraphNodes\\';

    /**
     * @var array The decoded body of the FacebookResponse entity from Graph.
     */
    protected array $decodedBody;

    /**
     * Init this Graph object.
     *
     * @param Response $response The response entity from Graph.
     */
    #[Pure] public function __construct(protected Response $response)
    {
        $this->decodedBody = $response->getDecodedBody();
    }

    /**
     * Tries to convert a FacebookResponse entity into a GraphNode.
     *
     * @param string|null $subclassName The GraphNode subclass to cast to.
     *
     * @return GraphNode
     *
     * @throws FacebookSDKException
     */
    public function makeGraphNode(string $subclassName = null): GraphNode
    {
        $this->validateResponseCastableAsGraphNode();

        return $this->castAsGraphNodeOrGraphEdge($this->decodedBody, $subclassName);
    }

    /**
     * Convenience method for creating a GraphAchievement collection.
     *
     * @return \Facebook\GraphNodes\GraphAchievement|\Facebook\GraphNodes\GraphNode
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function makeGraphAchievement(): GraphAchievement|GraphNode
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphAchievement');
    }

    /**
     * Convenience method for creating a GraphAlbum collection.
     *
     * @return \Facebook\GraphNodes\GraphAlbum|\Facebook\GraphNodes\GraphNode
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function makeGraphAlbum(): GraphAlbum|GraphNode
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphAlbum');
    }

    /**
     * Convenience method for creating a GraphPage collection.
     *
     * @return \Facebook\GraphNodes\GraphPage|\Facebook\GraphNodes\GraphNode
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function makeGraphPage(): GraphPage|GraphNode
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphPage');
    }

    /**
     * Convenience method for creating a GraphSessionInfo collection.
     *
     * @return \Facebook\GraphNodes\GraphSessionInfo|\Facebook\GraphNodes\GraphNode
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function makeGraphSessionInfo(): GraphSessionInfo|GraphNode
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphSessionInfo');
    }

    /**
     * Convenience method for creating a GraphUser collection.
     *
     * @return \Facebook\GraphNodes\GraphUser|\Facebook\GraphNodes\GraphNode
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function makeGraphUser(): GraphUser|GraphNode
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphUser');
    }

    /**
     * Convenience method for creating a GraphEvent collection.
     *
     * @return \Facebook\GraphNodes\GraphEvent|\Facebook\GraphNodes\GraphNode
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function makeGraphEvent(): GraphEvent|GraphNode
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphEvent');
    }

    /**
     * Convenience method for creating a GraphGroup collection.
     *
     * @return \Facebook\GraphNodes\GraphNode|\Facebook\GraphNodes\GraphGroup
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function makeGraphGroup(): GraphNode|GraphGroup
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphGroup');
    }

    /**
     * Tries to convert a FacebookResponse entity into a GraphEdge.
     *
     * @param string|null $subclassName The GraphNode subclass to cast the list items to.
     * @param boolean     $auto_prefix  Toggle to auto-prefix the subclass name.
     *
     * @return GraphEdge
     *
     * @throws FacebookSDKException
     */
    public function makeGraphEdge(string $subclassName = null, bool $auto_prefix = true): GraphEdge
    {
        $this->validateResponseCastableAsGraphEdge();

        if ($subclassName && $auto_prefix) {
            $subclassName = static::BASE_GRAPH_OBJECT_PREFIX . $subclassName;
        }

        return $this->castAsGraphNodeOrGraphEdge($this->decodedBody, $subclassName);
    }

    /**
     * Validates that the return data can be cast as a GraphNode.
     *
     * @throws FacebookSDKException
     */
    public function validateResponseCastableAsGraphNode()
    {
        if (isset($this->decodedBody['data']) && static::isCastableAsGraphEdge($this->decodedBody['data'])) {
            throw new FacebookSDKException(
                'Unable to convert response from Graph to a GraphNode because the response looks like a GraphEdge. Try using GraphNodeFactory::makeGraphEdge() instead.',
                620
            );
        }
    }

    /**
     * Validates that the return data can be cast as a GraphEdge.
     *
     * @throws FacebookSDKException
     */
    public function validateResponseCastableAsGraphEdge()
    {
        if (!(isset($this->decodedBody['data']) && static::isCastableAsGraphEdge($this->decodedBody['data']))) {
            throw new FacebookSDKException(
                'Unable to convert response from Graph to a GraphEdge because the response does not look like a GraphEdge. Try using GraphNodeFactory::makeGraphNode() instead.',
                620
            );
        }
    }

    /**
     * Safely instantiates a GraphNode of $subclassName.
     *
     * @param array       $data         The array of data to iterate over.
     * @param string|null $subclassName The subclass to cast this collection to.
     *
     * @return GraphNode
     *
     * @throws FacebookSDKException
     */
    public function safelyMakeGraphNode(array $data, string $subclassName = null): GraphNode
    {
        $subclassName = $subclassName ?: static::BASE_GRAPH_NODE_CLASS;
        static::validateSubclass($subclassName);

        // Remember the parent node ID
        $parentNodeId = $data['id'] ?? null;

        $items = [];

        foreach ($data as $k => $v) {
            // Array means could be recurable
            if (is_array($v)) {
                // Detect any smart-casting from the $graphObjectMap array.
                // This is always empty on the GraphNode collection, but subclasses can define
                // their own array of smart-casting types.
                $graphObjectMap = $subclassName::getNodeMap();
                $objectSubClass = $graphObjectMap[$k] ?? null;
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
     * @param array       $data         The array of data to iterate over.
     * @param string|null $subclassName The subclass to cast this collection to.
     * @param string|null $parentKey    The key of this data (Graph edge).
     * @param string|null $parentNodeId The parent Graph node ID.
     *
     * @return GraphNode|GraphEdge
     *
     * @throws FacebookSDKException
     */
    public function castAsGraphNodeOrGraphEdge(
        array  $data,
        string $subclassName = null,
        string $parentKey = null,
        string $parentNodeId = null,
    ): GraphEdge|GraphNode
    {
        if (isset($data['data'])) {
            // Create GraphEdge
            if (static::isCastableAsGraphEdge($data['data'])) {
                return $this->safelyMakeGraphEdge($data, $subclassName, $parentKey, $parentNodeId);
            }
            // Sometimes Graph is a weirdo and returns a GraphNode under the "data" key
            $outerData = $data;
            unset($outerData['data']);
            $data = $data['data'] + $outerData;
        }

        // Create GraphNode
        return $this->safelyMakeGraphNode($data, $subclassName);
    }

    /**
     * Return an array of GraphNode's.
     *
     * @param array       $data         The array of data to iterate over.
     * @param string|null $subclassName The GraphNode subclass to cast each item in the list to.
     * @param string|null $parentKey    The key of this data (Graph edge).
     * @param string|null $parentNodeId The parent Graph node ID.
     *
     * @return GraphEdge
     *
     * @throws FacebookSDKException
     */
    public function safelyMakeGraphEdge(
        array  $data,
        string $subclassName = null,
        string $parentKey = null,
        string $parentNodeId = null,
    ): GraphEdge
    {
        if (!isset($data['data'])) {
            throw new FacebookSDKException('Cannot cast data to GraphEdge. Expected a "data" key.', 620);
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
     * Get the metadata from a list in a Graph response.
     *
     * @param array $data The Graph response.
     *
     * @return array
     */
    public function getMetaData(array $data): array
    {
        unset($data['data']);

        return $data;
    }

    /**
     * Determines whether the data should be cast as a GraphEdge.
     *
     * @param array $data
     *
     * @return boolean
     */
    public static function isCastableAsGraphEdge(array $data): bool
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
     * @param string $subclassName The GraphNode subclass to validate.
     *
     * @throws FacebookSDKException
     */
    public static function validateSubclass(string $subclassName): void
    {
        if (
            $subclassName == static::BASE_GRAPH_NODE_CLASS
            || is_subclass_of($subclassName, static::BASE_GRAPH_NODE_CLASS)
        ) {
            return;
        }

        throw new FacebookSDKException('The given subclass "' . $subclassName . '" is not valid. Cannot cast to an object that is not a GraphNode subclass.', 620);
    }
}
