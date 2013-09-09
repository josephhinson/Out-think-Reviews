<?php
/*
Plugin Name: Out:think Reviews
Plugin URI: http://outthinkgroup.com/
Description: This plugin is designed to give the user an interface to add reviews, then categorize them as "Sources" to be used for specific places in the site. Call with shortcode [reviews number='X or -1 for all' orderby='rand | menu_order | date' source='source-slug'].
Version: 1.0
Author: Joseph Hinson
Author URI: http://outthinkgroup.com

    Copyright 2011 - Out:think Group  (email : joseph@outthinkgroup.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// initializes the widget on WordPress Load
add_action('widgets_init', 'ot_reviews_init_widget');

// initializes the post type
add_action( 'init', 'register_review_init' );

//calling jQuery if it's not already enqueue
add_action('init', 'ot_reviews_enqueue');
function ot_reviews_enqueue() {
	$file_dir = get_bloginfo('template_directory');
	wp_enqueue_script( 'jquery-cycle', plugins_url( 'js/jquery.cycle.all.min.js', __FILE__ ), array('jquery') );
    wp_enqueue_script('jquery');
	wp_enqueue_style( 'ot_reviews_styles', plugins_url('/css/otr-styles.css', __FILE__));
}

add_action( 'init', 'ot_reviews_plugin_updater_init' );

/**
 * Load and Activate Plugin Updater Class.
 */
function ot_reviews_plugin_updater_init() {
	/* Load Plugin Updater */
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/plugin-updater.php' );
	$userinfo = get_option('ot-plugin-validation');
	
	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( __FILE__ ), //required
		'username'    => $userinfo['user'], // user login name in your site.
		'key' => $userinfo['email'],

		'repo_uri'  => 'http://outthinkgroup.com/',
		'repo_slug' => 'outthink-reviews',
	);

	/* Load Updater Class */
	new OTReviews_Plugin_Updater( $config );
}

// This is the shortcode for the reviews takes paramters 'source' and 'number' 
// [reviews source='higher-ed' number='10']
function ot_reviews_func($atts) {
        extract(shortcode_atts(array(
			"source" => "",
			"orderby" => "menu_order",
			"number" => "-1"
        ), $atts));
		$quotestxt = array(
			"numberposts" => $number,
			"orderby" => $orderby,
			"order" => "ASC",
			"post_type" => "reviews",
			"post_status" => "publish",
			"sources" => $source
		);
		$c=0;
		$quotes = get_posts($quotestxt);
		$return='<div id="reviews">';
		foreach($quotes as $quote) {
			$return.= '<p class="quote num-'.$c.'">';
			$return.= '<span class="otr_leftquo">&ldquo;</span>' . trim($quote->post_content). '<span class="otr_rightquo">&rdquo;</span> <span class="source">';
			if (has_post_thumbnail($quote->ID)) {
				$return .= get_the_post_thumbnail($quote->ID, 'review-thumb', array('class' => 'q-thumb'));
			}
			$return .= '&ndash;'.$quote->post_title.'</span>';
			$return .= '</p>';
			$c++;
		} // endfor
		$return .= '</div>';
		return $return;
}
add_shortcode("reviews", "ot_reviews_func");
// end shortcode

