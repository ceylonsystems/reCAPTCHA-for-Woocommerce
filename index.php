<?php
/*
  Plugin Name: reCAPTCHA for WooCommerce
  Description: Woocommerce Recaptcha by Ceylon Systems.
  Version: 1.0
  Author: Ceylon Systems
  Author URI: http://ceylonsystems.com
  License: GPLv2 or later
 */

include 'autoload.php';

/*
 * 
 * Register reCAPTCHA script
 * 
 */

function cs_woo_recaptcha_head() {
    if (!cs_woo_recaptcha_check_data())
        return;
    wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js');
    wp_enqueue_style('cs-woo-recaptcha', plugin_dir_url(__FILE__) . 'main.css');
}

add_action('wp_enqueue_scripts', 'cs_woo_recaptcha_head');

/*
 * 
 * Adding the reCAPTCHA to the registration form
 * 
 */

function cs_woo_recaptcha_reg_field() {
    if (!cs_woo_recaptcha_check_data())
        return;
    echo '<div class="g-recaptcha" data-sitekey="' . get_option('cs_recatcha_sitekey') . '"></div>';
}

add_action('woocommerce_register_form', 'cs_woo_recaptcha_reg_field');

/*
 * 
 * Validating the reCAPTCHA
 * 
 */

function cs_woo_recaptcha_validate_extra_register_fields($username, $email, $validation_errors) {
    $recaptcha = new \ReCaptcha\ReCaptcha(get_option('cs_recatcha_secretkey'));
    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
    if (!$resp->isSuccess()) {
        $validation_errors->add('g-recaptcha-response', __('Captcha was invalid!', 'woocommerce'));
    }
}

add_action('woocommerce_register_post', 'cs_woo_recaptcha_validate_extra_register_fields', 10, 3);

/*
 * 
 * reCAPTCHA options page
 * 
 */

function cs_woo_recatcha_options() {
    ?>
    <div class="wrap">
        <h1>reCAPTCHA options</h1>
        <form  method="post" action="options.php">
            <?php settings_fields('cs_woo_recaptcha_settings'); ?>
            <?php do_settings_sections('cs_woo_recaptcha_settings'); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label>Site key</label></th>
                        <td>
                            <input name="cs_recatcha_sitekey" class="regular-text" value='<?php echo get_option('cs_recatcha_sitekey'); ?>'>
                            <p class="description">Don't have it? <a href="https://www.google.com/recaptcha/admin">Get it here</a></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Secret key</label></th>
                        <td>
                            <input name="cs_recatcha_secretkey" class="regular-text" value='<?php echo get_option('cs_recatcha_secretkey'); ?>'>
                            <p class="description">Don't have it? <a href="https://www.google.com/recaptcha/admin">Get it here</a></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function cs_woo_recaptcha_plugin_settings() {
    register_setting('cs_woo_recaptcha_settings', 'cs_recatcha_sitekey');
    register_setting('cs_woo_recaptcha_settings', 'cs_recatcha_secretkey');
}

add_action('admin_init', 'cs_woo_recaptcha_plugin_settings');

/*
 * 
 * Check if data is present
 * 
 */

function cs_woo_recaptcha_check_data() {
    $sitekey = get_option('cs_recatcha_sitekey');
    $secretkey = get_option('cs_recatcha_secretkey');
    if (!empty($secretkey) && !empty($sitekey)) {
        return TRUE;
    }
    return FALSE;
}

/*
 * 
 * Add submenu to the options page
 * 
 */

function cs_woo_recaptcha_addmenu() {
    add_submenu_page('options-general.php', 'Woocommerce reCAPTCHA', 'Woo reCAPTCHA', 'manage_options', 'cs-woo-recaptcha', 'cs_woo_recatcha_options');
}

add_action('admin_menu', 'cs_woo_recaptcha_addmenu');
