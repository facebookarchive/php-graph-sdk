<?php
/**
 * 
 * Facebook PHP SDK v4 login / logout / get profile demo script
 * 
 * This is an extension to the provided "quick start" examples to allow a better understanding of the API.
 * 
 * Set up:
 * 
 *  - Download and extract the latest release of SDK (https://github.com/facebook/facebook-php-sdk-v4/releases)
 *    on a public folder of your web server.
 * 
 *  - Create a subfolder (e.g. 'examples') and place this script there
 * 
 *  - Before running this example you must install the vendor dependencies using composer (http://getcomposer.org)
 * 
 *    To do so, you can run these commands on the SDK deployment's main directory (e.g. facebook-php-sdk-v4):
 * 
 *       curl -sS https://getcomposer.org/installer | php
 * 
 *       php composer.phar install
 * 
 *  - Finally: fill the app id, secret and login, logout urls below. 
 * 
 * Don't forget to allow myappdomain.com on your facebook app settings page (You'll find a link to it on 
 * http://developers.facebook.com/apps where you can also create a new app and find the app's id and secret).
 * 
 * Usage:
 * 
 *  - Point your browser to http://whatever.myappdomain.com/path/to/fbsdkexample.php
 * 
 * PHP >= 5.4 IS REQUIRED TO RUN VERSION 4.X.X OF Facebook PHP SDK
 * 
 */
    
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;

session_start();

/* CHECK THIS PATH IS CORRECT */
require_once '../vendor/autoload.php';

/* 
 * EDIT THE FOLLOWING 4 FIELDS BEFORE RUNNING THIS SCRIPT 
 *     - myappdomain.com MUST BE ALLOWED on the facebbok app settings page 
 *     (That's https://developers.facebook.com/apps/{$appId}/settings/)
 */

// your login url (in this example fbsdkexample.php URL)
$loginRedirectUrl = 'http://whatever.myappdomain.com/path/to/fbsdkexample.php';
// your logout url (in this example fbsdkexample.php?logout=whatever URL)
$logoutRedirectUrl = 'http://whatever.myappdomain.com/path/to/fbsdkexample.php?logout=true';
// your app id
$appId = '';
// your app secret
$appSecret = '';

FacebookSession::setDefaultApplication($appId, $appSecret);
$out = '';

if (isset($_GET['logout'])){
    
    // New session
    $_SESSION = array();
    session_regenerate_id(true);
    
}

$helper = new FacebookRedirectLoginHelper($loginRedirectUrl);

try {

    if (isset($_SESSION['fb_token'])){

        $session = new FacebookSession($_SESSION['fb_token']);

    } else {

        $session = $helper->getSessionFromRedirect();

    }

    if (isset($session)) {

        $_SESSION['fb_token'] = $session->getToken();

        // Logged in
        $request = new FacebookRequest($session, 'GET', '/me');
        $response = $request->execute();
        $graphObject = $response->getGraphObject();

        $out = "<p><a href=".$helper->getLogoutUrl($session, $logoutRedirectUrl).">logout</a></p>";
        $out .= "<p>ME:</p><p><pre><code>" . var_export($graphObject, true)."</code></pre></p>";

    } else {

        // New session
        $_SESSION = array();
        session_regenerate_id(true);
        
        $out = "<p><a href="
            . $helper->getLoginUrl(
                array(
                    'scope' => 
                        'email' // scope contains a comma separated list of permissions to be requested
                    )
                )
            . ">login</a></p>";

    }

} catch(FacebookRequestException $ex) {

    // When Facebook returns an error
    
    $out = "<p>FacebookRequestException: ".$ex->getMessage()."</p>";
    
    // New session
    $_SESSION = array();
    session_regenerate_id(true);
    
    $out .= "<p><a href="
            . $helper->getLoginUrl(
                array(
                    'scope' => 
                        'email' // scope contains a comma separated list of permissions to be requested
                    )
                )
            . ">login</a></p>";

} catch(Exception $ex) {

    // When validation fails or other local issues
    
    $out = "<p>Exception ".$ex->getMessage()."</p>";
    
    // New session
    $_SESSION = array();
    session_regenerate_id(true);
    
    $out .= "<p><a href="
            . $helper->getLoginUrl(
                array(
                    'scope' => 
                        'email' // scope contains a comma separated list of permissions to be requested
                    )
                )
            . ">login</a></p>";

}
?><!doctype html>
<html>
<head>
    <title>
        facebook login / logout / get profile test page
    </title>
</head>
<body>
<?=$out?>
</body>
</html>
