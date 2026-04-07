<?php

/*
Plugin Name: Aimax – YouTube Channel Scraper Admin
Description: Manage and Scrape YouTube channel videos from WP Admin Dashboard.
Author: A.DEV
Version: 1.0.0
Requires Plugins: jwt-authentication-for-wp-rest-api
*/

define('YCS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('YCS_UPLOAD_PATH', wp_upload_dir()['basedir'] . '/youtube_scraper');
define('YCS_UPLOAD_URL', wp_upload_dir()['baseurl'] . '/youtube_scraper');

register_activation_hook(__FILE__, function () {
    if (!file_exists(YCS_UPLOAD_PATH)) {
        mkdir(YCS_UPLOAD_PATH, 0755, true);
    }
});

// add_action('admin_menu', function () {
//     add_menu_page('YouTube Scraper', 'YouTube Scraper', 'manage_options', 'ycs-admin', 'ycs_admin_page');
// });

add_action('admin_menu', function () {
    add_menu_page(
        'YouTube Scraper',              // Page title
        'YouTube Scraper',              // Menu title
        'manage_options',               // Capability
        'ycs-admin',                    // Menu slug
        'ycs_admin_page',               // Callback function
        'dashicons-video-alt3',         // Icon (YouTube-like icon)
        30                              // Position
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_ycs-admin') {
        wp_enqueue_script('ycs-script', plugins_url('js/scraper.js', __FILE__), ['jquery'], null, true);
        wp_localize_script('ycs-script', 'ycs_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ycs_nonce')
        ]);
    }
    wp_enqueue_style('youtube-scraper-admin-style', plugins_url('css/admin-style.css', __FILE__));
});

require_once plugin_dir_path(__FILE__) . 'admin/dashboard.php';

// AJAX handlers
add_action('wp_ajax_ycs_add_channel', 'ycs_add_channel');
add_action('wp_ajax_ycs_delete_channel', 'ycs_delete_channel');
add_action('wp_ajax_ycs_scrape_channel', 'ycs_scrape_channel');
add_action('wp_ajax_ycs_check_scrape_status', 'ycs_check_scrape_status');

function ycs_add_channel()
{
    check_ajax_referer('ycs_nonce');
    $name = sanitize_text_field($_POST['name']);
    $id = sanitize_text_field($_POST['id']);

    $channels = get_option('ycs_channels', []);
    $channels[] = ['name' => $name, 'id' => $id];
    update_option('ycs_channels', $channels);
    wp_send_json_success($channels);
}

function ycs_delete_channel()
{
    check_ajax_referer('ycs_nonce');
    $id = sanitize_text_field($_POST['id']);

    $channels = array_filter(get_option('ycs_channels', []), function ($c) use ($id) {
        return $c['id'] !== $id;
    });
    update_option('ycs_channels', array_values($channels));
    wp_send_json_success($channels);
}

// function ycs_scrape_channel()
// {
//     check_ajax_referer('ycs_nonce');
//     $id = sanitize_text_field($_POST['id']);
//     $json_path = YCS_UPLOAD_PATH . "/$id.json";
//     $python = '"C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python313\\python.exe"';
//     $script = escapeshellarg(YCS_PLUGIN_PATH . 'scraper.py');
//     $output_file = escapeshellarg($json_path);
//     $cmd = "$python $script $id $output_file";
//     $output = shell_exec($cmd);
//     update_option("ycs_last_scrape_$id", current_time('mysql'));
//     wp_send_json_success(['output' => $output]);
// }

function ycs_scrape_channel()
{
    check_ajax_referer('ycs_nonce');

    $id = sanitize_text_field($_POST['id']);
    $json_path = YCS_UPLOAD_PATH . "/$id.json";
    $python = '"C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python313\\python.exe"';
    $script = escapeshellarg(YCS_PLUGIN_PATH . 'scraper.py');
    $output_file = escapeshellarg($json_path);

    // Background command
    $cmd = "$python $script $id $output_file > NUL 2>&1 &";

    // Execute the command in background
    pclose(popen("start /B " . $cmd, "r"));

    update_option("ycs_last_scrape_$id", current_time('mysql'));

    wp_send_json_success(['message' => "Scraping started for $id. It will complete in background."]);
}

function ycs_check_scrape_status()
{
    check_ajax_referer('ycs_nonce');
    $id = sanitize_text_field($_POST['id']);
    
    $json_path = YCS_UPLOAD_PATH . "/$id.json";
    $status_path = YCS_UPLOAD_PATH . "/{$id}_status.txt";
    
    $response = [
        'status' => 'scraping',
        'progress' => '',
        'output' => '',
        'last_scraped' => get_option("ycs_last_scrape_$id", 'Never')
    ];
    
    // Check if scraping is complete
    if (file_exists($json_path) && filesize($json_path) > 0) {
        $response['status'] = 'completed';
        $response['output'] = file_get_contents($json_path);
        
        // Clean up status file if exists
        if (file_exists($status_path)) {
            unlink($status_path);
        }
    }
    
    // Check for error status
    if (file_exists($status_path)) {
        $status_content = file_get_contents($status_path);
        if (strpos($status_content, 'ERROR:') === 0) {
            $response['status'] = 'error';
            $response['error'] = str_replace('ERROR:', '', $status_content);
        }
    }
    
    wp_send_json_success($response);
}
