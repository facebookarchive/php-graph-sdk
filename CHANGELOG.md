# CHANGELOG

As you may have already noticed, the Facebook SDK v4 does not follow strict [semver](http://semver.org/). The versioning format used for this SDK is more like `4.MAJOR.(MINOR|PATCH)`. The `MINOR` and `PATCH` versions are squashed together but there shouldn't be any breaking changes between `MINOR|PATCH` releases.


## 4.1.x

- 4.1.0 (2014-??-??)
  - Added batch support
  - Added `graph.beta.facebook.com` support
  - Moved exception classes to `Exception\*` directory
  - Moved response collection objects to `GraphNodes\*` directory
  - Moved helpers to `Helpers\*` directory
  - Moved `FacebookRequest` and `FacebookResponse` to `Entities\*` directory
  - Killed `FacebookSession` in favor of `Facebook\Entities\AccessToken`
  - Added `FacebookClient` service
  - Renamed `FacebookRequestException` to `FacebookResponseException`
  - Renamed `FacebookHttpable` to `FacebookHttpClientInterface`
  - Updated the API for the helpers.
  - Refactored request/response handling
  - Added support for "rerequest" authorization
  - [`AccessToken`] Added serialization support
  - Added `ext-mbstring` to composer require
  - Added `Facebook\Entities\FacebookApp` entity
  - Namespaced tests
  - Grouped functional tests under `functional` group
  - Added `Facebook\Facebook` super service
  - Added this CHANGELOG. Hi! :)


## 4.0.x

- 4.0.12 (2014-10-30)
  - Added Graph v2.2 support
  - Fixed potential duplicate `type` param in URL's
  - [`FacebookRedirectLoginHelper`] Added `getReRequestUrl()`
  - [`GraphUser`] Added `getEmail()`
- 4.0.11 (2014-08-25)
  - [`FacebookCurlHttpClient`] Added a method to disable IPv6 resolution
- 4.0.10 (2014-08-12)
  - [`GraphObject`] Fixed improper usage of `stdClass`
  - Fixed warnings when `open_basedir` directive set
  - Fixed long lived sessions forgetting the signed request
  - [`CanvasLoginHelper`] Removed GET processing
  - Updated visibility on `FacebookSession::useAppSecretProof`
- 4.0.9 (2014-06-27)
  - [`FacebookPageTabHelper`] Added ability to fetch `app_data`
  - Added `GraphUserPage` Graph node collection
  - Cleaned up test files
  - Decoupled signed request handling
  - Added some stronger type hinting
  - Explicitly added separator in `http_build_query()`
  - [`FacebookCurlHttpClient`] Updated the calculation of the request body size
  - Decoupled access token handling
  - [`FacebookRedirectLoginHelper`] Implemented better CSPRNG
  - Added autoloader for those poor non-composer peeps
- 4.0.8 (2014-06-10)
  - Enabled `appsecret_proof` by default
  - Added stream wrapper and Guzzle HTTP client implementations
- 4.0.7 (2014-05-31)
  - Improved testing environment
  - Added `FacebookPageTabHelper`
  - [`FacebookSession`] Fixed issue where `validateSessionInfo()` would return incorrect results
- 4.0.6 (2014-05-24)
  - Added feature to inject custom HTTP clients
  - [`FacebookCanvasLoginHelper`] Fixed bug that would throw when logging out
  - Removed appToken from test credentials file
  - [`FacebookRequest`] Added `appsecret_proof` handling
- 4.0.5 (2014-05-19)
  - Fixed bug in cURL where proxy headers are not included in header_size
  - Added internal SDK error codes for thrown exceptions
  - Added stream wrapper fallback for hosting environments without cURL
  - Added getter methods for signed requests
  - Fixed warning that showed up in tests
  - Changed SDK error code for stream failure
  - Added `GraphAlbum` Graph node collection
- 4.0.4 (2014-05-15)
  - Added more error codes to accommodate more Graph error responses
  - [`JavaScriptLoginHelper`] Fixed bug that would try to get a new access token when one already existed
- 4.0.3 (2014-05-14)
  - Fixed bug for "Missing client_id parameter" error
  - Fixed bug for eTag support when "Network is unreachable" error occurs
  - Fixed pagination issue related to `sdtClass`
- 4.0.2 (2014-05-07)
  - [`composer.json`] Upgraded to use PSR-4 autoloading instead of Composer's `classmap`
  - [`FacebookCanvasLoginHelper`] Abstracted access to super globals
  - [`FacebookRequest`] Fixed bug that blindly appended params to a url
  - [`FacebookRequest`] Added support for `DELETE` and `PUT` methods
  - Added eTag support to Graph requests
- 4.0.1 (2014-05-05)
  - All exceptions are now extend from `FacebookSDKException`
  - [`FacebookSession`] Signed request parsing will throw on malformed signed request input
  - Excluded test credentials from tests
  - [`FacebookRedirectLoginHelper`] Changed scope on `$state` property
  - [`phpunit.xml`] Normalized
- 4.0.0 (2014-04-30)
  - Initial release. Yay!
