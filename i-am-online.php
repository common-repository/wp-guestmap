<?php
require_once(dirname(__FILE__).'/common.inc.php');
require_once(dirname(__FILE__).'/geoipregionvars.php');

header("Content-type: text/xml; charset=utf-8");

if(empty($_COOKIE['GeoInfo'])) exit('<?xml version="1.0" encoding="UTF-8"?><locations />');

if (empty($wp)) {
	wp();
}

$wpgm_online_users = unserialize(get_option('wpgm_online_users'));
$wpgm_tracker_timeout = intval(get_option('wpgm_tracker_timeout'));
$wpgm_tracker_timeout = $wpgm_tracker_timeout? $wpgm_tracker_timeout : 900;

$wpgm_online_users_new = array();
 
$now = time();

$hash = md5(SERVER_VAR('HTTP_USER_AGENT').SERVER_VAR('REMOTE_ADDR'));

$safe_cookie = addslashes($_COOKIE['GeoInfo']);
list($lat, $lng, $city, $country, $region_code, $country_code) = explode('|', $safe_cookie);

$wpgm_online_users[$hash] = array('lat'=> $lat, 'lng'=> $lng, 'city'=> $city, 'region'=> $GEOIP_REGION_NAME[$country_code][$region_code], 'country'=> $country, 'country_code'=> $country_code, 'time'=> $now);


echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<!-- generator="WP GuestMap" -->';
echo '<locations>';

foreach($wpgm_online_users as $k => $user){
	if($now-$user['time']>$wpgm_tracker_timeout) continue;
	if($hash != $k) echo '<location lat="'.$user['lat'].'" lng="'.$user['lng'].'" city="'.xmlspecialchars($user['city']).'" region="'.xmlspecialchars($user['region']).'" country="'.xmlspecialchars($user['country']).'" country_code="'.$user['country_code'].'" />';
	$wpgm_online_users_new[$k] = $user;
}

echo '</locations>';

update_option('wpgm_online_users', serialize($wpgm_online_users_new));
?>