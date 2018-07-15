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
            date_start,
            date_end,
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
    $row['date_start']   = isset($row['date_start'])  ? $row['date_start']  : '1970-01-01 00:00:00';
    $row['date_end']     = isset($row['date_end'])    ? $row['date_end']    : '';
    $row['status']       = isset($row['status'])      ? $row['status']      : 'выключено';
    $row['is_new_keys']  = isset($row['is_new_keys']) ? $row['is_new_keys'] : '0';
    $row['done']         = isset($row['done'])        ? $row['done']        : '';
    $row['msg']          = isset($row['msg'])         ? $row['msg']         : 'Заданий не назначено';

    if (isset($_POST['send_status_cron'])) {        
        echo json_encode($row);    
        exit();
    }
    return $row;
}


function insert_sheduler_cron($date_create, $date_start, $status, $is_new_keys, $done, $msg){
    global $wpdb;    
    global $tch_tbl_cron;
    $table_name_c = $wpdb->get_blog_prefix() . $tch_tbl_cron;
    
    $wpdb->insert(
        $table_name_c,
        array (
            'date_create' => $date_create,
            'date_start' => $date_start,            
            'status' => $status,
            'is_new_keys' =>  $is_new_keys,
            'done' => $done,
            'msg' => $msg
        ), 
        array ('%s', '%s', '%s', '%s', '%s', '%s')
    );    
}


function update_sheduler_cron($key_id, $date_create, $date_start, $date_end, $status, $is_new_keys, $done, $msg){
    global $wpdb;    
    global $tch_tbl_cron;
    $table_name_c = $wpdb->get_blog_prefix() . $tch_tbl_cron;

    $arr_key = $wpdb->get_results(
        $wpdb->prepare (
            "SELECT 
                date_create,        
                date_start,
                date_end,
                status,
                is_new_keys,
                done,
                msg        
            FROM $table_name_c
            where key_id = %d",
            $key_id
        )
    );

    $date_create = ($date_create != '')  ? $date_create : $arr_key[0]->date_create;
    $date_start  = ($date_start  != '')  ? $date_start  : $arr_key[0]->date_start;
    $date_end    = ($date_end    != '')  ? $date_end    : $arr_key[0]->date_end;
    $status      = ($status      != '')  ? $status      : $arr_key[0]->status;
    $is_new_keys = ($is_new_keys != '')  ? $is_new_keys : $arr_key[0]->is_new_keys;
    $done        = ($done        != '')  ? $done        : $arr_key[0]->done;
    $msg         = ($msg         != '')  ? $msg         : $arr_key[0]->msg;
    
    $wpdb->update(
        $table_name_c,
        array ( 
            'date_create' => $date_create,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'status' => $status,
            'is_new_keys' =>  $is_new_keys,
            'done' => $done,
            'msg' => $msg
        ),
        array('key_id' => $key_id ), 
        array ('%s', '%s', '%s', '%s', '%s', '%s', '%s'),
        array ('%d')
    );

}  

//Это для вызова из JS
if(isset($_POST['send_status_cron'])){
    get_status_cron();
}