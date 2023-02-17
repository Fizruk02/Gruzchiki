<?

namespace systems\classes\firstUserInfo;

class firstUserInfo
{
    public function create_record()
    {
        global $obj;
        if (($chat = $obj['message']['chat']) != '' && $chat['type'] == 'private') {
            $chat_id = $chat['id'];
        }

        if (!$chat_id || singleQuery('SELECT * FROM `usersAll` WHERE chat_id = ?', [ $chat_id ])) return;
        
        $language_code = $chat['language_code']??"";

        query("INSERT INTO `usersAll` (`photo`, `chat_id`, `username`, `first_name`, `lan`) VALUES (-10, ?,?,?,?);",
            [ $chat_id, $chat['username'] ?? '', $chat['first_name'], $language_code]);
    }

    public function user_logo(){
        global $obj;
        if (($chat = $obj['message']['chat']) != '' && $chat['type'] == 'private') {
            $chat_id = $chat['id'];
        }
        if (!$chat_id || !($us=singleQuery('SELECT * FROM `usersAll` WHERE chat_id = ?', [$chat_id]))) return;
        if($us['photo']!=-10) return;
        $photoGroup = -1;
        
        $profilePhoto = methods()->getUserProfilePhotos($chat_id);
        $arr = json_decode($profilePhoto);
        if ($arr->ok && $file_id = $arr->result->photos[0][0]->file_id) {
            $savePhoto = loadFiles()->savePhoto($arr->result->photos[0], $file_id);
            if ($savePhoto['id_file']) {
                $photoGroup = loadFiles()->getFileGroup();
                query('UPDATE files SET id_group = :id_group WHERE id = :id', [':id' => $savePhoto['id_file'], ':id_group' => $photoGroup]);
            }

        }
        
        query('UPDATE `usersAll` SET `photo`=? WHERE `chat_id`=?',[ $photoGroup,$chat_id ]);
    }

}
