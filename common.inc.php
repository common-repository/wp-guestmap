<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-config.php');
$wpgm_table_name = $table_prefix.'guestmap';
$wpgm_version = '1.8';
$wpgm_domain = 'wpgm';
$wpgm_last_modified = 'Fri, 30 Nov 2007 12:00:00 GMT';

function SERVER_VAR($varName) {
	global $HTTP_SERVER_VARS;
	global $HTTP_ENV_VARS;

	if(!isset($_SERVER)){
		$_SERVER = $HTTP_SERVER_VARS;
		if(!isset($_SERVER["REMOTE_ADDR"]))
			$_SERVER = $HTTP_ENV_VARS; // must be Apache
	}
	
	if(isset($_SERVER[$varName]))
		return $_SERVER[$varName];
	else
		return "";
}
function http_modified($identifier) {
	global $wpgm_last_modified;
	global $wpgm_version;
	$last_modified = $wpgm_last_modified;
	$etag = '"'.md5($last_modified.$identifier.$wpgm_version).'"';
	$client_etag = SERVER_VAR('HTTP_IF_NONE_MATCH') ? trim(SERVER_VAR('HTTP_IF_NONE_MATCH')) : false;
	$client_last_modified = SERVER_VAR('HTTP_IF_MODIFIED_SINCE') ? trim(SERVER_VAR('HTTP_IF_MODIFIED_SINCE')) : 0;
	$client_last_modified_timestamp = strtotime($client_last_modified);
	$last_modified_timestamp = strtotime($last_modified);

	if(($client_last_modified && $client_etag) ? (($client_last_modified_timestamp == $last_modified_timestamp) && ($client_etag == $etag)) : (($client_last_modified_timestamp == $last_modified_timestamp) || ($client_etag == $etag))){
		header('Not Modified',true,304);
		exit();
	}else{
		header('Last-Modified:'.$last_modified);
		header('ETag:'.$etag);
	}
}

