<?php
include_once( dirname( __FILE__ ) . '/tch-db.php');

//Клюс слов
$arr_kw = get_tch_keywords($_REQUEST['post_id']);
if (!empty($arr_kw)){
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

        $data .= '{"c":[{"v":"Date('. $date->Format('Y') .','. $date->Format('m') .','. $date->Format('d')  .')"},';
        
        foreach ($arr_kw as $key => $value) {
            $cur_key_id = $value->key_id;
            
            $pos_arr = get_tch_pos_by_date($cur_key_id, $cur_dat);
            foreach ($pos_arr as $key => $value) {
            	$data .= '{"v":"'. $value->pos .'"},';
            }
        }
        
        $data .= ']},';
    }
    
    
$data .= ']}';
}
echo $data;