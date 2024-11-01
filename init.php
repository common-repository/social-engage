<?php
/*
Plugin Name: Social Engage
Version: 1.1.1
Author: ravinder855
Description: Get your social network shares, likes, tweets, and view counts of posts from different social networks including facebook, twitter, pinterest and linkedin. Track the performance of your pages and know your best performing posts.
Author URI: http://robofollow.com
License: GPLv2+
*/

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */

add_action('admin_menu','rfse_socialengage');

function rfse_socialengage() {
	
	//this is the main item for the menu
	add_menu_page('SocialEngage', //page title
	'SocialEngage', //menu title
	'manage_options', //capabilities
	'rfse_dashboard', //menu slug
	'rfse_dashboard', //function
	'dashicons-networking'
	);
	

}

/* Cron active hook */
register_activation_hook(__FILE__, 'rfse_my_activation');

function rfse_my_activation() {
    if (! wp_next_scheduled ( 'rfse_my_hourly_event' )) {
	wp_schedule_event(time(), 'hourly', 'rfse_my_hourly_event');
    }
}

add_action('rfse_my_hourly_event', 'rfse_do_this_hourly');

function rfse_do_this_hourly() 
{	
	global $post;
	$current_datetime = date('Y-m-d H:i:s');
	ini_set('max_execution_time', 600); // 10 minutes

	/* Get recent 10 posts data */
	$query = array(
	    'post_status' => array('publish'),
	    'post_type'   => 'post',
	    'numberposts' => 10,
	    'offset' => 0,
	    'orderby' => 'post_date',
	    'order' => 'DESC'  
	);
	$loop = get_posts($query);
	$update_post_ids = array();
	if(!empty($loop))
	{
		foreach ($loop as $key => $post) 
		{
			$post_id  = $post->ID;

			/* Get post meta details */
			$meta_details = get_post_meta($post_id);
			if(empty($meta_details)){
				$update_post_ids[] = $post_id;
			}else{
				$last_post_modified = (!empty($meta_details['rf_social_last_modified'][0])) ? $meta_details['rf_social_last_modified'][0] : '';
				$hourdiff = (int) round((strtotime($current_datetime) - strtotime($last_post_modified))/3600, 1);
				if($hourdiff >= 2)
				{
					$update_post_ids[] = $post_id;
				}
			}
		}
	}
	if(!empty($update_post_ids))
	{
		rfse_update_social_counts($update_post_ids);exit;
	}

	/* Get next 10-20 posts data */
	$query = array(
	    'post_status' => array('publish'),
	    'post_type'   => 'post',
	    'numberposts' => 10,
	    'offset' => 10,
	    'orderby' => 'post_date',
	    'order' => 'DESC'  
	);
	$loop = get_posts($query);
	$update_post_ids = array();
	if(!empty($loop))
	{
		foreach ($loop as $key => $post) 
		{
			$post_id  = $post->ID;

			/* Get post meta details */
			$meta_details = get_post_meta($post_id);
			if(empty($meta_details)){
				$update_post_ids[] = $post_id;
			}else{
				$last_post_modified = (!empty($meta_details['rf_social_last_modified'][0])) ? $meta_details['rf_social_last_modified'][0] : '';
				$daysdiff = (int) round((strtotime($current_datetime) - strtotime($last_post_modified))/86400, 1);
				if($daysdiff >= 3)
				{
					$update_post_ids[] = $post_id;
				}
			}
		}
	}
	if(!empty($update_post_ids))
	{
		rfse_update_social_counts($update_post_ids);exit;
	}

	/* Get next 20-100 posts data */
	for($page = 20; $page < 60; $page+=10) 
	{ 
		$query = array(
		    'post_status' => array('publish'),
		    'post_type'   => 'post',
		    'numberposts' => 10,
		    'offset' => (int) $page,
		    'orderby' => 'post_date',
		    'order' => 'DESC'  
		);
		$loop = get_posts($query);
		$update_post_ids = array();
		if(!empty($loop))
		{
			foreach ($loop as $key => $post) 
			{
				$post_id  = $post->ID;

				/* Get post meta details */
				$meta_details = get_post_meta($post_id);
				if(empty($meta_details)){
					$update_post_ids[] = $post_id;
				}else{
					$last_post_modified = (!empty($meta_details['rf_social_last_modified'][0])) ? $meta_details['rf_social_last_modified'][0] : '';
					$daysdiff = (int) round((strtotime($current_datetime) - strtotime($last_post_modified))/86400, 1);
					if($daysdiff >= 7)
					{
						$update_post_ids[] = $post_id;
					}
				}
			}
		}
		if(!empty($update_post_ids))
		{
			rfse_update_social_counts($update_post_ids);exit;
		} 
	}
}

