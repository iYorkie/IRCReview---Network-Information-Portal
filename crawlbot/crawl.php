<?php
// To add servers, create a file named 'servers' in the working directory,
// (Usually '~/crawl/servers') and separate the servers with a new line.

//Initial directory to place crawl data: (Please include a backslash at the end)
// We wont need this as everything will be stored in mysql! Ill keep it here for now though (J0rd4n)
$initialDir = "/var/www/crawldata/";

// You should know what to do here... >_>
$ident = 'IRCReview';
$realname = 'IRCReview';
$nickname = 'IRCREVIEW';
$quitmsg = 'IRCReview Information Collection Bot!';
// MySQL Details
$mysqlhost = 'localhost';
$mysqluser = 'userhere';
$mysqlpass = 'userpasshere';
$mysqldatabase = 'databasehere';
// Some stuff we might not need, but i'll put it in there
// Call back url in case we add some sort of reaction everytime the crawler activates?
// Possibly a little message on the site saying how long ago everything was checked
$callbackurl = 'callbackurlhere.php';



// End configuration. Do not edit below this line.
require("./main.php");
?>
