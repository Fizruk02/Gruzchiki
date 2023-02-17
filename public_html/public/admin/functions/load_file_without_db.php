<?php 
    include("functions.php");
$file = @$_FILES['file'];
$error = '';
$success = 2;
 

// Директория, куда будут загружаться файлы.
$childpatch='/files/loaded/';
$path = $_SERVER["DOCUMENT_ROOT"] . $childpatch;
 
if (!empty($file)) {
	// Проверим на ошибки загрузки.
	if (!empty($file['error']) || empty($file['tmp_name'])) {
		switch (@$file['error']) {
			case 1:
			case 2: $error = 'Превышен размер загружаемого файла.'; break;
			case 3: $error = 'Файл был получен только частично.'; break;
			case 4: $error = 'Файл не был загружен.'; break;
			case 6: $error = 'Файл не загружен - отсутствует временная директория.'; break;
			case 7: $error = 'Не удалось записать файл на диск.'; break;
			case 8: $error = 'PHP-расширение остановило загрузку файла.'; break;
			case 9: $error = 'Файл не был загружен - директория не существует.'; break;
			case 10: $error = 'Превышен максимально допустимый размер файла.'; break;
			case 11: $error = 'Данный тип файла запрещен.'; break;
			case 12: $error = 'Ошибка при копировании файла.'; break;
			default: $error = 'Файл не был загружен - неизвестная ошибка.'; break;
			
		}
	} elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
		$error = 'Не удалось загрузить файл.';
	} else {
		// Оставляем в имени файла только буквы, цифры и некоторые символы.
		$pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
		$name = mb_eregi_replace($pattern, '-', $file['name']);
		$name = mb_ereg_replace('[-]+', '-', $name);
		
		
		
		$ext = end(explode(".", $name));
        // t_log($name);
        $id = randhash(10);
        $name = "$id.$ext";
         
		$parts = pathinfo($name);
		if (empty($name) || empty($parts['extension'])) {
			$error = 'Не удалось загрузить файл.';
		} else {

			if (move_uploaded_file($file['tmp_name'], $path . $name)) {
        		 $link = $path . $name;
        		 $success = 1;

			} else 
				$error = 'Не удалось загрузить файл.';
			

			
			
			
			
			
		}
	}
 
	// Выводим сообщение о результате загрузки.
	if ($success==1) 
		echo json_encode(array('success'=>1, 'file'=>$link, 'public'=> '..'.$childpatch . $name));		
	 else 
	    echo json_encode(array('success'=>2, 'err'=>$error));
	
}