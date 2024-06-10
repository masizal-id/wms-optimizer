<?php
function so_setup_cache() {
    if (!is_admin() && get_option('so_cache')) {
        $cache_dir = WP_CONTENT_DIR . '/cache/';
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $cache_file = $cache_dir . md5($request_uri) . '.html';

        if (file_exists($cache_file)) {
            echo file_get_contents($cache_file);
            exit;
        }

        ob_start(function ($buffer) use ($cache_file) {
            file_put_contents($cache_file, $buffer);
            return $buffer;
        });
    }
}
?>
