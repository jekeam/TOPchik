<?php
include_once( dirname( __FILE__ ) . '/src/phpQuery-onefile.php');
$my_domain = strtolower($_POST['domain']);

$html = file_get_contents('https://yandex.ru/search/xml?user='.$_POST['user']
                                .'&key='.$_POST['key']
                                .'&query='.urlencode($_POST['keyword'])
                                .'lr=225&l10n=ru&sortby=rlv&filter=strict&maxpassages=1&groupby=attr%3Dd.mode%3Ddeep.groups-on-page%3D50.docs-in-group%3D1');

$doc = phpQuery::newDocument($html);

$domains = pq($doc->find('domain'));

echo 'Ваша текущая позиция: ';
echo '<div>'.get_my_place($domains).'</div>';
//get_list_domains($domains);

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
            echo $place;
            return;
        }
        ++$place;
    }   
}

//Получаем список доменов - для дебага
function get_list_domains($domains_xml)
{
    $place = 1;
    
    foreach($domains_xml as $domain)
    {
        $cur_domain = strtolower(pq($domain)->text());
        echo $place.' '.$cur_domain.'</br>';
        ++$place;
    }   
}

phpQuery::unloadDocuments($html);