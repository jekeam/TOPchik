<?php
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

$arr_data = array('cols' => array(
                                  0 => array(
                                             'label'=>'Поисковые запросы',
                                             'type' => 'string'
                                             ),
                                  1 => array(
                                             'label'=>'Блог астматика',
                                             'type' => 'number'
                                             ),                                             
                                  ),
                  'rows' => array( 0 => array(
                                             'c'=> array(
                                                          0 => array('v'=>'01.01.2017'),
                                                          1 => array('v' => '10')
                                                        )),
                                    1=> array(
                                              'c'=> array(
                                                          0 => array('v'=>'01.01.2017'),
                                                          1 => array('v' => '10')
                                                          )
                                              )
                                  )
                                  
                  );

echo json_encode($arr_data);