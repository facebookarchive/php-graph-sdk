# SignedRequest entity for the Facebook SDK for PHP

The `Facebook\SignedRequest` entity represents a signed request.

## Facebook\SignedRequest

[Signed requests](https://developers.facebook.com/docs/games/gamesonfacebook/login#detectingloginstatus) contain payloads of data that can be validated against a hash signature to ensure it is from Facebook. The `Facebook\SignedRequest` entity can validate a signed request signature and decode the payload.

To instantiate a new `Facebook\SignedRequest` entity, pass the [`Facebook\FacebookApp`](FacebookApp.md) entity and raw signed request to the constructor.

```php
$fbApp = new Facebook\FacebookApp('{app-id}', '{app-secret}');
$signedRequest = new Facebook\SignedRequest($fbApp, 'raw.signed_request');
```

Usually `Facebook\SignedRequest` entities are obtained using one of the [helpers](../reference.md).

```php
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
```

## Instance Methods

### getRawSignedRequest()
```php
public string|null getRawSignedRequest()
```
Returns the original raw encoded signed request in the form of a string.

### getPayload()
```php
public array|null getPayload()
```
Returns the [signed request payload](https://developers.facebook.com/docs/reference/login/signed-request/) in the form of an array.

### get()
```php
public string|null get(string $key, string|null $default)
```
Returns a [field from the signed request payload](https://developers.facebook.com/docs/reference/login/signed-request) or `$default` if the value does not exist.

### getUserId()
```php
public string|null getUserId()
```
Returns the `user_id` field from the signed request payload if it exists or `null` if it does not exists.

### hasOAuthData()
```php
public boolean hasOAuthData()
```
Returns `true` if the payload data contains either an `oauth_token` or `code` field. Returns `false` if neither value exists.

### make()
```php
public string make(array $payload)
```
Generates a valid raw signed request as a string that contains the data from the `$payload` array. The signature is signed using the app secret from the `Facebook\FacebookApp` entity. This can be useful for testing purposes.

```php
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
```
