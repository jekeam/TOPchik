<?php
function tch_install ()
{
   global $wpdb;
   
   global $tch_keywords_db_ver;
   global $tch_serp_db_ver;
   
   global $tch_tbl_keywords;
   global $tch_tbl_serp;
   global $tch_tbl_cron;

   $table_name1 = $wpdb->prefix . $tch_tbl_keywords;
   $table_name2 = $wpdb->prefix . $tch_tbl_serp;
   $table_name3 = $wpdb->prefix . $tch_tbl_cron;   
   
   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   
   if($wpdb->get_var("show tables like '$table_name1'") != $table_name1) 
   {
        $sql1 = "CREATE TABLE " . $table_name1 . " 
        (
            key_id bigint(20) NOT NULL,
            keyword varchar(255) NOT NULL,
            post_id bigint(20) NOT NULL,
            PRIMARY KEY (key_id)
        );";
        
        dbDelta($sql1);
        //add_option("tch_keywords_db_ver", $tch_keywords_db_ver);
        
        $sql1 = "ALTER TABLE " . $table_name1 . " ADD INDEX (post_id) ;";
        dbDelta($sql1);
   }
   
   if($wpdb->get_var("show tables like '$table_name2'") != $table_name2)
   {
        $sql2 = "CREATE TABLE " . $table_name2 . " 
        (
            key_id bigint(20) NOT NULL,
            data DATE DEFAULT '00-00-0000' NOT NULL,
            place bigint(20) NOT NULL,
            PRIMARY KEY (key_id, data)
        );";
        dbDelta($sql2);
        
        $sql2 = "ALTER TABLE " . $table_name2 . " ADD INDEX (data) ;";
        dbDelta($sql2);
        
        $sql2 = "ALTER TABLE " . $table_name2 . " ADD INDEX (place) ;";
        dbDelta($sql2);
   }

      
   if($wpdb->get_var("show tables like '$table_name3'") != $table_name3)
   {
        $sql3 = "CREATE TABLE " . $table_name3 . " 
        (            
            key_id bigint(20) NOT NULL AUTO_INCREMENT,
            date_create DATETIME DEFAULT '0000-00-00 00:00:00',
            date_start DATETIME DEFAULT '0000-00-00 00:00:00',
            date_end DATETIME DEFAULT '0000-00-00 00:00:00',
            status varchar(25),
            is_new_keys bigint(1) DEFAULT 0,
            done bigint(3),
            msg varchar(225),
            PRIMARY KEY (key_id)
        );";
        
        dbDelta($sql3);  
    }      
   
   
   //Обновление таблиц
   $installed_ver  = get_option( "tch_keywords_db_ver" );

    if($tch_serp_db_ver != $installed_ver or $tch_keywords_db_ver != $installed_ver ) 
    {
        /* $sql3 = "CREATE TABLE " . $table_name . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time bigint(11) DEFAULT '0' NOT NULL,
        name tinytext NOT NULL,
        text text NOT NULL,
        url VARCHAR(100) NOT NULL,
        UNIQUE KEY id (id)
        );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql3);
        update_option( "tch_keywords_db_ver", $cur_tch_keywords_db_ver );*/
        null;
    }
}