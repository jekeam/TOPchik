<?php
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/src/phpQuery-onefile.php');
require_once '../../../wp-load.php';
$debag = 'on';
$file = dirname( __FILE__ ) . '/log/xml.log';

//Получаем наше текущее место домена из xml
function get_my_place($domains_xml){
    $place = 1;
    global $my_domain;
    
    foreach($domains_xml as $domain){
        $cur_domain = strtolower(pq($domain)->text());
        
        if (strcmp($my_domain, $cur_domain)==0){
            return $place;
        }
        ++$place;
    }
    return 200;
}


//Запрос лимитов на этот час
function getMyLimit($v_user, $v_key, $hour){
    $url = 'https://yandex.ru/search/xml?action=limits-info&user='.$v_user.'&key='.$v_key;
    //print_r($url);
    $html = file_get_contents($url);
    $doc = phpQuery::newDocument($html);
    //print_r($doc);
    echo $hour;
    echo $doc;

    
    //Проверяем есть ли ошибки
    $error_text = pq($doc->find('error'))->text();
    
    if(strlen($error_text)>0){
        $error = 'Ошибка '. $error_text ."\n";
        $v_current .= $error."\n\n";
        if ($debag = 'on'){
            file_put_contents($v_file, $v_current);
        }
        echo $error;
        return;
    } 
    //Если все ОК работаем дальше
    $limits = pq($doc->find($hour));
    $v_current .= '$limits:'.$limits."\n";
    
    // Пишем содержимое обратно в файл
    if ($debag = 'on') {
        file_put_contents($v_file, $v_current);
    }
    phpQuery::unloadDocuments($html);
}


//Запрос позиции
function search($v_keyword, $v_user, $v_key, $v_my_domain, $v_file, $v_current){
    
    $v_current .= date('H:i:s', time() - date('Z'))."\n";
    $v_current .= '$user:'.$v_user."\n";
    $v_current .= '$key:'.$v_key."\n";
    $v_current .= '$my_domain:'.$v_my_domain."\n";
    $v_current .= '$keyword:'.$v_keyword."\n";
    $my_position = 0;
    
    $html = file_get_contents('https://yandex.ru/search/xml?user='.$v_user
                                    .'&key='.$v_key
                                    .'&query='.urlencode($v_keyword)
                                    .'lr=225&l10n=ru&sortby=rlv&filter=strict&maxpassages=1&groupby=attr%3Dd.mode%3Ddeep.groups-on-page%3D100.docs-in-group%3D1');
    
    $doc = phpQuery::newDocument($html);
    //$v_current .= $doc;
    //Проверяем есть ли ошибки
    $error_text = pq($doc->find('error'))->text();
    
    if(strlen($error_text)>0){
        $error = 'Ошибка '. $error_text ."\n";
        $v_current .= $error."\n\n";
        if ($debag = 'on'){
            file_put_contents($v_file, $v_current);
        }
        echo $error;
        return;
    } 
    //Если все ОК работаем дальше
    $domains = pq($doc->find('domain'));
    $v_current .= '$domains:'.$domains."\n";
    
    $my_position = get_my_place($domains);
    $v_current .= 'Моя позиция:'.$my_position."\n\n";
    
    // Пишем содержимое обратно в файл
    if ($debag = 'on') {
        file_put_contents($v_file, $v_current);
    }
    echo $my_position;
    
    phpQuery::unloadDocuments($html);
}
    
//пишем логи XML сообщений
if ($debag = 'on'){
    // Открываем файл для получения существующего содержимого
    $current = file_get_contents($file);
}

$prowp_options = get_option( 'tch_options_api' );

$user = $prowp_options['option_user'];
$key = $prowp_options['option_key'];
$my_domain = strtolower($prowp_options['server_name']);
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
$hour = isset($_POST['hour']) ? $_POST['hour'] : null;

if (isset($keyword)){
    search($keyword, $user, $key, $my_domain, $file ,$current);
}elseif (isset($hour)){
    getMyLimit($user, $key, $hour);
}else{//массовая проверка
    set_time_limit(0); 
    ob_implicit_flush(true);
    ob_end_flush();
     
    for($i = 0; $i < 10; $i++){
        //Hard work!!
        sleep(1);
        $p = ($i+1)*10; //Progress
        $response = array(  'message' => $p . '% выполено. время сервера: ' . date("h:i:s", time()) . '<br>', 
                            'progress' => $p);
        
        echo json_encode($response);
    }
    
    sleep(1);
    $response = array(  'message' => 'Завершено', 
                        'progress' => 100);
        
    echo json_encode($response);
}