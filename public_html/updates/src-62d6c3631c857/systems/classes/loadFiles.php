<?

namespace systems\classes\loadFiles;

class loadFiles
{



    function deleteFilegroup($group_id) {
        $files = arrayQuery('SELECT small_size, medium_size, large_size FROM `files` WHERE id_group = ?',[$group_id]);
        foreach($files as $file){
            if($file['small_size']) unlink( $_SERVER['DOCUMENT_ROOT'].'/'.$file['small_size'] );
            if($file['medium_size']) unlink( $_SERVER['DOCUMENT_ROOT'].'/'.$file['medium_size'] );
            if($file['large_size']) unlink( $_SERVER['DOCUMENT_ROOT'].'/'.$file['large_size'] );
        }
        query('DELETE FROM `files` WHERE id_group = ?',[$group_id]);
    }



# функция получения местонахождения файла
    private function getPhotoPath($file_id) {
        global $tgHost;
        $getFile = curl()->post($tgHost.'/getFile', ['file_id' => $file_id]);
        $res = json_decode($getFile,true);

        if(pathinfo($res['result']['file_path'], PATHINFO_EXTENSION)=='htaccess') {
            tgMess('Предупреждение! Вы попытались загрузить файл .htaccess, администратору приложения отправлено уведомление');
            return false;
        }
        return  $res['result']['file_path'];
    }

    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    public function getFileFromId($file_id) {
        $name = uniqid();
        $file = $this->copyFile($this->getPhotoPath($file_id), $_SERVER['DOCUMENT_ROOT'].'/files/downloads', $name);

        $fileDir = $_SERVER['DOCUMENT_ROOT'].'/files/downloads/'.$file;

        return [
            'dir'=> $fileDir
            ,'link'=> _dir_.'/files/downloads/'.$file
        ];

    }
# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

    # копируем документ в папку
    private function copyFile($file_path, $save_dir, $file_name) {

        # ссылка на файл в телеграме
        $file_from_tgrm = "https://api.telegram.org/file/bot".$GLOBALS['Telegram_botkey']."/".$file_path;
        # достаем расширение файла
        $ext =  end(explode(".", $file_path));
        $link = "$save_dir/$file_name.$ext";
        # назначаем свое имя здесь $file_name.расширение_файла
        if(copy($file_from_tgrm, $link))
            return "$file_name.$ext";
        else
            return '';
    }

    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


    # сохраняем файл, делаем запись в базе
    public function saveVideo($data, $id_file='') {
        $data = json_decode(json_encode($data), true);
        $name = uniqid(); # часть имени файла
        $video = $this->copyFile($this->getPhotoPath($data['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/documents', 'doc_'.$name);

        if(!$video)
            return false;

        $video='files/documents/'.$video;
        $insertId = insertQuery('INSERT INTO files (id_file, type_file, small_size, medium_size, large_size, id_group) VALUES (:id_file, "video", :file, NULL, NULL, -1)', [ ':id_file'=> $data['file_id'], ':file'=> $video]);

        return [ 'file'=> $video ,'id_file'=> $insertId ];
    }


    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


    # сохраняем файл, делаем запись в базе
    public function saveDocument($data, $id_file='') {
        $data = json_decode(json_encode($data), true);
        $name = uniqid(); # часть имени файла
        $doc = $this->copyFile($this->getPhotoPath($data['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/documents', 'doc_'.$name);

        if(!$doc)
            return false;

        $doc='files/documents/'.$doc;
        $insertId = insertQuery('INSERT INTO files (id_file, type_file, small_size, medium_size, large_size, id_group) VALUES (:id_file, "doc", :file, NULL, NULL, -1)', [ ':id_file'=> $data['file_id'], ':file'=> $doc]);

        return [ 'file'=> $doc ,'id_file'=> $insertId ];
    }
    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    public function getPhotoFromId($file_id) {
        $name = uniqid();
        $photo = $this->copyFile($this->getPhotoPath($file_id), $_SERVER['DOCUMENT_ROOT'].'/files/images', $name);


        $photoDir = $_SERVER['DOCUMENT_ROOT'].'/files/images/'.$photo;

        return [
             'dir'=> $photoDir
            ,'link'=> _dir_.'/files/images/'.$photo
            ,'shortlink'=> 'files/images/'.$photo
        ];
    }
    # ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    # сохраняем фото, делаем запись в базе
    public function savePhoto($data, $id_file='') {
        $data = json_decode(json_encode($data), true);
        $name = uniqid(); # часть имени файла

        $small = $this->copyFile($this->getPhotoPath($data[0]['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/images', 'small_'.$name);
        $medium = $this->copyFile($this->getPhotoPath($data[1]['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/images', 'medium_'.$name);
        if($data[2])
            $large = $this->copyFile($this->getPhotoPath($data[2]['file_id']), $_SERVER['DOCUMENT_ROOT'].'/files/images', 'large_'.$name);
        else
            $large = $medium;

        if(!$small && !$medium && !$large)
            return false;

        if(!$medium) $medium = $large?$large:$small;
        if(!$large) $large = $medium?$medium:$small;
        $small='files/images/'.$small;
        $medium='files/images/'.$medium;
        $large='files/images/'.$large;
        $insertId = insertQuery('INSERT INTO files (id_file, type_file, small_size, medium_size, large_size, id_group) VALUES (:id_file, "img", :small_size, :medium_size, :large_size, -1)', [ ':id_file'=> $id_file, ':small_size'=> $small, ':medium_size'=> $medium, ':large_size'=> $large ]);

        return [ 'file'=> $medium ,'id_file'=> $insertId ];
    }


    public function getFileGroup() {
        return singleQuery("SELECT IFNULL(max(id_group),0)+1 result FROM `files`")['result'];
    }

    /**
     * Получить массив файлов для веба
     */
    public function getFilesforweb($id_group) {
        if(!$id_group||$id_group==="0"||$id_group==="false") return [];

        if($id_group=='all'){
            $arr=arrayQuery('SELECT * FROM `files` ORDER BY id DESC');
        } else {
            $arr=arrayQuery('SELECT * FROM `files` WHERE id_group = ?', [ $id_group ]);
        }

        return array_map(function($it) {
            $preview = $it['small_size']?:($it['medium_size']?:$it['large_size']);
            $file =    $it['large_size']?:($it['medium_size']?:$it['small_size']);
            $ext=strtolower(pathinfo($file, PATHINFO_EXTENSION));
            switch($ext){
                case 'mov':
                    $it['type_file'] = 'doc';
                    break;
            }
            return [
                'id_group'=> $it['id_group']
                ,'preview'=> strpos($preview, 'http')===false? '/'.$preview : $preview
                ,'file'=> strpos($file, 'http')===false? '/'.$file : $file
                ,'fileid'=> $it['id']
                ,'type'=> $it['type_file']
                ,'ext'=> $ext
                ,'lg'=> $it['large_size']
                ,'md'=> $it['medium_size']
                ,'sm'=> $it['small_size']
            ];
        }, $arr);
    }













}