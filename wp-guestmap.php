<?php
/*  Copyright 2007  Jiang Kuan  (email : kuan.jiang@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
Plugin Name: WP GuestMap
Plugin URI: http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/
Description: Add a Google Map widget to your blog, currently four kinds of map are available: Guest Locator, Online Tracker, Stats Map and Weather Map.
Version: 1.8
Author: Jiang Kuan
Author URI: http://blog.codexpress.cn
*/
require_once(dirname(__FILE__).'/common.inc.php');

$wpgm_is_table_created = 0;

$wpgm_api_key = '';
$wpgm_cache_enabled = false;

$wpgm_zoom_level = 3;
$wpgm_map_type = 1;
$wpgm_default_center = '30.581355, 114.335328';

$wpgm_welcome_message = 'You are in %city%, %country%, ain\'t you?';
$wpgm_tracker_info = 'Hi!<br /> We have %online_user_count% friend(s) online!';
$wpgm_tracker_timeout = 900;
$wpgm_tracker_refresh_rate = 60;
$wpgm_online_users = serialize(array());
$wpgm_enable_stats = false;
$wpgm_stats_pagesize = 25;
$wpgm_stats_start_date = '2007-10-01';

$wpgm_table_version = 2;

$wpgm_blog_url = get_bloginfo('wpurl');

add_option('wpgm_api_key', $wpgm_api_key, 'Google Maps API Key');
add_option('wpgm_cache_enabled', $wpgm_cache_enabled, 'Whether you use WP-Cache');

add_option('wpgm_feed_api_key', $wpgm_feed_api_key, 'Required in Weather Map');
add_option('wpgm_welcome_message', $wpgm_welcome_message, 'Required in Guest Locator');
add_option('wpgm_tracker_info', $wpgm_tracker_info, 'Required in Online Tracker');
add_option('wpgm_tracker_timeout', $wpgm_tracker_timeout, 'Required in Online Tracker');
add_option('wpgm_tracker_refresh_rate', $wpgm_tracker_refresh_rate, 'Required in Online Tracker');
add_option('wpgm_online_users', $wpgm_online_users, 'Required in Online Tracker');
add_option('wpgm_enable_stats', $wpgm_enable_stats, 'Required in Stats Map');
add_option('wpgm_stats_pagesize', $wpgm_stats_pagesize, 'Required in Stats Map');
add_option('wpgm_stats_start_date', $wpgm_stats_start_date, 'Required in Stats Map');
add_option('wpgm_auth_key', 'wpgm_auth_key', 'auth_key in Stats Map');
add_option('wpgm_location_param', $wpgm_location_param, 'Required in Weather Map');
add_option('wpgm_unit', $wpgm_unit, 'Required in Weather Map');

add_option('wpgm_table_version', 0, 'Table version');



//add_option('wpgm_weather_report', $wpgm_weather_report, 'Required in Weather Map');


add_action('admin_menu', 'add_wpgm_option_page');
add_action('wp_head', 'add_wpgm_head');



function add_wpgm_head(){
	global $wpgm_version, $wpgm_blog_url;
	echo "<!-- WP GuestMap $wpgm_version -->";
	if(get_option('wpgm_enable_stats')!='on') return;
	
	$wpgm_stats_url = $wpgm_blog_url.'/wp-content/plugins/wp-guestmap/stats.php';
	if(empty($_COOKIE['GeoInfo']) || get_option('wpgm_cache_enabled')){
		echo  '
<script src="http://j.maxmind.com/app/geoip.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
function setCookie(name,value,expireHours){
	var cookieString=name+"="+value;
	if(expireHours>0){
		var date=new Date();
		date.setTime(date.getTime+expireHours*3600*1000);
		cookieString=cookieString+"; expire="+date.toGMTString();
	}
	document.cookie=cookieString+"; path=/;";
}
setCookie("GeoInfo", [geoip_latitude(), geoip_longitude(), geoip_city(), geoip_country_name(), geoip_region(), geoip_country_code()].join("|" ), "", "/");
document.write("<scr"+"ipt src=\"'.$wpgm_stats_url.'\" type=\"text/javascript\"></scr"+"ipt>");
//]]>
</script>';
	}else{
		echo '<script src="'.$wpgm_stats_url.'" type="text/javascript"></script>';
	}
}

