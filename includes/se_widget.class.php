<?php 

class rfse_popular_posts_widget extends WP_Widget {

	// constructor
	function rfse_popular_posts_widget() {
		        parent::WP_Widget(false, $name = __('Social Engage Popular Posts', 'rfse_popular_posts_widget') );
	}

	// widget form creation
	function form($instance) {	
	   // Check values
		if( $instance) {
		     $title = esc_attr($instance['title']);
		     $select = esc_attr($instance['select']); // Added
		} else {
		     $title = '';
		     $select = ''; // Added
		}
		?>

		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id('select'); ?>"><?php _e('Ordered by', 'wp_widget_plugin'); ?></label>
		<select name="<?php echo $this->get_field_name('select'); ?>" id="<?php echo $this->get_field_id('select'); ?>" class="widefat">
		<?php
		$options = array('rf_facebook', 'rf_twitter', 'rf_pinterest', 'rf_linkdin');
		foreach ($options as $option) {
			echo '<option value="' . $option . '" id="' . $option . '"', $select == $option ? ' selected="selected"' : '', '>', $option, '</option>';
		}
		?>
		</select>
		</p>

		<?php
}

	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
	      // Fields
	      $instance['title'] = strip_tags($new_instance['title']);
	      $instance['select'] = strip_tags($new_instance['select']);
	     return $instance;
	}
	

	// widget display
	function widget($args, $instance) {
		extract( $args );
	   // these are the widget options
	   $title = apply_filters('widget_title', $instance['title']);
	   $select = $instance['select'];

	   $postargs = array( 'post_status' => array('publish'), 'post_type'=> 'post','numberposts' => 5, 'meta_key' => $select, 'orderby' => 'meta_value', 'order'  => 'DESC');
       $myposts = get_posts( $postargs );

	   echo $before_widget;
	   // Display the widget
	   echo '<div class="widget-text wp_widget_plugin_box">';

	   // Check if title is set
	   if ( $title ) {
	      echo $before_title . $title . $after_title;
	   }
	   echo '<ul>';
		$i = 1; 
		foreach ( $myposts as $post ) : setup_postdata( $post ); 
				$post_meta_details = get_post_meta($post->ID);
				$total_share_count = (int) @$post_meta_details['rf_facebook'][0] + (int) @$post_meta_details['rf_linkdin'][0] + (int) @$post_meta_details['rf_twitter'][0] + (int) @$post_meta_details['rf_pinterest'][0];

			    echo "<li> <a href=".get_permalink( $post->ID )." >".$post->post_title."</a> - <span style='display: inline-block;padding: 0 15px;height: 25px;font-size: 14px;line-height: 23px;border-radius: 25px;background-color: #f1f1f1;'>".$total_share_count ." Shares </span></li>";
                $i++; 
         endforeach;
	   echo '</ul>';
	   echo '</div>';
	   echo $after_widget;
	}
}
?>