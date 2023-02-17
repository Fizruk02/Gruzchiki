<?

namespace project\modules\chat;

use systems\classes\keyboards\keyboards as keyboards;
use telegram\methods\methods as methods;
use systems\classes\text\text as text;
use systems\curl\curl as curl;
use systems\db\db as db;
class chat
{
    public function start( array $par=[] )
    {
        global $original, $chat_id, $username, $message_id, $text_message, $user_settings;
        $settings = json_decode($par['chat'], true);
        foreach($settings as &$tSetting){
						$tSetting = text::variables($tSetting, $par);
						$tSetting = text::shortcodes($tSetting, $par);
				}


        $chat_2 = $settings['id_chat'];

        if(!$chat_2)
            return err(DIALTEXT('chatTheChatIdOfTheInterlocutorWasNotFound'));

        $id_chat = insertQuery('INSERT INTO `m_chats` (`id`, `t_date`, `created`) VALUES (NULL, CURRENT_TIMESTAMP, :id_user)', [':id_user'=> $chat_id]);
        if(!$id_chat)
            return err(DIALTEXT('chatErrorCreatingARecordInThe_m_chats_Table'));

        $id_us = insertQuery('INSERT INTO `m_chats_users` (`id_chat`, `id_user`) VALUES (:id_chat, :id_user)', [ ':id_chat'=> $id_chat, ':id_user'=> $chat_id ]);
        $id_us2 = insertQuery('INSERT INTO `m_chats_users` (`id_chat`, `id_user`) VALUES (:id_chat, :id_user)', [ ':id_chat'=> $id_chat, ':id_user'=> $chat_2 ]);
        if(!$id_us || !$id_us2)
            return err(DIALTEXT('chatErrorCreatingARecordInThe_m_chats_users_Table'));

        $par['id_chat_bridge'] = $id_chat;
    //    $mess = $par['script_messages'][0]['message'];
    //    if($mess){
    //        $dial = dial($mess);
    //        $body = $dial['body'];
    //        $body = text::variables($body, $par);
    //    }
//
    //    if(!$body)
    //        $body = $text_message;
    //
    //    if(!$body)
    //        return err('Отсутствует содержимое сообщения');
    //
    //
    //    $tKb = keyboards::getKeyboardFromSystemMess($par);
    //    if($tKb)
    //    $kb  = $tKb;
    //
    //
        tgMess(DIALTEXT('chatSendAMessage'));

        set_pos($par['step'], $par);
        return true;
    }

    public function chatAnswerChat($par){
        $num_chat = $par['id_chat'];
        $chat = singleQuery('SELECT * FROM `m_chats` WHERE id = :id', [ ':id'=> $num_chat ]);
        if($chat['status']==3)
            return notification('Это обращение уже закрыто, создайте новое');
            
        tgMess(DIALTEXT('chatSendAMessage'));
        $par['id_chat_bridge'] = $num_chat;
        $par['answer'] = '1';
        set_pos('chat', $par);
    }

    public function chatCloseChat($par){
        $num_chat = $par['id_chat'];
        $chat = singleQuery('SELECT * FROM `m_chats` WHERE id = :id', [ ':id'=> $num_chat ]);
        $created = $chat['created'];
        if($chat['status']==3)
            return notification('Это обращение уже закрыто, создайте новое');
        $users = arrayQuery('SELECT id_user FROM `m_chats_users` WHERE id_chat = :id_chat AND id_user <> :created', [ ':id_chat'=> $num_chat, ':created'=> $created ]);
        foreach($users as $user)
			send_mess(['id_chat'=> $user['id_user'], 'body'=> 'Обращение '.$num_chat.' закрыто']);
    
        query('UPDATE `m_chats` SET `status` = 3 WHERE `id` = :id', [ ':id'=> $num_chat ]);
        $sm = ['☹️','😟','😐','🙂','😀'];
        $kb=[];
        for($i=0;$i<5;$i++)
        $kb[] =  ['text' => $sm[$i], 'callback_data' => json_encode(['mtd'=> 'chatEvaluation', 'id_chat'=> $num_chat, 'val'=> $i+1])];
				

        $kb=["inline_keyboard"=>[$kb]];
        send_mess(['id_chat'=> $created, 'body'=> 'Обращение '.$num_chat.' закрыто, пожалуйста оцените работу сотрудника', 'kb'=> $kb]);
    
    }
    
