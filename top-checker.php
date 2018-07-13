<?php
/*
Plugin Name: TopChik
Plugin URI: https://vk.com/jekeam
Description: Проверка позиций ключевых слов в поисковой выдаче Яндекса и Google, удобная аналитика.
Author: Александр Савиных
Version: Alpha
Author URI: https://vk.com/sun4eese
*/

//define('TOP_CHECKER_VERSION', '0.1');
//Создадим таблицу для ключевых слов и таблицу для свбора статистика по КС
//версии таблиц
$tch_keywords_db_ver = "0.1";
$tch_serp_db_ver = "0.1";

//суфиксы таблиц
global $tch_tbl_keywords;
global $tch_tbl_serp;
global $tch_tbl_cron;
global $date_query;

$tch_tbl_keywords = "tch_keywords";
$tch_tbl_serp = "tch_serp";
$tch_cron = "tch_cron";
$date_query = date("Y-m-d");

function getCurrentPath(){ 
    $curPageURL = "";

    if ($_SERVER["HTTPS"] != "on")
        $curPageURL .= "http://";
    else
        $curPageURL .= "https://" ;

    if ($_SERVER["SERVER_PORT"] == "80")
        $curPageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    else
        $curPageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];

    $count = strlen(basename($curPageURL));
    $path = substr($curPageURL,0, -$count);
    return $path;
}


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/tch-install.php');
include_once( dirname( __FILE__ ) . '/tch-uninstall.php');
include_once( dirname( __FILE__ ) . '/tch-db.php');
//include_once( dirname( __FILE__ ) . '/tch-cron.php');//создадим задание крон, которое будет смотреть в таблю каждую минуту и если запускать проверку если надо 
include_once( dirname( __FILE__ ) . '/tch-cron-db.php');
    
//Объявляем подменю для плагина
add_action( 'admin_menu', 'tch_create_settings_submenu' );

// подключаем функцию активации мета блока для поисковых запросов
add_action('add_meta_boxes', 'tch_store_register_meta_box', 0);

//зацепка-действие для сохранения данных метаполя, когда сохраняется запись
add_action( 'save_post','tch_store_save_meta_box' );

//Диактивация плагина
register_deactivation_hook(__FILE__, 'tch_deactivate' );

//Создаем таблицы
register_activation_hook(__FILE__,'tch_install');

//Подключаем js скрипт
function tch_action_javascript() 
{
    //include_once( dirname( __FILE__ ) . '/src/phpQuery-onefile.php');
    if( get_current_screen()->id != 'post' ) 
    {
        //wp_enqueue_script('tch-script-core', plugins_url('/js/core.js',__FILE__));
        //wp_enqueue_script('tch-script-progressBar', plugins_url('/js/progressBar.js',__FILE__));
        //wp_enqueue_script('tch-script-d3js-avg', plugins_url('/src/loader.js',__FILE__));//для гугл графиков
        //wp_enqueue_script('tch-script-graphic-avg', plugins_url('/js/graphic-avg.js',__FILE__));
        //wp_enqueue_script('tch-script-graphic-dynamics', plugins_url('/js/graphic-dynamics.js',__FILE__));
    }
    else
    {
        //wp_enqueue_script('tch-script-core', plugins_url('/js/core.js',__FILE__));
        wp_enqueue_script('tch-script-d3js', plugins_url('/src/loader.js',__FILE__));
        wp_enqueue_script('tch-script-graphics', plugins_url('/js/graphics.js',__FILE__));
    }
}
add_action('admin_enqueue_scripts', 'tch_action_javascript', 999);

//Подключаем стили
add_action( 'admin_enqueue_scripts', 'tch_stylesheet' );
function tch_stylesheet()
{
    wp_enqueue_style("style-tch", plugins_url('/css/tch-style-admin.css',__FILE__));
    wp_enqueue_style("style-tch-progressBar", plugins_url('/css/progressBar.css',__FILE__));
}

//Задаем настройки для меню плагина
function tch_create_settings_submenu() 
{
    add_options_page( 'ТопЧик', 'ТопЧик', 'manage_options', 'tch_settings_menu', 'tch_settings_page' );
    //Задаим функцию для настройки плагина
    add_action('admin_init', 'tch_register_settings' );
}

