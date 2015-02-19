<card>
# SignedRequest entity for the Facebook SDK for PHP

The `Facebook\SignedRequest` entity represents a signed request.
</card>

<card>
## Facebook\SignedRequest {#overview}

[Signed requests](https://developers.facebook.com/docs/facebook-login/using-login-with-games#checklogin) contain payloads of data that can be validated against a hash signature to ensure it is from Facebook. The `Facebook\SignedRequest` entity can validate a signed request signature and decode the payload.

To instantiate a new `Facebook\SignedRequest` entity, pass the [`Facebook\FacebookApp`](/docs/php/FacebookApp) entity and raw signed request to the constructor.

~~~~
$fbApp = new Facebook\FacebookApp('{app-id}', '{app-secret}');
$signedRequest = new Facebook\SignedRequest($fbApp, 'raw.signed_request');
~~~~

Usually `Facebook\SignedRequest` entities are obtained using one of the [helpers](/docs/php/sdk_reference#helpers).

~~~~
$fb = new Facebook\Facebook([/* . . . */]);

// Obtain a signed request entity from the cookie set by the JavaScript SDK
$helper = $fb->getJavaScriptHelper();
$signedRequest = $helper->getSignedRequest();

// Obtain a signed request entity from a canvas app
$helper = $fb->getCanvasHelper();
$signedRequest = $helper->getSignedRequest();

// Obtain a signed request entity from a page tab
$helper = $fb->getPageTabHelper();
$signedRequest = $helper->getSignedRequest();
~~~~
</card>

<card>
## Instance Methods {#instance-methods}

### getRawSignedRequest() {#get-raw-signed-request}
~~~~
public string|null getRawSignedRequest()
~~~~
Returns the original raw encoded signed request in the form of a string.
</card>

<card>
### getPayload() {#get-payload}
~~~~
public array|null getPayload()
~~~~
Returns the [signed request payload](https://developers.facebook.com/docs/reference/login/signed-request/) in the form of an array.
</card>

<card>
### get() {#get}
~~~~
public string|null get(string $key, string|null $default)
~~~~
Returns a [field from the signed request payload](https://developers.facebook.com/docs/reference/login/signed-request) or `$default` if the value does not exist.
</card>

<card>
### getUserId() {#get-user-id}
~~~~
public string|null getUserId()
~~~~
Returns the `user_id` field from the signed request payload if it exists or `null` if it does not exists.
</card>

<card>
### hasOAuthData() {#has-oauth-data}
~~~~
public boolean hasOAuthData()
~~~~
Returns `true` if the payload data contains either an `oauth_token` or `code` field. Returns `false` if neither value exists.
</card>

<card>
### make() {#make}
~~~~
public string make(array $payload)
~~~~
Generates a valid raw signed request as a string that contains the data from the `$payload` array. The signature is signed using the app secret from the `Facebook\FacebookApp` entity. This can be useful for testing purposes.

~~~~
$fbApp = new Facebook\FacebookApp('{app-id}', '{app-secret}');
$signedRequest = new Facebook\SignedRequest($fbApp);

$payload = [
  'algorithm' => 'HMAC-SHA256',
  'issued_at' => time(),
  'foo' => 'bar',
  ];
$rawSignedRequest = $signedRequest->make($payload);

var_dump($rawSignedRequest);
// string(129) "c9RNpwW4vGYTGc7_E-_XQu5aoEQrWrx_KDOdz3x9Ec0=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQxODE4MjI1NSwiZm9vIjoiYmFyIn0="
~~~~
</card>
