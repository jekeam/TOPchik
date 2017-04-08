<?php
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/src/phpQuery-onefile.php');
require_once '../../../wp-load.php';

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

$debag = 'on';
//пишем логи XML сообщений
if ($debag = 'on') 
{
    $file = dirname( __FILE__ ) . '/log/xml.log';
}
// Открываем файл для получения существующего содержимого
$current = file_get_contents($file);

$prowp_options = get_option( 'tch_options' );

$user = $prowp_options['option_user'];
$key = $prowp_options['option_key'];
$my_domain = strtolower($prowp_options['server_name']);
$keyword = $_POST['keyword'];

$current .= date('H:i:s', time() - date('Z'))."\n";
$current .= '$user:'.$user."\n";
$current .= '$key:'.$key."\n";
$current .= '$my_domain:'.$my_domain."\n";
$current .= '$keyword:'.$keyword."\n";
$my_position = 0;

$html = file_get_contents('https://yandex.ru/search/xml?user='.$user
                                .'&key='.$key
                                .'&query='.urlencode($keyword)
                                .'lr=225&l10n=ru&sortby=rlv&filter=strict&maxpassages=1&groupby=attr%3Dd.mode%3Ddeep.groups-on-page%3D100.docs-in-group%3D1');

$doc = phpQuery::newDocument($html);
//$current .= $doc;
//Проверяем есть ли ошибки
$error_text = pq($doc->find('error'))->text();
if(strlen($error_text)>0)
{
    $error = 'Ошибка '. $error_text ."\n";
    $current .= $error."\n\n";
    if ($debag = 'on') 
    {
        file_put_contents($file, $current);
    }
    echo $error;
    return;
} 
//Если все ОК работаем дальше
$domains = pq($doc->find('domain'));
$current .= '$domains:'.$domains."\n";

$my_position = get_my_place($domains);
$current .= 'Моя позиция:'.$my_position."\n\n";

// Пишем содержимое обратно в файл
if ($debag = 'on') 
{
    file_put_contents($file, $current);
}
echo $my_position;

phpQuery::unloadDocuments($html);