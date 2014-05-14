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
namespace Facebook;

/**
 * Class FacebookRequest
 * @package Facebook
 * @author Fosco Marotto <fjm@fb.com>
 * @author David Poll <depoll@fb.com>
 */
class FacebookRequest
{

  /**
   * @const string Version number of the Facebook PHP SDK.
   */
  const VERSION = '4.0.0';

  /**
   * @const string Default Graph API version for requests
   */
  const GRAPH_API_VERSION = 'v2.0';

  /**
   * @const string Signed Request Algorithm
   */
  const SIGNED_REQUEST_ALGORITHM = 'HMAC-SHA256';

  /**
   * @const string Graph API URL
   */
  const BASE_GRAPH_URL = 'https://graph.facebook.com';

  /**
   * @var FacebookSession The session used for this request
   */
  private $session;

  /**
   * @var string The HTTP method for the request
   */
  private $method;

  /**
   * @var string The path for the request
   */
  private $path;

  /**
   * @var array The parameters for the request
   */
  private $params;

  /**
   * @var string The Graph API version for the request
   */
  private $version;

  /**
   * @var string ETag sent with the request
   */
  private $etag;

  /**
   * getSession - Returns the associated FacebookSession.
   *
   * @return FacebookSession
   */
  public function getSession()
  {
    return $this->session;
  }

  /**
   * getPath - Returns the associated path.
   *
   * @return string
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * getParameters - Returns the associated parameters.
   *
   * @return array
   */
  public function getParameters()
  {
    return $this->params;
  }

  /**
   * getMethod - Returns the associated method.
   *
   * @return string
   */
  public function getMethod()
  {
    return $this->method;
  }

  /**
   * getETag - Returns the ETag sent with the request.
   *
   * @return string
   */
  public function getETag()
  {
    return $this->etag;
  }

  /**
   * FacebookRequest - Returns a new request using the given session.  optional
   *   parameters hash will be sent with the request.  This object is
   *   immutable.
   *
   * @param FacebookSession $session
   * @param string $method
   * @param string $path
   * @param array|null $parameters
   * @param string|null $version
   * @param string|null $etag
   */
  public function __construct(
    $session, $method, $path, $parameters = null, $version = null, $etag = null
  ) {
    $this->session = $session;
    $this->method = $method;
    $this->path = $path;
    if ($version) {
      $this->version = $version;
    } else {
      $this->version = static::GRAPH_API_VERSION;
    }
    $this->etag = $etag;

    $params = ($parameters ?: array());
    if ($session
      && !isset($params["access_token"])) {
      $params["access_token"] = $session->getToken();
    }
    $this->params = $params;
  }

  /**
   * Returns the base Graph URL.
   *
   * @return string
   */
  protected function getRequestURL() {
    return static::BASE_GRAPH_URL . '/' . $this->version . $this->path;
  }

  /**
   * execute - Makes the request to Facebook and returns the result.
   *
   * @return FacebookResponse
   *
   * @throws FacebookSDKException
   * @throws FacebookRequestException
   */
  public function execute() {
    $url = $this->getRequestURL();
    $params = $this->getParameters();
    $curl = curl_init();
    $options = array(
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => 60,
      CURLOPT_ENCODING       => '', // Support all available encodings.
      CURLOPT_USERAGENT      => 'fb-php-' . self::VERSION,
      CURLOPT_HEADER         => true // Enable header processing
    );
    if ($this->method === "GET") {
      $url = self::appendParamsToUrl($url, $params);
    } else {
      $options[CURLOPT_POSTFIELDS] = $params;
    }
    if ($this->method === 'DELETE' || $this->method === 'PUT') {
      $options[CURLOPT_CUSTOMREQUEST] = $this->method;
    }
    $options[CURLOPT_URL] = $url;

    // ETag
    if ($this->etag != null) {
      $options[CURLOPT_HTTPHEADER] = array('If-None-Match: '.$this->etag);
    }
    curl_setopt_array($curl, $options);

    $rawResult = curl_exec($curl);
    $error = curl_errno($curl);

    if ($error == 60 || $error == 77) {
      curl_setopt($curl, CURLOPT_CAINFO,
        dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fb_ca_chain_bundle.crt');
      $rawResult = curl_exec($curl);
      $error = curl_errno($curl);
    }

    // With dual stacked DNS responses, it's possible for a server to
    // have IPv6 enabled but not have IPv6 connectivity.  If this is
    // the case, curl will try IPv4 first and if that fails, then it will
    // fall back to IPv6 and the error EHOSTUNREACH is returned by the
    // operating system.
    if ($rawResult === false && empty($opts[CURLOPT_IPRESOLVE])) {
      $matches = array();
      $regex = '/Failed to connect to ([^:].*): Network is unreachable/';
      if (preg_match($regex, curl_error($curl), $matches)) {
        if (strlen(@inet_pton($matches[1])) === 16) {
          error_log(
            'Invalid IPv6 configuration on server, ' .
            'Please disable or get native IPv6 on your server.'
          );
          curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
          $rawResult = curl_exec($curl);
          $error = curl_errno($curl);
        }
      }
    }

    $errorMessage = curl_error($curl);
    $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    curl_close($curl);

    if ($rawResult === false) {
      throw new FacebookSDKException($errorMessage, $error);
    }

    $etagHit = 304 == $httpStatus;
    $headers = mb_substr($rawResult, 0, $headerSize);
    $result = mb_substr($rawResult, $headerSize);

    $etagReceived = null;
    if (($etagPos = strpos($headers, 'ETag: ')) !== FALSE) {
      $etagPos += strlen('ETag: ');
      $etagReceived = substr($headers, $etagPos,
                            strpos($headers, chr(10), $etagPos)-$etagPos-1);
    }

    $decodedResult = json_decode($result);
    if ($decodedResult === null) {
      $out = array();
      parse_str($result, $out);
      return new FacebookResponse($this, $out, $result, $etagHit, $etagReceived);
    }
    if (isset($decodedResult->error)) {
      throw FacebookRequestException::create(
        $result, $decodedResult->error, $httpStatus
      );
    }

    return new FacebookResponse($this, $decodedResult, $result, $etagHit, $etagReceived);
  }

  /**
   * appendParamsToUrl - Gracefully appends params to the URL.
   *
   * @param string $url
   * @param array $params
   *
   * @return string
   */
  public static function appendParamsToUrl($url, $params = array())
  {
    if (!$params) {
      return $url;
    }

    if (strpos($url, '?') === false) {
      return $url . '?' . http_build_query($params);
    }

    list($path, $query_string) = explode('?', $url, 2);
    parse_str($query_string, $query_array);

    // Favor params from the original URL over $params
    $params = array_merge($params, $query_array);

    return $path . '?' . http_build_query($params);
  }

}