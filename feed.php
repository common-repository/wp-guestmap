<?php
require_once(dirname(__FILE__).'/common.inc.php');

if (empty($wp)) {
	require_once('wp-config.php');
	wp('feed=rss');
}


global $wpgm_table_name;

$wpgm_feed_url = get_bloginfo('wpurl').'/wp-content/plugins/wp-guestmap/feed.php';

$timestamp = mktime();
$yesterday = floor($timestamp/3600/24) * 3600*24;
$today = ceil($timestamp/3600/24) * 3600*24;

$param = array();
$param['date'] = isset($_GET['date']) ? intval($_GET['date']) : $yesterday-1;
$param['auth'] = isset($_GET['auth']) && (get_option('wpgm_auth_key')==$_GET['auth']) ? $_GET['auth'] : '';

$date_needle = "'".date('Y-m-d', $param['date'])."'";

$results = $wpdb->get_results("SELECT `ip`, `time`, `lat`, `lng`, `city`, `region`, `country`, `country_code`, `ua` FROM `$wpgm_table_name` WHERE YEAR($date_needle) = YEAR(`time`) AND DAYOFYEAR($date_needle) = DAYOFYEAR(`time`) ");

//exit("SELECT `ip`, `time`, `lat`, `lng`, `city`, `region`, `country`, `country_code`, `ua` FROM `$wpgm_table_name` WHERE YEAR($date_needle) = YEAR(`time`) AND DAYOFYEAR($date_needle)-1 = DAYOFYEAR(`time`) ");


header('Content-Type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8"?'.'>';

if(!isset($_GET['visual'])){
?>

<rss version="2.0">
<channel>
	<title> WP GuestMap Stats Feed </title>
	<link><?php echo $wpgm_feed_url . '?auth=' . $param['auth']; ?></link>
	<description>This feed is based on statistics collected by WP GuestMap Stats Map.</description>
	<pubDate><?php echo date("D, d M Y H:i:s", $today) . " GMT"; ?></pubDate>
	<generator>WP GuestMap</generator>
	<language><?php echo get_option('rss_language'); ?></language>
	<item>
		<title><?php echo date("[Y/m/d]", $param['date']).' Daily Report'; ?></title>
		<link><?php echo $wpgm_feed_url . '?date=' . $param['date'] . '&amp;auth=' . $param['auth']; ?></link>
		<pubDate><?php echo date("D, d M Y H:i:s", $today) . " GMT"; ?></pubDate>
		<guid isPermaLink="false"><?php echo $wpgm_feed_url . '?date=' . $param['date'] . '&amp;auth=' . $param['auth']; ?></guid>
		<description><![CDATA[<?php
	
if($param['auth']){
	echo '<h3>Daily Report for ' . date("F d, Y ", $param['date']) .' ('. count($results) . ' Visitors)</h3>'; 
	echo '<p>Click <a href="http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/" title="WP GuestMap"><strong>HERE</strong></a> to get the latest version of WP GuestMap.</p>'; 
	echo '<p>Click <a href="http://maps.google.com/maps?f=q&ie=UTF8&z=2&q='. urlencode($wpgm_feed_url . '?date=' . $param['date'] . '&visual=enabled') . '" title="WP GuestMap Visual"><strong>HERE</strong></a> to view your visitor distribution visually.</p>'; 
	echo '<div id="wp-guestmap-data"><table width="90%" border="1" cellpadding="2" cellspacing="0" frame="border" rules="all"><thead><tr><th width="20%">IP</th><th width="25%">Location</th><th width="15%">Last Visit</th><th>User Agent</th></tr></thead><tbody>';
	if($results){
		foreach($results as $result){
			$location = $result->city? $result->city . ', ': '';
			$location .= $result->region? $result->region . ', ': '';
			$location .= $result->country? $result->country. ', ' : '';
			$location = xmlspecialchars(substr($location, 0, strlen($location)-2));
			
			echo '<tr><td>'.long2ip($result->ip).'</td><td>'.$location.'</td><td>'.$result->time.'</td><td>'.$result->ua.'</td></tr>';
		}
	}
	echo '</tbody></table></div>';
}else{
	echo '<h3>Sorry, you are not authorized to get the data.</h3>';
}
?>]]></description>
	</item>
</channel>
</rss>

<?php
}else{
?>

<rss version="2.0" xmlns:georss="http://www.georss.org/georss/">
<channel>
	<title> Daily Report for <?php echo date("F d, Y ", $param['date']) ?> (<?php echo count($results); ?> Visitors) </title>
	<link><?php echo $wpgm_feed_url . '?visual=enabled'; ?></link>
	<description><![CDATA[This feed is based on statistics collected by <a href="http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/" title="WP GuestMap"><strong>WP GuestMap</strong></a> Stats Map.]]></description>
	<pubDate><?php echo date("D, d M Y H:i:s", $today) . " GMT"; ?></pubDate>
	<generator>WP GuestMap</generator>
	<language><?php echo get_option('rss_language'); ?></language>
	
<?php
	if($results){
		$i=0;
		foreach($results as $result){
			$location = $result->city? $result->city . ', ': '';
			$location .= $result->region? $result->region . ', ': '';
			$location .= $result->country? $result->country. ', ' : '';
			$location = xmlspecialchars(substr($location, 0, strlen($location)-2));

?>
	<item>
		<title><?php echo $location; ?></title>
		<link>http://blog.codexpress.cn/php/wordpress-plugin-wp-guestmap/</link>
		<georss:point><?php echo $result->lat . ' '. $result->lng; ?></georss:point>
		<pubDate><?php echo date("D, d M Y H:i:s", $today) . " GMT"; ?></pubDate>
		<guid isPermaLink="false"><?php echo $wpgm_feed_url . '?date=' . $param['date'] . '&amp;visual='. $i; ?></guid>
		<description><![CDATA[<?php echo '<div>AT <strong>' .$result->time .'</strong></div> <div>FROM <strong>'. $location .'</strong></div>'; ?>]]></description>
	</item>
<?php
		$i++;
		}
	}
	
}
?>

</channel>
</rss>