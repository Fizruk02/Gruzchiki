<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
include("simpleImage.php");
    
$file = @$_FILES['file'];
$error = $success = '';
 
// Разрешенные расширения файлов.
$ext_images = array('jpg', 'jpeg', 'png', 'gif');

// $allowext = array('img'=>array('jpg', 'jpeg', 'png', 'gif'), 'pdf'=> array('pdf'), 'doc'=>array());
 
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
        $id = randhash(10);
        $name = "$id.$ext";
         
		$parts = pathinfo($name);
		if (empty($name) || empty($parts['extension'])) {
			$error = 'Не удалось загрузить файл.';
		} elseif (!empty($ext_images) && !in_array(strtolower($parts['extension']), $ext_images)) {
		    
			if (move_uploaded_file($file['tmp_name'], $path . $name)) {
				
				
		  
        		  $group = ($_POST['filegroup']??'')?:loadFiles()->getFileGroup();
        		  
        		  $insert_id = insertQuery("INSERT INTO files (id_group, name, small_size, medium_size, large_size, type_file) VALUES (:id_group, :name, 'files/systems/file.png', 'files/systems/file.png', :large_size,'doc');",
        		  [ ':id_group'=> $group, ':name'=> $file['name'], ':large_size'=> $childpatch.$name ]);

        		  $success = true;
        		  
        		  $resize=[];
        		  $resize['small_size'] = '/files/systems/file.png';
        		  $resize['medium_size'] = '/files/systems/file.png';
        		  $resize['fileid'] = $insert_id;
        		  $resize['typefile'] = 'file';
        		  $resize['original_link'] = '/'.$childpatch.$name;



			} else {
				$error = 'Не удалось загрузить файл.';
			}

		} else {
		    
			if (move_uploaded_file($file['tmp_name'], $path . $name)) {
				
				
                $group = ($_POST['filegroup']??'')?:loadFiles()->getFileGroup();
                $resize = resize($id, $path . $name, $group);
				$resize['typefile'] = 'img';
			
				$success = true;
			} else {
				$error = 'Не удалось загрузить файл.';
			}
		}
	}


	if ($success) {
		echo json_encode([ 'success'=>1, 'id_group'=>$group, 'small_size'=>$resize['small_size'], 'medium_size'=>$resize['medium_size'], 'fileid'=>$resize['fileid'], 'typefile'=>$resize['typefile'], 'original_link'=>$resize['original_link'] ]);		
	} else {
	    echo json_encode([ 'success'=>2, 'error'=>$error ]);
	}
}