<?php
namespace project\modules\input_files;

class input_files
{

    public function start(array $par = [])
    {
        $settings = json_decode($par['input_files'], true);

        foreach ($settings as & $tSetting)
        {
            $tSetting = text()->variables($tSetting, $par);
            $tSetting = text()->shortcodes($tSetting, $par);
        }

        if ($settings['join']) $settings['files'] = arrayQuery('SELECT `id` AS `id_file`, `small_size` AS `file` FROM `files` WHERE id_group = ?', [$settings['join']]);

        if ($tKb = keyboards()->getKeyboardFromSystemMess($par)) $settings['kb'] = $tKb;

        $par['input_files'] = $settings;

        if (!$par = echo_message_from_par($par)) return;
        set_pos($par['step'], $par); # listener
        return true;
    }

    public function listener(array $par = [])
    {
        global $obj, $chat_id, $text_message;

        $filesText = $text_message ? : $obj['message']['caption'];

        $sendMess = false;
        

        if (!intermediate_function($par)) return;

        $script_step = $par['script_step'];

        $settings = $par['input_files'];
        
        $only_file_id = $settings['only_file_id']??false;
        $hide_process = $settings['hide_process']??false;
        
        
        $sendBtnLabel = 'Отправить файлы';
        if ($settings['labelOnTheButtonSendFile'])
        {
            $sendBtnLabel = $settings['labelOnTheButtonSendFile'];
        }

        $input_text = $settings['input_text'];

        if ($text_message == $sendBtnLabel)
        { # если нажали на кнопку, отправляющую файлы на сервер (окончание)
            $group = loadFiles()->getFileGroup();

            foreach ($settings['files'] as $fileItem) query('UPDATE `files` SET `id_group` = :id_group  WHERE `id` = :id', [':id_group' => $group, ':id' => $fileItem['id_file']]);

            $par[$script_step] = $group;

            unset($par['input_files']);
            return the_distribution_module($par['script_source'], $par);
        }

        $skip_commands = preg_split("/\\r\\n?|\\n/", $settings['skip_commands']);
        $skip_commands = array_values($skip_commands); # убирает пустые строки
        if (!$settings['skip_commands']) $skip_commands = ['Пропустить'];

        if (array_search($text_message, $skip_commands) !== false)
        {
            $par[$script_step] = '';
            unset($par['input_files']);
            return the_distribution_module($par['script_source'], $par);
        }

        $document = $obj['message']['document'] ?? [];
        $photo = $obj['message']['photo'] ?? [];
        $video = $obj['message']['video'] ?? [];
        $audio = $obj['message']['audio'] ?? [];
        if (!isset($settings['files'])) $settings['files'] = [];
        if ($settings['limit'] > 0 && count($settings['files']) >= $settings['limit'] && ($document || $photo || $video))
        {
            tgMess('Лимит загрузки файлов: ' . $settings['limit']);
            return;
        }
        
        if(!$hide_process) {
            $sm = send_mess(['body' => DIALTEXT('inputFilesUploadingAFile') , 'id_chat' => $chat_id]);
            $id_system_mess = $sm[0]['message_id'];
        }


        # когда загружают несколько файлов, бот сначала их загружает, потом обрабатывает, поэтому, чтобы потом сообщения не вываливались разом, контролируем это
        $mdGrpId = ['number' => 0, 'status' => 0];
        if (isset($obj['message']['media_group_id']))
        {
            $media_group_id = 'media_group_id_' . $obj['message']['media_group_id'];
            if (isset($par[$media_group_id])) $par[$media_group_id]['number']++;
            else $par[$media_group_id] = ['number' => 1, 'status' => 0];

            $mdGrpId = $par[$media_group_id];
        }

        $kb = [[["text" => $sendBtnLabel]]];
        if ($settings['kb']) $kb = array_merge($kb, $settings['kb']);

        foreach ($kb as $k1 => $rkb) foreach ($rkb as $k2 => $kkb) if (array_search($kkb['text'], $skip_commands) !== false) unset($kb[$k1][$k2]);

        $keyboard = ["keyboard" => $kb, "one_time_keyboard" => false, "resize_keyboard" => true];

        $textPass = false;
        if ($input_text && $filesText != "")
        {
            if ($par[$script_step . '_text'] != $filesText)
            {
                $par[$script_step . '_text'] = $filesText;
                $sendMess = 'Текст добавлен';
                if(!$hide_process) methods()->edit_message('Текст добавлен', '', $chat_id, $id_system_mess);
            }
            $textPass = true;
        }

        if (!count($photo) && !count($document) && !count($video) && !count($audio) && !$textPass)
        {
            methods()->delete_mess($chat_id, $id_system_mess);
            tgMess(DIALTEXT('inputFilesSendTheFiles'));
            return;
        }

        if (count($photo) || count($document) || count($video) || count($audio))
        {

            if (count($settings['files']) == 10)
            {
                methods()->delete_mess($chat_id, $id_system_mess);

                if ($mdGrpId['number'] < 2 || $mdGrpId['status'] != 3) tgMess(DIALTEXT('inputFilesTheMaximumNumberOfFilesIs10'));

                if ($media_group_id) $par[$media_group_id]['status'] = 3;
                set_pos($par['step'], $par);

                return;
            }

            if(!$hide_process) methods()->edit_message(DIALTEXT('inputFilesSearchForAGroupOfFilesInAGroup') , '', $chat_id, $id_system_mess);

            if(!$hide_process) methods()->edit_message(DIALTEXT('inputFilesSendingAFileToTheServer') , '', $chat_id, $id_system_mess);
            
            if($only_file_id){
                $save_info=false;
                $slq='INSERT INTO `files` (`id_group`, `small_size`, `type_file`, `id_file`) VALUES (0,"",?,?)';
                if (count($photo)) $fid = query($slq, [ 'img', $photo[count($photo)-1]['file_id'] ]);
                if (count($document)) $fid = query($slq, [ 'img', $document['file_id'] ]);
                if (count($video)) $fid = query($slq, [ 'img', $video['file_id'] ]);
                if (count($audio)) $fid = query($slq, [ 'img', $audio['file_id'] ]);
                if($fid)  $save_info=[ 'file'=> '', 'id_file'=> $fid  ];
            } else {
                if (count($photo)) $save_info = loadFiles()->savePhoto($photo, $photo[count($photo)-1]['file_id']);
                if (count($document)) $save_info = loadFiles()->saveDocument($document, $document['file_id']);
                if (count($audio)) $save_info = loadFiles()->saveDocument($audio, $audio['file_id']);
                if (count($video)) $save_info = loadFiles()->saveVideo($video, $video['file_id']);
            }
            
            if ($save_info === false) return $hide_process ?
                tgMess(DIALTEXT('inputFilesUploadingAFileAnErrorOccurredWhileUploadingTheFileToTheServer')):
                methods()->edit_message(DIALTEXT('inputFilesUploadingAFileAnErrorOccurredWhileUploadingTheFileToTheServer') , '', $chat_id, $id_system_mess);

            if(!$hide_process) methods()->delete_mess($chat_id, $id_system_mess);

            $settings['files'][] = $save_info;

            if (count($photo))
            {
                if ($mdGrpId['number'] < 2 || $mdGrpId['status'] != 1) $sendMess = str_replace('#sendBtnLabel#', $sendBtnLabel, DIALTEXT('inputFilesPhotoAddedSuccessfully'));
                if ($media_group_id) $par[$media_group_id]['status'] = 1;

            }

            if (count($document))
            {
                if ($mdGrpId['number'] < 2 || $mdGrpId['status'] != 1) $sendMess = str_replace('#sendBtnLabel#', $sendBtnLabel, DIALTEXT('inputFilesFileAddedSuccessfully'));
                if ($media_group_id) $par[$media_group_id]['status'] = 1;
            }

            if (count($audio))
            {
                if ($mdGrpId['number'] < 2 || $mdGrpId['status'] != 1) $sendMess = str_replace('#sendBtnLabel#', $sendBtnLabel, DIALTEXT('inputFilesFileAddedSuccessfully'));
                if ($media_group_id) $par[$media_group_id]['status'] = 1;
            }


            if (count($video))
            {
                if ($mdGrpId['number'] < 2 || $mdGrpId['status'] != 1) $sendMess = str_replace('#sendBtnLabel#', $sendBtnLabel, DIALTEXT('inputFilesVideoAddedSuccessfully'));
                if ($media_group_id) $par[$media_group_id]['status'] = 1;
            }

        }

        methods()->delete_mess($chat_id, $id_system_mess);

        if ($settings['limit'] > 0 && $settings['files'] >= $settings['limit'] && $settings['auto_send'])
        {
            $group = loadFiles()->getFileGroup();

            foreach ($settings['files'] as $fileItem) query('UPDATE `files` SET `id_group` = ?  WHERE `id` = ?', [ $group, $fileItem['id_file']]);

            $par[$script_step] = $group;
            unset($par['input_files']);
            the_distribution_module($par['script_source'], $par);
            return;
        }

        send_mess(["body" => $sendMess, "kb" => $keyboard]);

        $par['input_files'] = $settings;
        set_pos($par['step'], $par);

        return true;
    }

}
