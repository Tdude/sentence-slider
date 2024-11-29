<?php
/**
 * Plugin Name: Simple Sentence Slider/fader for Maja
 * Plugin URI: https://stegetfore.nu/simple-sentence-slider
 * Description: A simple slider plugin that allows admins to add sentences that rotate in a fader/slider.
 * Version: 0.0.3
 * Requires at least WP: 5.2
 * Requires PHP: 7.2
 * Author: Tibor Berki
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sentence-slider
 * Domain Path: /languages
 *
 * Use the shortcode [sentence_slider] anywhere you want to display the slider
 * 
 * Legal rant default text:
 * Simple Sentence Slider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Simple Sentence Slider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * GNU General Public License see https://www.gnu.org/licenses/gpl-2.0.html.
 */


if (!defined('WPINC')) {
    die;
}

// constants
define('SENTENCE_SLIDER_VERSION', '0.0.3');
define('SENTENCE_SLIDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SENTENCE_SLIDER_PLUGIN_URL', plugin_dir_url(__FILE__));


function sentence_slider_load_textdomain() {
    load_plugin_textdomain(
        'sentence-slider',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('plugins_loaded', 'sentence_slider_load_textdomain');


function sentence_slider_admin_scripts($hook) {
    // Only load on our admin page
    if ($hook != 'toplevel_page_sentence-slider') {
        return;
    }

    wp_enqueue_script(
        'sentence-slider-admin',
        SENTENCE_SLIDER_PLUGIN_URL . 'js/admin.js',
        array('jquery'),
        SENTENCE_SLIDER_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'sentence_slider_admin_scripts');

function sentence_slider_frontend_scripts() {
    wp_enqueue_script(
        'sentence-slider-frontend',
        SENTENCE_SLIDER_PLUGIN_URL . 'js/frontend.js',
        array('jquery'),
        SENTENCE_SLIDER_VERSION,
        true
    );

    wp_enqueue_style(
        'sentence-slider-styles',
        SENTENCE_SLIDER_PLUGIN_URL . 'css/styles.css',
        array(),
        SENTENCE_SLIDER_VERSION
    );
}
add_action('wp_enqueue_scripts', 'sentence_slider_frontend_scripts');


// Register settings and create admin page
function sentence_slider_menu() {
    add_menu_page(
        'Sentence Slider',
        'Sentence Slider',
        'manage_options',
        'sentence-slider',
        'sentence_slider_page',
        'dashicons-slides'
    );
}
add_action('admin_menu', 'sentence_slider_menu');

function sentence_slider_settings_init() {
    register_setting('sentence_slider', 'sentence_slider_settings');
    
    add_settings_section(
        'sentence_slider_section',
        esc_html__('Slider Sentences', 'sentence-slider'),
        'sentence_slider_section_callback',
        'sentence-slider'
    );
    
    add_settings_field(
        'sentence_slider_sentences',
        esc_html__('Sentences', 'sentence-slider'),
        'sentence_slider_field_callback',
        'sentence-slider',
        'sentence_slider_section'
    );

    add_settings_field(
        'sentence_slider_animation',
        esc_html__('Animation Style', 'sentence-slider'),
        'sentence_slider_animation_callback',
        'sentence-slider',
        'sentence_slider_section'
    );

    // Add timing field
    add_settings_field(
        'sentence_slider_timing',
        esc_html__('Slide Duration (seconds)', 'sentence-slider'),
        'sentence_slider_timing_callback',
        'sentence-slider',
        'sentence_slider_section'
    );
}
add_action('admin_init', 'sentence_slider_settings_init');


function sentence_slider_section_callback() {
    echo '<p>' . esc_html__('Add sentences to display in the slider. Each sentence will be shown one at a time. You can also choose which ones to show and set the overall animation duration. Display in posts/pages with the shortcode [sentence_slider].', 'sentence-slider') . '</p>';
}


function sentence_slider_field_callback() {
    $options = get_option('sentence_slider_settings', array());
    $sentences = isset($options['sentences']) ? $options['sentences'] : array('');
    $active_slides = isset($options['active_slides']) ? $options['active_slides'] : array();
    ?>
    <div class="wrap" style="max-width: 800px;">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="wp-list-table-th"><?php esc_html_e('Sentence', 'sentence-slider'); ?></th>
                    <th class="wp-list-table-th" style="width: 20%"><?php esc_html_e('Status', 'sentence-slider'); ?></th>
                    <th class="wp-list-table-th" style="width: 20%"><?php esc_html_e('Actions', 'sentence-slider'); ?></th>
                </tr>
            </thead>
            <tbody id="sentence-fields">
                <?php foreach ($sentences as $index => $sentence) : ?>
                    <tr class="sentence-field">
                        <td>
                            <input type="text" 
                                   name="sentence_slider_settings[sentences][]" 
                                   value="<?php echo esc_attr($sentence); ?>" 
                                   class="widefat"
                                   placeholder="<?php esc_attr_e('Enter a sentence', 'sentence-slider'); ?>">
                        </td>
                        <td>
                            <label class="sentence-active-toggle">
                                <input type="checkbox" 
                                       name="sentence_slider_settings[active_slides][]" 
                                       value="<?php echo $index; ?>"
                                       <?php checked(in_array($index, $active_slides) || empty($active_slides)); ?>>
                                <?php esc_html_e('Active', 'sentence-slider'); ?>
                            </label>
                        </td>
                        <td>
                            <button type="button" class="button remove-sentence" <?php echo $index === 0 ? 'style="display:none;"' : ''; ?>>
                                <?php esc_html_e('Remove', 'sentence-slider'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="submit">
            <button type="button" class="button button-secondary" id="add-sentence">
                <?php esc_html_e('Add Another Sentence', 'sentence-slider'); ?>
            </button>
        </p>
    </div>
    <?php
}

function sentence_slider_timing_callback() {
    $options = get_option('sentence_slider_settings', array());
    $timing = isset($options['slide_timing']) ? intval($options['slide_timing']) : 5;
    ?>
    <input type="number" 
           name="sentence_slider_settings[slide_timing]" 
           value="<?php echo esc_attr($timing); ?>" 
           min="1" 
           max="20" 
           step="1" 
    />
    <?php
}


// Add the animation style selector callback
function sentence_slider_animation_callback() {
    $options = get_option('sentence_slider_settings', array());
    $current_animation = isset($options['animation_style']) ? $options['animation_style'] : 'fade';
    
    $animations = array(
        'fade' => esc_html__('Fade', 'sentence-slider'),
        'slide-right' => esc_html__('Slide from Right', 'sentence-slider'),
        'slide-bottom' => esc_html__('Slide from Bottom', 'sentence-slider'),
        'zoom' => esc_html__('Zoom In', 'sentence-slider'),
        'spin' => esc_html__('Spin', 'sentence-slider'),
        'flip' => esc_html__('Flip', 'sentence-slider'),
        'bounce' => esc_html__('Bounce', 'sentence-slider'),
        'slide-rotate' => esc_html__('Slide and Rotate', 'sentence-slider')
    );
    
    echo '<select name="sentence_slider_settings[animation_style]">';
    foreach ($animations as $value => $label) {
        echo sprintf(
            '<option value="%s" %s>%s</option>',
            esc_attr($value),
            selected($current_animation, $value, false),
            esc_html($label)
        );
    }
    echo '</select>';
}


function sentence_slider_shortcode() {
    $options = get_option('sentence_slider_settings', array());
    $sentences = isset($options['sentences']) ? array_filter($options['sentences']) : array();
    $animation_style = isset($options['animation_style']) ? $options['animation_style'] : 'fade';
    $timing = isset($options['slide_timing']) ? intval($options['slide_timing']) : 5;
    $active_slides = isset($options['active_slides']) ? $options['active_slides'] : array();
    
    if (empty($sentences)) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="sentence-slider animation-<?php echo esc_attr($animation_style); ?>" 
         id="sentence-slider" 
         data-timing="<?php echo esc_attr($timing * 1000); ?>">
        <?php 
        foreach ($sentences as $index => $sentence) : 
            // Skip if not active
            if (!empty($active_slides) && !in_array($index, $active_slides)) {
                continue;
            }
        ?>
            <div class="slider-sentence"><?php echo esc_html($sentence); ?></div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('sentence_slider', 'sentence_slider_shortcode');


function sentence_slider_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Sentence Slider Settings', 'sentence-slider'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('sentence_slider');
            do_settings_sections('sentence-slider');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
