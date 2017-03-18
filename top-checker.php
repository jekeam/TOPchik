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
    $id = $post->ID . 1;
    $keysword = get_tch_keyword($id);
    $place = get_tch_place($id);
    // проверяем временное значение из соображений безопасности
    wp_nonce_field( 'meta-box-save', 'tch-plugin' );
    //Морда плагина
    echo '<table>';
        echo '<thead>';
            echo '<tr>';
                echo '<th>';
                    echo 'Ключевая фраза';
                echo '</th>';
                echo '<th>';
                    echo 'Позиция';
                echo '</th>';
                echo '<th>';
                    //для кнопки
                echo '</th>';
            echo '</tr>';
        echo '</thead>';
        echo '<tfoot>';
            echo '<td height="60">';
                echo '<input name="save" type="submit" class="button button-primary button-large" id="publish" value="Обновить">';
                echo '</td>';
                echo '<td>';
                echo '</td>';
                echo '<td>';
                echo '</td>';
            echo '</tr>';
        echo '</tfoot>';
        echo '<tbody>';
            echo '<tr>';
                echo '<td>';
                    echo '<input type="text" id="tch_keyword_text" name="tch_keyword_text" value="' .esc_attr( $keysword ).'" size="75">';
                echo '</td>';
                echo '<td>';
                    echo '<input type="text" name="tch_place_text" id="tch_place_text" value="' .esc_attr( $place ).'" size="10">';
                echo '</td>';
                echo '<td>';
                    echo '<botton id="tch_action" name="tch_action" class="preview button">Получить</botton>';
                echo '</td>';
            echo '</tr>';
        echo '</tbody>';
    echo '</table>';
    echo '<div id="get_answer"></div>';
    echo '<textarea id="get_answer_orign">'. file_get_contents('https://yandex.ru/search/xml?user=jekeam&key=03.342975233:d814aa1537550b5bacec1fd65ce41fe0&query=%D0%B1%D0%BB%D0%BE%D0%B3+%D0%B0%D1%81%D1%82%D0%BC%D0%B0%D1%82%D0%B8%D0%BA%D0%B0&lr=225&l10n=ru&sortby=rlv&filter=strict&maxpassages=1&groupby=attr%3D%22%22.mode%3Dflat.groups-on-page%3D10.docs-in-group%3D1&page=1') .'</textarea>';
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
            set_db_tch_serp( $post_id . 1, sanitize_text_field($_POST['tch_place_text']));
        }
        else 
        {
            null;//тут ключевую фразу
        }
    }
}

//add_action('admin_print_scripts', 'my_action_javascript'); // такое подключение будет работать не всегда
add_action('admin_print_footer_scripts', 'tch_action_javascript', 99);
function tch_action_javascript() {
	?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) 
	{
	     $('#tch_action').click(function () 
	     {
    		/*var data = 
    		{
    			action: 'tch_action',
    			whatever: 1234
            };
    		// с версии 2.8 'ajaxurl' всегда определен в админке
    		jQuery.post( ajaxurl, data, function(response) 
    		{
    			alert('Получено с сервера: ' + response);
    		});*/
    		var keyword_val = $('#tch_keyword_text').val();//'PHP библиотека Яндекс.xml';
    		$.ajax
    		({
                type: "POST",
                url: "/wp-content/plugins/top-checker/yandex-xml.php",
                data: ({keyword: keyword_val}),
            success: function (data) 
                {
                    //$('#tch_place_text').val(data);
                    $('#get_answer').html(data);
                }
            });
        });
    });
	</script>
	<?php
}

add_action('wp_ajax_tch_action', 'my_action_callback');
function my_action_callback() {
	$whatever = intval( $_POST['whatever'] );

	$whatever += 10;
	echo $whatever;

	wp_die(); // выход нужен для того, чтобы в ответе не было ничего лишнего, только то что возвращает функция
}