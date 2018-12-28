<?php
// Create our Application instance.
$facebook = new Facebook(array(
  'appId'  => FACEBOOKAPPID,
  'secret' => FACEBOOKAPPSECRET,
  'cookie' => true,
));

// We may or may not have this data based on a $_GET or $_COOKIE based session.
// If we get a session here, it means we found a correctly signed session using
// the Application Secret only Facebook and the Application know. We dont know
// if it is still valid until we make an API call using the session. A session
// can become invalid if it has already expired (should not be getting the
// session back in this case) or if the user logged out of Facebook.
$fbsession = $facebook->getSession();

$fb = null;
// Session based graph API call.
if ($fbsession) {
  try {
    $uid = $facebook->getUser();
    $fb = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    //d($e);
  }
}