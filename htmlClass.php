<?php
/**
 * Html template class
 * Author: George Huynh on 29/01/2010
 * 
 */
// Rss parse class
class Html{

	var $Count=0;
    
	var $Template;
	
	function Html() {
        return true;
    } 

	function parseSponsored($htmlCode,$feedArray,$adNum='6')
	{
		$htmlCode = stripslashes($htmlCode);
		if(is_array($feedArray) && $htmlCode != '')
		{
			$extrahtml = $feedArray[0];
			for($m=1;$m<$adNum;$m++)
			{
				$blockCode = $htmlCode;
				foreach($feedArray[$m] as $rkey => $rval)
				{
					$blockCode = str_replace("{".$rkey."}",$rval,$blockCode);
				}
				$returnCode .= $blockCode;
			}
			if($extrahtml != '')
				$returnCode .= $extrahtml;
		}

		return $returnCode;
	}
	
	function parseFeedBlock($feedBlock,$htmlCode,$feedType='',$feedKeyword='')
	{	
		$htmlCode = stripslashes($htmlCode);
		$blockCode = str_replace("{FEED_BLOCK}",$feedBlock,$htmlCode);
		
		if($feedType == 'tz')
			$blockCode = str_replace('sponsored links', 'Sponsored Results for '.$feedKeyword, $blockCode);
		return $blockCode;
	}
}


?>