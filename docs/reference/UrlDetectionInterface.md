# The URL detection interface for the Facebook SDK for PHP

The URL detection interface allows you to overwrite the default URL detection logic by coding to the `Facebook\Url\UrlDetectionInterface`.

## Facebook\Url\UrlDetectionInterface

If you're using a web framework that handles routes and URL generation for you, you might want to code a custom URL detection handler to ensure that your URL's are being generated consistently.

For example if you are using Laravel, a custom handler might look like this:

```php
use Facebook\Url\UrlDetectionInterface;

class MyLaravelUrlDetectionHandler implements UrlDetectionInterface
{
  /**
   * @inheritdoc
   */
  public function getCurrentUrl()
  {
    return \Request::url();
  }
}
```

To enable your custom URL detection implementation in the SDK, you can set an instance of the handler to the `url_detection_handler` config of the `Facebook\Facebook` super service.

```php
$fb = new Facebook\Facebook([
  // . . .
  'url_detection_handler' => new MyLaravelUrlDetectionHandler(),
  // . . .
  ]);
```

Alternatively, if you're working with the `Facebook\Helpers\FacebookRedirectLoginHelper` directly, you can inject your custom handler via the constructor.

```php
use Facebook\Helpers\FacebookRedirectLoginHelper;

$myUrlDetectionHandler = new MyLaravelUrlDetectionHandler();
$helper = new FacebookRedirectLoginHelper($fbApp, null, $myUrlDetectionHandler);
```

## Method Reference

### getCurrentUrl()
```php
public string getCurrentUrl()
```
Returns the full and currently active URL.
