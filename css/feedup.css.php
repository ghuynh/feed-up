<?php
header("Content-type: text/css"); 
include("../../../../wp-load.php");
$title_color = get_option('feed_title_color') != '' ? get_option('feed_title_color') : "";
$title_deco = get_option('feed_title_deco') != '' ? get_option('feed_title_deco') : "none";
$desc_color = get_option('feed_desc_color') != '' ? get_option('feed_desc_color') : "";
$desc_deco = get_option('feed_desc_deco') != '' ? get_option('feed_desc_deco') : "none";
$link_color = get_option('feed_link_color') != '' ? get_option('feed_link_color') : "";
$link_deco = get_option('feed_link_deco') != '' ? get_option('feed_link_deco') : "none";
?>
a.URLtop {
		color:<?php echo $title_color; ?> !important;
        text-decoration:<?php echo $title_deco; ?> !important;
		padding:1px 0 1px 0px !important;
}

a.URLbottom { 
        color:<?php echo $link_color; ?> !important;
        text-decoration:<?php echo $link_deco; ?> !important;
		padding:1px 0 1px 0px !important;
}

a.URLtext { 
        text-decoration:<?php echo $desc_deco; ?> !important;
        color:<?php echo $desc_color; ?> !important;
		padding:1px 0 1px 0px !important;
}

.sponsoredresults {
        padding:4px 0px 0px 0px !important;
        font-size:10px !important; 
        color:#999 !important; 
}

#results {
        margin:0 !important;
        padding-left:8px; !important;
}

#results p { 
        margin:5px 0px 10px 0px !important; 
        padding:0 !important;
		line-height:150% !important;
}

.URLtop {
        font-weight: normal !important;
        font-size: 12pt !important;
        text-decoration:<?php echo $title_deco; ?> !important;
}

.URLbottom { 
        color:#404040 !important; 
         text-decoration:<?php echo $link_deco; ?> !important;
}

.URLtext { 
        text-decoration:<?php echo $desc_deco; ?> !important;
        color:#5D646D !important;
}