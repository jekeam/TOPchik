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
    
    if( get_current_screen()->id != 'post' ) 
    {
        return;
    }
    else
    {
        wp_enqueue_script('tch-script', plugins_url('/js/core.js',__FILE__));
    }
}
add_action('admin_enqueue_scripts', 'tch_action_javascript', 999);

//Подключаем стили
add_action( 'admin_enqueue_scripts', 'tch_stylesheet' );
function tch_stylesheet()
{
    wp_enqueue_style("style-tch", plugins_url('/css/tch-style-admin.css',__FILE__));
}

//Создадим таблицу для ключевых слов и таблицу для свбора статистика по КС
//версии таблиц
$tch_keywords_db_ver = "0.1";
$tch_serp_db_ver = "0.1";

//суфиксы таблиц
global $tch_tbl_keywords;
global $tch_tbl_serp;
    
$tch_tbl_keywords = "tch_keywords";
$tch_tbl_serp = "tch_serp";

//Задаем настройки для меню плагина
function tch_create_settings_submenu() 
{
    add_options_page( 'История запросов', 'Поисковые запросы', 'manage_options', 'tch_settings_menu', 'tch_settings_page' );
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
    <div class="wrap">
        <h1>TOP CHECKER</h1>
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
    </div>
<?php
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
    $post_id = $post->ID;
    $arr_list = get_tch_list($post_id);
    
    // проверяем временное значение из соображений безопасности
    wp_nonce_field( 'meta-box-save', 'tch-plugin' );
    
    //Див для скрытых параметров
    echo '<div id="tch-inside">';
    echo '</div>';
    
    //Кнопка добавления нового КС
    echo '<div id="tch-add-button">';
        echo '<input type="button" id="tch_add_keyword" class="page-title-action" value="Добавить">';
        echo '<input type="button" id="tch_add_keywords" class="page-title-action" value="Добавить несколько">';
    echo '</div>';
        
    if (!empty($arr_list))
    {
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
                        /*echo '<td>';
                            echo '<input type="text" key_keyword_id="'.$id.'" class="tch-keyword" value="'.esc_attr( $value->keyword ).'" name="tch_keyword_text_'.$id.'">';
                        echo '</td>';*/
                        echo '<td key_keyword_id="'.$id.'" class="tch-keyword" name="tch_keyword_text_'.$id.'" style="width: 100%;">';
                            echo '<a href="https://yandex.ru/search/?text='.esc_attr( $cur_keyword ).'" target="_blank">'.esc_attr( $cur_keyword ).'</a>';
                        echo '</td>';
                        /*echo '<td>';
                            echo '<input type="number" key_place_id="'.$id.'" class="tch-position" value="'.esc_attr( $value->place ).'" >';
                        echo '</td>';*/
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