<?php


/**
 * Register and enqueue a custom stylesheet in the WordPress admin.
 */
function rfse_enqueue_admin_style() {
    wp_enqueue_style( 'rfse_bootstrap', plugins_url( 'includes/css/bootstrap.min.css', __FILE__ ) );
    wp_enqueue_style( 'rfse_jquery_datatables_css', plugins_url( 'includes/css/jquery.dataTables.min.css', __FILE__ ) );
    wp_enqueue_style( 'rfse_custom_css', plugins_url( 'includes/css/rfse.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'rfse_enqueue_admin_style' );

/**
 * Enqueue a script in the WordPress admin, excluding edit.php.
 *
 */
function rfse_enqueue_admin_script() {

    wp_enqueue_script("jquery");
    wp_enqueue_script( 'rfse_jquery_datatables_js', plugins_url( 'includes/js/jquery.dataTables.min.js', __FILE__ ), array(), '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'rfse_enqueue_admin_script');

function rfse_dashboard()
{ 
    ?>

	<!DOCTYPE html>
    <html>
    <head>
    </head>
    <body>
    <?php
        global $post;
        $args = array( 'post_status' => array('publish'), 'post_type'=> 'post','numberposts' => 60);
        $myposts = get_posts( $args );
    ?>
    <div class="container">
      <p class="heading">Dashboard - Social Engage</p>  
      <hr/>
      <div class="table-responsive">          
      <table class="table" id="myTable">
        <thead>
          <tr>
            <th>S.NO.</th>
            <th>Post</th>
            <th>Facebook Shares</th>
            <th>Linkdin Shares</th>
            <th>Twitter Shares</th>
            <th>Pinterest Shares</th>
            <th>Total Shares</th>
          </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ( $myposts as $post ) : setup_postdata( $post ); 
                $post_meta_details = get_post_meta($post->ID);
            ?>
                <tr>
                    <td align="center"><?php echo $i; ?></td>
                    <td><a href="<?php the_permalink(); ?>" target="_blank"><?php the_title(); ?></a>
                        <p style="color:rgb(158, 158, 158);"><?php echo date('M d,Y',strtotime($post->post_date)); ?></p>
                    </td>
                    <td align="center"><span class="fb_stats"><?php echo (!empty($post_meta_details['rf_facebook'][0])) ? $post_meta_details['rf_facebook'][0] : 0; ?></span></td>
                    <td align="center"><span class="ld_stats"><?php echo (!empty($post_meta_details['rf_linkdin'][0])) ? $post_meta_details['rf_linkdin'][0] : 0; ?></span></td>
                    <td align="center"><span class="tw_stats"><?php echo (!empty($post_meta_details['rf_twitter'][0])) ? $post_meta_details['rf_twitter'][0] : 0; ?></span></td>
                    <td align="center"><span class="pt_stats"><?php echo (!empty($post_meta_details['rf_pinterest'][0])) ? $post_meta_details['rf_pinterest'][0] : 0; ?></span></td>
                    <td align="center"><span class="total_stats"><?php echo (int) @$post_meta_details['rf_facebook'][0] + (int) @$post_meta_details['rf_linkdin'][0] + (int) @$post_meta_details['rf_twitter'][0] + (int) @$post_meta_details['rf_pinterest'][0]; ?></span></td>
                </tr>
            <?php $i++; endforeach; ?>
       </tbody>
      </table>

      </div>
    </div>

    </body>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery('#myTable').DataTable();
        });
    </script>
</html>
<?php } ?>