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

use Facebook\Exceptions\FacebookSDKException;

/**
 * Class GraphObject
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 * @author David Poll <depoll@fb.com>
 */
class GraphObject extends Collection
{

  /**
   * @var array Maps object key names to Graph object types.
   */
  protected $graphObjectMap = [];

  /**
   * Init this Graph object.
   *
   * @param array $data
   */
  public function __construct(array $data = [])
  {
    $items = static::castGraphTypes($data, $this->graphObjectMap);
    parent::__construct($items);
  }

  /**
   * Takes a raw response from Graph and determines how to cast each node.
   *
   * @param array $data The backing of the GraphObject data.
   * @param string|null $graphObjectSubClass The sub class to cast.
   *
   * @return GraphObject
   */
  public static function make(array $data, $graphObjectSubClass = null)
  {
    if (isset($data['data'])) {
      if (static::isCastableAsGraphList($data['data'])) {
        return new GraphList($data);
      }
      return static::make($data['data']);
    }

    $graphObjectName = $graphObjectSubClass ?: static::className();
    return new $graphObjectName($data);
  }

  /**
   * Iterates over a Graph "data" array recursively and detects the types
   * each node should be cast to and returns all the items as an array.
   *
   * @param array $data
   * @param array $graphObjectMap
   *
   * @return array
   */
  public static function castGraphTypes(array $data, array $graphObjectMap = [])
  {
    $items = [];

    foreach ($data as $k => $v) {
      if (is_array($v)) {
        $objectSubClass = static::getObjectSubClass($k, $graphObjectMap);
        $items[$k] = static::make($v, $objectSubClass);
      } elseif (static::shouldCastAsDateTime($k)) {
        $items[$k] = static::castToDateTime($v);
      } else {
        $items[$k] = $v;
      }
    }

    return $items;
  }

  /**
   * Returns the name of the GraphObject sub class that a value
   * should be cast to.
   *
   * @param string $key
   * @param array $graphObjectMap
   *
   * @return string
   */
  private static function getObjectSubClass($key, array $graphObjectMap)
  {
    // We default to get_class() instead of get_called_class() so that all
    // unknown object types get cast as the generic "GraphObject".
    return isset($graphObjectMap[$key]) ? $graphObjectMap[$key] : get_class();
  }

  /**
   * Returns the string class name of the GraphObject or subclass.
   *
   * @return string
   */
  public static function className()
  {
    return get_called_class();
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
   * Determines if a value from Graph should be cast to DateTime.
   *
   * @param string $key
   *
   * @return boolean
   */
  public static function shouldCastAsDateTime($key)
  {
    return in_array($key, [
        'created_time',
        'updated_time',
        'start_time',
        'end_time',
        'backdated_time',
        'issued_at',
        'expires_at',
        'birthday',
      ], true);
  }

  /**
   * Casts a date value from Graph to DateTime.
   *
   * @param int|string $value
   *
   * @return \DateTime
   */
  public static function castToDateTime($value)
  {
    if (is_int($value)) {
      $dt = new \DateTime();
      $dt->setTimestamp($value);
    } else {
      $dt = new \DateTime($value);
    }
    return $dt;
  }

  /**
   * Gets the value of the named property for this graph object.
   *
   * @param string $name The property to retrieve.
   * @param mixed $default The default to return if the property doesn't exist.
   *
   * @return mixed
   */
  public function getProperty($name, $default = null)
  {
    if (isset($this->items[$name])) {
      return $this->items[$name];
    }
    return $default ?: null;
  }

  /**
   * Returns a list of all properties set on the object.
   *
   * @return array
   */
  public function getPropertyNames()
  {
    return array_keys($this->items);
  }

  /**
   * Return a new instance of a GraphObject that is cast as $type.
   *
   * @param string|null $type The GraphObject subclass to cast to.
   *
   * @return GraphObject
   *
   * @throws FacebookSDKException
   */
  public function cast($type = null)
  {
    $type = $type ?: get_class();

    if ($type == get_class() || is_subclass_of($type, get_class())) {
      return new $type($this->asStrictArray());
    }

    throw new FacebookSDKException(
      'Cannot cast to an object that is not a GraphObject subclass', 620
    );
  }

  /**
   * Convert a string to snake case.
   * Credit: https://github.com/laravel/framework/blob/397045ef988c77e2fb2988798b26e45775906de9/src/Illuminate/Support/Str.php
   *
   * @param  string  $value
   * @param  string  $delimiter
   * @return string
   */
  public static function snake($value, $delimiter = '_')
  {
    $replace = '$1'.$delimiter.'$2';

    return ctype_lower($value) ? $value : strtolower(preg_replace('/(.)([A-Z])/', $replace, $value));
  }

  /**
   * Magically get properties or cast objects.
   *
   * @param string $name The name of the method.
   * @param array $arguments The arguments sent to the method.
   *
   * @return mixed
   */
  public function __call($name, array $arguments)
  {
    // Dynamically call properties with getPropertyName('default');
    if (strpos($name, 'get') === 0) {
      $propertyName = preg_replace('/^get(.*)/', '$1', $name);
      $propertyName = static::snake($propertyName);
      $default = isset($arguments[0]) ? $arguments[0] : null;

      return call_user_func_array([$this, 'getProperty'], [$propertyName, $default]);
    }

    // Dynamically cast GraphObject types with castAsGraphUser();
    if (strpos($name, 'castAs') === 0) {
      $subClass = preg_replace('/^castAs(.*)/', '$1', $name);

      $prefix = isset($arguments[0]) && $arguments[0] === false
        ? ''
        : '\\Facebook\\GraphNodes\\';

      return call_user_func_array([$this, 'cast'], [$prefix.$subClass]);
    }
    return null;
  }

}
