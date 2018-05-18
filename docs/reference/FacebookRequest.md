# FacebookRequest for the Facebook SDK for PHP

Represents a request that will be sent to the Graph API.

## Facebook\FacebookRequest

You can instantiate a new `FacebookRequest` entity directly by sending the arguments to the constructor.

```php
use Facebook\FacebookRequest;

$request = new FacebookRequest(
  Facebook\FacebookApp $app,
  string $accessToken,
  string $method,
  string $endpoint,
  array $params,
  string $eTag,
  string $graphVersion
);
```

Alternatively, you can make use of the [`request()` factory provided by `Facebook\Facebook`](Facebook.md#request) to create new `FacebookRequest` instances.

The `FacebookRequest` entity does not actually make any calls to the Graph API, but instead just represents a request that can be sent to the Graph API later. This is most useful for making batch requests using [`Facebook\Facebook::sendBatchRequest()`](Facebook.md#sendbatchrequest) or [`Facebook\FacebookClient::sendBatchRequest()`](FacebookClient.md#sendbatchrequest).

Usage:

```php
$fbApp = new Facebook\FacebookApp('{app-id}', '{app-secret}');
$request = new Facebook\FacebookRequest($fbApp, '{access-token}', 'GET', '/me');

// OR

$fb = new Facebook\Facebook(/* . . . */);
$request = $fb->request('GET', '/me');

// Send the request to Graph
try {
  $response = $fb->getClient()->sendRequest($request);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$graphNode = $response->getGraphNode();

echo 'User name: ' . $graphNode['name'];
```

## Instance Methods

### setAccessToken()
```php
public setAccessToken(string|Facebook\AccessToken $accessToken)
```
Sets the access token to be used for the request.

### getAccessToken()
```php
public string getAccessToken()
```
Returns the access token to be used for the request in the form of a string.

### setApp()
```php
public setApp(Facebook\FacebookApp $app)
```
Sets the [`Facebook\FacebookApp`](FacebookApp.md) entity used with this request.

### getApp()
```php
public Facebook\FacebookApp getApp()
```
Returns the [`Facebook\FacebookApp`](FacebookApp.md) entity used with this request.

### getAppSecretProof()
```php
public string getAppSecretProof()
```
Returns the [app secret proof](https://developers.facebook.com/docs/graph-api/securing-requests/#appsecret_proof) to sign the request.

### setMethod()
```php
public setMethod(string $method)
```
Sets the HTTP verb to use for the request.

### getMethod()
```php
public string setMethod()
```
Returns the HTTP verb to use for the request.

### setEndpoint()
```php
public setEndpoint(string $endpoint)
```
Sets the Graph URL endpoint to be used with the request. The endpoint must be excluding the host name and Graph version number prefix.

```php
$request->setEndpoint('/me');
```

### getEndpoint()
```php
public string getEndpoint()
```
Returns the Graph URL endpoint to be used with the request.

### setHeaders()
```php
public setHeaders(array $headers)
```
Sets additional request headers to be use with the request. The supplied headers will be merged with the existing headers. The headers should be sent as an associative array with the key being the header name and the value being the header value.

```php
$request->setHeaders([
  'X-foo-header' => 'Something',
]);
```

### getHeaders()
```php
public array getHeaders()
```
Returns the request headers that will be sent with the request. The eTag headers `If-None-Match` are appended automatically.

### setETag()
```php
public setETag(string $eTag)
```
Sets the eTag that will be using for matching the `If-None-Match` header.

### setParams()
```php
public setParams(array $params)
```
For `GET` requests, the array of params will be converted to a query string and appended to the URL.

```php
$request->setParams([
  'foo' => 'bar',
  'limit' => 10,
]);
// /endpoint?foo=bar&limit=10
```

For `POST` requests, the array of params will be sent in the `POST` body encoded as `application/x-www-form-urlencoded` for most request. If the request includes a file upload the params will be encoded as `multipart/form-data`.

### getParams()
```php
public array getParams()
```
Returns an array of params to be sent with the request. The `access_token` and `appsecret_proof` params will be automatically appended to the array of params.

### getGraphVersion()
```php
public string getGraphVersion()
```
Returns the Graph version prefix to be used with the request.

### getUrl()
```php
public string getUrl()
```
Returns the endpoint of the Graph URL for the request. This will include the Graph version prefix but will not include the host name. The host name is determined after the request is sent to [`Facebook\FacebookClient`](FacebookClient.md).

```php
$fb = new Facebook\Facebook(/* . . . */);
$request = $fb->request('GET', '/me', ['fields' => 'id,name']);

$url = $request->getUrl();
// /v2.10/me?fields=id,name&access_token=token&appsecret_proof=proof
```
