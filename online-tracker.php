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

http_modified('wpgm_online_tracker_cached'.empty($_COOKIE['wpgm_widget_disabled']));

if (empty($wp)) {
	wp();
}


$wpgm_api_key = get_option('wpgm_api_key');
$wpgm_tracker_info = get_option('wpgm_tracker_info');
$wpgm_tracker_refresh_rate = intval(get_option('wpgm_tracker_refresh_rate')) * 1000;
$wpgm_map_type = $_GET['mt'];
$wpgm_zoom_level = $_GET['zl'];
$wpgm_default_center = $_GET['dc'];

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta http-equiv="last-modified" content="<?php echo $wpgm_last_modified; ?>" />
<title>WP GuestMap  &raquo;  Online Tracker v<?php echo $wpgm_version; ?></title>
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

var map, point, marker, label = null;
var markers = new Array();
var baseIcon = new GIcon();
baseIcon.shadow = "./images/shadow.gif";
baseIcon.iconSize = new GSize(16, 11);
baseIcon.shadowSize = new GSize(1, 1);
baseIcon.iconAnchor = new GPoint(8, 5);

var myIcon= new GIcon(baseIcon);
myIcon.image = "./images/"+(GeoInfo.country_code).toLowerCase()+".gif";


function createMarker(point,info, myIcon, html){
	//创建地标
	var mk = new GMarker(point, {icon: myIcon, title: info/*, zIndexProcess: function(m,b){return 1}*/});
	/* 以下部分用于监听点击事件
	GEvent.addListener(marker, "click", function()
			{
				marker.openInfoWindowHtml(html);
			}
	);*/
	map.addOverlay(mk);
	return mk;
}
function showOnlineUsersAlias(){
	showOnlineUsers();
}

function showOnlineUsers(){
	var request = GXmlHttp.create();
	var url = "<?php echo dirname(SERVER_VAR('SCRIPT_NAME')); ?>/i-am-online.php?";
	request.open("GET", "<?php echo dirname(SERVER_VAR('SCRIPT_NAME')); ?>/i-am-online.php?"+"rand="+Math.random(), true);
	request.onreadystatechange = function() {
		if (request.readyState == 4){
			try{
				var i, count;
				//解析XML文件
				var xmlDoc = request.responseXML;
				//获取地标节点
				var markersNode = xmlDoc.documentElement.getElementsByTagName("location");
				
				if(!!markersNode) count = markersNode.length;
				else return;
				
				for (i=markers.length-1; i>-1; i--){
					map.removeOverlay(markers[i]);
				}
				markers = [];
				
				//标记访客位置
				if(!label){
					var info = ("<?php echo $wpgm_tracker_info; ?>").replace(/%online_user_count%/g, "<span id='online_user_count'> </span>").replace(/%online_other_user_count%/g, "<span id='online_other_user_count'> </span>").replace(/%city%/g, GeoInfo.city).replace(/%country%/g, GeoInfo.country).replace(/%country_code%/g, GeoInfo.country_code).replace(/%latitude%/g, GeoInfo.latitude).replace(/%longitude%/g, GeoInfo.longitude);
					label = new TLabel();
					label.id = 'label9527';
					label.anchorLatLng = point;
					label.markerOffset = new GSize (0, 5);
					label.anchorPoint = 'bottomCenter';
					label.content = '<div style="padding-bottom: 8px; background: url(images/pointer.gif) no-repeat bottom;width:120px; "><div><b class="wpgm"><b class="wpgm1"><b></b></b><b class="wpgm2"><b></b></b><b class="wpgm3"></b><b class="wpgm4"></b><b class="wpgm5"></b></b> <div class="wpgm_content">'+info+'</div><b class="wpgm"><b class="wpgm5"></b><b class="wpgm4"></b><b class="wpgm3"></b><b class="wpgm2"><b></b></b><b class="wpgm1"><b></b></b></b></div> </div>';
					label.percentOpacity = 75;
					map.addTLabel(label);
				}
				var node = document.getElementById("online_user_count");
				if(!!node) node.innerHTML = String(count + 1) +" ";
				var node = document.getElementById("online_other_user_count");
				if(!!node) node.innerHTML = String(count) +" ";
				
				
				//地标数组
				//var markers_array = [];
				//var mm = new GMarkerManager(map, {borderPadding:1});
				for (i=0; i<count; i++){
						//获取经纬度信息
						var lat =	parseFloat(markersNode[i].getAttribute("lat"));
						var lng =	parseFloat(markersNode[i].getAttribute("lng"));
						var p = new GLatLng(lat, lng);
						
						var city = markersNode[i].getAttribute("city");
						var region = markersNode[i].getAttribute("region");
						var country = markersNode[i].getAttribute("country");
						//获取客户端信息及地理信息
						var info ="From ";
						info += (!city?"Unknown":city) + ", ";
						info += (!region?"Unknown":region) + ", ";
						info += (!country?"Unknown":country);
						
						var currIcon = new GIcon(baseIcon);
						currIcon.image = "./images/"+markersNode[i].getAttribute("country_code").toLowerCase()+".gif";
						
						//创建地标
						markers.push(createMarker(p, info, currIcon));
						
						
				}
			}catch(e){
				setTimeout("showOnlineUsersAlias()", 5000);
			}
			<?php if($wpgm_tracker_refresh_rate>0) echo "setTimeout(function(){showOnlineUsers()}, $wpgm_tracker_refresh_rate);" ; ?>
			
		}
	}
	request.send(null);
}


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
		
		
		map.setMapType(map.getMapTypes()[<?php echo $wpgm_map_type; ?>]);
		
		marker = new GMarker(point, {icon: myIcon});
		map.addOverlay(marker);
		map.panTo(point);
		
		
		showOnlineUsers();
	}
}
//]]>
</script>
</head>
<!--加载时调用load()函数加载地图，注意加上onunload="GUnload()"防止内存泄露-->
<body onload="load()" onunload="GUnload()">
<div id="map" style="text-align:center">
	<h3 style="font-size: 16px"> Please wait while loading <strong>Online Tracker</strong>... </h3>
	<div><a href="http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/" title="WP GuestMap">WP GuestMap</a> Powered By <a href="http://blog.codexpress.cn" title="CodeXpress.CN">CodeXpress.CN</a></div>
	<noscript> Please enable javascript to view Online Tracker! </noscript>
</div>
</body>
</html>
