<?php 

function propertyShortcode($args)
{
extract( shortcode_atts( array(
'email' => 'adelodundamilare@yahoo.com',
'property_price'=>'',
'property_plan' => '',
'plan_name' => '',
'description' => '',	
'currency' => get_option('paypal_currency_options'), // this will be changed or hardcoded to Naira
'reference' => '',	
'return' => '',
'cancel_url' => '',
'tax' => '',
'paypal_payment' => get_option('paypal_payment_option'),
), $args));	
$output = "";
if(empty($email)){
        $output = '<div id="message"><div class="alert alert-error">'.__('Error! Please enter your PayPal email address in payment options page under Appearance menu.','property-framework').'</div></div>';
        return $output;
}
// $paypal_payment = ($paypal_payment=="live")?"https://www.paypal.com/cgi-bin/webscr":"https://www.sandbox.paypal.com/cgi-bin/webscr";
$paypal_payment = "#";
$window_target = '';
if(!empty($new_window)){
$window_target = 'target="_blank"';
}
if(empty($property_price)){
$paypal_payment='';  
$pay_now =__('Free Subscription','property-framework');
}
else{
  $pay_now = get_option('payment_form_info');
  $pay_now=!empty($pay_now)?$pay_now:__('Pay Now','property-framework');
}
$output .= '<div class="wp_paypal_button_widget_any_amt">';
$output .= '<form id="property-payment" class="paypal-submit-form sai" name="_xclick" action="'.$paypal_payment.'" method="post" '.$window_target.'>';
if(!empty($reference)){
$output .= '<div class="wp_pp_button_reference_section">';
$output .= '<label for="wp_pp_button_reference">'.$reference.'</label>';
$output .= '<br />';
$output .= '<input type="hidden" name="on0" value="Reference" />';
$output .= '<input type="text" name="os0" value="" class="wp_pp_button_reference" />';
$output .= '</div>';
}
$this_email = '';
$this_first_name = '';
$this_last_name = '';
$this_username = '';
$this_actualast_name = '';
if(is_user_logged_in()) {
global $current_user;
wp_get_current_user();
$this_email = $current_user->user_email;
$this_first_name = $current_user->user_firstname;
$this_last_name = $current_user->user_lastname;
$this_username = $current_user->display_name;
$this_actualast_name = ($this_first_name=='')?$this_username:$this_first_name; }
$unique = uniqid();
$output .= '
<div class="row">
<div class="col-md-6">
<input readonly type="text" value="'.$this_actualast_name.'" id="username" name="first_name" class="form-control" placeholder="'.__('First name (Required)','property-framework').'">
<input type="hidden" id="postname" name="postname" value="property">
</div>
<div class="col-md-6">
        <input readonly id="lastname" value="'.$this_last_name.'" type="text" name="last_name" class="form-control" placeholder="'.__('Last name','property-framework').'">
</div>
</div>
<div class="row">
<div class="col-md-6">
<input type="text" readonly value="'.$this_email.'" name="email" id="email" class="form-control" placeholder="'.__('Your email (Required)','property-framework').'">
</div>
<div class="col-md-6">
<input id="phone" type="phone" name="H_PhoneNumber" class="form-control" placeholder="'.__('Your phone','property-framework').'">
</div>
</div>
<div class="row">
<div class="col-md-6">
<textarea id="address1" name="address1" rows="3" cols="5" class="form-control" placeholder="'.__('Your Address','property-framework').'"></textarea>
</div>
<div class="col-md-6">
        <textarea id="notes" rows="3" cols="5" name ="noteToSeller" class="form-control" placeholder="'.__('Additional Notes','property-framework').'"></textarea>
</div>
<div class="col-md-12">
        <strong>Select payment method</strong>
        <p style="display: flex;align-items:center; margin-bottom:24px;">
            <span style="margin-right: 12px;display: flex;align-items:center"><input style="margin-right: 6px" type="radio" id="paymentmethod" name="paymentmethod" value="banktransfer" checked />Bank Transfer</span>
            <span style="margin-right: 12px;display: flex;align-items:center"><input style="margin-right: 6px" type="radio" id="paymentmethod" name="paymentmethod" value="onlinepayment" /> Online Payment</span>
        </p>
</div>
</div>';
$output .= '<input type="hidden" name="rm" value="2">';
$output .= '<input type="hidden" name="amount" value="'.$property_price.'">';	
$output .= '<input type="hidden" name="cmd" value="_xclick">';
$output .= '<input type="hidden" name="business" value="'.$email.'">';
$output .= '<input type="hidden" name="currency_code" value="'.$currency.'">';
$output .= '<input type="hidden" name="item_name" value="'.stripslashes($description).'">';
$output .= '<input type="hidden" name="item_number" value="'.$plan_name.'">';
$output .= '<input type="hidden" name="return" value="'.get_permalink($property_plan).'" />';
if(is_numeric($tax)){
    $output .= '<input type="hidden" name="tax" value="'.$tax.'" />';
}
if(!empty($cancel_url)){
        $output .= '<input type="hidden" name="cancel_return" value="'.$cancel_url.'" />';
}
if(!empty($country_code)){
        $output .= '<input type="hidden" name="lc" value="'.$country_code.'" />';
}
$free_plan_old = get_user_meta($current_user->ID,'free_plan_name_value',true);
if(empty($property_price)&&!empty($free_plan_old)){
global $imic_options;
  if(isset($imic_options['free_plan_scheme'])&&!empty($imic_options['free_plan_scheme'])){
      if($imic_options['free_plan_scheme']>reset($free_plan_old)){
$output .= '<input id="donate-property" type="submit" name="donate" class="btn btn-primary btn-lg" value="'.$pay_now.'">';
      }else{
    $pay_for_plan_url= imic_get_template_url('template-price-listing.php');
  if(!empty($pay_for_plan_url)){
     $pay_for_plan_url= '<a href="'.$pay_for_plan_url.'">'.__('Choose plan','property-framework').'</a>';
  }
    $output .= "<div id=\"message\"><div class=\"alert alert-success\">".__('Sorry, you have exceeded the allowed free listing limit. Please purchase a paid plan to add more listings','property-framework').$pay_for_plan_url."</div></div>"; 
} 
}}else{
    $output .= '<input id="donate-property" type="submit" name="donate" class="btn btn-primary btn-lg" value="'.$pay_now.'">';  
}

if(!empty($property_price)){
$output .= '<div id="message"></div>';
}
$output .= '</form>';
$output .= '</div>';
return $output;
} 
?>