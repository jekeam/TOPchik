<?php
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/tch-db.php');

$key_id      = $_POST['key_id'];
$post_id     = $_POST['post_id'];
$new_keyword = $_POST['keyword'];
$new_place   = $_POST['place'];
$update      = $_POST['update'];


//Запишем новую позицию
if (!empty ($new_place) and $update === 'place')
{
    set_db_tch_serp( $key_id, $new_place);
}
//Изменим ключевое слово
if (!empty ($new_keyword) and $update === 'keyword')
{
    set_db_tch_keywords( $key_id, $new_keyword, $post_id);
}