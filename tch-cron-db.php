<?php
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');
require_once $_SERVER['DOCUMENT_ROOT'].'/wp-load.php';

//$table_name_s = $wpdb->get_blog_prefix() . $tch_tbl_serp;
//$table_name_k = $wpdb->get_blog_prefix() . $tch_tbl_keywords;



function get_status_cron(){ 
    global $wpdb;    
    global $tch_tbl_cron;
    $table_name_c = $wpdb->get_blog_prefix() . $tch_tbl_cron;

    $row = $wpdb->get_row(
        " SELECT 
            key_id,
            date_create,
            data_start,
            status,
            is_new_keys,
            done,
            msg
          FROM $table_name_c 
          ORDER BY key_id DESC
          LIMIT 1"
        ,ARRAY_A
    );    
    
    $row['key_id']       = isset($row['key_id'])      ? $row['key_id'] : '0';
    $row['date_create']  = isset($row['date_create']) ? $row['date_create'] : '1970-01-01 00:00:00';
    $row['data_start']   = isset($row['data_start'])  ? $row['data_start']  : '1970-01-01 00:00:00';    
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


function mod_sheduler_cron($key_id, $date_create, $data_start, $status, $is_new_keys = '0', $done = '0', $msg = ''){
    global $wpdb;    
    global $tch_tbl_cron;
    $table_name_c = $wpdb->get_blog_prefix() . $tch_tbl_cron;

    if (isset($date_create)){
        $wpdb->replace(
            $table_name_c,
            array (
                'key_id' => $key_id,
                'date_create' =>  $date_create,
                'data_start' => $data_start,
                'status' => $status,
                'is_new_keys' =>  $is_new_keys,
                'done' => $done,
                'msg' => $msg
            ), 
            array ('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
}    

//Это для вызова из JS
if(isset($_POST['send_status_cron'])){
    get_status_cron();
}