<?php
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/tch-db.php');

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

global $wpdb;
global $tch_tbl_serp;
global $tch_tbl_keywords;
global $date_query;

$table_name_s = $wpdb->get_blog_prefix() . $tch_tbl_serp;
$table_name_k = $wpdb->get_blog_prefix() . $tch_tbl_keywords;