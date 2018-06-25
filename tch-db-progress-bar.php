<?php
//Получить последнюю позицию ключевогой фразы поста по ид-ячейки
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/tch-db.php');

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

global $wpdb;
global $tch_tbl_serp;
global $date_query;

$table_name_s = $wpdb->get_blog_prefix() . $tch_tbl_serp;
$val = $wpdb->get_results($wpdb->prepare(
    //Все переписать, неверно считаются показатели!!
                          "SELECT (SELECT count(distinct key_id) FROM $table_name_s i WHERE i.place <= 3 and i.data <= %s) top3
                                 ,(SELECT count(distinct key_id) FROM $table_name_s i WHERE i.place <= 10 and i.data <= %s) top10
                                 ,(SELECT count(distinct key_id) FROM $table_name_s i WHERE i.place <= 30 and i.data <= %s) top30
                                 ,ROUND((SELECT sum(case 
                                                when i.place <= 3 then 1
                                                when i.place = 4 then 0.85
                                                when i.place = 5 then 0.6
                                                when i.place in (6,7)  then 0.5
                                                when i.place in (8,9) then 0.3
                                                when i.place = 10 then 0.2
                                            end) FROM $table_name_s i
                                            WHERE i.data <= %s), 1) visibility_serp
                                            
                                 ,COALESCE((SELECT sum(case when a.place < b.place then b.place-a.place else 0 end)
                                   FROM $table_name_s a
                                   JOIN $table_name_s b on a.key_id = b.key_id
                                    and a.data = (select max(t1.data) from $table_name_s t1 where t1.data <= %s)/*берем послед срез*/
                                    and b.data = (select max(t2.data) from $table_name_s t2 
                                                  where t2.data != (select max(t3.data) from $table_name_s t3 where t3.data <= %s)
                                                    and t2.data <= %s)/*берем предпосл срез*/
                                    and a.data <= %s
                                    and b.data <= %s), 0) 
                                  as pos_improved
                                 ,COALESCE((SELECT sum(case when a.place > b.place then a.place-b.place else 0 end)
                                   FROM $table_name_s a
                                   JOIN $table_name_s b on a.key_id = b.key_id
                                    and a.data = (select max(t1.data) from $table_name_s t1 where t1.data <= %s)/*берем послед срез*/
                                    and b.data = (select max(t2.data) from $table_name_s t2 
                                                  where t2.data != (select max(t3.data) from $table_name_s t3 where t3.data <= %s)
                                                    and t2.data <= %s)/*берем предпосл срез*/
                                    and a.data <= %s
                                    and b.data <= %s), 0) 
                                  as pos_deteriorated
                                 ,COALESCE((SELECT sum(b.place-1)
                                   FROM $table_name_s b
                                   WHERE b.data = (select max(t2.data) from $table_name_s t2 
                                                   where t2.data != (select max(t3.data) from $table_name_s t3 where t3.data <= %s)
                                                     and t2.data <= %s)/*берем предпосл срез*/
                                     and b.data <= %s), 0) 
                                  as pos_available
                                 ,(SELECT count(distinct key_id) FROM $table_name_s i) count_all"
                          , $date_query, $date_query, $date_query, $date_query, $date_query, $date_query, $date_query
                          , $date_query, $date_query, $date_query, $date_query, $date_query, $date_query, $date_query
                          , $date_query, $date_query, $date_query
                          ));
echo json_encode($val);