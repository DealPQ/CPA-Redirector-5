<?php
/*
Plugin Name: DealPQ
Plugin URI: https://www.dealpq.com
Description: DealPQ
Author: DealPQ
Version: 5
Author URI: http://www.dealpq.com
*/ 

function cpar_install(){
	update_option('wpbh_magicnumber', 111);
	
}


function curPageURL() {
	$pageURL = 'http';

	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}

	$pageURL .= "://";

	if ($_SERVER["SERVER_PORT"] != "80") {

		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

	 } else {

		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 	}

 return preg_replace('/\?.*/', '', $pageURL);
}


function wpbh_get_url(){

	global $post;

	if(is_home()) $urls =  get_option('wpbh_hpaffiliateurl');

	else if(is_category()) {
		$cat = get_query_var('cat');	
		if (function_exists('get_terms_meta'))
			    $urls = get_terms_meta($cat, "cats", true);
	}

	else if(is_archive()) $urls =  get_option('wpbh_archiveaffiliateurl');

	else if(is_tag())  $urls = get_option('wpbh_tagaffiliateurl');
	

	else $urls = get_post_meta($post->ID, "theurl", true);

	$url_arr = explode("\n", $urls);

	$url = trim($url_arr[array_rand($url_arr)]);
	
	if($url=="") {echo "Error: No URL"; exit();}

	return $url;
	
}


