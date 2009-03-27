<?
/**
 * this example tries to get an user's quota from Xuite Photo service.
 */

require_once 'Services_Xuite_Photo.php';

Services_Xuite_Photo::$publicKey = '__PUT_YOUR_PUBLIC_KEY_HERE__';
Services_Xuite_Photo::$privateKey = '__PUT_YOUR_PRIVATE_KEY_HERE__';

# Note: To get auth token, you have do the API authentication process first,
# How to authenticate? http://photo.xuite.net/_dev/xmlrpc/flow
$auth_token = '__PUT_THE_AUTH_TOKEN_HERE__';

# Get the service impl
$xuite_service = Services_Xuite_Photo::getService();
# invokes the getQuota method.
$quota = $xuite_service.getQuota($auth_token);

echo '<p>Used: '.$quota['used'].' KB</p>';
echo '<p>Quota: '.$quota['max'].' KB</p>';
