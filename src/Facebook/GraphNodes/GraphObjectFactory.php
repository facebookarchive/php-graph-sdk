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
namespace Facebook\GraphNodes;

use Facebook\Entities\FacebookResponse;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class GraphObjectFactory
 *
 * @package Facebook
 *
 * ## Assumptions ##
 * GraphList - is ALWAYS a numeric array
 * GraphList - is ALWAYS an array of GraphObject types
 * GraphObject - is ALWAYS an associative array
 * GraphObject - MAY contain GraphObject's "recurrable"
 * GraphObject - MAY contain GraphList's "recurrable"
 * GraphObject - MAY contain DateTime's "primitives"
 * GraphObject - MAY contain string's "primitives"
 */
class GraphObjectFactory
{

  /**
   * @const string The base graph object class.
   */
  const BASE_GRAPH_OBJECT_CLASS = '\\Facebook\\GraphNodes\\GraphObject';

  /**
   * @const string The graph object prefix.
   */
  const BASE_GRAPH_OBJECT_PREFIX = '\\Facebook\\GraphNodes\\';

  /**
   * @var array The decoded body of the FacebookResponse entity from Graph.
   */
  protected $decodedBody;

  /**
   * Init this Graph object.
   *
   * @param FacebookResponse $response The response entity from Graph.
   */
  public function __construct(FacebookResponse $response)
  {
    $this->decodedBody = $response->getDecodedBody();
  }

  /**
   * Tries to convert a FacebookResponse entity into a GraphObject.
   *
   * @param string|null $subclassName The GraphObject sub class to cast to.
   *
   * @return GraphObject
   *
   * @throws FacebookSDKException
   */
  public function makeGraphObject($subclassName = null)
  {
    $this->validateResponseAsArray();
    $this->validateResponseCastableAsGraphObject();

    // Sometimes Graph is a weirdo and returns a GraphObject under the "data" key
    if (isset($this->decodedBody['data'])) {
      $this->decodedBody = $this->decodedBody['data'];
    }

    return $this->safelyMakeGraphObject($this->decodedBody, $subclassName);
  }

