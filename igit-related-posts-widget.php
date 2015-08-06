<?php /**
 * Plugin Name: IGIT Related Posts Widget
 * Plugin URI: http://www.hackingethics.com/blog/wordpress-plugins/igit-related-posts-widget/
 * Description: A related posts widget. Related posts show by matching category and tags.
 * Version: 1.3
 * Author: Ankur Gandhi
 * Author URI: http://www.hackingethics.com/
 *
License: GNU General Public License (GPL), v3 (or newer)
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags:Related posts, related post with images
Copyright (c) 2010 - 2012 Ankur Gandhi. All rights reserved.
 
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
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
if ( ! defined( 'IGIT_RPWID_PLUGIN_FOLDER_NAME' ) )
      define( 'IGIT_RPWID_PLUGIN_FOLDER_NAME', 'igit-related-posts-widget' );	
if (!defined('IGIT_WIDGET_REL_CSS_URL'))
    define('IGIT_WIDGET_REL_CSS_URL', WP_CONTENT_URL . '/plugins/'.IGIT_RPWID_PLUGIN_FOLDER_NAME.'/css');
		
require_once(dirname(__FILE__) . '/inc/get-the-image.php');
add_action('widgets_init', 'igit_rel_load_widgets');
function igit_rel_load_widgets()
{
    register_widget('IGIT_Related_Posts_Widget');
}
function IGITwidobjectToArray($objecttemp)
{
    foreach ($objecttemp as $object) {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        } //!is_object($object) && !is_array($object)
        if (is_object($object)) {
            $object = get_object_vars($object);
        } //is_object($object)
        $lastarray[] = $object;
        //return array_map( 'objectToArray', $object );
    } //$objecttemp as $object
    return $lastarray;
}
class IGIT_Related_Posts_Widget extends WP_Widget
{
    function IGIT_Related_Posts_Widget()
    {
        $widget_ops  = array(
            'classname' => 'example',
            'description' => __('IGIT Related Posts Widget Plugin allows you to embed related posts into your sidebar', 'example')
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
       
		if ($post->ID != '' && !is_home()) {
			$cats = get_the_category($post->ID);
			if ($cats) {
				foreach ($cats as $cat) {
					$cat_id_array[] = $cat->cat_ID;
				} //$cats as $cat
			} //$cats
			$ptags = get_the_tags($post->ID);
			$cstr  = "";
			$i     = 1;
			if ($ptags) {
				foreach ($ptags as $ptag) {
					$tag_id_array[] = get_tag_ID(trim($ptag->name));
					$i++;
				} //$ptags as $ptag
			} //$ptags
			if (isset($cat_id_array) && isset($tag_id_array)) 
			{ 
				$resultstag = get_posts(array(
					'tag__in' => $tag_id_array,
					'post__not_in' => array(
						$post->ID
					)
				));
				
				
					
				/**** Reset Index of $cat_id_array ****/
				$cat_id_array = array_values($cat_id_array);
				
				if(!empty($cat_id_array)){
					$resultscat = get_posts(array(
					'category__in' => $cat_id_array,
					'post__not_in' => array(
						$post->ID
					 )
					));
				} 
		
				if ($resultscat && $resultstag) {
					$array1  = IGITwidobjectToArray($resultstag);
					$array2  = IGITwidobjectToArray($resultscat);
					$results = array_intersect($array1, $array2);
				} //$resultscat && $resultstag
				else {
					$resultscat = get_posts(array(
						'category__in' => $cat_id_array,
						'post__not_in' => array(
							$post->ID
						)
					));
					$igitwid_results    = IGITwidobjectToArray($resultscat);
				}
			} //isset($cat_id_array) && isset($tag_id_array)
			else if (isset($cat_id_array) && !isset($tag_id_array)) {
			
				
				
				
				/**** Reset Index of $cat_id_array ****/
				$cat_id_array = array_values($cat_id_array);
				
				if(!empty($cat_id_array)){
					$resultscat = get_posts(array(
					'category__in' => $cat_id_array,	
					'posts_per_page'   => $igitwid_limit,				
					'post__not_in' => array(
						$post->ID
					 )
					));
				} 
				
				
				
				if(!empty($resultscat)){
					 $igitwid_results    = IGITwidobjectToArray($resultscat);
				}
				else{
					 $igitwid_results = array();
				}
			
			
			} //isset($cat_id_array) && !isset($tag_id_array)
			else if (!isset($cat_id_array) && isset($tag_id_array)) {
				$resultstag = get_posts(array(
					'tag__in' => $tag_id_array,
					'posts_per_page'   => $igitwid_limit,
					'post__not_in' => array(
						$post->ID
					)
				));
				$igitwid_results    = IGITwidobjectToArray($resultstag);
			} //!isset($cat_id_array) && isset($tag_id_array)
			else {
				
				$resultscat = get_posts(array(
					'post__not_in' => array(
						$post->ID
					),
					'posts_per_page'   => $igitwid_limit,
				));
				$igitwid_results    = IGITwidobjectToArray($resultscat);
			}
		
			if (!$igitwid_results) {
				$resultscat = get_posts(array(
					'orderby' => 'rand',
					'posts_per_page'   => $igitwid_limit
				));
				$igitwid_results    = IGITwidobjectToArray($resultscat);
			} //!$results
		}else {
			$resultscat = get_posts(array(
				'orderby' => 'rand',
				'posts_per_page'   => $igitwid_limit,
				'post__not_in' => array(
					$post->ID
				)
			));
			$igitwid_results    = IGITwidobjectToArray($resultscat);
           // $igitwid_results = false;
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
			 $igitwid_result_counter = 0;
            foreach ($igitwid_results as $igitwid_result) {
                $igitwid_pstincat = false;
                $igitwid_title    = trim(stripslashes($igitwid_result['post_title']));
                $igitwid_image    = "";
               
                if ($showigiteidimage) {
                    $igitwid_divlnk = "onclick=location.href='" . get_permalink($igitwid_result['ID']) . "'; style=cursor:pointer;";
                    $igitwid_output .= '<li id="igit_wid_rpwt_li" style="height:' . $igitwid_height . 'px;    margin-bottom: 10px;" ' . $igitwid_divlnk . '>';
                    $igitwid_output .= '<div id="igit_wid_rpwt_main_image"  style="float:left;"><a href="' . get_permalink($igitwid_result['ID']) . '" target="_top"><img id="igit_rpwid_thumb" src="' . WP_PLUGIN_URL . '/'.IGIT_RPWID_PLUGIN_FOLDER_NAME.'/timthumb.php?src=' . IGIT_igitwid_get_the_image(array(
                        'post_id' => $igitwid_result['ID']
                    )) . '&w=' . $igitwid_width . '&h=' . $igitwid_height . '&zc=0"/></a></div>';
                    $igitwid_output .= '<div id="igit_wid_title" style="float:left;padding-left:7px;    width: 65%;"><a href="' . get_permalink($igitwid_result['ID']) . '" target="_top">' . $igitwid_title . '</a></div></li>';
                    $igitwid_nodatacnt = 1;
                }
                if (!$showigiteidimage) {
                    $igitwid_divlnk = "onclick=location.href='" . get_permalink($igitwid_result['ID']) . "'; style=cursor:pointer;";
                    $igitwid_output .= '<li id="igit_wid_rpwt_li"' . $igitwid_divlnk . '  style="list-style-type:disc;">' . $widcnt . '. <a href="' . get_permalink($igitwid_result['ID']) . '" target="_top">' . $igitwid_title . '</a></li>';
                    $igitwid_nodatacnt = 1;
                    $widcnt++;
                }
                $igitwid_result_counter++;
                if ($igitwid_result_counter == $igitwid_limit)
                    break;
            }
            if ($igitwid_nodatacnt == 0) {
                $igitwid_output = '<div id="crp_wid_related">';
                $igitwid_output .= ($igitwid_crp_settings['blank_output']) ? ' ' : '<p>' . __("No related posts.", CRP_LOCAL_NAME) . '</p>';
            }
            $igitwid_output .= '</ul>';
        } else {
            $igitwid_output = '<div id="crp_wid_related">';
            $igitwid_output .= ($igitwid_crp_settings['blank_output']) ? ' ' : '<p>' . __($igitwid_igit_rpwt['no_related_post_text'], CRP_LOCAL_NAME) . '</p>';
        }
        $igitwid_output .= '</div>';
       /* $igitwid_output .= '<div style="font-size: 10px; float: left;width:100%;padding-top:0px;padding-bottom:0px;" ><a style="color:#ffffff" href="http://www.hackingethics.com/"  title="PHP Freelancer,PHP Programmer India,PHP Developer India,PHP Freelance Programmer,PHP freelance Developer">PHP Freelancer</a></div>';*/
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