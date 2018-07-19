"use strict";
jQuery(document).ready(function($) {


    function get_positin() {

        if (!window.XMLHttpRequest) {
            alert("Проверка невозможна - ваш браузер не поддерживает собственный объект XMLHttpRequest...");
            return;
        }
        try {
            var xhr = new XMLHttpRequest();
            xhr.previous_text = '';

            xhr.onerror = function() {
                //alert("Фатальная ошибка!");
                console.log("Фатальная ошибка!");
            };
            xhr.onreadystatechange = function() {
                try {} catch (e) {
                    //alert("Возникла ошибка: " + e);
                    console.log("Возникла ошибка: " + e);
                }
            };
            var is_new_keys = document.getElementById('is_new_keys').checked;
            if (is_new_keys) {
                xhr.open("GET", "/wp-content/plugins/TopChik/tch-cron.php?is_new_keys=1", true);
            } else {
                xhr.open("GET", "/wp-content/plugins/TopChik/tch-cron.php?is_new_keys=0", true);
            }
            xhr.send();
        } catch (e) {
            //alert("Возникла ошибка: " + e);
            console.log("Возникла ошибка: " + e);
        }

    }


    function getStatusCron() {
        $.ajax({
            type: "POST",
            url: "/wp-content/plugins/TopChik/tch-cron-db.php",
            data: ({
                send_status_cron: '1'
            }),
            beforeSend: function() {},
            success: function(data) {
                var result = JSON.parse(data);

                var text_desc = 'Задание №' + result.key_id + '<br>' +
                    'Статус: ' + result.status + '<br>' +
                    '% выполнения: ' + result.done + '<br>' +
                    'Описание: ' + result.msg + '<br><br>' +
                    'Создано: ' + result.date_create + '<br>' +
                    'Старт: ' + result.date_start + '<br>' +
                    'Окончание: ' + result.date_end + '<br>';

                var desc = document.getElementById("divProgress").innerHTML;
                if (desc != text_desc) {
                    document.getElementById("divProgress").innerHTML = text_desc;
                }

                var stat = document.getElementById('cron_status_h1').innerHTML;
                if (stat != result.status) {
                    document.getElementById('cron_status_h1').innerHTML = result.status;
                }

                document.getElementById('progressor').style.width = result.done + "%";

                if (result.status == 'выключено' || result.status == 'завершено' || result.status == 'ошибка') {
                    document.getElementById('add_task_on_demand').disabled = '';
                    document.getElementById('stop_task_on_demand').disabled = 'true';
                } else {
                    document.getElementById('add_task_on_demand').disabled = 'true';
                    document.getElementById('stop_task_on_demand').disabled = '';
                }
            }
        });
    }


    getStatusCron();
    setInterval(getStatusCron, 1000);


    $('input#add_task_on_demand').click(function() {

        document.getElementById('add_task_on_demand').disabled = 'true';
        document.getElementById('stop_task_on_demand').disabled = '';
        //проверяем позиции
        get_positin();
    });


    $('input#stop_task_on_demand').click(function() {
        var inputElements = document.querySelectorAll("[name='tch_options_sheduler[sheduler_mode]'");
        for (var i = 0; inputElements[i]; ++i) {
            if (inputElements[i].value == 'on_demand') {
                inputElements[i].checked = true;
                break;
            }
        }

        document.getElementById('stop_task_on_demand').disabled = 'disabled';
        document.getElementById('add_task_on_demand').disabled = '';

        $.ajax({
            type: "POST",
            url: "/wp-content/plugins/TopChik/tch-cron-db.php",
            data: ({
                update_sheduler_cron: '1'
            }),
            beforeSend: function() {},
            success: function(data) {
                var result = JSON.parse(data);

                if (result.status == 'выключено' || result.status == 'завершено' || result.status == 'ошибка') {
                    document.getElementById('add_task_on_demand').disabled = '';
                    document.getElementById('stop_task_on_demand').disabled = 'true';
                } else {
                    document.getElementById('add_task_on_demand').disabled = 'true';
                    document.getElementById('stop_task_on_demand').disabled = '';
                }

            }
        });
        document.getElementsByClassName('button-primary')[0].click();


    });

});