<?php
class api {
var $token = null;
var $username = null;

var $message = null;
var $user_id = null;
var $chat_id = null;
var $chat_type = null;
var $text = '';

var $data = null;
var $hash = null;

var $is_admin = false;

	function __construct(&$update){
		$this->token = @BOT_TOKEN;
		$this->username = @BOT_USERNAME;
		$this->message = $update->message;
		$this->user_id = $this->message->from->id;
		$this->chat_id = $this->message->chat->id;
		$this->chat_type = $this->message->chat->type;
		$this->text = empty($this->message->text) ? '' : trim(strval($this->message->text));

		unset($update);

		$this->data = json_decode(file_get_contents(@FILE_DATA));
		$this->hash = md5(serialize($this->data));

		if( empty($this->data) ) $this->data = (object) null;
		if( empty($this->data->items) ) $this->data->items = array();
		if( empty($this->data->admins) ) $this->data->admins = array();

		$this->is_admin = in_array($this->user_id, $this->data->admins);
	}

	# ACTIONS

	function upd_admins(){
		if( !$this->is_admin && !empty($this->data->admins) ) return;
		if( ( $admins = $this->query('getChatAdministrators', array('chat_id' => @CHAT_ID)) ) === false ) return;

		$ids = array();
		foreach( $admins as $admin ) $ids[] = $admin->user->id;
		$this->data->admins = $ids;
		$this->send_message('Admins Update', $this->chat_id);
	}

	function del_items(){
		if( !$this->is_admin ) return;
		$this->data->items = array();
		$this->send_message('Items Deleted', $this->chat_id);
	}

	function mega_open(){
		if( !$this->is_admin || ( isset($this->data->open) ) ) return;
		$this->data->open = true;
		$text = 'Mega Open';
		$this->send_message($text, @CHAT_ID);
		if( $this->chat_id != @CHAT_ID ) $this->send_message($text, $this->chat_id);
	}

	function mega_close(){
		if( !$this->is_admin || ( !isset($this->data->open) ) ) return;
		unset($this->data->open);
		$text = 'Mega Close';
		$this->send_message($text, @CHAT_ID);
		if( $this->chat_id != @CHAT_ID ) $this->send_message($text, $this->chat_id);
	}

	function send_info(){
		if( !$this->is_admin ) return;
		$text = array();
		if( !empty($this->data->head) ) $text[] = 'Head:'. PHP_EOL . $this->data->head;
		if( !empty($this->data->foot) ) $text[] = 'Foot:'. PHP_EOL . $this->data->foot;
		if( !empty($this->data->pool) ) $text[] = 'Pool: '. $this->data->pool;
		if( !empty($this->data->step) ) $text[] = 'Step: '. $this->data->step;
		if( !empty($this->data->min) ) $text[] = 'Min: '. $this->data->min;
		if( !empty($this->data->max) ) $text[] = 'Max: '. $this->data->max;
		if( !empty($this->data->len) ) $text[] = 'Len: '. $this->data->len;
		$text[] = 'Items: '. count($this->data->items);
		$this->send_message($text, $this->chat_id);
	}

	function send_list(){
		if( !$this->is_admin ) return;
		if( empty($this->data->items) ) return;

		$this->sort_items();

		$n = 1;
		$text = array();
		$count = count($this->data->items);

		foreach( $this->data->items as $i => $item ){
			$text[] = $item->members .' @'. $item->username .' '. $item->text;
			if( isset($this->data->step) && ( !($n % $this->data->step) ) && ( $n < $count ) ) $text[] = '';
			$n++;
		}

		$this->send_message($text, $this->chat_id);
	}

	function send_post(){
		if( !$this->is_admin ) return;
		if( empty($this->data->items) ) return;

		$this->sort_items();

		$n = 1;
		$text = array();
		$count = count($this->data->items);

		if( isset($this->data->head) ) array_push($text, $this->data->head, '');
		foreach( $this->data->items as $i => $item ){
			$text[] = ' @'. $item->username .' '. $item->text;
			if( isset($this->data->step) && ( !($n % $this->data->step) ) && ( $n < $count ) ) $text[] = '';
			$n++;
		}
		if( isset($this->data->foot) ) array_push($text, '', $this->data->foot);

		$this->send_message($text, @CHAT_ID);
		if( $this->chat_id != @CHAT_ID ) $this->send_message($text, $this->chat_id);
	}

