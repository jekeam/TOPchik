"use strict";
jQuery(document).ready(function($){
    
  $('input#add_task_on_demand').click(function(){
      function get_positin (){
	         $.ajax({
	            type: "POST",
                url: "/wp-content/plugins/ТопЧик - анализ поисковых запросов/yandex-xml.php",
                beforeSend: function(){
                    $( function() {
                        var progressbar = $( "#progressbar" ),
                            progressLabel = $( ".progress-label" );
                     
                        progressbar.progressbar({
                          value: false,
                          change: function() {
                            progressLabel.text( progressbar.progressbar( "value" ) + "%" );
                          },
                          complete: function() {
                            progressLabel.text( "Позиции сняты!" );
                          }
                        });
                     
                        function progress() {
                          var val = progressbar.progressbar( "value" ) || 0;
                     
                          progressbar.progressbar( "value", val + 2 );
                     
                          if ( val < 99 ) {
                            setTimeout( progress, 80 );
                          }
                        }
                     
                        setTimeout( progress, 2000 );
                      });
                },
                success: function (data){
                    alert('Позиции успешно сняты');
                }
	         });
        }
        //проверяем позиции
        get_positin();
  });
});