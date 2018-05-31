<?php
# ФИЛЬТРЫ
function filter_str($s){
	return trim(preg_replace_array(array(
		"'[\x{2010}-\x{2015}\x{2043}\x{2212}\x{23AF}\x{2796}]'u" => '-',
		"'(?<!\pL)(https?://(www\.)?|www\.)'iu"=>' ',
		"'[^\.,:;?!\\\/\-+*$%&()\[\]<>\w\s]'iu"=>' ',
		"'\s+'u"=>' '
	), (string)$s));
}
function filter_text($s){
	$s = mb_ucfirst(preg_replace_array(array(
		"'^[^\pL\pN]+'u"=>'',
		"'[^\pL\pN\.,?!]+$'u"=>''
	), filter_str($s)));
	if( !preg_match("'[\.,?!]$'u", $s) ) $s.= '.';
	return $s;
}
function preg_replace_array(array $array, $s){return preg_replace(array_keys($array), array_values($array), $s);}

# ДРУГОЕ
function plural($n, $f){ return $n%10==1&&$n%100!=11?$f[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$f[1]:$f[2]); }
function mb_ucfirst($s){ return mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1); }
function mb_lcfirst($s){ return mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1); }

function bot_query($method, $fields = array()){
	$ch = curl_init('https://api.telegram.org/bot'. @BOT_TOKEN .'/'. $method);
	curl_setopt_array($ch, array(
		CURLOPT_POST => count($fields),
		CURLOPT_POSTFIELDS => http_build_query($fields),
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_TIMEOUT => 10
	));
	$r = json_decode(curl_exec($ch));
	curl_close($ch);
	return $r;
}

?>