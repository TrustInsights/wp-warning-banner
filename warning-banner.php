<?php
/*
Plugin Name: Warning Banner
Description: Displays a warning banner on posts older than a specified number of days.
Version: 1.0
Author: TrustInsights.ai
*/

function warning_banner_menu() {
    add_options_page('Warning Banner Settings', 'Warning Banner', 'manage_options', 'warning-banner', 'warning_banner_options_page');
}
add_action('admin_menu', 'warning_banner_menu');

function warning_banner_enqueue_color_picker($hook_suffix) {
    // Load only on plugin settings page
    if('settings_page_warning-banner' != $hook_suffix) return;

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
}
add_action('admin_enqueue_scripts', 'warning_banner_enqueue_color_picker');


function warning_banner_settings_init() {
    // Register a new setting for our options page.
    register_setting('warning-banner', 'warning_banner_options');

    // Add a new section to our options page.
    add_settings_section(
        'warning_banner_section',
        'Warning Banner Settings',
        'warning_banner_section_cb',
        'warning-banner'
    );

    // Days field
    add_settings_field(
        'warning_banner_days',
        'Number of days',
        'warning_banner_days_cb',
        'warning-banner',
        'warning_banner_section'
    );
    
    // Warning Message field
    add_settings_field(
        'warning_banner_message',
        'Warning message',
        'warning_banner_message_cb',
        'warning-banner',
        'warning_banner_section'
    );
    
    // RGB Color field
    add_settings_field(
        'warning_banner_color',
        'Banner color (RGB or HEX)',
        'warning_banner_color_cb',
        'warning-banner',
        'warning_banner_section'
    );
}
add_action('admin_init', 'warning_banner_settings_init');

function warning_banner_section_cb() {
    echo '<p>Configure the warning banner settings.</p>';
}


function warning_banner_color_cb() {
    $options = get_option('warning_banner_options');
    echo '<input type="text" name="warning_banner_options[color]" value="' . esc_attr($options['color'] ?? '') . '" class="warning-banner-color-field">';
}

function warning_banner_message_cb() {
    $options = get_option('warning_banner_options');
    echo '<textarea name="warning_banner_options[message]" rows="3" cols="50">' . esc_textarea($options['message'] ?? 'Warning: this content is older than X days. It may be out of date and no longer relevant.') . '</textarea>';
}

function warning_banner_days_cb() {
    $options = get_option('warning_banner_options');
    echo '<input type="number" min="1" name="warning_banner_options[days]" value="' . esc_attr($options['days'] ?? '365') . '" > days';
}


function warning_banner_options_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('warning-banner');
            do_settings_sections('warning-banner');
            submit_button('Save Changes');
            ?>
        </form>
    </div>
    <?php
}

function warning_banner_display($content) {
    if (is_single() && 'post' === get_post_type()) {
        $options = get_option('warning_banner_options');
        $days = $options['days'] ?? 365;
        $message = str_replace('X', $days, $options['message']);
        $color = $options['color'];

        $post_date = get_the_date('U');
        $current_date = current_time('timestamp');

        if (($current_date - $post_date) / DAY_IN_SECONDS > $days) {
            $banner = "<div style='background-color: {$color}; padding: 10px; text-align: center; font-size: larger; font-weight: bold;'>{$message}</div>";
            $content = $banner . $content;
        }
    }

    return $content;
}
add_filter('the_content', 'warning_banner_display');

function warning_banner_admin_footer() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.warning-banner-color-field').wpColorPicker();
        });
    </script>
    <?php
}
add_action('admin_footer', 'warning_banner_admin_footer');
