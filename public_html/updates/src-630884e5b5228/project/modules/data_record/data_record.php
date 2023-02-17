<?php

namespace project\modules\data_record;

class data_record
{
    public function start( array $par=[] )
    {
        global $chat_id, $mysqli;
        $data_record = json_decode($par['data_record'], true);
        $sql_data = [];
        $output_messages_data = [];

        $mainVariables = $par;
        $parForOutput = []; # параметры для передечи в переменную с этим шагом в $par
        if (isset($data_record['db_list'])) {
            foreach ($data_record['db_list'] as $r) {
                $fields = '';
                $values = '';
                $uniqueFields = [];
                $uniqueFields = [];
                foreach ($r['cells'] as & $tCell) {
                    $fields = $fields ? $fields . ",`{$tCell['field']}`" : "`{$tCell['field']}`";

                    $value = $tCell['val'];

                    $mainVariables = array_merge($mainVariables, text()->substitution_of_the_global_variables($value)['variables']);
                    $value = text()->variables($value, $par);

                    $value = text()->shortcodes($value, $par);

                    if ($tCell['unique'] == '1') array_push($uniqueFields, ['field' => $tCell['field'], 'value' => $value]);

                    $value = $mysqli->escape_string($value);
                    $values = $values ? $values . ",'$value'" : "'$value'";
                    $tCell['res_val'] = $value;
                }
                $sql = "INSERT INTO `{$r['table']}` ($fields) VALUES($values)";
                array_push($sql_data, ['sql_query' => $sql, 'table' => $r['table'], 'uniqueFields' => $uniqueFields, 'method' => 'insert', 'id_row' => 0, 'initial_data' => $r]);
            }
        }

        $reply_to_message = $par['reply_to_message'];
        $reply_to_message = $reply_to_message['message_id'];


        if (isset($data_record['output-data'])) {
            foreach ($data_record['output-data'] as $d) {

                if ($chat_name=$d['chat_name']) {
                    if ($chat_name == 'this') $id_chat = $chat_id;
                    else {
                        $chatInfo = get_chat(['techname' => $chat_name]);
                        if ($chatInfo['success'] == 1)
														$id_chat = $chatInfo['id_chat'];
                        else
														$id_chat = text()->variables($chat_name, $par);
                        # else
                        #     return tgMess('не найден чат для вывода данных');

                    }
                }

                $body = $d['message'];
                $mainVariables = array_merge($mainVariables, text()->substitution_of_the_global_variables($body)['variables']);

                $body = text()->variables($body, $par);
                $body = text()->shortcodes($body, $par);
                $wildcard_values = ['body' => $d['message']];

                $send_mess_par = ['body' => $body, 'id_chat' => $id_chat, 'chatName' => $chat_name /* для inline */, 'reply_to_message_id' => $reply_to_message];
                $files = $d['files'];



                if ($keyboard = keyboards()->getKeyboard($d['keyboard'])) {
                    $keyboard = text()->substituteVariablesInAnArray( $keyboard, $par );
                    $send_mess_par['kb'] = $keyboard;
                }
                if ($files = text()->variables($files, $par)) $send_mess_par['files'] = $files;




                $send_mess_par['wildcard_values'] = $wildcard_values;
                array_push($output_messages_data, $send_mess_par);


            }
        }

        $moder_mode = false;
        $moderation = $data_record['moderation'];
        if (isset($moderation['moder_mode']) && $moderation['moder_mode'] == 1) {
            $moder_mode = true;
            $moder_chat_name = $moderation['moder_chat_name'];
            $moder_chat_name = text()->variables($moder_chat_name, $par);
            $moder_chat_name = text()->shortcodes($moder_chat_name, $par);
            $message_for_moder = $moderation['message_for_moder'];
            $message_for_moder = text()->variables($message_for_moder, $par);
            $message_for_moder = text()->shortcodes($message_for_moder, $par);
            if(singleQuery('SELECT * FROM `usersAll` WHERE chat_id=?',[$moder_chat_name])){
                $id_moder_chat=$moder_chat_name;
            } else {
                if ($moder_chat_name == 'this') $id_moder_chat = $chat_id;
                else {
                    $get_chat = get_chat(['techname' => $moder_chat_name]);
                    if ($get_chat['success'] == 1) $id_moder_chat = $get_chat['id_chat'];
                    if (!$id_moder_chat) return tgMess(DIALTEXT('dataRecordNoChatFoundForSendingDataForModeration'));
                }
            }
        }
        
        $source_information = ['chat_id' => $chat_id]; # information about the source
        if (!$moder_mode) foreach ($output_messages_data as & $r) $r['data_of_sent_messages'] = send_mess($r);
        $data = ['output_messages_data' => $output_messages_data, 'sql_data' => $sql_data, 'source' => $source_information, 'blockchain' => $par['script_source'], 'moderblockchain'=> $data_record['moderblockchain']];
        $parForOutput['output_messages_data'] = $output_messages_data;
        $parForOutput['insert_data'] = [];
        $data_id = setData('');
        $previous_insert_id = [];
        if (!$moder_mode) for ($i = 0;$i < count($data['sql_data']);$i++) {
            $s = $data['sql_data'][$i];
            $table = $s['table'];
            $result = arrayQuery("SHOW COLUMNS FROM `$table`");
            $mess_field = false;
            foreach ($result as $row) if ($row['Field'] == 'link_data_id') $mess_field = true;
            if (!$mess_field) {
                query("ALTER TABLE `$table` ADD `link_data_id` INT(10) NOT NULL COMMENT 'system field';");
            }
            query("ALTER TABLE `$table` CHANGE `link_data_id` `link_data_id` INT NULL DEFAULT NULL COMMENT 'system field';");
            $unique = '';
            $uniqueFields = $s['uniqueFields'];
            foreach ($uniqueFields as $u) $unique = $unique ? $unique . " AND `{$u['field']}` = '{$u['value']}'" : "`{$u['field']}` = '{$u['value']}'";
            if ($unique) deleteQuery("DELETE FROM `$table` WHERE $unique");
            # подставляем insert_id предыдущих записей
            foreach ($previous_insert_id as $key => $val) $s['sql_query'] = str_replace($key, $val, $s['sql_query']);
            $insert_id = insertQuery($s['sql_query']);
            if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return tgmess($stmtErr);
            $data['sql_data'][$i]['id_row'] = $insert_id;
            $previous_insert_id[$table . '_insert_id'] = $insert_id;
            query("UPDATE $table SET `link_data_id` = $data_id WHERE id = $insert_id");
            # передаем полученные id
            $par[$table . '_insert_id'] = $insert_id;
            array_push($parForOutput['insert_data'], ['table' => $table, 'id' => $insert_id]);
        }


        foreach($mainVariables as $key=> $mainVariable)
            $mainVariables[$key] = $mainVariable;
        
        foreach(['type_step','intermediate_function','data_record','script_messages','callback_data','wildcardData','variable_data','message','step','id_step','script_step','script_source'] as $tt) unset($mainVariables[$tt]);

        $data['par'] = $mainVariables;

        updateData( $data_id, $data );

        if ($moder_mode) {
            foreach($data_record['moderation_keys'] as $moderation_key)
            $moderation_keys[$moderation_key['button']] = 1;
            $kb = [];
            foreach ($output_messages_data as $outputMsg) {
				$outputMsg['chatName'] = text()->variables($outputMsg['chatName'], $par);
								
                $chatName = singleQuery('SELECT chat_name FROM `chats` WHERE name = :chat', [':chat'=> $outputMsg['chatName'] ]);
                if ($chatName) $chatName = $chatName['chat_name'];
                else $chatName = $outputMsg['chatName'];
                if ($chatName == 'this') $chatName = DIALTEXT('dataRecordTheLabelOnTheSendNotificationToSenderButton');
                array_push($kb, [['text' => '✔️' . ' ' . $chatName, 'callback_data' => json_encode(['id' => $outputMsg['id_chat'], 'mtd' => 'dataRecord_chat', 'st' => 1]) ]]);
            }
            $keys = [];
            if ($moderation_keys['cancel'] == 1) array_push($keys, ['text' => '❌', 'callback_data' => json_encode(['data_id' => $data_id, 'mtd' => 'data_recording_cancel']) ]);
            if ($moderation_keys['edit'] == 1) array_push($keys, ['text' => '✏️', 'callback_data' => json_encode(['data_id' => $data_id, 'mtd' => 'data_edit_on_mode']) ]);
            if ($moderation_keys['notification'] == 1) array_push($keys, ['text' => '🔔', 'callback_data' => json_encode(['mtd' => 'data_public_sound']) ]);
            array_push($keys, ['text' => '✅', 'callback_data' => json_encode(['data_id' => $data_id, 'mtd' => 'data_record_from_moderation']) ]);
            array_push($kb, $keys);
            $kb = ["inline_keyboard" => $kb];
            $moderFiles = $moderation['files'];
            $moderFiles = text()->variables($moderFiles, $par);
            $moderFiles = text()->shortcodes($moderFiles, $par);
            
            $send_mess_par = ['id_chat' => $id_moder_chat, 'body' => $message_for_moder, 'files' => $moderFiles, 'kb' => $kb];
            send_mess($send_mess_par);
        }

        $par[$par['script_step']] = $parForOutput;
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
    }




public function data_record_from_moderation($par) {
    global $chat_id, $message_id, $original, $mysqli;
    $data_id = $par['data_id'];
    $data = getData( $data_id );
    if (!$data) return tgMess(DIALTEXT('dataRecordNoDataFound'));


    $output_messages_data = &$data['output_messages_data'];

    $disable_notification = false;
    $chats = [];
    $rm = $original['callback_query']['message']['reply_markup'];
    foreach ($rm['inline_keyboard'] as $trow) foreach ($trow as $t) {
        if ($t['text'] == '🔕') $disable_notification = true;
        $cbData = json_decode($t['callback_data'], true);
        if ($cbData['st'] == 1) array_push($chats, $cbData['id']);
    }
    $send_messages = [];
    foreach ($output_messages_data as & $r) {
        if (in_array($r['id_chat'], $chats)) {
            if ($disable_notification) $r['disable_notification'] = 1;
            $r['body'] = text()->variables($r['body'], $data['par'], false);
            $r['body'] = text()->shortcodes($r['body'], $data['par']);
						$r['id_chat'] = text()->variables($r['id_chat'], $data['par'], false);
            $t = send_mess($r);
            $r['data_of_sent_messages'] = $t;
            array_push($send_messages, $t);
        }
    }

    $tempPar = $data['par'];

    $previous_insert_id = [];
    for ($i = 0;$i < count($data['sql_data']);$i++) {
        $s = $data['sql_data'][$i];
        $table = $s['table'];
        $result = arrayQuery("SHOW COLUMNS FROM `$table`");
        $mess_field = false;
        foreach ($result as $row) if ($row['Field'] == 'link_data_id') $mess_field = true;
        if (!$mess_field) {
            query("ALTER TABLE `$table` ADD `link_data_id` INT(10) NOT NULL COMMENT 'system field';");
        }
        if ($s['method'] == 'insert') {
            $unique = '';
            $uniqueFields = $s['uniqueFields'];
            foreach ($uniqueFields as $u) $unique = $unique ? $unique . " AND `{$u['field']}` = '{$u['value']}'" : "`{$u['field']}` = '{$u['value']}'";
            if ($unique) deleteQuery("DELETE FROM `$table` WHERE $unique");
            $fields = '';
            $values = '';
            foreach ($s['initial_data']['cells'] as $cell) {

                $tVal = $cell['val'];


                $tVal = text()->variables($tVal, $tempPar, false);
                $tVal = text()->shortcodes($tVal, $tempPar);
                $tVal = $mysqli->escape_string($tVal);
                $fields = $fields ? $fields . ",`{$cell['field']}`" : "`{$cell['field']}`";
                $values = $values ? $values . ",'$tVal'" : "'$tVal'";


            }
            $s['sql_query'] = "INSERT INTO `{$s['initial_data']['table']}` ($fields) VALUES($values)";
            # подставляем insert_id предыдущих записей
            foreach ($previous_insert_id as $key => $val) {
                $s['sql_query'] = str_replace($key, $val, $s['sql_query']);
            }
            $insert_id = insertQuery($s['sql_query']);
            if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return tgmess($stmtErr);
            $data['sql_data'][$i]['id_row'] = $insert_id;
            $previous_insert_id[$table . '_insert_id'] = $insert_id;
            query("UPDATE $table SET `link_data_id` = $data_id WHERE id = $insert_id");
        }
    }
    updateData( $data_id, $data );

    $kb = ["inline_keyboard" => [[["text" => '❌', "callback_data" => json_encode(['data_id' => $data_id, 'mtd' => 'data_recording_cancel']) ]]]];
    methods()->edit_inline_keyboard($chat_id, $message_id, $kb);

    if ($send_messages[0][0]) {
        $messid = $send_messages[0][0]['message_id'];
        $chatid = $send_messages[0][0]['channel_id'];
        $chatname = $send_messages[0][0]['channel_name'];
        $sourceid = $data['source']['chat_id'];
		if($sourceid!=$chat_id){  # сами себе не отправляем уведомление
				$shortId = substr($chatid, 4, 50);
				if ($chatname) $link = "https://t.me/$chatname/$messid";
				else $link = "https://t.me/c/$shortId/$messid";
				if ($chatid == $sourceid) {
						$sm = ['body' => DIALTEXT('dataRecordTheDataIsApprovedByTheModerator', $sourceid), 'id_chat' => $sourceid];
						$sm['reply_to_message_id'] = $messid;
				} else {
						$sm = ['body' => str_replace('#link#', $link, DIALTEXT('dataRecordTheDataIsApprovedByTheModeratorWithALink', $sourceid)),
						'id_chat' => $sourceid];
				}
				send_mess($sm);
		}
        
    }
    
    if($data['moderblockchain']) go_to($data['moderblockchain'],false,$par);
}




