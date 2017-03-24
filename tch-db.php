<?php

//Запись данных в таблицу $wpdb->prefix . $tch_tbl_keywords 
function set_db_tch_keywords($id, $keyword, $post_id) 
{
    global $wpdb;
    global $tch_tbl_keywords;
    
    //дописываем префик вне цикла, под другому пока не понял как это сделать
    if(!isset($wpdb))
    {
        require_once '../../../wp-load.php';
        $wpdb = new wpdb('jekeam','','c9','localhost');
    }
    
    if(!isset($wpdb->get_blog_prefix))
    {
        $tbl_prefix = 'wp_';
    } else 
    {
        $tbl_prefix = $wpdb->get_blog_prefix();   
    }
    
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
    global $wpdb;
    global $tch_tbl_serp;
    
    //дописываем префик вне цикла, под другому пока не понял как это сделать
    if(!isset($wpdb))
    {
        require_once '../../../wp-load.php';
        $wpdb = new wpdb('jekeam','','c9','localhost');
    }
    
    if(!isset($wpdb->get_blog_prefix))
    {
        $tbl_prefix = 'wp_';
    } else 
    {
        $tbl_prefix = $wpdb->get_blog_prefix();   
    }
    
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
function get_tch_place ($id)
{
    global $wpdb;
    global $tch_tbl_serp;
    
    $table_name = $wpdb->get_blog_prefix() . $tch_tbl_serp;
    
    $place = $wpdb->get_var
                            (
                                $wpdb->prepare
                                            ( 
                                               "SELECT place
                                                FROM $table_name
                                                WHERE key_id = %d
                                                ORDER BY data DESC
                                                LIMIT 1",
                                                $id
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
                                                FROM $table_keywords t_key,
                                                     $table_position t_pos
                                                WHERE t_key.post_id = %d
                                                  AND t_key.key_id = t_pos.key_id
                                                  AND t_pos.data = (SELECT MAX(i.DATA)
                                                                    FROM $table_position i
                                                                    WHERE i.key_id = t_pos.key_id)
                                                ", 
                                                $post_id
                                            )
                            ); 
    return $arr_key;
}