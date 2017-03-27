<?php
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/src/phpQuery-onefile.php');
require_once '../../../wp-load.php';

//пишем логи XML сообщений
$file = dirname( __FILE__ ) . '/log/xml.log';
// Открываем файл для получения существующего содержимого
$current = file_get_contents($file);

$prowp_options = get_option( 'tch_options' );

$user = $prowp_options['option_user'];
$key = $prowp_options['option_key'];
$my_domain = strtolower($prowp_options['server_name']);
$keyword = $_POST['keyword'];

$current .= "\n\n".date('H:i:s', time() - date('Z'));
$current .= "\n".'$user:'.$user;
$current .= "\n".'$key:'.$key;
$current .= "\n".'$my_domain:'.$my_domain;
$current .= "\n".'$keyword:'.$keyword;

$html = file_get_contents('https://yandex.ru/search/xml?user='.$user
                                .'&key='.$key
                                .'&query='.urlencode($keyword)
                                .'lr=225&l10n=ru&sortby=rlv&filter=strict&maxpassages=1&groupby=attr%3Dd.mode%3Ddeep.groups-on-page%3D50.docs-in-group%3D1');

$doc = phpQuery::newDocument($html);

$domains = pq($doc->find('domain'));

$current .= "\n".$domains;

$my_position = get_my_place($domains);

$current .= "\n".'Моя позиция:'.$my_position;

//TODO Обработку ошибок

//Получаем наше текущее место домена из xml
function get_my_place($domains_xml)
{
    $place = 1;
    global $my_domain;
    
    foreach($domains_xml as $domain)
    {
        $cur_domain = strtolower(pq($domain)->text());
        
        if (strcmp($my_domain, $cur_domain)==0)
        {
            return $place;
        }
        ++$place;
    }
    return 0;
}

//Получаем список доменов - для дебага
function get_list_domains($domains_xml)
{
    $place = 0;
    
    foreach($domains_xml as $domain)
    {
        $cur_domain = strtolower(pq($domain)->text());
        echo $place.' '.$cur_domain.'</br>';
        ++$place;
    }   
}
// Пишем содержимое обратно в файл
file_put_contents($file, $current);
echo $my_position;


phpQuery::unloadDocuments($html);