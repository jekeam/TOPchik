"use strict";
jQuery(document).ready(function($) {
    function getMyLimit() {
        var date = new Date();
        var hour = 
                    date.getFullYear() + '-' +  
                    ("0" + (date.getMonth()+1)).slice(-2) + '-' + 
                    ("0" + date.getDate()).slice(-2) + ' ' + 
                    ("0" + date.getHours()).slice(-2) + ':00:00 +0000';
        $.ajax({
            type: "POST",
            url: "/wp-content/plugins/TopChik/yandex-xml.php",
            data: ({
                hour: hour
            }),
            beforeSend: function() {},
            success: function(data) {
                if (data.slice(0, 6) == 'Ошибка') {
                    console.log('Ошибка:' + data);
                }
                else {
                    console.log('Ок:' + data);
                }

            }
        });
    }

    getMyLimit();
});