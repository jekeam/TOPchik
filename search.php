<?php
	// Исходный массив
	$flowers = array("Астра", "Нарцисс", "Роза", "Пион", "Примула",
			         "Подснежник", "Мак", "Первоцвет", "Петуния", "Фиалка");
	
	if (!empty($_GET['term']))       
    {
        $term = $_GET['term'];
		
		// Шаблон рег. выражения
		$pattern = '/^'.preg_quote($term).'/iu';
		
		echo json_encode(preg_grep($pattern, $flowers));
    }
	
?>