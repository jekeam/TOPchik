<?php
include_once( dirname( __FILE__ ) . '/tch-db.php');

$arr_list = get_tch_keywords($_REQUEST['post_id'], 1);

if (!empty($arr_list)){
    //Получаем данные из массива
    $data = '{"cols": [{"label":"Поисковые запросы","type":"string"},';
    
    foreach ($arr_list as $key => $value) {
        $cur_keyword = $value->keyword;
        $data .= '{"label":"'. $cur_keyword . '","type":"number"},';
    }
    
$data = substr($data, 0, -1);
$data .= '],
"rows": [
    {"c":[{"v":"01.01.2017"},{"v":"10"},{"v":9},{"v":9},{"v":9},{"v":9},{"v":9}]},
    {"c":[{"v":"01.02.2017"},{"v":"5"},{"v":8},{"v":9},{"v":9},{"v":9},{"v":9}]}
  ]}';
}
/*
$data = '{
"cols": 
[
	{"label":"Поисковые запросы","type":"string"},
	{"label":"астма психоматика2","type":"number"},
	{"label":"астматический бронхит и бронхиальная астма в чем разница","type":"number"},
	{"label":"блог астматика","type":"number"},
	{"label":"бронхиальная астма симптомы и лечение у взрослых","type":"number"},
	{"label":"можно ли курить когда астма","type":"number"}
],
 "rows": 
 [
	{"c":[{"v":"01.01.2017"},{"v":"10"},{"v":9},{"v":9},{"v":9},{"v":9},{"v":9}]},
    {"c":[{"v":"01.02.2017"},{"v":"5"},{"v":8},{"v":9},{"v":9},{"v":9},{"v":9}]}
 ]
}';*/
echo $data;

//echo json_encode($arr_list);
/*
$data = '{
  "cols": [
        {"label":"Поисковые запросы","type":"string"},
        {"label":"Блог астматика","type":"number"},
        {"label":"Как вылечить астму","type":"number"}
      ],
  "rows": [
        {"c":[{"v":"01.01.2017"},{"v":"10"},{"v":9}]},
        {"c":[{"v":"01.02.2017"},{"v":"5"},{"v":8}]},
        {"c":[{"v":"01.03.2017"},{"v":"4"},{"v":7}]},
        {"c":[{"v":"01.04.2017"},{"v":"3"},{"v":1}]},
        {"c":[{"v":"01.05.2017"},{"v":"2"},{"v":1}]},
      ]
}';

echo $data;*/