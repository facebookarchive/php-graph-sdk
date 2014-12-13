<card>
# The cryptographically secure pseudo-random string generator interface for the Facebook SDK for PHP

The cryptographically secure pseudo-random string generator interface allows you to overwrite the default CSPRSG logic by coding to the `Facebook\PseudoRandomString\PseudoRandomStringGeneratorInterface`.
</card>

<card>
## Facebook\PseudoRandomString\PseudoRandomStringGeneratorInterface {#overview}

By default the SDK will attempt to generate a cryptographically secure random string using a number of methods. If a cryptographically secure method is not detected, a `Facebook\Exceptions\FacebookSDKException` will be thrown.

If your hosting environment does not support any of the CSPRSG methods used by the SDK or if you have preferred CSPRSG, you can provide your own CSPRSG to the SDK using this interface.

> **Caution:** Although it is popular to use `rand()`, `mt_rand()` and `uniqid()` to generate random strings in PHP, these methods are not cryptographically secure. Since the pseudo-random string generator is used to validate against Cross-Site Request Forgery (CSRF) attacks, the random strings _must_ be cryptographically secure. Only overwrite this functionality if your custom pseudo-random string generator is a cryptographically strong one.

An example of implementing a custom CSPRSG:

~~~~
use Facebook\PseudoRandomString\PseudoRandomStringGeneratorInterface;

class MyCustomPseudoRandomStringGenerator implements PseudoRandomStringGeneratorInterface
{
  /**
   * @inheritdoc
   */
  public function getPseudoRandomString($length)
  {
    $randomString = '';

    // . . . Do CSPRSG logic here . . .

    return $randomString;
  }
}
~~~~

To enable your custom CSPRSG implementation in the SDK, you can set an instance of the generator to the `pseudo_random_string_generator` config of the `Facebook\Facebook` super service.

~~~~
$fb = new Facebook\Facebook([
  // . . .
  'pseudo_random_string_generator' => new MyCustomPseudoRandomStringGenerator(),
  // . . .
  ]);
~~~~

Alternatively, if you're working with the `Facebook\Helpers\FacebookRedirectLoginHelper` directly, you can inject your custom generator via the constructor.

~~~~
use Facebook\Helpers\FacebookRedirectLoginHelper;

$myPseudoRandomStringGenerator = new MyCustomPseudoRandomStringGenerator();
$helper = new FacebookRedirectLoginHelper($fbApp, null, null, $myPseudoRandomStringGenerator);
~~~~
</card>

<card>
## Method Reference {#method-reference}

### getPseudoRandomString() {#get-pseudo-random-string}
~~~~
public string getPseudoRandomString(int $length)
~~~~
Returns a cryptographically secure pseudo-random string that is `$length` characters long.
</card>
