<?php
/*
Plugin Name: TOP-CHECKER
Plugin URI: https://suineg.ru/top-checker.html
Description: Проверка позиций ключевых слов в поисковой выдаче Яндекса и Google, удобная аналитика.
Author: Alex Savinyh
Version: 0.1
Author URI: https://suineg.ru/
*/

//define('TOP_CHECKER_VERSION', '0.1');
//Создадим таблицу для ключевых слов и таблицу для свбора статистика по КС
//версии таблиц
$tch_keywords_db_ver = "0.1";
$tch_serp_db_ver = "0.1";

//суфиксы таблиц
global $tch_tbl_keywords;
global $tch_tbl_serp;
global $date_query;

$tch_tbl_keywords = "tch_keywords";
$tch_tbl_serp = "tch_serp";
$date_query = date("Y-m-d");


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/tch-install.php');
include_once( dirname( __FILE__ ) . '/tch-uninstall.php');
include_once( dirname( __FILE__ ) . '/tch-db.php');
    
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
    add_options_page( 'Top-checker', 'Top-checker', 'manage_options', 'tch_settings_menu', 'tch_settings_page' );
    //Задаим функцию для настройки плагина
    add_action('admin_init', 'tch_register_settings' );
}

// регистрируем настройки
function tch_register_settings() 
{
    register_setting( 'tch-settings-group', 'tch_options','tch_sanitize_options');
}

