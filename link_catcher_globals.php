<?php
// https://github.com/mynetx/codebird-php
require_once ('codebird.php');
// create app, get keys and tokens: https://dev.twitter.com/docs/auth/tokens-devtwittercom
Codebird::setConsumerKey('', ''); // EDIT THIS
$cb = Codebird::getInstance();
$cb->setToken('', ''); // EDIT THIS
// local data storage dir
$datapath = '/opt/link_catcher_data/'; // EDIT THIS
// your twitter handle
$global_own_user = 'dummy'; // EDIT THIS
?>
