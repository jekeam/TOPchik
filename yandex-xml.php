<?php
//$html = file_get_contents('https://yandex.ru/search/xml?user=jekeam&key=03.342975233:d814aa1537550b5bacec1fd65ce41fe0&query=%D0%B1%D0%BB%D0%BE%D0%B3+%D0%B0%D1%81%D1%82%D0%BC%D0%B0%D1%82%D0%B8%D0%BA%D0%B0&lr=225&l10n=ru&sortby=rlv&filter=strict&maxpassages=1&groupby=attr%3D%22%22.mode%3Dflat.groups-on-page%3D10.docs-in-group%3D1&page=1');
//echo $html;
//urlencode
//&lr=225&l10n=ru&sortby=tm.order%3Dascending&filter=strict&groupby=attr%3D%22%22.mode%3Dflat.groups-on-page%3D10.docs-in-group%3D1
/*
/**
 * Example of yandexXml usage
//  */
// error_reporting(E_ALL | E_STRICT);
// ini_set('display_errors', 1);
// // Use your autoloader of choice. In this case composers.
// //require __DIR__ . '/../vendor/autoload.php';
// // Or without autoloader
// require __DIR__ . '/src/YandexXml/Client.php';
// require __DIR__ . '/src/YandexXml/Request.php';
// require __DIR__ . '/src/YandexXml/Response.php';
// require __DIR__ . '/src/YandexXml/Exceptions/YandexXmlException.php';
// use AntonShevchuk\YandexXml\Client;
// use AntonShevchuk\YandexXml\Exceptions\YandexXmlException;
// /** Demo values
//  */
// $user = 'jekeam';
// $key = '03.342975233:d814aa1537550b5bacec1fd65ce41fe0';
// $lr = 225; //Saint-Petersburg, Russia
// /**
//  * Proxy demo values
//  */
// $proxyHost = '';
// $proxyPort = 80;
// $proxyUser = '';
// $proxyPass = '';
// /**
//  * Start the party!
//  */

 
// try {
//     $request = Client::request($user, $key);
//     $response = $request
//         ->query($_POST['keyword'])
//         ->lr($lr)
//         ->limit(100)
//         ->proxy($proxyHost, $proxyPort, $proxyUser, $proxyPass)
//         ->send()
//     ;
//     //$total = $response->totalHuman();
//     //$pages = $response->pages();
    
//     /**
//      * Output
//      */
//     //echo "\nTotal resalts: " . $total . "\n";
//     //echo "\nPages: " . $pages . "\n";
//     //echo "\nResults: \n";
//     $place = 1;
//     //$last_domain = 'begin';
//     foreach ($response->results() as $i => $result) {        ;
//         //echo Client::highlight($result->title), "<br/>";
//         //echo $result->url, "<br/>";
//         //if ($last_domain != $result->domain)
//         {
//             $last_domain = $result->domain;
//             echo $place . ' ' . $result->domain, "<br/>";
//             $place = $place+1;
            
//         }
//         /*if (isset($result->headline)) {
//             echo $result->headline, "<br/>";
//         }
//         if ($result->passages) {
//             foreach ($result->passages as $passage) {
//                 echo Client::highlight($passage), "<br/>";
//             }
//         }*/
//     }
// } catch (YandexXmlException $e) {
//     echo "\nYandexXmlException occurred:\n";
//     echo $e->getMessage() . "\n";
// } catch (Exception $e) {
//     echo "\nAn unexpected error occurred:\n";
//     echo $e->getMessage() . "\n";
// }