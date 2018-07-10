"use strict";
jQuery(document).ready(function($) {

  $('input#add_task_on_demand').click(function() {
    
    //document.getElementById('add_task_on_demand').disabled = 'disable';
    
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
          try {
            if (xhr.readyState == 4) {
            }
            else if (xhr.readyState > 2) {
              var new_response = xhr.responseText.substring(xhr.previous_text.length);
              console.log(new_response);
              var result = JSON.parse(new_response);

              document.getElementById("divProgress").innerHTML += result.message + '';
              document.getElementById('progressor').style.width = result.progress + "%";

              xhr.previous_text = xhr.responseText;
              if (result.progress == '100'){
                document.getElementById('add_task_on_demand').disabled = '';
              }
            }
          }
          catch (e) {
            //alert("Возникла ошибка: " + e);
            console.log("Возникла ошибка: " + e);
          }
        };
        var is_new_keys = document.getElementById('is_new_keys').checked;
        if (is_new_keys) {
          xhr.open("GET", "/wp-content/plugins/TopChik/tch-cron.php?is_new_keys=1", true);
        }else{
          xhr.open("GET", "/wp-content/plugins/TopChik/tch-cron.php?is_new_keys=0", true);
        }
        xhr.send();
      }
      catch (e) {
        //alert("Возникла ошибка: " + e);
        console.log("Возникла ошибка: " + e);
      }

    }

    //проверяем позиции
    get_positin();
  });
});