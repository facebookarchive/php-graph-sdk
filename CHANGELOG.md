# CHANGELOG

Starting with version 5, the Facebook PHP SDK follows [SemVer](http://semver.org/).


## 5.x

Version 5 of the Facebook PHP SDK is a complete refactor of version 4. It comes loaded with lots of new features and a friendlier API.
- 5.3.1
  - Fixed a bug where the `polyfills.php` file wasn't being included properly when using the built-in auto loader (#633)
- 5.3.0
  - Bump Graph API version to v2.7.
- 5.2.1
  - Fix notice that is raised in `FacebookUrlDetectionHandler` (#626)
  - Fix bug in `FacebookRedirectLoginHelper::getLoginUrl()` where the CSRF token gets overwritten in certain scenarios (#613)
  - Fix bug with polyfills not getting loaded when installing the Facebook PHP SDK manually (#599)
- 5.2.0
  - Added new Birthday class to handle Graph API response variations
  - Bumped Graph version to v2.6
  - Added better error checking for app IDs that are cast as int when they are greater than PHP_INT_MAX
- 5.1.5
  - Removed mbstring extension dependency
  - Updated required PHP version syntax in composer.json
- 5.1.4
  - Breaking changes
    - Changes the serialization method of FacebookApp
      - FacebookApps serialized by versions prior 5.1.4 cannot be unserialized by this version
  - Fixed redirect_uri injection vulnerability
- 5.0 (2015-??-??)
  - New features
    - Added the `Facebook\Facebook` super service for an easier API
    - Improved "reauthentication" and "rerequest" support
    - Requests/Responses
      - Added full batch support
      - Added full file upload support for videos & photos
      - Added methods to make pagination easier
      - Added "deep" pagination support so that Graph edges embedded in a Graph node can be paginated over easily
      - Beta support at `graph.beta.facebook.com`
      - Added `getMetaData()` to `GraphEdge` to obtain all the metadata associated with a list of Graph nodes
      - Full nested param support
      - Many improvements to the Graph node subtypes
    - New injectable interfaces
      - Added a `PersistentDataInterface` for custom persistent data handling
      - Added a `PseudoRandomStringGeneratorInterface` for customizable CSPRNG's
      - Added a `UrlDetectionInterface` for custom URL-detection logic
  - Codebase changes
    - Moved exception classes to `Exception\*` directory
    - Moved response collection objects to `GraphNodes\*` directory
    - Moved helpers to `Helpers\*` directory
    - Killed `FacebookSession` in favor of the `AccessToken` entity
    - Added `FacebookClient` service
    - Renamed `FacebookRequestException` to `FacebookResponseException`
    - Renamed `FacebookHttpable` to `FacebookHttpClientInterface`
    - Added `FacebookApp` entity that contains info about the Facebook app
    - Updated the API for the helpers
    - Added `HttpClients`, `PersistentData` and `PseudoRandomString` factories to reduce main class' complexity
  - Tests
    - Added namespaces to the tests
    - Grouped functional tests under `functional` group
  - Other changes
    - Made PSR-2 compliant
    - Adopted SemVer
    - Completely refactored request/response handling
    - Refactored the OAuth 2.0 logic
    - Added `ext-mbstring` to composer require
    - Added this CHANGELOG. Hi! :)


## 4.1-dev

Since the Facebook PHP SDK didn't follow SemVer in version 4.x, the master branch was going to be released as 4.1. However, the SDK switched to SemVer in v5.0. So any references on the internet to version 4.1 can be assumed to be an alias to version `5.0.0`


## 4.0.x

Version 4.0 of the Facebook PHP SDK did not follow [SemVer](http://semver.org/). The versioning format used was as follows: `4.MAJOR.(MINOR|PATCH)`. The `MINOR` and `PATCH` versions were squashed together.

- 4.0.23 (2015-04-03)
  - Added support for new JSON response types in Graph v2.3 when requesting access tokens
- 4.0.22 (2015-04-02)
  - Fixed issues related to multidimensional params
  - **Bumped default fallback Graph version to `v2.3`**
- 4.0.21 (2015-03-31)
  - Added a `FacebookPermissions` class to reference all the Facebook permissions
- 4.0.20 (2015-03-02)
  - Fixed a bug introduced in `4.0.19` related to CSRF comparisons
- 4.0.19 (2015-03-02)
  - Added stricter CSRF comparison checks to `SignedRequest` and `FacebookRedirectLoginHelper`
- 4.0.18 (2015-02-24)
  - [`FacebookHttpable`] Reverted a breaking change from `4.0.17` that changed the method signatures
- 4.0.17 (2015-02-19)
  - [`FacebookRedirectLoginHelper`] Added multiple auth types to `getLoginUrl()`
  - [`GraphUser`] Added `getTimezone()`
  - [`FacebookCurl`] Additional fix for `curl_init()` handling
  - Added support for https://graph-video.facebook.com when path ends with `/videos`
- 4.0.16 (2015-02-03)
  - [`FacebookRedirectLoginHelper`] Added "reauthenticate" functionality to `getLoginUrl()`
  - [`FacebookCurl`] Fixed `curl_init()` issue
- 4.0.15 (2015-01-06)
  - [`FacebookRedirectLoginHelper`] Added guard against accidental exposure of app secret via the logout link
- 4.0.14 (2014-12-29)
  - [`GraphUser`] Added `getGender()`
  - [`FacebookRedirectLoginHelper`] Added CSRF protection for rerequest links
  - [`GraphAlbum`] Fixed bugs in getter methods
- 4.0.13 (2014-12-12)
  - [`FacebookRedirectLoginHelper`] Added `$displayAsPopup` param to `getLoginUrl()`
  - [`FacebookResponse`] Fixed minor pagination bug
  - Removed massive cert bundle and replaced with `DigiCertHighAssuranceEVRootCA` for peer verification
- 4.0.12 (2014-10-30)
  - **Updated default fallback Graph version to `v2.2`**
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
