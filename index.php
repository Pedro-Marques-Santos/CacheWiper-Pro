<?php

/*
Plugin Name: CacheWiper Pro
Description: A powerful plugin to clear WP Rocket cache, object cache, server cache, CDN cache, and force browser cache cleanup. Developed by <a href="https://github.com/Pedro-Marques-Santos/CacheWiper-Pro" target="_blank">Pedro Marques</a>.
Version: 1.0
Author: Pedro Marques
*/

// Function to clear WP Rocket cache
function clear_wp_rocket_cache()
{
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain(); // Clears the entire site cache
        return '✅ WP Rocket cache cleared successfully.';
    } else {
        return '❌ WP Rocket is not active or the rocket_clean_domain function does not exist.';
    }
}

// Function to clear object cache (Memcached, Redis, etc.)
function clear_object_cache()
{
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush(); // Clears object cache
        return '✅ Object cache cleared successfully.';
    } else {
        return '❌ wp_cache_flush function does not exist.';
    }
}

// Function to clear server cache (Varnish, Nginx FastCGI, etc.)
function clear_server_cache()
{
    $server_command = get_option('ltc_server_command', '');

    if (!empty($server_command)) {
        exec($server_command, $output, $return_var);

        if ($return_var === 0) {
            return '✅ Server cache cleared successfully.';
        } else {
            return '❌ Error clearing server cache. Return code: ' . $return_var;
        }
    } else {
        return '❌ No server command configured.';
    }
}

// Function to clear CDN cache (Cloudflare, StackPath, etc.)
function clear_cdn_cache()
{
    $zone_id = get_option('ltc_cdn_zone_id', '');
    $api_key = get_option('ltc_cdn_api_key', '');
    $email = get_option('ltc_cdn_email', '');

    if (!empty($zone_id) && !empty($api_key) && !empty($email)) {
        $url = "https://api.cloudflare.com/client/v4/zones/$zone_id/purge_cache";
        $args = [
            'headers' => [
                'X-Auth-Email' => $email,
                'X-Auth-Key' => $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(['purge_everything' => true]),
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return '❌ Error clearing Cloudflare cache: ' . $response->get_error_message();
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code === 200) {
                return '✅ Cloudflare cache cleared successfully.';
            } else {
                return '❌ Error clearing Cloudflare cache. Response code: ' . $response_code;
            }
        }
    } else {
        return '❌ CDN credentials not configured.';
    }
}

// Function to force browser cache cleanup
function avoid_browser_cache()
{
    if (!headers_sent()) {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        return '✅ Browser cache headers set successfully.';
    } else {
        return '❌ Browser cache headers could not be set.';
    }
}

// Main function to clear all caches
function clear_total_cache()
{
    $results = [];

    // Header
    $results[] = 'CacheWiper Pro executed.';
    $results[] = 'The following cache layers were cleared:';
    $results[] = '--------------------------------------------------';

    // Clear WP Rocket cache
    $results[] = clear_wp_rocket_cache();

    // Clear object cache
    $results[] = clear_object_cache();

    // Clear server cache
    $results[] = clear_server_cache();

    // Clear CDN cache
    $results[] = clear_cdn_cache();

    // Force browser cache cleanup
    $results[] = avoid_browser_cache();

    // Footer
    $results[] = '--------------------------------------------------';
    $results[] = '✅ Total cache cleanup completed successfully!';
    $results[] = 'Plugin developed by Pedro Marques.';
    $results[] = 'GitHub Repository: <a href="https://github.com/Pedro-Marques-Santos/CacheWiper-Pro" target="_blank">CacheWiper Pro</a>';

    return implode('<br>', $results);
}

// Create an endpoint to clear cache
add_action('init', function () {
    if (isset($_GET['clear_total_cache']) && $_GET['clear_total_cache'] === 'true') {
        // Verify security password
        $saved_password = get_option('ltc_security_password', '');
        $provided_password = isset($_GET['password']) ? sanitize_text_field($_GET['password']) : '';

        if ($provided_password === $saved_password) {
            $result = clear_total_cache();
            error_log($result);
            exit($result); // Stop execution after clearing cache
        } else {
            $error_message = '❌ Error: Incorrect security password.';
            $error_message .= '<br>Make sure to provide the correct password in the "password" parameter.';
            $error_message .= '<br><br>Example URL:';
            $error_message .= '<br><code>http://' . $_SERVER['HTTP_HOST'] . '/?clear_total_cache=true&password=CORRECT_PASSWORD</code>';

            error_log($error_message);
            exit($error_message);
        }
    }
});

