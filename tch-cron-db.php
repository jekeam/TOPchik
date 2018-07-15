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
    $row = $wpdb->get_row(
        "SELECT *
         FROM $table_name_c"
        ,ARRAY_A
    );
    

    $row['date_create']  = isset($row['date_create']) ? $row['date_create'] : '0000-00-00 00:00:00';
    $row['data_start']   = isset($row['data_start'])  ? $row['data_start']  : '0000-00-00 00:00:00';    
    $row['status']       = isset($row['status'])      ? $row['status']      : 'выключено';
    $row['is_new_keys']  = isset($row['is_new_keys']) ? $row['is_new_keys'] : '0';
    $row['done']         = isset($row['done'])        ? $row['done']        : '';
    $row['msg']          = isset($row['msg'])         ? $row['msg']         : 'Заданий не назначено';

    if (isset($_POST['send_status_cron'])) {
        echo $row['status'];
        exit();
    }
    return $row;
}

//Это для вызова из JS
if(isset($_POST['send_status_cron'])){
    get_status_cron();
}