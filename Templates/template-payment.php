<?php
/* Template Name: Payment */
get_header();
global $current_user;
get_currentuserinfo();
 $current_user_id =$current_user->ID;
 /* Site Showcase */
imic_page_banner($pageID = get_the_ID());
$transaction_id=isset($_REQUEST['tx'])?$_REQUEST['tx']:'';
if($transaction_id!='') {
global $wpdb;
$table_name = $wpdb->prefix . "imic_payment_transaction";
$payment_array=imic_validate_payment($transaction_id);
$st = $payment_array['payment_status'];
$user_id=isset($_REQUEST['item_number'])?$_REQUEST['item_number']:'';
if(!empty($transaction_id)&&!empty($st)){
$sql_select="select transaction_id from $table_name WHERE `transaction_id` = '$transaction_id'";
$data =$wpdb->get_results($sql_select,ARRAY_A)or print mysql_error();
if(empty($data)){
$amt=isset($_REQUEST['amt'])?$_REQUEST['amt']:'';
$total_property_value = get_user_meta($current_user_id,'property_value',true);
if($st=='Completed') {
updateUserPlanValueAfterPayment($current_user_id,$user_id);
}
$sql = "UPDATE $table_name SET transaction_id='$transaction_id',status='$st' WHERE property_plan='$user_id'";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
}else{}
}
}
/* End Site Showcase */
?>
<!-- Start Content -->
<div class="main" role="main"><div id="content" class="content full"><div class="container">
<div class="page"><div class="row">
<?php 
$payment =get_query_var('payment');
if(empty($payment)&&isset($_GET['item_number'])){
$payment=$_GET['item_number'];
}
if(current_user_can( 'administrator' )||current_user_can( 'agent' )){
if(!empty($payment)||!empty($transaction_id)){
 global $imic_options; 
if(isset($imic_options['plan_group'])){
$plan_group= $imic_options['plan_group']; 
}else{
$plan_group='';
} 
$plan_price=$plan_description='';
$flag_show=0;
if(!empty($plan_group)){
$payment_option =$payment;
foreach($plan_group as $new_plan_group){
if(in_array($payment, $new_plan_group)){
$plan_price =$new_plan_group['property_price'];
$plan_description =$new_plan_group['property_description'];
$number_of_property =$new_plan_group['number_of_property'];
$property_price =$new_plan_group['property_price'];
$flag_show=1;
}
}
}
if($flag_show==1){
echo '<div class="col-md-4 col-sm-4">';
/* Page Content
======================*/
echo '<div class="alert alert-default fade in">';

if(!empty($payment)){
echo '<h4>'.__('Plan Name: ','property-framework').$payment.'</h4>';
}
if(!empty($number_of_property)){
echo '<h4>'.__('Number of property: ','property-framework').$number_of_property.'</h4>';
}
if(!empty($property_price)){
echo '<h4>'.__('Plan Price: ','property-framework').$property_price.'</h4>';
}
if(!empty($plan_description)){
echo apply_filters('the_content',$plan_description);
}
echo'</div>';
echo '</div>';
}
$subclass =($flag_show==1)?8:12;
?>
<div class="col-md-<?php echo $subclass; ?> col-sm-<?php echo $subclass; ?> login-form">
<?php echo do_shortcode('[imic_property property_id="'.get_the_ID().'" property_price ="'.$plan_price.'" plan_name="'.$payment.'" description="'.$payment_option.'"]'); ?>
</div>
<?php }
else{
echo '<div class="alert alert-danger">'.__('Sorry, but you are not authorized to access this page','property-framework').'</div>';   
}}
else{
 echo '<div class="alert alert-danger">'.__('Sorry, but you are not an agent of this website','property-framework').'</div>';   
}
?>
</div></div></div></div></div>
<?php get_footer(); ?>