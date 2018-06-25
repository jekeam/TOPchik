<?php

//Получаем адрес субд
function get_hostname_db(){
    
    global $wpdb;
    
    $db_hostname = $wpdb->get_var
                            ("select @@GLOBAL.hostname");
    return $db_hostname;
}


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


//Запись данных в таблицу $wpdb->prefix . $tch_tbl_keywords - ключевые слова
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


//Запись данных в таблицу $wpdb->prefix . $tch_tbl_serp - позиция КС
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
                                                $id//, $date
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
    
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
    
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
                                                                       WHERE i.key_id = t_pos.key_id))", 
                                                $post_id
                                            )
                            );
    
    return $arr_key;
}


//Получить только  ключевые слова поста
function get_tch_keywords($post_id)
{
    global $wpdb;
    global $tch_tbl_keywords;
    global $tch_tbl_serp;
    
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
    
    $table_keywords = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
    $table_position = $wpdb->get_blog_prefix() . $tch_tbl_serp;
    
    $arr_key = $wpdb->get_results
                            (
                                $wpdb->prepare
                                            ( 
                                               "SELECT
                                                    t_key.keyword keyword,
                                                    t_key.key_id key_id
                                                FROM $table_keywords t_key
                                                WHERE t_key.post_id = %d
                                                ORDER BY t_key.key_id
                                                ", 
                                                $post_id
                                            )
                            );
    
    return $arr_key;
}


//Получить вообще все КС - для массовой проверки
function get_tch_all_keywords($new = 0)
{
    global $wpdb;
    global $tch_tbl_keywords;
    global $tch_tbl_serp;
    
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
    
    $table_keywords = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
    $table_position = $wpdb->get_blog_prefix() . $tch_tbl_serp;
    
    if ($new == 0){
        $arr_key = $wpdb->get_results
            (//Проверим только новые (исключим уже проверенные за сегодня)
               "SELECT 
                    t_key.keyword keyword,
                    t_key.key_id key_id
                FROM $table_keywords t_key
                WHERE t_key.key_id not in 
                  (SELECT x.key_id 
                   FROM $table_position x
                   WHERE x.data = DATE_FORMAT(sysdate(), '%Y-%m-%d'))"
            );
    } else {
        $arr_key = $wpdb->get_results
            (//Проверим все
                "SELECT
                    t_key.keyword keyword,
                    t_key.key_id key_id
                 FROM $table_keywords t_key"
            );
    }
    
    return $arr_key;
}


//Получить даты ключевых слов поста
function get_tch_dates($post_id=null)
{
    global $wpdb;
    global $tch_tbl_keywords;
    global $tch_tbl_serp;
    
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
    
    $table_keywords = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
    $table_position = $wpdb->get_blog_prefix() . $tch_tbl_serp;
    
    if(!is_null($post_id)){
         $arr_key = $wpdb->get_results
                            (
                                $wpdb->prepare
                                            ( 
                                               "SELECT distinct
                                                    t_pos.data as dat
                                                FROM $table_keywords t_key
                                                     JOIN
                                                     $table_position t_pos 
                                                     ON t_key.key_id = t_pos.key_id 
                                                WHERE t_key.post_id = %d
                                                ORDER BY t_pos.data
                                                ", 
                                                $post_id
                                            )
                            );
    }else{
        $arr_key = $wpdb->get_results( 
            "SELECT distinct
              t_pos.data as dat
              FROM $table_position t_pos 
              ORDER BY t_pos.data"
        );
        
    }
        
    return $arr_key;
}

//Получить последнюю позицию кс по дате
function get_tch_pos_by_date($date, $key_id=null)
{
    global $wpdb;
    global $tch_tbl_keywords;
    global $tch_tbl_serp;
    
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
    
    $table_keywords = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
    $table_position = $wpdb->get_blog_prefix() . $tch_tbl_serp;
    
    if(!is_null($key_id)){
         $arr_key = $wpdb->get_results
                            (
                                $wpdb->prepare
                                            ( 
                                               "SELECT t_pos.place as pos
                                                FROM $table_keywords t_key
                                                     JOIN
                                                     $table_position t_pos 
                                                     ON t_key.key_id = t_pos.key_id 
                                                WHERE t_key.key_id = %d
                                                  AND t_pos.data = %s
                                                LIMIT 1
                                                ",
                                                $key_id, $date
                                            )
                            );
    }else{
        $arr_key = $wpdb->get_var(
            $wpdb->prepare( 
                "SELECT round(avg(t_pos.place)) as pos
                 FROM $table_position t_pos
                 WHERE t_pos.data = %s
                 GROUP BY t_pos.data
                 LIMIT 1", $date
            )
        );
    }
    
    return $arr_key;
}

//Удалить КС по ИД
function delete_tch_keyword ($key_id)
{
    global $wpdb;
    global $tch_tbl_keywords;
    global $tch_tbl_serp;
    
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
    
    $tbl_prefix = $wpdb->get_blog_prefix();   
    
    $table_keywords = $tbl_prefix . $tch_tbl_keywords;
    $table_position = $tbl_prefix . $tch_tbl_serp;
    
    $wpdb->delete( $table_keywords, array( 'key_id' => $key_id ) );
    $wpdb->delete( $table_position, array( 'key_id' => $key_id ) );
}
