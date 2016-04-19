<card>
# Facebook SDK for PHP Reference (v5)

Below is the API reference for the Facebook SDK for PHP.
</card>

<card>
# Core API {#core-api}

These classes are at the core of the Facebook SDK for PHP.

%FB(devsite:markdown-wiki:table {
  columns: ['Class name','Description',],
  rows: [
    [
      '[`Facebook\\Facebook`](/docs/php/Facebook)',
      'The main service object that helps tie all the SDK components together.',
    ],
    [
      '[`Facebook\\FacebookApp`](/docs/php/FacebookApp)',
      'An entity that represents a Facebook app and is required to send requests to Graph.',
    ],
  ],
})
</card>

<card>
# Authentication {#authentication}

These classes facilitate authenticating a Facebook user with OAuth 2.0.

%FB(devsite:markdown-wiki:table {
  columns: ['Class name','Description',],
  rows: [
    [
      '[`Facebook\\Helpers\\FacebookRedirectLoginHelper`](/docs/php/FacebookRedirectLoginHelper)',
      'An OAuth 2.0 service to obtain a user access token from a redirect using a "Log in with Facebook" link.',
    ],
    [
      '[`Facebook\\Authentication\\AccessToken`](/docs/php/AccessToken)',
      'An entity that represents an access token.',
    ],
    [
      '`Facebook\\Authentication\\AccessTokenMetadata`',
      'An entity that represents metadata from an access token.',
    ],
    [
      '`Facebook\\Authentication\\OAuth2Client`',
      'An OAuth 2.0 client that sends and receives HTTP requests related to user authentication.',
    ],
  ],
})
</card>

<card>
# Requests and Responses {#requests-and-responses}

These classes are used in a Graph API request/response cycle.

%FB(devsite:markdown-wiki:table {
  columns: ['Class name','Description',],
  rows: [
    [
      '[`Facebook\\FacebookRequest`](/docs/php/FacebookRequest)',
      'An entity that represents an HTTP request to be sent to Graph.',
    ],
    [
      '[`Facebook\\FacebookResponse`](/docs/php/FacebookResponse)',
      'An entity that represents an HTTP response from Graph.',
    ],
    [
      '[`Facebook\\FacebookBatchRequest`](/docs/php/FacebookBatchRequest)',
      'An entity that represents an HTTP batch request to be sent to Graph.',
    ],
    [
      '[`Facebook\\FacebookBatchResponse`](/docs/php/FacebookBatchResponse)',
      'An entity that represents an HTTP response from Graph after sending a batch request.',
    ],
    [
      '[`Facebook\\FacebookClient`](/docs/php/FacebookClient)',
      'A service object that sends HTTP requests and receives HTTP responses to and from the Graph API.',
    ],
  ],
})
</card>

<card>
# Signed Requests {#signed-requests}

Classes to help obtain and manage signed requests.

%FB(devsite:markdown-wiki:table {
  columns: ['Class name','Description',],
  rows: [
    [
      '[`Facebook\\Helpers\\FacebookJavaScriptHelper`](/docs/php/FacebookJavaScriptHelper)',
      'Used to obtain an access token or signed request from the cookie set by the JavaScript SDK.',
    ],
    [
      '[`Facebook\\Helpers\\FacebookCanvasHelper`](/docs/php/FacebookCanvasHelper)',
      'Used to obtain an access token or signed request from within the context of an app canvas.',
    ],
    [
      '[`Facebook\\Helpers\\FacebookPageTabHelper`](/docs/php/FacebookPageTabHelper)',
      'Used to obtain an access token or signed request from within the context of a page tab.',
    ],
    [
      '[`Facebook\\SignedRequest`](/docs/php/SignedRequest)',
      'An entity that represents a signed request.',
    ],
  ],
})
</card>

<card>
# Core Exceptions {#core-exceptions}

These are the core exceptions that the SDK will throw when an error occurs.

