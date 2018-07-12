<?php
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

require_once $_SERVER['DOCUMENT_ROOT'].'/wp-load.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';

global $wpdb;
global $tch_tbl_serp;
global $tch_tbl_keywords;
global $tch_tbl_cron;
global $date_query;


$table_name_s = $wpdb->get_blog_prefix() . $tch_tbl_serp;
$table_name_k = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
$table_name_c = $wpdb->get_blog_prefix() . $tch_tbl_cron;


function get_status_cron(){ 
    global $wpdb;
    $status = $wpdb->get_var(
        "SELECT status
        FROM $table_name_c"        
    ); 
    return isset($status)? $status : 'выключен';    
}