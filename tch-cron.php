<?php
//set_time_limit(0); 
ob_implicit_flush(true);
ob_end_flush();
try{
    $response = array(  'message' => 'go<br>' , 
                        'progress' => '10');
    echo json_encode($response);
    
    ini_set('log_errors', 'On');
    ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');
    
    include_once( dirname( __FILE__ ) . '/src/phpQuery-onefile.php');
    require_once '../../../wp-load.php';
    
    
    $is_new_keys = isset($_GET['is_new_keys']) ? $_GET['is_new_keys'] : 0;//по умолчанию исключаем те по которым сегодня проверяли
    
    
    //Устанавливаем задание для крон
    function add_cron(){
        
        $parametri = array('suneg@inbox.ru', 'Тест тема', 'Тест сообщение');
        $response = array('message' => (string)wp_next_scheduled('once_get_places', $parametri ) . '<br>', 
                          'progress' => '50');
        echo json_encode($response);
     
    if (!wp_next_scheduled('once_get_places', $parametri )) wp_schedule_event( time(), 'hourly', 'once_get_places', $parametri );
            $response = array(  'message' => 'set_add' , 
                                'progress' => '100');
            echo json_encode($response);
     
        add_action( 'once_get_places', 'send_email', 10, 3 );
         
        function send_email( $to, $subject, $msg ) {
        	wp_mail( $to, $subject, $msg );
            $response = array(  'message' => 'send_email' , 
                                'progress' => '100');
            echo json_encode($response);
        }
    }
    
    add_cron();
    
             $response = array(  'message' => 'end' , 
                                'progress' => '100');
            echo json_encode($response);
}
catch (Exception $e) {
    $response = array(  'message' => 'Выброшено исключение: '.$e->getMessage().'<br>' , 
                        'progress' => '0');
    echo json_encode($response);
}