// fading reviews function
function ot_reviews($timeout = 6, $number = -1, $orderby = 'rand', $source = '', $limit = 0, $type = 'content') { ?>
	<?php if ($timeout > 0): ?>
		<script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function() {
				jQuery('.otreviews_widget #reviews').cycle({
					fx: 'fade', // choose your transition type, ex: fade, scrollUp, shuffle, etc...
					containerResize: 1,
					fit: 1,
					prev:   '#otr_prev a', 
				    next:   '#otr_next a',
					timeout: <?php echo ($timeout * 1000); ?>
				});
			});
		</script>
		
	<?php endif; ?>
	<style type="text/css" media="screen">
		.reviews span.source {
			display:block;
		}
	</style>
	<div class="otr_nav">
		<span id="otr_prev" class="btn btn-mini"><a href="javascript:void(null);"><i class="icon-chevron-left"></i></a></span>
		<span id="otr_next" class="btn btn-mini"><a href="javascript:void(null);"><i class="icon-chevron-right"></i></a></span>
	</div>
	<div id="reviews">
		<?php $quotestxt = array(
			"numberposts" => $number,
			"orderby" => $orderby,
			"order" => "ASC",
			"post_type" => "reviews",
			"post_status" => "publish",
			"sources" => $source
		);
		$quotes = get_posts($quotestxt); $c=0;
		$thiscontent;
		foreach($quotes as $quote) {
			// checking to see if type is excerpt, otherwise make it the content
			if ($type == 'excerpt') {
				if (strlen($quote->post_excerpt) > 0) {
					$thiscontent = $quote->post_excerpt;
				}
			} else {
				$thiscontent = $quote->post_content;
			}
			// if there is a limit, make sure that the string length of the "content" is less than the limit, if it is, allow the variable to continue.
			if ($limit > 0 && str_word_count($thiscontent) < $limit):
				$thiscontent = $thiscontent;
			elseif ($limit > 0 && str_word_count($thiscontent) > $limit) :
				$thiscontent = '';
			endif; ?>
			<?php if (strlen($thiscontent) > 0): ?>
				<p class="quote"<?php if ($c > 0): ?> style="display:none;"<?php endif; ?>>
					<span class="otr_leftquo">&ldquo;</span><?php echo trim($thiscontent); ?><span class="otr_rightquo">&rdquo;</span>
					<span class="source"><?php
						if (has_post_thumbnail($quote->ID)) {
							echo get_the_post_thumbnail($quote->ID, 'review-thumb', array('class' => 'q-thumb'));
						}
						?>&ndash;<?php echo $quote->post_title; ?></span>
				</p>
				
			<?php endif; ?>
			<?php $thiscontent = ''; ?>
			<?php $c++;
		} // endfor ?>
	</div><!--END reviews-->
<?php } // END reviews function

//custom post type for Review
function register_review_init() {
	register_post_type('reviews', 
	array(	
		'label' => 'Reviews',
		'description' => '',
		'public' => true,
		'exclude_from_search' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => false,
		'capability_type' => 'post',
		'hierarchical' => false,
		'rewrite' => array('slug' => ''),
		'query_var' => true,
		'supports' => array('title','editor','excerpt','custom-fields','revisions','thumbnail',),
		'taxonomies' => array('sources',),
		'labels' => array (
			'name' => 'Reviews',
			'singular_name' => 'Review',
			'menu_name' => 'Reviews',
			'add_new' => 'Add Review',
			'add_new_item' => 'Add New Review',
			'edit' => 'Edit',
			'edit_item' => 'Edit Review',
			'new_item' => 'New Review',
			'view' => 'View Review',
			'view_item' => 'View Review',
			'search_items' => 'Search Reviews',
			'not_found' => 'No Reviews Found',
			'not_found_in_trash' => 'No Reviews Found in Trash',
			'parent' => 'Parent Review',
		),
	));
	
	// Taxonomy for "Sources" -- works like tags
	register_taxonomy( 'sources', 'reviews', array( 'hierarchical' => true, 'label' => 'Sources', 'query_var' => true, 'rewrite' => true, 'singular-label' => 'Source', 'show_admin_column' => true) );

}

// Should be called above from "add_action" [line 28]
function ot_reviews_init_widget() {
	register_widget( 'OT_Reviews_Widget' );
} 

// Change the default "Title" to be "Enter Reviewer Name"
function ot_change_reviews_title( $title ){
     $screen = get_current_screen();
 
     if  ( 'reviews' == $screen->post_type ) {
          $title = 'Enter Reviewer Name';
     }
 
     return $title;
}
add_filter( 'enter_title_here', 'ot_change_reviews_title' );


