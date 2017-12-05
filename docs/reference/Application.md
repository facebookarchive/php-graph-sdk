# Application for the Facebook SDK for PHP

In order to make requests to the Graph API, you need to [create a Facebook app](https://developers.facebook.com/apps) and obtain the app ID and the app secret. The `Facebook\Application` entity represents the Facebook app that is making the requests to the Graph API.

> **Warning:** It is quite uncommon to work with the `Application` entity directly since the `Facebook\Facebook` service handles injecting it into the required classes for you.

## Facebook\Application

To instantiate a new `Facebook\Application` entity, pass the app ID and app secret to the constructor.

```php
$fbApp = new Facebook\Application('{app-id}', '{app-secret}');
```

Alternatively you can obtain the `Facebook\Application` entity from the [`Facebook\Facebook`](Facebook.md) super service class.

```php
$fb = new Facebook\Facebook([/* . . . */]);
$fbApp = $fb->getApplication();
```

You'll rarely be using the `Application` entity directly unless you're doing some extreme customizations of the SDK for PHP. But this entity plays an important role in the internal workings of the SDK for PHP.

## Instance Methods

## getAccessToken()
```php
public Facebook\Authentication\AccessToken getAccessToken()
```
Returns an app access token in the form of an [`AccessToken`](AccessToken.md) entity.

## getId()
```php
public string getId()
```
Returns the app id.

## getSecret()
```php
public string getSecret()
```
Returns the app secret.

## Serialization

The `Facebook\Application` entity can be serialized and unserialized.

```php
$fbApp = new Facebook\Application('foo-app-id', 'foo-app-secret');

$serializedApplication = serialize($fbApp);
// C:29:"Facebook\\Application":54:{a:2:{i:0;s:10:"foo-app-id";i:1;s:14:"foo-app-secret";}}

$unserializedApplication = unserialize($serializedApplication);
echo $unserializedApplication->getAccessToken();
// foo-app-id|foo-app-secret
```
