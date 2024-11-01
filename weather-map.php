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

http_modified('wpgm_weather_map_cached'.empty($_COOKIE['wpgm_widget_disabled']));

if (empty($wp)) {
	wp();
}


$wpgm_api_key = get_option('wpgm_api_key');
$wpgm_feed_api_key = get_option('wpgm_feed_api_key');
$wpgm_location_param = get_option('wpgm_location_param');
$wpgm_unit = get_option('wpgm_unit');

$wpgm_weather_url = "http://xml.weather.yahoo.com/forecastrss?p=$wpgm_location_param&u=$wpgm_unit";

$wpgm_map_type = $_GET['mt'];
$wpgm_zoom_level = $_GET['zl'];
$wpgm_default_center = $_GET['dc'];

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta http-equiv="last-modified" content="<?php echo $wpgm_last_modified; ?>" />
<title>WP GuestMap  &raquo;  Weather Map v<?php echo $wpgm_version; ?></title>
<style type="text/css">
@import url("widget.css");
</style> 
<!--导入Google Maps API库文件。注意将本代码中的API Key替换为前文申请到的API Key-->
<script src="<?php echo $wpgm_api_key; ?>" type="text/javascript"></script>
<script src="http://www.google.com/jsapi?key=<?php echo $wpgm_feed_api_key; ?>" type="text/javascript"></script>
<script src="cache.php" type="text/javascript"></script>
<script src="tlabel.js" type="text/javascript"></script>
<script src="gbutton.js" type="text/javascript"></script>

<script type="text/javascript">
//<![CDATA[

google.load("feeds", "1");

var map, point, marker, label;
//RSS命名空间
var nsGeo = "http://www.w3.org/2003/01/geo/wgs84_pos#";
var nsYWeather = "http://xml.weather.yahoo.com/ns/rss/1.0";
var iconUrlTemplate = "http://st.msn.com/as/wea3/i/en-US/saw/{code}.gif";

//网页加载时用于初始化Google地图
function initialize(){
	if (GBrowserIsCompatible()){
		//创建GMap2对象
		map = new GMap2(document.getElementById("map"));
		point = new GLatLng(30.581355, 114.335328);
		
		//缩放级别设得比较小，如不合适可自行设定合适的级别
		map.setCenter(new GLatLng(<?php echo $wpgm_default_center; ?>), <?php echo $wpgm_zoom_level; ?>);
		map.addControl(new GSmallZoomControl());
		map.addControl(new GButton({caption:"<img src='./images/close.gif' alt='Hide this widget' title='Hide this widget' style='border:0;'>", style:"border:0;margin:0;padding:0;cursor:pointer", callback:function(){if(!getCookie("wpgm_widget_disabled"))setCookie("wpgm_widget_disabled", 1, 720);location.reload();}}));
		
		map.setMapType(map.getMapTypes()[<?php echo $wpgm_map_type; ?>]);
		loadWeather();
	}
}

function loadWeather(){
	var url = "<?php echo $wpgm_weather_url; ?>&rand="+Math.random();
	var feed = new google.feeds.Feed(url);
	feed.setResultFormat(google.feeds.Feed.XML_FORMAT);
	feed.load(function(result){
		if(!result.error){
			//XML文档
 			var xmldoc = result.xmlDocument;
 			//经纬度
 			var loc = google.feeds.getElementsByTagNameNS(xmldoc.documentElement, nsYWeather, "location")[0];
 			var city = loc.getAttribute("city") + ", " + loc.getAttribute("country");
 			var latitude = google.feeds.getElementsByTagNameNS(xmldoc.documentElement, nsGeo, "lat")[0].firstChild.nodeValue;
 			var longitude = google.feeds.getElementsByTagNameNS(xmldoc.documentElement, nsGeo, "long")[0].firstChild.nodeValue;
 			point = new GLatLng(latitude, longitude);
 			
 			var forecasts = google.feeds.getElementsByTagNameNS(xmldoc.documentElement, nsYWeather , "forecast");
 			var units = google.feeds.getElementsByTagNameNS(xmldoc.documentElement, nsYWeather , "units");
			var unit = (units[0].getAttribute("temperature")=="C")?"&#8451;":"&#8457;";
 			
 			var myIcon= new GIcon();
 			myIcon.image = "http://st.msn.com/as/wea3/i/en-US/sab/" +forecasts[0].getAttribute("code")+ ".gif";
 			myIcon.shadow = "./images/shadow.gif";
 			myIcon.iconSize = new GSize(35, 21);
 			myIcon.shadowSize = new GSize(0, 0);
 			myIcon.iconAnchor = new GPoint(18, 0);
 			
 			var info = "<table align='center' border='0' cellpadding='0' cellspacing='0'><tbody><tr><td colspan='3' style='font-size:14px'><strong>"+city+"</strong></td></tr><tr><td valign='middle'><strong>"+forecasts[0].getAttribute("day")+"</strong></td><td align='center' valign='middle'><img width='35' height='21' alt='"+forecasts[0].getAttribute("text")+"'title='"+forecasts[0].getAttribute("text")+"' src='"+iconUrlTemplate.replace("{code}", forecasts[0].getAttribute("code"))+"'/></td><td align='center' valign='middle'>"+forecasts[0].getAttribute("low")+unit+" - "+forecasts[0].getAttribute("high")+unit+"</td></tr><tr><td valign='middle'><strong>"+forecasts[1].getAttribute("day")+"</strong></td><td align='center' valign='middle'><img width='35' height='21' alt='"+forecasts[1].getAttribute("text")+"'title='"+forecasts[1].getAttribute("text")+"' src='"+iconUrlTemplate.replace("{code}", forecasts[1].getAttribute("code"))+"'/></td><td align='center' valign='middle'>"+forecasts[1].getAttribute("low")+unit+" - "+forecasts[1].getAttribute("high")+unit+"</td></tr></tbody></table>";
 			//标记访客位置
 			marker = new GMarker(point, {icon: myIcon});
 			map.addOverlay(marker);
 			
 			setTimeout(function(){
 				var label = new TLabel();
 				label.id = 'labelweather';
 				label.anchorLatLng = point;
	 			label.anchorPoint = 'bottomCenter';
	 			label.content = '<div style="padding-bottom: 8px; background: url(images/pointer.gif) no-repeat bottom; width:140px"><div><b class="wpgm"><b class="wpgm1"><b></b></b><b class="wpgm2"><b></b></b><b class="wpgm3"></b><b class="wpgm4"></b><b class="wpgm5"></b></b> <div class="wpgm_content">'+info+'</div><b class="wpgm"><b class="wpgm5"></b><b class="wpgm4"></b><b class="wpgm3"></b><b class="wpgm2"><b></b></b><b class="wpgm1"><b></b></b></b></div> </div>';
 				label.percentOpacity = 75;
 				map.addTLabel(label);
 			},500);
 		}
	});
}

google.setOnLoadCallback(initialize);
//]]>
</script>
</head>
<!--加载时调用load()函数加载地图，注意加上onunload="GUnload()"防止内存泄露-->
<body onunload="GUnload()">
<div id="map" style="text-align:center">
	<h3 style="font-size: 16px"> Please wait while loading <strong>Weather Map</strong>... </h3>
	<div><a href="http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/" title="WP GuestMap">WP GuestMap</a> Powered By <a href="http://blog.codexpress.cn" title="CodeXpress.CN">CodeXpress.CN</a></div>
	<noscript> Please enable javascript to view Weather Map! </noscript>
</div>
</body>
</html>
