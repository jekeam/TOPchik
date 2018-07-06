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


//Не выводит а возвращает, копия search - только без echo (заменено на return)
function search_all($v_keyword, $v_user, $v_key, $v_my_domain, $v_file, $v_current, $p){
    
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
    
    if(strlen($error_text) > 0){
        $error = 'Ошибка '. $error_text ."\n";
        $v_current .= $error."\n\n";
        if ($debag = 'on'){
            file_put_contents($v_file, $v_current);
        }
        $min = intval(date('i'));
        $min = ((60+5) - $min);
        $response = array(  'message' => date("h:i:s", time()) .': "'.$error.'"<br> Повторный запрос через '.$min.' мин.', 
                            'progress' => $p);
        echo json_encode($response);
        //exit();
        sleep(($min+5)*60);
        search_all($v_keyword, $v_user, $v_key, $v_my_domain, $v_file, $v_current, $p);
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
    return $my_position;
    
    phpQuery::unloadDocuments($html);
}


//пишем логи XML сообщений
if ($debag = 'on'){
    // Открываем файл для получения существующего содержимого
    $current = file_get_contents($file);
}

$prowp_options = get_option( 'tch_options_api' );

$user = $prowp_options['option_user'];
$p_key = $prowp_options['option_key'];
$my_domain = strtolower($prowp_options['server_name']);
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
$hour = isset($_POST['hour']) ? $_POST['hour'] : null;
$is_new_keys = isset($_GET['is_new_keys']) ? $_GET['is_new_keys'] : 0;//по умолчанию исключаем те по которым сегодня проверяли

if (isset($keyword)){
    search($keyword, $user, $p_key, $my_domain, $file ,$current);
}elseif (isset($hour)){
    getMyLimit($user, $p_key, $hour);
}else{//массовая проверка
    $с = 1;//Переменная для прогресс бара - вычисляем номер цикла
    $p = 0;//Переменная для прогресс бара - вычисляем процент
    set_time_limit(0); 
    ob_implicit_flush(true);
    ob_end_flush();
     
        $arr_kw = get_tch_all_keywords($is_new_keys);
        if (!empty($arr_kw)){
            //echo "asddas:" . count($arr_kw);
            $i = count($arr_kw);//Всего КС
            $step =  $i > 0 ? 100/$i : 0;//узнаем сколько будет 1 кс в процентах
            foreach ($arr_kw as $key => $value) {
                $cur_keyword = $value->keyword;
                $id_keyword = $value->key_id;
                //get new place in SERP
                sleep(1);
                $new_place = search_all($cur_keyword, $user, $p_key, $my_domain, $file ,$current, $p);
                if ($new_place > 0) {
                    set_db_tch_serp($id_keyword, $new_place);
                }
                
                $p = $p + $step;//Прибавим процент
                $p = round($p, 2);
                $response = array(  'message' => date("h:i:s", time()) .' выполено '. $p . '% ('.$с.'/'.$i.')<br>', 
                                    'progress' => $p);
                echo json_encode($response);
                $с++;
            }
        }
    
    sleep(1);
    $response = array(  'message' => 'Завершено', 
                        'progress' => 100);
        
    echo json_encode($response);
}