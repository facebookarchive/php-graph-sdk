<card>
# FacebookClient service class for the Facebook SDK for PHP

The `Facebook\FacebookClient` service class juggles the dependencies needed to make requests to the Graph API.
</card>

<card>
## Facebook\FacebookClient {#overview}

You most likely won't be working with the `Facebook\FacebookClient` service directly if you're using the `Facebook\Facebook` super service class, but if you have a highly customized environment, you might need to send requests with an instance of `Facebook\FacebookClient`.

You can grab an instance of a `Facebook\FacebookClient` service, from the `Facebook\Facebook` super service class.

~~~~
$fb = new Facebook\Facebook([/* */]);
$fbClient = $fb->getClient();
~~~~

Alternatively you could instantiate a new `Facebook\FacebookClient` service directly.

~~~~
$fbClient = new Facebook\FacebookClient($httpClientHandler, $enableBeta = false);
~~~~

The Graph API has a number of different base URL's based on what request you want to send. For example, if you wanted to send requests to the beta version of Graph, you'd need to send requests to [https://graph.beta.facebook.com](https://graph.beta.facebook.com) instead [https://graph.facebook.com](https://graph.facebook.com). And if you wanted to upload a video, that request would need to be sent to [https://graph-video.facebook.com](https://graph-video.facebook.com).

The `Facebook\FacebookClient` service takes the guess-work out of managing those base URL's by automatically sending your requests to the proper URL.
</card>

<card>
## Instance Methods {#instance-methods}

### getHttpClientHandler() {#get-http-client-handler}
~~~~
public Facebook\HttpClients\FacebookHttpClientInterface getHttpClientHandler()
~~~~
Returns the instance of [`Facebook\HttpClients\FacebookHttpClientInterface`](/docs/php/FacebookHttpClientInterface) that the service is using.
</card>

<card>
### setHttpClientHandler() {#set-http-client-handler}
~~~~
public setHttpClientHandler(Facebook\HttpClients\FacebookHttpClientInterface $client)
~~~~
If you've coded your own HTTP client to the [`Facebook\HttpClients\FacebookHttpClientInterface`](/docs/php/FacebookHttpClientInterface), you can inject it into the service using this method.
</card>

<card>
### enableBetaMode() {#enable-beta-mode}
~~~~
public enableBetaMode(boolean $enable = true)
~~~~
Tells the service to send requests to the beta URL's which include [https://graph.beta.facebook.com](https://graph.beta.facebook.com) and [https://graph-video.beta.facebook.com](https://graph-video.beta.facebook.com).
</card>

<card>
### sendRequest() {#send-request}
~~~~
public Facebook\FacebookResponse sendRequest(Facebook\FacebookRequest $request)
~~~~
Sends a non-batch request to Graph.

Takes a [`Facebook\FacebookRequest`](/docs/php/FacebookRequest) and sends it to the Graph API in the proper `application/x-www-form-urlencoded` or `multipart/form-data` encoded format.

Returns the response from Graph in the form of a [`Facebook\FacebookResponse`](/docs/php/FacebookResponse).

If there was an error processing the request before sending, a [`Facebook\Exceptions\FacebookSDKException`](/docs/php/FacebookSDKException) will be thrown.

If an error response from Graph was returned, a [`Facebook\Exceptions\FacebookResponseException`](/docs/php/FacebookResponseException) will be thrown.
</card>

<card>
### sendBatchRequest() {#send-batch-request}
~~~~
public Facebook\FacebookBatchResponse sendBatchRequest(Facebook\FacebookBatchRequest $batchRequest)
~~~~
Sends a batch request to Graph.

Takes a [`Facebook\FacebookBatchRequest`](/docs/php/FacebookBatchRequest) and sends it to the Graph API in the proper `application/x-www-form-urlencoded` or `multipart/form-data` encoded format.

Returns the response from Graph in the form of a [`Facebook\FacebookBatchResponse`](/docs/php/FacebookBatchResponse).

If there was an error processing the request before sending, a [`Facebook\Exceptions\FacebookSDKException`](/docs/php/FacebookSDKException) will be thrown.

If an error response from Graph was returned, a [`Facebook\Exceptions\FacebookResponseException`](/docs/php/FacebookResponseException) will be thrown.
</card>