	function set_head($val){
		if( !$this->is_admin ) return;
		if( empty($val) ){
			if( isset($this->data->head) ) unset($this->data->head);
			$this->send_message('Head Unset', $this->chat_id);
		} else {
			$this->data->head = $val;
			$this->send_message('Head Set', $this->chat_id);
		}
	}

	function set_foot($val){
		if( !$this->is_admin ) return;
		if( empty($val) ){
			if( isset($this->data->foot) ) unset($this->data->foot);
			$this->send_message('Foot Unset', $this->chat_id);
		} else {
			$this->data->foot = $val;
			$this->send_message('Foot Set', $this->chat_id);
		}
	}

	function set_pool($val){
		if( !$this->is_admin ) return;
		if( empty($val) ){
			if( isset($this->data->pool) ) unset($this->data->pool);
			$this->send_message('Pool Unset', $this->chat_id);
		} else {
			$this->data->pool = $val;
			$this->send_message('Pool Set', $this->chat_id);
		}
	}

	function set_step($val){
		if( !$this->is_admin ) return;
		if( empty($val) ){
			if( isset($this->data->step) ) unset($this->data->step);
			$this->send_message('Step Unset', $this->chat_id);
		} else {
			$this->data->step = $val;
			$this->send_message('Step Set', $this->chat_id);
		}
	}

	function set_min($val){
		if( !$this->is_admin ) return;
		if( isset($bod->data->max) && ( $val >= $this->data->max ) ){
			$this->send_message('Min can`t be more or equal Max.', $this->chat_id);
		} elseif( empty($val) ){
			if( isset($this->data->min) ) unset($this->data->min);
			$this->send_message('Min Unset', $this->chat_id);
		} else {
			$this->data->min = $val;
			$this->send_message('Min Set', $this->chat_id);
		}
	}

	function set_max($val){
		if( !$this->is_admin ) return;
		if( isset($bod->data->min) && ( $val <= $this->data->min ) ){
			$this->send_message('Max can`t be less or equal Min.', $this->chat_id);
		} elseif( empty($val) ){
			if( isset($this->data->max) ) unset($this->data->max);
			$this->send_message('Max Unset', $this->chat_id);
		} else {
			$this->data->max = $val;
			$this->send_message('Max Set', $this->chat_id);
		}
	}

	function set_len($val){
		if( !$this->is_admin ) return;
		if( empty($val) ){
			if( isset($this->data->len) ) unset($this->data->len);
			$this->send_message('Len Unset', $this->chat_id);
		} else {
			$this->data->len = $val;
			$this->send_message('Len Set', $this->chat_id);
		}
	}

	function add_item($data){
		if( !$this->is_admin ){
			if( $this->chat_id != @CHAT_ID ) return;
			if( empty($this->data->open) ) return;
			if( isset($this->data->pool) ){
				if( count($this->data->items) >= $this->data->pool ) return;
			}
		}

		$data = (object) array(
			'user_id'		=> $this->user_id,
			'username' 	=> $data['1'],
			'text' 			=> filter_text($data['2']),
		);

		if( isset($this->data->len) ){
			$len = mb_strlen('@'. $data->username .' '. $data->text);
			if( $len > $this->data->len ){
				$this->send_message('Error: @'. $data->username .' item lenght more '. $this->data->len .' char(s)');
				return;
			}
		}

		$key = strtolower($data->username);
		if( isset($this->data->items[$key]) ){
			$item = $this->data->items[$key];
			if( $items->user_id == $this->user_id || $this->is_admin ){
				if( $item->text != $data->text ) $item->text = $data->text;
				$this->data->items[$key] = $item;
				$text = '@'. $item->username .' item updated';
			} else {
				$text = '@'. $item->username .' no access';
			}
		} else {
			$data->members = $this->get_chat_members_count('@'. $data->username);
			if( $data->members === false ){
				$text = 'Error: @'. $data->username .' is wrong username';
			} elseif( isset($this->data->min) && ( $data->members < $this->data->min ) ){
				$text = 'Error: @'. $data->username .' have less '. $this->data->min .' member(s)';
			} elseif( isset($this->data->max) && ( $data->members > $this->data->min ) ){
				$text = 'Error: @'. $data->username .' have more '. $this->data->max .' member(s)';
			} else {
				$this->data->items[$key] = $data;
				$text = '@'. $data->username .' item added';
			}
		}
		$this->send_message($text, @CHAT_ID);
		if( $this->chat_id != @CHAT_ID ) $this->send_message($text, $this->chat_id);
	}

