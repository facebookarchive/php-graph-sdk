# Getting started with the Facebook SDK for PHP

Whether you're developing a website with Facebook login, creating a Facebook Canvas app or Page tab, the Facebook SDK for PHP does all the heavy lifting for you making it as easy as possible to deeply integrate into the Facebook platform.

## System requirements

- PHP 5.6 or greater
- [Composer](https://getcomposer.org/)

## Installing the Facebook SDK for PHP

The Facebook SDK for PHP is installed with [Composer](#installing-with-composer-recommended). Simply run the following in the root of your project.

```
composer require facebook/graph-sdk
```

> The Facebook SDK starting adhering to [SemVer](http://semver.org/) with version 5. Previous to version 5, the SDK did not follow SemVer.


Once you do this, composer will edit your `composer.json` file and download the latest version of the SDK and put it in the `/vendor/` directory.

Make sure to include the Composer autoloader at the top of your script.

```php
require_once __DIR__ . '/vendor/autoload.php';
```

## Configuration and setup

> **Warning:** This assumes you have already created and configured a Facebook App, which you can obtain from the [App Dashboard](https://developers.facebook.com/apps).

Before we can send requests to the Graph API, we need to load our app configuration into the `Facebook\Facebook` service.

```php
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.10',
  ]);
```

You'll need to replace the `{app-id}` and `{app-secret}` with your Facebook app's ID and secret which can be obtained from the [app settings tab](https://developers.facebook.com/apps).

> **Warning:** It's important that you specify a `default_graph_version` value as this will give you more control over which version of Graph you want to use. If you don't specify a `default_graph_version`, the SDK will throw an [`InvalidArgumentException`](http://php.net/invalidargumentexception). See more about [Graph API versions](https://developers.facebook.com/docs/apps/versions).

The `Facebook\Facebook` service ties all the components of the SDK for PHP together. [See the full reference for the `Facebook\Facebook` service](reference/Facebook.md).

## Authentication and authorization

The SDK can be used to support logging a Facebook user into your site using Facebook Login which is based on OAuth 2.0.

Most all request made to the Graph API require an access token. We can obtain user access tokens with the SDK using the [helper classes](reference.md).


### Obtaining an access token from redirect

For most websites, you'll use the [`Facebook\Helper\FacebookRedirectLoginHelper`](reference/FacebookRedirectLoginHelper.md) to generate a login URL with the `getLoginUrl()` method. The link will take the user to an app authorization screen and upon approval, will redirect them back to a URL that you specified. On the redirect callback page we can obtain the user access token as an [`AccessToken`](reference/AccessToken.md) entity.

> For this example we'll assume `login.php` will present the login link and the user will be redirected to `login-callback.php` where we will obtain the access token.

```php
# login.php
$fb = new Facebook\Facebook([/* . . . */]);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email', 'user_likes']; // optional
$loginUrl = $helper->getLoginUrl('http://{your-website}/login-callback.php', $permissions);

echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
```

> **Warning:** The `FacebookRedirectLoginHelper` makes use of sessions to store a [CSRF](http://en.wikipedia.org/wiki/Cross-site_request_forgery) value. You need to make sure you have sessions enabled before invoking the `getLoginUrl()` method. This is usually done automatically in most web frameworks, but if you're not using a web framework you can add [`session_start();`](http://php.net/session_start) to the top of your `login.php` & `login-callback.php` scripts.

```php
# login-callback.php
$fb = new Facebook\Facebook([/* . . . */]);

$helper = $fb->getRedirectLoginHelper();
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exception\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exception\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken)) {
  // Logged in!
  $_SESSION['facebook_access_token'] = (string) $accessToken;

  // Now you can redirect to another page and use the
  // access token from $_SESSION['facebook_access_token']
}
```


### Obtaining an access token from a Facebook Canvas context

If your app is on Facebook Canvas, use the `getAccessToken()` method on [`Facebook\Helper\FacebookCanvasHelper`](reference/FacebookCanvasHelper.md) to get an [`AccessToken`](reference/AccessToken.md) entity for the user.

> **Warning:** The `FacebookCanvasHelper` will detect a [signed request](reference.md#signed-requests) for you and attempt to obtain an access token using the payload data from the signed request. The signed request will only contain the data needed to obtain an access token if the user has already authorized your app sometime in the past. If they have not yet authorized your app the `getAccessToken()` will return `null` and you will need to log the user in with either the [redirect method](#obtaining-an-access-token-from-redirect) or by using the [SDK for JavaScript](https://developers.facebook.com/docs/javascript) and then use the SDK for PHP to [obtain the access token from the cookie](#obtaining-an-access-token-from-the-sdk-for-javascript) the SDK for JavaScript set.

```php
# example-canvas-app.php
$fb = new Facebook\Facebook([/* . . . */]);

$helper = $fb->getCanvasHelper();
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exception\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exception\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken)) {
  // Logged in.
}
```

> If your app exists within the context of a Page tab, you can obtain an access token using the example above since a Page tab is very similar to a Facebook Canvas app. But if you'd like to use a Page-tab-specific helper, you can use the [`Facebook\Helper\FacebookPageTabHelper`](reference/FacebookPageTabHelper.md)


### Obtaining an access token from the SDK for JavaScript

If you're already using the Facebook SDK for JavaScript to authenticate users, you can obtain the access token with PHP by using the [FacebookJavaScriptHelper](reference/FacebookJavaScriptHelper.md). The `getAccessToken()` method will return an [`AccessToken`](reference/AccessToken.md) entity.

```php
# example-obtain-from-js-cookie-app.php
$fb = new Facebook\Facebook([/* . . . */]);

$helper = $fb->getJavaScriptHelper();
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exception\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exception\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken)) {
  // Logged in
}
```

> **Warning:** Make sure you set the `{cookie:true}` option when you [initialize the SDK for JavaScript](https://developers.facebook.com/docs/javascript/reference/FB.init/v2.10). This will make the SDK for JavaScript set a cookie on your domain containing information about the user in the form of a signed request.

## Extending the access token

When a user first logs into your app, the access token your app receives will be a short-lived access token that lasts about 2 hours. It's generally a good idea to exchange the short-lived access token for a long-lived access token that lasts about 60 days.

To extend an access token, you can make use of the [`OAuth2Client`](reference/Facebook.md#getoauth2client).

```php
// OAuth 2.0 client handler
$oAuth2Client = $fb->getOAuth2Client();

// Exchanges a short-lived access token for a long-lived one
$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken('{access-token}');
```

[See more about long-lived and short-lived access tokens](https://developers.facebook.com/docs/facebook-login/access-tokens#usertokens).

## Making Requests to the Graph API

Once you have an instance of the `Facebook\Facebook` service and obtained an access token, you can begin making calls to the Graph API.

In this example we will send a GET request to the Graph API endpoint `/me`. The `/me` endpoint is a special alias to the [user node endpoint](https://developers.facebook.com/docs/graph-api/reference/user) that references the user or Page making the request.

```php
$fb = new Facebook\Facebook([/* . . . */]);

// Sets the default fallback access token so we don't have to pass it to each request
$fb->setDefaultAccessToken('{access-token}');

try {
  $response = $fb->get('/me');
  $userNode = $response->getGraphUser();
} catch(Facebook\Exception\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exception\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

echo 'Logged in as ' . $userNode->getName();
```

The `get()` method will return a [`Facebook\FacebookResponse`](reference/FacebookResponse.md) which is an entity that represents an HTTP response from the Graph API.

To get the response in the form of a nifty collection, we call `getGraphUser()` which returns a [`Facebook\GraphNode\GraphUser`](reference/GraphNode.md#graphuser-instance-methods) entity which represents a user node.

If you don't care about fancy collections and just want the response as a plain-old array, you can call the `getDecodedBody()` method on the `FacebookResponse` entity.

```php
try {
  $response = $fb->get('/me');
} catch(Facebook\Exception\FacebookSDKException $e) {
  // . . .
  exit;
}

$plainOldArray = $response->getDecodedBody();
```

For a full list of all of the components that make up the SDK for PHP, see the [SDK for PHP reference page](reference.md).
