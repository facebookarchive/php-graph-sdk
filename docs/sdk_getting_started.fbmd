<card>
# Getting started with the Facebook SDK for PHP

Whether you're developing a website with Facebook login, creating a Facebook Canvas app or Page tab, the Facebook SDK for PHP does all the heavy lifting for you making it as easy as possible to deeply integrate into the Facebook platform.
</card>

<card>
## Autoloading & namespaces {#psr-4}

The Facebook SDK for PHP v5 is coded in compliance with [PSR-4](http://www.php-fig.org/psr/psr-4/). This means it relies heavily on namespaces so that class files can be loaded for you automatically.

It would be advantageous to familiarize yourself with the concepts of [namespacing](http://php.net/manual/en/language.namespaces.rationale.php) and [autoloading](http://php.net/manual/en/function.spl-autoload-register.php) if you are not already acquainted with them.
</card>

<card>
## System requirements {#requirements}

- PHP 5.4 or greater
- The [mbstring](http://php.net/manual/en/book.mbstring.php) extension
- [Composer](https://getcomposer.org/) *(optional)*
</card>

<card>
## Installing the Facebook SDK for PHP {#installation}

There are two methods to install the Facebook SDK for PHP. The recommended installation method is by using [Composer](#install-composer). If are unable to use Composer for your project, you can still [install the SDK manually](#install-manually) by downloading the source files and including the autoloader.
</card>

<card>
## Installing with Composer (recommended) {#install-composer}

[Composer](https://getcomposer.org/) is the recommended way to install the Facebook SDK for PHP. Simply run the following in the root of your project.

~~~
composer require facebook/php-sdk-v4
~~~

%FB(devsite:markdown-wiki:info-card {
  content: "The Facebook SDK starting adhering to [SemVer](http://semver.org/) with version 5. Previous to version 5, the SDK did not follow SemVer.",
  type: 'info',
})

Once you do this, composer will edit your `composer.json` file and download the latest version of the SDK and put it in the `/vendor/` directory.

Make sure to include the Composer autoloader at the top of your script.

~~~
require_once __DIR__ . '/vendor/autoload.php';
~~~
</card>

<card>
## Manually installing (if you really have to) {#install-manually}

First, download the source code and unzip it wherever you like in your project.

%FB(devsite:markdown-wiki:button {
 text: 'Download the SDK for PHP v5.0',
 href: 'https://github.com/facebook/facebook-php-sdk-v4/archive/5.0-dev.zip',
 size: 'large',
 use: 'special',
})

Then include the autoloader provided in the SDK at the top of your script.

~~~
require_once __DIR__ . '/path/to/facebook-php-sdk-v4/src/Facebook/autoload.php';
~~~

The autoloader should be able to auto-detect the proper location of the source code.


### Keeping things tidy {#tidy-up}

The source code includes myriad files that aren't necessary for use in a production environment. If you'd like to strip out everything except the core files, follow this example.

%FB(devsite:markdown-wiki:info-card {
  content: "For this example we'll assume the root of your website is `/var/html`.",
  type: 'info',
})

After downloading the source code with the button above, extract the files in a temporary directory.

Move the folder `src/Facebook` to the root of your website installation or where ever you like to put third-party code. For this example we'll rename the `Facebook` directory to `facebook-sdk-v5`.

The path the the core SDK files should now be located in `/var/html/facebook-sdk-v5` and inside will also be the `autoload.php` file.

Assuming we have a script called `index.php` in the root of our web project, we need to include the autoloader at the top of our script.

~~~
require_once __DIR__ . '/facebook-sdk-v5/autoload.php';
~~~

If the autoloader is having trouble detecting the path to the source files, we can define the location of the source code before the `require_once` statement.

~~~
define('FACEBOOK_SDK_V4_SRC_DIR', __DIR__ . '/facebook-sdk-v5/');
require_once __DIR__ . '/facebook-sdk-v5/autoload.php';
~~~
</card>

<card>
## Configuration and setup {#setup}

%FB(devsite:markdown-wiki:info-card {
  content: "This assumes you have already created and configured a Facebook App, which you can obtain from the [App Dashboard](/apps).",
  type: 'warning',
})

Before we can send requests to the Graph API, we need to load our app configuration into the `Facebook\Facebook` service.

~~~
$fb = new Facebook\Facebook([
  'app_id' => '{app-id}',
  'app_secret' => '{app-secret}',
  'default_graph_version' => 'v2.5',
  ]);
~~~

You'll need to replace the `{app-id}` and `{app-secret}` with your Facebook app's ID and secret which can be obtained from the [app settings tab](/apps).

%FB(devsite:markdown-wiki:info-card {
  content: "It's important that you specify a `default_graph_version` value as this will give you more control over which version of Graph you want to use. If you don't specify a `default_graph_version`, the SDK for PHP will choose one for you and it might not be one that is compatible with your app.",
  type: 'warning',
})

The `Facebook\Facebook` service ties all the components of the SDK for PHP together. [See the full reference for the `Facebook\Facebook` service](/docs/php/Facebook).
</card>

<card>
## Authentication and authorization {#authentication}

The SDK can be used to support logging a Facebook user into your site using Facebook Login which is based on OAuth 2.0.

Most all request made to the Graph API require an access token. We can obtain user access tokens with the SDK using the [helper classes](/docs/php/reference#helpers).


### Obtaining an access token from redirect {#authentication-redirect}

For most websites, you'll use the [`Facebook\Helpers\FacebookRedirectLoginHelper`](/docs/php/FacebookRedirectLoginHelper) to generate a login URL with the `getLoginUrl()` method. The link will take the user to an app authorization screen and upon approval, will redirect them back to a URL that you specified. On the redirect callback page we can obtain the user access token as an [`AccessToken`](/docs/php/AccessToken) entity.

%FB(devsite:markdown-wiki:info-card {
  content: "For this example we'll assume `login.php` will present the login link and the user will be redirected to `login-callback.php` where we will obtain the access token.",
  type: 'info',
})

~~~
# login.php
$fb = new Facebook\Facebook([/* . . . */]);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email', 'user_likes']; // optional
$loginUrl = $helper->getLoginUrl('http://{your-website}/login-callback.php', $permissions);

echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
~~~

%FB(devsite:markdown-wiki:info-card {
  content: "The `FacebookRedirectLoginHelper` makes use of sessions to store a [CSRF](http://en.wikipedia.org/wiki/Cross-site_request_forgery) value. You need to make sure you have sessions enabled before invoking the `getLoginUrl()` method. This is usually done automatically in most web frameworks, but if you're not using a web framework you can add [`session_start();`](http://php.net/session_start) to the top of your `login.php` & `login-callback.php` scripts.",
  type: 'warning',
})

~~~
# login-callback.php
$fb = new Facebook\Facebook([/* . . . */]);

$helper = $fb->getRedirectLoginHelper();
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
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
~~~


### Obtaining an access token from a Facebook Canvas context {#authentication-canvas}

If your app is on Facebook Canvas, use the `getAccessToken()` method on [`Facebook\Helpers\FacebookCanvasHelper`](/docs/php/FacebookCanvasHelper) to get an [`AccessToken`](/docs/php/AccessToken) entity for the user.

%FB(devsite:markdown-wiki:info-card {
  content: "The `FacebookCanvasHelper` will detect a [signed request](/docs/reference/login/signed-request) for you and attempt to obtain an access token using the payload data from the signed request. The signed request will only contain the data needed to obtain an access token if the user has already authorized your app sometime in the past. If they have not yet authorized your app the `getAccessToken()` will return `null` and you will need to log the user in with either the [redirect method](#authentication-redirect) or by using the [SDK for JavaScript](/docs/javascript) and then use the SDK for PHP to [obtain the access token from the cookie](#authentication-javascript) the SDK for JavaScript set.",
  type: 'warning',
})

~~~
# example-canvas-app.php
$fb = new Facebook\Facebook([/* . . . */]);

$helper = $fb->getCanvasHelper();
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken)) {
  // Logged in.
}
~~~

%FB(devsite:markdown-wiki:info-card {
  content: "If your app exists within the context of a Page tab, you can obtain an access token using the example above since a Page tab is very similar to a Facebook Canvas app. But if you'd like to use a Page-tab-specific helper, you can use the [`Facebook\Helpers\FacebookPageTabHelper`](/docs/php/FacebookPageTabHelper)",
  type: 'info',
})


### Obtaining an access token from the SDK for JavaScript {#authentication-javascript}

If you're already using the Facebook SDK for JavaScript to authenticate users, you can obtain the access token with PHP by using the [FacebookJavaScriptHelper](/docs/php/FacebookJavaScriptHelper). The `getAccessToken()` method will return an [`AccessToken`](/docs/php/AccessToken) entity.

~~~
# example-obtain-from-js-cookie-app.php
$fb = new Facebook\Facebook([/* . . . */]);

$helper = $fb->getJavaScriptHelper();
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken)) {
  // Logged in
}
~~~

%FB(devsite:markdown-wiki:info-card {
  content: "Make sure you set the `{cookie:true}` option when you [initialize the SDK for JavaScript](/docs/javascript/reference/FB.init). This will make the SDK for JavaScript set a cookie on your domain containing information about the user in the form of a signed request.",
  type: 'warning',
})
</card>

<card>
## Extending the access token {#extending-access-token}

When a user first logs into your app, the access token your app receives will be a short-lived access token that lasts about 2 hours. It's generally a good idea to exchange the short-lived access token for a long-lived access token that lasts about 60 days.

To extend an access token, you can make use of the [`OAuth2Client`](/docs/php/OAuth2Client).

~~~
// OAuth 2.0 client handler
$oAuth2Client = $fb->getOAuth2Client();

// Exchanges a short-lived access token for a long-lived one
$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken('{access-token}');
~~~

[See more about long-lived and short-lived access tokens](/docs/facebook-login/access-tokens#extending).
</card>

<card>
## Making Requests to the Graph API {#making-requests}

Once you have an instance of the `Facebook\Facebook` service and obtained an access token, you can begin making calls to the Graph API.

In this example we will send a GET request to the Graph API endpoint `/me`. The `/me` endpoint is a special alias to the [user node endpoint](/docs/graph-api/reference/user) that references the user or Page making the request.

~~~
$fb = new Facebook\Facebook([/* . . . */]);

// Sets the default fallback access token so we don't have to pass it to each request
$fb->setDefaultAccessToken('{access-token}');

try {
  $response = $fb->get('/me');
  $userNode = $response->getGraphUser();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

echo 'Logged in as ' . $userNode->getName();
~~~

The `get()` method will return a [`Facebook\FacebookResponse`](/docs/php/FacebookResponse) which is an entity that represents an HTTP response from the Graph API.

To get the response in the form of a nifty collection, we call `getGraphUser()` which returns a [`Facebook\GraphNodes\GraphUser`](/docs/php/GraphNode#user-instance-methods) entity which represents a user node.

If you don't care about fancy collections and just want the response as a plain-old array, you can call the `getDecodedBody()` method on the `FacebookResponse` entity.

~~~
try {
  $response = $fb->get('/me');
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // . . .
  exit;
}

$plainOldArray = $response->getDecodedBody();
~~~

For a full list of all of the components that make up the SDK for PHP, see the [SDK for PHP reference page](/docs/php/reference).
</card>
