<?php
require_once(dirname(__FILE__).'/common.inc.php');
if(!isset($_GET['q'])) die('wrong parameter');

list($wpgm_api_key, $wpgm_zoom_level, $wpgm_map_type, $wpgm_default_center, $wpgm_welcome_message) = explode('|', base64_decode($_GET['q']));

if(!isset($_GET['type']) || ($_GET['type']!='stats')){
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>WP GuestMap</title>
<style type="text/css">
body, html, #map{
	margin:0px;
	width: 100%;
	height: 100%;
	font-size: 12px;
}
.wpgm{
display:block;
}
.wpgm *{
display:block;
height:1px;
overflow:hidden;
background:#f2efe9;
}
.wpgm1{
border-right:1px solid #686664;
padding-right:1px;
margin-right:3px;
border-left:1px solid #686664;
padding-left:1px;
margin-left:3px;
background:#b5b3ae;
}
.wpgm2{
border-right:1px solid #181717;
border-left:1px solid #181717;
padding:0px 1px;
background:#c4c1bc;
margin:0px 1px;
}
.wpgm3{
border-right:1px solid #c4c1bc;
border-left:1px solid #c4c1bc;
margin:0px 1px;
}
.wpgm4{
border-right:1px solid #686664;
border-left:1px solid #686664;
}
.wpgm5{
border-right:1px solid #b5b3ae;
border-left:1px solid #b5b3ae;
}
.wpgm_content{
padding:0px 5px;
background:#f2efe9;
}
</style> 
<!--导入Google Maps API库文件。注意将本代码中的API Key替换为前文申请到的API Key-->
<script src="<?php echo $wpgm_api_key; ?>" type="text/javascript"></script>
<script src="cache.php" type="text/javascript"></script>

<script type="text/javascript">
//<![CDATA[
// TLabel() GMaps API extension copyright 2005-2006 Tom Mangan
// http://gmaps.tommangan.us/tlabel.html
// free for non-commercial use
function TLabel(){}
TLabel.prototype.initialize=function(a){
 this.parentMap=a;
 var b=document.createElement('span');
 b.setAttribute('id',this.id);
 b.innerHTML=this.content;
 document.body.appendChild(b);
 b.style.position='absolute';
 b.style.zIndex=1;
 if(this.percentOpacity){this.setOpacity(this.percentOpacity);}
 this.w = document.getElementById(this.id).offsetWidth;
 this.h = document.getElementById(this.id).offsetHeight;
 this.mapTray=a.getPane(G_MAP_MAP_PANE);
 this.mapTray.appendChild(b);
 if(!this.markerOffset){this.markerOffset=new GSize(0,0);}
 this.setPosition();
 GEvent.bind(a,"zoomend",this,function(){this.setPosition()});
 GEvent.bind(a,"moveend",this,function(){this.setPosition()});
}
TLabel.prototype.setPosition=function(a){
 if(a){this.anchorLatLng=a;}
 var b=this.parentMap.fromLatLngToDivPixel(this.anchorLatLng);
 var x=parseInt(b.x);
 var y=parseInt(b.y);
 with(Math){switch(this.anchorPoint){
  case 'topLeft':break;
  case 'topCenter':x-=floor(this.w/2);break;
  case 'topRight':x-=this.w;break;
  case 'midRight':x-=this.w;y-=floor(this.h/2);break;
  case 'bottomRight':x-=this.w;y-=this.h;break;
  case 'bottomCenter':x-=floor(this.w/2);y-=this.h;break;
  case 'bottomLeft':y-=this.h;break;
  case 'midLeft':y-=floor(this.h/2);break;
  case 'center':x-=floor(this.w/2);y-=floor(this.h/2);break;
  default:break;
 }}
 var d=document.getElementById(this.id);
 d.style.left=x-this.markerOffset.width+'px';
 d.style.top=y-this.markerOffset.height+'px';
}
TLabel.prototype.setOpacity=function(b){
 if(b<0){b=0;} if(b>100){b=100;}
 var c=b/100;
 var d=document.getElementById(this.id);
 if(typeof(d.style.filter)=='string'){d.style.filter='alpha(opacity:'+b+')';}
 if(typeof(d.style.KHTMLOpacity)=='string'){d.style.KHTMLOpacity=c;}
 if(typeof(d.style.MozOpacity)=='string'){d.style.MozOpacity=c;}
 if(typeof(d.style.opacity)=='string'){d.style.opacity=c;}
}
GMap2.prototype.addTLabel=function(a){
 a.initialize(this);
}
GMap2.prototype.removeTLabel=function(a){
 var b=document.getElementById(a.id);
 this.getPane(G_MAP_MAP_PANE).removeChild(b);
 delete(b);
}


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
		//创建一个自定义的GIcon
		
		var myIcon= new GIcon();
		myIcon.image = "./images/flag_"+(GeoInfo.country_code).toLowerCase()+".gif";
		myIcon.shadow = "./images/shadow.gif";
		myIcon.iconSize = new GSize(18, 12);
		myIcon.shadowSize = new GSize(1, 1);
		myIcon.iconAnchor = new GPoint(9, 0);
		
		
		map.setMapType(map.getMapTypes()[<?php echo $wpgm_map_type; ?>]);
		
		var info = ("<?php echo $wpgm_welcome_message; ?>").replace(/%city%/g, GeoInfo.city).replace(/%country%/g, GeoInfo.country).replace(/%country_code%/g, GeoInfo.country_code).replace(/%latitude%/g, GeoInfo.latitude).replace(/%longitude%/g, GeoInfo.longitude);
		//标记访客位置
		marker = new GMarker(point, {icon: myIcon, title: info});
		map.addOverlay(marker);
		
		var label = new TLabel();
		label.id = 'label924';
		label.anchorLatLng = point;
		label.anchorPoint = 'bottomCenter';
		label.content = '<div style="padding-bottom: 8px; background: url(images/pointer.png) no-repeat bottom;width:120px; "><div><b class="wpgm"><b class="wpgm1"><b></b></b><b class="wpgm2"><b></b></b><b class="wpgm3"></b><b class="wpgm4"></b><b class="wpgm5"></b></b> <div class="wpgm_content">'+info+'</div><b class="wpgm"><b class="wpgm5"></b><b class="wpgm4"></b><b class="wpgm3"></b><b class="wpgm2"><b></b></b><b class="wpgm1"><b></b></b></b></div> </div>';
		label.percentOpacity = 75;
		
		//移动到当前访客的位置
		setTimeout(function(){map.panTo(point);map.addTLabel(label);}, 1000);
	}
}
//]]>
</script>
</head>
<!--加载时调用load()函数加载地图，注意加上onunload="GUnload()"防止内存泄露-->
<body onload="load()" onunload="GUnload()">
<div id="map" style="text-align:center">
	<h3 style="font-size: 16px"> Please wait while loading... </h3>
	<div>Powered By <a href="http://blog.codexpress.cn" title="CodeXpress.CN">CodeXpress.CN</a></div>
	<noscript> <div>Please enable javascript to view the map!</div> </noscript>
