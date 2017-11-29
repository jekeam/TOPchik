"use strict";
jQuery(document).ready(function($) 
{    
    function CheckAll(element)
    {
        var tbl = element.closest('.tch-table');
        
        if (element.is(":checked")){
             tbl.find('.tch-cb').each(function(){
                 $(this).prop('checked', true).change();
             });
        } 
        else
        {
            tbl.find('.tch-cb').each(function(){
                 $(this).removeAttr('checked').change();
             });
        }
    }
    
    function CheckdRemove()
    {
        $(".tch-cb").removeAttr('checked').change();
        $("#checkAll").removeAttr('checked').change();
    }
    
    //Скрипт который запускает проверку чз Яндекс-ХМЛ и возвращает позицию КС
     $(document).on('click','#doaction', function () 
     {
         var tch_action =  $(this).parent();
         var tch_window =  $(this).parent().parent();
         if (tch_window.find('#checkAll').is(":checked")){
            tch_window.find('#checkAll').removeAttr('checked').change();   
         }
         //Если выбрана проверка
         if (tch_window.find('#tch-action').val() === 'serp')
         {
             //Сколько всего отмечено
             var cb_cnt = tch_window.find('.tch-cb:visible:input:checkbox:checked').length;
             
	         tch_window.find('.tch-cb:visible:input:checkbox:checked').each(function(indx, el)
	         {
	            $(this).removeAttr('checked').change();
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
                                    $('[key_place_id="'+key_place_id+'"]').hide();
                                    $('[img_place_id="'+key_place_id+'"]').show();
                                    $('#error_log').hide();
                                },                        
                                success: function (data) 
                                {
                                    $('[img_place_id="'+key_place_id+'"]').hide();
                                    $('[key_place_id="'+key_place_id+'"]').show();
                                    var cur_old_place =  Number($('[key_place_id="'+key_place_id+'"]').text());
                                    if (data.slice(0,6) == 'Ошибка')
                                    {
                                        $('#error_log').show();
                                        $('#error_log').text(data).change();
                                        $('#error_log').css('color','red');
                                        
                                        $('[change_place_id="'+key_place_id+'"]').text(cur_old_place);
                                        $('[change_place_id="'+key_place_id+'"]').css('color','gray');
                                    }
                                    else
                                    {
                                        $('[key_place_id="'+key_place_id+'"]').text(data).change();
                                        var cur_new_place =  $('[key_place_id="'+key_place_id+'"]').text();
                                        if (cur_new_place > 0)
                                        {
                                            var difference = cur_old_place-cur_new_place;
                                            if ( difference > 0)
                                            {
                                                $('[change_place_id="'+key_place_id+'"]').text('+'+difference);
                                                $('[change_place_id="'+key_place_id+'"]').css('color','green');
                                            } 
                                            else if ( difference < 0)
                                            {
                                                $('[change_place_id="'+key_place_id+'"]').text(difference);
                                                $('[change_place_id="'+key_place_id+'"]').css('color','red');
                                            } else 
                                            {
                                                $('[change_place_id="'+key_place_id+'"]').text('0');
                                                $('[change_place_id="'+key_place_id+'"]').css('color','gray');
                                            }
                                        }
                                        else
                                        {
                                            $('[change_place_id="'+key_place_id+'"]').text(cur_old_place);
                                            $('[change_place_id="'+key_place_id+'"]').css('color','gray');
                                        }
                                    }
                                    
                                    //есили это последний элемент, обновим график
                                    if (cb_cnt == indx+1) 
                                    {
                                        location.reload();
                                    }
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
     //CheckdRemove();
     //Сбрасываем экшин
     $('#tch-action').val('-1');
     });
     /*
     //Скрипт автоматически сохраняет изменения ключевых фраз и позций
     $(document).on('change','.tch-position', function()
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
     $(document).on('change','.tch-keyword', function()
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
     */
     
     //Добавление нового КС
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
        
        var tableBody;
        // элемент-таблица КС
        if ($.isEmptyObject(d.getElementById('tch-table-body')))
        {
            //tableBody = d.createElement('tbody');
            $('<table id="tch-table" class="tch-table bordered">'+
            '<thead id="tch-table-thead">'+
                '<tr>'+
                    '<td>'+
                        '<input type="checkbox" id="checkAll" class="tch-cb-all">'+
                    '</td>'+
                   '<th>Ключевая фраза</th>'+
                 '<th colspan="2">Позиция</th>'+
                 '</tr>'+
            '</thead>'+
            //тело
            '<tbody id="tch-table-body">'+
            '</tbody>'+
        '</table>'+
        '<div id="tch-div-action">'+
            '<select id="tch-action">'+
                '<option value="-1">Сохранить</option>'+
        	    '<option value="serp">Проверить</option>'+
                '<option value="trash">Удалить</option>'+
            '</select>'+
            '<input type="submit" id="doaction" class="button" value="Применить">'+
        '</div>').insertBefore($('#not_found_keywords'));
        tableBody = d.getElementById('tch-table-body');
        }
        else
        {
            tableBody = d.getElementById('tch-table-body');
        }
        // новые элементы
        var tr = d.createElement('tr');
        
        var td_cb = d.createElement('td');
        var checkBox = d.createElement('input');
        checkBox.type = 'checkbox';
        checkBox.classList.add('tch-cb');
        checkBox.setAttribute('key_id', count_cb);
        
        var td_keywords = d.createElement('td'),
            inputText = d.createElement('input');
            td_keywords.setAttribute('colspan', 3);
            inputText.type = 'text';
            inputText.setAttribute('key_keyword_id', count_cb);
            inputText.setAttribute('name', 'tch_keyword_text_'+count_cb);
            inputText.setAttribute('style', 'width: 100%;');
            //td_keywords.css('width','100%');
    
        
        /*var td_place = d.createElement('td'),
            inputNumber = d.createElement('input');
            inputNumber.type = 'number';
            inputNumber.setAttribute('key_place_id', count_cb);
            inputNumber.setAttribute('name', 'tch_place_text_'+count_cb);*/
        
        // добавление в конец таблицы новой строки
        tableBody.appendChild(tr);
        //1 колонка
        tr.appendChild(td_cb);
        td_cb.appendChild(checkBox);
        //2 колонка
        tr.appendChild(td_keywords);
        td_keywords.appendChild(inputText);
        //3 колонка
        /*tr.appendChild(td_place);
        td_place.appendChild(inputNumber);*/
        
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
     $(document).on('click','#checkAll', function(){
        CheckAll($(this));
    });
    
    //Меняем для Удаления и применения на РЕфрещ страницы, а для сьема позиций все работает без перезагрузки
    $(document).on('change','#tch-action', function() {
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