function wpbh_cpa_red() {   	

    //if ( is_home() || is_category() || is_archive()) return false;

	//if (strlen(get_post_meta($post->ID, "theurl", true)) < 5) return false;

	if ($_GET['mn']==get_option('wpbh_magicnumber')){		

		echo '<html><head><META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW"></head><body><form action="' . curPageURL() . '" method="post" id="form1">

<input type="hidden"  name="mn" value="' . get_option('wpbh_magicnumber') . '" /></form>

<script language="JavaScript"> 
	document.getElementById(\'form1\').submit();</script></body></html>';
		return true; 
		exit();
}

    if ($_POST['mn']==get_option('wpbh_magicnumber')){

			echo '<html><head><META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
</head><body><form action="'. curPageURL() . '" method="post" id="form1">

<input type="hidden"  name="mn" value="' . get_option('wpbh_magicnumber') . get_option('wpbh_magicnumber') . '" /></form>

<script language="JavaScript"> 
	document.getElementById(\'form1\').submit();</script></body></html>';
		return true; 
		exit();		
}
	$dom = preg_replace( "/^www\./", "", $_SERVER[ 'HTTP_HOST' ] ) ;
	$ref= $_SERVER['HTTP_REFERER'];

	if (((strpos($ref, $dom)!=FALSE) || (trim($ref)=="" ) ) && ($_POST['mn']==(get_option('wpbh_magicnumber').get_option('wpbh_magicnumber')))){

		$h_url = 'Location: ' . wpbh_get_url();
		header($h_url);
		exit();
	}

	return false;
} 



function cpa_red_editor() { 

	if (isset ($_POST['update'])) {		


		update_option('wpbh_magicnumber', $_POST['magicnumber']);	

		update_option('wpbh_hpaffiliateurl', trim($_POST['hpaffiliateurl']));
		update_option('wpbh_archiveaffiliateurl', trim($_POST['archiveaffiliateurl']));
		update_option('wpbh_tagaffiliateurl', trim($_POST['tagaffiliateurl']));
		
		for($i = 1; $i <= $_POST['post_count']; $i++) {
			$url = 'url_' . $i;
			$post_id = "id_" . $i;
		
			delete_post_meta($_POST[$post_id], 'theurl');
			add_post_meta($_POST[$post_id], 'theurl', trim($_POST[$url]));
			
		}

		$n_cats=  get_categories();
		foreach($n_cats as $n_cat){
			$url = 'url_' . $n_cat->term_id;
			if (function_exists('get_terms_meta')){
				delete_terms_meta($n_cat->term_id, 'cats');			
				add_terms_meta($n_cat->term_id, 'cats', trim($_POST[$url]));
			}
		}

	}
	
	global $wpdb; 
	$links='';
	$posts_columns = array(
		//'ID'      => __('ID'),
		'title'      => __('Post Title'),
		'post URL'      => __('Post URL'),
		'URL'     => __('Affiliate URLs')
	);
	$posts_columns = apply_filters('manage_posts_columns', $posts_columns);


	
	$cat_columns = array(
		'cattitle'      => __('Cat Title'),
		'cat URL'      => __('Cat URL'),
		'URL'     => __('Affiliate URLs')
	);


	echo '<div class="wrap">';
		echo '<center><h2>DealPQ CPA Redirector 5</h2></center>';
	echo '<style type="text/css">';
	echo '#thecenter { text-align:center; }';
	echo '</style>';
	echo '<table class="widefat">';

	echo '<form action="options-general.php?page=' . $_GET['page'] . '" method="post">';
	echo '<input type="hidden" name="update" value="yes" />';

	if (!function_exists('get_terms_meta')) echo "<h1>Please install the WP Category Meta Plugin here " . '<a href="http://wordpress.org/extend/plugins/wp-category-meta/">http://wordpress.org/extend/plugins/wp-category-meta/</a></h1><br><br>';

	

	echo '<label for="magicnumber">Magic Number: <//label>';

	echo '<input name="magicnumber" type="text"  value="' .  get_option('wpbh_magicnumber')  . '"  size="20" /><br><br>';

	echo '<label for="hp">Home Page Affiliate URLs: <//label>';

	echo '<textarea name="hpaffiliateurl" rows="2" cols="60" >' . get_option('wpbh_hpaffiliateurl') . '</textarea><br><br>';

	echo '<label for="hp">Archive Pages Affiliate URLs: <//label>';

	echo '<textarea name="archiveaffiliateurl" rows="2" cols="60" >' . get_option('wpbh_archiveaffiliateurl') . '</textarea><br><br>';

	echo '<label for="hp">Tag Pages Affiliate URLs: <//label>';

	echo '<textarea name="tagaffiliateurl" rows="2" cols="60" >' .  get_option('wpbh_tagaffiliateurl') . '</textarea><br><br>';


	echo '<thead><tr>';
	foreach($cat_columns as $catcolumn_display_name) {
		echo '<th scope="col">';
		echo $catcolumn_display_name . '</th>';		
	}

		
	echo '</tr> </thead> <tbody id="the-list">';
	
	$n_cats=  get_categories();
	foreach($n_cats as $n_cat){
			//echo '<input type="hidden" name="idc_' . $i .'" value="' . $n_cat->term_id . '"/>';
			echo '<tr class="' . $class  . '"><th scope="row" style="text-align: left">' . $n_cat->name . '</th>';
			$category_link = get_category_link($n_cat->term_id );
			
			echo '<th scope="row" style="text-align: left"><input name="perma" type="text" id="perma" value="' . 	$category_link . '"  size="70" />';							if (function_exists('get_terms_meta'))
			    $cat_meta = get_terms_meta($n_cat->term_id, "cats", true);

			echo '<th scope="row" style="text-align: left"><textarea name="url_' . $n_cat->term_id . '" rows="2" cols="60" >' . $cat_meta  . '</textarea></th>';

	}
	echo '</tbody></table><br><br>';
	
	
	


    echo '<select name="store">';

       $tax_terms = get_terms('dealstore', array('hide_empty' => '0'));      
       foreach ( $tax_terms as $tax_term ):  
          echo '<option value="'.$tax_term->slug.'">'.$tax_term->slug.'</option>';   
       endforeach;

    echo  '</select> ';
    echo '<input type="submit" name="submit" vlaue="Choose options">';
if(isset($_POST['submit'])){
   if(!empty($_POST['store'])) {
        $storename = $_POST['store'];
         echo '<table class="widefat">';
	echo '<thead><tr>';
	foreach($posts_columns as $column_display_name) {
		echo '<th scope="col">';
		echo $column_display_name . '</th>';		
	}
	echo '</tr> </thead> <tbody id="the-list">';
	
	
        $time_difference = get_settings('gmt_offset'); 
        $now = gmdate("Y-m-d H:i:s",time()); 
    $request = "SELECT ID,post_title,post_excerpt, wp_terms.term_id, wp_terms.slug FROM wp_posts LEFT JOIN wp_term_relationships ON (ID = wp_term_relationships.object_id) LEFT JOIN wp_term_taxonomy ON (wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id) LEFT JOIN wp_terms ON (wp_term_taxonomy.term_taxonomy_id = wp_terms.term_id) WHERE wp_terms.slug = '$storename' "; 
        if($hide_pass_post) $request .= "AND post_password ='' "; 
        $request .= "AND post_date_gmt < '$now' ORDER BY post_date"; 
    $posts = $wpdb->get_results($request); 
    $cc = count($posts);    
    echo $cc;
		$i = 0;
	$bgcolor = '';

    if($posts) { 
         foreach ($posts as $post) { 
                $post_title = stripslashes($post->post_title); 
                $permalink = get_permalink($post->ID); 
                $links[$i] = '<a href="' . $permalink . '" rel="bookmark" title="Permanent Link: ' . htmlspecialchars($post_title, ENT_COMPAT) . '">' . strtolower(htmlspecialchars($post_title)) . '</a>'; 
				$i++;                        
				$class = ('alternate' == $class) ? '' : 'alternate';
				echo '<input type="hidden" name="id_' . $i .'" value="' . $post->ID . '"/>';
				echo '<tr class="' . $class  . '"><th scope="row" style="text-align: left">' . $post_title . '</th>';
				echo '<input type="hidden" name="id_' . $i .'" value="' . $post->ID . '"/>';
				//echo '<th scope="row" style="text-align: left">' . $permalink . '</th>';
				echo '<th scope="row" style="text-align: left"><input name="perma" type="text" id="perma" value="' . $permalink . '"  size="70" />';		
				
			//	echo '<th scope="row" style="text-align: left"><input name="url_' . $i .  '" value="' . get_post_meta($post->ID, "theurl", true) . '" size="40" /></th>';
						
		
	echo '<th scope="row" style="text-align: left"><textarea name="url_' . $i . '" rows="2" cols="60" >' . get_post_meta($post->ID, "theurl", true)  . '</textarea></th>';

		
				echo '</tr>';
				
         } 
	
		echo '</table>';

   }
   else {
        echo 'Please select the value.';
    }
}

	
		echo '<div style="clear:both;"></div>';
		echo '<input type="hidden" name="post_count" value="' . $i . '"/>';
		echo '<br>';
		echo '<div id="thecenter">';
		echo '<input type="submit" value="Save Settings" />';
		echo '</form>';
		echo '</div></div>';
     }
	
} 


 

function prc_add_options_to_admin() { 
   add_options_page('CPA Redirector 5', 'CPA Redirector 5', 8, __FILE__, 'cpa_red_editor'); 
} 

if (function_exists('add_action')) { 
  	add_action('admin_menu', 'prc_add_options_to_admin'); 
	add_action('get_header','wp_cpa_red_head');

} 

function wp_cpa_red_head(){
	
	if (wpbh_cpa_red()) exit();

} 

register_activation_hook(__FILE__,'cpar_install');

?>