$(document).ready(function() {
    PopUpHide();
});


function PopUpShow() {
    $("#popup1").show();
}


function PopUpHide() {
    $("#popup1").hide();
}


//Массовое добавление ключей
function PopUpaddKey() {
    PopUpHide();
    //Ресделим введенные фразы
    arr_keys = document.getElementById('thc-add-keys').value.toLowerCase().split(/[\t\n]+/);

    for (var i = 0; i < arr_keys.length; i++) {
        if (arr_keys[i] != null) {
            document.getElementById('tch_add_keyword').click;
            document.getElementById('tch_add_keyword').click();
            //Найдем максимальный ИД КС
            var max_id = $('[key_keyword_id]')[$('[key_keyword_id]').length - 1].getAttribute('key_keyword_id');
            max_id = max_id == null ? $('#post_ID').val()+1 : max_id;
            //Добавим новый КС
            document.querySelector('input[key_keyword_id="' + max_id + '"]').setAttribute('value', arr_keys[i]);
        }
    }
}


//Кнопка "Проверить все КС"
function PopUpSerpAll(){
    document.getElementById('checkAll').click;
    document.getElementById('checkAll').click();
    document.getElementById('tch-action').getElementsByTagName('option')[1].selected = 'selected';
    document.getElementById('doaction').click;
    document.getElementById('doaction').click();
}