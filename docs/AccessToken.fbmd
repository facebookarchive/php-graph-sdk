<card>
# AccessToken for the Facebook SDK for PHP

Requests to the Graph API need to have an access token sent with them to identify the app, user and/or page that is making the request. The `Facebook\Authentication\AccessToken` entity represents an access token.
</card>

<card>
## Facebook\Authentication\AccessToken {#overview}

Whenever you use the PHP SDK to obtain an access token, the access token will be returned as an instance of `AccessToken`. The `AccessToken` entity contains a number of methods that make it easier to handle access tokens.

### getValue() {#get-value}
~~~~
public string getValue()
~~~~
Returns the access token as a string. The `AccessToken` entity also makes use of the [magic method `__toString()`](http://php.net/manual/en/language.oop5.magic.php#object.tostring) so you can cast an `AccessToken` entity to a string with: `$token = (string) $accessTokenEntity;`

### getExpiresAt() {#get-expires-at}
~~~~
public \DateTime|null getExpiresAt()
~~~~
If the expiration date was provided when the `AccessToken` entity was instantiated, the `getExpiresAt()` method will return the access token expiration date as a [`DateTime` entity](http://php.net/manual/en/class.datetime.php). If the expiration date was not originally provided, the method will return `null`.

### isExpired() {#is-expired}
~~~~
public boolean|null isExpired()
~~~~
If the expiration date was provided when the `AccessToken` entity was instantiated, the `isExpired()` method will return `true` if the access token has expired. If the access token is still active, the method will return `false`. If the expiration date was not
originally provided, the method will return `null`.

### isLongLived() {#is-long-lived}
~~~~
public boolean|null isLongLived()
~~~~
If the expiration date was provided when the `AccessToken` entity was instantiated, the `isLongLived()` method will return `true` if the access token is long-lived. If the token is short-lived, the method will return `false`. If the expiration date was not
originally provided, the method will return `null`. [See more about long-lived and short-lived access tokens](https://developers.facebook.com/docs/facebook-login/access-tokens#extending).

### isAppAccessToken() {#is-app-access-token}
~~~~
public boolean isAppAccessToken()
~~~~
Since app access tokens contain the app secret in plain-text, it's very important that app access tokens aren't used in client-side contexts where someone might be able to grab the app secret. For this reason you should do a check on the access token to ensure it is not an app access token before using it on the client-side. The `isAppAccessToken()` will return `true` if the access token is an app access token and `false` if it is not.

### getAppSecretProof() {#is-app-secret-proof}
~~~~
public string getAppSecretProof(string $appSecret)
~~~~
For better security, all requests to the Graph API should be [signed with an app secret proof](https://developers.facebook.com/docs/graph-api/securing-requests#appsecret_proof) and your app settings should enable the app secret proof requirement for all requests. The PHP SDK will generate the app secret proof for each request automatically, but if you need to generate one, pass your app secret to the `getAppSecretProof()` method and it will return the HMAC hash that is the app secret proof.
</card>

<card>
## Making an entity from a string {#from-string}

If you already have an access token in the form of a string (from a session or database for example), you can make an `AccessToken` entity with it by passing the access token string as the first argument in the `AccessToken` the constructor.

You can optionally pass an expiration date in the form of timestamp as the second argument.

~~~~
$expires = time() + 60 * 60 * 2;
$accessToken = new Facebook\Authentication\AccessToken('{example-access-token}', $expires);
~~~~
</card>