// Add a settings page to the WordPress admin menu
function ltc_add_settings_page()
{
    add_menu_page(
        'CacheWiper Pro', // Page title
        'CacheWiper Pro', // Menu title
        'manage_options', // Capability
        'ltc-settings', // Menu slug
        'ltc_settings_page', // Callback function
        'dashicons-performance', // Icon
        100 // Position
    );
}
add_action('admin_menu', 'ltc_add_settings_page');

// Settings page content
function ltc_settings_page()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ltc_settings'])) {
        if (!isset($_POST['ltc_nonce']) || !wp_verify_nonce($_POST['ltc_nonce'], 'ltc_settings_nonce')) {
            echo '<div class="notice notice-error"><p>❌ Security error. Please try again.</p></div>';
        } else {
            // Save settings
            update_option('ltc_security_password', sanitize_text_field($_POST['ltc_security_password']));
            update_option('ltc_cdn_zone_id', sanitize_text_field($_POST['ltc_cdn_zone_id']));
            update_option('ltc_cdn_api_key', sanitize_text_field($_POST['ltc_cdn_api_key']));
            update_option('ltc_cdn_email', sanitize_text_field($_POST['ltc_cdn_email']));
            update_option('ltc_server_command', sanitize_text_field($_POST['ltc_server_command']));
            echo '<div class="notice notice-success"><p>✅ Settings saved successfully!</p></div>';
        }
    }

    // Retrieve saved values
    $saved_password = get_option('ltc_security_password', '');
    $cdn_zone_id = get_option('ltc_cdn_zone_id', '');
    $cdn_api_key = get_option('ltc_cdn_api_key', '');
    $cdn_email = get_option('ltc_cdn_email', '');
    $server_command = get_option('ltc_server_command', '');
?>
    <div class="wrap">
        <h1>CacheWiper Pro</h1>
        <p>This plugin allows you to clear WP Rocket cache, object cache, server cache, CDN cache, and force browser cache cleanup.</p>

        <h2>Settings</h2>
        <form method="post" action="">
            <input type="hidden" name="ltc_settings" value="1">
            <?php wp_nonce_field('ltc_settings_nonce', 'ltc_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ltc_security_password">Security Password:</label></th>
                    <td>
                        <input type="text" id="ltc_security_password" name="ltc_security_password" value="<?php echo esc_attr($saved_password); ?>" required>
                        <p class="description">Set a password to protect the cache cleanup endpoint.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ltc_cdn_zone_id">CDN Zone ID:</label></th>
                    <td>
                        <input type="text" id="ltc_cdn_zone_id" name="ltc_cdn_zone_id" value="<?php echo esc_attr($cdn_zone_id); ?>">
                        <p class="description">Enter your CDN Zone ID (e.g., Cloudflare).</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ltc_cdn_api_key">CDN API Key:</label></th>
                    <td>
                        <input type="text" id="ltc_cdn_api_key" name="ltc_cdn_api_key" value="<?php echo esc_attr($cdn_api_key); ?>">
                        <p class="description">Enter your CDN API Key.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ltc_cdn_email">CDN Email:</label></th>
                    <td>
                        <input type="text" id="ltc_cdn_email" name="ltc_cdn_email" value="<?php echo esc_attr($cdn_email); ?>">
                        <p class="description">Enter your CDN account email.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ltc_server_command">Server Command:</label></th>
                    <td>
                        <input type="text" id="ltc_server_command" name="ltc_server_command" value="<?php echo esc_attr($server_command); ?>">
                        <p class="description">Enter the command to clear server cache (e.g., Varnish).</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>

        <h2>Clear Cache Now</h2>
        <p>Click the button below to clear all cache layers:</p>
        <form method="post" action="">
            <input type="hidden" name="ltc_clear_cache" value="true">
            <?php submit_button('Clear Cache Now'); ?>
        </form>

        <?php
        if (isset($_POST['ltc_clear_cache'])) {
            $result = clear_total_cache();
            echo '<div class="notice notice-success"><p>' . $result . '</p></div>';
        }
        ?>

        <div style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px;">
            <h2>Automation</h2>
            <p>This plugin provides an endpoint for integration with cron jobs or automation tools:</p>
            <p><code>http://<?php echo $_SERVER['HTTP_HOST']; ?>/?clear_total_cache=true&password=<?php echo esc_attr($saved_password); ?></code></p>
        </div>

        <div style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px;">
            <p>Developed by <a href="https://github.com/Pedro-Marques-Santos/CacheWiper-Pro" target="_blank">Pedro Marques</a>.</p>
        </div>
    </div>
<?php
}
