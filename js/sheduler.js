"use strict";
jQuery(document).ready(function($) {

  $('input#add_task_on_demand').click(function() {
    function get_positin() {
      /*
            $.ajax({
              type: "POST",
              url: "/wp-content/plugins/TopChik/yandex-xml.php",
              beforeSend: function() {
                var progressbar = $("#progressbar"),
                  progressLabel = $(".progress-label");

                progressbar.progressbar({
                  value: false,
                  change: function() {
                    progressLabel.text(progressbar.progressbar("value") + "%");
                  },
                  complete: function() {
                    progressLabel.text("Позиции сняты!");
                  }
                });

                $(function() {

                  function progress() {
                    var val = progressbar.progressbar("value") || 0;

                    progressbar.progressbar("value", val + 2);

                    if (val < 99) {
                      setTimeout(progress, 80);
                    }
                  }

                  setTimeout(progress, 2000);
                });
              },
              success: function(data) {
                console.log('data:' + data);
                alert('Позиции успешно сняты');
              }
            });*/

      if (!window.XMLHttpRequest) {
        alert("Ваш браузер не поддерживает собственный объект XMLHttpRequest.");
        return;
      }
      try {
        var xhr = new XMLHttpRequest();
        xhr.previous_text = '';

        xhr.onerror = function() { alert("[XHR] Фатальная ошибка."); };
        xhr.onreadystatechange = function() {
          try {
            if (xhr.readyState == 4) {
              alert('[XHR] Готово')
            }
            else if (xhr.readyState > 2) {
              var new_response = xhr.responseText.substring(xhr.previous_text.length);
              var result = JSON.parse(new_response);

              document.getElementById("divProgress").innerHTML += result.message + '';
              document.getElementById('progressor').style.width = result.progress + "%";

              xhr.previous_text = xhr.responseText;
            }
          }
          catch (e) {
            alert("[XHR STATECHANGE] Возникла ошибка: " + e);
          }
        };
        xhr.open("GET", "/wp-content/plugins/TopChik/yandex-xml.php", true);
        xhr.send();
      }
      catch (e) {
        alert("[XHR REQUEST] Возникла ошибка: " + e);
      }

    }

    //проверяем позиции
    get_positin();
  });
});