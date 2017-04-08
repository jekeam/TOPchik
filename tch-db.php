<?php

//Получения значения ид-кс для поста
function get_next_key_id($post_id)
{
    global $wpdb;
    global $tch_tbl_keywords;
    
    $table_name = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
    
    $next_key_id = $wpdb->get_var
                            (
                                $wpdb->prepare
                                            ( 
                                               "SELECT max(key_id)+1
                                                FROM $table_name
                                                WHERE post_id = %d",
                                                $post_id
                                            )
                            );
    return $next_key_id;
}

//Запись данных в таблицу $wpdb->prefix . $tch_tbl_keywords 
function set_db_tch_keywords($id, $keyword, $post_id) 
{
    global $wpdb;
    global $tch_tbl_keywords;
    
    $tbl_prefix = $wpdb->get_blog_prefix();
    
    $table_name = $tbl_prefix . $tch_tbl_keywords;
    
    $wpdb->replace
    (
        $table_name, 
    	array (
    	        'key_id' =>  $id,
    	        'keyword' => $keyword,
    	        'post_id' => $post_id
    	       ), 
    	array ('%d', '%s', '%d')
    );
}
//Запись данных в таблицу $wpdb->prefix . $tch_tbl_serp
function set_db_tch_serp($id, $place = 0, $time =  00000000)
{
    //define('SHORTINIT', true);
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
    global $wpdb;
    global $tch_tbl_serp;
    
    $tbl_prefix = $wpdb->get_blog_prefix();   
    
    $table_name = $tbl_prefix . $tch_tbl_serp;
 
    //$id = ;
    if ($time == 00000000)
    {
        $time = current_time('Ymd');
    }
             
    $wpdb->replace
    (
        $table_name, 
        array (
                'key_id' => $id,
                'data' => $time,
                'place' => $place
               ), 
        array ('%d', '%s', '%d')
    );
}

//Получить ключевое слово поста определенной ячейки
function get_tch_keyword($id)
{
    global $wpdb;
    global $tch_tbl_keywords;
    
    $table_name = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
    
    $keyword = $wpdb->get_var
                            (
                                $wpdb->prepare
                                            ( 
                                               "SELECT keyword
                                                FROM $table_name
                                                WHERE key_id = %d",
                                                $id
                                            )
                            ); 
    return $keyword;
}

//Получить последнюю позицию ключевогой фразы поста по ид-ячейки
function get_tch_place ($id, $date = '0000-00-00')
{
    global $wpdb;
    global $tch_tbl_serp;
    
    $table_name = $wpdb->get_blog_prefix() . $tch_tbl_serp;
    
    $place = $wpdb->get_var
                            (
                                $wpdb->prepare
                                            ( 
                                               "SELECT i.place
                                                FROM $table_name i
                                                WHERE i.key_id = %d
                                                  AND i.data = (SELECT o.data
                                                                FROM $table_name o
                                                                WHERE i.key_id = o.key_id
                                                                ORDER BY o.data DESC
                                                                LIMIT 1, 1)
                                                LIMIT 1",
                                                $id, $date
                                            )
                            ); 
    return $place;
}

//Получить все ключевые слова поста
function get_tch_list($post_id)
{
    global $wpdb;
    global $tch_tbl_keywords;
    global $tch_tbl_serp;
    
    $table_keywords = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
    $table_position = $wpdb->get_blog_prefix() . $tch_tbl_serp;
    
    $arr_key = $wpdb->get_results
                            (
                                $wpdb->prepare
                                            ( 
                                               "SELECT 
                                                    t_key.key_id key_id, 
                                                    t_key.keyword keyword, 
                                                    t_pos.data data, 
                                                    t_pos.place place
                                                FROM $table_keywords t_key
                                                     JOIN
                                                     $table_position t_pos 
                                                     ON (t_key.key_id = t_pos.key_id 
                                                     AND t_key.post_id = %d
                                                     AND t_pos.data = (SELECT MAX(i.DATA)
                                                                       FROM $table_position i
                                                                       WHERE i.key_id = t_pos.key_id))
                                                ", 
                                                $post_id
                                            )
                            ); 
    return $arr_key;
}

function delete_tch_keyword ($key_id)
{
    global $wpdb;
    global $tch_tbl_keywords;
    global $tch_tbl_serp;
    
    $tbl_prefix = $wpdb->get_blog_prefix();   
    
    $table_keywords = $tbl_prefix . $tch_tbl_keywords;
    $table_position = $tbl_prefix . $tch_tbl_serp;
    
    $wpdb->delete( $table_keywords, array( 'key_id' => $key_id ) );
    $wpdb->delete( $table_position, array( 'key_id' => $key_id ) );
}
