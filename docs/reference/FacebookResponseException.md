<card>
# FacebookResponseException for the Facebook SDK for PHP

Represents an error response from the Graph API.
</card>

<card>
## Facebook\Exceptions\FacebookResponseException {#overview}

Whenever a `FacebookResponseException` is thrown, you can access it's previous exception with the `getPrevious()` method to get more information on the specific type of error response that the Graph API returned.

~~~~
try {
  // Some request to the Graph API
} catch (Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Message: ' . $e->getMessage();
  $previousException = $e->getPrevious();
  // Do some further processing on $previousException
  exit;
}
~~~~

%FB(devsite:markdown-wiki:table {
  columns: ['Class name','Description',],
  rows: [
    [
      '`Facebook\\Exceptions\\FacebookAuthenticationException`',
      'Thrown when Graph returns an authentication error.',
    ],
    [
      '`Facebook\\Exceptions\\FacebookAuthorizationException`',
      'Thrown when Graph returns a user permissions error.',
    ],
    [
      '`Facebook\\Exceptions\\FacebookClientException`',
      'Thrown when Graph returns a duplicate post error.',
    ],
    [
      '`Facebook\\Exceptions\\FacebookOtherException`',
      'Thrown when Graph returns an error that is unknown to the SDK.',
    ],
    [
      '`Facebook\\Exceptions\\FacebookServerException`',
      'Thrown when Graph returns a server error.',
    ],
    [
      '`Facebook\\Exceptions\\FacebookThrottleException`',
      'Thrown when Graph returns a throttle error.',
    ],
  ],
})

These exceptions are derived from the [error responses from the Graph API](/docs/graph-api/using-graph-api/#errors).
</card>

<card>
## Instance Methods {#instance-methods}

`FacebookResponseException` extends from the base `\Exception` class, so `getCode()` and `getMessage()` are available by default.

### getHttpStatusCode {#gethttpstatus}
`getHttpStatusCode()`  
Returns the HTTP status code returned with this exception.

### getSubErrorCode {#getsuberrorcode}
`getSubErrorCode()`  
Returns the numeric sub-error code returned from the Graph API.

### getErrorType {#geterrortype}
`getErrorType()`  
Returns the type of error as a string.

### getResponseData {#getresponsedata}
`getResponseData()`
Returns the decoded response body used to create the exception as an array.

### getRawResponse {#getrawresponse}
`getRawResponse()`  
Returns the raw response body used to create the exception as a string.

### getResponse {#getresponse}
`getResponse()`
Returns the `FacebookResponse` entity which represents the HTTP response.
</card>