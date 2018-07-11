<?php
//set_time_limit(0); 
ob_implicit_flush(true);
ob_end_flush();

try{
    $response = array('message' => 'go<br>' , 'progress' => '10');
    echo json_encode($response);
    
    ini_set('log_errors', 'On');
    ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');
    
    include_once( dirname( __FILE__ ) . '/src/phpQuery-onefile.php');
    require_once '../../../wp-load.php';
    
    $is_new_keys = isset($_GET['is_new_keys']) ? $_GET['is_new_keys'] : 0;//по умолчанию исключаем те по которым сегодня проверяли
    
    //Устанавливаем задание для крон
    function add_cron($is_new_keys){
        wp_schedule_single_event( time() + 1, 'tch_add_shed_hook',   $is_new_keys);
        file_put_contents(dirname( __FILE__ ) . '/log/xml.log', 'add sheduler');
    }
    add_cron($is_new_keys);
    
    $response = array('message' => 'end' , 'progress' => '100');
    echo json_encode($response);
}
catch (Exception $e) {
    $response = array(  'message' => 'Выброшено исключение: '.$e->getMessage().'<br>' , 
                        'progress' => '0');
    echo json_encode($response);
}