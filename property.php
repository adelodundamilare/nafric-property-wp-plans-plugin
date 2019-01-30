<?php
/*
Plugin Name: Nafric Property Plans
Version: 1.4
Author: imithemes
Author URI: http://www.imithemes.com
Description: This plugin adds paid property functionality in Real Space theme.
License: This plugin is bundled with Real Space Theme and should be use with Real Space Theme only.
Text Domain: property-framework
Domain Path: /language
*/
if ( ! defined( 'IMI_PROPERTY_BASE_FILE' ) )
    define( 'IMI_PROPERTY_BASE_FILE', __FILE__ );
if ( ! defined( 'IMI_PROPERTY_BASE_DIR' ) )
    define( 'IMI_PROPERTY_BASE_DIR', dirname( IMI_PROPERTY_BASE_FILE ) );
if ( ! defined( 'IMI_PROPERTY_PLUGIN' ) )
    define( 'IMI_PROPERTY_PLUGIN', plugin_dir_url( __FILE__ ) );
include_once('shortcode.php');
include_once('property_functions.php');
include_once('property-payment.php');
add_shortcode('imic_property','propertyDonateNow');
function propertyDonateNow($args)
{ 
$output = propertyShortcode($args);
return $output;
}
// add_action('admin_menu', 'propertyOptionPage');
function propertyOptionPage() {
global $propertyOption;
$propertyOption =	add_submenu_page( 'themes.php',__('Payment Options','property-framework'), __('Payment Options','property-framework'),'manage_options', 'property_options', 'property_options',7 );
//add_action('load-'.$propertyOption, 'propertyOptionHelpTab');
add_action( 'admin_init', 'imicRegisterSettings' );
}

function imicRegisterSettings() {
//register our settings
register_setting( 'property-options-group', 'paypal_email_address' );
register_setting( 'property-options-group', 'paypal_token_id' );
register_setting( 'property-options-group', 'paypal_currency_options' );
register_setting( 'property-options-group', 'paypal_payment_option' );
register_setting( 'property-options-group', 'property_list_id' );
register_setting( 'property-options-group', 'property_grid_id' );
register_setting( 'property-options-group', 'payment_form_info' );
register_setting( 'property-options-group', 'registration_form_info' );
}
function property_options() { ?>
<div class="wrap">
<h2><?php _e('Paid Listing Options','property-framework'); ?></h2>
<form method="post" action="options.php">
<?php settings_fields( 'property-options-group' ); ?>
<?php do_settings_sections( 'property-options-group' ); ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Paypal Email Address:','property-framework'); ?></th>
<td><input type="text" name="paypal_email_address" value="<?php echo get_option('paypal_email_address'); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Paypal Token ID','property-framework'); ?></th>
<td><input type="text" name="paypal_token_id" value="<?php echo get_option('paypal_token_id'); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Paypal Currency','property-framework'); ?></th>
<td>
<select id="paypal_currency_options" name="paypal_currency_options">
<?php 
                            _e('<option value="USD"'); echo (get_option('paypal_currency_options')=="USD")?'selected':'';  _e('>US Dollar</option>');
                            _e('<option value="AUD"'); echo (get_option('paypal_currency_options')=="AUD")?'selected':'';  _e('>Australian Dollar</option>');
                            _e('<option value="BRL"'); echo (get_option('paypal_currency_options')=="BRL")?'selected':'';  _e('>Brazilian Real</option>');
                            _e('<option value="CAD"'); echo (get_option('paypal_currency_options')=="CAD")?'selected':'';  _e('>Canadian Dollar</option>');
                            _e('<option value="CZK"'); echo (get_option('paypal_currency_options')=="CZK")?'selected':'';  _e('>Czech Koruna</option>');
                            _e('<option value="DKK"'); echo (get_option('paypal_currency_options')=="DKK")?'selected':'';  _e('>Danish Krone</option>');
                            _e('<option value="EUR"'); echo (get_option('paypal_currency_options')=="EUR")?'selected':'';  _e('>Euro</option>');
                            _e('<option value="HKD"'); echo (get_option('paypal_currency_options')=="HKD")?'selected':'';  _e('>Hong Kong Dollar</option>');
                            _e('<option value="HUF"'); echo (get_option('paypal_currency_options')=="HUF")?'selected':'';  _e('>Hungarian Forint</option>');
                            _e('<option value="ILS"'); echo (get_option('paypal_currency_options')=="ILS")?'selected':'';  _e('>Israeli New Sheqel</option>');
                            _e('<option value="JPY"'); echo (get_option('paypal_currency_options')=="JPY")?'selected':'';  _e('>Japanese Yen</option>');
                            _e('<option value="MYR"'); echo (get_option('paypal_currency_options')=="MYR")?'selected':'';  _e('>Malaysian Ringgit</option>');
                            _e('<option value="MXN"'); echo (get_option('paypal_currency_options')=="MXN")?'selected':'';  _e('>Mexican Peso</option>');
                            _e('<option value="NOK"'); echo (get_option('paypal_currency_options')=="NOK")?'selected':'';  _e('>Norwegian Krone</option>');
                            _e('<option value="NZD"'); echo (get_option('paypal_currency_options')=="NZD")?'selected':'';  _e('>New Zealand Dollar</option>');
                            _e('<option value="PHP"'); echo (get_option('paypal_currency_options')=="PHP")?'selected':'';  _e('>Philippine Peso</option>');
                            _e('<option value="PLN"'); echo (get_option('paypal_currency_options')=="PLN")?'selected':'';  _e('>Polish Zloty</option>');
                            _e('<option value="GBP"'); echo (get_option('paypal_currency_options')=="GBP")?'selected':'';  _e('>Pound Sterling</option>'); 
                            _e('<option value="SGD"'); echo (get_option('paypal_currency_options')=="SGD")?'selected':'';  _e('>Singapore Dollar</option>');
                            _e('<option value="SEK"'); echo (get_option('paypal_currency_options')=="SEK")?'selected':'';  _e('>Swedish Krona</option>');
                            _e('<option value="CHF"'); echo (get_option('paypal_currency_options')=="CHF")?'selected':'';  _e('>Swiss Franc</option>');
                            _e('<option value="TWD"'); echo (get_option('paypal_currency_options')=="TWD")?'selected':'';  _e('>Taiwan New Dollar</option>');
                            _e('<option value="THB"'); echo (get_option('paypal_currency_options')=="THB")?'selected':'';  _e('>Thai Baht</option>');
                            _e('<option value="TRY"'); echo (get_option('paypal_currency_options')=="TRY")?'selected':'';  _e('>Turkish Lira</option>');
?>
</select>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Payment mode','property-framework'); ?></th>
<td>
<select id="paypal_payment_option" name="paypal_payment_option">
<?php 
_e('<option value="live"'); echo (get_option('paypal_payment_option')=="live")?'selected':'';  _e('>Live</option>');
_e('<option value="sandbox"'); echo (get_option('paypal_payment_option')=="sandbox")?'selected':'';  _e('>Sandbox</option>'); ?>
</select>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Payment button text:','property-framework'); ?></th>
<td><input type="text" name="payment_form_info" value="<?php echo get_option('payment_form_info'); ?>" /></td>
</tr>
</table>
<?php submit_button(); ?>
</form>
</div>
<?php } ?>