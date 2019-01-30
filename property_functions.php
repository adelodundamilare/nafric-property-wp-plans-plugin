<?php
if(!function_exists('imicDonatePropertyMenuPage')){
    function imicDonatePropertyMenuPage(){
        global $propertyPayments; 
        $propertyPayments = add_submenu_page( 'edit.php?post_type=property',__('Property Payments','property-framework'), __('Property Payments','property-framework'),'manage_options', 'property_payments', 'property_payment',6 );
        add_action("load-$propertyPayments", "property_payments_screen_options");
    }
    add_action( 'admin_menu', 'imicDonatePropertyMenuPage');
}
if(!function_exists('property_payment')){
    function property_payment(){
        wp_enqueue_style('property-style',IMI_PROPERTY_PLUGIN.'css/property-style.css');
        wp_enqueue_script('property-jquery',IMI_PROPERTY_PLUGIN.'js/property-jquery.js');
        wp_localize_script('property-jquery', 'ajax', array('url' => admin_url('admin-ajax.php')));
        $propertyPaymentList = new propertyPaymentsListTable();
        echo '</pre><div class="wrap"><h2>'.__('Property Payments','property-framework').'</h2>'; 
        $propertyPaymentList->prepare_items(); 
        $propertyPaymentList->display(); 
        echo '</div>';		
    }
}