  /**
   * Convenience method for creating a GraphAlbum collection.
   *
   * @return GraphAlbum
   *
   * @throws FacebookSDKException
   */
  public function makeGraphAlbum()
  {
    return $this->makeGraphObject(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphAlbum');
  }

  /**
   * Convenience method for creating a GraphPage collection.
   *
   * @return GraphPage
   *
   * @throws FacebookSDKException
   */
  public function makeGraphPage()
  {
    return $this->makeGraphObject(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphPage');
  }

  /**
   * Convenience method for creating a GraphSessionInfo collection.
   *
   * @return GraphSessionInfo
   *
   * @throws FacebookSDKException
   */
  public function makeGraphSessionInfo()
  {
    return $this->makeGraphObject(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphSessionInfo');
  }

  /**
   * Convenience method for creating a GraphUser collection.
   *
   * @return GraphUser
   *
   * @throws FacebookSDKException
   */
  public function makeGraphUser()
  {
    return $this->makeGraphObject(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphUser');
  }

  /**
   * Tries to convert a FacebookResponse entity into a GraphList.
   *
   * @param string|null $subclassName The GraphObject sub class to cast the list items to.
   * @param boolean $auto_prefix Toggle to auto-prefix the subclass name.
   *
   * @return GraphList
   *
   * @throws FacebookSDKException
   */
  public function makeGraphList($subclassName = null, $auto_prefix = true)
  {
    $this->validateResponseAsArray();
    $this->validateResponseCastableAsGraphList();

    if ($subclassName && $auto_prefix) {
      $subclassName = static::BASE_GRAPH_OBJECT_PREFIX . $subclassName;
    }

    return $this->safelyMakeGraphList($this->decodedBody, $subclassName);
  }

  /**
   * Validates the decoded body.
   *
   * @throws FacebookSDKException
   */
  public function validateResponseAsArray()
  {
    if ( ! is_array($this->decodedBody)) {
      throw new FacebookSDKException(
        'Unable to get response from Graph as array.', 620
      );
    }
  }

  /**
   * Validates that the return data can be cast as a GraphObject.
   *
   * @throws FacebookSDKException
   */
  public function validateResponseCastableAsGraphObject()
  {
    if (isset($this->decodedBody['data'])
      && static::isCastableAsGraphList($this->decodedBody['data'])) {
      throw new FacebookSDKException(
        'Unable to convert response from Graph to a GraphObject ' .
        'because the response looks like a GraphList. ' .
        'Try using GraphObjectFactory::makeGraphList() instead.', 620
      );
    }
  }

  /**
   * Validates that the return data can be cast as a GraphList.
   *
   * @throws FacebookSDKException
   */
  public function validateResponseCastableAsGraphList()
  {
    if ( ! (isset($this->decodedBody['data'])
      && static::isCastableAsGraphList($this->decodedBody['data']) ) ) {
      throw new FacebookSDKException(
        'Unable to convert response from Graph to a GraphList ' .
        'because the response does not look like a GraphList. ' .
        'Try using GraphObjectFactory::makeGraphObject() instead.', 620
      );
    }
  }

  /**
   * Safely instantiates a GraphObject of $subclassName.
   *
   * @param array $data The array of data to iterate over.
   * @param string|null $subclassName The subclass to cast this collection to.
   *
   * @return GraphObject
   *
   * @throws FacebookSDKException
   */
  public function safelyMakeGraphObject(array $data, $subclassName = null)
  {
    $subclassName = $subclassName ?: static::BASE_GRAPH_OBJECT_CLASS;
    static::validateSubclass($subclassName);

    $items = [];

    foreach ($data as $k => $v) {
      // Array means could be recurable
      if (is_array($v)) {
        // Detect any smart-casting from the $graphObjectMap array.
        // This is always empty on the GraphObject collection, but subclasses can define
        // their own array of smart-casting types.
        $graphObjectMap = $subclassName::getObjectMap();
        $objectSubClass = isset($graphObjectMap[$k])
          ? $graphObjectMap[$k]
          : null;

        // Could be a GraphList or GraphObject
        $items[$k] = $this->castAsGraphObjectOrGraphList($v, $objectSubClass);
      } else {
        $items[$k] = $v;
      }
    }

    return new $subclassName($items);
  }

  /**
   * Takes an array of values and determines how to cast each node.
   *
   * @param array $data The array of data to iterate over.
   * @param string|null $subclassName The subclass to cast this collection to.
   *
   * @return GraphObject|GraphList
   *
   * @throws FacebookSDKException
   */
  public function castAsGraphObjectOrGraphList(array $data, $subclassName = null)
  {
    if (isset($data['data'])) {
      // Create GraphList
      if (static::isCastableAsGraphList($data['data'])) {
        return $this->safelyMakeGraphList($data, $subclassName);
      }
      // Sometimes Graph is a weirdo and returns a GraphObject under the "data" key
      $data = $data['data'];
    }

    // Create GraphObject
    return $this->safelyMakeGraphObject($data, $subclassName);
  }

  /**
   * Return an array of GraphObject's.
   *
   * @param array $data The array of data to iterate over.
   * @param string|null $subclassName The GraphObject subclass to cast each item in the list to.
   *
   * @return GraphList
   *
   * @throws FacebookSDKException
   */
  public function safelyMakeGraphList(array $data, $subclassName = null)
  {
    if ( ! isset($data['data'])) {
      throw new FacebookSDKException(
        'Cannot cast data to GraphList. Expected a "data" key.', 620
      );
    }

    $dataList = [];
    foreach ($data['data'] as $graphNode) {
      $dataList[] = $this->safelyMakeGraphObject($graphNode, $subclassName);
    }

    // @TODO: Look for meta data here
    $metaData = [];

    return new GraphList($dataList, $metaData);
  }

  /**
   * Determines whether or not the data should be cast as a GraphList.
   *
   * @param array $data
   *
   * @return boolean
   */
  public static function isCastableAsGraphList(array $data)
  {
    if ($data === []) {
      return true;
    }
    // Checks for a sequential numeric array which would be a GraphList
    return array_keys($data) === range(0, count($data) - 1);
  }

  /**
   * Ensures that the subclass in question is valid.
   *
   * @param string $subclassName The GraphObject subclass to validate.
   *
   * @throws FacebookSDKException
   */
  public static function validateSubclass($subclassName)
  {
    if ($subclassName == static::BASE_GRAPH_OBJECT_CLASS
      || is_subclass_of($subclassName, static::BASE_GRAPH_OBJECT_CLASS)) {
      return;
    }

    throw new FacebookSDKException(
      'The given subclass "' . $subclassName . '" is not valid. '
      . 'Cannot cast to an object that is not a GraphObject subclass.', 620
    );
  }

}
