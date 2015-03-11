<?php

/*
  Plugin Name: WP Galleriffic Bootstrap
  Description: This is a image gallery plugin for WordPress built in function using Galleriffic in bootstrap base theme's
  Version: 0.5
  Author: Jan Maat
  License: GPLv2
 */

/*  Copyright 2011  Jan Maat  (email : jenj.maat@gmail.com)

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
/*
 * Install 
 */
register_activation_hook(__FILE__, 'wp_galleriffic_bootstrap_install');

function wp_galleriffic_bootstrap_install() {
    if (version_compare(get_bloginfo('version'), '3.8.1', '<')) {        
        die("This Plugin requires WordPress version 3.8.1 or higher");
    }
}
function wp_galleriffic_bootstrap_method() {
    wp_enqueue_script('galleriffic', plugins_url('jquery.galleriffic.js', __FILE__), array('jquery'));
    wp_enqueue_style('galleriffic', plugins_url('galleriffic-2.css', __FILE__));
    $options = get_option('wp_galleriffic_bootstrap_options');
    if (($options['size'] === 'large')) {
        $width = get_option('large_size_w') + 20;
        $height = get_option('large_size_h') + 20;
    } else {
        $width = get_option('medium_size_w') + 20;
        $height = get_option('medium_size_h') + 20;
    }
        $thumbnail_height = get_option( 'thumbnail_size_h' );
    $caption = $options['caption'];
    $custom_css = "
                div#gallery.content {
                       width: {$width}px;
                }
                div.loader {
                    width: {$width}px;
                    height: {$height}px;
                }
                div.slideshow a.advance-link {	
                    width: {$width}px;
                    height: {$height}px; 
                    line-height: {$height}px;
                 }
                 span.image-caption {
                    width: {$width}px;
                 }
                 div.slideshow-container {
                    height: {$height}px;
                 }
                 div.caption-container  {
                    height: {$caption}px;
                 }
                 ul.thumbs li  {
                    height: {$thumbnail_height}px;
                 }
                ";
    wp_add_inline_style('galleriffic', $custom_css);
}
add_action('wp_enqueue_scripts', 'wp_galleriffic_bootstrap_method');

/**
 * Translate with qtranslate function
 */
function wp_galleriffic_bootstrap_translate($input) {
    global $q_config;
    $output = $input;
    if (function_exists('qtrans_use')) {
        $output = qtrans_use($q_config['language'], $input, true);
    }
    return $output;
}



function wp_galleriffic_bootstrap_init()
{
// Localization
load_plugin_textdomain('wp_galleriffic_bootstrap', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}

// Add actions
add_action('init', 'wp_galleriffic_bootstrap_init');


remove_shortcode('gallery');
add_shortcode('gallery', 'wp_galleriffic_bootstrap_shortcode');

/**
 * The Gallery shortcode.
 *
 * This implements the functionality of the Gallery Shortcode for displaying
 * WordPress images on a post.
 *
 * @since 2.5.0
 *
 * @param array $attr Attributes of the shortcode.
 * @return string HTML content to display gallery.
 */
function wp_galleriffic_bootstrap_shortcode($atts) {
    global $post;
    
    static $instance = 0;
	$instance++;
        
    $order = 'ASC';
    $orderby = 'post__in';
    $output_buffer = "";
    $post = get_post();
    $options = get_option('wp_galleriffic_bootstrap_options');
    $size = $options['size'];
    $include = $atts['ids'];

    if (!empty($include)) {
        $include = preg_replace('/[^0-9,]+/', '', $include);
        $_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));

        $attachments = array();
        foreach ($_attachments as $key => $val) {
            $attachments[$val->ID] = $_attachments[$key];
        }
    } else {
        Return $output_buffer;
    }
    
    $playLinkText = __('Start', 'wp_galleriffic_bootstrap');
    $pauseLinkText = __('Stop', 'wp_galleriffic_bootstrap');
    $prevLinkText = __('Vorige', 'wp_galleriffic_bootstrap');
    $nextLinkText = __('Volgende', 'wp_galleriffic_bootstrap');
    $nextPageLinkText = __('Volgende &rsaquo;', 'wp_galleriffic_bootstrap');
    $prevPageLinkText = __('&lsaquo; Vorige', 'wp_galleriffic_bootstrap');
// built output slideshow layout
    $output_buffer .= '<div class="row">

        <div class="col-12 col-lg-8">
            <div id="gallery'.$instance.'" class="content">
                <div id="controls'.$instance.'" class="controls"></div>
                <div class="slideshow-container">
                    
                    <div id="slideshow'.$instance.'" class="slideshow"></div>
                </div>
                <div id="caption'.$instance.'" class="caption-container"></div>
            </div>
        </div>
        <div class=" col-lg-4">
        <div id="thumbs'.$instance.'" class="navigation">';
    if ($attachments) {
        $output_buffer .= '<ul class="thumbs noscript">';
        foreach ($attachments as $attachment_id => $attachment) {
            $output_buffer .= '<li> <a class="thumb" href="  ';
            $image = wp_get_attachment_image_src($attachment_id, $size);
            $output_buffer .= $image[0];
            $output_buffer .= '" title="';
            $output_buffer .= wp_galleriffic_bootstrap_translate($attachment->post_title);
            $output_buffer .= '">';
            $output_buffer .= wp_get_attachment_image($attachment_id, 'thumbnail');
            $output_buffer .= '</a>';
            $output_buffer .= '<div class="caption">';
            if ($attachment->post_excerpt) {
                $output_buffer .= wp_galleriffic_bootstrap_translate($attachment->post_excerpt);
            }
            if ($attachment->post_content) {
                $output_buffer .='<br>' . wp_galleriffic_bootstrap_translate($attachment->post_content);
            }
            $output_buffer .= '</div></li>';
        }
        $output_buffer .= '</ul>';
    }
    $output_buffer .= '</div></div></div>';
    $output_buffer .= "<script>
    jQuery(document).ready(function() {
        jQuery('#thumbs$instance').galleriffic({
            imageContainerSel: '#slideshow$instance',
            controlsContainerSel: '#controls$instance',
            captionContainerSel: '#caption$instance' ,
            playLinkText:'$playLinkText',
            pauseLinkText:  '$pauseLinkText',
            prevLinkText:   '$prevLinkText',
            nextLinkText:  '$nextLinkText',
            nextPageLinkText:   '$nextPageLinkText',
            prevPageLinkText: '$prevPageLinkText'
        });        
    });    
</script>";

    return $output_buffer;
}

if (is_admin()) {
    require_once('wp_galleriffic_bootstrap_admin.php');
}

