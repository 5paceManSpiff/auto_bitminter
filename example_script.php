<?php
/***********************************************************************************
The auto_bitminter.php script has two main functions, generating the cookie file
allowing the user to login, and using the generated cookie file to add new workers
to the BitMinter account.
************************************************************************************/

//requires file in order to instantiate class
require_once('auto_bitminter.php');

//instantiates manager class
$bm = new BitminterManager;

/*
The first step in order to generate the cookie file is to specify the file path
as well as the openid username and password.  The only openid provider that this
library supports is Verisign Labs' "Personal Identity Portal".  Signup only
requires a username and password along with an email address used only for
verification.  Once signed up you can set the password through the Manager object.
If the username and password aren't set, no cookie will be generated.  If the cookie
path aren't set then it will default to 'cookie.txt'.
*/

//the path is reletave to auto_bitminter.php
$bm->setCookiePath('cookie.txt');

//sets user info
$bm->setUsername('name');
$bm->setPassword('pass');
//or
$bm->setUserPass('name', 'pass');


//generates cookie file at cookie path
$bm->generateCookie();

//The only prerequisites for adding a worker is that the cookie path is set correctly
$bm->setCookiePath('cookie.txt');
$bm->addWorker('worker_name', 'worker_pass');
?>