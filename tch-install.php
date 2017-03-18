<?php
function tch_install ()
{
   global $wpdb;
   
   global $tch_keywords_db_ver;
   global $tch_serp_db_ver;
   
   global $tch_tbl_keywords;
   global $tch_tbl_serp;

   $table_name1 = $wpdb->prefix . $tch_tbl_keywords;
   $table_name2 = $wpdb->prefix . $tch_tbl_serp;
   
   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   
   if($wpdb->get_var("show tables like '$table_name1'") != $table_name1) 
   {
        $sql1 = "CREATE TABLE " . $table_name1 . " 
        (
            key_id bigint(20) NOT NULL,
            keyword varchar(255) NOT NULL,
            post_id bigint(20) NOT NULL,
            UNIQUE KEY (key_id)
        );";
        
        dbDelta($sql1);
        //add_option("tch_keywords_db_ver", $tch_keywords_db_ver);
   }
   
   if($wpdb->get_var("show tables like '$table_name2'") != $table_name2)
   {
        $sql2 = "CREATE TABLE " . $table_name2 . " 
        (
            key_id bigint(20) NOT NULL,
            data DATE DEFAULT '00-00-0000' NOT NULL,
            place bigint(20) NOT NULL,
            UNIQUE KEY (key_id)
        );";
        
        dbDelta($sql2);
        //add_option("tch_keywords_db_ver", $tch_serp_db_ver);
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