<?php
header("Content-type: text/javascript; charset=utf-8");

if(!isset($_GET['reloaded'])){
?>
function setCookie(name,value,expireHours){
	var cookieString=name+"="+escape(value);
	if(!isNaN(expireHours)){
		var date=new Date();
		date.setTime(date.getTime()+expireHours*3600*1000);
		cookieString+="; expires="+date.toGMTString();
	}
	document.cookie=cookieString;
}
function getCookie(name){
	var strCookie=document.cookie;
	var arrCookie=strCookie.split("; ");
	for(var i=0;i <arrCookie.length;i++){
		var arr=arrCookie[i].split("=");
		if(arr[0]==name)return unescape(arr[1]);
	}
	return "";
}

function loadGeoInfo (){
	var arr = getCookie("GeoInfo").split("|");
	if(arr.length==6){
		this.latitude = arr[0];
		this.longitude = arr[1];
		this.city = arr[2];
		this.country = arr[3];
		this.region_code = arr[4];
		this.country_code = arr[5];
		window.GeoInfo = this;
	}else{
		document.write("<scr"+"ipt src=\"http://j.maxmind.com/app/geoip.js\" type=\"text/javascript\"></scr"+"ipt>");
		document.write("<scr"+"ipt src=\"<?php echo $_SERVER["SCRIPT_NAME"]; ?>?reloaded=true\" type=\"text/javascript\"></scr"+"ipt>");
	}
}
<?php
}else{
?>
setCookie("GeoInfo", [geoip_latitude(), geoip_longitude(), geoip_city(), geoip_country_name(), geoip_region(), geoip_country_code()].join("|" ));
<?php
}
?>

if(location.href.indexOf('weather-map.php')==-1) loadGeoInfo();
