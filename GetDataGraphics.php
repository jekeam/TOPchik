<?php
include_once( dirname( __FILE__ ) . '/tch-db.php');

global $date_query;

//Позиции по посту
if (!empty($_REQUEST['post_id'])){
    $arr_kw = get_tch_keywords($_REQUEST['post_id']);
    //Получаем данные из массива
    $data = '{"cols": [{"label":"","type":"date"},';
    
    foreach ($arr_kw as $key => $value) {
        $cur_keyword = $value->keyword;
        $data .= '{"label":"'. $cur_keyword . '","type":"number"},';
    }

    $data .= '],
    "rows": [';
        
    //Даты
    $arr_dates = get_tch_dates($_REQUEST['post_id']);    
    foreach ($arr_dates as $key => $value) {
        $cur_dat = $value->dat;
        $date = new DateTime($cur_dat);

        $data .= '{"c":[{"v":"Date('. $date->Format('Y') .','. ((int) date_format($date, 'm') - 1) .','. $date->Format('d')  .')"},';
        
        foreach ($arr_kw as $key => $value) {
            $cur_key_id = $value->key_id;
            
            $pos_arr = get_tch_pos_by_date($cur_dat, $cur_key_id);
            if (!empty($pos_arr)){
                foreach ($pos_arr as $key => $value) {
                	$data .= '{"v":"'. $value->pos .'"},';
                }
            }else{
                $data .= '{"v":"null"},';
            }
        }
        
        $data .= ']},';
    }
    $data .= ']}';
}elseif(!empty($_REQUEST['graphic']) && $_REQUEST['graphic']=='chart_dynamic_div'){
    //Общая динамика изменения параметров
    $data = '{"cols": [{"label":"","type":"date"}
                      ,{"label":"Видимость сайта","type":"number"}
                      ,{"label":"Запросов в топ 3","type":"number"}
                      ,{"label":"Запросов в топ 10","type":"number"}
                      ,{"label":"Запросов в топ 30","type":"number"}';
    
    $data .= '],
    "rows": [';
        
    //Даты
    $arr_dates = get_tch_dates();    
    foreach ($arr_dates as $key => $value) {
        file_put_contents(dirname( __FILE__ ) . '/log/php_errors.log', '<pre>' . print_r( $value->dat, true ) . '</pre>', FILE_APPEND);
        $cur_dat = $value->dat;
        $date = new DateTime($cur_dat);
        $date_query = $date->Format('Y') .'-'.  $date->Format('m') .'-'. $date->Format('d') ;
    
        $data .= '{"c":[{"v":"Date('. $date->Format('Y') .','. ((int) date_format($date, 'm') - 1) .','. $date->Format('d')  .')"}';

        ob_start(); 
        include('tch-db-progress-bar.php'); 
        $my_json = ob_get_clean();
        
        $result = json_decode($my_json);
        file_put_contents(dirname( __FILE__ ) . '/log/php_errors.log', '<pre>' . print_r( $result, true ) . '</pre>', FILE_APPEND);        
        
        $data .= ',{"v":"'.$result['0']->{'visibility_serp'}.'"}';
        $data .= ',{"v":"'.$result['0']->{'top3'}.'"}';
        $data .= ',{"v":"'.$result['0']->{'top10'}.'"}';
        $data .= ',{"v":"'.$result['0']->{'top30'}.'"}';        
        $data .= ']},';
        
    }
    $data .= ']}';
    
}elseif(!empty($_REQUEST['graphic']) && $_REQUEST['graphic']=='chart_position_div'){
    //Общая динамика изменения параметров
    $data = '{"cols": [{"label":"","type":"date"}                      
                      ,{"label":"Позиций улучшилось","type":"number"}
                      ,{"label":"Позиций ухудшилось","type":"number"}';
    
    $data .= '],
    "rows": [';
        
    //Даты
    $arr_dates = get_tch_dates();    
    foreach ($arr_dates as $key => $value) {
        $cur_dat = $value->dat;
        $date = new DateTime($cur_dat);
        $date_query = $date->Format('Y') .'-'.  $date->Format('m') .'-'. $date->Format('d') ;
    
        $data .= '{"c":[{"v":"Date('. $date->Format('Y') .','. ((int) date_format($date, 'm') - 1) .','. $date->Format('d')  .')"}';

        ob_start(); 
        include('tch-db-progress-bar.php'); 
        $my_json = ob_get_clean();
        
        $result = json_decode($my_json);        
        $data .= ',{"v":"'.$result['0']->{'pos_improved'}.'"}';
        $data .= ',{"v":"'.$result['0']->{'pos_deteriorated'}.'"}';
        $data .= ']},';
        
    }
    $data .= ']}';
    
}else{
    //Общие позиции по сайту
    $data = '{"cols": [{"type":"date"},
                       {"label":"Средняя позиция по сайту","type":"number"}';
    
    $data .= '],
    "rows": [';
        
    //Даты
    $arr_dates = get_tch_dates();
    foreach ($arr_dates as $key => $value) {
        $cur_dat = $value->dat;
        $date = new DateTime($cur_dat);
    
        $data .= '{"c":[{"v":"Date('. $date->Format('Y') .','. ((int) date_format($date, 'm') - 1) .','. $date->Format('d')  .')"},';
        
        $pos_arr = round(get_tch_pos_by_date($cur_dat));
        $data .= '{"v":"'. $pos_arr .'"}';
        $data .= ']},';
        
    }
    $data .= ']}';
}

echo $data;