// Hook in the options page function
function add_wpgm_option_page() {
	global $wpdb;
	$wpgm_page = add_options_page('WordPress GuestMap Options', 'WP GuestMap', 8, basename(__FILE__), 'wpgm_options_page');
	add_action("admin_print_scripts-$wpgm_page", 'wpgm_add_admin_script');
}
// wp_nonce
if ( !function_exists('wp_nonce_field') ) {
	function wpgm_nonce_field($action = -1) { return; }
	$wpgm_nonce = -1;
} else {
	function wpgm_nonce_field($action = -1) { return wp_nonce_field($action); }
	$wpgm_nonce = 'wpgm-update-key';
}

function wpgm_create_table() {
	global $wpdb, $wpgm_table_name, $wpgm_table_version;
	// fix the compatibility with MySQL prior to 4.1.2
	$mysql412_compatible = version_compare(mysql_get_server_info(), '4.1.2', '>=') ? 'default CURRENT_TIMESTAMP' : '';
	
	$q = <<<SQLSTR
CREATE TABLE IF NOT EXISTS `$wpgm_table_name` (
  `id` int(11) NOT NULL auto_increment,
  `ip` int(11) NOT NULL,
  `time` timestamp NOT NULL $mysql3_compatible,
  `lat` double NOT NULL,
  `lng` double NOT NULL,
  `city` varchar(255) NOT NULL,
  `region` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `country_code` char(2) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `hash` char(32) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `hash` (`hash`)
)
SQLSTR;
	if(defined('DB_CHARSET') && (DB_CHARSET!='')) $q .= ' DEFAULT CHARSET='.DB_CHARSET;
	if(defined('DB_COLLATE') && (DB_COLLATE!='')) $q .= ' COLLATE '.DB_COLLATE;
	$q .= ';';
	
	if(intval(get_option('wpgm_table_version')) < $wpgm_table_version){
		$wpdb->query("DROP TABLE IF EXISTS `$wpgm_table_name`");
		if($wpdb->query($q)){
			update_option('wpgm_table_version', $wpgm_table_version);
		}
	}
}

