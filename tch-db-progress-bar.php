<?php
ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/tch-db.php');

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

global $wpdb;
global $tch_tbl_serp;
global $tch_tbl_keywords;
global $date_query;

$post_id = $_GET['post_id'];

$table_name_s = $wpdb->get_blog_prefix() . $tch_tbl_serp;
$table_name_k = $wpdb->get_blog_prefix() . $tch_tbl_keywords;

$sql_text = $wpdb->prepare(
    //Все переписать, неверно считаются показатели!!
    "SELECT 
        (
            select count(distinct key_id) cnt
            from $table_name_s
            where place <= 3
             and place != 0
             and data <= %s
             and (key_id, data) in (
                select key_id, max(data) date
                from $table_name_s
                where data <= %s
                group by key_id
            )
        ) as top3,
        
        (
            select count(distinct key_id) cnt
            from $table_name_s
            where place <= 10
            and place != 0
            and data <= %s
            and (key_id, data) in (
                select key_id, max(data) date
                from $table_name_s
                where data <= %s
                group by key_id
            )
        ) as top10,
        
        (
            select count(distinct key_id) cnt
            from $table_name_s
            where place <= 30
            and place != 0
            and data <= %s
            and (key_id, data) in (
                select key_id, max(data) date
                from $table_name_s
                where data <= %s
                group by key_id
            )
        ) as top30,
        
        COALESCE(ROUND(
            (select sum(case 
                        when place <= 3 then 1
                        when place = 4 then 0.85
                        when place = 5 then 0.6
                        when place in (6,7)  then 0.5
                        when place in (8,9) then 0.3
                        when place = 10 then 0.2
                    end)
            from $table_name_s
            where place != 0
              and data <= %s
              and (key_id, data) in (
                select key_id, max(data) date
                from $table_name_s
                where data <= %s
                 group by key_id
                )
            ),1), 0) as visibility_serp
                    
         ,COALESCE(
            (SELECT sum(case when a.place < b.place then b.place-a.place else 0 end)
            FROM $table_name_s a
            JOIN $table_name_s b on a.key_id = b.key_id
              and a.data = (select max(t1.data) from $table_name_s t1 where t1.data <= %s)/*берем послед срез*/
              and b.data = (select max(t2.data) from $table_name_s t2 
                            where t2.data != (select max(t3.data) from $table_name_s t3 where t3.data <= %s)
                              and t2.data <= %s)/*берем предпосл срез*/
              and a.data <= %s
              and b.data <= %s
              and a.place != 0
              and b.place != 0
            JOIN $table_name_k c on c.key_id = a.key_id and c.key_id = b.key_id
            WHERE c.post_id = %d or %d = 0
            )
        , 0) as pos_improved
          
        ,COALESCE(
            (SELECT sum(case when a.place > b.place then a.place-b.place else 0 end)
            FROM $table_name_s a
            JOIN $table_name_s b on a.key_id = b.key_id
              and a.data = (select max(t1.data) from $table_name_s t1 where t1.data <= %s)/*берем послед срез*/
              and b.data = (select max(t2.data) from $table_name_s t2 
                            where t2.data != (select max(t3.data) from $table_name_s t3 where t3.data <= %s)
              and t2.data <= %s)/*берем предпосл срез*/
              and a.data <= %s
              and b.data <= %s
              and a.place != 0
              and b.place != 0
            JOIN $table_name_k c on c.key_id = a.key_id and c.key_id = b.key_id
            WHERE c.post_id = %d or %d = 0
              )
        , 0) as pos_deteriorated
          
         ,COALESCE((SELECT sum(b.place)
           FROM $table_name_s b
           WHERE b.data = (select max(t2.data) from $table_name_s t2 
                           where t2.data != (select max(t3.data) from $table_name_s t3 where t3.data <= %s)
                             and t2.data <= %s)/*берем предпосл срез*/
             and b.data <= %s
             and b.place != 0), 0) 
          as pos_available,
          
          (
            select count(distinct key_id) cnt
            from $table_name_s
            where data = %s
              and place != 0
          ) as cnt_cur_pos,
          
          
         (SELECT count(*) FROM $table_name_k i) count_all"
    , $date_query, $date_query, $date_query, $date_query, $date_query 
    , $date_query, $date_query, $date_query, $date_query, $date_query 
    , $date_query, $date_query, $date_query
    , $post_id, $post_id
    , $date_query, $date_query, $date_query, $date_query, $date_query
    , $post_id, $post_id
    , $date_query, $date_query, $date_query, $date_query
    );

$val = $wpdb->get_results($sql_text);
echo json_encode($val);