function rfse_update_social_counts($post_ids)
{
	foreach ($post_ids as $key => $post_id) 
	{
		$post_url = get_permalink($post_id);
		if($post_url)
		{
			$rest_api_url = RFSE_REST_API_URL . $post_url;
		
			/* To check post url exist ip address or not */
			if (!preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $rest_api_url, $match)) {
				try {
					/* Get social results */
					$social_resp = @json_decode(file_get_contents($rest_api_url));
					if(isset($social_resp) && !empty($social_resp))
					{
						$twitter_count   = (!empty($social_resp->tweetscount)) ? $social_resp->tweetscount : 0;
						$facebook_count  = (!empty($social_resp->fbsharescount)) ? $social_resp->fbsharescount : 0;
						$pinterest_count = (!empty($social_resp->pinscount)) ? $social_resp->pinscount : 0;
						$linkdin_count   = (!empty($social_resp->linkedincount)) ? $social_resp->linkedincount : 0;

						/* Update post meta data */
						$fb_status = update_post_meta($post_id,'rf_facebook',$facebook_count);
						$tw_status = update_post_meta($post_id,'rf_twitter',$twitter_count);
						$pi_status = update_post_meta($post_id,'rf_pinterest',$pinterest_count);
						$ld_status = update_post_meta($post_id,'rf_linkdin',$linkdin_count);
						/* Update post meta modified date */
						update_post_meta($post_id,'rf_social_last_modified',date('Y-m-d H:i:s'));
						if($fb_status || $tw_status || $pi_status || $ld_status){
							echo "Updated";
						}else{
							echo "Failed To Update OR did not find any changes";
						}
					}
				}
				catch (Exception $e) {
		            echo $e->getMessage();
		        }
			}else{
				echo "IP address url not allowed";
			}
		}
	}
}

/* Cron deactive hook */
register_deactivation_hook(__FILE__, 'rfse_my_deactivation');

function rfse_my_deactivation() {
	wp_clear_scheduled_hook('rfse_my_hourly_event');
}

define('RFSE_ROOTDIR', plugin_dir_path(__FILE__));
define('RFSE_REST_API_URL', 'http://robofollow.com/rest/social/stats?url=');
require_once(RFSE_ROOTDIR . 'dashboard.php');
require_once(RFSE_ROOTDIR . 'includes/se_widget.class.php');
// register widget
add_action('widgets_init', create_function('', 'return register_widget("rfse_popular_posts_widget");'));

/**
* Get the current time and set it as an option when the plugin is activated.
*
* @return null
*/
function rfse_set_activation_date() {

    $now = strtotime( "now" );
    add_option( 'rfse_activation_date', $now );

}
register_activation_hook( __FILE__, 'rfse_set_activation_date' );

/**
* Check date on admin initiation and add to admin notice if it was over 10 days ago.
*
* @return null
*/
function rfse_check_installation_date() {
 
     // Added Lines Start
    $nobug = "";
    $nobug = get_option('rfse_no_bug');
 
    if (!$nobug) {
    // Added Lines End
 
        $install_date = get_option( 'rfse_activation_date' );
        $past_date = strtotime( '-5 days' );
 
        if ( $past_date >= $install_date ) {
 
            add_action( 'admin_notices', 'rfse_display_admin_notice' );
 
        }
 
    // Added Lines Start
    }
    // Added Lines End
 
}
add_action( 'admin_init', 'rfse_check_installation_date' );

/**
* Display Admin Notice, asking for a review
*
* @return null
*/
function rfse_display_admin_notice() {
 
    // Review URL - Change to the URL of your plugin on WordPress.org
    $reviewurl = 'https://wordpress.org/support/plugin/social-engage/reviews/?rate=5#new-post';
 
    $nobugurl = get_admin_url() . '?rfsenobug=1';
 
    echo '<div class="updated">'; 
 
    printf( __( "You have been using Social Engage for a week now, do you like it? If so, please leave us a review with your feedback! <a href='%s' target='_blank'>Leave A Review</a> <a href='%s'>Leave Me Alone</a>" ), $reviewurl, $nobugurl ); 
 
    echo "</div>";
}

/**
* Set the plugin to no longer bug users if user asks not to be.
*
* @return null
*/
function rfse_set_no_bug() {

    $nobug = "";

    if ( isset( $_GET['rfsenobug'] ) ) {
        $nobug = esc_attr( $_GET['rfsenobug'] );
    }

    if ( 1 == $nobug ) {

        add_option( 'rfse_no_bug', TRUE );

    }

} add_action( 'admin_init', 'rfse_set_no_bug', 5 );
