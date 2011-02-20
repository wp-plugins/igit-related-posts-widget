<?php /**
 * Plugin Name: IGIT Related Posts Widget
 * Plugin URI: http://www.hackingethics.com/blog/wordpress-plugins/igit-related-posts-widget/
 * Description: A widget that embed related posts into your sidebar or wherver your theme supoorts widgets.
 * Version: 1.0
 * Author: Ankur Gandhi
 * Author URI: http://www.hackingethics.com/
 *
 * 
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 1.0
 */
if (!defined('ABSPATH'))
    die("Aren't you supposed to come here via WP-Admin?");
if (!defined('WP_CONTENT_URL'))
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_PLUGIN_URL'))
    define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
if (!defined('IGIT_WIDGET_REL_CSS_URL'))
    define('IGIT_WIDGET_REL_CSS_URL', WP_CONTENT_URL . '/plugins/igit-related-posts-widget/css');
require_once(dirname(__FILE__) . '/inc/get-the-image.php');
add_action('widgets_init', 'igit_rel_load_widgets');
function igit_rel_load_widgets()
{
    register_widget('IGIT_Related_Posts_Widget');
}
class IGIT_Related_Posts_Widget extends WP_Widget
{
    function IGIT_Related_Posts_Widget()
    {
        $widget_ops  = array(
            'classname' => 'example',
            'description' => __('An example widget that displays a person\'s name and sex.', 'example')
        );
        $control_ops = array(
            'width' => 300,
            'height' => 350,
            'id_base' => 'igit-relted-posts-widget'
        );
        $this->WP_Widget('igit-relted-posts-widget', __('IGIT Related Posts Widget', 'example'), $widget_ops, $control_ops);
    }
    function widget($args, $instance)
    {
        extract($args);
        global $wpdb, $post, $single, $WP_PLUGIN_URL;
        $title            = apply_filters('widget_title', $instance['title']);
        $pstwidcount      = $instance['igit_widget_show_post_count'];
        $showigiteidimage = isset($instance['igit_widget_show_image']) ? $instance['igit_widget_show_image'] : false;
        $igitwid_limit    = $pstwidcount;
        if (!$igitwid_limit)
            $igitwid_limit = 5;
        $time_difference     = get_settings('gmt_offset');
        $now                 = gmdate("Y-m-d H:i:s", (time() + ($time_difference * 3600)));
        $pcontigitwidget     = preg_replace('/<img[^>]+./', '', $post->post_content);
        $igit_wid_search_str = addslashes($post->post_title . ' ' . $pcontigitwidget);
        if (($post->ID != '') || ($igit_wid_search_str != '')) {
            $igitwid_sql = "SELECT DISTINCT ID,post_title,post_date,post_content," . "MATCH(post_title,post_content) AGAINST ('" . $igit_wid_search_str . "' WITH QUERY EXPANSION) AS score " . "FROM " . $wpdb->posts . " WHERE " . "MATCH (post_title,post_content) AGAINST ('" . $igit_wid_search_str . "'  WITH QUERY EXPANSION) " . "AND post_date <= '" . $now . "' " . "AND post_status = 'publish' AND post_password = '' " . "AND id != " . $post->ID . " AND post_type = 'post' ";
            $igitwid_sql .= "ORDER BY RAND() LIMIT 0," . $igitwid_limit;
            $igitwid_result_counter = 0;
            $igitwid_results        = $wpdb->get_results($igitwid_sql);
            if (!$igitwid_results) {
                $igitwid_ptags = get_the_tags($post->ID);
                $igitwid_cstr  = "";
                $i             = 1;
                if ($igitwid_ptags) {
                    foreach ($igitwid_ptags as $igitwid_ptag) {
                        if ($i < count($igitwid_ptags))
                            $igitwid_cstr .= " post_title LIKE '%" . $igitwid_ptag->name . "%' OR ";
                        else if ($i == count($ptags))
                            $igitwid_cstr .= " post_title LIKE '%" . $igitwid_ptag->name . "%'";
                        $i++;
                    }
                    $igitwid_tags_sql = "SELECT DISTINCT ID,post_title,post_date,post_content FROM " . $wpdb->posts . " WHERE (" . $igitwid_cstr . ") AND (post_date <= '" . $now . "' " . "AND post_status = 'publish'  AND post_password = '' " . "AND ID != " . $post->ID . " AND post_type = 'post' )";
                    $tags_sql .= "ORDER BY RAND() LIMIT 0," . $limit;
                    $igitwid_result_counter = 0;
                    $igitwid_results        = $wpdb->get_results($igitwid_tags_sql);
                }
                if (!$igitwid_result) {
                    $igitwid_random_sql     = "SELECT DISTINCT ID, post_title, post_content, post_date,comment_count FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type = 'post' AND post_password = '' AND ID != $post->ID ORDER BY RAND() LIMIT 0," . $igitwid_limit;
                    $igitwid_result_counter = 0;
                    $igitwid_results        = $wpdb->get_results($igitwid_random_sql);
                }
            }
        } else {
            $igitwid_results = false;
        }
        $igitwid_output = '<style type="text/css">


#igit_wid_rpwt_li
{
	background:none repeat scroll 0 0 transparent !important;
	padding-left:5px !important;
}


</style><div id="igit_wid_rpwt_css" style= "border: 0pt none ; margin: 0pt; padding: 0pt; clear: both;">';
        if ($igitwid_results) {
            $igitwid_width  = $instance['igit_widget_image_width'];
            $igitwid_height = $instance['igit_widget_image_height'];
            if ($showigiteidimage) {
                $igitwid_output .= '<ul class="igit_wid_thumb_ul_list" style="list-style-type: none;padding-left:0px;">';
            }
            if (!$showigiteidimage) {
                $igitwid_output .= '<ul style="padding-left:5px;list-style-type:disc;">';
            }
            $igitwid_nodatacnt = 0;
            $widcnt            = 1;
            foreach ($igitwid_results as $igitwid_result) {
                $igitwid_pstincat = false;
                $igitwid_title    = trim(stripslashes($igitwid_result->post_title));
                $igitwid_image    = "";
                preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', $igitwid_result->post_content, $igitwid_matches);
                if (isset($igitwid_matches)) {
                    $igitwid_image = $igitwid_matches[1][0];
                    $igitwid_bgurl = get_bloginfo('url');
                    if (!strstr($igitwid_image, $igitwid_bgurl)) {
                        $igitwid_image = WP_PLUGIN_URL . '/igit-related-posts-widget/images/noimage.gif';
                    }
                }
                if (strlen(trim($image)) == 0) {
                    $igitwid_image = WP_PLUGIN_URL . '/igit-related-posts-widget/images/noimage.gif';
                }
                $igitwid_image = parse_url($igitwid_image, PHP_URL_PATH);
                if ($showigiteidimage) {
                    $igitwid_divlnk = "onclick=location.href='" . get_permalink($igitwid_result->ID) . "'; style=cursor:pointer;";
                    $igitwid_output .= '<li id="igit_wid_rpwt_li" style="height:' . $igitwid_height . 'px;    padding-bottom: 10px;" ' . $igitwid_divlnk . '>';
                    $igitwid_output .= '<div id="igit_wid_rpwt_main_image"  style="float:left;"><a href="' . get_permalink($igitwid_result->ID) . '" target="_top"><img id="igit_rpwt_thumb" src="' . WP_PLUGIN_URL . '/igit-related-posts-widget/timthumb.php?src=' . IGIT_igitwid_get_the_image(array(
                        'post_id' => $igitwid_result->ID
                    )) . '&w=' . $igitwid_width . '&h=' . $igitwid_height . '&zc=1"/></a></div>';
                    $igitwid_output .= '<div id="igit_wid_title" style="float:left;padding-left:7px;    width: 65%;"><a href="' . get_permalink($igitwid_result->ID) . '" target="_top">' . $igitwid_title . '</a></div></li>';
                    $igitwid_nodatacnt = 1;
                }
                if (!$showigiteidimage) {
                    $igitwid_divlnk = "onclick=location.href='" . get_permalink($igitwid_result->ID) . "'; style=cursor:pointer;";
                    $igitwid_output .= '<li id="igit_wid_rpwt_li"' . $igitwid_divlnk . '  style="list-style-type:disc;">' . $widcnt . '. <a href="' . get_permalink($igitwid_result->ID) . '" target="_top">' . $igitwid_title . '</a></li>';
                    $igitwid_nodatacnt = 1;
                    $widcnt++;
                }
                $igitwid_result_counter++;
                if ($igitwid_result_counter == $igitwid_limit)
                    break;
            }
            if ($igitwid_nodatacnt == 0) {
                $igitwid_output = '<div id="crp_wid_related">';
                $output .= ($igitwid_crp_settings['blank_output']) ? ' ' : '<p>' . __("No related posts.", CRP_LOCAL_NAME) . '</p>';
            }
            $output .= '</ul>';
        } else {
            $igitwid_output = '<div id="crp_wid_related">';
            $igitwid_output .= ($igitwid_crp_settings['blank_output']) ? ' ' : '<p>' . __($igitwid_igit_rpwt['no_related_post_text'], CRP_LOCAL_NAME) . '</p>';
        }
        $igitwid_output .= '</div>';
        $igitwid_output .= '<div style="font-size: 10px; float: left;width:100%;padding-top:0px;padding-bottom:0px;" ><a style="color:#ffffff" href="http://www.hackingethics.com/"  title="PHP Freelancer,PHP Programmer India,PHP Developer India,PHP Freelance Programmer,PHP freelance Developer">PHP Freelancer</a></div>';
        echo $before_widget;
        if ($title)
            echo $before_title . $title . $after_title;
        if ($igitwid_output)
            echo $igitwid_output;
        echo $after_widget;
    }
    function update($new_instance, $old_instance)
    {
        $instance                                = $old_instance;
        $instance['title']                       = strip_tags($new_instance['title']);
        $instance['igit_widget_show_post_count'] = strip_tags($new_instance['igit_widget_show_post_count']);
        $instance['igit_widget_show_image']      = $new_instance['igit_widget_show_image'];
        $instance['igit_widget_image_width']     = $new_instance['igit_widget_image_width'];
        $instance['igit_widget_image_height']    = $new_instance['igit_widget_image_height'];
        return $instance;
    }
    function form($instance)
    {
        $defaults = array(
            'title' => __('Related Posts', 'relatedposts'),
            'igit_widget_show_post_count' => __('5', 'example'),
            'igit_widget_show_image' => 'yes',
            'igit_widget_image_width' => '55',
            'igit_widget_image_height' => '55'
        );
        $instance = wp_parse_args((array) $instance, $defaults);
?>

		<!-- Widget Title: Text Input -->
        
        <p>
            <label for="<?php
        echo $this->get_field_id('title');
?>">
            <?php
        _e('Title:', 'hybrid');
?>
            </label>
            
            
            
            <input id="<?php
        echo $this->get_field_id('title');
?>" name="<?php
        echo $this->get_field_name('title');
?>" value="<?php
        echo $instance['title'];
?>" style="width:100%;" class="widefat"/>
            
            </p>
        
        
        
		

		<!-- Your Name: Text Input -->
        
        
            <p>
            <label for="<?php
        echo $this->get_field_id('igit_widget_show_post_count');
?>">
            <?php
        _e('Enter records to show:', 'hybrid');
?>
            </label>
            
            
            
            <input id="<?php
        echo $this->get_field_id('igit_widget_show_post_count');
?>" name="<?php
        echo $this->get_field_name('igit_widget_show_post_count');
?>" value="<?php
        echo $instance['igit_widget_show_post_count'];
?>" style="width:100%;" class="widefat"/>
            
            </p>
		
		
		

		<!-- Show Sex? Checkbox -->
        <?php
        if ($instance['igit_widget_show_image'] == "yes") {
            $chkdstr = "checked='checked'";
        } else {
            $chkdstr = "";
        }
?>
		<p>
			<input class="checkbox" type="checkbox" <?php
        echo $chkdstr;
?> id="<?php
        echo $this->get_field_id('igit_widget_show_image');
?>" name="<?php
        echo $this->get_field_name('igit_widget_show_image');
?>" value="yes"/> 
			<label for="<?php
        echo $this->get_field_id('igit_widget_show_image');
?>"><?php
        _e('Show Image?', 'example');
?></label>
		</p>
        
        
        <!-- Your Name: Text Input -->
        
        
            <p>
            <label for="<?php
        echo $this->get_field_id('igit_widget_image_width');
?>">
            <?php
        _e('Image Width:', 'hybrid');
?>
            </label>
            
            
            
            <input id="<?php
        echo $this->get_field_id('igit_widget_image_width');
?>" name="<?php
        echo $this->get_field_name('igit_widget_image_width');
?>" value="<?php
        echo $instance['igit_widget_image_width'];
?>" style="width:100%;" class="widefat"/>
            
            </p>
            
             
        <!-- Your Name: Text Input -->
        
        
            <p>
            <label for="<?php
        echo $this->get_field_id('igit_widget_image_height');
?>">
            <?php
        _e('Image Height:', 'hybrid');
?>
            </label>
            
            
            
            <input id="<?php
        echo $this->get_field_id('igit_widget_image_height');
?>" name="<?php
        echo $this->get_field_name('igit_widget_image_height');
?>" value="<?php
        echo $instance['igit_widget_image_height'];
?>" style="width:100%;" class="widefat"/>
            
            </p>
		

	<?php
    }
    function igit_widget_show_rel_post($pstwidcount, $showigiteidimage)
    {
    }
}
?>