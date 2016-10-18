# Facebook\Helpers\FacebookPageTabHelper

Page tabs are similar to the context to app canvases but are treated slightly differently. Use the `FacebookPageTabHelper` to obtain an access token or signed request within the context of a page tab.

## Usage

The usage of the `FacebookPageTabHelper` is exactly the same as [`FacebookCanvasHelper`](FacebookCanvasHelper.md) with  additional methods to obtain the `page` data from the signed request.

```php
$fb = new Facebook\Facebook([/* */]);
$pageHelper = $fb->getPageTabHelper();
$signedRequest = $pageHelper->getSignedRequest();

if ($signedRequest) {
  $payload = $signedRequest->getPayload();
  var_dump($payload);
}
```

If a user has already authenticated your app, you can also obtain an access token.

```php
$fb = new Facebook\Facebook([/* */]);
$pageHelper = $fb->getPageTabHelper();

try {
  $accessToken = $pageHelper->getAccessToken();
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

## Instance Methods

### getPageData()
```php
public string|null getPageData($key, $default = null)
```
Gets a value from the `page` property if present.

### isAdmin()
```php
public boolean isAdmin()
```
Returns `true` is the user has authenticated your app and is an admin of the parent page.

### getPageId()
```php
public string|null getPageId()
```
Returns the ID of the parent page if it can be obtained from the `page` property in the signed request.
