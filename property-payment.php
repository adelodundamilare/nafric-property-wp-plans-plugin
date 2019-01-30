<?php 
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class propertyPaymentsListTable extends WP_List_Table {
    function __construct(){
        global $status, $page;
        parent::__construct( array(
            'singular'  => __( 'Property Payment', 'property-framework' ),     //singular name of the listed records
            'plural'    => __( 'Property Payments', 'property-framework' ),   //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
            )
        );
    }
    function extra_tablenav( $which ) {
        if ( $which == "top" ){
            $status = (isset($_GET['status']))?$_GET['status']:'select'; ?>
 <div class="alignleft actions">
 <form id="posts-filter" method="get" action="">
<input class="post_status_page" type="hidden" value="property_payments" name="page">
<input class="post_type_page" type="hidden" value="property" name="post_type">
<select name="status">
<option value="select"><?php _e('Select Status','property-framework') ?></option>
<option value="Completed" <?php echo ($status=='Completed')?'selected':''; ?>><?php _e('Received','property-framework') ?></option>
<option value="Incompleted" <?php echo ($status=='Incompleted')?'selected':''; ?>><?php _e('Not received','property-framework') ?></option>
<option value="Pending" <?php echo ($status=='Pending')?'selected':''; ?>><?php _e('Pending','property-framework') ?></option>
</select>
<input type ="text" class ="manual_transaction_id" name="manual_transaction_id"/>
<input class="button" type="submit" value="Filter" name="">
</form>
</div><div id="overlay" class="overlay-bg">
<div id="popup" class="overlay-content popup1">
<strong class="msg"></strong>
<p><u><?php _e('Update Payment Status for user','property-framework') ?></u></p>
<p>
<select id="user-payment-status">
<option value="Completed"><?php _e('Received','property-framework') ?></option>
<option value="Pending"><?php _e('Pending','property-framework') ?></option>
<option value="Incompleted"><?php _e('Not received','property-framework') ?></option>
</select>
</p>
<p>
<u><?php _e('Enter Transaction ID','property-framework');?></u>
<input readonly value="bank-transfer" type ="text" class ="manual_transaction_id" name="manual_transaction_id"/>
</p>
<p>
<button class="close-btn update-btn"><?php _e('Update','property-framework') ?></button>
<button class="close-btn"><?php _e('Close','property-framework') ?></button>
</p>
</div>
</div>
<?php
}
}
function no_items() {
    _e( 'No property payments found.','property-framework');
}
function column_default( $item, $column_name ) {
    switch( $column_name ) { 
        case 'ID':
        case 'transaction_id':
        case 'status':
        case 'property_plan_name':
        case 'user_email':
        case 'paid_by':
        case 'date':
        case 'amount':
        return $item[ $column_name ];
        default:
        return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
}
function get_columns(){
    $columns = array(
        'ID' => __( 'ID', 'property-framework' ),
        'transaction_id' => __( 'Transaction Id', 'property-framework' ),
        'status'    => __( 'Status', 'property-framework' ),
        'property_plan_name'      => __( 'Plan Name (No of Properties)', 'property-framework' ),
        'user_email'      => __( 'Email', 'property-framework' ),
        'paid_by'      => __( 'Paid By', 'property-framework' ),
        'date'      => __( 'Date', 'property-framework' ),
        'amount'      => __( 'Amount (NGN) ', 'property-framework' )
    );
    return $columns;
}
private function table_data() {
    global $wpdb;
    $post_name = 'property';
    $payments_list = array();
    $status = (isset($_GET['status']))?$_GET['status']:'Completed';
    $table_name = $wpdb->prefix . "imic_payment_transaction";
    if(isset($_GET['status'])) {
        $sql_select="select * FROM $table_name WHERE `status` = '$status'"; 
    } else {
        $sql_select="select * FROM $table_name"; 
    }
    $data = $wpdb->get_results($sql_select,OBJECT);
    if(!empty($data)){
        
        $serial = 1;            

        foreach($data as $data_t){

            $property_plan = $data_t->property_plan;
            $number_of_property=  getNumberOfPropertyByPlan($property_plan);
            if(!empty($number_of_property)){
                $number_of_property="(".$number_of_property.")";
            }

            if($data_t->user_notes == 'banktransfer'){
                $payment_row = array( 
                    'ID' => $serial,
                    'transaction_id'    => '<span id="transaction-'. $data_t->id .'">'. $data_t->transaction_id.'</span>',
                    'status'    => '<span id="status-'. $data_t->id .'">'. $data_t->status.'</span>',
                    'property_plan_name'      => $property_plan.$number_of_property,
                    'user_email'    => $data_t->user_email,
                    'paid_by'      => '<span id="'. $data_t->id .'" class="pay"><a>'.$data_t->user_name.'</a></span>',
                    'date'      => date('d-m-Y', strtotime($data_t->date)),
                    'amount'      => $data_t->amount
                ); 
            }else if($data_t->user_notes == 'onlinepayment'){
                $payment_row = array( 
                    'ID' => $serial,
                    'transaction_id'    => '<span id="transaction-'. $data_t->id .'">'. $data_t->transaction_id.'</span>',
                    'status'    => '<span id="status-'. $data_t->id .'">'. $data_t->status.'</span>',
                    'property_plan_name'      => $property_plan.$number_of_property,
                    'user_email'    => $data_t->user_email,
                    'paid_by'      => $data_t->user_name,
                    'date'      => date('d-m-Y', strtotime($data_t->date)),
                    'amount'      => $data_t->amount
                ); 
            }

            
            array_push($payments_list, $payment_row);	
            $serial++; 
        }
    }
    return 	$payments_list;
}
function prepare_items() {
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = array();
    $data = $this->table_data();
    $user = get_current_user_id();
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $per_page = get_user_meta($user, $screen_option, true);
    if ( empty ( $per_page) || $per_page < 1 ) {
        $per_page = $screen->get_option( 'per_page', 'default' );
    }
    $perPage = $per_page;
    $currentPage = $this->get_pagenum();
    $totalItems = count($data);
    $this->set_pagination_args( array(
        'total_items' => $totalItems,
        'per_page'    => $perPage
        ) );
        $this->_actions = '';
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }} ?>