add_action( 'wp_enqueue_scripts', 'imic_property_frontend_script' );
if(!function_exists('property_payments_screen_options')){
    function property_payments_screen_options(){
        global $propertyPayments;
        $screen = get_current_screen();
        // get out of here if we are not on our settings page
        if(!is_object($screen) || $screen->id != $propertyPayments)
        return;
        $args = array(
            'label' => __('Payments per page', 'property-framework'),
            'default' => 10,
            'option' => 'payments_per_page'
        );
        add_screen_option( 'per_page', $args );
    }
}
if(!function_exists('property_payments_set_screen_option')){
    function property_payments_set_screen_option($status, $option, $value) {
        if ( 'payments_per_page' == $option ) return $value;
    }
}
add_filter('set-screen-option', 'property_payments_set_screen_option', 10, 3);
function imic_property_frontend_script() {
    wp_enqueue_script('property-front-jquery',IMI_PROPERTY_PLUGIN.'js/property.js','','',true);
	wp_localize_script('property-front-jquery', 'url_front_ajax',array('ajaxurl' => admin_url('admin-ajax.php')));
}
if(!function_exists('imic_create_transaction_table')){
    function imic_create_transaction_table() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        $db_table_name = $wpdb->prefix . 'imic_payment_transaction';
        if( $wpdb->get_var( "SHOW TABLES LIKE '$db_table_name'" ) != $db_table_name ) {
            if ( ! empty( $wpdb->charset ) )
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty( $wpdb->collate ) )
            $charset_collate .= " COLLATE $wpdb->collate";
            $sql = "CREATE TABLE " . $db_table_name . " (
                id int(11) NOT NULL AUTO_INCREMENT,
                transaction_id varchar(60) NOT NULL,
                property_plan varchar(20) NOT NULL,
                amount int(60) NOT NULL,
                status varchar(60) NOT NULL,
                user_name varchar(20) NOT NULL,
                user_lname varchar(20) NOT NULL,
                user_email varchar(60) NOT NULL,
                user_phone int(11) NOT NULL,
                user_address varchar(20) NOT NULL,
                user_notes varchar(255) NOT NULL,
                date datetime NOT NULL,
                PRIMARY KEY (id)
                ) $charset_collate;";
                dbDelta( $sql );
            }
        }
        add_action('wp_head','imic_create_transaction_table');
    }
    function imic_property_function() {
        $output = '';
        $id = $_POST['id'];
        global $wpdb;
        $table_name = $wpdb->prefix . "imic_payment_transaction";
        $sql_select="select * from ".$table_name ." WHERE id IN ($id)";
        $data =$wpdb->get_results($sql_select,OBJECT);
        $output .= '<p><u>'.__('This is the information of user.', 'property-framework').'</u></p>';
        $output .= '<p>'.__('User Transaction ID: ', 'property-framework').''.$data[0]->transaction_id.'</p>';
        $output .= '<p>'.__('User Name: ', 'property-framework').''.$data[0]->user_name.' '.$data[0]->user_lname.'</p>';
        $output .= '<p class ="plan_name" id ="'.$data[0]->property_plan.'">'.__('Plan Name: ', 'property-framework').''.$data[0]->property_plan.'</p>';
$output .= '<p class ="plan_agent_email" id ="'.$data[0]->user_email.'">'.__('User Email: ', 'property-framework').''.$data[0]->user_email.'</p>';
$output .= '<p>'.__('User Phone: ', 'property-framework').''.$data[0]->user_phone.'</p>';
$output .= '<p>'.__('User Address: ', 'property-framework').''.$data[0]->user_address.'</p>';
$output .= '<p>'.__('User Notes: ', 'property-framework').''.$data[0]->user_notes.'</p>';
echo $output;
die();
}
add_action('wp_ajax_nopriv_imic_property_function', 'imic_property_function');
add_action('wp_ajax_imic_property_function', 'imic_property_function');
function imic_property_status_function() {
    $output = '';
    $id = $_POST['id'];
    $status = $_POST['status'];
    $transaction_id = $_POST['manual_transaction_id'];
    $plan_agent_email = $_POST['plan_agent_email'];
    $plan_name = $_POST['plan_name'];
    $agent_detail = get_user_by('email',$plan_agent_email);
    global $wpdb;
    $table_name = $wpdb->prefix . "imic_payment_transaction";
    
    if(isset($_POST['manual_transaction_id'])&& ($_POST['status'] == 'Received')){
        $transaction_id = 'bank-transfer-' . current_time(' timestamp ');
        $sql1 = "UPDATE $table_name SET transaction_id='$transaction_id',status='Completed' WHERE id='$id'";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql1);
        updateUserPlanValueAfterPayment($agent_detail->ID,$plan_name);
        echo $_POST['manual_transaction_id'];
        die();
    }
    if(isset($_POST['status'])&&!empty($_POST['status'])){
        $sql2 = "UPDATE $table_name SET status='$status' WHERE id='$id'";   
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql2);
        echo $_POST['status'];
        die();
    }
}
    add_action('wp_ajax_nopriv_imic_property_status_function', 'imic_property_status_function');
    add_action('wp_ajax_imic_property_status_function', 'imic_property_status_function');
    function imic_validate_payment($tx) {
        $paypal_payment=get_option('paypal_payment_option');
        // Init cURL
        $request = curl_init();
        $paypal_payment = ($paypal_payment=="live")?"https://www.paypal.com/cgi-bin/webscr":"https://www.sandbox.paypal.com/cgi-bin/webscr";
        // Set request options
        curl_setopt_array($request, array
        (
            CURLOPT_URL => $paypal_payment,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => http_build_query(array
            (
                'cmd' => '_notify-synch',
                'tx' => $tx,
                'at' => get_option('paypal_token_id'),
            )),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER => FALSE,
            // CURLOPT_SSL_VERIFYPEER => TRUE,
            // CURLOPT_CAINFO => 'cacert.pem',
        ));
        // Execute request and get response and status code
        $response = curl_exec($request);
        $status   = curl_getinfo($request, CURLINFO_HTTP_CODE);
        // Close connection
        curl_close($request);
        // Remove SUCCESS part (7 characters long)
        $response = substr($response, 7);
        // URL decode
        $response = urldecode($response);
        // Turn into associative array
        preg_match_all('/^([^=\s]++)=(.*+)/m', $response, $m, PREG_PATTERN_ORDER);
        if(!empty($m[1])&&!empty($m[2])){
            $response = array_combine($m[1], $m[2]);
            $flagWithInvalidKey=2;
        }
        else{
            echo '<h2>'.__('Your key is not valid','property-framework').'</h2>';
            $flagWithInvalidKey=1;  
        }
        if($flagWithInvalidKey!=1){
            // Fix character encoding if different from UTF-8 (in my case)
            if(isset($response['charset']) AND strtoupper($response['charset']) !== 'UTF-8')
            {
                foreach($response as $key => &$value)
                {
                    $value = mb_convert_encoding($value, 'UTF-8', $response['charset']);
                }
                $response['charset_original'] = $response['charset'];
                $response['charset'] = 'UTF-8';
            }
            // Sort on keys for readability (handy when debugging)
            ksort($response);
        }
        return $response;
    } 
    /* GET TEMPLATE URL
    ================================================*/
    if(!function_exists('imic_get_template_url')) {
        function imic_get_template_url($TEMPLATE_NAME){
            $url;
            $pages = query_posts(array(
                'post_type' =>'page',
                'meta_key'  =>'_wp_page_template',
                'meta_value'=> $TEMPLATE_NAME
            ));
            $url = null;
            if(isset($pages[0])) {
                $url = get_page_link($pages[0]->ID);
            }
            wp_reset_query();
            return $url;
        }
    }
    if(!function_exists('imic_get_currency_symbol')){
        function imic_get_currency_symbol( $currency = '' ) {
            if ( ! $currency ) {
                $currency = imic_get_currency();
            }
            switch ( $currency ) {
                case 'AED' :
                $currency_symbol = '&#x62f;.&#x625;';
                break;
                case 'AFN' :
                $currency_symbol = '&#x60b;';
                break;
                case 'ALL' :
                $currency_symbol = 'L';
                break;
                case 'AMD' :
                $currency_symbol = 'AMD';
                break;
                case 'ANG' :
                $currency_symbol = '&fnof;';
                break;
                case 'AOA' :
                $currency_symbol = 'Kz';
                break;
                case 'AWG' :
                $currency_symbol = '&fnof;';
                break;
                case 'AZN' :
                $currency_symbol = 'AZN';
                break;
                case 'BAM' :
                $currency_symbol = 'KM';
                break;
                case 'BDT' :
                $currency_symbol = '&#2547;&nbsp;';
                break;
                case 'BGN' :
                $currency_symbol = '&#1083;&#1074;.';
                break;
                case 'BHD' :
                $currency_symbol = '.&#x62f;.&#x628;';
                break;
                case 'BIF' :
                $currency_symbol = 'Fr';
                break;
                case 'BOB' :
                $currency_symbol = 'Bs.';
                break;
                case 'BRL' :
                $currency_symbol = '&#82;&#36;';
                break;
		case 'BTC' :
        $currency_symbol = '&#3647;';
        break;
		case 'BTN' :
        $currency_symbol = 'Nu.';
        break;
		case 'BWP' :
        $currency_symbol = 'P';
        break;
		case 'BYR' :
        $currency_symbol = 'Br';
        break;
		case 'CDF' :
        $currency_symbol = 'Fr';
        break;
		case 'CHF' :
        $currency_symbol = '&#67;&#72;&#70;';
        break;
		case 'CNY' :
        $currency_symbol = '&yen;';
        break;
		case 'CRC' :
        $currency_symbol = '&#x20a1;';
        break;
		case 'CZK' :
        $currency_symbol = '&#75;&#269;';
        break;
		case 'DJF' :
        $currency_symbol = 'Fr';
        break;
		case 'DKK' :
        $currency_symbol = 'DKK';
        break;
		case 'DOP' :
        $currency_symbol = 'RD&#36;';
        break;
		case 'DZD' :
        $currency_symbol = '&#x62f;.&#x62c;';
        break;
		case 'EGP' :
        $currency_symbol = 'EGP';
        break;
		case 'ERN' :
        $currency_symbol = 'Nfk';
        break;
		case 'ETB' :
        $currency_symbol = 'Br';
        break;
		case 'FJD' :
        $currency_symbol = '&#36;';
        break;
		case 'FKP' :
        $currency_symbol = 'EGP';
			break;
            case 'GEL' :
			$currency_symbol = '&#x10da;';
			break;
		case 'GHS' :
			$currency_symbol = '&#x20b5;';
			break;
            case 'GMD' :
			$currency_symbol = 'D';
			break;
            case 'GNF' :
			$currency_symbol = 'Fr';
			break;
            case 'GTQ' :
			$currency_symbol = 'Q';
			break;
            case 'HNL' :
			$currency_symbol = 'L';
			break;
            case 'HRK' :
			$currency_symbol = 'Kn';
			break;
            case 'HTG' :
			$currency_symbol = 'G';
			break;
            case 'HUF' :
			$currency_symbol = '&#70;&#116;';
			break;
            case 'IDR' :
			$currency_symbol = 'Rp';
			break;
            case 'ILS' :
			$currency_symbol = '&#8362;';
			break;
            case 'INR' :
			$currency_symbol = '&#8377;';
			break;
            case 'IQD' :
			$currency_symbol = '&#x639;.&#x62f;';
			break;
            case 'IRR' :
			$currency_symbol = '&#xfdfc;';
			break;
            case 'IRT' :
			$currency_symbol = '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;';
			break;
            case 'ISK' :
			$currency_symbol = 'Kr.';
			break;
            case 'JOD' :
			$currency_symbol = '&#x62f;.&#x627;';
			break;
            case 'KES' :
			$currency_symbol = 'KSh';
			break;
		case 'KGS' :
        $currency_symbol = '&#x441;&#x43e;&#x43c;';
			break;
            case 'KHR' :
			$currency_symbol = '&#x17db;';
			break;
            case 'KMF' :
			$currency_symbol = 'Fr';
			break;
            case 'KPW' :
			$currency_symbol = '&#x20a9;';
			break;
            case 'KRW' :
			$currency_symbol = '&#8361;';
			break;
            case 'KWD' :
			$currency_symbol = '&#x62f;.&#x643;';
			break;
            case 'KZT' :
			$currency_symbol = 'KZT';
			break;
            case 'LAK' :
			$currency_symbol = '&#8365;';
			break;
            case 'LBP' :
			$currency_symbol = '&#x644;.&#x644;';
			break;
            case 'LKR' :
			$currency_symbol = '&#xdbb;&#xdd4;';
			break;
            case 'LSL' :
			$currency_symbol = 'L';
			break;
            case 'LYD' :
			$currency_symbol = '&#x644;.&#x62f;';
			break;
            case 'MAD' :
			$currency_symbol = '&#x62f;.&#x645;.';
			break;
            case 'MDL' :
			$currency_symbol = 'MDL';
			break;
            case 'MGA' :
			$currency_symbol = 'Ar';
			break;
            case 'MKD' :
			$currency_symbol = '&#x434;&#x435;&#x43d;';
			break;
            case 'MMK' :
			$currency_symbol = 'Ks';
			break;
            case 'MNT' :
			$currency_symbol = '&#x20ae;';
			break;
            case 'MOP' :
			$currency_symbol = 'P';
			break;
            case 'MRO' :
			$currency_symbol = 'UM';
			break;
            case 'MUR' :
			$currency_symbol = '&#x20a8;';
			break;
            case 'MVR' :
			$currency_symbol = '.&#x783;';
			break;
            case 'MWK' :
			$currency_symbol = 'MK';
			break;
            case 'MYR' :
			$currency_symbol = '&#82;&#77;';
			break;
            case 'MZN' :
			$currency_symbol = 'MT';
			break;
            case 'NGN' :
			$currency_symbol = '&#8358;';
			break;
            case 'NIO' :
			$currency_symbol = 'C&#36;';
			break;
            case 'NOK' :
			$currency_symbol = '&#107;&#114;';
			break;
            case 'NPR' :
			$currency_symbol = '&#8360;';
			break;
            case 'OMR' :
			$currency_symbol = '&#x631;.&#x639;.';
			break;
            case 'PAB' :
			$currency_symbol = 'B/.';
			break;
            case 'PEN' :
			$currency_symbol = 'S/.';
			break;
            case 'PGK' :
			$currency_symbol = 'K';
			break;
            case 'PHP' :
			$currency_symbol = '&#8369;';
			break;
            case 'PKR' :
			$currency_symbol = '&#8360;';
			break;
            case 'PLN' :
			$currency_symbol = '&#122;&#322;';
			break;
            case 'PRB' :
			$currency_symbol = '&#x440;.';
			break;
            case 'PYG' :
			$currency_symbol = '&#8370;';
			break;
            case 'QAR' :
			$currency_symbol = '&#x631;.&#x642;';
			break;
            case 'RON' :
			$currency_symbol = 'lei';
			break;
            case 'RSD' :
			$currency_symbol = '&#x434;&#x438;&#x43d;.';
			break;
		case 'RUB' :
        $currency_symbol = '&#8381;';
        break;
		case 'RWF' :
        $currency_symbol = 'Fr';
        break;
		case 'SAR' :
        $currency_symbol = '&#x631;.&#x633;';
        break;
		case 'SCR' :
        $currency_symbol = '&#x20a8;';
        break;
		case 'SDG' :
        $currency_symbol = '&#x62c;.&#x633;.';
        break;
		case 'SEK' :
        $currency_symbol = '&#107;&#114;';
        break;
		case 'SLL' :
        $currency_symbol = 'Le';
        break;
		case 'SOS' :
        $currency_symbol = 'Sh';
        break;
		case 'STD' :
        $currency_symbol = 'Db';
        break;
		case 'SYP' :
        $currency_symbol = '&#x644;.&#x633;';
        break;
		case 'SZL' :
        $currency_symbol = 'L';
        break;
		case 'THB' :
        $currency_symbol = '&#3647;';
        break;
		case 'TJS' :
        $currency_symbol = '&#x405;&#x41c;';
        break;
		case 'TMT' :
        $currency_symbol = 'm';
        break;
		case 'TND' :
        $currency_symbol = '&#x62f;.&#x62a;';
        break;
		case 'TOP' :
        $currency_symbol = 'T&#36;';
        break;
		case 'TRY' :
        $currency_symbol = '&#8378;';
			break;
            case 'TWD' :
			$currency_symbol = '&#78;&#84;&#36;';
			break;
            case 'TZS' :
			$currency_symbol = 'Sh';
			break;
            case 'UAH' :
			$currency_symbol = '&#8372;';
			break;
            case 'UGX' :
			$currency_symbol = 'UGX';
			break;
            case 'UZS' :
			$currency_symbol = 'UZS';
			break;
            case 'VEF' :
			$currency_symbol = 'Bs F';
			break;
            case 'VND' :
			$currency_symbol = '&#8363;';
			break;
            case 'VUV' :
			$currency_symbol = 'Vt';
			break;
            case 'WST' :
			$currency_symbol = 'T';
			break;
            case 'XAF' :
			$currency_symbol = 'Fr';
			break;
            case 'XOF' :
			$currency_symbol = 'Fr';
			break;
            case 'XPF' :
			$currency_symbol = 'Fr';
			break;
            case 'YER' :
			$currency_symbol = '&#xfdfc;';
			break;
            case 'ZAR' :
			$currency_symbol = '&#82;';
			break;
            case 'ZMW' :
			$currency_symbol = 'ZK';
			break;
            case 'EUR' :
			$currency_symbol = '&euro;';
			break;
            case 'ARS' :
            case 'AUD' :
            case 'BBD' :
            case 'BMD' :
            case 'BND' :
            case 'BSD' :
            case 'BZD' :
            case 'CAD' :
            case 'CLP' :
            case 'COP' :
            case 'CUC' :
            case 'CUP' :
            case 'CVE' :
            case 'GYD' :
            case 'HKD' :
            case 'JMD' :
            case 'KYD' :
            case 'LRD' :
            case 'MXN' :
            case 'NAD' :
            case 'NZD' :
            case 'SBD' :
            case 'SGD' :
            case 'SRD' :
            case 'TTD' :
            case 'USD' :
            case 'UYU' :
            case 'XCD' :
            $currency_symbol = '&#36;';
            break;
            case 'FKP' :
            case 'GBP' :
            case 'GGP' :
            case 'GIP' :
            case 'IMP' :
            case 'JEP' :
            case 'SHP' :
            case 'SSP' :
            $currency_symbol = '&pound;';
            break;
            case 'JPY' :
            case 'RMB' :
            $currency_symbol = '&yen;';
            break;
            default    : $currency_symbol = ''; break;
        }
        return $currency_symbol;
    }
}
function imic_property_grids() {
    
    // this function is called when user tries to upgrade plan
    $date = date('Y-m-d H:i:s');
    $itemnumber = $_POST['itemnumber'];
    
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $amount = $_POST['amount'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $notes = $_POST['notes']; //this should be the payment method
    global $wpdb;
    $table_name = $wpdb->prefix . "imic_payment_transaction";
    $sql = "INSERT INTO $table_name (transaction_id,property_plan,amount,status,user_name,user_lname,user_email,user_phone,user_address,user_notes,date) VALUES ('','$itemnumber', '$amount','pending','$name','$lastname','$email', '$phone','$address','$notes','$date')";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    if(empty($amount)){
        $agent_detail = get_user_by('email',$email);
        updateUserPlanValueAfterPayment($agent_detail->ID,$itemnumber);   
        $free_plan_old = get_user_meta($agent_detail->ID,'free_plan_name_value',true);
        $free_plan_array =array(
            $itemnumber=>reset($free_plan_old)+1,
        );
        update_user_meta($agent_detail->ID,'free_plan_name_value', $free_plan_array);
    }
    die();
}
add_action('wp_ajax_nopriv_imic_property_grids', 'imic_property_grids');
add_action('wp_ajax_imic_property_grids', 'imic_property_grids');
function getNumberOfPropertyByPlan($property_plan){
    global $imic_options; 
    if(isset($imic_options['plan_group'])){
        $plan_group= $imic_options['plan_group']; 
    }else{
        $plan_group='';
    } 
    $number_of_property='';
    if(!empty($plan_group)&&!empty($property_plan)){
        foreach($plan_group as $new_plan_group){
            if(in_array($property_plan, $new_plan_group)){
                $number_of_property=$new_plan_group['number_of_property'];
            }
        }
    }
    return $number_of_property;
}
if(!function_exists('updateUserPlanValueAfterPayment')){
    function updateUserPlanValueAfterPayment($current_user_id,$plan){
        $NumberOfProperty =getNumberOfPropertyByPlan($plan);
        $total_property_old = get_user_meta($current_user_id,'property_value',true);
        $total_property =$total_property_old+$NumberOfProperty;
        update_user_meta($current_user_id,'property_value',$total_property);
    }}
    /* ADD QUERY ARGUMENTS
    =========================================================*/
    if(!function_exists('setQueryVarsFilter')) {
        function setQueryVarsFilter( $vars ){
            $vars[] = "payment";
            return $vars;
        }
        add_filter( 'query_vars', 'setQueryVarsFilter' );
    }
    ?>
    