</div>
</body>
</html>
<?php
	
}else{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>WP GuestMap</title>
<style type="text/css">
body, html, #map{
	margin:0px;
	width: 100%;
	height: 100%;
	font-size: 12px;
}
</style> 
<!--导入Google Maps API库文件。注意将本代码中的API Key替换为前文申请到的API Key-->
<script src="<?php echo $wpgm_api_key; ?>" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[

    //全局GMap
    var map;
    
    //自定义icon
    var myIcon= new GIcon();
    myIcon.image = "http://labs.google.com/ridefinder/images/mm_20_yellow.png";
    myIcon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
    myIcon.iconSize = new GSize(12, 20);
    myIcon.shadowSize = new GSize(22, 20);
    myIcon.iconAnchor = new GPoint(6, 20);
    myIcon.infoWindowAnchor = new GPoint(5, 1);
    
    //地标数组
    var markers_array = new Array();
    
    //创建地标并监听点击事件
    function createMarker(point,title,icon, html)
    {
      //创建地标
      var marker = new GMarker(point, {icon:icon, title:title});
      /*/以下部分用于监听点击事件
      GEvent.addListener(marker, "click", function()
                {
                  marker.openInfoWindowHtml(html);
                }
      );*/
      return marker;
    }

    
    //网页加载时用于初始化Google地图
    function load()
    {
      if (GBrowserIsCompatible())       {
        //创建GMap2对象
        map = new GMap2(document.getElementById("map"));
		
        //缩放级别设得比较小，如不合适可自行设定合适的级别
        map.setCenter(new GLatLng(<?php echo $wpgm_default_center; ?>), <?php echo $wpgm_zoom_level; ?>);
		map.addControl(new GSmallZoomControl());
		map.setMapType(map.getMapTypes()[<?php echo $wpgm_map_type; ?>]);
        //AJAX获取地标信息
        //此处动态生成XML文件的URL，并加载
        GDownloadUrl("<?php echo dirname(SERVER_VAR('SCRIPT_NAME')); ?>/output.php?rand="+Math.random(), function (doc)
            {
               //解析XML文件
               var xmlDoc = GXml.parse(doc);
               
               //获取地标节点
               var markers = xmlDoc.documentElement.getElementsByTagName("location");
               for (var i = 0; i < markers.length; i++)
               {
               	   //获取经纬度信息
               	   var lat =  parseFloat(markers[i].getAttribute("lat"));
               	   var lng =  parseFloat(markers[i].getAttribute("lng"));
               	   var point = new GLatLng(lat, lng);
               	   
               	   //获取客户端信息及地理信息
               	   var info = markers[i].getAttribute("visits") + " visit(s) from ";
               	   info += markers[i].getAttribute("city") + ", ";
               	   info += markers[i].getAttribute("region") + ", ";
               	   info += markers[i].getAttribute("country");
               	   
               	   //创建地标
               	   var marker = createMarker(point, info, myIcon);
               	   
                   
               	   //存入地标数组
               	   markers_array.push(marker);
               }
               
               //创建GMarkerManager
               var mm = new GMarkerManager(map, {borderPadding:1});
               
               //添加地标数组
               mm.addMarkers(markers_array, 0);
               
               //显示地标
               mm.refresh();
               
            }
        );
      }
    }
    
//]]>
</script>
</head>
<!--加载时调用load()函数加载地图，注意加上onunload="GUnload()"防止内存泄露-->
<body onload="load()" onunload="GUnload()">
<div id="map" style="text-align:center">
	<h3 style="font-size: 16px"> Please wait while loading... </h3>
	<div>Powered By <a href="http://blog.codexpress.cn" title="CodeXpress.CN">CodeXpress.CN</a></div>
	<noscript> <div>Please enable javascript to view the map!</div> </noscript>
</div>
</body>
</html>
<?php
}	
?>
