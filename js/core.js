"use strict";
jQuery(document).ready(function($) 
{    
    function CheckAll()
    {
        if ($("#checkAll").is(":checked"))
        {
             $(".tch-cb").prop('checked', true).change();
        } 
        else
        {
            $(".tch-cb").removeAttr('checked').change();
        }
    }
    
    function CheckdRemove()
    {
        $(".tch-cb").removeAttr('checked').change();
        $("#checkAll").removeAttr('checked').change();
    }
    
    //Скрипт который запускает проверку чз Яндекс-ХМЛ и возвращает позицию КС
     $('#doaction').click(function () 
     {
         //Если выбрана проверка
         if ($('#tch-action').val() === 'serp')
         {
	         $('.tch-cb:visible:input:checkbox:checked').each(function()
	         {
                var keyword_val = $(this).val();
                var key_place_id = $(this).attr( 'key_id');
                
                function get_positin ()
                {
        	         $.ajax({
                	             type: "POST",
                                 url: "/wp-content/plugins/top-checker/yandex-xml.php",
                                 data: ({
                                         keyword: keyword_val
                                        }),
                                beforeSend: function()
                                {
                                    //TODO ожидание
                                },                        
                                success: function (data) 
                                {
                                    $('[key_place_id="'+key_place_id+'"]').text(data).change();
                                }
            		  });
                }
                //проверяем позиции
                get_positin();
	         });
         } 
         //Удаление КС
         else if ($('#tch-action').val() == 'trash')
         {  //Ищем отмеченные жлементы
            var list_del_key_id;
            
            if ($("#list_del_key_id").val().length > 0 )
            {
                list_del_key_id = $("#list_del_key_id").val()+',';
            }
            else
            {
                list_del_key_id = $("#list_del_key_id").val();
            }
            
	        $('.tch-cb:input:checkbox:checked').each(function(){
                $(this).parents('tr').hide();
                $('[key_keyword_id="'+$(this).attr('key_id')+'"]').removeAttr('name');
                $('[key_keyword_id="'+$(this).attr('key_id')+'"]').attr('name', 'del_key_keyword_id_'+$(this).attr('key_id'));
                
                //Проверим, если это последний чекбокс, то выведем инфу что мол друг извини, надо нажать кнопку "Добавить"
                if ($('.tch-cb:visible').length == 0 && $('#not_found_keywords').length == 0)
                {
                    $('#tch-div-action').hide();
                    $('.tch-cb-all').parents('tr').hide();//Помечаем к удалению из БД
                    $('#tch-add-button').append('<div id="not_found_keywords">Ключевые слова не заданы.</>');
                }
                //Помечаем к удалению из БД удаленные КС
                list_del_key_id = list_del_key_id + $(this).attr('key_id')+',';                    
            });
            list_del_key_id = list_del_key_id.slice(0,-1);
            $("#list_del_key_id").val(list_del_key_id);
            //Помечаем к удалению из БД удаленные КС
          
         }
     
     //снимаем флажки
     CheckdRemove();
     //Сбрасываем экшин
     $('#tch-action').val('-1');
     });
     
     //Скрипт автоматически сохраняет изменения ключевых фраз и позций
     $('.tch-position').change(function()
     {
         var v_key_id = $(this).attr('key_place_id');
         var v_position = $(this).text();
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
     $(document).on('click', '#tch_add_keyword', function()
     {
        var d         = document;
        var count_cb  = 0;
        var post_id   = $('#post_ID').val();
        
        //Получаем уникальный ид- на основе максимального ид элемнтов КС
         if (!$.isEmptyObject($('.tch-cb')))
         {
             var max_id = 0;
	         $('.tch-cb').each(function() {
	             count_cb = ++count_cb;
	             
	             //Борем значение больше чем у элементов из базы или созданных динамически
	             if ($(this).attr('key_id') > max_id)
	             {
	                max_id = $(this).attr('key_id');
	             } 
	             if (count_cb > max_id)
	             {
	                 count_cb = count_cb;
	             }
	         });
	         
	         //получаем следующее значени
         }
         
         if (max_id>count_cb)
         {
             count_cb = parseInt(max_id)+parseInt(1);
         } else
         {
             //Узнаем колчиесво строк, и получаем новый ид
	         if (count_cb > 0)
	         {
	            count_cb = post_id+count_cb;
	         }
	         else
	         {
	            count_cb = post_id+1;
	         }
         }
        
        // элемент-таблица КС
        var tableBody = d.getElementById('tch-table-body');
        // новые элементы
        var tr = d.createElement('tr');
        
        var td_cb = d.createElement('td');
        var checkBox = d.createElement('input');
        checkBox.type = 'checkbox';
        checkBox.classList.add('tch-cb');
        checkBox.setAttribute('key_id', count_cb);
        
        var td_keywords = d.createElement('td'),
            inputText = d.createElement('input');
            inputText.type = 'text';
            inputText.setAttribute('key_keyword_id', count_cb);
            inputText.setAttribute('name', 'tch_keyword_text_'+count_cb);
    
        
        var td_place = d.createElement('td'),
            inputNumber = d.createElement('input');
            inputNumber.type = 'number';
            inputNumber.setAttribute('key_place_id', count_cb);
            inputNumber.setAttribute('name', 'tch_place_text_'+count_cb);
        
        
        
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
        
        var cur_list_key_id = $('#list_key_id').val();
        $('#list_key_id').val(cur_list_key_id+','+count_cb);

        //Сбрасывает на всяки
        $('#tch-action').val('-1');
        $('#tch-div-action').show();
     });
     
     
     var d = document;
   //Блок создания создания списка(смтроки) ид для инсерта/обновления КС
     var input_key_id = d.createElement('input');
     var text_key_id = '';
     input_key_id.type = 'hidden';
     input_key_id.id = 'list_key_id';
     input_key_id.name = 'list_key_id';
     $('.tch-cb').each(function() {
        text_key_id = text_key_id+(this).getAttribute('key_id')+',';
     });
     input_key_id.value = text_key_id.slice(0, -1);
     
      //помещаем созданные элементы на страницу
     var divInside = d.getElementById('tch-inside');
     divInside.appendChild(input_key_id);
     
    //Блог для создания списка(строки)с ид КС для удаления
     var input_del_key_id = d.createElement('input');
     input_del_key_id.type = 'hidden';
     input_del_key_id.id = 'list_del_key_id';
     input_del_key_id.name = 'list_del_key_id';
     
     //помещаем созданные элементы на страницу
     divInside.appendChild(input_del_key_id);
     
     // Отметить|снять отметку со ВСЕХ
     $('#checkAll').click(function()
     {
        CheckAll();
    });
    
    //Меняем для Удаления и применения на РЕфрещ страницы, а для сьема позиций все работает без перезагрузки
    $('#tch-action').change(function() {
        if ($('#tch-action').val() == 'serp')
        {
            $('#doaction').removeAttr('type');
            $('#doaction').attr('type','button');
        } else
        {
            $('#doaction').removeAttr('type');
            $('#doaction').attr('type','submit');
        }
    });
});