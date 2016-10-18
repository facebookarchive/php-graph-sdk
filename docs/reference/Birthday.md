# Birthday for the Facebook SDK for PHP

Extends `\DateTime` and represents a user's birthday returned from the Graph API which can be returned omitting certain information.

Users may opt not to share birth day or month, or may not share birth year. Possible returns:

* MM/DD/YYYY
* MM/DD
* YYYY

## Facebook\GraphNodes\Birthday

After retrieving a GraphUser from the Graph API, the `getBirthday()` method will return the birthday in the form of a `Facebook\GraphNodes\Birthday` entity which indicates which aspects of the birthday the user opted to share.

The `Facebook\GraphNodes\Birthday` entity extends `DateTime` so `format` may be used to present the information appropriately depending on what information it contains.

Usage:

```php
$fb = new Facebook\Facebook(\* *\);
// Returns a `Facebook\FacebookResponse` object
$response = $fb->get('/me');

// Get the response typed as a GraphUser
$user = $response->getGraphUser();

// Gets birthday value, assume Graph return was format MM/DD
$birthday = $user->getBirthday();

var_dump($birthday);
// class Facebook\GraphNodes\Birthday ...

var_dump($birthday->hasDate());
// true

var_dump($birthday->hasYear());
// false

var_dump($birthday->format('m/d'));
// 03/21
```

## Instance Methods

### hasDate()
```php
public boolean hasDate()
```
Returns whether or not the birthday object contains the day and month of birth.

### hasYear()
```php
public boolean hasYear()
```
Returns whether or not the birthday object contains the year of birth.
