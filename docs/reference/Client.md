# FacebookClient service class for the Facebook SDK for PHP

The `Facebook\Client` service class juggles the dependencies needed to make requests to the Graph API.

## Facebook\Client

You most likely won't be working with the `Facebook\Client` service directly if you're using the `Facebook\Facebook` super service class, but if you have a highly customized environment, you might need to send requests with an instance of `Facebook\Client`.

You can grab an instance of a `Facebook\Client` service, from the `Facebook\Facebook` super service class.

```php
$fb = new Facebook\Facebook([/* */]);
$fbClient = $fb->getClient();
```

Alternatively you could instantiate a new `Facebook\Client` service directly.

```php
$fbClient = new Facebook\Client($httpClientHandler, $enableBeta = false);
```

The Graph API has a number of different base URL's based on what request you want to send. For example, if you wanted to send requests to the beta version of Graph, you'd need to send requests to [https://graph.beta.facebook.com](https://graph.beta.facebook.com) instead [https://graph.facebook.com](https://graph.facebook.com). And if you wanted to upload a video, that request would need to be sent to [https://graph-video.facebook.com](https://graph-video.facebook.com).

The `Facebook\Client` service takes the guess-work out of managing those base URL's by automatically sending your requests to the proper URL.

## Instance Methods

### getHttpClientHandler()
```php
public Facebook\HttpClients\HttpClientInterface getHttpClientHandler()
```
Returns the instance of `Facebook\HttpClients\HttpClientInterface` that the service is using.

### setHttpClientHandler()
```php
public setHttpClientHandler(Facebook\HttpClients\HttpClientInterface $client)
```
If you've coded your own HTTP client to the `Facebook\HttpClients\HttpClientInterface`, you can inject it into the service using this method.

### enableBetaMode()
```php
public enableBetaMode(boolean $enable = true)
```
Tells the service to send requests to the beta URL's which include [https://graph.beta.facebook.com](https://graph.beta.facebook.com) and [https://graph-video.beta.facebook.com](https://graph-video.beta.facebook.com).

### sendRequest()
```php
public Facebook\Response sendRequest(Facebook\Request $request)
```
Sends a non-batch request to Graph.

Takes a [`Facebook\Request`](Request.md) and sends it to the Graph API in the proper `application/x-www-form-urlencoded` or `multipart/form-data` encoded format.

Returns the response from Graph in the form of a [`Facebook\Response`](Response.md).

If there was an error processing the request before sending, a [`Facebook\Exception\SDKException`](SDKException.md) will be thrown.

If an error response from Graph was returned, a [`Facebook\Exception\ResponseException`](ResponseException.md) will be thrown.

### sendBatchRequest()
```php
public Facebook\BatchResponse sendBatchRequest(Facebook\BatchRequest $batchRequest)
```
Sends a batch request to Graph.

Takes a [`Facebook\BatchRequest`](BatchRequest.md) and sends it to the Graph API in the proper `application/x-www-form-urlencoded` or `multipart/form-data` encoded format.

Returns the response from Graph in the form of a [`Facebook\BatchResponse`](BatchResponse.md).

If there was an error processing the request before sending, a [`Facebook\Exception\SDKException`](SDKException.md) will be thrown.

If an error response from Graph was returned, a [`Facebook\Exception\ResponseException`](ResponseException.md) will be thrown.
