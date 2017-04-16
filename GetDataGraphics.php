<?php
include_once( dirname( __FILE__ ) . '/tch-db.php');

$arr_list = get_tch_list($_REQUEST['post_id'], 1);


if (!empty($arr_list)){
    //Получаем данные из массива
    echo '{"cols": [{"label":"Поисковые запросы","type":"string"},';
    foreach ($arr_list as $key => $value) {
        $id = $value->key_id;
        $cur_place = $value->place;
        $cur_keyword = $value->keyword;
        echo '{"label":"'. $cur_keyword . '","type":"number"},';
    }
    echo '],
  "rows": [
        {"c":[{"v":"01.01.2017"},{"v":"10"},{"v":9}]},
        {"c":[{"v":"01.02.2017"},{"v":"5"},{"v":8}]},
        {"c":[{"v":"01.03.2017"},{"v":"4"},{"v":7}]},
        {"c":[{"v":"01.04.2017"},{"v":"3"},{"v":1}]},
        {"c":[{"v":"01.05.2017"},{"v":"2"},{"v":1}]},
      ]
}';
}

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