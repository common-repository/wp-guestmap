<?php

require_once(dirname(__FILE__).'/common.inc.php');
http_modified('wpgm_stats_map_cached'.empty($_COOKIE['wpgm_widget_disabled']));

if (empty($wp)) {
	wp();
}

$wpgm_api_key = get_option('wpgm_api_key');
$wpgm_welcome_message = get_option('wpgm_welcome_message');
$wpgm_map_type = $_GET['mt'];
$wpgm_zoom_level = $_GET['zl'];
$wpgm_default_center = $_GET['dc'];

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta http-equiv="last-modified" content="<?php echo $wpgm_last_modified; ?>" />
<title>WP GuestMap  &raquo;  Stats Map Version v<?php echo $wpgm_version; ?></title>
<style type="text/css">
@import url("widget.css");
</style> 
<!--导入Google Maps API库文件。注意将本代码中的API Key替换为前文申请到的API Key-->
<script src="<?php echo $wpgm_api_key; ?>" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[

//全局GMap
var map;
var page = 0;

//自定义icon
var baseIcon = new GIcon();
baseIcon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
baseIcon.iconSize = new GSize(12, 20);
baseIcon.shadowSize = new GSize(22, 20);
baseIcon.iconAnchor = new GPoint(6, 20);
baseIcon.infoWindowAnchor = new GPoint(5, 1);

var blueIcon = new GIcon(baseIcon);
blueIcon.image = "http://labs.google.com/ridefinder/images/mm_20_blue.png";
var greenIcon = new GIcon(baseIcon);
greenIcon.image = "http://labs.google.com/ridefinder/images/mm_20_green.png";
var whiteIcon = new GIcon(baseIcon);
whiteIcon.image = "http://labs.google.com/ridefinder/images/mm_20_white.png";
var yellowIcon = new GIcon(baseIcon);
yellowIcon.image = "http://labs.google.com/ridefinder/images/mm_20_yellow.png";
var orangeIcon = new GIcon(baseIcon);
orangeIcon.image = "http://labs.google.com/ridefinder/images/mm_20_orange.png";
var purpleIcon = new GIcon(baseIcon);
purpleIcon.image = "http://labs.google.com/ridefinder/images/mm_20_purple.png";
var redIcon = new GIcon(baseIcon);
redIcon.image = "http://labs.google.com/ridefinder/images/mm_20_red.png";

//创建地标并监听点击事件
function createMarker(point,info,myIcon,html){
	//创建地标
	var marker = new GMarker(point, {icon: myIcon, title: info/*, zIndexProcess: function(m,b){return 1}*/});
	/*以下部分用于监听点击事件
	GEvent.addListener(marker, "click", function()
			{
				marker.openInfoWindowHtml(html);
			}
	);*/
	map.addOverlay(marker);
	return marker;
}


//网页加载时用于初始化Google地图
function load(){
	if (GBrowserIsCompatible()){
	//创建GMap2对象
	map = new GMap2(document.getElementById("map"));
	
	//缩放级别设得比较小，如不合适可自行设定合适的级别
	map.setCenter(new GLatLng(<?php echo $wpgm_default_center; ?>), <?php echo $wpgm_zoom_level; ?>);
	map.addControl(new GSmallZoomControl());
	map.setMapType(map.getMapTypes()[<?php echo $wpgm_map_type; ?>]);
	
	//创建GMarkerManager
	//mm = new GMarkerManager(map, {borderPadding:1});
	//AJAX获取地标信息
	//此处动态生成XML文件的URL，并加载
	loadMarkers();
	}
}

function loadMarkersAlias(){
	loadMarkers();
}

function loadMarkers(){
	var request = GXmlHttp.create();
	request.open("GET", "<?php echo dirname(SERVER_VAR('SCRIPT_NAME')); ?>/output.php?page="+(page++)+"&rand="+Math.random(), true);
	request.onreadystatechange = function() {
		if (request.readyState == 4){
			try{
				//解析XML文件
				var xmlDoc = request.responseXML;
				
				//获取地标节点
				var markers = xmlDoc.documentElement.getElementsByTagName("location");
				var count = markers.length;
				if(count==0){
					//显示地标
					return;
				}
				loadMarkers();
				
				
				//地标数组
				//var markers_array = [];
				//var mm = new GMarkerManager(map, {borderPadding:1});
				for (var i=0; i<count; i++){
						//获取经纬度信息
						var lat = parseFloat(markers[i].getAttribute("lat"));
						var lng = parseFloat(markers[i].getAttribute("lng"));
						var point = new GLatLng(lat, lng);
						
						var visits = markers[i].getAttribute("visits");
						var city = markers[i].getAttribute("city");
						var region = markers[i].getAttribute("region");
						var country = markers[i].getAttribute("country");
						//获取客户端信息及地理信息
						var info = visits + " visit(s) from ";
						info += (!city?"Unknown":city) + ", ";
						info += (!region?"Unknown":region) + ", ";
						info += (!country?"Unknown":country);
						
						
						
						//创建地标
						if(visits==1) var marker = createMarker(point, info, whiteIcon);
						else if(visits<10) var marker = createMarker(point, info, yellowIcon);
						else if(visits<30) var marker = createMarker(point, info, orangeIcon);
						else if(visits<100) var marker = createMarker(point, info, purpleIcon);
						else var marker = createMarker(point, info, redIcon);
						
						
						//存入地标数组
						//markers_array.push(marker);
				}
				
				
				//添加地标数组
				
				//mm.addMarkers(markers_array, 0);
				//mm.refresh();
			}catch(e){
				loadMarkersAlias();
			}
		}
	}
	request.send(null);
}
//]]>
</script>
</head>
<!--加载时调用load()函数加载地图，注意加上onunload="GUnload()"防止内存泄露-->
<body onload="load()" onunload="GUnload()">
<div id="map" style="text-align:center">
	<h3 style="font-size: 16px"> Please wait while loading <strong>Stats Map</strong>... </h3>
	<div><a href="http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/" title="WP GuestMap">WP GuestMap</a> Powered By <a href="http://blog.codexpress.cn" title="CodeXpress.CN">CodeXpress.CN</a></div>
	<noscript> Please enable javascript to view Stats Map! </noscript>
</div>
</body>
</html>
