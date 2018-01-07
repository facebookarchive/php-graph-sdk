# Facebook SDK for PHP Reference (v5)

Below is the API reference for the Facebook SDK for PHP.

# Core API

These classes are at the core of the Facebook SDK for PHP.

| Class name  | Description |
| ------------- | ------------- |
| [`Facebook\Facebook`](reference/Facebook.md)  | The main service object that helps tie all the SDK components together.  |
| [`Facebook\FacebookApp`](reference/FacebookApp.md)  | An entity that represents a Facebook app and is required to send requests to Graph.  |

# Authentication

These classes facilitate authenticating a Facebook user with OAuth 2.0.

| Class name  | Description |
| ------------- | ------------- |
| [`Facebook\Helpers\FacebookRedirectLoginHelper`](reference/FacebookRedirectLoginHelper.md)  | An OAuth 2.0 service to obtain a user access token from a redirect using a "Log in with Facebook" link.  |
| [`Facebook\Authentication\AccessToken`](reference/AccessToken.md)  | An entity that represents an access token.  |
| `Facebook\Authentication\AccessTokenMetadata`  | An entity that represents metadata from an access token.  |
| `Facebook\Authentication\OAuth2Client`  | An OAuth 2.0 client that sends and receives HTTP requests related to user authentication.  |

# Requests and Responses

These classes are used in a Graph API request/response cycle.

| Class name  | Description |
| ------------- | ------------- |
| [`Facebook\FacebookRequest`](reference/FacebookRequest.md) | An entity that represents an HTTP request to be sent to Graph.  |
| [`Facebook\FacebookResponse`](reference/FacebookResponse.md)  | An entity that represents an HTTP response from Graph.  |
| [`Facebook\FacebookBatchRequest`](reference/FacebookBatchRequest.md)  | An entity that represents an HTTP batch request to be sent to Graph.  |
| [`Facebook\FacebookBatchResponse`](reference/FacebookBatchResponse.md)  | An entity that represents an HTTP response from Graph after sending a batch request.  |
| [`Facebook\FacebookClient`](reference/FacebookClient.md)  | A service object that sends HTTP requests and receives HTTP responses to and from the Graph API.  |

# Signed Requests

Classes to help obtain and manage signed requests.

| Class name  | Description |
| ------------- | ------------- |
| [`Facebook\Helpers\FacebookJavaScriptHelper`](reference/FacebookJavaScriptHelper.md) | Used to obtain an access token or signed request from the cookie set by the JavaScript SDK.  |
| [`Facebook\Helpers\FacebookCanvasHelper`](reference/FacebookCanvasHelper.md)  | Used to obtain an access token or signed request from within the context of an app canvas.  |
| [`Facebook\Helpers\FacebookPageTabHelper`](reference/FacebookPageTabHelper.md)  | Used to obtain an access token or signed request from within the context of a page tab.  |
| [`Facebook\SignedRequest`](reference/SignedRequest.md)  | An entity that represents a signed request.  |

# Core Exceptions

These are the core exceptions that the SDK will throw when an error occurs.

| Class name  | Description |
| ------------- | ------------- |
| [`Facebook\Exceptions\FacebookSDKException`](reference/FacebookSDKException.md) | The base exception to all exceptions thrown by the SDK. Thrown when there is a non-Graph-response-related error.  |
| [`Facebook\Exceptions\FacebookResponseException`](reference/FacebookResponseException.md)  | The base exception to all Graph error responses. This exception is never thrown directly.  |

# Graph Nodes and Edges

Graph nodes are collections that represent nodes returned by the Graph API. And Graph edges are a collection of nodes returned from an edge on the Graph API.

| Class name  | Description |
| ------------- | ------------- |
| [`Facebook\GraphNodes\GraphNode`](reference/GraphNode.md) | The base collection object that represents a generic node.  |
| [`Facebook\GraphNodes\GraphEdge`](reference/GraphEdge.md)  | A collection of GraphNode\'s with special methods to help paginate over the edge.  |
| [`Facebook\GraphNodes\GraphAchievement`](reference/GraphNode.md#graphachievement-instance-methods) | A collection that represents an Achievement node.  |
| [`Facebook\GraphNodes\GraphAlbum`](reference/GraphNode.md#graphalbum-instance-methods)  | A collection that represents an Album node. |
| [`Facebook\GraphNodes\GraphLocation`](reference/GraphNode.md#graphlocation-instance-methods)  | A collection that represents a Location node. |
| [`Facebook\GraphNodes\GraphPage`](reference/GraphNode.md#graphpage-instance-methods)  | A collection that represents a Page node. |
| [`Facebook\GraphNodes\GraphPicture`](reference/GraphNode.md#graphpicture-instance-methods)  | A collection that represents a Picture node. |
| [`Facebook\GraphNodes\GraphUser`](reference/GraphNode.md#graphuser-instance-methods)  | A collection that represents a User node. |

# File Uploads

These are entities that represent files to be uploaded with a Graph request.

| Class name  | Description |
| ------------- | ------------- |
| [`Facebook\FileUpload\FacebookFile`](reference/FacebookFile.md) | Represents a generic file to be uploaded to the Graph API.  |
| [`Facebook\FileUpload\FacebookVideo`](reference/FacebookVideo.md)  | Represents a video file to be uploaded to the Graph API.  |

# Extensibility

You can overwrite certain functionality of the SDK by coding to an interface and injecting an instance of your custom functionality.

| Interface name  | Description |
| ------------- | ------------- |
| `Facebook\HttpClients\ FacebookHttpClientInterface` | An interface to code your own HTTP client implementation.  |
| `Facebook\Http\GraphRawResponse`  | An entity that is returned from an instance of a `FacebookHttpClientInterface` that represents a raw HTTP response from the Graph API.  |
| [`Facebook\PersistentData\PersistentDataInterface`](reference/PersistentDataInterface.md) | An interface to code your own persistent data storage implementation.  |
| [`Facebook\Url\UrlDetectionInterface`](reference/UrlDetectionInterface.md) | An interface to code your own URL detection logic.  |
| [`Facebook\PseudoRandomString\ PseudoRandomStringGeneratorInterface`](reference/PseudoRandomStringGeneratorInterface.md) | An interface to code your own cryptographically secure pseudo-random string generator.  |
