<?php
/*
Plugin Name: Feed Up
Plugin URI: http://twitter.com/gwhuynh
Description: Automatically adds various feeds into the content of posts and pages.
Version: 1.0
Author: George Huynh
Author URI: http://twitter.com/gwhuynh
*/

function feedup_options_page() { ?>
	<div class="wrap">
	<h2>Feed Up</h2>
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>

	<p><label>Feed Status
    <select name="feed_status">
    <option value="1"<?php if('1' == get_option('feed_status')) echo ' selected'; ?>>On</option>
    <option value="0"<?php if('1' != get_option('feed_status')) echo ' selected'; ?>>Off</option>
    </select>
	</label>
	</p>
	<p><label>Feed Redirection
    <select name="feed_redirect">
    <option value="yes"<?php if('yes' == get_option('feed_redirect')) echo ' selected'; ?>>Yes</option>
    <option value="no"<?php if('yes' != get_option('feed_redirect')) echo ' selected'; ?>>No</option>
    </select>
	</label>
	</p>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="feed_status" />
	<input type="hidden" name="page_options" value="feed_redirect" />
	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	
	</form>
	</div><?php
}

add_action('init', 'feed_set_session');
add_action('wp_head', 'feed_styles');
add_action('template_redirect','feed_mapping_redir');
add_action('shutdown', 'feed_unset_session');

function feed_mapping_redir()
{
	if(is_search())
	{
		get_search_feed();
	}
	else
	{
		add_action('loop_start', 'get_post_fields');
	}

	$s_keyword = isset($_REQUEST['k']) ? $_REQUEST['k'] : '';
	$s_mapping = isset($_REQUEST['m']) ? $_REQUEST['m'] : '';
	if($s_mapping != '')
	{
		$feed_mapping_list = get_option('feed_mapping');
		if($feed_mapping_list != '')
		{
			$feed_mappings = explode('||', $feed_mapping_list);
			foreach($feed_mappings as $feed_mapping)
			{
				$mapping = explode(',', $feed_mapping);
				if(strtolower($s_mapping) == strtolower($mapping[0]))
					$s_mapping = $mapping[1];
			}
		}
		
		$_SESSION['countnum'] = 1;
		$_SESSION['k'] = $s_keyword;
		$_SESSION['m'] = $s_mapping;
		header("Location:".$_SERVER['REDIRECT_URL']);
	}
}

function feed_unset_session()
{
	if($_SESSION['countnum']>2)
	{
		unset($_SESSION['countnum']);
		unset($_SESSION['k']);
		unset($_SESSION['m']);
	}
}

function feed_set_session()
{
	if (!session_id())
		session_start();
}

function feed_styles()
{
	 $feedup_path =  get_bloginfo('wpurl')."/wp-content/plugins/feed-up/";
	$feedupscript = "
	<!-- begin feedup scripts -->
	<link rel=\"stylesheet\" href=\"".$feedup_path."css/feedup.css.php\" type=\"text/css\" media=\"screen\" charset=\"utf-8\"/>
	<!-- end feedup scripts -->\n";
	echo($feedupscript);
}

function get_search_feed()  {
	if ('1' == get_option('feed_status'))
	{
		get_header();
		global $keyword;
		global $mapping;
		$sponsorkeyword = '';
		
		$keyword = isset($_REQUEST['w']) ? $_REQUEST['w'] : $_REQUEST['s'];
		$keyword = str_replace("+", " ", $keyword);
		$keyword = ucwords($keyword);
		
		$mapping = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';
		$mapping = str_replace("+", " ", $mapping);
		$mapping = ucwords($mapping);
		
		echo '<div id="content">';
		echo '<h1>'.$keyword.'</h1>';

		$default_structure = '<P><a class="URLtop" href="{CLICK_LINK}" target="_blank">{TITLE}</a><br>
		<a class="URLtext" href="{CLICK_LINK}" target="_blank">{DESCRIPTION}</a><br>
		<a class="URLbottom" href="{CLICK_LINK}" target="_blank">{SITEHOST}</a>
		</P>';
		$default_block = '<div style="font-size:small;color:#E2E2E2;text-align:right;">sponsored links</div>
		<div id="results">{FEED_BLOCK}</div>';
		$feed_type = get_option('feed_type') != '' ? get_option('feed_type') : 'ob';
		$feed_structure = get_option('feed_structure') != '' ?  get_option('feed_structure') : $default_structure;
		$feed_block = $default_block;
		
		require_once('feed/'.$feed_type.'.php');

		$feed = new $feed_type();
		$insert = $feed->get_feed_code(0, $mapping);
		
		require_once('htmlClass.php');
		$Html = new Html();
		if(is_array($insert))
		{
			$feed_number = count($insert);
			$insert = $Html->parseSponsored($feed_structure,$insert,$feed_number);

		}
		$insert = $Html->parseFeedBlock($insert,$feed_block,$feed_type,$sponsorkeyword);

		echo $insert;
		echo '</div>';
		
		get_sidebar(); 
		get_footer();
		exit;
	}
	
}

