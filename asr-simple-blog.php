<?php
/*
Plugin Name: Simple Blog Layout
Author: Akhtarujjaman Shuvo
Author URI: https://fb.com/suvobd.ml
Version: 1.0
Description: Simple Plugin for Blog a custom blog layout
*/
// Load Scripts
function asrsb_scripts(){
	wp_enqueue_style('asr-grid', plugin_dir_url(__FILE__).'assets/css/grid.css',null,'1.01');
	wp_enqueue_style('asrsb-stylesheet', plugin_dir_url(__FILE__).'assets/css/asb-stylesheet.css',null,'1.01');
}
add_action('wp_enqueue_scripts','asrsb_scripts');



//Building Shortcode
function asrsb_shortcode( $atts ){ 
	
	extract( shortcode_atts( array (
        'show' => 3,
        'color' => '',
        'cat' => '',
    ), $atts ) );
		
	ob_start();?>
            <div class="asr-blog">
				<style type="text/css"> 
					div.asr-blog h4,
					div.asr-blog p,
					div.asr-blog ul.post-meta,
					div.asr-blog h4 a{
						color:<?php echo $color; ?>;
					}
				</style>
                <?php $the_query = new WP_Query( array('posts_per_page' => $show,'category_name' => $cat) ); ?>
				<?php while ($the_query -> have_posts()) : $the_query -> the_post(); ?>
				
                <div class="row">
                    <div class="single-blog-post">
                        <div class="vc_col-sm-3 col-sm-3 col-xs-12 pull-right">
                            <?php the_post_thumbnail('blog', array('class' => 'img-responsive')); ?>
                        </div>
                        <div class="vc_col-sm-1 col-sm-1 col-xs-3">
                            <ul class="post-meta">
                                <span class="date-top"><?php echo get_the_date('d'); ?></span><span class="date-bot"><?php echo get_the_date('M y'); ?></span>
                            </ul> 
                        </div>
                        <div class="vc_col-sm-8 col-sm-8 col-xs-9">
                            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                            <p><?php the_excerpt(); ?></p>
							<div class="clearfix"> 
								<span class="view-count" title="<?php echo asr_getpostviews(get_the_ID()); ?> Views"> 
									<span class="dashicons dashicons-visibility"></span> <?php echo asr_getpostviews(get_the_ID()); ?>
								</span>
								<a href="<?php comments_link(); ?>" title="<?php comments_number(__('No Comments', 'abiz'),__('1 Comment', 'abiz'),__('% Comments', 'abiz')); ?>"><span class="cmnt-count"><span class="dashicons dashicons-testimonial"></span> <?php comments_number(__('0', 'abiz'),__('1', 'abiz'),__('% Comments', 'abiz')); ?></span></a>
								<a class="blog-btn" href="<?php the_permalink(); ?>"><?php _e('Read More','abiz'); ?></a>
							</div>
                           
                        </div>
                    </div>
                </div>		
				
                <?php endwhile; ?><!-- END SINGLE BLOG POST -->
                <?php wp_reset_postdata(); ?>
            </div>
	<?php return ob_get_clean();

}

add_shortcode('asr-blog','asrsb_shortcode');


//reset the count from database on deactivation
register_deactivation_hook( __FILE__, 'asr_plugin_deactivate' );
function asr_plugin_deactivate(){
    $count_key = 'post_views_count_asr';
	
	$allposts = get_posts( 'numberposts=-1&post_type=any&post_status=any' );
	 
	foreach( $allposts as $postinfo ) {
		delete_post_meta( $postinfo->ID, 'related_posts' );
		
		delete_post_meta($postinfo->ID,$count_key);
		add_post_meta($postinfo->ID, $count_key, '0');
	}

}

// function to display number of posts.
if(!function_exists('asr_getpostviews')){
	function asr_getpostviews($postID){
		$count_key = 'post_views_count_asr';
		$count = get_post_meta($postID, $count_key, true);
		if($count==''){
			delete_post_meta($postID, $count_key);
			add_post_meta($postID, $count_key, '0');
			return "0";
		}
		return $count;
	}
}

// function to count views.
if(!function_exists('asr_setpostviews')){
	function asr_setpostviews($postID) {
		$count_key = 'post_views_count_asr';
		$count = get_post_meta($postID, $count_key, true);
		if($count==''){
			$count = 0;
			delete_post_meta($postID, $count_key);
			add_post_meta($postID, $count_key, '0');
		}else{
			if ( is_singular( 'page' ) ) {
				$count++;
				update_post_meta($postID, $count_key, $count);
			}else{
				$count = $count+1/2;
				update_post_meta($postID, $count_key, $count);
			}
		}
	}
}



// Set post view function inside post/page loop
if(!function_exists('asr_the_post_action')){
	function asr_the_post_action() {
		asr_setpostviews(get_the_ID());
	}
}
add_action( 'loop_start', 'asr_the_post_action' );