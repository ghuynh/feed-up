<?php
require_once( ABSPATH . '/wp-content/plugins/feed-up/xml2Array.php' );

class tz
{
	function get_feed_code($feed_post_id = 0, $mapping ='') {
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
		$ip = @urlencode( $_SERVER['REMOTE_ADDR'] );
		$domain = str_replace("www.", "", @urlencode( $_SERVER['SERVER_NAME'] ));
		$kw = @urlencode(str_replace(" ", "+", $mapping));
		$rf = @urlencode( $_SERVER['HTTP_REFERER'] );
		$ua = @urlencode($_SERVER["HTTP_USER_AGENT"]);
			
		$tzurl = "http://partners.trafficz.com/z.php?domain=".$domain."&kw=".$kw."&rf=".$rf."&ua=".$ua."&nsr=".$feed_number."&ip=".$ip;
			
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $tzurl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$feeddata=curl_exec($ch);

		$rss = new xml2Array();
		$results = $rss -> parse($feeddata);
		
		
		$feed_body = $results[0]['children'][0]['children'];
		
		if($feed_body):
			$i=1;
			$yFeed[0] = '';
			foreach($feed_body as $row)
			{
					$yFeed[$i]['no'] 				= $i;	
					$yFeed[$i]['CLICK_LINK'] 		= @'http://partners.trafficz.com/'.trim($row['children'][3]['tagData']);
					if(get_option('feed_redirect') == 'yes')
						$yFeed[$i]['CLICK_LINK'] = 'http://'.$_SERVER['SERVER_NAME'].'/index.php?redirurl='.base64_encode($yFeed[$i]['CLICK_LINK']);
					$yFeed[$i]['TITLE'] 			= @$row['children'][0]['tagData'];
					$yFeed[$i]['DESCRIPTION'] 		= @$row['children'][1]['tagData'];
					$yFeed[$i]['SITEHOST'] 			= @$row['children'][2]['tagData'];		
					$yFeed[$i]['MASKING_URL']		= @str_replace('partners.trafficz.com', $_SERVER['SERVER_NAME'],$yFeed[$i]['CLICK_LINK']);
					$yFeed[$i]['PPC']				= '';
				$i++;
		
			}
			return $yFeed;
		else:
			return '';
		endif;
	}
}
?>