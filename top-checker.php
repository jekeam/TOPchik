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
    
    //Кнопка добавления нового КС
    echo '<div id="tch-add-button">';
        echo '<input type="button" id="tch_add_keyword" value="Добавить">';
    echo '</div>';
        
    if (!empty($arr_list))
    {
        echo '<table id="tch-table">';
            //заголовки
            echo '<thead id="tch-table-thead">';
                echo '<tr>';
                    echo '<td>';
                        echo '<input type="checkbox" id="checkAll" class="tch-cb-all">';
                    echo '</td>';
                    echo '<th>';
                        echo 'Ключевая фраза';
                    echo '</th>';
                    echo '<th>';
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
                    echo '<tr>';
                        echo '<td>';
                            echo '<input type="checkbox" key_id="'.$id.'" class="tch-cb" value="'.esc_attr( $value->keyword ).'">';
                        echo '</td>';
                        echo '<td>';
                            echo '<input type="text" key_keyword_id="'.$id.'" class="tch-keyword" value="'.esc_attr( $value->keyword ).'" name="tch_keyword_text_'.$id.'">';
                        echo '</td>';
                        echo '<td>';
                            echo '<input type="number" key_place_id="'.$id.'" class="tch-position" value="'.esc_attr( $value->place ).'" name="tch_place_text_'.$id.'">';
                        echo '</td>';
                    echo '</tr>';
                }
            echo '</tbody>';
            
        echo '</table>';
        
        echo '<div>';
            echo '<select id="tch-action">';
                echo '<option value="-1">Действия</option>';
        	    echo '<option value="serp">Проверить</option>';
                echo '<option value="trash">Удалить</option>';
            echo '</select>';
            echo '<input type="button" id="doaction" value="Применить">';
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
        $attr_keyword = 'tch_keyword_text_';
        $id = ($post_id*10)+1;
        while (!empty($_POST['tch_keyword_text_'.$id]))
        {
            //Запись КС
            set_db_tch_keywords( $id, sanitize_text_field($_POST['tch_keyword_text_'.$id]), $post_id);
            
            //Запись позиции
            if (!empty ($_POST['tch_place_text_'.$id]))
            {
                set_db_tch_serp( $id, sanitize_text_field($_POST['tch_place_text_'.$id]));
            }
            
            $id = ++$id;
            
        }
        // сохраняем данные метаполя в произвольных полях записи
    }
}

//подключаем JS
//TODO
 
add_action( 'wp_enqueue_scripts', 'true_include_myscript' );

