<?php
require_once(dirname(__FILE__).'/common.inc.php');
require_once(dirname(__FILE__).'/geoipregionvars.php');

if (empty($wp)) {
	wp();
}

$pagesize = intval(get_option('wpgm_stats_pagesize'));
$pagesize = ($pagesize>0)? $pagesize:25;
$start = isset($_GET['page'])? $_GET['page']*$pagesize : 0;

// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");
header("Content-type: text/xml; charset=utf-8");

global $wpgm_table_name;

$wpgm_stats_start_date = get_option('wpgm_stats_start_date');

$where_clause = $wpgm_stats_start_date? "WHERE `time`>'$wpgm_stats_start_date'" : '';

$results = $wpdb->get_results("SELECT `lat`, `lng`, `city` , `region` , `country` , `country_code` , COUNT( * ) as `visits` FROM `$wpgm_table_name` $where_clause GROUP BY `hash` LIMIT $start, $pagesize; ");

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<!-- generator="WP GuestMap" -->';
echo '<locations>';


foreach($results as $result){
	echo '<location lat="'.$result->lat.'" lng="'.$result->lng.'" city="'.xmlspecialchars($result->city).'" region="'.xmlspecialchars($result->region).'" country="'.xmlspecialchars($result->country).'" country_code="'.$result->country_code.'" visits="'.$result->visits.'" />';
}

echo '</locations>';
?>