    public function atEdtFld($callback_data) {
        
        tgmess(print_r($callback_data, true));
        
        $dataId = $callback_data['did'];
        $stepId = $callback_data['id'];
        $sql = singleQuery('SELECT * FROM `s_steps` WHERE id = :id', [':id' => $stepId]);
        if (!$sql) return notification(DIALTEXT('dataRecordNoFillConditionsFound'));
        
        tgmess(print_r($sql, true));
        
        $stepTechName = $sql['item'];
        $par = [];
        switch ($sql['type_step']) {
            case 'custom_function':
                $f = get_custom_function($sql['custom_function']);
                if ($f) $func = $f['funcName'];
                break;


            }
        
        $par = get_mess_for_distribution_module($sql['id'], $par);
        $par['dataId'] = $dataId;
        $par['script_source'] = 'editData';
        $par['script_step'] = $stepTechName;
        $par['step'] = $func;
        
        tgmess(print_r($par, true));
        
        //$func($par);
    }



    public function data_edit_on_mode($callback_data) {
        global $chat_id, $message_id;
        $dataId = $callback_data['data_id'];
        $data = getData( $dataId );
        if (!$data) return notification(DIALTEXT('dataRecordNoDataFound'));
        $blockchain = $data['blockchain'];
        if (!$blockchain) return notification(DIALTEXT('dataRecordDataManagementScriptIdNotFound'));
        $sql = arrayQuery("SELECT s.id, s.item, s.name, s.type_step FROM `constructors` c
            JOIN `s_steps` s ON s.source = c.id
            WHERE c.techname = :techname AND s.status = 1 AND user_editable = 1
            ORDER BY s.t_sort", [':techname' => $blockchain]);
        if (!$sql) return notification(DIALTEXT('dataRecordNoScriptDataFound'));
        $kb = [];
        foreach ($sql as $row) switch ($row['type_step']) {
            case 'category_group':
            case 'input_text':
            case 'input_phone':
            case 'input_email':
            case 'input_image':
                array_push($kb, [['text' => '✏️' . ' ' . $row['name'], 'callback_data' => json_encode(['mtd' => 'atEdtFld', 'id' => $row['id'], 'did' => $dataId]) ]]);
            break;
        }
        if (!count($kb)) return notification(DIALTEXT('dataRecordNoEditableFieldsFound'));
        array_push($kb, [['text' => backmark, 'callback_data' => 'return_the_keyboard']]);
        insertQuery("INSERT INTO `s_data_before_the_update` (`id_mess`, `id_chat`, `body`) VALUES (:id_mess, :id_chat, :body)", [':id_mess' => $message_id, ':id_chat' => $chat_id, ':body' => json_encode($obj) ]);
        $kb = ["inline_keyboard" => $kb];
        methods()->edit_inline_keyboard($chat_id, $message_id, $kb);
    }

    public function data_public_sound($callback_data) {
        global $original, $chat_id, $message_id;
        $rm = $original['callback_query']['message']['reply_markup'];
        foreach ($rm['inline_keyboard'] as & $trow) foreach ($trow as & $t) {
            if ($t['text'] == '🔔') {
                $t['text'] = '🔕';
                break;
            }
            if ($t['text'] == '🔕') {
                $t['text'] = '🔔';
                break;
            }
        }
        methods()->edit_inline_keyboard($chat_id, $message_id, $rm);
    }



public function dataRecord_chat($callback_data) {
    global $original;
    $rm = $original['callback_query']['message']['reply_markup'];
    $idChat = $callback_data['id'];
    foreach ($rm['inline_keyboard'] as & $trow) foreach ($trow as & $t) {
        $cbData = json_decode($t['callback_data'], true);
        $rmText = $t['text'];
        if ($cbData['id'] == $idChat) {
            if ($cbData['st'] == 1) {
                $cbData['st'] = 0;
                $rmText = str_replace('✔️', '', $rmText);
            } else {
                $cbData['st'] = 1;
                $rmText = '✔️' . ' ' . $rmText;
            }
            $t['callback_data'] = json_encode($cbData);
            $t['text'] = $rmText;
        }
    }
    methods()->edit_inline_keyboard($chat_id, $message_id, $rm);
}


public function data_recording_cancel($callback_data){
    global $chat_id, $message_id;
    $kb = [];
    if ($chat_id == get_admin(true)) {
        array_push($kb, [['text' => 'comment?' . commentmark, 'callback_data' => 'blockanswer'], ]);
        array_push($kb, [['text' => 'Yes', 'callback_data' => json_encode(['mtd' => 'data_delete_with_comment', 'data_id' => $callback_data['data_id']]) ],
                         ['text' => 'No',  'callback_data' => json_encode(['mtd' => 'data_delete_nocomment', 'data_id' => $callback_data['data_id']]) ], ]);
        $kb = ["inline_keyboard" => $kb];
        methods()->edit_inline_keyboard($chat_id, $message_id, $kb);
    } else {
        $this->delete_data($callback_data['data_id'], 'owner');
    }
}



public function data_delete_with_comment($callback_data){
    global $chat_id, $message_id;
    set_pos('data_delete_with_comment_listener', [dataId => $callback_data['data_id'], 'messageId' => $message_id]);
    tgMess('comment text');
}

public function data_delete_nocomment($callback_data){
    $this->delete_data($callback_data['data_id'], 'moderation');
}


public function data_delete_with_comment_listener($par){
    global $last, $text_message, $message_id;

    $last_position = $last['position'];
    $last_message_id = $last['message_id'];
    $last_par = json_decode($last['parameters'], true);
        $dataId = $last_par['dataId'];
        $message_id = $last_par['messageId'];
        $this->delete_data($dataId, 'moderation', $text_message, $message_id);
        set_pos('#the final function#');
}




public function delete_data($data_id, $source_method = 'owner' /* moderator */, $comment = '', $messageId = '') {
    global $chat_id, $message_id;
    $data = getData( $data_id );
    if (!$data) return notification(DIALTEXT('dataRecordNoDataFound'));

    foreach ($data['sql_data'] as $r) {
        switch ($r['method']) {
            case 'insert':
                deleteQuery("DELETE FROM `{$r['table']}` WHERE id = {$r['id_row']}");
            break;
        }
    }
    $source = $data['source'];
    $first = true;
    foreach ($data['output_messages_data'] as $r1) foreach ($r1['data_of_sent_messages'] as $r2) foreach ($r1['data_of_sent_messages'] as $r) {
        if ($first) {
            switch ($source_method) {
                case 'owner':
                    $ttext = DIALTEXT('dataRecordMessageAboutDeletingData');
                break;
                case 'moderation':
                    $ttext = DIALTEXT('dataRecordMessageAboutDataRejection', $source['chat_id']);
                break;
            }
            if ($comment) $ttext.= "\n".DIALTEXT('dataRecordReason', $source['chat_id'])."\n$comment";
            $sm = ['body' => $ttext, 'id_chat' => $source['chat_id']];
            $fm = methods()->forward_message($r['channel_id'], $r['message_id'], $source['chat_id']);
            $fm = json_decode($fm, true);
            if (isset($fm['result']['message_id'])) $sm['reply_to_message_id'] = $fm['result']['message_id'];
            send_mess($sm);
        }
        methods()->delete_mess($r['channel_id'], $r['message_id']);
        $first = false;
    }
    if ($first) {
        switch ($source_method) {
            case 'owner':
                $ttext = DIALTEXT('dataRecordMessageAboutDeletingData');
            break;
            case 'moderation':
                $ttext = DIALTEXT('dataRecordMessageAboutDataRejection', $source['chat_id']);
            break;
        }
        if ($comment) $ttext.= "\n".DIALTEXT('dataRecordReason', $source['chat_id'])."\n$comment";
        send_mess(['body' => $ttext, 'id_chat' => $source['chat_id']]);
    }
    if ($messageId) $message_id = $messageId;
    deleteData( $data_id );
    methods()->edit_inline_keyboard($chat_id, $message_id, ["inline_keyboard" => [[["text" => 'deleted', "callback_data" => 'blockanswer']]]]);
}
public function update_messages_from_data($data_id) {
    global $chat_id, $message_id;
    $data = getData( $data_id );
    if (!$data) return ['success' => false, 'err' => DIALTEXT('dataRecordNoDataFound')];
    $output_messages_data = $data['output_messages_data'];
    foreach ($output_messages_data as $r2) foreach ($r2['data_of_sent_messages'] as $r) methods()->delete_mess($r['channel_id'], $r['message_id']);
    foreach ($output_messages_data as & $r) $r['data_of_sent_messages'] = send_mess($r);
    $data['output_messages_data'] = $output_messages_data;
    updateData( $data_id, $data );

    return ['success' => true];
}
public function pinned_messages_from_data($data_id, $mode) {
    global $chat_id, $message_id;
    $data = getData( $data_id );
    if (!$data) return ['success' => false, 'err' => DIALTEXT('dataRecordNoDataFound')];

    foreach ($data['output_messages_data'] as $r2) foreach ($r2['data_of_sent_messages'] as $r)
    switch ($mode) {
        case 'pinned':
            pinned_mess($r['channel_id'], $r['message_id']);
        break;
        case 'unpinned':
            unpinned_mess($r['channel_id'], $r['message_id']);
        break;
    }
    return ['success' => true];
}
public function get_data_text_message($data_id, $par = []) {
    /* par
           linkFromChat - message from a specific chat
    */
    $data = getData( $data_id );
    if (!$data) return ['success' => false, 'err' => DIALTEXT('dataRecordNoDataFound')];
    $output_messages_data = $data['output_messages_data'][0]['data_of_sent_messages'];
    $files = $data['output_messages_data'][0]['files'];
    $send_messages = $data['output_messages_data'][0]['data_of_sent_messages'][0];
    if (isset($par['linkFromChat'])) {
        $linkFromChat = $par['linkFromChat'];
        foreach ($data['output_messages_data'] as $omd) foreach ($omd['data_of_sent_messages'] as $dsm) if ($dsm['channel_id'] == $linkFromChat) {
            $channel_id = $dsm['channel_id'];
            $channel_name = $dsm['channel_name'];
            $mesgId = $dsm['message_id'];
            break;
        }
    } else {
        $channel_id = $send_messages['channel_id'];
        $channel_name = $send_messages['channel_name'];
        $mesgId = $send_messages['message_id'];
    }
    $shortId = substr($channel_id, 4, 50);
    if ($channel_name) $link = "https://t.me/$channel_name/$mesgId";
    else $link = "https://t.me/c/$shortId/$mesgId";
    $body = $data['output_messages_data']['body'];
    return ['success' => true, 'body' => $body, 'files' => $files, 'link' => $link, 'channel_id' => $channel_id, 'channel_name' => $channel_name, 'mesgId' => $mesgId];
}



public function edit_data_moder($par) {
    $dataId = $par['dataId'];
    $data = getData( $dataId );
    if (!$data) return notification(DIALTEXT('dataRecordNoDataFound'));

    $script_step = $par['script_step'];
    $output_messages_data = [];
    foreach ($data['output_messages_data'] as $mess) array_push($output_messages_data, ['chatId' => $mess['id_chat'], 'mask' => $mess['wildcard_values']['body']]);
    foreach ($data['variables'] as & $var) if ($var['var'] == $script_step) $var['val'] = $par[$script_step];
    $p = post(_dir_ . '/admin/functions/dataEdit.php', ['variables' => json_encode($data['variables']), 'dataId' => $dataId, 'chatListFromPage' => json_encode($output_messages_data) ]);
    send_mess(['body' => DIALTEXT('dataRecordDataChanged'), 'kb' => ["remove_keyboard" => true]]);
    set_pos('#the final function#');
}
public function edit_data_moder2($par) {
    $dataId = $par['dataId'];
    $data = getData( $dataId );
    if (!$data) return notification( DIALTEXT('dataRecordNoDataFound') );

    $script_step = $par['script_step'];
    foreach ($data['variables'] as & $edVar) if ($edVar['var'] == $script_step) $edVar['val'] = $par[$script_step];
    //$wildcard_data=[];
    // foreach($data['variables'] as $var)
    //     array_push($wildcard_data, ['res_var'=>$var['val'], 'name_var'=>$var['var'], 'type'=>$var['type']]);
    $wildcard_data = [];
    foreach ($par as $key => $r) if (!is_array($r)) {
        $rv = $par['variable_data'][$key];

        array_push($wildcard_data, ['res_var' => $r, 'name_var' => $key, 'type' => 'text', 'srcData' => $rv]);
    }
    foreach ($data['output_messages_data'] as & $edMess) {
        $wildcard_values = $edMess['wildcard_values'];
        $mask = $wildcard_values['body'];
        $wldcrdData = text()->wildcard_data($mask, $wildcard_data);
        $body = $wldcrdData['body'];
        $body = text()->shortcodes($body);
        $edMess['body'] = $body;
    }
    updateData( $dataId, $data );
    send_mess(['body' => DIALTEXT('dataRecordDataChanged'), 'kb' => ["remove_keyboard" => true]]);
    set_pos('#the final function#');
}





}
