# Facebook\Helpers\FacebookJavaScriptHelper

If you're using the [JavaScript SDK](https://developers.facebook.com/docs/javascript) on your site, information on the logged in user is stored in a cookie. Use the `FacebookJavaScriptHelper` to obtain an access token or signed request from the cookie.

## Usage

This helper will handle validating and decode the signed request from the cookie set by the JavaScript SDK.

```php
$fb = new Facebook\Facebook([/* */]);
$jsHelper = $fb->getJavaScriptHelper();
$signedRequest = $jsHelper->getSignedRequest();

if ($signedRequest) {
  $payload = $signedRequest->getPayload();
  var_dump($payload);
}
```

If a user has already authenticated your app, you can also obtain an access token.

```php
$fb = new Facebook\Facebook([/* */]);
$jsHelper = $fb->getJavaScriptHelper();

try {
  $accessToken = $jsHelper->getAccessToken();
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
```

You will likely want to make an Ajax request when the login state changes in the Facebook SDK for JavaScript.  Information about that here: [FB.event.subscribe](https://developers.facebook.com/docs/reference/javascript/FB.getLoginStatus/#events)

## Instance Methods

### __construct()
```php
public FacebookJavaScriptHelper __construct(FacebookApp $app, FacebookClient $client, $graphVersion = null)
```
Upon instantiation, `FacebookJavaScriptHelper` validates and decodes the signed request that exists in the cookie set by the JavaScript SDK if present.

### getAccessToken()
```php
public Facebook\AccessToken|null getAccessToken( Facebook\FacebookClient $client )
```
Checks the signed request for authentication data and tries to obtain an access token access token.

### getUserId()
```php
public string|null getUserId()
```
A convenience method for obtaining a user's ID from the signed request if present. This will only return the user's ID if a valid signed request can be obtained and decoded and the user has already authorized the app.

```php
$userId = $jsHelper->getUserId();

if ($userId) {
  // User is logged in
}
```

This is equivalent to accessing the user ID from the signed request entity.

```php
$signedRequest = $jsHelper->getSignedRequest();

if ($signedRequest) {
  $userId = $signedRequest->getUserId();
  // OR
  $userId = $signedRequest->get('user_id');
}
```

### getSignedRequest()
```php
public Facebook\SignedRequest|null getSignedRequest()
```
Returns the signed request as a [`Facebook\SignedRequest`](SignedRequest.md) entity if present.

### getRawSignedRequest()
```php
public string|null getRawSignedRequest()
```
Returns the raw encoded signed request as a `string` or `null`.
