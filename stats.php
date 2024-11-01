<?php
require_once(dirname(__FILE__).'/common.inc.php');
require_once(dirname(__FILE__).'/geoipregionvars.php');

if (empty($wp)) {
	wp();
}

// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");
header("Content-type: text/javascript; charset=utf-8");

global $wpgm_table_name;
if(!empty($_COOKIE['GeoInfo']) && !isset($_COOKIE['GeoInfoAdded'])){
	$ua = SERVER_VAR('HTTP_USER_AGENT');
	$ip = ip2long(SERVER_VAR('REMOTE_ADDR'));
	
	$safe_cookie = addslashes($_COOKIE['GeoInfo']);
	list($lat, $lng, $city, $country, $region_code, $country_code) = explode('|', $safe_cookie);
	
	$region = $GEOIP_REGION_NAME[$country_code][$region_code];
	$hash = md5($city.$region.$country);
	setcookie('GeoInfoAdded', $wpdb->query("INSERT INTO `$wpgm_table_name` (`ip`, `lat`, `lng`, `city`, `region`, `country`, `country_code`, `ua`, `hash`) VALUES ($ip, $lat, $lng, '$city', '$region', '$country', '$country_code', '$ua', '$hash')"));
}
?>
