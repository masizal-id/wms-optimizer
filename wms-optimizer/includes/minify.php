<?php
function so_minify_output() {
    if (get_option('so_minify_html')) {
        ob_start('so_minify_html');
    }
}

function so_minify_html($buffer) {
    $search = [
        '/<!--(?!<!)[^\[>][\s\S]*?-->/', 
        '/\s+(?=<)/',  
        '/>\s+/',  
        '/\s{2,}/', 
    ];
    $replace = [
        '',
        '',
        '>',
        ' ',
    ];
    return preg_replace($search, $replace, $buffer);
}

function so_minify_css($css) {
    $css = preg_replace('/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $css);
    $css = preg_replace('/\s*([{}|:;,])\s*/', '$1', $css);
    $css = preg_replace('/;\s*}/', '}', $css);
    return $css;
}

add_filter('style_loader_tag', 'so_minify_css_output', 10, 2);

function so_minify_css_output($html, $handle) {
    if (get_option('so_minify_css') && strpos($html, '.css') !== false) {
        preg_match('/href=["\']?([^"\'>]+)["\']?/', $html, $matches);
        if (!empty($matches[1])) {
            $css_url = $matches[1];
            $response = wp_remote_get($css_url);
            if (is_array($response) && !is_wp_error($response)) {
                $css = wp_remote_retrieve_body($response);
                $minified_css = so_minify_css($css);
                return '<style>' . $minified_css . '</style>';
            }
        }
    }
    return $html;
}
?>
