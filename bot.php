<?php
header("HTTP/1.1 200 OK");
header('Content-Type: application/json; charset=utf-8');
mb_internal_encoding('UTF-8');

$update = @json_decode(@file_get_contents("php://input"));
if( empty($update->message) ) exit;

include_once('config.php');
include_once(@PATH .'core.php');

if( !defined('CHAT_ID') ){
	bot_query('sendMessage', array(
		'chat_id' => $update->message->chat->id,
		'text' => $update->message->chat->id
	));
	exit;
}

include_once(PATH .'api.php');

$bot = new api($update);

if( preg_match("'^/admins(@". preg_quote($bot->username, "'") .")?$'iu", $bot->text) ){
	$bot->upd_admins();
} elseif( preg_match("'^/reset(@".	preg_quote($bot->username, "'") .")?$'iu", $bot->text) ){
	$bot->del_items();
} elseif( preg_match("'^/open(@".		preg_quote($bot->username, "'") .")?$'iu", $bot->text) ){
	$bot->mega_open();
} elseif( preg_match("'^/close(@".	preg_quote($bot->username, "'") .")?$'iu", $bot->text) ){
	$bot->mega_close();
} elseif( preg_match("'^/info(@".		preg_quote($bot->username, "'") .")?$'iu", $bot->text) ){
	$bot->send_info();
} elseif( preg_match("'^/list(@".		preg_quote($bot->username, "'") .")?$'iu", $bot->text) ){
	$bot->send_list();
} elseif( preg_match("'^/post(@".		preg_quote($bot->username, "'") .")?$'iu", $bot->text) ){
	$bot->send_post();
} elseif( preg_match("'^/head(\s+(.+))?$'isu",	$bot->text, $m) ){
	$bot->set_head(empty($m['2']) ? false : trim(strval($m['2'])));
} elseif( preg_match("'^/foot(\s+(.+))?$'isu",	$bot->text, $m) ){
	$bot->set_foot(empty($m['2']) ? false : trim(strval($m['2'])));
} elseif( preg_match("'^/pool(\s+(\d+))?$'iu",	$bot->text, $m) ){
	$bot->set_pool(empty($m['2']) ? false : intval($m['2']));
} elseif( preg_match("'^/step(\s+(\d+))?$'iu",	$bot->text, $m) ){
	$bot->set_step(empty($m['2']) ? false : intval($m['2']));
} elseif( preg_match("'^/min(\s+(\d+))?$'iu",		$bot->text, $m) ){
	$bot->set_min(empty($m['2']) ? false : intval($m['2']));
} elseif( preg_match("'^/max(\s+(\d+))?$'iu",		$bot->text, $m) ){
	$bot->set_max(empty($m['2']) ? false : intval($m['2']));
} elseif( preg_match("'^/len(\s+(\d+))?$'iu",		$bot->text, $m) ){
	$bot->set_len(empty($m['2']) ? false : intval($m['2']));
} elseif( preg_match("'^/del\s+@?([_a-z0-9]{5,32})$'iu", $bot->text, $m) ){
	$bot->del_item(trim(strval($m['1'])));
} elseif( preg_match("'^/add\s+@?([_a-z0-9]{5,32})\s+([^@]+)?$'isu", $bot->text, $m) ){
	$bot->add_item($m);
}

?>