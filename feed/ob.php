<?php
require_once( ABSPATH . '/wp-content/plugins/feed-up/xml2Array.php' );

class ob
{	
	function get_feed_tracking($mapping) {
		$feed_keywords = get_option('feed_keywords');
		$feed_track = @trim(get_option('feed_tracking')) != '' ? get_option('feed_tracking') : str_replace("www.", "", $_SERVER['HTTP_HOST']);

		if($feed_keywords != '')
		{
			$feed_trackings = explode('||', $feed_keywords);
			foreach($feed_trackings as $feed_tracking)
			{
				$tracking = explode(',', $feed_tracking);
				if(strtolower($mapping) == strtolower($tracking[0]))
					return $tracking[1];
			}
		}
		return $feed_track;
	}
	
	function get_feed_code($feed_post_id = 0, $mapping ='') {
		$feed_code = get_option('feed_code') != '' ? get_option('feed_code') : '34906';
		$feed_number = get_option('feed_number') != '' ? get_option('feed_number') : '6';
		
		if($mapping == '')
		{
			$mapping = get_post_meta($feed_post_id, "mapping", true);
			if(!$mapping)
			{
				$categories = get_the_category($feed_post_id);
				if(!empty($categories))
				{
					$mapping = $categories[0]->name;
				}
			}
		}
		$ip = $_SERVER['REMOTE_ADDR'];
		$feed_tracking = urlencode($this->get_feed_tracking($mapping));
		$serveUrl 	= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);	
		$agent = urlencode($_SERVER["HTTP_USER_AGENT"]);

		$oburl = "http://kudo1.com/partners/default/results.php?q=".urlencode($mapping)."&pai=".urlencode($feed_code)."&p3=".$feed_tracking."&c=".($feed_number)."&o=0&ip=".$ip."&ua=".$agent."&serveUrl=".urlencode($serveUrl);

		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $oburl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$feeddata=curl_exec($ch);

		$rss = new xml2Array();
		$results = $rss -> parse($feeddata);
		
		
		$feed_body = $results[0]['children'];
		if($feed_body):
			$i=1;
			$yFeed[0] = '';
			foreach($feed_body as $row)
			{
					$yFeed[$i]['no'] 				= $i;
					$yFeed[$i]['CLICK_LINK'] 		= @$row['children'][0]['tagData'];
					if(get_option('feed_redirect') == 'yes')
						$yFeed[$i]['CLICK_LINK'] = 'http://'.$_SERVER['SERVER_NAME'].'/index.php?redirurl='.base64_encode($yFeed[$i]['CLICK_LINK']);
					$yFeed[$i]['TITLE'] 			= @$row['children'][1]['tagData'];
					$yFeed[$i]['DESCRIPTION'] 		= @$row['children'][2]['tagData'];
					$yFeed[$i]['SITEHOST']			= @$row['children'][3]['tagData'];
					$yFeed[$i]['PPC']				= @$row['children'][4]['tagData']*100;
				$i++;
		
			}

			return $yFeed;
		else:
			return '';
		endif;
	}
}
?>