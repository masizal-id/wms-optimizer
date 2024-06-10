<?php
// Minify HTML output
function so_minify_output() {
    if (get_option('so_minify_html')) {
        ob_start('so_minify_html');
    }
}

function so_minify_html($buffer) {
    $search = [
        '/<!--(?!<!)[^\[>][\s\S]*?-->/', // Remove HTML comments
        '/\s+(?=<)/',  // Remove whitespace before tags
        '/>\s+/',  // Remove whitespace after tags
        '/\s{2,}/',  // Shorten multiple whitespace sequences
    ];
    $replace = [
        '', // Remove comments
        '', // Remove whitespace before tags
        '>', // Remove whitespace after tags
        ' ', // Shorten multiple whitespace sequences
    ];
    return preg_replace($search, $replace, $buffer);
}

// Minify CSS output
function so_minify_css($css) {
    $css = preg_replace('/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $css); // Remove comments
    $css = preg_replace('/\s*([{}|:;,])\s*/', '$1', $css); // Remove whitespace around punctuation
    $css = preg_replace('/;\s*}/', '}', $css); // Remove whitespace before closing brace
    return $css;
}

add_filter('style_loader_tag', 'so_minify_css_output', 10, 2);

function so_minify_css_output($html, $handle) {
    if (get_option('so_minify_css') && strpos($html, '.css') !== false) {
        // Extract URL from style tag
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
