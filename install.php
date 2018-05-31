<?php
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding("UTF-8");

error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once('config.php');
include_once(@PATH . 'core.php');

echo '<pre>';

echo 'set webhook...' , PHP_EOL;

$set = bot_query('setWebhook', array(
	'url' => 'https://'. $_SERVER['SERVER_NAME'] . str_replace(array("\\", $_SERVER['DOCUMENT_ROOT']), array('/',''), @PATH) .'bot.php',
	'max_connections' => 1,
	'allowed_updates'	=> json_encode(array('message')),
));
if( $set === false ){
	echo 'Error' , PHP_EOL;
} else {
	echo 'Result:' , PHP_EOL;
	var_dump($set);
	echo PHP_EOL;
}

echo 'get webhook info...' , PHP_EOL;

$get = bot_query('getWebhookInfo');
if( $get === false ){
	echo 'Error' , PHP_EOL;
} else {
	echo 'Result:' , PHP_EOL;
	var_dump($get);
	echo PHP_EOL;
}

?>