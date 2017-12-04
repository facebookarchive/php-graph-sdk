# FacebookSDKException for the Facebook SDK for PHP

Represents an exception thrown by the SDK.

## Facebook\Exception\SDKException

A `FacebookSDKException` is thrown when something goes wrong. For example if an invalid signed request is sent to the `Facebook\SignedRequest` entity, it will throw an `FacebookSDKException`.

When an error response is returned from the Graph API, it will be thrown as a `FacebookSDKException` subtype called a [Facebook\Exception\ResponseException](ResponseException.md).

## Instance Methods

`FacebookSDKException` extends from the base `\Exception` class, so `getCode()` and `getMessage()` are available by default.
