<?php
require_once(dirname(__FILE__).'/common.inc.php');

if(isset($_GET['activate'])){
	setcookie('wpgm_widget_disabled', '', time() - 3600);
	header('location: '.SERVER_VAR('SCRIPT_NAME').'?mt='.$_GET['mt'].'&zl='.$_GET['zl'].'&dc='.$_GET['dc']);
	exit();
}

if(!empty($_COOKIE['wpgm_widget_disabled'])){
	show_disabled_page();
	exit();
}

http_modified('wpgm_guest_locator_cached'.empty($_COOKIE['wpgm_widget_disabled']));

if (empty($wp)) {
	wp();
}


$wpgm_api_key = get_option('wpgm_api_key');
$wpgm_welcome_message = get_option('wpgm_welcome_message');
$wpgm_map_type = $_GET['mt'];
$wpgm_zoom_level = $_GET['zl'];
$wpgm_default_center = $_GET['dc'];

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta http-equiv="last-modified" content="<?php echo $wpgm_last_modified; ?>" />
<title>WP GuestMap  &raquo;  Guest Locator v<?php echo $wpgm_version; ?></title>
<style type="text/css">
@import url("widget.css");
</style> 
<!--导入Google Maps API库文件。注意将本代码中的API Key替换为前文申请到的API Key-->
<script src="<?php echo $wpgm_api_key; ?>" type="text/javascript"></script>
<script src="cache.php" type="text/javascript"></script>
<script src="tlabel.js" type="text/javascript"></script>
<script src="gbutton.js" type="text/javascript"></script>

<script type="text/javascript">
//<![CDATA[
// TLabel() GMaps API extension copyright 2005-2006 Tom Mangan
// http://gmaps.tommangan.us/tlabel.html
// free for non-commercial use

var map, point, marker, label;

//网页加载时用于初始化Google地图
function load(){
	if (GBrowserIsCompatible()){
		//创建GMap2对象
		map = new GMap2(document.getElementById("map"));
		point = new GLatLng(GeoInfo.latitude, GeoInfo.longitude);
		
		//缩放级别设得比较小，如不合适可自行设定合适的级别
		map.setCenter(new GLatLng(<?php echo $wpgm_default_center; ?>), <?php echo $wpgm_zoom_level; ?>);
		map.addControl(new GSmallZoomControl());
		map.addControl(new GButton({caption:"<img src='./images/close.gif' alt='Hide this widget' title='Hide this widget' style='border:0;'>", style:"border:0;margin:0;padding:0;cursor:pointer", callback:function(){if(!getCookie("wpgm_widget_disabled"))setCookie("wpgm_widget_disabled", 1, 720);location.reload();}}));
		
		var myIcon= new GIcon();
		myIcon.image = "./images/"+(GeoInfo.country_code).toLowerCase()+".gif";
		myIcon.shadow = "./images/shadow.gif";
		myIcon.iconSize = new GSize(16, 11);
		myIcon.shadowSize = new GSize(1, 1);
		myIcon.iconAnchor = new GPoint(8, 0);
		
		
		map.setMapType(map.getMapTypes()[<?php echo $wpgm_map_type; ?>]);
		
		var info = ("<?php echo $wpgm_welcome_message; ?>").replace(/%city%/g, GeoInfo.city).replace(/%country%/g, GeoInfo.country).replace(/%country_code%/g, GeoInfo.country_code).replace(/%latitude%/g, GeoInfo.latitude).replace(/%longitude%/g, GeoInfo.longitude);
		//标记访客位置
		marker = new GMarker(point, {icon: myIcon, title: info});
		map.addOverlay(marker);
		
		var label = new TLabel();
		label.id = 'label924';
		label.anchorLatLng = point;
		label.anchorPoint = 'bottomCenter';
		label.content = '<div style="padding-bottom: 8px; background: url(images/pointer.gif) no-repeat bottom;width:120px; "><div><b class="wpgm"><b class="wpgm1"><b></b></b><b class="wpgm2"><b></b></b><b class="wpgm3"></b><b class="wpgm4"></b><b class="wpgm5"></b></b> <div class="wpgm_content">'+info+'</div><b class="wpgm"><b class="wpgm5"></b><b class="wpgm4"></b><b class="wpgm3"></b><b class="wpgm2"><b></b></b><b class="wpgm1"><b></b></b></b></div> </div>';
		label.percentOpacity = 75;
		
		//移动到当前访客的位置
		setTimeout(function(){map.panTo(point);map.addTLabel(label);}, 500);
	}
}
//]]>
</script>
</head>
<!--加载时调用load()函数加载地图，注意加上onunload="GUnload()"防止内存泄露-->
<body onload="load()" onunload="GUnload()">
<div id="map" style="text-align:center">
	<h3 style="font-size: 16px"> Please wait while loading <strong>Guest Locator</strong>... </h3>
	<div><a href="http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/" title="WP GuestMap">WP GuestMap</a> Powered By <a href="http://blog.codexpress.cn" title="CodeXpress.CN">CodeXpress.CN</a></div>
	<noscript> Please enable javascript to view Guest Locator! </noscript>
</div>
</body>
</html>
