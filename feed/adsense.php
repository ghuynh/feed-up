<?php
require_once( ABSPATH . '/wp-content/plugins/feed-up/xml2Array.php' );

class adsense
{
	function get_feed_code($feed_post_id = 0, $mapping ='') {
		$feed_code = get_option('feed_code') != '' ? get_option('feed_code') : '';
		return $feed_code;
	}
}
?>