%FB(devsite:markdown-wiki:table {
  columns: ['Class name','Description',],
  rows: [
    [
      '[`Facebook\\Exceptions\\FacebookSDKException`](/docs/php/FacebookSDKException)',
      'The base exception to all exceptions thrown by the SDK. Thrown when there is a non-Graph-response-related error.',
    ],
    [
      '[`Facebook\\Exceptions\\FacebookResponseException`](/docs/php/FacebookResponseException)',
      'The base exception to all Graph error responses. This exception is never thrown directly.',
    ],
  ],
})
</card>

<card>
# Graph Nodes and Edges {#graph-nodes-and-edges}

Graph nodes are collections that represent nodes returned by the Graph API. And Graph edges are a collection of nodes returned from an edge on the Graph API.

%FB(devsite:markdown-wiki:table {
  columns: ['Class name','Description',],
  rows: [
    [
      '[`Facebook\\GraphNodes\\GraphNode`](/docs/php/GraphNode)',
      'The base collection object that represents a generic node.',
    ],
    [
      '[`Facebook\\GraphNodes\\GraphEdge`](/docs/php/GraphEdge)',
      'A collection of GraphNode\'s with special methods to help paginate over the edge.',
    ],
    [
      '[`Facebook\\GraphNodes\\GraphAchievement`](/docs/php/GraphNode#achievement-instance-methods)',
      'A collection that represents an Achievement node.',
    ],
    [
      '[`Facebook\\GraphNodes\\GraphAlbum`](/docs/php/GraphNode#album-instance-methods)',
      'A collection that represents an Album node.',
    ],
    [
      '[`Facebook\\GraphNodes\\GraphLocation`](/docs/php/GraphNode#location-instance-methods)',
      'A collection that represents a Location node.',
    ],
    [
      '[`Facebook\\GraphNodes\\GraphPage`](/docs/php/GraphNode#page-instance-methods)',
      'A collection that represents a Page node.',
    ],
    [
      '[`Facebook\\GraphNodes\\GraphPicture`](/docs/php/GraphNode#picture-instance-methods)',
      'A collection that represents a Picture node.',
    ],
    [
      '[`Facebook\\GraphNodes\\GraphUser`](/docs/php/GraphNode#user-instance-methods)',
      'A collection that represents a User node.',
    ],
  ],
})
</card>

<card>
# File Uploads {#file-uploads}

These are entities that represent files to be uploaded with a Graph request.

%FB(devsite:markdown-wiki:table {
  columns: ['Class name','Description',],
  rows: [
    [
      '[`Facebook\\FileUpload\\FacebookFile`](/docs/php/FacebookFile)',
      'Represents a generic file to be uploaded to the Graph API.',
    ],
    [
      '[`Facebook\\FileUpload\\FacebookVideo`](/docs/php/FacebookVideo)',
      'Represents a video file to be uploaded to the Graph API.',
    ],
  ],
})
</card>

<card>
# Extensibility {#extensibility}

You can overwrite certain functionality of the SDK by coding to an interface and injecting an instance of your custom functionality.

%FB(devsite:markdown-wiki:table {
  columns: ['Interface name','Description',],
  rows: [
    [
      '`Facebook\\HttpClients\\ FacebookHttpClientInterface`',
      'An interface to code your own HTTP client implementation.',
    ],
    [
      '`Facebook\\Http\\GraphRawResponse`',
      'An entity that is returned from an instance of a `FacebookHttpClientInterface` that represents a raw HTTP response from the Graph API.',
    ],
    [
      '[`Facebook\\PersistentData\\PersistentDataInterface`](/docs/php/PersistentDataInterface)',
      'An interface to code your own persistent data storage implementation.',
    ],
    [
      '[`Facebook\\Url\\UrlDetectionInterface`](/docs/php/UrlDetectionInterface)',
      'An interface to code your own URL detection logic.',
    ],
    [
      '[`Facebook\\PseudoRandomString\\ PseudoRandomStringGeneratorInterface`](/docs/php/PseudoRandomStringGeneratorInterface)',
      'An interface to code your own cryptographically secure pseudo-random string generator.',
    ],
  ],
})
</card>
