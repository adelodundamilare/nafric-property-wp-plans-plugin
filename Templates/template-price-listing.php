<?php 
/* Template Name: Price Listing */
get_header();  
global $imic_options; //Theme Global Variable
/* Page Banner HTML
=============================*/
imic_page_banner($pageID = get_the_ID()); 
$sidebar = get_post_meta(get_the_ID(),'imic_select_sidebar_from_list',false); 
$class = (empty($sidebar)||!is_active_sidebar($sidebar[0]))?12:9; ?>
  <!-- Start Content -->
<div class="main" role="main">
    <div id="content" class="content full">
<div class="container">
<div class="page">
<div class="row">
<div class="col-md-<?php echo $class; ?>">
<?php global $imic_options; 
if(isset($imic_options['plan_group'])){
$plan_group= $imic_options['plan_group']; 
}else{
$plan_group='';
} 
if(!empty($plan_group)){
$payment_url = imic_get_template_url('template-payment.php');
$count_words = array( "zero", "one" , "two", "three", "four" );
?>
<div class="pricing-table <?php echo $count_words[count($plan_group)]; ?>-cols margin-40">
<?php  foreach($plan_group as $new_plan_group){
$payment_url=esc_url(add_query_arg('payment',$new_plan_group['title'],$payment_url));
echo'<div class="pricing-column ">
<h3>'.$new_plan_group['title'].'</h3>
<div class="pricing-column-content">';
if(!empty($new_plan_group['property_price'])){
$currency_symbol = imic_get_currency_symbol(get_option('paypal_currency_options')); 
echo'<h4> <span class="dollar-sign">'.$currency_symbol.'</span> '.$new_plan_group['property_price'].' </h4>
<span class="interval">'.__('Per Month','property-framework').'</span>';
}
if(!empty($new_plan_group['property_description'])){
echo '<ul class="features">';
$plan_description = explode('.',$new_plan_group['property_description']);
foreach($plan_description as $plan_description){
if(!empty($plan_description)){
 echo'<li>'.$plan_description.'</li>';
}}
echo '</ul>';
}
echo'<a class="btn btn-primary" href="'.$payment_url.'">'.__('Sign up now!','property-framework').'</a> </div>
</div>';
} ?>
 </div>
<?php } ?>
</div>
<!-- Start Sidebar -->
<?php if(!empty($sidebar)&&is_active_sidebar($sidebar[0])) { ?>
<div class="sidebar right-sidebar col-md-3">
<?php dynamic_sidebar($sidebar[0]); ?>
</div> 
<?php } ?> 
</div> 
</div> </div>
</div>
</div>
</div>
<?php get_footer(); ?>