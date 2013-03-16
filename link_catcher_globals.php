<?php

// https://github.com/mynetx/codebird-php
require_once ('codebird.php');
// create app, get keys and tokens: https://dev.twitter.com/docs/auth/tokens-devtwittercom
Codebird::setConsumerKey('', ''); 
$cb = Codebird::getInstance();
$cb->setToken('', '');

// local data storage dir
$datapath = '/opt/link_catcher_data/';


?>