	function item_del($username){
		if( !$this->is_admin ){
			if( $this->chat_id != @CHAT_ID ) return;
			if( empty($this->data->open) ) return;
		}

		$key = strtolower($username);
		if( isset($this->data->items[$key]) ){
			$item = $this->data->items[$key];
			if( $items->user_id == $this->user_id || $this->is_admin ){
				unset($this->data->items[$key]);
				$text = '@'. $item->username .' item deleted';
			} else {
				$text = '@'. $item->username .' no access';
			}
		} else {
			$text = '@'. $username .' not exits';
		}
		$this->send_message($text, @CHAT_ID);
		if( $this->chat_id != @CHAT_ID ) $this->send_message($text, $this->chat_id);
	}

	function sort_items(){
		if( empty($this->data->items) ) return;
		$sort = array();
		foreach( $this->data->items as $i => $item ) $sort[$i] = $item->members;
		array_multisort($sort, SORT_NUMERIC, SORT_DESC, $this->data->items);
	}

	# API
	function query($method, $fields = array()){
		$ch = curl_init('https://api.telegram.org/bot'. $this->token.'/'. $method);
		curl_setopt_array($ch, array(
			CURLOPT_POST => count($fields),
			CURLOPT_POSTFIELDS => $this->query_fields($fields),
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 10
		));
		if( ( $r = curl_exec($ch) ) === false ) $this->set_log('Error CURL ['. $method .'] '. curl_error($ch));
		curl_close($ch);
		if( $r === false ) return false;
		if( ( $r = @json_decode($r) ) === false ) return $this->set_log('Error API ['. $method .'] json decode');
		if( $r->ok === false ) return $this->set_log('Error API ['. $method .'] '. $r->description);
		return $r->result;
	}

	function query_fields($fields){
		$fields = (array) $fields;
		if( !empty($fields) ) foreach( $fields as $k=>$v ) if( is_array($v) ) $fields[$k] = @json_encode($v);
		return http_build_query($fields);
	}

	function get_chat_members_count($chat_id){
		return $this->query('getChatMembersCount', array('chat_id' => $chat_id));
	}

	function send_message($text, $chat_id){
		if( empty($this->chat_id) ) return;
		$this->query('sendMessage', array(
			'chat_id'	=> $chat_id,
			'text'		=> is_array($text) ? implode(PHP_EOL, $text) : $text,
			'disable_web_page_preview' => true,
			'disable_notification' => true,
		));
	}

	function set_log($str){
		file_put_contents(@FILE_LOG, date('d-m-Y H:i:s ') . $str . PHP_EOL, file_exists(@FILE_LOG) ? FILE_APPEND : 0);
		return false;
	}

	function set_dump($var){
		ob_start();
		var_dump($var);
		$this->set_log(ob_get_clean());
	}

	function __destruct(){
		if( empty($this->data) ){
			if( file_exists(@FILE_DATA) ) @unlink(@FILE_DATA);
		} else {
			$hash = md5(serialize($this->data));
			if( $hash != $this->hash ) file_put_contents(@FILE_DATA, json_encode($this->data));
		}
	}
}

?>