function wpgm_add_admin_script() {
	global $wpgm_blog_url, $wpgm_default_center, $wpgm_zoom_level, $wpgm_version, $wpgm_domain;
	
	$wpgm_api_key = isset($_POST['wpgm_api_key'])?$_POST['wpgm_api_key']:get_option('wpgm_api_key');
	$wpgm_cache_enabled = isset($_POST['wpgm_cache_enabled'])?$_POST['wpgm_cache_enabled']:get_option('wpgm_cache_enabled');
	
	$wpgm_feed_api_key = isset($_POST['wpgm_feed_api_key'])?$_POST['wpgm_feed_api_key']:get_option('wpgm_feed_api_key');
	$wpgm_welcome_message = isset($_POST['wpgm_welcome_message'])?$_POST['wpgm_welcome_message']:get_option('wpgm_welcome_message');
	$wpgm_tracker_info = isset($_POST['wpgm_tracker_info'])?$_POST['wpgm_tracker_info']:get_option('wpgm_tracker_info');
	$wpgm_tracker_timeout = isset($_POST['wpgm_tracker_timeout'])?$_POST['wpgm_tracker_timeout']:get_option('wpgm_tracker_timeout');
	$wpgm_tracker_refresh_rate = isset($_POST['wpgm_tracker_refresh_rate'])?$_POST['wpgm_tracker_refresh_rate']:get_option('wpgm_tracker_refresh_rate');
	$wpgm_enable_stats = isset($_POST['wpgm_enable_stats'])?$_POST['wpgm_enable_stats']:get_option('wpgm_enable_stats');
	$wpgm_stats_pagesize = isset($_POST['wpgm_stats_pagesize'])?$_POST['wpgm_stats_pagesize']:get_option('wpgm_stats_pagesize');
	$wpgm_stats_start_date = isset($_POST['wpgm_stats_start_date'])?$_POST['wpgm_stats_start_date']:get_option('wpgm_stats_start_date');
	$wpgm_auth_key = isset($_POST['wpgm_auth_key'])?$_POST['wpgm_auth_key']:get_option('wpgm_auth_key');
	$wpgm_location_param = isset($_POST['wpgm_location_param'])?$_POST['wpgm_location_param']:get_option('wpgm_location_param');
	$wpgm_unit = isset($_POST['wpgm_unit'])?$_POST['wpgm_unit']:get_option('wpgm_unit');
	//$wpgm_weather_report = $_POST['wpgm_weather_report'])?$_POST['wpgm_weather_report']:get_option('wpgm_weather_report');
	
	
	load_plugin_textdomain('wpgm', 'wp-content/plugins/wp-guestmap/languages');
	$str_click_to_view = __('CLICK HERE TO VIEW', $wpgm_domain);
	$str_private_feed = __('Private Feed:', $wpgm_domain);
	$str_visual_feed = __('Visual Feed:', $wpgm_domain);
	
    if (get_magic_quotes_gpc()) {
        $wpgm_welcome_message = stripslashes($wpgm_welcome_message);
        $wpgm_tracker_info = stripslashes($wpgm_tracker_info);
        //$wpgm_weather_report = stripslashes($wpgm_weather_report);
    }
	//$wpgm_params = base64_encode(implode('|', array($wpgm_api_key, $wpgm_zoom_level, $wpgm_map_type, $wpgm_default_center, $wpgm_welcome_message)));
	$wpgm_map_path = $wpgm_blog_url.'/wp-content/plugins/wp-guestmap/';
	
	$s= <<<WPGM_SCRIPT

<style type="text/css">
h3{text-indent:3em}
</style>
<script src="$wpgm_api_key" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[


[].indexOf||(Array.prototype.indexOf=function(v){
	for(var i=this.length;i--&&this[i]!==v;);
	return i;
});

Date.prototype.format = function(style) {
  var o = {
    "M+" : this.getMonth() + 1, //month
    "d+" : this.getDate(),      //day
    "h+" : this.getHours(),     //hour
    "m+" : this.getMinutes(),   //minute
    "s+" : this.getSeconds(),   //second
    "q+" : Math.floor((this.getMonth() + 3) / 3),  //quarter
    "S"  : this.getMilliseconds() //millisecond
  }
  if(/(y+)/.test(style)) {
    style = style.replace(RegExp.$1,
    (this.getFullYear() + "").substr(4 - RegExp.$1.length));
  }
  for(var k in o){
    if(new RegExp("("+ k +")").test(style)){
      style = style.replace(RegExp.$1,
        RegExp.$1.length == 1 ? o[k] :
        ("00" + o[k]).substr(("" + o[k]).length));
    }
  }
  return style;
};

var maptypes, geocoder,api_key, feed_api_key, cache_enabled, welcome_message, tracker_info, tracker_timeout, tracker_refresh_rate, enable_stats , stats_pagesize , stats_start_date, auth_key, location_param, unit, zoom_level, map_type, default_center, map_width, map_height, map_id, map_use_object, config_form;

function getLatLngString(){
	var address = default_center.value;
	var arr = address.split(",");
	var lat=parseFloat(arr[0]);
	var lng=parseFloat(arr[1]);
	if((arr.length==2) && !isNaN(lat) && (-90<lat) && (lat<90) && !isNaN(lng) && (-180<lng) && (lng<180) ){
		var point = new GLatLng(lat, lng);
		map.panTo(point);
		marker.setPoint(point);
	}else if (geocoder){
		geocoder.getLatLng(
			address,
			function(point) {
				if (!point) {
					alert(address + " not found");
				} else {
					map.panTo(point);
					default_center.value = point.toUrlValue();
					marker.setPoint(point);
				}
			}
		);
	}
}

function loadMap(){
	var e = function(id){ return document.getElementById(id)};
	geocoder=new GClientGeocoder();
	
	api_key = e("wpgm_api_key");
	cache_enabled = e("wpgm_cache_enabled");
	
	feed_api_key = e("wpgm_feed_api_key");
	welcome_message = e("wpgm_welcome_message");
	tracker_info = e("wpgm_tracker_info");
	tracker_timeout = e("wpgm_tracker_timeout");
	tracker_refresh_rate = e("wpgm_tracker_refresh_rate");
	enable_stats = e("wpgm_enable_stats");
	stats_pagesize = e("wpgm_stats_pagesize");
	stats_start_date = e("wpgm_stats_start_date");
	auth_key = e("wpgm_auth_key");
	location_param = e("wpgm_location_param");
	unit = e("wpgm_unit");
	//weather_report = e("wpgm_weather_report");
	
	
	zoom_level = e("wpgm_zoom_level");
	map_type = e("wpgm_map_type");
	default_center = e("wpgm_default_center");
	map_width = e("wpgm_width");
	map_height = e("wpgm_height");
	map_id = e("wpgm_id");
	map_use_object = e("wpgm_use_object");
	
	config_form = e("wpgm_config");
	var private_feed_url = "{$wpgm_map_path}feed.php?auth=$wpgm_auth_key";
	var visual_feed_url = "http://maps.google.com/maps?f=q&ie=UTF8&z=2&q="+encodeURIComponent("{$wpgm_map_path}feed.php?visual=enabled");
	e("wpgm_feed_url").innerHTML = '<strong>$str_private_feed</strong> <a href="'+private_feed_url+'" target="_blank" title="'+private_feed_url+'">$str_click_to_view</a> <br /> <strong>$str_visual_feed</strong> <a href="'+visual_feed_url+'" target="_blank" title="'+visual_feed_url+'">$str_click_to_view</a>';
	
	api_key.value = "$wpgm_api_key";
	cache_enabled.checked = "$wpgm_cache_enabled";
	
	feed_api_key.value = "$wpgm_feed_api_key";
	welcome_message.value = "$wpgm_welcome_message";
	tracker_info.value = "$wpgm_tracker_info";
	tracker_timeout.value = "$wpgm_tracker_timeout";
	tracker_refresh_rate.value = "$wpgm_tracker_refresh_rate";
	enable_stats.checked = "$wpgm_enable_stats";
	stats_pagesize.value = "$wpgm_stats_pagesize";
	stats_start_date.value = "$wpgm_stats_start_date";
	auth_key.value = "$wpgm_auth_key";
	location_param.value = "$wpgm_location_param";
	unit.selectedIndex = ("$wpgm_unit"=="c")?0:1;
	//weather_report.value = "wpgm_weather_report";
	
	
	
	map = new GMap2(e("adminmap"));
	var point = new GLatLng($wpgm_default_center);
	map.setCenter(point, $wpgm_zoom_level);
	
	maptypes = map.getMapTypes();
	var index = maptypes.length - 1;
	
	map.setMapType(maptypes[index]);
	
	marker = new GMarker(point);
	map.addOverlay(marker);
	
	
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
	
	for(var i=0; i<maptypes.length; i++){
		var opt = document.createElement("option");
		opt.value = i;
		opt.innerHTML = maptypes[i].getName();
		map_type.appendChild(opt);
	}
	
	//map.setMapType(maptypes[$wpgm_map_type]);
	map_type.selectedIndex = maptypes.indexOf(map.getCurrentMapType());
	GEvent.addListener(map, "move", function(){default_center.value = map.getCenter().toUrlValue()});
	GEvent.addListener(map, "zoomend", function(oldlevel, newlevel){zoom_level.value = newlevel});
	GEvent.addListener(map, "maptypechanged", function(){map_type.selectedIndex = maptypes.indexOf(map.getCurrentMapType())});
	
	GEvent.addDomListener(map_type, "change", function(){map.setMapType(maptypes[this.selectedIndex])});
	GEvent.addDomListener(zoom_level, "blur", function(){var val= parseInt(this.value); if(!isNaN(val) && (val>=0)) map.setZoom(val)});
	
	default_center.value = "$wpgm_default_center";
	map_type.selectedIndex = index;
}
GEvent.addDomListener(window, "load", loadMap);
GEvent.addDomListener(window, "unload", GUnload);


function getCode(type){
	var id = !map_id.value? '' : 'id="'+map_id.value+'"';
	var w = map_width.value;
	var h = map_height.value;
	
	var mt = map_type.selectedIndex = maptypes.indexOf(map.getCurrentMapType());
	var dc = default_center.value = map.getCenter().toUrlValue();
	var zl = zoom_level.value = map.getZoom();
	
	var template = '<iframe '+id+' src="$wpgm_map_path'+type+'.php?mt='+mt+'&amp;dc='+dc+'&amp;zl='+zl+'" scrolling="no" style="margin:0px" width="'+w+'" height="'+h+'" frameborder="0"></iframe>';
	
	if(!!map_use_object.checked){
		w = isNaN(w)? w: w+'px';
		h = isNaN(h)? h: h+'px';
		template = '<object classid="clsid:25336920-03F9-11CF-8FD0-00AA00686F13" type="text/html" '+id+' data="$wpgm_map_path'+type+'.php?mt='+mt+'&amp;dc='+dc+'&amp;zl='+zl+'" style="margin:0px; width:'+w+'; height:'+h+'"></object>';
	}
	
	return template;
}

//]]>
</script>
WPGM_SCRIPT;
	if($wpgm_api_key) echo $s;
}


function wpgm_options_page() {
	global $wpdb, $wpgm_table_name, $wpgm_domain, $wpgm_nonce;
	// If we are a postback, store the options
 	if (isset($_POST['basic_update'])) {
		check_admin_referer('$wpgm_nonce', $wpgm_nonce);
		update_option('wpgm_api_key', $_POST['wpgm_api_key']);
		update_option('wpgm_cache_enabled', $_POST['wpgm_cache_enabled']);
	}
 	if (isset($_POST['widget_update'])) {
		check_admin_referer('$wpgm_nonce', $wpgm_nonce);
		update_option('wpgm_feed_api_key', $_POST['wpgm_feed_api_key']);
		update_option('wpgm_enable_stats', $_POST['wpgm_enable_stats']);
		update_option('wpgm_stats_pagesize', intval($_POST['wpgm_stats_pagesize']));
		update_option('wpgm_stats_start_date', $_POST['wpgm_stats_start_date']);
		update_option('wpgm_auth_key', $_POST['wpgm_auth_key']);
		update_option('wpgm_welcome_message', $_POST['wpgm_welcome_message']);
		update_option('wpgm_tracker_info', $_POST['wpgm_tracker_info']);
		update_option('wpgm_tracker_timeout', intval($_POST['wpgm_tracker_timeout']));
		update_option('wpgm_tracker_refresh_rate', intval($_POST['wpgm_tracker_refresh_rate']));
		update_option('wpgm_location_param', $_POST['wpgm_location_param']);
		update_option('wpgm_unit', $_POST['wpgm_unit']);
		//update_option("wpgm_weather_report", $_POST["wpgm_weather_report"]);
		
		$birthday = $_POST['wpgm_stats_start_date'];
		if($_POST["wpgm_delete_old_stats"] && preg_match("/^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29-))$/", $birthday)){
			$wpdb->query("DELETE FROM `$wpgm_table_name` WHERE time<'$birthday'");
		}
		if($_POST['wpgm_enable_stats']){
			wpgm_create_table();
		}
	}
	load_plugin_textdomain('wpgm', 'wp-content/plugins/wp-guestmap/languages');

	if (!empty($_POST)) $message = 'Options saved.';
	if (!get_option('wpgm_api_key')) $message = 'You need a valid Google Maps API key.';
	
	if($message) {	
?>
<div id="message" class="updated fade"><p><strong><?php  _e($message, $wpgm_domain); ?></strong></p></div>
<?php } ?>
<form id="wpgm_config" name="wpgm_config" method="post" action="options-general.php?page=wp-guestmap.php">
<?php wpgm_nonce_field('$wpgm_nonce', $wpgm_nonce); ?>
<div class="wrap">
	<h2><?php _e('WP GuestMap Configuration', $wpgm_domain); ?></h2>
	<fieldset class="options">
		<legend><?php _e('Basic Settings', $wpgm_domain); ?></legend>
		<h3><?php _e('Google Maps API (Required)', $wpgm_domain); ?></h3>
		<table class="editform" cellspacing="2" cellpadding="5" width="100%">
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_api_key"><?php _e('API Key URL:', $wpgm_domain); ?></label>
				</th>
				<td valign="top">
					<input type="text" style="width:100%" name="wpgm_api_key" id="wpgm_api_key" value="" />
					<p style="margin: 5px 10px;"><?php _e('You have to obtain a Google Maps API key to make all these stuffs work. Sign up at <a href="http://www.google.com/apis/maps/signup.html" target="_blank">http://www.google.com/apis/maps/signup.html</a>', $wpgm_domain); ?></p>
					<p style="margin: 5px 10px;"><?php _e('<strong>NOTICE</strong>: You should put the <strong>Key URL</strong> here, not just the key. You may wish to make a little hack with the URL. <br />eg: German bloggers may like to point the URL to <em>http://maps.google.com/maps?file=api&amp;hl=de&amp;v=2&amp;key=your_key</em>, while Chinese bloggers would prefer <em>http://ditu.google.com/maps?file=api&amp;v=2&amp;key=your_key</em> to use more detailed map.', $wpgm_domain); ?></p>
				</td>
			</tr>
		</table>
		<h3><?php _e('Miscellaneous', $wpgm_domain); ?></h3>
		<table class="editform" cellspacing="2" cellpadding="5" width="100%">
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_cache_enabled"><?php _e('WP-Cache Enabled:', $wpgm_domain); ?></label>
				</th>
				<td valign="top">
					<input type="checkbox" name="wpgm_cache_enabled" id="wpgm_cache_enabled" /> <?php _e('If you are using the plugin "WP-Cache" and have enabled it, please check this box to avoid conflict.', $wpgm_domain); ?> 
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="basic_update" value="<?php _e('Submit Changes', $wpgm_domain); ?>" />
		</p>
	</fieldset>
	<hr />
	<fieldset class='options'>
		<legend><?php _e('Widget Settings', $wpgm_domain); ?></legend>
		<h3><?php _e('Guest Locator', $wpgm_domain); ?></h3>
		<table class="editform" cellspacing="2" cellpadding="5" width="100%">
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_welcome_message"><?php _e('Welcome Message:', $wpgm_domain); ?></label>
				</th>
				<td valign="top">
					<input type="text" style="width:100%" name="wpgm_welcome_message" id="wpgm_welcome_message" value="" />
					<p style="margin: 5px 10px;"><?php _e('Plain text and HTML code are allowed. Besides, you can also use tags like <strong>%country%</strong>, <strong>%country_code%</strong>, <strong>%city%</strong>, <strong>%latitude%</strong> and <strong>%longitude%</strong>.', $wpgm_domain); ?></p>
				</td>
			</tr>
		</table>
		<h3><?php _e('Online Tracker', $wpgm_domain); ?></h3>
		<table class="editform" cellspacing="2" cellpadding="5" width="100%">
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_tracker_info"><?php _e('Tracker\'s Information:', $wpgm_domain); ?></label>
				</th>
				<td valign="top" colspan="3">
					<input type="text" style="width:100%" name="wpgm_tracker_info" id="wpgm_tracker_info" value="" />
					<p style="margin: 5px 10px;"><?php _e('This widget is almost the same as <strong>Guest Locator</strong>, except that this one also shows other online users, and refresh every minute. You have two extra tag <strong>%online_user_count%</strong> and <strong>%online_other_user_count%</strong> besides those in <strong>Guest Locator</strong>.', $wpgm_domain); ?></p>
				</td>
			</tr>
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_tracker_timeout"><?php _e('Timeout:', $wpgm_domain); ?></label>
				</th>
				<td width="25%" valign="top">
					<input type="text" size="4" name="wpgm_tracker_timeout" id="wpgm_tracker_timeout" value="" /> <?php _e('Seconds', $wpgm_domain); ?>
					<p style="margin: 5px 10px;"><?php _e('If a client doesn\'t respond for a long time, it should be considered offline. 15 minutes (900 seconds) is recommended.', $wpgm_domain); ?></p>
				</td>
				<th width="10%" valign="top">
					<label for="wpgm_tracker_refresh_rate"><?php _e('Refresh Rate:', $wpgm_domain); ?></label>
				</th>
				<td valign="top">
					<input type="text" size="4" name="wpgm_tracker_refresh_rate" id="wpgm_tracker_refresh_rate" value="" /> <?php _e('Seconds (0 for passive mode)', $wpgm_domain); ?>
					<p style="margin: 5px 10px;"><?php _e('<strong>Online Tracker</strong> loads online status periodically by default, and this defines its frequency. In passvie mode, the online status is loaded only when the page is first loaded.', $wpgm_domain); ?></p>
				</td>
			</tr>
		</table>
		<h3><?php _e('Stats Map', $wpgm_domain); ?></h3>
		<table class="editform" cellspacing="2" cellpadding="5" width="100%">
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_enable_stats"><?php _e('Status:', $wpgm_domain); ?></label>
				</th>
				<td width="15%" valign="top">
					<input type="checkbox" name="wpgm_enable_stats" id="wpgm_enable_stats" onclick="var d=new Date();wpgm_stats_start_date.value= !this.checked? '': d.format('yyyy-MM-dd')}" /> <label for="wpgm_enable_stats"> <?php _e('Enabled:', $wpgm_domain); ?></label>
				</td>
				<td valign="middle" colspan="2">
					<p style="margin: 0;"><?php _e('If enabled, WP GuestMap will begin to collect statistics, no matter whether you choose to display Stats Map.', $wpgm_domain); ?></p>
				</td>
			</tr>
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_stats_pagesize"><?php _e('Output Pagesize:', $wpgm_domain); ?></label>
				</th>
				<td width="15%" valign="top">
					<input type="text" name="wpgm_stats_pagesize" id="wpgm_stats_pagesize" />
				</td>
				<td valign="middle" colspan="2">
					<p style="margin: 0;"><?php _e('The performance of Stats Map is greatly dependent on pagesize. Now the default value is 25; the previous on is 100.', $wpgm_domain); ?></p>
				</td>
			</tr>
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_stats_start_date"><?php _e('Date of Birth:', $wpgm_domain); ?></label>
				</th>
				<td width="15%" valign="top">
					<input type="text" name="wpgm_stats_start_date" id="wpgm_stats_start_date" />
				</td>
				<td width="20%" valign="middle">
					<input type="checkbox" name="wpgm_delete_old_stats" id="wpgm_delete_old_stats" onclick="if(!!this.checked) this.checked = confirm('Do you really wish to do so?\nYou cannot recover the deleted data if you submit your configuration!')" />
					<label for="wpgm_delete_old_stats"><?php _e('Delete data before birthday.', $wpgm_domain); ?></label>
				</td>
				<td valign="middle">
					<p style="margin: 0;"><?php _e('<strong>Only data after this day will show on Stats Map</strong>. Format <strong>YYYY-MM-DD</strong>.', $wpgm_domain); ?></p>
				</td>
			</tr>
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_auth_key"><?php _e('Authentic Key:', $wpgm_domain); ?></label>
				</th>
				<td width="15%" valign="top">
					<input type="text" name="wpgm_auth_key" id="wpgm_auth_key" />
				</td>
				<td valign="top" colspan="2">
					<p id="wpgm_feed_url" style="margin: 0;"></p>
					<p style="margin: 0;"><?php _e('The URLs above are RSS feeds containing a daily report of your visitors. Because IP address and other client information is kind of privacy of visitors, you should keep <strong>Private Feed</strong> URL a secret. If you find the URL is abused, please change the key, and use the NEW URL. I cannot adopt a better authentic method, otherwise you will not be able to subscribe it via online RSS readers.', $wpgm_domain); ?></p>
				</td>
			</tr>
		</table>
		<h3><?php _e('Weather Map', $wpgm_domain); ?></h3>
		<table class="editform" cellspacing="2" cellpadding="5" width="100%">
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_feed_api_key"><?php _e('Google AJAX Feed API Key:', $wpgm_domain); ?></label>
				</th>
				<td valign="top" colspan="3">
					<input type="text" style="width:100%" name="wpgm_feed_api_key" id="wpgm_feed_api_key" value="" />
					<p style="margin: 5px 10px;"><?php _e('Just key is OK, no URL. We use Google AJAX Feed Fetcher to get weather infomation. Sign up at <a href="http://code.google.com/apis/ajaxfeeds/signup.html" target="_blank">http://code.google.com/apis/ajaxfeeds/signup.html</a>', $wpgm_domain); ?></p>
				</td>
			</tr>
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_location_param"><?php _e('Location parameter:', $wpgm_domain); ?></label>
				</th>
				<td width="50%" valign="top">
					<input type="text" style="width:100%" name="wpgm_location_param" id="wpgm_location_param" value="" />
					<p style="margin: 5px 10px;"><?php _e('The location parameter can be a US Zip code or a location ID. To find your location ID, browse or search for your city from the <a href="http://weather.yahoo.com/" target="_blank">Yahoo! Weather</a> home page. The weather ID is in the URL for the forecast page for that city. You can also get the location ID by entering your zip code on the home page. For example, if you search for Los Angeles on the Yahoo! Weather home page, the forecast page for that city is http://weather.yahoo.com/forecast/USCA0638.html. The location ID is USCA0638.', $wpgm_domain); ?></p>
				</td>
				<th width="10%" valign="top">
					<label for="wpgm_feed_api_key"><?php _e('Unit:', $wpgm_domain); ?></label>
				</th>
				<td valign="top">
					<select style="width:100%" name="wpgm_unit" id="wpgm_unit">
						<option value="c"><?php _e('Celsius', $wpgm_domain); ?></option>
						<option value="f"><?php _e('Fahrenheit', $wpgm_domain); ?></option>
					</select>
					<p style="margin: 5px 10px;"><?php _e('Temperature unit', $wpgm_domain); ?></p>
				</td>
			</tr>
			<!--tr>
				<th width="20%" valign="top">
					<label for="wpgm_welcome_message">Weather Report:</label>
				</th>
				<td valign="top" colspan="3">
					<input type="text" style="width:100%" name="wpgm_weather_report" id="wpgm_weather_report" value="" />
					<p style="margin: 5px 10px;">Plain text and HTML code are allowed. Besides, you can also use tags like <strong>%country%</strong>, <strong>%country_code%</strong>, <strong>%city%</strong>, <strong>%latitude%</strong> and <strong>%longitude%</strong>.</p>
				</td>
			</tr-->
		</table>
		<p class="submit">
			<input type="submit" name="widget_update" value="<?php _e('Submit Changes', $wpgm_domain); ?>" />
		</p>
	</fieldset>
	<hr />
	<fieldset class="options">
		<legend><?php _e('Map Builder', $wpgm_domain); ?></legend>
		<h3><?php _e('Adjust Map Options', $wpgm_domain); ?></h3>
		<table class="editform" cellspacing="2" cellpadding="5" width="100%">
			<tr>
				<th width="20%" valign="top">
					<label for="wpgm_zoom_level"><?php _e('Zoom Level:', $wpgm_domain); ?></label>
				</th>
				<td width="10%" valign="top">
					<input style="width:100%" type="text" name="wpgm_zoom_level" id="wpgm_zoom_level" value="3" title="<?php _e('integer between 0~17', $wpgm_domain); ?>" />
					<p style="margin: 5px 10px;"><?php _e('integer between 0~17', $wpgm_domain); ?></p>
				</td>
				<th width="10%" valign="top">
					<label for="wpgm_map_type"><?php _e('Map Type:', $wpgm_domain); ?></label>
				</th>
				<td width="10%" valign="top">
					<select style="width:100%" name="wpgm_map_type" id="wpgm_map_type" title="<?php _e('Hybird Map is recommended.', $wpgm_domain); ?>">
					</select>
					<p style="margin: 5px 10px;"><?php _e('Strongly recommend not to use Normal Map', $wpgm_domain); ?></p>
				</td>
				<td rowspan="4" align="right">
					<div id="adminmap" style="position:relative; height:300px; width: 90%;"></div>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="wpgm_default_center"><?php _e('Default Center:', $wpgm_domain); ?></label>
				</th>
				<td valign="top" colspan="3">
					<input style="width:65%;" type="text" name="wpgm_default_center" id="wpgm_default_center" value="" /><input style="width:30%;" type='button' name='wpgm_search_city' value='<?php _e('Locate', $wpgm_domain); ?>' onclick="getLatLngString()" />
					<p style="margin: 5px 10px;"><?php _e('You can put city name, or your latitude and longitude(eg: 32.52, 122.53) in the textbox, then press "Locate" button to set the center of the map. If it fails to , you have to find geoloaction by yourself.', $wpgm_domain); ?></p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="wpgm_width"><?php _e('Width:', $wpgm_domain); ?></label>
				</th>
				<td valign="top">
					<input style="width:100%" type="text" size="6" name="wpgm_width" id="wpgm_width" value="100%" />
					<p style="margin: 5px 10px;"><?php _e('Integer or percentage.', $wpgm_domain); ?></p>
				</td>
				<th valign="top">
					<label for="wpgm_height"><?php _e('Height:', $wpgm_domain); ?></label>
				</th>
				<td valign="top">
					<input style="width:100%" type="text" size="6" name="wpgm_height" id="wpgm_height" value="200" />
					<p style="margin: 5px 10px;"><?php _e('Integer', $wpgm_domain); ?></p>
				</td>
			</tr>
			<tr>
				<th valign="top">
					<label for="wpgm_id"><?php _e('ID:', $wpgm_domain); ?></label>
				</th>
				<td colspan="3">
					<input style="width:100%" type="text" name="wpgm_id" id="wpgm_id" value="wpgm_frame" />
					<p style="margin: 5px 10px;"><?php _e('ID can be used in CSS', $wpgm_domain); ?></p>
				</td>
			</tr>
		</table>
		<h3><?php _e('Get Your Code', $wpgm_domain); ?></h3>
		<table class="editform" cellspacing="2" cellpadding="5" width="100%">
			<tr>
				<th width="20%" valign="top" style="padding-top: 10px;">
					<label for="wpgm_code"><?php _e('Use &lt;object&gt;:', $wpgm_domain); ?></label>
				</th>
				<td valign="top" style="padding-top: 10px;">
					<input type="checkbox" name="wpgm_use_object" id="wpgm_use_object" /> 
					<?php _e('If DOCTYPE of your theme is <strong>XHTML Strict</strong>, please check here to use &lt;object&gt; instead of &lt;iframe&gt;.', $wpgm_domain); ?>
				</td>
			</tr>
			<tr>
				<th valign="top" style="padding-top: 10px;">
					<label for="wpgm_code"><?php _e('Code:', $wpgm_domain); ?></label>
				</th>
				<td valign="top">
					<textarea id="wpgm_code" name="wpgm_code" style="width:100%; height: 200px" onmouseover="this.focus();this.select();"></textarea>
				</td>
			</tr>
			<tr>
				<td valign="top" class="submit" colspan="2">
					<input type='button' name='get_code1' value='<?php _e('Get Your Guest Locator', $wpgm_domain); ?>' onclick="wpgm_code.value=getCode('guest-locator')" />
					<input type='button' name='get_code2' value='<?php _e('Get Your Online Tracker', $wpgm_domain); ?>' onclick="wpgm_code.value=getCode('online-tracker')" />
					<input type='button' name='get_code3' value='<?php _e('Get Your Stats Map', $wpgm_domain); ?>' onclick="wpgm_code.value=getCode('stats-map')" />
					<input type='button' name='get_code4' value='<?php _e('Get Your Weather Map', $wpgm_domain); ?>' onclick="wpgm_code.value=getCode('weather-map')" />
				</td>
			</tr>
		</table>
		
	</fieldset>
	<h2></h2>
</div>
</form>
<?php
}

/******************************** widget *******************************/

?>
