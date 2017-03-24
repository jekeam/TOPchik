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

ini_set('log_errors', 'On');
ini_set('error_log', dirname( __FILE__ ) . '/log/php_errors.log');

include_once( dirname( __FILE__ ) . '/tch-install.php');
include_once( dirname( __FILE__ ) . '/tch-uninstall.php');
include_once( dirname( __FILE__ ) . '/tch-db.php');

function console_log( $data ){
  echo '<script>';
  echo 'console.log('. json_encode( $data ) .')';
  echo '</script>';
}

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
//Подключаем скрипт
function my_scripts_method() {
	wp_deregister_script( 'jquery' );
	wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
	wp_enqueue_script( 'jquery' );
}    

add_action( 'wp_enqueue_scripts', 'my_scripts_method', 11 );


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
    
    //Морда плагина
    
    //Кнопка добавления нового КС
    //echo '<link href="'.plugin_dir_url( __FILE__ ).'/css/style-admin.css" rel="stylesheet">';
    echo '<div class="wrap">';
        echo '<input type="button" id="tch_add_keyword" value="Добавить">';
    echo '</div>';
        
    if (!empty($arr_list))
    {
        /*echo '<div class="alignleft actions bulkactions">';
            echo '<select name="action" id="bulk-action-selector-top">';
                echo '<option value="-1">Действия</option>';
        	    echo '<option value="edit" class="hide-if-no-js">Изменить</option>';
                echo '<option value="trash">Удалить</option>';
            echo '</select>';
            echo '<input type="submit" id="doaction" class="button action" value="Применить">';
        echo '</div>';*/
        echo '<div>';
        echo '<table>';
            echo '<thead>';
                echo '<tr>';
                    echo '<td>';
                        echo '<input type="checkbox" class="tch-cb-all">';
                    echo '</td>';
                    echo '<th>';
                        echo 'Ключевая фраза';
                    echo '</th>';
                    echo '<th>';
                        echo 'Позиция';
                    echo '</th>';
                    /*echo '<th>';
                        echo 'Дата';
                    echo '</th>';*/
                echo '</tr>';
            echo '</thead>';
            echo '<tfoot>';
            echo '<tr>';
                echo '<td>';
                    echo '<input type="checkbox" class="tch-cb-all">';
                echo '</td>';
                echo '<th>';
                    echo 'Ключевая фраза';
                echo '</th>';
                echo '<th>';
                    echo 'Позиция';
                echo '</th>';
            echo '</tfoot>';
            echo '<tbody>';
                //Получаем данные из массива
                foreach ($arr_list as $key => $value) {
                    $id = $value->key_id;
                    echo '<tr>';
                        echo '<td>';
                            echo '<input type="checkbox" id="tch-cb-id-'.$id.'" class="tch-cb">';
                        echo '</td>';
                        echo '<td>';
                            echo '<input type="text" value="'.esc_attr( $value->keyword ).'">';
                        echo '</td>';
                        echo '<td>';
                            echo '<input type="number" value="'.esc_attr( $value->place ).'">';
                        echo '</td>';
                        /*echo '<td>';
                            echo '<input type="text" value="' .substr(esc_attr( $value->data ), 5, 5).'" disabled>';
                        echo '</td>';*/
                    echo '</tr>';
                }
            echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '<div>';
            echo '<select id="tch-action">';
                //echo '<option value="-1">Действия</option>';
        	    echo '<option value="serp">Проверить</option>';
                echo '<option value="trash">Удалить</option>';
            echo '</select>';
            echo '<input type="button" id="doaction" value="Применить">';
        echo '</div>';
    }
    else 
    {
        echo '<div>Ключевые слова не заданы.</div>';
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
        if (!empty ($_POST['tch_keyword_text']))
        {
            //update_post_meta( $post_id, '_tch_keyword_text', sanitize_text_field( $_POST['tch_keyword_text'] ));
            set_db_tch_keywords( $post_id . 1, sanitize_text_field($_POST['tch_keyword_text']), $post_id);
            if (!empty ($_POST['tch_place_text']))
            {
                set_db_tch_serp( $post_id . 1, sanitize_text_field($_POST['tch_place_text']));
            }
        }
        else 
        {
            null;//тут ключевую фразу
        }
    }
}

