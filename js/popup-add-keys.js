"use strict";
jQuery(document).ready(function(jQuery) {
    PopUpHide();
});    

    
function PopUpShow(id) {
    jQuery("#popup_"+id).show();
}


function PopUpHide(id) {
    if (id == null){
        jQuery(".popup").hide();
    }else{
        jQuery("#popup_"+id).hide();   
    }
}


//Массовое добавление ключей
function PopUpaddKey(id) {
    PopUpHide();
    //Ресделим введенные фразы
    var arr_keys = document.getElementById('thc-add-keys-'+id).value.toLowerCase().split(/[\t\n]+/);

    for (var i = 0; i < arr_keys.length; i++) {
        if (arr_keys[i] != null) {
            document.getElementById('tch_add_keyword_'+id).click();
            //Найдем максимальный ИД КС
            var post_id = id;
            var win = jQuery(document.getElementById('tch_add_keyword_'+id)).closest('.tch_window');
            var cb = win.find('.tch-cb');
            var count_cb = 0;
            //Получаем уникальный ид - на основе максимального ид элемнтов КС
            if (cb.length > 0) {
                var max_id = 0;
                jQuery(cb).each(function() {
                    count_cb = ++count_cb;
    
                    //Борем значение больше чем у элементов из базы или созданных динамически
                    if (jQuery(this).attr('key_id') > max_id) {
                        max_id = jQuery(this).attr('key_id');
                    }
                    if (count_cb > max_id) {
                        count_cb = count_cb;
                    }
                });
    
                //получаем следующее значени
            }
    
            if (max_id > count_cb) {
                count_cb = parseInt(max_id) + parseInt(1);
            }
            else {
                //Узнаем колчиесво строк, и получаем новый ид
                if (count_cb > 0) {
                    count_cb = post_id + count_cb;
                }
                else {
                    count_cb = post_id + 1;
                }
            }
            
            
            //Добавим новый КС
            document.querySelector('input[key_keyword_id="' + max_id + '"]').setAttribute('value', arr_keys[i]);
        }
    }
}


//Кнопка "Проверить все КС"
function PopUpSerpAll(id){
    document.getElementById('checkAll').click();
    document.getElementById('tch-action').getElementsByTagName('option')[1].selected = 'selected';
    function in_serp(){
        jQuery('#doaction').attr('type', 'button');
        document.getElementById('doaction').click();
    }
    setTimeout(in_serp, 1500);
}