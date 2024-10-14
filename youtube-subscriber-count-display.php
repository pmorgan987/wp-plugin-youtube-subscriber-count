<?php
/**
 * Plugin Name: YouTube Subscriber Count Display
 * Description: A simple plugin to display your YouTube channel subscriber count using the YouTube Data API.
 * Version: 1.0
 * Author: Patrick Morgan
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Add a shortcode to display the subscriber count
function youtube_subscriber_count_shortcode($atts) {
    // Your YouTube API key and channel ID from the passed attributes
    $api_key = sanitize_text_field($atts['api_key']);
    $channel_id = sanitize_text_field($atts['channel_id']);

    if ($api_key == '' || $channel_id == '') {
        return 'Must provide api_key and channel_id.';
    }

    // YouTube API URL
    $api_url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id={$channel_id}&key={$api_key}";
    //$api_url = "https://www.googleapis.com/youtube/v3/channels?part=statistics&id={$channel_id}&fields=items(id%2Csnippet%2Fthumbnails)&key={$api_key}";


    // Make the API request
    $response = wp_remote_get($api_url);

    // Check for errors
    if (is_wp_error($response)) {
        return 'Unable to retrieve subscriber count.';
    }

    // Parse the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if the subscriber count is available
    if (!empty($data)) {
        $subscriber_count = $data['items'][0]['statistics']['subscriberCount'];
        $icon = $data['items'][0]['snippet']['thumbnails']['default']['url'];
        $name = $data['items'][0]['snippet']['title'];
        $custom_url = $data['items'][0]['snippet']['customUrl'];
 //       return 'YouTube Subscribers: ' . number_format($subscriber_count);
        return youtube_subscriber_count_display($subscriber_count,$icon,$name,$custom_url);
    } else {
        return 'Unable to retrieve subscriber count.';
    }
}

add_shortcode('youtube_subscriber_count', 'youtube_subscriber_count_shortcode');



// Enqueue the plugin stylesheet
function youtube_subscriber_count_enqueue_styles() {
    // Get the URL of the plugin directory
    $plugin_url = plugin_dir_url(__FILE__);

    // Enqueue the stylesheet
    wp_enqueue_style('youtube-subscriber-count-style',$plugin_url.'css/style.css');
}
add_action('wp_enqueue_scripts','youtube_subscriber_count_enqueue_styles');



// Display function
function youtube_subscriber_count_display($subscriber_count,$icon,$name,$custom_url) {
    return "
    <div class='ysc_container'>
        <img src='{$icon}' alt=''>
        <div>
            <div class='ysc_channel_name'>{$name}</div>
            <div class='ysc_subscriber_count'>{$subscriber_count} Subscribers</div>
            <a href='https://www.youtube.com/{$custom_url}' target='_blank'>Join Us!</a>
        </div>
    </div>
    ";
}