//создать страницу параметров
function tch_settings_page()
{
?>
<h1>Topсhecker — съем позиций прямо из WP</h1>
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
    $recent_posts_array = get_posts(); // получаем массив постов
    foreach( $recent_posts_array as $recent_post_single ) : // для каждого поста из массива
    	echo '<a href="' . get_permalink( $recent_post_single ) . '">' . $recent_post_single->post_title . '</a><br>'; // выводим ссылку
    	tch_meta_box($recent_post_single);
    	echo '<br><br><br>';
    endforeach; // конец цикла
?>
    
<?php 
}elseif ($_GET['tch_page']=='statistics') {
    wp_enqueue_script('tch-script-progressBar', plugins_url('/js/progressBar.js',__FILE__));
    wp_enqueue_script('tch-script-d3js-avg', plugins_url('/src/loader.js',__FILE__));//для гугл графиков
    wp_enqueue_script('tch-script-graphic-avg', plugins_url('/js/graphic-avg.js',__FILE__));
    wp_enqueue_script('tch-script-graphic-dynamics', plugins_url('/js/graphic-dynamics.js',__FILE__));
?>
<text x="0" y="15.1875" style="cursor: default; user-select: none; -webkit-font-smoothing: antialiased; font-family: Roboto; font-size: 16px;" fill="#757575" dx="0px">Ключевые показатели сайта</text>
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
    <p><b>Выберите расписание проверок</b></p>
    <p><input name="sheduler_mode" type="radio" value="days_of_week">По дням недели, в
        <input name="time_days_of_week" type="number" value="" min="0" max="24"> час(а,ов)</p>
        <ul class="sheduler">
            <li><input type="checkbox" name="days_of_week" id="1d"/>ПН</li>
            <li><input type="checkbox" name="days_of_week" id="2d"/>ВТ</li>
            <li><input type="checkbox" name="days_of_week" id="3d"/>СР</li>
            <li><input type="checkbox" name="days_of_week" id="4d"/>ЧТ</li>
            <li><input type="checkbox" name="days_of_week" id="5d"/>ПТ</li>
            <li><input type="checkbox" name="days_of_week" id="6d"/>СБ</li>
            <li><input type="checkbox" name="days_of_week" id="7d"/>ВС</li>
        </ul>
    <p>
        <input name="sheduler_mode" type="radio" value="days_of_month">По дням месяца, в
        <input name="time_days_of_month" type="number" value="" min="0" max="24"> час(а,ов)</p>
    </p>
        <ol class="sheduler" style="width:538px;">
            &nbsp&nbsp<li>1 <input type="checkbox" name="days_of_week" id="1d"/></li>
            &nbsp&nbsp<li>2 <input type="checkbox" name="days_of_week" id="2d"/></li>
            &nbsp&nbsp<li>3 <input type="checkbox" name="days_of_week" id="3d"/></li>
            &nbsp&nbsp<li>4 <input type="checkbox" name="days_of_week" id="4d"/></li>
            &nbsp&nbsp<li>5 <input type="checkbox" name="days_of_week" id="5d"/></li>
            &nbsp&nbsp<li>6 <input type="checkbox" name="days_of_week" id="6d"/></li>
            &nbsp&nbsp<li>7 <input type="checkbox" name="days_of_week" id="7d"/></li>
            &nbsp&nbsp<li>8 <input type="checkbox" name="days_of_week" id="8d"/></li>
            &nbsp&nbsp<li>9 <input type="checkbox" name="days_of_week" id="9d"/></li>
            <li>10 <input type="checkbox" name="days_of_week" id="10d"/></li>
            <li>11 <input type="checkbox" name="days_of_week" id="11d"/></li>
            <li>12 <input type="checkbox" name="days_of_week" id="12d"/></li>
            <li>13 <input type="checkbox" name="days_of_week" id="13d"/></li>
            <li>14 <input type="checkbox" name="days_of_week" id="14d"/></li>
            <li>15 <input type="checkbox" name="days_of_week" id="15d"/></li>
            <li>16 <input type="checkbox" name="days_of_week" id="16d"/></li>
            <li>17 <input type="checkbox" name="days_of_week" id="17d"/></li>
            <li>18 <input type="checkbox" name="days_of_week" id="18d"/></li>
            <li>19 <input type="checkbox" name="days_of_week" id="19d"/></li>
            <li>20 <input type="checkbox" name="days_of_week" id="20d"/></li>
            <li>21 <input type="checkbox" name="days_of_week" id="21d"/></li>
            <li>22 <input type="checkbox" name="days_of_week" id="22d"/></li>
            <li>23 <input type="checkbox" name="days_of_week" id="23d"/></li>
            <li>24 <input type="checkbox" name="days_of_week" id="24d"/></li>
            <li>25 <input type="checkbox" name="days_of_week" id="25d"/></li>
            <li>26 <input type="checkbox" name="days_of_week" id="26d"/></li>
            <li>27 <input type="checkbox" name="days_of_week" id="27d"/></li>
            <li>28 <input type="checkbox" name="days_of_week" id="28d"/></li>
            <li>29 <input type="checkbox" name="days_of_week" id="29d"/></li>
            <li>30 <input type="checkbox" name="days_of_week" id="30d"/></li>
            <li>31 <input type="checkbox" name="days_of_week" id="31d"/></li>
        </ol>
    <p><input name="sheduler_mode" type="radio" value="once_a_month">Раз в месяц</p>
    <p><input name="sheduler_mode" type="radio" value="after_update">После апдейтов Яндекса, через 
        <input name="time_after_update" type="number" value="" min="2" max="24"> часа</p>
    <p><input name="sheduler_mode" type="radio" value="on_demand">По требованию</p>
    <p><input type="submit" class="button-primary" value="Сохранить" /></p>
</from>    
<?php
} elseif ($_GET['tch_page']=='settings') {
?>
<h2>Яндекс.XML</h2>
<p>
    Укажите ваши данные из 
    <a href="https://xml.yandex.ru/settings/">Яндекс.XML</a>
</p>
<form method="post" action="options.php">
    <?php settings_fields( 'tch-settings-group' ); ?>
    <?php $prowp_options = get_option( 'tch_options' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Логин в Яндекс.XML:</th>
            <td>
                <input type="text" name="tch_options[option_user]" 
                 value="<?php echo esc_attr( $prowp_options['option_user'] ); ?>" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Ключ:</th>
            <td>
                <input class="regular-text" type="text" name="tch_options[option_key]" 
                 value="<?php echo esc_attr( $prowp_options['option_key'] ); ?>"/>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Ваш ip-адрес сервера:</th>
            <td>
                <input class="regular-text" type="text" name="tch_options[server_addr]" 
                 value="<?php echo esc_attr( $prowp_options['server_addr'] ); ?>" />
                 
                <input class="regular-text" type="text" name="SERVER[SERVER_ADDR]" 
                 value="<?php echo esc_attr( $_SERVER['SERVER_ADDR'] ); ?>" disabled />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Адрес вашего сайта:</th>
            <td>
                <input class="regular-text" type="text" name="tch_options[server_name]" 
                 value="<?php echo esc_attr( $prowp_options['server_name'] ); ?>"/>
                 
                <input class="regular-text" type="text" name="SERVER[SERVER_NAME]" 
                 value="<?php echo esc_attr( $_SERVER['SERVER_NAME'] ); ?>" disabled />
            </td>
        </tr>
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
    $input['option_name'] = sanitize_text_field( $input['option_name'] );
    $input['option_email'] = sanitize_email( $input['option_email'] );
    $input['option_unl'] = esc_url( $input['option_url'] );
    return $input;
}

function tch_store_register_meta_box() {
	add_meta_box( 'tch-product-meta', __('Поисковые запросы', 'tch-plugin'), 'tch_meta_box', 'post', 'normal', 'high'  );
}

function tch_meta_box( $post )
{
    wp_enqueue_script('tch-script-core', plugins_url('/js/core.js',__FILE__));
    $post_id = $post->ID;
    $arr_list = get_tch_list($post_id);
    
    // проверяем временное значение из соображений безопасности
    wp_nonce_field( 'meta-box-save', 'tch-plugin' );
    echo '<div id="tch_window">';
        //Див для скрытых параметров
        echo '<div id="tch-inside">';
        echo '</div>';
        
        if (isset($arr_list)){
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
                echo '<tbody id="tch-table-body">';
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
            
            //Кнопка добавления нового КС
            echo '<div id="tch-add-button">';
                echo '<input type="button" id="tch_add_keyword" class="page-title-action" value="Добавить фразу">';
                //echo '<input type="button" id="tch_add_keywords" class="page-title-action" value="Добавить несколько">';
            echo '</div>';
            
            echo '<div id="tch-div-action">';
                echo '<select id="tch-action">';
                    echo '<option value="-1">Сохранить</option>';
            	    echo '<option value="serp">Проверить</option>';
                    echo '<option value="trash">Удалить</option>';
                echo '</select>';
                echo '<input type="submit" id="doaction" class="button" value="Применить">';
            echo '</div>';
    
        }
        else 
        {
            echo '<div id="not_found_keywords">Ключевые слова не заданы.</div>';
        }
        echo '<div id="error_log"></div>';
        echo "<div id='chart_div'></div>";
    echo '</div>';
}

// сохраняем данные метаполя
function tch_store_save_meta_box( $post_id ) 
{
    // проверяем, относится ли запись к нашему типу и были ли отправлены метаданные
    if ( isset( $_POST['tch-plugin'] ) && get_post_type( $post_id ) == 'post'  ) 
    {
        // если установлено автосохранение, пропускаем сохранение данных
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;
        
        // проверка из соображений безопасности
        check_admin_referer( 'meta-box-save', 'tch-plugin' );
        
        // сохраняем данные метаполя в произвольных полях записи
        $list_key_id = $_POST['list_key_id'];
        $arr_key_id = explode(',',$list_key_id);
        foreach ($arr_key_id as $id) 
        {
            if (!empty($_POST['tch_keyword_text_'.$id]))
            {
                //Запись КС
                set_db_tch_keywords( $id, sanitize_text_field($_POST['tch_keyword_text_'.$id]), $post_id);
                
                //Запись позиции
                if (!empty ($_POST['tch_place_text_'.$id]))
                {
                    set_db_tch_serp( $id, sanitize_text_field($_POST['tch_place_text_'.$id]));
                }
                else 
                {
                    set_db_tch_serp( $id, 0);
                }
            }
        }
        //Удаляем все что получили для удаления
        $list_del_key_id = $_POST['list_del_key_id'];
        $arr_del_key_id = explode(',',$list_del_key_id);
        foreach ($arr_del_key_id as $id) 
        {
            delete_tch_keyword($id);
        }
    }
}
//TODO сделать статусы для КС - ВКЛ/ВЫКЛ