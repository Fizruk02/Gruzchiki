<?php
    function addSystemMessages($systemMessages){
 
        $uniqIdList = [];
        
        foreach($systemMessages as $s){
            
            $uniqIdList[] = $s['uniqId'];
            if(!singleQuery('SELECT * FROM `dialogue` WHERE name = :uniqId', [':uniqId'=> $s['uniqId']])){
               
                # 1. Ищем группу, если не находим, то создаем её
                if(!$groupSQL = singleQuery('SELECT * FROM `dialogue_group` WHERE name = :group', [':group'=> $s['group']]))
                    $groupId = insertQuery('INSERT INTO `dialogue_group` (`name`, `type`) VALUES (:group, 50)', [':group'=> $s['group']]);
                else
                    $groupId = $groupSQL['id'];
                # 1. Ищем язык, если не находим, то ставим по умолчанию 1
                if(!$lanSQL = singleQuery('SELECT * FROM `dialogue_lan` WHERE name = :lan OR description = :lan', [':lan'=> $s['lan']]))
                    $lanId = 1;
                else
                    $lanId = $lanSQL['id'];
                
                # добавляем в таблицу информацию о сообщении
                $dialId = insertQuery('INSERT INTO `dialogue` (`name`, `description`, `id_group`) VALUES (:name, :description, :id_group)', [ ':name'=> $s['uniqId'], ':description'=> $s['name'], ':id_group'=> $groupId ]);
                
                # добавляем вариант перевода
                insertQuery('INSERT INTO `dialogue_translate` (`id_dial`, `id_lan`, `body`) VALUES (:id_dial, :id_lan, :body)', [ ':id_dial'=> $dialId, ':id_lan'=> $lanId, ':body'=> $s['body'] ]);
            }
        }
        
        # сохраняем id сообщений, чтобы при деинсталяции модуля удалить их
        $fileDir = dirname($_SERVER['SCRIPT_FILENAME']);
        if($fileDir && count($uniqIdList))
            file_put_contents($fileDir.'/messages_db', json_encode($uniqIdList));
        
        
    }
    
    function addSystemUserSettings($userSettings){
        $uniqIdList = [];
        foreach($userSettings as $s){
            $uniqIdList[] = $s['key'];
            
            if($s['group']==='events'){
                $search = singleQuery('SELECT * FROM `settings` WHERE t_key = ? AND `value` = ?', [ $s['key'], $s['value'] ]);
                $s['visible']=0;
                $s['type']='text';
                $s['name'] = $s['key'].' - '.$s['value'];
            } else {
                $search = singleQuery('SELECT * FROM `settings` WHERE t_key = :t_key', [':t_key'=> $s['key']]);
                
                
                if($s['group']&&!singleQuery('SELECT * FROM `settings_group` WHERE techname=? OR name=?', [ $s['group'],$s['group_name'] ])){
                    query('INSERT INTO `settings_group` (`techname`, `name`, `owner`, `sort`) VALUES (?,?,"",2)', [ $s['group'],$s['group_name']?:$s['group'] ]);
                }
            }
            
            if(!$search)
                insertQuery('INSERT INTO `settings` (`t_key`, `value`, `name`, `visible`, `type`, `t_group`) VALUES (:t_key, :value, :name, :visible, :type, :group)',
                                        [ ':t_key'=> $s['key'], ':value'=> $s['value'], ':name'=> $s['name'],  ':visible'=> $s['visible'], ':type'=> $s['type'], ':group'=> $s['group'] ]);

        }
        
        $fileDir = dirname($_SERVER['SCRIPT_FILENAME']);
        if($fileDir && count($uniqIdList))
            file_put_contents($fileDir.'/settings_db', json_encode($uniqIdList));
    }
         
         
         
    function addCron($crdata){
        if(!isset($crdata['command'])) return false;
        foreach(['minutes','hours','days','months','weekdays'] as $p)
        if(!isset($crdata[$p])) $crdata[$p]='*';  
        
        $cronfile = $_SERVER['DOCUMENT_ROOT'].'/../cron/data.json';
        if(!file_exists($cronfile)) file_put_contents($cronfile, '[]');
        $data = json_decode(file_get_contents($cronfile), true);  
        foreach($data as $d) if($d['command']==$crdata['command']) return false;
        $data[]=$crdata;
        file_put_contents($cronfile, json_encode($data));
    }   
           
    
    function deleteMessage($message){
        
        if(!$mess = singleQuery('SELECT id, id_group FROM `dialogue` WHERE name = :name', [ ':name'=> $message ])) return;
        
        deleteQuery('DELETE FROM `dialogue` WHERE name = :name', [ ':name'=> $message ]);
        deleteQuery('SELECT * FROM `dialogue_translate` WHERE id_dial = :id', [ ':id'=> $mess['id'] ]);
        
        if(!singleQuery('SELECT id FROM `dialogue` WHERE id_group = :id_group', [ ':id_group'=>  $mess['id_group']]))
        deleteQuery('DELETE FROM `dialogue_group` WHERE id = :id', [ ':id'=>  $mess['id_group'] ]);
        
    }
    
    
    
    function overwrite_directory($source, $destination){
        # перезаписать директорию и оставить исходники
        removeDirectory($destination);
        copy_directory($source, $destination);
    }
    
    function copy_directory($source, $destination)
    {
        if (is_dir($source)) {
            @mkdir($destination);
            $directory = dir($source);
            while (FALSE !== ($readdirectory = $directory->read())) {
                if ($readdirectory == '.' || $readdirectory == '..') {
                    continue;
                }
                $PathDir = $source . '/' . $readdirectory;
                if (is_dir($PathDir)) {
                    copy_directory($PathDir, $destination . '/' . $readdirectory);
                    continue;
                }
                copy($PathDir, $destination . '/' . $readdirectory);
            }
            $directory->close();
        } else {
            copy($source, $destination);
        }
    }
    







    function exportingRecordsToDatabase( $dataFile ){
        global $pdo;
        ini_set('upload_max_filesize', '1000M');
        ini_set('post_max_size', '1000M');
        ini_set('memory_limit', '1000M');
        
        $pathinfo = pathinfo($dataFile);
        
        $tempPath = uniqid();
        $dbDir = $pathinfo['dirname'].'/'.$tempPath;
        $dataFilename  = $pathinfo['filename'];
        
        $zip = new ZipArchive;
        $resZip = $zip->open($dataFile);
        if ($resZip !== TRUE) {
            $res['success'] = false;
            $res['err'] = 'ошибка разархивирования файла '.$fileGet['file'].'. код:'.$resZip;
            return $res;
        }
        
        $zip->extractTo($dbDir);
        $zip->close();
        unset($zip);
        $dataFiles = array_diff(scandir($dbDir), array('..', '.'));
        foreach($dataFiles as $dataFile)
        {
            $file = file_get_contents($dbDir.'/'.$dataFile);
            $file = json_decode($file, true);
            $table = $file['table'];
            $fields = $file['fields'];
            $values = $file['values'];
            
            if(!count($values) || !count($values) || !$table)
                continue;
                
            /** search first row
             */
            $search = [];
            $valFirstRow = $values[0];
            foreach($fields as $key=> $field)
                $search[] = "`$field` = '{$valFirstRow[$key]}'";
            
            
            if(singleQuery('SELECT * FROM `'.$table.'` WHERE '.implode(' AND ', $search))) continue;
            
            $fields = '`'.implode('`, `', $fields).'`';
            $insert = [];
            
            foreach($values as $key => $value)
                $insert[] = '( \''.implode("','", $value).'\')';
        
            $stmt = $pdo->prepare( 'INSERT INTO `'.$table.'` ( '.$fields.' ) VALUES '. implode(',', $insert) );
            $stmt->execute([]);
            
            unset($values);
        }
        
        removeDirectory( $dbDir );
        return [ 'success'=> true ];
    }


    function addMenuItems(  $items= [], $group = '' ){
        
        if( $group != '' )
            query('DELETE FROM `s_menu` WHERE `items_group` = :items_group', [ ':items_group'=> $group ]);

        function setMenuItems($items = [], $owner = 0, $group = ''){
            foreach( $items as $menuItem ){
                $insertId = insertQuery('INSERT INTO `s_menu` (`items_group`, `item`, `owner`,  `menu`, `link`, `display`, `sort`) VALUES (:items_group, :item, :owner, "admin", :link, 1, '.++$i.')',
                    [ ':item'=> $menuItem['name'], ':link'=> $menuItem['link'] ? $menuItem['link']:'', ':items_group'=> $group, ':owner'=> $owner ]);
                
                if(isset($menuItem['items']))
                    setMenuItems( $menuItem['items'], $insertId, $group );
            }
            
        }
        setMenuItems( $items, 0, $group );
        
        
    }
    
    
    function convertImage($originalImage, $outputImage, $quality){
    
    $ext = pathinfo($originalImage)['extension'];
    
    if (preg_match('/jpg|jpeg/i',$ext)){
        $imageTmp=imagecreatefromjpeg($originalImage);
    }
    else if (preg_match('/png/i',$ext)){
        $imageTmp=imagecreatefrompng($originalImage);
        imagepalettetotruecolor($imageTmp);
        imagealphablending($imageTmp, true);
        imagesavealpha($imageTmp, true);
    }
    else if (preg_match('/gif/i',$ext)){
        $imageTmp=imagecreatefromgif($originalImage);
    }
    else if (preg_match('/bmp/i',$ext)){
        $imageTmp=imagecreatefromwbmp($originalImage);
    }
    else    {
        return false;
    }



    if (preg_match('/jpg|jpeg/i',$ext)){
        $res = imagejpeg($imageTmp, $outputImage, $quality);
    }
    else if (preg_match('/png/i',$ext)){
        $res = imagepng($imageTmp, $outputImage, $quality);
    }
    else if (preg_match('/gif/i',$ext)){
        $res = imagegif($imageTmp, $outputImage, $quality);
    }
    else if (preg_match('/bmp/i',$ext)){
        $res = imagebmp($imageTmp, $outputImage, $quality);
    }




    //$npar = pathinfo($originalImage);
//
    //if (preg_match('/webp/i',$npar['extension'])){
    //    
    //    
    //    $res = imagewebp($imageTmp, $outputImage, $quality);
    //    
    //    if (filesize($outputImage) % 2 == 1) {
    //    	file_put_contents($outputImage, "\0", FILE_APPEND);
    //    }
    //
    //} else if (preg_match('/jpg|jpeg/i',$npar['extension'])){
    //    $res = imagejpeg($imageTmp, $outputImage, $quality);
    //}
    //else    {
    //    return false;
    //}
    //
    //imagedestroy($imageTmp);
    
    
    
    return $res;
    }


    function webpResize($img, $max = 500){
        $im = imagecreatefromwebp($img);
  
        //$width = $im->getImageWidth();
        //$height = $im->getImageHeight();
        //
        //$st = $width>$height?$width:$height;
        //
        //$div = 1;
        //if($st>$max){
        //    $div = (int) ($st/$max);
        //}
        //
        //$newWidth = (int)($width/$div);
        //$newHeight = (int)($height/$div);
        //
        //$im->adaptiveResizeImage($newWidth, $newHeight);
        file_put_contents ($img, $im);
    }



    
