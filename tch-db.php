<?php
//Запись данных в таблицу $wpdb->prefix . $tch_tbl_keywords 
function set_db_tch_keywords($id, $keyword, $post_id) 
{
    global $wpdb;
    
    global $tch_tbl_keywords;
    
    $table_name = $wpdb->get_blog_prefix() . $tch_tbl_keywords;
    
    //console_log(mysql_insert_id());
    
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
    
    $table_name = $wpdb->get_blog_prefix() . $tch_tbl_serp;
 
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