// регистрируем настройки
function tch_register_settings() 
{
    register_setting( 'tch-settings-api', 'tch_options_api','tch_sanitize_options');
    register_setting( 'tch-settings-sheduler', 'tch_options_sheduler','tch_sanitize_options');
}

//создать страницу параметров
function tch_settings_page()
{
?>
<h1>ТопЧик — съем позиций прямо из WP</h1>
<div>
 <ul class="subsubsub">
     
     <li class="all"><a href="/wp-admin/options-general.php?page=tch_settings_menu" 
      class="<?php if (!isset($_GET['tch_page'])){echo 'current';} ?>">Поисковые запросы</a></li> |
      
     <li class="all"><a href="/wp-admin/options-general.php?page=tch_settings_menu&tch_page=statistics"
      class="<?php if (isset($_GET['tch_page'])){ if  ($_GET['tch_page']=='statistics'){echo 'current';}} ?>">Статистика поисковой выдачи</a></li> |
      
     <li class="all"><a href="/wp-admin/options-general.php?page=tch_settings_menu&tch_page=sheduler" 
      class="<?php if (isset($_GET['tch_page'])){ if  ($_GET['tch_page']=='sheduler'){echo 'current';}} ?>">Расписание проверок</a></li> |
      
     <li class="all"><a href="/wp-admin/options-general.php?page=tch_settings_menu&tch_page=settings" 
      class="<?php if (isset($_GET['tch_page'])){ if  ($_GET['tch_page']=='settings'){echo 'current';}} ?>">Подключения(API)</a></li>
      
</ul>
<br>
<br>
</div>
<div class="wrap" style="background: #fff; padding: 20px; weight:10%;">
<?php 

if (!isset($_GET['tch_page'])) {
    
    wp_enqueue_script('tch-script-progressBar', plugins_url('/plugins/jquery.collapse.js',__FILE__));
    wp_enqueue_script('tch-script-search-page', plugins_url('/js/search-page.js',__FILE__));
    
    
    //Убрал пока сделаю проще без базы через JS
    /*
    echo '<style>
            .ui-autocomplete-loading {
                background: white url("/wp-content/plugins/TopChik/img/ui-anim_basic_16x16.gif") right center no-repeat;
            }
        </style>';
        
    echo '<div class="ui-widget">
            <label for="birds"><h2>Поиск:</h2></label>
            <input id="birds" style="width: 500px;">
          </div>';
          
    wp_enqueue_script('tch-script-auto-compl', plugins_url('/plugins/auto-compl.js',__FILE__));
    wp_enqueue_script("jquery");
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_style('jquery-ui-styles' );
    */          
    
    echo '<h2>Поиск:</h2><input type="text" style="width: 500px;" id="tc-search" onkeyup="searchPage(this.value)">';
    
    $get_post_prop = array(
        'numberposts'       => -1,
    );
    
    $recent_posts_array = get_posts($get_post_prop); // получаем массив постов
    foreach( $recent_posts_array as $recent_post_single ) : // для каждого поста из массива
        echo '<div data-collapse id="collapse-'.$recent_post_single->ID.'">';
        	echo '<h2><a href="' . get_edit_post_link( $recent_post_single ) . '" 
        	             target="blank_"
        	             class="page-name"
        	             data-id="'.$recent_post_single->ID.'">' . $recent_post_single->post_title . '</a></h2>';//url="'.urldecode(get_permalink($recent_post_single)).'"
        	echo '<form method="post" action="/wp-content/plugins/TopChik/tch_store_save_meta_box.php">';
        	    tch_meta_box($recent_post_single);
        	echo '</form>';
        echo '</div>';
    endforeach; // конец цикла
}elseif ($_GET['tch_page']=='statistics') {
    wp_enqueue_script('tch-script-progressBar', plugins_url('/js/progressBar.js',__FILE__));
    wp_enqueue_script('tch-script-d3js-avg', plugins_url('/src/loader.js',__FILE__));//для гугл графиков
    wp_enqueue_script('tch-script-graphic-avg', plugins_url('/js/graphic-avg.js',__FILE__));
    wp_enqueue_script('tch-script-graphic-dynamics', plugins_url('/js/graphic-dynamics.js',__FILE__));
?>
<text x="0" y="15.1875" style="cursor: default; user-select: none; -webkit-font-smoothing: antialiased; font-family: Roboto; font-size: 16px;" fill="#757575" 
      dx="0px">Ключевые показатели сайта (всего фраз: <span id="cnt_keys">0</span>, проверено за сегодня: <span id="cnt_cur_pos">0</span>)</text>
<div class="tch-bubble">
    <div title="1, 2 и 3-я позиции — коэффициент 1
4-я позиция — 0,85
5-я позиция — 0,6
6 и 7-я позиция — 0,5
8 и 9-я позиция — 0,3
10-я позиция — 0,2">
        <p class="ptitle">Видимость сайта</p>
        <div class="pie pie-1">
            <div class="clip1">
                <div class="slice1"></div>
            </div>
            <div class="clip2">
                <div class="slice2"></div>
            </div>
            <div class="status"></div>
        </div>
    </div>
    
     <div>
        <p class="ptitle">Запросов в топ 3</p>        
        <div class="pie pie-2">
            <div class="clip1">
                <div class="slice1"></div>
            </div>
            <div class="clip2">
                <div class="slice2"></div>
            </div>
            <div class="status"></div>
        </div>
    </div>        
    
    <div>
        <p class="ptitle">Запросов в топ 10</p>
        <div class="pie pie-3">
            <div class="clip1">
                <div class="slice1"></div>
            </div>
            <div class="clip2">
                <div class="slice2"></div>
            </div>
            <div class="status"></div>
        </div>
    </div>
        
    <div>
        <p class="ptitle">Запросов в топ 30</p>
        <div class="pie pie-4">
            <div class="clip1">
                <div class="slice1"></div>
            </div>
            <div class="clip2">
                <div class="slice2"></div>
            </div>
            <div class="status"></div>
        </div>
    </div>
        
    <div>
        <p class="ptitle">Позиций улучшилось</p>        
        <div class="pie pie-5">
            <div class="clip1">
                <div class="slice1 green"></div>
            </div>
            <div class="clip2">
                <div class="slice2 green"></div>
            </div>
            <div class="status"></div>
        </div>
    </div>        
        
    <div>
        <p class="ptitle">Позиций ухудшилось</p>        
        <div class="pie pie-6">
            <div class="clip1">
                <div class="slice1 red"></div>
            </div>
            <div class="clip2">
                <div class="slice2 red"></div>
            </div>
            <div class="status"></div>
        </div>
    </div>
</div>
<!--Динамика позиций-->
<div id="chart_dynamic_div"></div>
<!--Общий график позиций-->
<div id="chart_avg_div"></div>
<?php
} elseif ($_GET['tch_page']=='sheduler') {
?>
<form method="post" action="options.php">
<?php 
    wp_enqueue_script('tch-script-sheduler', plugins_url('/js/sheduler.js',__FILE__));
    
    if (is_admin()) {
        wp_enqueue_script("jquery-ui-core", array('jquery'));
        wp_enqueue_script("jquery-ui-progressbar", array('jquery','jquery-ui-progressbar'));
        
        global $wp_scripts;
        $ui = $wp_scripts->query('jquery-ui-core');
        $protocol = is_ssl() ? 'https' : 'http';
        $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
        wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
    }

    
    settings_fields( 'tch-settings-sheduler' ); 
    $prowp_options = get_option( 'tch_options_sheduler' );
    //Установка задания в крон
    
    $stat = get_status_cron();
    
    echo "<h1>Назначьте задание для проверки позиции (текущий статус проверки: ".$stat.")</h1>
        <div class='float_left'>
            <div id='progress_wrapper'>
                <div id='progressor'></div>
            </div>";
    echo '<input type="button" class="button" id="add_task_on_demand" style="margin-top: 5px;" value="Снять позиции"'. (($stat == "выключено")?"":"disabled") .'/>';
    echo "<div style='margin:5px;'><input type='checkbox' id='is_new_keys'/><span>Проверить все КС заново при повторном запуске</span></div>";
    echo "</div>
        <div class='float_left'>
            <h3>Логи</h3>
            <div id='divProgress'></div>
        </div>";
    
    
    //echo '<div class="box" style="display:flex;width: 100%;">';
?>
    <div style="display:inline-block;width: 100%;" id="progressbar"><div class="progress-label"></div></div>
    </div>

    <p><b>Выберите расписание проверок [в разработке]</b></p>
    <p><input disabled name="tch_options_sheduler[sheduler_mode]" type="radio" value="days_of_week" 
          <?php checked('days_of_week', isset($prowp_options['sheduler_mode'])?$prowp_options['sheduler_mode']:''); ?>
        >По дням недели, в
          
        <input disabled
            name="tch_options_sheduler[time_days_of_week]"
            type="number" 
            value="<?php echo esc_attr( isset($prowp_options['time_days_of_week'])?$prowp_options['time_days_of_week']:'0' ); ?>" 
            min="0" 
            max="24"> час(а,ов)</p>
            
        <ul class="sheduler">
            
            <li><input disabled type="checkbox" name="tch_options_sheduler[day1_of_week]" value="day1_of_week"
                <?php checked('day1_of_week',
                               isset($prowp_options['day1_of_week'])
                               ?
                               $prowp_options['day1_of_week']:''); ?>
                >ПН
            </li>
                
            <li><input disabled type="checkbox" name="tch_options_sheduler[day2_of_week]" value="day2_of_week"
                <?php checked('day2_of_week',isset($prowp_options['day2_of_week'])?$prowp_options['day2_of_week']:''); ?>
                >ВТ
            </li>
            
            <li><input disabled type="checkbox" name="tch_options_sheduler[day3_of_week]" value="day3_of_week"
                <?php checked('day3_of_week',isset($prowp_options['day3_of_week'])?$prowp_options['day3_of_week']:''); ?>
                >СР
            </li>
            
             <li><input disabled type="checkbox" name="tch_options_sheduler[day4_of_week]" value="day4_of_week" 
                <?php checked('day4_of_week',isset($prowp_options['day4_of_week'])?$prowp_options['day4_of_week']:''); ?>
                >ЧТ
            </li>
            
             <li><input disabled type="checkbox" name="tch_options_sheduler[day5_of_week]" value="day5_of_week" 
                <?php checked('day5_of_week',isset($prowp_options['day5_of_week'])?$prowp_options['day5_of_week']:''); ?>
                >ПТ
            </li>
            
            
             <li><input disabled type="checkbox" name="tch_options_sheduler[day6_of_week]" value="day6_of_week" 
                <?php checked('day6_of_week',isset($prowp_options['day6_of_week'])?$prowp_options['day6_of_week']:''); ?>
                >СБ
            </li>
            
             <li><input disabled type="checkbox" name="tch_options_sheduler[day7_of_week]" value="day7_of_week" 
                <?php checked('day7_of_week',isset($prowp_options['day7_of_week'])?$prowp_options['day7_of_week']:''); ?>
                >ВС
            </li>
            
        </ul>
    <p>
        <input disabled name="tch_options_sheduler[sheduler_mode]" type="radio" value="days_of_month"
          <?php checked('days_of_month', isset($prowp_options['sheduler_mode'])?$prowp_options['sheduler_mode']:''); ?>
          >По дням месяца, в
        
        <input disabled
            name="tch_options_sheduler[time_days_of_month]" 
            type="number" 
            value="<?php echo esc_attr( isset($prowp_options['time_days_of_month'])?$prowp_options['time_days_of_month']:'1' ); ?>"
            min="1" 
            max="24"> час(а,ов)</p>
    </p>
        <ol class="sheduler" style="width:538px;">
            &nbsp&nbsp<li>1 <input disabled type="checkbox" name="tch_options_sheduler[1d]" value="1d"
                            <?php checked('1d',isset($prowp_options['1d'])?$prowp_options['1d']:''); ?>>
                      </li>
            &nbsp&nbsp<li>2 <input disabled type="checkbox" name="tch_options_sheduler[2d]" value="2d"
                            <?php checked('2d',isset($prowp_options['2d'])?$prowp_options['2d']:''); ?>
                      </li>
            &nbsp&nbsp<li>3 <input disabled type="checkbox" name="tch_options_sheduler[3d]" value="3d"
                            <?php checked('3d',isset($prowp_options['3d'])?$prowp_options['3d']:''); ?>
                      </li>
            &nbsp&nbsp<li>4 <input disabled type="checkbox" name="tch_options_sheduler[4d]" value="4d"
                            <?php checked('4d',isset($prowp_options['4d'])?$prowp_options['4d']:''); ?>
                      </li>
            &nbsp&nbsp<li>5 <input disabled type="checkbox" name="tch_options_sheduler[5d]" value="5d"
                            <?php checked('5d',isset($prowp_options['5d'])?$prowp_options['5d']:''); ?>
                      </li>
            &nbsp&nbsp<li>6 <input disabled type="checkbox" name="tch_options_sheduler[6d]" value="6d"
                            <?php checked('6d',isset($prowp_options['6d'])?$prowp_options['6d']:''); ?>
                      </li>
            &nbsp&nbsp<li>7 <input disabled type="checkbox" name="tch_options_sheduler[7d]" value="7d"
                            <?php checked('7d',isset($prowp_options['7d'])?$prowp_options['7d']:''); ?>
                      </li>
            &nbsp&nbsp<li>8 <input disabled type="checkbox" name="tch_options_sheduler[8d]" value="8d"
                            <?php checked('8d',isset($prowp_options['8d'])?$prowp_options['8d']:''); ?>
                      </li>
            &nbsp&nbsp<li>9 <input disabled type="checkbox" name="tch_options_sheduler[9d]" value="9d"
                            <?php checked('9d',isset($prowp_options['9d'])?$prowp_options['9d']:''); ?>
                      </li>
            <li>10 <input disabled type="checkbox" name="tch_options_sheduler[10d]" value="10d"
                    <?php checked('10d',isset($prowp_options['10d'])?$prowp_options['10d']:''); ?>
            </li>
            
            <li>11 <input disabled type="checkbox" name="tch_options_sheduler[11d]" value="11d"
                    <?php checked('11d',isset($prowp_options['11d'])?$prowp_options['11d']:''); ?>
            </li>
            
            <li>12 <input disabled type="checkbox" name="tch_options_sheduler[12d]" value="12d"
                    <?php checked('12d',isset($prowp_options['12d'])?$prowp_options['12d']:''); ?>
            </li>
            
            <li>13 <input disabled type="checkbox" name="tch_options_sheduler[13d]" value="13d"
                    <?php checked('13d',isset($prowp_options['13d'])?$prowp_options['13d']:''); ?>
            </li>
            
            <li>14 <input disabled type="checkbox" name="tch_options_sheduler[14d]" value="14d"
                    <?php checked('14d',isset($prowp_options['14d'])?$prowp_options['14d']:''); ?>
            </li>
            
            <li>15 <input disabled type="checkbox" name="tch_options_sheduler[15d]" value="15d"
                    <?php checked('15d',isset($prowp_options['15d'])?$prowp_options['15d']:''); ?>
            </li>
            
            <li>16 <input disabled type="checkbox" name="tch_options_sheduler[16d]" value="16d"
                    <?php checked('16d',isset($prowp_options['16d'])?$prowp_options['16d']:''); ?>
            </li>
            
            <li>17 <input disabled type="checkbox" name="tch_options_sheduler[17d]" value="17d"
                    <?php checked('17d',isset($prowp_options['17d'])?$prowp_options['17d']:''); ?>
            </li>
            
            <li>18 <input disabled type="checkbox" name="tch_options_sheduler[18d]" value="18d"
                    <?php checked('18d',isset($prowp_options['18d'])?$prowp_options['18d']:''); ?>
            </li>
            
            <li>19 <input disabled type="checkbox" name="tch_options_sheduler[19d]" value="19d"
                    <?php checked('19d',isset($prowp_options['19d'])?$prowp_options['19d']:''); ?>
            </li>
            
            <li>20 <input disabled type="checkbox" name="tch_options_sheduler[20d]" value="20d"
                    <?php checked('20d',isset($prowp_options['20d'])?$prowp_options['20d']:''); ?>
            </li>
            
            <li>21 <input disabled type="checkbox" name="tch_options_sheduler[21d]" value="21d"
                    <?php checked('21d',isset($prowp_options['21d'])?$prowp_options['21d']:''); ?>
            </li>
            
            <li>22 <input disabled type="checkbox" name="tch_options_sheduler[22d]" value="22d"
                    <?php checked('22d',isset($prowp_options['22d'])?$prowp_options['22d']:''); ?>
            </li>
            
            <li>23 <input disabled type="checkbox" name="tch_options_sheduler[23d]" value="23d"
                    <?php checked('23d',isset($prowp_options['23d'])?$prowp_options['23d']:''); ?>
            </li>
            
            <li>24 <input disabled type="checkbox" name="tch_options_sheduler[24d]" value="24d"
                    <?php checked('24d',isset($prowp_options['24d'])?$prowp_options['24d']:''); ?>
            </li>
            
            <li>25 <input disabled type="checkbox" name="tch_options_sheduler[25d]" value="25d"
                    <?php checked('25d',isset($prowp_options['25d'])?$prowp_options['25d']:''); ?>
            </li>
            
            <li>26 <input disabled type="checkbox" name="tch_options_sheduler[26d]" value="26d"
                    <?php checked('26d',isset($prowp_options['26d'])?$prowp_options['26d']:''); ?>
            </li>
            
            <li>27 <input disabled type="checkbox" name="tch_options_sheduler[27d]" value="27d"
                    <?php checked('27d',isset($prowp_options['27d'])?$prowp_options['27d']:''); ?>
            </li>
            
            <li>28 <input disabled type="checkbox" name="tch_options_sheduler[28d]" value="28d"
                    <?php checked('28d',isset($prowp_options['28d'])?$prowp_options['28d']:''); ?>
            </li>
            
            <li>29 <input disabled type="checkbox" name="tch_options_sheduler[29d]" value="29d"
                    <?php checked('29d',isset($prowp_options['29d'])?$prowp_options['29d']:''); ?>
            </li>
            
            <li>30 <input disabled type="checkbox" name="tch_options_sheduler[30d]" value="30d"
                    <?php checked('30d',isset($prowp_options['30d'])?$prowp_options['30d']:''); ?>
            </li>
            
            <li>31 <input disabled type="checkbox" name="tch_options_sheduler[31d]" value="31d"
                    <?php checked('31d',isset($prowp_options['31d'])?$prowp_options['31d']:''); ?>
            </li>
            
        </ol>
        
    <p><input disabled name="tch_options_sheduler[sheduler_mode]" type="radio" value="once_a_month"
        <?php checked('once_a_month', isset($prowp_options['sheduler_mode'])?$prowp_options['sheduler_mode']:''); ?>
        >Раз в месяц</p>
        
    <p><input disabled name="tch_options_sheduler[sheduler_mode]" type="radio" value="after_update"
        <?php checked('after_update', isset($prowp_options['sheduler_mode'])?$prowp_options['sheduler_mode']:''); ?>
        >После апдейтов Яндекса, через 
        <input disabled
            name="time_after_update" 
            type="number" 
            value="<?php echo esc_attr( isset($prowp_options['time_after_update'])?$prowp_options['time_after_update']:'2' ); ?>"
            min="2" 
            max="24"> часа</p>
        
    <p><input disabled name="tch_options_sheduler[sheduler_mode]" type="radio" value="on_demand"
        <?php checked('on_demand', isset($prowp_options['sheduler_mode'])?$prowp_options['sheduler_mode']:''); ?>
        >По требованию
        
    <p><input disabled type="submit" class="button-primary" value="Сохранить"/></p>
</from>    
<?php
//Вывод Крон задач

// получаем все задачи из базы данных
$cron_zadachi = get_option( 'cron' );
 
// можно использовать функции print_r() или var_dump() для вывода всех задач
} elseif ($_GET['tch_page']=='settings') {
?>
<h2>Яндекс.XML</h2>
<p>
    Укажите ваши данные из 
    <a href="https://xml.yandex.ru/settings/" target="blank_">Яндекс.XML</a>
</p>
<form method="post" action="options.php">
    <?php settings_fields( 'tch-settings-api' ); ?>
    <?php $prowp_options = get_option( 'tch_options_api' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Логин в Яндекс.XML:</th>
            <td>
                <input type="text" name="tch_options_api[option_user]"
                 value="<?php echo esc_attr( $prowp_options['option_user'] ); ?>" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Ключ:</th>
            <td>
                <input class="regular-text" type="text" name="tch_options_api[option_key]" 
                 value="<?php echo esc_attr( $prowp_options['option_key'] ); ?>"/>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Ваш ip-адрес сервера:</th>
            <td>
                <input class="regular-text" type="text" name="tch_options_api[server_addr]" 
                 value="<?php echo esc_attr( $prowp_options['server_addr'] ); ?>" />
                 
                <input class="regular-text" type="text" name="SERVER[SERVER_ADDR]" 
                 value="<?php $ip_db = gethostbyname(get_hostname_db()); echo $ip_db; ?>" disabled />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Адрес вашего сайта:</th>
            <td>
                <input class="regular-text" type="text" name="tch_options_api[server_name]" 
                 value="<?php echo esc_attr( $prowp_options['server_name'] ); ?>"/>
                 
                <input class="regular-text" type="text" name="SERVER[SERVER_NAME]" 
                 value="<?php echo esc_attr( $_SERVER['SERVER_NAME'] ); ?>" disabled />
            </td>
        </tr>
        
        <!--p>Доступно лимитов на этот час: <input type="number" name="myLimit" value="0" disabled /></p>
              
        < ?php wp_enqueue_script('tch-script-get-my-limit', plugins_url('/js/get-my-limit.js',__FILE__)); ?-->
        
    </table>
    <p class="submit">
        <input type="submit" class="button-primary" value="Сохранить" />
    </p>
</form>
<?php
}
}

//Функция очистки всех данных, передаваемых настройкам плагина, перед сохранением в базе данных.
function tch_sanitize_options($input) 
{
    if (isset($input['option_name'])) {
        $input['option_name'] = sanitize_text_field( $input['option_name'] );
    }
    if (isset($input['option_email'])) {
        $input['option_email'] = sanitize_email( $input['option_email'] );
    }
    
    if (isset($input['option_url'])) {
        $input['option_unl'] = esc_url( $input['option_url'] );
    }
    return $input;
}

function tch_store_register_meta_box() {
	add_meta_box( 'tch-product-meta', __('TopChik', 'tch-plugin'), 'tch_meta_box', 'post', 'normal', 'high'  );
}

function tch_meta_box( $post )
{
    wp_enqueue_script('tch-script-core', plugins_url('/js/core.js',__FILE__));
    $post_id = $post->ID;
    $arr_list = get_tch_list($post_id);
    // проверяем временное значение из соображений безопасности
    wp_nonce_field( 'meta-box-save', 'tch-plugin' );
    echo '<div id="tch_window" class="tch_window">';
        //Див для скрытых параметров
        echo '<div id="tch-inside" class="tch-inside">';
            echo '<input name ="post_id" value="'.$post_id.'" type="hidden" />';
        echo '</div>';
        if (!empty($arr_list)){
            echo '<table id="tch-table" class="tch-table bordered">';
                //заголовки
                echo '<thead id="tch-table-thead">';
                    echo '<tr>';
                        echo '<td>';
                            echo '<input type="checkbox" id="checkAll" class="tch-cb-all">';
                        echo '</td>';
                        echo '<th>';
                            echo 'Ключевая фраза';
                        echo '</th>';
                        echo '<th colspan="2">';
                            echo 'Позиция';
                        echo '</th>';
                        //TODO Дату и последние три апа
                    echo '</tr>';
                echo '</thead>';
                //тело
                echo '<tbody id="tch-table-body" class="tch-table-body">';
                    //Получаем данные из массива
                    foreach ($arr_list as $key => $value) {
                        $id = $value->key_id;
                        $cur_place = $value->place;
                        $old_place = get_tch_place($id);
                        $cur_keyword = $value->keyword;
                        
                        echo '<tr>';
                            echo '<td class="tch-td-str">';
                                echo '<input type="checkbox" key_id="'.$id.'" class="tch-cb" value="'.esc_attr( $cur_keyword ).'">';
                            echo '</td>';
                            echo '<td key_keyword_id="'.$id.'" class="tch-keyword" name="tch_keyword_text_'.$id.'" style="width: 100%;">';
                                echo '<a href="https://yandex.ru/search/?text='.esc_attr( $cur_keyword ).'" target="_blank">'.esc_attr( $cur_keyword ).'</a>';
                            echo '</td>';
                            echo '<td>';
                                echo '<div key_place_id="'.$id.'" class="tch-position" name="tch_place_text_'.$id.'" style="width: 100%;">';
                                    echo esc_attr( $cur_place );
                                echo '</div>';
                                echo '<div img_place_id="'.$id.'" style="display: none;">';
                                    echo '<img src="'. plugins_url('/img/load-1.gif',__FILE__). '" style="width: 100%;">';
                                echo '</div>';
                            echo '</td>';
                            echo '<td>';
                                echo '<div change_place_id="'.$id.'">';
                                if (!empty($old_place))
                                {
                                    $position = $old_place-$cur_place;
                                    if ( $position > 0)
                                    {
                                        echo '<font color="green">'.'+'.$position.'</font>';
                                    } 
                                    elseif ( $position == 0)
                                    {
                                        echo '<font color="gray">0</font>';
                                    } else 
                                    {
                                        echo '<font color="red">'.$position.'</font>';
                                    }
                                    
                                } 
                                else
                                {
                                    echo '<font color="gray">0</font>';
                                }
                                echo '</div>';
                            echo '</td>';
                        echo '</tr>';
                    }
                echo '</tbody>';
                
            echo '</table>';
            
            echo '<div id="tch-div-action" style="margin:10px;">';
                echo '<select id="tch-action">';
                    echo '<option value="-1">Сохранить</option>';
            	    echo '<option value="serp">Проверить</option>';
                    echo '<option value="trash">Удалить</option>';
                echo '</select>';
                echo '<input type="submit" id="doaction" class="button" post-id="'.$post_id.'" value="Применить">';
                echo '<a id="serp_all" class="button" href="javascript:PopUpSerpAll('.$post_id.')">Проверить все</a>';
            echo '</div>';

        }
        else 
        {
            echo '<div id="not_found_keywords" style="margin-left:15px;">Добавьте поисковые запросы</div>';
        }
        //Кнопка добавления новоtch_add_keywordго КС
        echo '<div id="tch-add-button" style="margin:10px;">';
            echo '<input data-post="'.$post_id.'" type="button" id="tch_add_keyword_'.$post_id.'" class="page-title-action tch_add_keyword" value="Добавить">';
            echo '<a type="button" id="tch_add_keywords_'.$post_id.'" class="page-title-action" href="javascript:PopUpShow('.$post_id.')">Добавить несколько</a>';
            echo '<div class="b-popup" id="popup_'.$post_id.'" class="popup" style="display:none; margin:10px;">
                    <div class="b-popup-content">
                      <div><textarea style="width:90%; height:150px; margin:10px;margin-left:40px;margin-top:5px;" autocomplete="off" aria-hidden="true" id="thc-add-keys-'.$post_id.'"
                            placeholder="Разделите фразы пререносом строки"></textarea></div>
                      <a href="javascript:PopUpaddKey('.$post_id.')" type="button" class="page-title-action" style="margin:10px; margin-left:40px;">Добавить</a>
                    </div>
                  </div>';
            wp_enqueue_script('tch-script-popup-add-keys', plugins_url('/js/popup-add-keys.js',__FILE__));
        echo '</div>';

        echo '<div id="error_log"></div>';
        echo "<div id='chart_div'></div>";
    echo '</div>';
}

// сохраняем данные метаполя
function tch_store_save_meta_box( $post_id ) 
{
    include 'tch_store_save_meta_box.php';
}
//TODO сделать статусы для КС - ВКЛ/ВЫКЛ

//Зацепка для запуска еденичной проверки позиций
add_action( 'tch_add_shed_hook', 'add_sheduler_cron_once');
 
function add_sheduler_cron_once($is_new_keys) {
    include_once( dirname( __FILE__ ) . '/tch-db.php');
        
    //hard-work    
    $_GET['is_new_keys'] = $is_new_keys;
    include dirname( __FILE__ ) . '/yandex-xml.php';    
}