function get_post_fields($post)  {
	if ('1' == get_option('feed_status'))
	{
		global $fix_home;
		global $keyword;
		global $mapping;
		global $sponsorkeyword;
		
		if(!isset($fix_home))
			$fix_home = false;
		$keywords = get_post_custom_values('adgroup', $post->post->ID);
		if(!empty($keywords))
			$keyword = $keywords[0];
		else
			$keyword = '';
		
		if(isset($_SESSION['countnum']))
			$_SESSION['countnum']++;
		$keyword = isset($_SESSION['k']) ? $_SESSION['k'] : $keyword;
		$mapping = isset($_SESSION['m']) ? $_SESSION['m'] : '';
		
		$keyword = isset($_REQUEST['k']) ? $_REQUEST['k'] : $keyword;	
		$keyword = isset($_REQUEST['s']) ? $_REQUEST['s'] : $keyword;
		
		$keyword = str_replace("+", " ", $keyword);
		$keyword = ucwords($keyword);
		
		$sponsorkeyword = $mapping != '' ? ucwords(str_replace("+", " ", $mapping)) : $keyword;

		if ('yes' == get_option('feed_session'))
		{
			if(isset($_SESSION['pt']))
				$switch = TRUE;
			else if(isset($_REQUEST['k'])||isset($_SESSION['k']))
			{
				$_SESSION['pt'] = '1';
				$switch = TRUE;
			}
			else
				$switch = FALSE;
		}
		else
			$switch = TRUE;

		if($switch)
		{
			echo '<h1>'.$keyword.'</h1>';
			global $feed_post_id;
			$feed_post_id = $post->post->ID;
			if(is_single()):
				add_filter('the_content', 'feedup_insert');
			elseif(!is_home() && !is_page() && !$fix_home):
				$default_structure = '<P><a class="URLtop" href="{CLICK_LINK}" target="_blank">{TITLE}</a><br>
				<a class="URLtext" href="{CLICK_LINK}" target="_blank">{DESCRIPTION}</a><br>
				<a class="URLbottom" href="{CLICK_LINK}" target="_blank">{SITEHOST}</a>
				</P>';
				$default_block = '<div style="font-size:small;color:#E2E2E2;text-align:right;">sponsored links</div>
				<div id="results">{FEED_BLOCK}</div>';
				$feed_type = get_option('feed_type') != '' ? get_option('feed_type') : 'ob';
				$feed_structure = get_option('feed_structure') != '' ?  get_option('feed_structure') : $default_structure;
				$feed_block = $default_block;
				
				require_once('feed/'.$feed_type.'.php');
	
				$feed = new $feed_type();
				if(is_search())
					$insert = $feed->get_feed_code(0, $_REQUEST['s']);
				else
					$insert = $feed->get_feed_code($feed_post_id, $mapping);
				
				require_once('htmlClass.php');
				$Html = new Html();
				if(is_array($insert))
				{
					$feed_number = count($insert);
					$insert = $Html->parseSponsored($feed_structure,$insert,$feed_number);
	
				}
				$insert = $Html->parseFeedBlock($insert,$feed_block,$feed_type,$sponsorkeyword);
	
				echo $insert;
			endif;
		}
	}
}