add_action('admin_print_footer_scripts', 'tch_action_javascript', 99);
function tch_action_javascript($post_id) 
{
     $prowp_options = get_option( 'tch_options' );
	?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) 
	{    
	    function CheckAll()
	    {
	        if ($("#checkAll").is(":checked"))
	        {
	             //$(".tch-cb").attr("checked",true).change();
	             //$(".tch-cb").prop('checked', true).change();
	            // $("#checkAll").prop('checked', true).change();
	             $(".tch-cb").prop('checked', true).change();
	        } 
	        else
	        {
	            //$(".tch-cb").attr("checked",false).change();
	            //$(".tch-cb").removeAttr('checked').change();
	            //$("#checkAll").removeAttr('checked').change();
	            $(".tch-cb").removeAttr('checked').change();
	        }
	    }
	    
	    function CheckdRemove()
	    {
	        $(".tch-cb").removeAttr('checked').change();
	        $("#checkAll").removeAttr('checked').change();
	    }
	    
	    //Скрипт который запускает проверку чз Яндекс-ХМЛ и возвращает позицию КС
	     $('#doaction').click(function () {
	         //Если выбрана проверка
	         CheckdRemove();
	         if ($('#tch-action').val() === 'serp')
	         {
    	         $('.tch-cb:input:checkbox:checked').each(function()
    	         {
                     var keyword_val = $(this).val();
                     var key_place_id = $(this).attr( 'key_id');
                     
        	         $.ajax({
                	             type: "POST",
                                 url: "/wp-content/plugins/top-checker/yandex-xml.php",
                                 data: ({
                                         user: "<?php echo esc_attr( $prowp_options['option_user'] ); ?>",
                                         key: "<?php echo esc_attr( $prowp_options['option_key'] ); ?>",
                                         domain: "<?php echo esc_attr( $prowp_options['server_name'] ); ?>",
                                         keyword: keyword_val
                                        }),
                                beforeSend: function()
                                {
                                    //TODO ожидание
                                },                        
                                success: function (data) 
                                {
                                    $('[key_place_id = "'+key_place_id+'"]').val(data).change();
                                }
            		  });
    	         });
	         } 
	         //Удаление КС
	         else if ($('#tch-action').val() == 'trash')
	         {  //Ищем отмеченные жлементы
	              $('.tch-cb:input:checkbox:checked').each(function(){
	                  $(this).parents('tr').remove();
	                  
	                  //Проверим, если это последний чекбокс, то выведем инфу что мол друг извини, надо нажать кнопку "Добавить"
	                  if ($('.tch-cb').length == 0 )
	                  {
                        $('.tch-cb-all').parents('tr').remove();
                        $('#tch-add-button').append('<div id="not_found_keywords">Ключевые слова не заданы.</>');
	                  }
	                  
	                  //Удаляем из БД
	                  //(this).getAttribute('key_id'));
	                  //"< ?php delete_tch_keyword(14); ?>";
    	         });
	         }
	     });
	     
	     //Скрипт автоматически сохраняет изменения ключевых фраз и позций
	     $('.tch-position').change(function()
	     {
	         var v_key_id = $(this).attr('key_place_id');
	         var v_position = $(this).val();
	         $.ajax({
	             type: "POST",
                 url: "/wp-content/plugins/top-checker/tch-update.php",
                 data: ({
                     key_id: v_key_id,
                     place: v_position,
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
	     $('.tch-keyword').change(function()
	     {
	         var v_post_id = $('#post_ID').val();
	         var v_key_id = $(this).attr('key_keyword_id');
	         var v_keyword = $(this).val();
	         
	         $.ajax({
        	             type: "POST",
                         url: "/wp-content/plugins/top-checker/tch-update.php",
                         data: ({
                                     post_id: v_post_id,
                                     key_id: v_key_id,
                                     keyword: v_keyword,
                                     update: 'keyword'
                                }),
                        beforeSend: function()
                        {
                            //ожидание
                        },                        
                        success: function (data) 
                        {
                            //результат
                            $('[key_id = "'+v_key_id+'"').val(v_keyword);
                        }
	             });
	     });
	     
	     //Добавление новых КС
	     $('#tch_add_keyword').live('click', function(event)
	     {
	        var d         = document;
	        var count_cb  = 0;
	        var post_id   = $('#post_ID').val();
	        
	        //Получаем уникальный ид- на основе максимального ид элемнтов КС
	         if (!$.isEmptyObject($('.tch-cb')))
	         {
    	         $('.tch-cb').each(function() {
    	             count_cb = ++count_cb;
    	         });
    	         //получаем следующее значени
    	         count_cb++;
	         }
	        
	        // элемент-таблица КС
            var tableBody = d.getElementById('tch-table-body');
            // новые элементы
            var tr = d.createElement('tr');
            
            var td_cb = d.createElement('td');
            var checkBox = d.createElement('input');
            checkBox.type = 'checkbox';
            checkBox.classList.add('tch-cb');
            
            //Узнаем колчиесво строк, и получаем новый ид
            if (count_cb > 0)
            {
                checkBox.setAttribute('key_id', post_id+count_cb);
            } else
            {
                checkBox.setAttribute('key_id', post_id+1);
            }
            
            var td_keywords = d.createElement('td'),
                inputText = d.createElement('input');
                inputText.type = 'text';
            if (count_cb > 0)
            {
                inputText.setAttribute('key_keyword_id', post_id+count_cb);
                inputText.setAttribute('name', 'tch_keyword_text_'+post_id+count_cb);
            } else
            {
                inputText.setAttribute('key_keyword_id', post_id+1);
                inputText.setAttribute('name', 'tch_keyword_text_'+post_id+1);
            }
            
            var td_place = d.createElement('td'),
                inputNumber = d.createElement('input');
                inputNumber.type = 'number';
            if (count_cb > 0)
            {
                inputNumber.setAttribute('key_place_id', post_id+count_cb);
                inputNumber.setAttribute('name', 'tch_place_text_'+post_id+count_cb);
            } else
            {
                inputNumber.setAttribute('key_place_id', post_id+1);
                inputNumber.setAttribute('name', 'tch_place_text_'+post_id+1);
            }
            
            
            // добавление в конец таблицы новой строки
            tableBody.appendChild(tr);
            //1 колонка
            tr.appendChild(td_cb);
            td_cb.appendChild(checkBox);
            //2 колонка
            tr.appendChild(td_keywords);
            td_keywords.appendChild(inputText);
            //3 колонка
            tr.appendChild(td_place);
            td_place.appendChild(inputNumber);
            
            //Удаление уведомления если есть not_found_keywords
            if(!$.isEmptyObject($('#not_found_keywords')))
            {
                $('#not_found_keywords').remove();
            }

	     });
	     
	     // Отметить|снять отметку со ВСЕХ
	     $("#checkAll").click(function()
	     {
	        CheckAll();
	    });
	    
    });
	</script>
	<?php
}
//TODO сделать статусы для КС - ВКЛ/ВЫКЛ