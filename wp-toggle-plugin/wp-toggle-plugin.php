<?php
/**
 * Plugin Name: WP Toggle Plugin
 * Plugin URI: https://example.com
 * Description: A simple WordPress plugin that adds a styled toggle component.
 * Version: 1.0
 * Author: Inherente
 * Author URI: https://about.me/inherente
 * License: GPL2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue CSS & JS
function wp_toggle_enqueue_scripts() {
    wp_enqueue_style('wp-toggle-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_script('wp-toggle-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'wp_toggle_enqueue_scripts');

// Shortcode to display toggle component
function wp_toggle_shortcode() {
    return '<div class="toggle-container">
                <div class="toggle-frame">
                    <p class="toggle-title">Click to Toggle:</p>
                    <button id="toggleButton">Show</button>
                    <div id="toggleContent">Hello! I am a toggleable element.</div>
                </div>
            </div>';
}
add_shortcode('wp_toggle', 'wp_toggle_shortcode');
?>