<card>
# Facebook\Helpers\FacebookCanvasHelper

The `FacebookCanvasHelper` is used to obtain an access token or signed request when working within the context of an [app canvas](https://developers.facebook.com/docs/games/canvas).

~~~
Facebook\Helpers\FacebookCanvasHelper( Facebook\FacebookApp $facebookApp )
~~~
</card>

<card>
## Usage {#usage}

If your app is loaded through Canvas, Facebook sends a POST request to your app with a signed request.  This helper will handle validating and decrypting the signed request.

~~~
$fb = new Facebook\Facebook([/* */]);
$canvasHelper = $fb->getCanvasHelper();
$signedRequest = $canvasHelper->getSignedRequest();

if ($signedRequest) {
  $payload = $signedRequest->getPayload();
  var_dump($payload);
}
~~~

If a user has already authenticated your app, you can also obtain an access token.

~~~
$fb = new Facebook\Facebook([/* */]);
$canvasHelper = $fb->getCanvasHelper();

try {
  $accessToken = $canvasHelper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
}

if (isset($accessToken)) {
  // Logged in.
}
~~~

The `$accessToken` will be `null` if the signed request did not contain any OAuth 2.0 data to obtain the access token.
</card>

<card>
## Instance Methods {#instance-methods}

### __construct() {#construct}
~~~~
public FacebookCanvasHelper __construct(FacebookApp $app, FacebookClient $client, $graphVersion = null)
~~~~
Upon instantiation, `FacebookCanvasHelper` validates and decrypts the signed request that was sent via POST if present.
</card>

<card>
### getAccessToken() {#get-access-token}
~~~
public Facebook\AccessToken|null getAccessToken()
~~~
Checks the signed request for authentication data and tries to obtain an access token access token.
</card>

<card>
### getUserId() {#get-user-id}
~~~
public string|null getUserId()
~~~
A convenience method for obtaining a user's ID from the signed request if present. This will only return the user's ID if a valid signed request can be obtained and decrypted and the user has already authorized the app.

~~~
$userId = $canvasHelper->getUserId();

if ($userId) {
  // User is logged in
}
~~~

This is equivalent to accessing the user ID from the signed request entity.

~~~
$signedRequest = $canvasHelper->getSignedRequest();

if ($signedRequest) {
  $userId = $signedRequest->getUserId();
  // OR
  $userId = $signedRequest->get('user_id');
}
~~~
</card>

<card>
### getAppData() {#get-app-data}
~~~
public string|null getAppData()
~~~
Gets the value that is set in the `app_data` property if present.
</card>

<card>
### getSignedRequest() {#get-signed-request}
~~~
public Facebook\SignedRequest|null getSignedRequest()
~~~
Returns the signed request as an instance of [`Facebook\SignedRequest`](/docs/php/SignedRequest) if present.
</card>

<card>
### getRawSignedRequest() {#get-raw-signed-request}
~~~
public string|null getRawSignedRequest()
~~~
Returns the raw encoded signed request as a `string` if present in the POST variables or `null`.
</card>
