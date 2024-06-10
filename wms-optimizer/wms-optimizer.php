<?php
/**
 * Plugin Name: WMS Optimizer
 * Description: Plugin untuk mempercepat loading website, memperkecil HTML, CSS, dan mengatur cache.
 * Version:     1.0.1
 * Author:      Masizal
 * Author URI:  https://faizal.my.id
 * License:     GPL 2.0
 */

if (!defined('ABSPATH')) exit;

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/minify.php';
require_once plugin_dir_path(__FILE__) . 'includes/cache.php';

// Hooks
add_action('template_redirect', 'so_minify_output');
add_action('init', 'so_setup_cache');
add_action('admin_menu', 'so_add_admin_menu');
add_action('admin_init', 'so_register_settings');
add_action('admin_bar_menu', 'so_admin_bar_cache_clear', 999);

// Admin Menu
function so_add_admin_menu() {
    add_menu_page('WMS Optimizer', 'WMS Optimizer', 'manage_options', 'wms-optimizer', 'so_settings_page', 'dashicons-admin-generic');
}

// Settings Registration
function so_register_settings() {
    register_setting('so_settings_group', 'so_minify_html');
    register_setting('so_settings_group', 'so_minify_css');
    register_setting('so_settings_group', 'so_cache');

    add_settings_section('so_settings_section', 'Pengaturan WMS Optimizer', null, 'wms-optimizer');
    add_settings_field('so_minify_html', 'Minify HTML', 'so_minify_html_render', 'wms-optimizer', 'so_settings_section');
    add_settings_field('so_minify_css', 'Minify CSS', 'so_minify_css_render', 'wms-optimizer', 'so_settings_section');
    add_settings_field('so_cache', 'Aktifkan Cache', 'so_cache_render', 'wms-optimizer', 'so_settings_section');
}

function so_minify_html_render() {
    echo "<input type='checkbox' name='so_minify_html' value='1' " . checked(1, get_option('so_minify_html'), false) . ">";
}

function so_minify_css_render() {
    echo "<input type='checkbox' name='so_minify_css' value='1' " . checked(1, get_option('so_minify_css'), false) . ">";
}

function so_cache_render() {
    echo "<input type='checkbox' name='so_cache' value='1' " . checked(1, get_option('so_cache'), false) . ">";
}

function so_settings_page() {
    ?>
    <div class="wrap">
        <h1>Pengaturan WMS Optimizer</h1>
        <form method="post" action="options.php" style="float: left; width: 60%;">
            <?php
            settings_fields('so_settings_group');
            do_settings_sections('wms-optimizer');
            submit_button();
            ?>
        </form>
        <div style="float: right; width: 35%; padding: 20px; border: 1px solid #ddd; background: #fff;">
            <img src="https://wms.faizal.my.id/wms-optimizer.png" alt="WMS Optimizer" style="width: 100%; height: auto;">
            <p>Plugin ini masih dalam tahap pengembangan, kunjungi <a href="https://faizal.my.id/wms-optimizer">https://faizal.my.id</a> untuk mendapatkan update terkini.</p>
            <p>Jika Anda merasa plugin ini bermanfaat, Anda dapat memberikan donasi untuk mendukung pengembangan lebih lanjut melalui <a href="https://trakteer.id/masizal" target="_blank">Traktir Kopi</a>. Terima kasih!</p>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php
}

// Clear Cache Functionality
add_action('admin_post_clear_cache', 'so_clear_cache');

function so_clear_cache() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    // Clear the cache directory
    $cache_dir = WP_CONTENT_DIR . '/cache/';
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                so_rrmdir($file);
            } else {
                unlink($file);
            }
        }
    }

    // Clear transients and other cache mechanisms
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    wp_cache_flush();

    wp_redirect(admin_url('admin.php?page=wms-optimizer&cache_cleared=true'));
    exit;
}

function so_rrmdir($dir) {
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? so_rrmdir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

function so_admin_bar_cache_clear($wp_admin_bar) {
    $args = [
        'id' => 'so_cache_clear',
        'title' => 'Hapus Cache',
        'href' => admin_url('admin-post.php?action=clear_cache'),
    ];
    $wp_admin_bar->add_node($args);
}
?>