add_action('admin_print_footer_scripts', 'tch_action_javascript', 99);
function tch_action_javascript($post_id) 
{
     $prowp_options = get_option( 'tch_options' );
     var_dump($post_id);
	?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) 
	{    //Скрипт который запускает проверку чз Яндекс-ХМЛ и возвращает позицию КС
	     $('#doaction').click(function () {
	         //Если выбрана проверка
	         if ($('#tch-action').val() === 'serp')
	         {
    	         //var keyword_val = $('#tch_keyword_text').val();//'PHP библиотека Яндекс.xml';
    	         $('.tch-cb:input:checkbox:checked').each(function()
    	         {
                    alert(1);
                 });/*
    	         $.ajax({
    	             type: "POST",
                     url: "/wp-content/plugins/top-checker/yandex-xml.php",
                     data: ({
                         user: "<?php echo esc_attr( $prowp_options['option_user'] ); ?>",
                         key: "<?php echo esc_attr( $prowp_options['option_key'] ); ?>",
                         domain: "<?php echo esc_attr( $prowp_options['server_name'] ); ?>",
                         keyword: keyword_val
                         }),
                    beforeSend: function(){
                        $('#tch_action').text('Проверка...');
                    },                        
                    success: function (data) {
                        $('#tch_place_text').val(data).change();
                        $('#tch_action').text('Проверить');
                    }
        		});*/
	         } 
	         else if ($('#tch-action').val() == 'trash')
	         {
	             
	         }
	     });
	     
	     //Скрипт автоматически сохраняет изменения ключевых фраз и позций
	     $('#tch_place_text').change(function()
	     {
	         var v_post_id = $('#post_ID').val();
	         var v_place = $('#tch_place_text').val();
	         $.ajax({
	             type: "POST",
                 url: "/wp-content/plugins/top-checker/tch-update.php",
                 data: ({
                     post_id: v_post_id,
                     place: v_place,
                     update: 'place'
                 }),
                beforeSend: function(){
                    //ожидание
                },                        
                success: function (data) {
                    //результат
                }
	        });
	     });
	     
	     //Скрипт автоматически сохраняет изменения ключевых фраз и позций
	     $('#tch_keyword_text').change(function()
	     {
	         var v_post_id = $('#post_ID').val();
	         var v_keyword = $('#tch_keyword_text').val();
	         $.ajax({
	             type: "POST",
                 url: "/wp-content/plugins/top-checker/tch-update.php",
                 data: ({
                     post_id: v_post_id,
                     keyword: v_keyword,
                     update: 'keyword'
                 }),
                beforeSend: function(){
                    //ожидание
                },                        
                success: function (data) {
                    //результат
                }
	        });
	     });
	     
	     $('.tch_add_keyword').live('click', function(event)
	     {
	        $('.var_tch_keyword').parent().append(
                    '<tr>'+
                        '<td>'+
                            '<input type="text" id="tch_keyword_text" name="tch_keyword_text" value="">'+
                        '</td>'+
                        '<td>'+
                            '<input type="number" name="tch_place_text" id="tch_place_text" value="">'+
                        '</td>'+
                        '<td>'+
                            '<botton name="tch_action" id="tch_action" class="tch_action preview button">Проверить</botton>'+
                        '</td>'+
                    '</tr>'
	        );
	        //alert('ok');
	     });
	     
	     $('.tch_del_keyword').on('click', function()
	     {
	            $(this).parent().parent().parent().remove();
	        //alert('ok');
	     });
    });
	</script>
	<?php
}