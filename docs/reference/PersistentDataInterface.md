<card>
# The persistent data handler interface for the Facebook SDK for PHP

The persistent data handler interface stores values in a persistent data store. By default the SDK for PHP uses native PHP sessions to store the persistent data. You can overwrite this behavior by coding to the `Facebook\PersistentData\PersistentDataInterface`.
</card>

<card>
## Facebook\PersistentData\PersistentDataInterface {#overview}

If you're using a web framework that handles persistent data for you, you might want to code a custom persistent data handler to ensure that your persistent storage is being handled consistently.

For example if you are using Laravel, a custom handler might look like this:

~~~~
use Facebook\PersistentData\PersistentDataInterface;

class MyLaravelPersistentDataHandler implements PersistentDataInterface
{
  /**
   * @var string Prefix to use for session variables.
   */
  protected $sessionPrefix = 'FBRLH_';

  /**
   * @inheritdoc
   */
  public function get($key)
  {
    return \Session::get($this->sessionPrefix . $key);
  }

  /**
   * @inheritdoc
   */
  public function set($key, $value)
  {
    \Session::put($this->sessionPrefix . $key, $value);
  }
}
~~~~

To enable your custom persistent data handler implementation in the SDK, you can set an instance of the handler to the `persistent_data_handler` config of the `Facebook\Facebook` super service.

~~~~
$fb = new Facebook\Facebook([
  // . . .
  'persistent_data_handler' => new MyLaravelPersistentDataHandler(),
  // . . .
  ]);
~~~~

Alternatively, if you're working with the `Facebook\Helpers\FacebookRedirectLoginHelper` directly, you can inject your custom handler via the constructor.

~~~~
use Facebook\Helpers\FacebookRedirectLoginHelper;

$myPersistentDataHandler = new MyLaravelPersistentDataHandler();
$helper = new FacebookRedirectLoginHelper($fbApp, $myPersistentDataHandler);
~~~~
</card>

<card>
## Method Reference {#method-reference}

### get() {#get}
~~~~
public mixed get(string $key)
~~~~
Returns a value from the persistent data store or `null` if the value does not exist.
</card>

<card>
### set() {#set}
~~~~
public void set(string $key, mixed $value)
~~~~
Sets a value to the persistent data store.
</card>
