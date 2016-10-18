# GraphNode for the Facebook SDK for PHP

A `Facebook\GraphNodes\GraphNode` is a collection that represents a node returned by the Graph API.

## Facebook\GraphNodes\GraphNode

This base class has several subclasses:

[__GraphUser__](#user-instance-methods)
[__GraphPage__](#page-instance-methods)
[__GraphAlbum__](#album-instance-methods)
[__GraphLocation__](#location-instance-methods)
[__GraphPicture__](#picture-instance-methods)
[__GraphAchievement__](#achievement-instance-methods)

`GraphNode`s are obtained from a [`Facebook\FacebookResponse`](FacebookResponse.md) object which represents an HTTP response from the Graph API.

Usage:

```
$fb = new Facebook\Facebook(\* *\);
// Returns a `Facebook\FacebookResponse` object
$response = $fb->get('/something');

// Get the base class GraphNode from the response
$graphNode = $response->getGraphNode();

// Get the response typed as a GraphUser
$user = $response->getGraphUser());

// Get the response typed as a GraphPage
$page = $response->getGraphPage();

// User example
echo $graphNode->getField('name'); // From GraphNode
echo $user->getName(); // From GraphUser

// Location example
echo $graphNode->getField('country'); // From GraphNode
echo $location->getCountry(); // From GraphLocation
```

## SPL Libraries

The `GraphNode` collection and its subclasses implement several [SPL](http://php.net/manual/en/book.spl.php) libraries and [predefined PHP interfaces and classes](http://php.net/manual/en/reserved.interfaces.php) which make it convenient to work with the object in PHP. The supported libraries are `ArrayAccess`, `ArrayIterator`, `Countable`, and `IteratorAggregate`.

All of the following operations are possible on a `GraphNode`.

```
$graphNode = $response->getGraphNode();

// Array access
$id = $graphNode['id'];

// Iteration
foreach ($graphNode as $key => $value) {
  // . . .
}

// Counting
$total = count($graphNode);
```

## GraphNode Instance Methods


### asArray
`asArray()`  
Returns the raw representation (associative arrays, nested) of the node's underlying data.


### asJson
`asJson()`
Returns the data as a JSON string.


### getField
`getField(string $name, string $default = 'foo')`
Gets the value from the field of a Graph node.  If the value is a scalar (string, number, etc.) it will be returned.  If it's an associative array, it will be returned as a GraphNode.

The second argument lets you define a default value to return if the field doesn't exist.


### getFieldNames
`getFieldNames()`
Returns an array with the names of all fields present on the graph node.


### map
`map(Closure $callback)`
Provides a way to map over the data within the collection just like `array_map()`.

## GraphUser Instance Methods

The `GraphUser` collection represents a [User](https://developers.facebook.com/docs/graph-api/reference/user) Graph node.

### Auto-cast properties

The following properties on the `GraphUser` collection will get automatically cast as `GraphNode` subtypes:

| Property  | GraphNode subtype |
| ------------- | ------------- |
| `hometown` | [`Facebook\\GraphNodes\\GraphPage`](#page-instance-methods)  |
| `location`  | [`Facebook\\GraphNodes\\GraphPage`](#page-instance-methods) |
| `significant_other`  | [`Facebook\\GraphNodes\\GraphUser`](#user-instance-methods)  |

All getter methods return `null` if the property does not exist on the node.

### getId()
```
public string|null getId()
```
Returns the `id` property for the user as a string if present.

### getName()
```
public string|null getName()
```
Returns the `name` property for the user as a string if present.

### getFirstName()
```
public string|null getFirstName()
```
Returns the `first_name` property for the user as a string if present.

### getMiddleName()
```
public string|null getMiddleName()
```
Returns the `middle_name` property for the user as a string if present.

### getLastName()
```
public string|null getLastName()
```
Returns the `last_name` property for the user as a string if present.

### getLink()
```
public string|null getLink()
```
Returns the `link` property for the user as a string if present.

### getBirthday()
```
public \Facebook\GraphNodes\Birthday|null getBirthday()
```
Returns the `birthday` property for the user as a [`Facebook\GraphNodes\Birthday`](Birthday.md) if present.

### getLocation()
```
public Facebook\GraphNodes\GraphPage|null getLocation()
```
Returns the `location` property for the user as a `Facebook\GraphNodes\GraphPage` if present.

### getHometown()
```
public Facebook\GraphNodes\GraphPage|null getHometown()
```
Returns the `hometown` property for the user as a `Facebook\GraphNodes\GraphPage` if present.

### getSignificantOther()
```
public Facebook\GraphNodes\GraphUser|null getHometown()
```
Returns the `significant_other` property for the user as a `Facebook\GraphNodes\GraphUser` if present.

## GraphPage Instance Methods

The `GraphPage` collection represents a [Page](https://developers.facebook.com/docs/graph-api/reference/page) Graph node.

### Auto-cast properties

The following properties on the `GraphPage` collection will get automatically cast as `GraphNode` subtypes:

| Property  | GraphNode subtype |
| ------------- | ------------- |
| `best_page` | [`Facebook\\GraphNodes\\GraphPage`](#page-instance-methods)  |
| `global_brand_parent_page`  | [`Facebook\\GraphNodes\\GraphPage`](#page-instance-methods) |
| `location`  | [`Facebook\\GraphNodes\\GraphLocation`](#location-instance-methods)  |


All getter methods return `null` if the property does not exist on the node.

### getId()
```
public string|null getId()
```
Returns the `id` property for the page as a string if present.

### getName()
```
public string|null getName()
```
Returns the `name` property for the page as a string if present.

### getCategory()
```
public string|null getCategory()
```
Returns the `category` property for the page as a string if present.

### getBestPage()
```
public Facebook\GraphNodes\GraphPage|null getBestPage()
```
Returns the `best_page` property for the page as a `Facebook\GraphNodes\GraphPage` if present.

### getGlobalBrandParentPage()
```
public Facebook\GraphNodes\GraphPage|null getGlobalBrandParentPage()
```
Returns the `global_brand_parent_page` property for the page as a `Facebook\GraphNodes\GraphPage` if present.

### getLocation()
```
public Facebook\GraphNodes\GraphLocation|null getLocation()
```
Returns the `location` property for the page as a `Facebook\GraphNodes\GraphLocation` if present.

### getAccessToken()
```
public string|null getAccessToken()
```
Returns the `access_token` property for the page if present. (Only available in the `/me/accounts` context.)

### getPerms()
```
public array|null getAccessToken()
```
Returns the `perms` property for the page as an `array` if present. (Only available in the `/me/accounts` context.)

## GraphAlbum Instance Methods

The `GraphAlbum` collection represents an [Album](https://developers.facebook.com/docs/graph-api/reference/album) Graph node.

### Auto-cast properties

The following properties on the `GraphAlbum` collection will get automatically cast as `GraphNode` subtypes:

| Property  | GraphNode subtype |
| ------------- | ------------- |
| `from` | [`Facebook\\GraphNodes\\GraphUser`](#user-instance-methods)  |
| `place` | [`Facebook\\GraphNodes\\GraphPage`](#page-instance-methods) |

All getter methods return `null` if the property does not exist on the node.

### getId()
```
public string|null getId()
```
Returns the `id` property for the album as a string if present.

### getName()
```
public string|null getName()
```
Returns the `name` property for the album as a string if present.

### getCanUpload()
```
public boolean|null getCanUpload()
```
Returns the `can_upload` property for the album as a boolean if present.

### getCount()
```
public int|null getCount()
```
Returns the `count` property for the album as an integer if present.

### getCoverPhoto()
```
public string|null getCoverPhoto()
```
Returns the `cover_photo` property for the album as a string if present.

### getCreatedTime()
```
public \DateTime|null getCreatedTime()
```
Returns the `created_time` property for the album as a `\DateTime` if present.

### getUpdatedTime()
```
public \DateTime|null getUpdatedTime()
```
Returns the `updated_time` property for the album as a `\DateTime` if present.

### getDescription()
```
public string|null getDescription()
```
Returns the `description` property for the album as a string if present.

### getFrom()
```
public Facebook\GraphNodes\GraphUser|null getFrom()
```
Returns the `from` property for the album as a `Facebook\GraphNodes\GraphUser` if present.

### getPlace()
```
public Facebook\GraphNodes\GraphPage|null getPlace()
```
Returns the `place` property for the album as a `Facebook\GraphNodes\GraphPage` if present.

### getLink()
```
public string|null getLink()
```
Returns the `link` property for the album as a string if present.

### getLocation()
```
public Facebook\GraphNodes\GraphNode|string|null getLocation()
```
Returns the `location` property for the album as a `Facebook\GraphNodes\GraphNode` or string if present.

### getPrivacy()
```
public string|null getPrivacy()
```
Returns the `privacy` property for the album as a string if present.

### getType()
```
public string|null getType()
```
Returns the `type` property for the album as a string (`profile`, `mobile`, `wall`, `normal` or `album`) if present.

## GraphLocation Instance Methods

All getter methods return `null` if the property does not exist on the node.

### getStreet()
```
public string|null getStreet()
```
Returns the `street` property for the location as a string if present.

### getCity()
```
public string|null getCity()
```
Returns the `city` property for the location as a string if present.

### getCountry()
```
public string|null getCountry()
```
Returns the `country` property for the location as a string if present.

### getZip()
```
public string|null getZip()
```
Returns the `zip` property for the location as a string if present.

### getLatitude()
```
public float|null getLatitude()
```
Returns the `latitude` property for the location as a float if present.

### getLongitude()
```
public float|null getLongitude()
```
Returns the `longitude` property for the location as a float if present.

## GraphPicture Instance Methods

All getter methods return `null` if the property does not exist on the node.

### getUrl()
```
public string|null getUrl()
```
Returns the `url` property for the picture as a string if present.

## GraphAchievement Instance Methods

All getter methods return `null` if the property does not exist on the node.

### getId()
```
public string|null getId()
```
Returns the `id` property for the achievement as a string if present.

## GraphEvent Instance Methods

All getter methods return `null` if the property does not exist on the node.


### getId()
```
public string|null getId()
```
Returns the `id` property (The event ID) for the event as a string if present.

### getCover()
```
public GraphCoverPhoto|null getCover()
```
Returns the `cover` property (Cover picture) for the event as a GraphCoverPhoto if present.

### getDescription()
```
public string|null getDescription()
```
Returns the `description` property (Long-form description) for the event as a string if present.

### getEndTime()
```
public DateTime|null getEndTime()
```
Returns the `end_time` property (End time, if one has been set) for the event as a DateTime if present.

### getIsDateOnly()
```
public bool|null getIsDateOnly()
```
Returns the `is_date_only` property (Whether the event only has a date specified, but no time) for the event as a bool if present.

### getName()
```
public string|null getName()
```
Returns the `name` property (Event name) for the event as a string if present.

### getOwner()
```
public GraphNode|null getOwner()
```
Returns the `owner` property (The profile that created the event) for the event as a GraphNode if present.

### getParentGroup()
```
public GraphGroup|null getParentGroup()
```
Returns the `parent_group` property (The group the event belongs to) for the event as a GraphGroup if present.

### getPlace()
```
public GraphPage|null getPlace()
```
Returns the `place` property (Event Place information) for the event as a GraphPage if present.

### getPrivacy()
```
public string|null getPrivacy()
```
Returns the `privacy` property (Who can see the event) for the event as a string if present.

### getStartTime()
```
public DateTime|null getStartTime()
```
Returns the `start_time` property (Start time) for the event as a DateTime if present.

### getTicketUri()
```
public string|null getTicketUri()
```
Returns the `ticket_uri` property (The link users can visit to buy a ticket to this event) for the event as a string if present.

### getTimezone()
```
public string|null getTimezone()
```
Returns the `timezone` property (Timezone) for the event as a string if present.

### getUpdatedTime()
```
public DateTime|null getUpdatedTime()
```
Returns the `updated_time` property (Last update time) for the event as a DateTime if present.

### getPicture()
```
public GraphPicture|null getPicture()
```
Returns the `picture` property (Event picture) for the event as a GraphPicture if present.

### getAttendingCount()
```
public int|null getAttendingCount()
```
Returns the `attending_count` property (Number of people attending the event) for the event as a int if present.

### getDeclinedCount()
```
public int|null getDeclinedCount()
```
Returns the `declined_count` property (Number of people who declined the event) for the event as a int if present.

### getMaybeCount()
```
public int|null getMaybeCount()
```
Returns the `maybe_count` property (Number of people who maybe going to the event) for the event as a int if present.

### getNoreplyCount()
```
public int|null getNoreplyCount()
```
Returns the `noreply_count` property (Number of people who did not reply to the event) for the event as a int if present.

### getInvitedCount()
```
public int|null getInvitedCount()
```
Returns the `invited_count` property (Number of people invited to the event) for the event as a int if present.

## GraphGroup Instance Methods

All getter methods return `null` if the field does not exist on the node.

### getId()
```
public string|null getId()
```
Returns the `id` field (The Group ID) for the group as a string if present.

### getCover()
```
public GraphCoverPhoto|null getCover()
```
Returns the `cover` field (The cover photo of the Group) for the group as a GraphCoverPhoto if present.

### getDescription()
```
public string|null getDescription()
```
Returns the `description` field (A brief description of the Group) for the group as a string if present.

### getEmail()
```
public string|null getEmail()
```
Returns the `email` field (The email address to upload content to the Group. Only current members of the Group can use this) for the group as a string if present.

### getIcon()
```
public string|null getIcon()
```
Returns the `icon` field (The URL for the Group's icon) for the group as a string if present.

### getLink()
```
public string|null getLink()
```
Returns the `link` field (The Group's website) for the group as a string if present.

### getName()
```
public string|null getName()
```
Returns the `name` field (The name of the Group) for the group as a string if present.

### getMemberRequestCount()
```
public int|null getMemberRequestCount()
```
Returns the `member_request_count` field (Number of people asking to join the group.) for the group as a int if present.

### getOwner()
```
public GraphNode|null getOwner()
```
Returns the `owner` field (The profile that created this Group) for the group as a GraphNode if present.

### getParent()
```
public GraphNode|null getParent()
```
Returns the `parent` field (The parent Group of this Group, if it exists) for the group as a GraphNode if present.

### getPrivacy()
```
public string|null getPrivacy()
```
Returns the `privacy` field (The privacy setting of the Group) for the group as a string if present.

### getUpdatedTime()
```
public DateTime|null getUpdatedTime()
```
Returns the `updated_time` field (The last time the Group was updated (this includes changes in the Group's properties and changes in posts and comments if user can see them)) for the group as a DateTime if present.

### getVenue()
```
public GraphLocation|null getVenue()
```
Returns the `venue` field (The location for the Group) for the group as a GraphLocation if present.
