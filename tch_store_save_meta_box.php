<?php

if (!isset($post_id)){
    $post_id = $_POST['post_id'];
    require_once '../../../wp-load.php';
}

// проверяем, относится ли запись к нашему типу и были ли отправлены метаданные
if ( get_post_type( $post_id ) == 'post'  ) 
{
    // если установлено автосохранение, пропускаем сохранение данных
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;
    
    // сохраняем данные метаполя в произвольных полях записи
    $list_key_id = $_POST['list_key_id'];
    $arr_key_id = explode(',',$list_key_id);
    foreach ($arr_key_id as $id) 
    {
        if (!empty($_POST['tch_keyword_text_'.$id]))
        {
            //Запись КС
            set_db_tch_keywords( $id, sanitize_text_field($_POST['tch_keyword_text_'.$id]), $post_id);
            
            //Запись позиции
            if (!empty ($_POST['tch_place_text_'.$id]))
            {
                set_db_tch_serp( $id, sanitize_text_field($_POST['tch_place_text_'.$id]));
            }
            else 
            {
                set_db_tch_serp( $id, 0);
            }
        }
    }
    //Удаляем все что получили для удаления
    $list_del_key_id = $_POST['list_del_key_id'];
    $arr_del_key_id = explode(',',$list_del_key_id);
    foreach ($arr_del_key_id as $id) 
    {
        delete_tch_keyword($id);
    }
}