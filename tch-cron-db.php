<?php
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');
require_once $_SERVER['DOCUMENT_ROOT'].'/wp-load.php';

global $wpdb;

$table_name_s = $wpdb->get_blog_prefix() . $tch_tbl_serp;
$table_name_k = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
$table_name_c = $wpdb->get_blog_prefix() . $tch_tbl_cron;


function get_status_cron(){ 
    global $wpdb;
    global $table_name_c;
    $status = $wpdb->get_var(
        "SELECT status
        FROM $table_name_c"        
    ); 
    $res = isset($status)? $status : 'выключено';
    if (isset($_POST['send_status_cron'])) {
        echo $res;
        exit();
    }
    return $res;
}


if(isset($_POST['send_status_cron'])){
    get_status_cron();
}