//global $pdo;
//$dataFile = $_SERVER['DOCUMENT_ROOT'].'/admin/_dev/modules/subjects_of_geography/files/data.zip';
//
//$pathinfo = pathinfo($dataFile);
//
//$tempPath = uniqid();
//$dbDir = $pathinfo['dirname'].'/'.$tempPath;
//$dataFilename  = $pathinfo['filename'];
//
//$zip = new ZipArchive;
//$resZip = $zip->open($dataFile);
//if ($resZip !== TRUE) {
//    $res['success']=2;
//    $res['err'] = 'ошибка разархивирования файла '.$fileGet['file'].'. код:'.$resZip;
//    echo json_encode($res);
//    return;
//}
//
//$zip->extractTo($dbDir);
//$zip->close();
//
//$dataFiles = array_diff(scandir($dbDir), array('..', '.'));
//foreach($dataFiles as $dataFile)
//{
//    $file = file_get_contents($dbDir.'/'.$dataFile);
//    $file = json_decode($file, true);
//    $table = $file['table'];
//    $fields = $file['fields'];
//    $values = $file['values'];
//    
//    if(!count($values) || !count($values) || !$table)
//        continue;
//        
//    /** search first row
//     */
//    $search = [];
//    $valFirstRow = $values[0];
//    foreach($fields as $key=> $field)
//        $search[] = "`$field` = '{$valFirstRow[$key]}'";
//    
//    
//    if(singleQuery('SELECT * FROM `'.$table.'` WHERE '.implode(' AND ', $search))) continue;
//    
//    $fields = '`'.implode('`, `', $fields).'`';
//    $insert = [];
//    
//    foreach($values as $key => $value)
//        $insert[] = '( \''.implode("','", $value).'\')';
//
//    $stmt = $pdo->prepare( 'INSERT INTO `'.$table.'` ( '.$fields.' ) VALUES '. implode(',', $insert) );
//    $stmt->execute([]);
//
//}





