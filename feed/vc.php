<?php
require_once( ABSPATH . '/wp-content/plugins/feed-up/xml2Array.php' );

class vc
{	
	function get_feed_tracking($mapping) {
		$feed_keywords = get_option('feed_keywords');
		if($feed_keywords != '')
		{
			$feed_trackings = explode('||', $feed_keywords);
			foreach($feed_trackings as $feed_tracking)
			{
				$tracking = explode(',', $feed_tracking);
				if(strtolower($mapping) == strtolower($tracking[0]))
					return $tracking[1];
			}
			return get_option('feed_code');
		}
		else
			return get_option('feed_code');
	}
	
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
		$ip = $_SERVER['REMOTE_ADDR'];
		$feed_code = urlencode($this->get_feed_tracking($mapping));
		$agent = urlencode($_SERVER["HTTP_USER_AGENT"]);
		$via = @urlencode($_SERVER['HTTP_VIA']);
		$xfwd = @urlencode($_SERVER['HTTP_X_FORWARDED_FOR']);
		$serveurl = @urlencode( $_SERVER['SERVER_NAME'] );

		$vcurl = "http://feed.validclick.com/?affid=".$feed_code."&maxcount=".($feed_number)."&search=".urlencode($mapping)."&xfwd=".$xfwd."&xflag=show-extras&xtype=1&xformat=xml&ip=".$ip."&via=".$via."&agent=".$agent."&serveurl=".$serveurl;
			
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $vcurl);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$feeddata=curl_exec($ch);

		$rss = new xml2Array();
		$results = $rss -> parse($feeddata);
		
		
		$feed_body = $results[0]['children'];
		
		if($feed_body):
			$i=1;
			$yFeed[0] = '<script type="text/javascript" src="http://feed.validclick.com/check.php?affid='.$feed_code.'"></script>';
			foreach($feed_body as $row)
			{
					$yFeed[$i]['no'] 				= $i;	
					$yFeed[$i]['CLICK_LINK'] 		= @urldecode($row['children'][8]['tagData']);
					if(get_option('feed_redirect') == 'yes')
						$yFeed[$i]['CLICK_LINK'] = 'http://'.$_SERVER['SERVER_NAME'].'/index.php?redirurl='.base64_encode($yFeed[$i]['CLICK_LINK']);
					$yFeed[$i]['TITLE'] 			= @$row['children'][5]['tagData'];
					$yFeed[$i]['DESCRIPTION'] 		= @$row['children'][6]['tagData'];
					$yFeed[$i]['SITEHOST'] 			= @$row['children'][7]['tagData'];
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