function feedup_insert($ret) {
	global $feed_post_id;
	global $keyword;
	global $mapping;
	global $sponsorkeyword;
	
	$default_structure = '<P><a class="URLtop" href="{CLICK_LINK}" target="_blank">{TITLE}</a><br>
	<a class="URLtext" href="{CLICK_LINK}" target="_blank">{DESCRIPTION}</a><br>
	<a class="URLbottom" href="{CLICK_LINK}" target="_blank">{SITEHOST}</a>
	</P>';
	$default_block = '<div style="font-size:small;color:#E2E2E2;text-align:right;">sponsored links</div>
	<div id="results">{FEED_BLOCK}</div>';
	$substr = get_option('feed_insert') != '' ? get_option('feed_insert') : 400;

	$feed_type = get_option('feed_type') != '' ? get_option('feed_type') : 'ob';
	$feed_structure = get_option('feed_structure') != '' ?  get_option('feed_structure') : $default_structure;
	$feed_block = get_option('feed_block') != '' ?  get_option('feed_block') : $default_block;
	require_once('feed/'.$feed_type.'.php');

	$feed = new $feed_type();
	$insert = $feed->get_feed_code($feed_post_id, $mapping);

	if ($insert=='' || empty($insert))
		return $ret;
		
	require_once('htmlClass.php');
	$Html = new Html();
	if(is_array($insert))
	{
		$feed_number = count($insert);
		$insert = $Html->parseSponsored($feed_structure,$insert,$feed_number);
	}
	$insert = $Html->parseFeedBlock($insert,$feed_block,$feed_type,$sponsorkeyword);
	
	if ($insert=='')
		return $ret;
	
	if ($substr >= 999)
	{
		$ret = $ret.$insert;
	}
	else if ($substr>0 && $substr<999) {
		if (strlen($ret) <= $substr)
			return $insert.$ret;
		$testchar = substr($ret, $substr, 1);

		while ($testchar!= " ") {

		$substr = $substr - 1;

		$testchar = substr($ret, $substr, 1);

		}

		$latestupdate1 = substr($ret, 0, $substr);
		$latestupdate2 = substr($ret, $substr); 

		$ret = $latestupdate1."</p>".$insert."<p>".$latestupdate2;
	}
	else
	{
		$ret = $insert.$ret;
	}

	return $ret;
}

if(get_option('feed_redirect') == 'yes')
{
	add_action('template_redirect','feed_template');
	function feed_template($arg){
		global $wp_query;
		global $wpdb;
		
		if( !isset($wp_query->query_vars['redirurl']))
			return $arg;
		else
			header("Location:".base64_decode($wp_query->query_vars['redirurl']));
	}
	
	add_filter('query_vars', 'feed_queryvars' );
	function feed_queryvars( $qvars ){
		$qvars[] = 'redirurl';
		return $qvars;
	}
}

define('FEED_FILE', trailingslashit(ABSPATH).'wp-content/plugins/feed-up/feed-up.php');

function feed_install() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}
register_activation_hook(FEED_FILE, 'feed_install');
add_action('generate_rewrite_rules', 'feed_add_rewrite_rules');
	
function feed_add_rewrite_rules( $wp_rewrite ) {	
	$new_rules = array( 
						 "result.php" => 'index.php'
							);

	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

if(get_option('feed_park_structure') == 'yes')
{
	add_action('template_redirect','feed_keywords');
	function feed_keywords($arg){
		global $wp_query;
		global $wpdb;
		
		if( !isset($wp_query->query_vars['Keywords']))
			return $arg;
		else
		{
			$category_name = strtolower(str_replace(' ', '-', str_replace('+', '-', $wp_query->query_vars['Keywords'])));
			$myposts = get_posts('numberposts=1&category_name='.$category_name);
			foreach($myposts as $post){
				$redir_post = $post->guid;
			}
			header("Location:".$redir_post);
		}
	}
	
	add_filter('query_vars', 'feed_keyqueryvars' );
	function feed_keyqueryvars( $qvars ){
		$qvars[] = 'Keywords';
		return $qvars;
	}
}
?>