// new class to extend WP_Widget function
class OT_Reviews_Widget extends WP_Widget {
	/** Widget setup.  */
	function OT_Reviews_Widget() {
		/* Widget settings. */
		$widget_ops = array(
			'classname' => 'otreviews_widget',
			'description' => __('Widget for Reviews', 'otreviews_widget') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'ot-reviews-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'ot-reviews-widget', __('Out:think Reviews Widget', 'Options'), $widget_ops, $control_ops );
	}
	
	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title'] );

		/* Our variables from the widget settings. */
		$timeout = $instance['reviews_timeout'];
		$number = $instance['reviews_num'];
		$orderby = $instance['reviews_orderby'];
		$source = $instance['reviews_source'];
		$strlen = $instance['reviews_strlen'];
		$rcontent = $instance['reviews_content'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display title from widget settings if one was input. */

		if ( $title )
			echo $before_title . $title . $after_title;
		
		// Settings from the widget

		ot_reviews($timeout, $number, $orderby, $source, $strlen, $rcontent );
		/* After widget (defined by themes). */
		echo $after_widget;
	}

  /**
    * Saves the widgets settings.
    *
    */
    function update($new_instance, $old_instance){
      $instance = $old_instance;
      $instance['title'] = strip_tags( $new_instance['title'] );
      $instance['reviews_timeout'] = strip_tags(stripslashes($new_instance['reviews_timeout']));
      $instance['reviews_num'] = strip_tags(stripslashes($new_instance['reviews_num']));
      $instance['reviews_source'] = strip_tags(stripslashes($new_instance['reviews_source']));
      $instance['reviews_orderby'] = $new_instance['reviews_orderby'];
      $instance['reviews_strlen'] = strip_tags(stripslashes($new_instance['reviews_strlen']));
      $instance['reviews_content'] = $new_instance['reviews_content'];
    return $instance;
  }

/**
 * Displays the widget settings controls on the widget panel.
 * Make use of the get_field_id() and get_field_name() function
 * when creating your form elements. This handles the confusing stuff.
*/
	function form( $instance ) {

		// Set up some default widget settings.		
		$defaults = array(
			'title' => __('', 'ot_reviews'),
			'reviews_timeout' => '12',
			'reviews_num' => '-1',
			'reviews_orderby' => 'rand',
			'reviews_source' => '',
			'reviews_strlen' => '',
			'reviews_content' => 'content'
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'ot_reviews'); ?></label><br>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>">
	</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'reviews_timeout' ); ?>">Seconds between reviews <small>0 for no transition</small></label><br><input type="text" name="<?php echo $this->get_field_name( 'reviews_timeout' ); ?>" value="<?php echo $instance['reviews_timeout']; ?>" id="<?php echo $this->get_field_id( 'reviews_timeout' ); ?>">				
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'reviews_num'); ?>">Number of reviews to display</label><br><input type="text" name="<?php echo $this->get_field_name( 'reviews_num'); ?>" value="<?php echo $instance['reviews_num']; ?>" id="<?php echo $this->get_field_id( 'reviews_num'); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'reviews_source'); ?>">Source of Review <small>(slug of source, ex: reader-review)</small></label><br><input type="text" name="<?php echo $this->get_field_name( 'reviews_source'); ?>" value="<?php echo $instance['reviews_source']; ?>" id="<?php echo $this->get_field_id( 'reviews_source'); ?>">
			
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'reviews_strlen'); ?>">Limit by String Length <small>(ex: 250 for short reviews)</small></label><br><input type="text" name="<?php echo $this->get_field_name( 'reviews_strlen'); ?>" value="<?php echo $instance['reviews_strlen']; ?>" id="<?php echo $this->get_field_id( 'reviews_strlen'); ?>">
		</p>
		<p><label for="<?php echo $this->get_field_id( 'reviews_content' ); ?>">What content area do you want to display in this widget?</label><br>
			<select name="<?php echo $this->get_field_name( 'reviews_content'); ?>" id="<?php echo $this->get_field_id( 'reviews_content'); ?>">
				<option <?php if ( 'content' == $instance['reviews_content'] ) echo 'selected="selected"'; ?> value="content">Content</option>
				<option <?php if ( 'excerpt' == $instance['reviews_content'] ) echo 'selected="selected"'; ?> value="excerpt">Excerpt</option>
			</select>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'reviews_orderby' ); ?>">In which order do you want the reviews to display?</label><br>
			<select name="<?php echo $this->get_field_name( 'reviews_orderby'); ?>" id="<?php echo $this->get_field_id( 'reviews_orderby'); ?>">
				<option <?php if ( 'menu_order' == $instance['reviews_orderby'] ) echo 'selected="selected"'; ?> value="menu_order">Menu Order</option>
				<option <?php if ( 'rand' == $instance['reviews_orderby'] ) echo 'selected="selected"'; ?> value="rand">Random</option>
				<option <?php if ( 'modified' == $instance['reviews_orderby'] ) echo 'selected="selected"'; ?> value="modified">Last Modified</option>				
			</select>
		</p>
		<small>This widget was created by Joseph Hinson of <a href="http://outthinkgroup.com" target="_blank" title="Out:think Group - Book and Author Marketing">Out:think Group</a>. If you have problems with it. Report them at <a href="http://support.outthinkgroup.com" target="_blank">Out:think Support</a></small>
	<?php
	}
}

require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/options.php' );
include	'tinymce/ot-reviews-tinymce.php';
include 'includes/ot-nlsignup.php';