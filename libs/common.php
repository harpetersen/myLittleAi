<?php
function selfURL() { 
	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
} 
function strleft($s1, $s2) { return substr($s1, 0, strpos($s1, $s2)); }
function stripWwww($s){
}

$parse = parse_url(selfURL());
$_ROOT_URL = $parse['scheme'] . '://' . $parse['host'];
if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $parse['host'])){ // if it's not an IP
	ini_set('session.cookie_domain', '.'.str_replace( 'www.', '',$parse['host']));
}

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once('config.php');
include_once('db.php');