function xmlspecialchars($input){

	return strtr($input,array(
		chr(1)=>"&#1;",
		chr(2)=>"&#2;",
		chr(3)=>"&#3;",
		chr(4)=>"&#4;",
		chr(5)=>"&#5;",
		chr(6)=>"&#6;",
		chr(7)=>"&#7;",
		chr(8)=>"&#8;",
		chr(9)=>"&#9;",
		chr(10)=>"&#10;",
		chr(11)=>"&#11;",
		chr(12)=>"&#12;",
		chr(13)=>"&#13;",
		chr(14)=>"&#14;",
		chr(15)=>"&#15;",
		chr(16)=>"&#16;",
		chr(17)=>"&#17;",
		chr(18)=>"&#18;",
		chr(19)=>"&#19;",
		chr(20)=>"&#20;",
		chr(21)=>"&#21;",
		chr(22)=>"&#22;",
		chr(23)=>"&#23;",
		chr(24)=>"&#24;",
		chr(25)=>"&#25;",
		chr(26)=>"&#26;",
		chr(27)=>"&#27;",
		chr(28)=>"&#28;",
		chr(29)=>"&#29;",
		chr(30)=>"&#30;",
		chr(31)=>"&#31;",
		chr(33)=>"&#33;",
		chr(34)=>"&#34;",
		chr(35)=>"&#35;",
		chr(36)=>"&#36;",
		chr(38)=>"&amp;",
		chr(39)=>"&#39;",
		chr(42)=>"&#42;",
		chr(43)=>"&#43;",
		chr(44)=>"&#44;",
		chr(59)=>"&#59;",
		chr(60)=>"&#60;",
		chr(62)=>"&#62;",
		chr(64)=>"&#64;",
		chr(92)=>"&#92;",
		chr(94)=>"&#94;",
		chr(96)=>"&#96;",
		chr(123)=>"&#123;",
		chr(124)=>"&#124;",
		chr(125)=>"&#125;",
		chr(126)=>"&#126;",
		chr(127)=>"&#127;",
		chr(128)=>"&#128;",
		chr(129)=>"&#129;",
		chr(130)=>"&#130;",
		chr(131)=>"&#131;",
		chr(132)=>"&#132;",
		chr(133)=>"&#133;",
		chr(134)=>"&#134;",
		chr(135)=>"&#135;",
		chr(136)=>"&#136;",
		chr(137)=>"&#137;",
		chr(138)=>"&#138;",
		chr(139)=>"&#139;",
		chr(140)=>"&#140;",
		chr(141)=>"&#141;",
		chr(142)=>"&#142;",
		chr(143)=>"&#143;",
		chr(144)=>"&#144;",
		chr(145)=>"&#145;",
		chr(146)=>"&#146;",
		chr(147)=>"&#147;",
		chr(148)=>"&#148;",
		chr(149)=>"&#149;",
		chr(150)=>"&#150;",
		chr(151)=>"&#151;",
		chr(152)=>"&#152;",
		chr(153)=>"&#153;",
		chr(154)=>"&#154;",
		chr(155)=>"&#155;",
		chr(156)=>"&#156;",
		chr(157)=>"&#157;",
		chr(158)=>"&#158;",
		chr(159)=>"&#159;",
		chr(160)=>"&#160;",
		chr(161)=>"&#161;",
		chr(162)=>"&#162;",
		chr(163)=>"&#163;",
		chr(164)=>"&#164;",
		chr(165)=>"&#165;",
		chr(166)=>"&#166;",
		chr(167)=>"&#167;",
		chr(168)=>"&#168;",
		chr(169)=>"&#169;",
		chr(170)=>"&#170;",
		chr(171)=>"&#171;",
		chr(172)=>"&#172;",
		chr(173)=>"&#173;",
		chr(174)=>"&#174;",
		chr(175)=>"&#175;",
		chr(176)=>"&#176;",
		chr(177)=>"&#177;",
		chr(178)=>"&#178;",
		chr(179)=>"&#179;",
		chr(180)=>"&#180;",
		chr(181)=>"&#181;",
		chr(182)=>"&#182;",
		chr(183)=>"&#183;",
		chr(184)=>"&#184;",
		chr(185)=>"&#185;",
		chr(186)=>"&#186;",
		chr(187)=>"&#187;",
		chr(188)=>"&#188;",
		chr(189)=>"&#189;",
		chr(190)=>"&#190;",
		chr(191)=>"&#191;",
		chr(192)=>"&#192;",
		chr(193)=>"&#193;",
		chr(194)=>"&#194;",
		chr(195)=>"&#195;",
		chr(196)=>"&#196;",
		chr(197)=>"&#197;",
		chr(198)=>"&#198;",
		chr(199)=>"&#199;",
		chr(200)=>"&#200;",
		chr(201)=>"&#201;",
		chr(202)=>"&#202;",
		chr(203)=>"&#203;",
		chr(204)=>"&#204;",
		chr(205)=>"&#205;",
		chr(206)=>"&#206;",
		chr(207)=>"&#207;",
		chr(208)=>"&#208;",
		chr(209)=>"&#209;",
		chr(210)=>"&#210;",
		chr(211)=>"&#211;",
		chr(212)=>"&#212;",
		chr(213)=>"&#213;",
		chr(214)=>"&#214;",
		chr(215)=>"&#215;",
		chr(216)=>"&#216;",
		chr(217)=>"&#217;",
		chr(218)=>"&#218;",
		chr(219)=>"&#219;",
		chr(220)=>"&#220;",
		chr(221)=>"&#221;",
		chr(222)=>"&#222;",
		chr(223)=>"&#223;",
		chr(224)=>"&#224;",
		chr(225)=>"&#225;",
		chr(226)=>"&#226;",
		chr(227)=>"&#227;",
		chr(228)=>"&#228;",
		chr(229)=>"&#229;",
		chr(230)=>"&#230;",
		chr(231)=>"&#231;",
		chr(232)=>"&#232;",
		chr(233)=>"&#233;",
		chr(234)=>"&#234;",
		chr(235)=>"&#235;",
		chr(236)=>"&#236;",
		chr(237)=>"&#237;",
		chr(238)=>"&#238;",
		chr(239)=>"&#239;",
		chr(240)=>"&#240;",
		chr(241)=>"&#241;",
		chr(242)=>"&#242;",
		chr(243)=>"&#243;",
		chr(244)=>"&#244;",
		chr(245)=>"&#245;",
		chr(246)=>"&#246;",
		chr(247)=>"&#247;",
		chr(248)=>"&#248;",
		chr(249)=>"&#249;",
		chr(250)=>"&#250;",
		chr(251)=>"&#251;",
		chr(252)=>"&#252;",
		chr(253)=>"&#253;",
		chr(254)=>"&#254;",
		chr(255)=>"&#255;"
	));
}

function show_disabled_page() {
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<meta http-equiv="last-modified" content="<?php echo $wpgm_last_modified; ?>" />
<title>WP GuestMap  &raquo;  Weather Map v<?php echo $wpgm_version; ?></title>
<body>
<div id="map">
	<h3 style="font-size: 16px"> WP GuestMap is disabled for you! </h3>
	<div>If you wish to enable this widget again, please click <a href="<?php echo SERVER_VAR('REQUEST_URI'); ?>&amp;activate=true" title="Enable WP GuestMap">HERE</a> to activate it.</div>
</div>
</body>
</html>
<?php
}
?>