    public function chatEvaluation($par){
        global $chat_id, $message_id;
        $num_chat = $par['id_chat'];
        $val = $par['val'];
        query('UPDATE `m_chats` SET `evaluation` = :val WHERE `id` = :id', [ ':id'=> $num_chat, ':val'=> $val ]);
        methods::edit_message('Спасибо за оценку!', false, $chat_id, $message_id);
        
    }

    public function listener( array $par=[] )
    {
        global $original, $chat_id, $username, $message_id, $text_message, $user_settings, $obj;

        $num_chat = $par['id_chat_bridge'];
        $users = arrayQuery('SELECT id_user FROM `m_chats_users` WHERE id_chat = :id_chat AND id_user <> :id_user', [':id_chat'=> $num_chat, ':id_user'=> $chat_id]);

        $kbKeys=[];

				

        $kbKeys[] = [
            ['text' => DIALTEXT('chatToAnswer'), 'callback_data' => json_encode(['mtd'=> 'chatAnswerChat', 'id_chat'=> $num_chat])]
        ];
				
				
        $kbKeys[] = [
            ['text' => ' Закрыть обращение', 'callback_data' => json_encode(['mtd'=> 'chatCloseChat', 'id_chat'=> $num_chat])]
        ];
				
        $kb=["inline_keyboard"=>$kbKeys];


				$caption = '<code>'.DIALTEXT('chatChatNum').' '.$num_chat.'</code>'.PHP_EOL;
        foreach($users as $user){
            
			if(isset($original['message']['text'])){

					send_mess(['id_chat'=> $user['id_user'], 'body'=> $caption.$original['message']['text'], 'kb'=> $kb]);
			} elseif(
			    isset($original['message']['photo']) ||
			    isset($original['message']['video']) ||
			    isset($original['message']['document']) ||
			    isset($original['message']['audio']) ||
			    isset($original['message']['voice']) ||
			    isset($original['message']['sendMediaGroup'])
			    
			) {
                $copyMessage = methods::copyMessage($chat_id, $message_id, $user['id_user'], $kb, ['caption'=> $caption.$original['message']['caption'], 'parse_mode'=> 'html']);
                methods::error($copyMessage);
			} else {
			    # если номер обращения нельзя написать в заголовке текста, то пишем его в инлайн кнопке
			    # video_note, location, contact, dice и т.д.
		        $tkb = array_merge([[
                    ['text' => DIALTEXT('chatChatNum').' '.$num_chat, 'callback_data' => json_encode(['mtd'=> 'chatAnswerChat', 'id_chat'=> $num_chat])]
                ]], $kbKeys);
                $tkb=['inline_keyboard'=>$tkb];
				$copyMessage = methods::copyMessage($chat_id, $message_id, $user['id_user'], $tkb);
				# если отправлена эмоджи анимация, то отправляем значение
				if($dice = $original['message']['dice']){
				    send_mess(['id_chat'=> $user['id_user'], 'body'=> 'value: '.$dice['value']]);
				    
				}
			}

						

        }
        
        # если отправлена эмоджи анимация, то отправляем значение отправителю
        if($dice)
            tgMess('value: '.$dice['value']);

        insertQuery('INSERT INTO `m_chats_logs` (`id_chat`, `id_user`, `body`) VALUES (:id_chat, :id_user, :body)', [':id_chat'=> $id_chat, ':id_user'=> $chat_id, ':body'=> json_encode($original['message'])]);


        tgMess(DIALTEXT('chatMessageSent'));
        if(!intermediate_function($par)) return;
        unset($par['chat']);
        if(!isset($par['answer'])){
            set_pos($par['step'], $par);
            the_distribution_module($par['script_source'], $par);
        }
        else
        {
            set_pos('', []);
        }

        return true;
    }
}