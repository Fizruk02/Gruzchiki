<?php
namespace project\modules\yes_yes_yes;


class yes_yes_yes
{
    private $TWO_HOURS = 7200;
    private $THIRTY_MINUTS = 1800;

    //Старт класса
    public function start_yes(){
        $this->mailling_is_news();
        $this->mailling_is_approved();
        $this->mailling_is_new_order();
        $this->mailling_to_2h_30m();
        $this->mailling_notify();
    }


    //Основные процессы
    private function mailling_notify(){
        global $bot_hash, $Telegram_botkey;
        $user_order_data = arrayQuery('SELECT `orders_users`.*, `orders`.`cabinet_id`, `bot`.`hash`, `bot`.`bot_key`
                                       FROM `orders_users`
                                       JOIN `orders` ON `orders`.`id` = `orders_users`.`order_id`
                                       JOIN `bot` ON `bot`.`id`=`orders`.`bot_id`
                                       WHERE `orders_users`.`status` >= 4 AND `orders`.`status` = 1 AND `orders`.`active` = 1
                                         AND `orders`.`notify_at` IS NOT NULL AND `orders`.`notify_at` < '.time().'
                                         AND `orders_users`.`is_remember`=0 AND `orders_users`.`remember`=1');
        foreach ($user_order_data as $user_data) {
            $bot_hash = $user_data['hash'];
            $Telegram_botkey = $user_data['bot_key'];

            $text = "Вы назначены на заказ, ожидайте адрес для выезда";

            send_mess([
                'body' => $text,
                'id_chat' => $user_data['id_chat'],
            ]);

            query('UPDATE `orders_users` SET `is_remember` = 1 WHERE `id_chat` = ? AND `order_id` = ?', [ $user_data['id_chat'], $user_data['order_id'] ]);
        }
    }

    public function mailling_is_not_approved(){
        global $bot_hash, $Telegram_botkey;
        $orders = arrayQuery("SELECT * FROM `orders` WHERE status = 1 AND `active` = 1");

        foreach ($orders as $order){
            $need_employ = singleQuery("SELECT orders_values.value, orders_values.orders_id FROM `orders_values` JOIN `orders_fields`WHERE orders_fields.id = orders_values.orders_fields_id AND orders_fields.type = 'need_employ' AND orders_values.orders_id = ?", [ $order['id'] ]);
            $order_users_info = arrayQuery("SELECT * FROM `orders_users` WHERE `order_id` = ?", [ $order['id'] ]);
            if(!count($order_users_info)) continue;
            echo "#{$need_employ['orders_id']} Работников нужно: {$need_employ['value']}/";
            $approved = 0;
            $not_approveds = array();

            foreach ($order_users_info as $user_info){
                if($user_info['is_approved'] == 1) $approved++;
                else if($user_info['is_approved'] == 0) array_push($not_approveds, $user_info);
            }

            echo $approved;
            echo "\n";

            if($approved == (int)$need_employ['value']){
                foreach ($not_approveds as $not_approved){
                    $bot_id = $this->getOrder($not_approved['order_id'])['bot_id'];
                    $bot = singleQuery("SELECT `hash`, `bot_key` FROM `bot` WHERE `id` = ?", [ $bot_id ]);
                    $bot_hash = $bot['hash'];
                    $Telegram_botkey = $bot['bot_key'];
                    send_mess(['body' => 'Благодарим, за ваш отклик, но заказ забрали – ожидайте следующих!', 'id_chat' => $not_approved['id_chat']]);
                    query("UPDATE `orders_users` SET `is_approved` = -1 WHERE `id` = ?", [ $not_approved['id'] ]);
                }
            }
        }
        return true;
    }
    private function mailling_is_news(){
        global $bot_hash, $Telegram_botkey;
        $news_data = $this->getNews();

        foreach ($news_data as $data) {
            $bot_id = $data['bot_id'];
            $bot = singleQuery("SELECT `hash`, `bot_key` FROM `bot` WHERE `id` = ?", [ $bot_id ]);
            $users = arrayQuery("SELECT * FROM `users` WHERE `bot_id` = ? AND `is_deleted` != 1", [ $bot_id ]);
            $bot_hash = $bot['hash'];
            $Telegram_botkey = $bot['bot_key'];

            foreach ($users as $user){
                switch ($user['id_cms_privileges']){
                    case 2:
                        switch ($data['type_send']){
                            case 1:
                                send_mess(['body' => $data['description'], 'id_chat' => $user['id_chat']]);
                                break;
                            case 2:
                                if ($data['send_at'] <= time()) {
                                    send_mess(['body' => $data['description'], 'id_chat' => $user['id_chat']]);
                                }
                                break;
                        }
                        break;
                    case 3:
                        switch ($data['type_send']){
                            case 1:
                                send_mess(['body' => $data['title']."\n\n".$data['description'], 'id_chat' => $user['id_chat']]);
                                break;
                            case 2:
                                if ($data['send_at'] <= time()) {
                                    send_mess(['body' => $data['title']."\n\n".$data['description'], 'id_chat' => $user['id_chat']]);
                                }
                                break;
                        }
                        break;
                }
            }
            query("UPDATE `news` SET `status` = 1 WHERE `id` = ?", [ $data['id'] ]);
        }
    }
    private function mailling_is_approved(){
        global $bot_hash, $Telegram_botkey;
        $user_order_data = arrayQuery('SELECT `orders_users`.*, `orders`.`cabinet_id`, `bot`.`hash`, `bot`.`bot_key`
                                       FROM `orders_users`
                                       JOIN `orders` ON `orders`.`id` = `orders_users`.`order_id`
                                       JOIN `bot` ON `bot`.`id`=`orders`.`bot_id`
                                       WHERE `orders_users`.`status` = 4 AND `orders`.`status` = 1 AND `orders`.`active` = 1 AND `orders_users`.`is_approved`=0');

        foreach ($user_order_data as $user_data) {
            print_r($user_data);
            $bot_hash = $user_data['hash'];
            $Telegram_botkey = $user_data['bot_key'];

            $text = "Вы одобрены на выполнение заказа:\n";
            /*$data = $this->getOrderInfo($user_data['cabinet_id'], $user_data['order_id']);
            foreach($data as $title){
                if($title['name'] == 'Заголовок') $text .= $title['value'].' ';
                if($title['name'] == 'Район') $text .= $title['value'];
            }*/
            $data = $this->getOrderInfo($user_data['cabinet_id'], $user_data['order_id']);
            foreach($data as $title){
                if($title['is_first']) $text .= $title['value'].' ';
            }

            $text .= "\n".'Нажимайте, пожалуйста Кнопки состояния заказа:';

            send_mess([
                'body' => $text,
                'id_chat' => $user_data['id_chat'],
                'kb' => $this->getWorkerKeyboard($user_data['id_chat'], $user_data['order_id'])
            ]);

            query('UPDATE `orders_users` SET `is_approved` = 1 WHERE `id_chat` = ? AND `order_id` = ?', [ $user_data['id_chat'], $user_data['order_id'] ]);
        }
    }
    private function mailling_is_new_order(){
        global $bot_hash, $Telegram_botkey;

        $order_data = arrayQuery("SELECT `orders`.*, `bot`.`hash`, `bot`.`bot_key` FROM `orders`
                                    JOIN `bot` ON `orders`.`bot_id` = `bot`.`id`
                                    WHERE `status` = 0 AND `active` = 1
                                    AND `orders`.`bot_id` = `bot`.`id` AND `orders`.`type_send` >= 1");
        foreach ($order_data as $data) {
            //Рассылка админу о новом заказе
            /*$admins = $this->admin_users($data['id']);
            foreach ($admins as $admin) {
                $bot = singleQuery("SELECT `hash`, `bot_key` FROM `bot` WHERE `id` = ?", [ $admin['bot_id'] ]);
                $bot_hash = $bot['hash'];
                $Telegram_botkey = $bot['bot_key'];

                $text = '';
                $order_info = $this->getOrderInfo($data['cabinet_id'], $data['id']);

                foreach ($order_info as $title)
                    $text .= $title['name'].': '.$title['value']."\n";
                send_mess(['body' => $text, 'id_chat' => $admin['id_chat'] ]);
            }*/

            $bot_hash = $data['hash'];
            $Telegram_botkey = $data['bot_key'];

            $bot_id = $data['bot_id'];
            $order_mess = $this->getOrderMoreInfo($data['id'], $data['cabinet_id'], 0);
            if (($data['type_send'] == 1) || (($data['type_send'] == 2) && ($data['send_at'] <= time()))) {
                query("UPDATE `orders` SET `start_at` = ? WHERE id = ?", [time(), $data['id']]);
                $users = $this->users($bot_id);
                if (!count($users)) break;
                foreach ($users as $id) {
                    $user = singleQuery("SELECT * FROM `orders_users` WHERE `order_id` = ? AND user_id = ?", [ $data['id'], $id['id'] ]);
                    if ($user && ($user['status'] > 1)) continue;

                    if($id['status'] == 1) $kb = $this->get_yesNo_kb( ['txt' => 'Посмотреть', 'bool' => 1], ['txt' => 'Отказаться', 'bool' => -1], 0, $id['id_chat'], $data['id']);
                    else $kb = [];
                    $res = send_mess(['body' => $order_mess, 'id_chat' => $id['id_chat'], 'kb' => $kb]);
                    //file_put_contents(__DIR__.'/log.txt', print_r($res, true), FILE_APPEND);
                    $status = 0;
                    if (!empty($res) && !empty($res[0]) && isset($res[0]['message_id'])) $status = 1;
                    $this->setOrderUsers( $id['id'], $id['id_chat'], $data['id'], $status );
                }
                @query("UPDATE `orders` SET `status` = ?, `end_at` = ? WHERE id = ?", [1, time(), $data['id']]);
            }
        }
    }
    private function mailling_to_2h_30m(){
        global $bot_hash, $Telegram_botkey, $bot_id;

        $orders_data = arrayQuery("SELECT `orders`.*, `bot`.`hash`, `bot`.`bot_key` FROM `orders`
                                    JOIN `bot` ON `orders`.`bot_id` = `bot`.`id`
                                    WHERE `status` = 1 AND `active` = 1 AND `mailings_id` < 2");
        foreach($orders_data as $order){
            $bot_hash = $order['hash'];
            $Telegram_botkey = $order['bot_key'];
            $bot_id = $order['bot_id'];

            //$date = $this->convertDateTime($data['id'], $data['cabinet_id']);
            $date = $order['time_at'] - time();
            $users = [];
            if (($date <= $this->TWO_HOURS) && ($order['mailings_id'] == 0)) {
                $text = $this->getOrderMoreInfo($order['id'], $order['cabinet_id'], 2);
                $users = arrayQuery('SELECT * FROM `orders_users` WHERE `order_id` = ' . $order['id'].' AND `is_approved` = 1');
                foreach ($users as $user) {
                    if ($user['is_2hour']) {
                        send_mess(['body' => $text, 'id_chat' => $user['id_chat']]);
                        query("UPDATE `orders_users` SET `status` = 5 WHERE `id` = ".$user['id']);
                    }
                }
                query("UPDATE `orders` SET `mailings_id` = 1 WHERE `id` = {$order['id']}");
            }

            if (($date <= $this->THIRTY_MINUTS) && ($order['mailings_id'] == 1)) {
                $text = $this->getOrderMoreInfo($order['id'], $order['cabinet_id'], 3);
                $users = arrayQuery('SELECT * FROM `orders_users` WHERE `order_id` = ' . $order['id'].' AND `brigadier` = 1');
                foreach ($users as $user) {
                    if ($user['is_30min'])
                        send_mess(['body' => $text, 'id_chat' => $user['id_chat']]);
                }
                query("UPDATE `orders` SET `mailings_id` = 2 WHERE `id` = {$order['id']}");
            }
            /*$users = $this->users($data['bot_id']);
            switch ($data['mailings_id']){
                case 0:
                    if($date <= $this->TWO_HOURS){
                        $text = $this->getOrderMoreInfo($data['id'], $data['cabinet_id'], 2);
                        foreach ($users as $user) {
                            $is_approved = singleQuery("SELECT `is_approved` FROM `orders_users` WHERE `id_chat` = ? AND `order_id` = ?", [ $user['id_chat'], $data['id'] ])['is_approved'];
                            if($is_approved) send_mess(['body' => $text, 'id_chat' => $user['id_chat']]);
                        }
                        query("UPDATE `orders` SET `mailings_id` = 1 WHERE `id` = {$data['id']}");
                    }
                    break;
                case 1:
                    if($date <= $this->THIRTY_MINUTS){
                        $text = $this->getOrderMoreInfo($data['id'], $data['cabinet_id'], 3);
                        foreach ($users as $user) {
                            $is_approved = singleQuery("SELECT `is_approved` FROM `orders_users` WHERE `id_chat` = ? AND `order_id` = ?", [ $user['id_chat'], $data['id'] ])['is_approved'];
                            if($is_approved){
                                $is_brigadier = singleQuery("SELECT `brigadier` FROM `orders_users` WHERE `id_chat` = ? AND `order_id` = ?", [ $user['id_chat'], $data['id'] ]);
                                if(@$is_brigadier['brigadier'])
                                    send_mess(['body' => $text, 'id_chat' => $user['id_chat']]);
                            }
                        }
                        query("UPDATE `orders` SET `mailings_id` = 2 WHERE `id` = {$data['id']}");
                    }
                    break;
            }*/
        }
    }

    //Обработчики клавиатуры
    public function YesNo($input){
        global $original;
        $message = $original['callback_query']['message']['text'];
        // file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log.html', print_r($input,1));

        $orderId = (int) $input['oi'];
        $clientId = (int) $input['ci'];

        if ($input['num'] == 1 && $input['answer']==10) {
            $order = $this->getOrder( $orderId );
            $rules = singleQuery("SELECT `rules` FROM `cabinet` WHERE `id` = ?", [ $order['cabinet_id'] ])['rules'];
            send_mess(['body' => $rules ?? 'Правила', 'id_chat' => $clientId]);
            return;
        }
//tgMess(print_r($original, true));
//tgMess($input['num']);
        if ($input['num'] == 0) {
            methods()->delete_mess($clientId, $original['callback_query']['message']['message_id']);
        } else if (in_array($input['num'], range(1, 4)))
            methods()->editMsg([ 'text'=> $message ]);


            //methods()->editMsg([ 'text'=> $message.PHP_EOL.' '.PHP_EOL.'ОТВЕТ: <b>'.($input['answer']==1?'ДА':'НЕТ').'</b>' ]);

        /* пользователь нажал нет */
        if($input['answer'] == -1){
            send_mess(['body' => 'Заказ отклонен!', 'id_chat' => $clientId]);
            query("UPDATE `orders_users` SET `status` = -1 WHERE `id_chat` = ? AND `order_id` = ?", [ $clientId, $orderId ]);
            return 0;
        }

        if($input['answer']==0)
            return $this->setOrderMoreInfo($orderId, $clientId);

        switch ($input['num']) {
            case 0:
                /* Если заказ найден, то ставим статус 2 */
                $is_rejected = singleQuery("SELECT `status` FROM `orders_users` WHERE `id_chat` = ? AND `order_id` = ?", [ $clientId, $orderId ])['status'];
                if(isset($is_rejected) and $is_rejected == -1){
                    tgmess("Вы отказались от заказа!");
                    return false;
                }
                if ($this->setOrderMoreInfo($orderId, $clientId))
                    $this->setOrderUsersStatus($orderId, $clientId, 2);
                break;
            case 1:
                send_mess([
                    'body' => 'Вы ознакомились с правилами работы у нас?',
                    'id_chat' => $clientId,
                    'kb' => $this->get_yesNo_kb(['txt' => 'Да', 'bool' => 1], ['txt' => 'Нет', 'bool' => 0], 2, $clientId, $orderId),
                ]);
                break;
            case 2:
                send_mess(['body' => 'Вы ответственный работник? Вам можно доверять?',
                    'id_chat' => $clientId,
                    'kb' => $this->get_yesNo_kb(['txt' => 'Нет', 'bool' => 0], ['txt' => 'Да', 'bool' => 1], 3, $clientId, $orderId),
                ]);

                break;
            case 3:
                send_mess(['body' => 'Отправлю менеджеру ваше согласие на выполнение заказа?',
                    'id_chat' => $clientId,
                    'kb' => $this->get_yesNo_kb(['txt' => 'Да', 'bool' => 1], ['txt' => 'Нет', 'bool' => 0], 4, $clientId, $orderId),
                ]);
                break;
            case 4:
                send_mess([
                    'body' => 'Спасибо за заявку, если менеджер одобрит ее – вы получите об этом уведомление.',
                    'id_chat' => $clientId
                ]);
                $this->setOrderUsersStatus($orderId, $clientId, 3);
                break;
        }
    }

    /* Отправляем пользователю информацию по заказу с кнопками "Принять" и "Отклонить" */
    private function setOrderMoreInfo( $orderId, $clientId ){
        $order = $this->getOrder( $orderId );
        if(!$order){
            tgmess('Заказ не найден в базе');
            return false;
        }
        $cabinet_id = $order['cabinet_id'];
        $kb = $this->get_yesNo_kb( ['txt'=>'Принять', 'bool'=>1], ['txt'=>'Отклонить', 'bool'=>-1], 1, $clientId, $orderId, ['txt'=>'Правила', 'bool'=>10]);
        send_mess(['body'=>$this->getOrderMoreInfo($orderId, $cabinet_id, 1), 'id_chat'=> $clientId, 'kb'=> $kb]);
        return true;
    }


    public function setOrderStatus($input){
        global $bot_hash, $Telegram_botkey, $bot_id;
    	$data_orders_users = singleQuery("SELECT * FROM `orders_users` WHERE `id_chat` = ? AND status >= 4 AND `is_approved` = 1 AND `order_id` = ?", [ $input['ui'], $input['oi'] ]);
    	$admins = $this->admin_users($input['oi']);

    	/* * * * * * * * * * * * * * * * * * * * * * * * * *
    	 * -1   - отазался                                 *
    	 *  1   - дошло                                    *
    	 *  2   - согласился первый раз                    *
    	 *  3   - согласился второй раз, прожал ДА 3 раза  *
    	 *  4   - одобрен на заказ                         *
    	 *  5   - выехал                                   *
    	 *  6   - на месте                                 *
    	 *  7   - выполнил заказ                           *
    	 *  8   - получил оплату                           *
    	 *  100 - возникла проблема                        *
    	 * * * * * * * * * * * * * * * * * * * * * * * * * */
        $order = singleQuery("SELECT * FROM `orders` WHERE `id` = ?", [$input['oi']]);
    	switch ($input['bi']) {
    		case 1: //статус заказа 5
                if($data_orders_users['status'] == 4) {
                    //$t_bool = $this->convertDateTime($input['oi'], $order['cabinet_id']);
                    $t_bool = $order['time_at'] - time();
                    if ($t_bool <= 7200) {
                        $text = "";
                        $order_info = $this->getOrderInfo($order['cabinet_id'], $input['oi']);
                        foreach ($order_info as $title) {
                            if ($title['is_2hours']) $text .= ($title['name'].': '.$title['value']."\n");
                        }
                        send_mess(['body' => $text, 'id_chat' => $input['ui']]);
                        foreach ($admins as $admin) {
                            //$bot = singleQuery("SELECT `hash`, `bot_key` FROM `bot` WHERE `id` = ?", [ $admin['bot_id'] ]);
                            //$bot_hash = $bot['hash'];
                            //$Telegram_botkey = $bot['bot_key'];
                            $rab_name = singleQuery("SELECT `name` FROM `users` WHERE `id_chat` = ? AND bot_id = ?", [$input['ui'], $bot_id])['name'];
                            send_mess(['body' => "Заказ #" . $order['number'] . "\n" . $rab_name . " выехал на заказ.", 'id_chat' => $admin['id_chat']]);
                        }
                        query("UPDATE `orders_users` SET `status` = 5 WHERE `id_chat` = ? AND `order_id` = ? AND `status` = 4 ", [$input['ui'], $input['oi']]);
                    } else {
                        send_mess(['body' => 'За 2 часа до начала заказа вам придет уведомление об адресе заказа', 'id_chat' => $input['ui']]);
                        query("UPDATE `orders_users` SET `is_2hour` = 1 WHERE `id` = ".$data_orders_users['id']);
                    }
                }
    			break;
    		case 2: //статус заказа 6
                if($data_orders_users['status'] == 5){
                    $bot = singleQuery("SELECT * FROM `bot` WHERE id = ?", [ $bot_id ]);
                    $order_fields = singleQuery("SELECT * FROM `orders_fields` WHERE cabinet_id = ? AND `type` = 'need_employ'", [ $bot['cabinet_id'] ]);
                    $order_values = singleQuery("SELECT * FROM `orders_values` WHERE orders_id = ? AND orders_fields_id = ?", [ $input['oi'], $order_fields['id'] ]);
                    $text = "Вы приехали вовремя.";

                    if ($order_values['value'] > 1) {
                        $f = false;
                        $text2 = " Дождитесь других, чтобы идти на заказ:\n\nСегодня с вами работают:\n";
                        foreach (arrayQuery("SELECT * FROM `orders_users` WHERE `status` >= 4 AND `is_approved` = 1 AND `order_id` = ?", [ $input['oi'] ]) as $user) {
                            if($input['ui'] == $user['id_chat']) continue;
                            $f = true;
                            if($user['brigadier']){
                                $data = singleQuery("SELECT `name`,  `phone` FROM `users` WHERE `id` = ?", [ $user['user_id'] ]);
                                if($user['status'] >= 5) $text2 .= $data['name'].' '.$data['phone']." - на месте.\n";
                                else $text2 .= $data['name'].' '.$data['phone']." - пока отсутствует.\n";
                            } else {
                                $data = singleQuery("SELECT `name` FROM `users` WHERE `id` = ?", [ $user['user_id'] ]);
                                if($user['status'] >= 5) $text .= $data['name']." - на месте.\n";
                                else $text2 .= $data['name']." - пока отсутствует.\n";
                            }
                        }
                        if ($f) $text .= $text2;
                    }

                    send_mess(['body'=>$text, 'id_chat'=>$input['ui']]);
                    query("UPDATE `orders_users` SET `status` = 6 WHERE `id_chat` = ? AND `order_id` = ? AND `status` = 5", [ $input['ui'], $input['oi'] ]);

                    $t_bool = $order['time_at'] - time();
                    if ($data_orders_users['brigadier']) {
                        if ($t_bool > 1800) {//is_30min
                            send_mess(['body' => 'За 30 минут до начала заказа вам придет уведомление с данными заказчика', 'id_chat' => $input['ui']]);
                            query("UPDATE `orders_users` SET `is_30min` = 1 WHERE `id` = ".$data_orders_users['id']);
                        } else {
                            //Телефон клиента
                            $text = "";
                            $order_info = $this->getOrderInfo($order['cabinet_id'], $input['oi']);
                            foreach ($order_info as $title) {
                                if ($title['is_30minutes']) $text .= ($title['name'].': '.$title['value']."\n");
                            }
                            send_mess(['body' => $text, 'id_chat' => $input['ui']]);
                        }
                    }

                    foreach ($admins as $admin) {
                        //$bot = singleQuery("SELECT `hash`, `bot_key` FROM `bot` WHERE `id` = ?", [ $admin['bot_id'] ]);
                        //$bot_hash = $bot['hash'];
                        //$Telegram_botkey = $bot['bot_key'];
                    	$rab_name = singleQuery("SELECT `name` FROM `users` WHERE `id_chat` = ? AND `bot_id` = ?", [ $input['ui'], $bot_id ])['name'];
                    	send_mess(['body' => "Заказ #".$order['number']."\n".$rab_name." на месте.", 'id_chat' => $admin['id_chat'] ]);
                    }
                }
    			break;
    		case 3: //статус заказа 7
	    		if($data_orders_users['status'] == 6){
	    			send_mess(['body'=>'Благодарим за выполненный заказ.', 'id_chat'=>$input['ui']]);
	                query("UPDATE `orders_users` SET `status` = 7 WHERE `id_chat` = ? AND `order_id` = ? AND `status` = 6", [ $input['ui'], $input['oi'] ]);
	                foreach ($admins as $admin) {
                        //$bot = singleQuery("SELECT `hash`, `bot_key` FROM `bot` WHERE `id` = ?", [ $admin['bot_id'] ]);
                        //$bot_hash = $bot['hash'];
                        //$Telegram_botkey = $bot['bot_key'];
	                	$rab_name = singleQuery("SELECT `name` FROM `users` WHERE `id_chat` = ? AND `bot_id` = ?", [ $input['ui'], $bot_id ])['name'];
	                	send_mess(['body' => "Заказ #".$order['number']."\n".$rab_name." выполнил заказ.", 'id_chat' => $admin['id_chat'] ]);
	                }
	            }
	    		break;
    		case 4: //статус заказа 8
                if($data_orders_users['status'] == 7) {
                    send_mess(['body' => 'Благодарим за вашу работу, ждите новых заказов.', 'id_chat' => $input['ui']]);
                    query("UPDATE `orders_users` SET `status` = 8 WHERE `id_chat` = ? AND `order_id` = ? AND `status` = 7", [$input['ui'], $input['oi']]);
                    foreach ($admins as $admin) {
                        //$bot = singleQuery("SELECT `hash`, `bot_key` FROM `bot` WHERE `id` = ?", [ $admin['bot_id'] ]);
                        //$bot_hash = $bot['hash'];
                        //$Telegram_botkey = $bot['bot_key'];
                        $rab_name = singleQuery("SELECT `name` FROM `users` WHERE `id_chat` = ? AND `bot_id` = ?", [$input['ui'], $bot_id])['name'];
                        send_mess(['body' => "Заказ #".$order['number']."\n".$rab_name . " оплату получил.", 'id_chat' => $admin['id_chat']]);
                    }
                }
    			break;
    		case 5: //статус заказа 100
    			send_mess(['body'=>'Ваше сообщение отправлено, с вами свяжутся', 'id_chat'=>$input['ui']]);
                query("UPDATE `orders_users` SET `status` = 100 WHERE `id_chat` = ? AND `order_id` = ? AND `status` >= 4", [ $input['ui'], $input['oi'] ]);
                foreach ($admins as $admin) {
                    //$bot = singleQuery("SELECT `hash`, `bot_key` FROM `bot` WHERE `id` = ?", [ $admin['bot_id'] ]);
                    //$bot_hash = $bot['hash'];
                    //$Telegram_botkey = $bot['bot_key'];
                	$rab_name = singleQuery("SELECT `name` FROM `users` WHERE `id_chat` = ? AND `bot_id` = ?", [ $input['ui'], $bot_id ])['name'];
                	send_mess(['body' => "Заказ #".$order['number']."\n".$rab_name." возникла проблема.", 'id_chat' => $admin['id_chat'] ]);
                }
    			break;
    	}
    }

    //Функции хелперы
    private function convertDateTime($id, $cabinet_id){
        $date_time = array();
        $order_info = $this->getOrderInfo($cabinet_id, $id);
        foreach ($order_info as $title) {
            if($title['name'] == 'Дата') $date_time['date'] = $title['value'];
            if($title['name'] == 'Время') $date_time['time'] = $title['value'];
        }
        $date = explode('.', $date_time['date']);
        $date_time['date'] = $date[2].'-'.$date[1].'-'.$date[0];
        $order_mailling = $date_time['date'].' '.$date_time['time'].':00';
        return singleQuery("SELECT TIME_TO_SEC(TIMEDIFF(?, NOW())) second", [ $order_mailling ])['second'];
    }
    private function getOrderMoreInfo($id, $cabinet_id, $info_id){
        $order = singleQuery("SELECT * FROM `orders` WHERE `id` = ?", [$id]);
        $order_text = "Заказ #{$order['number']}\n";

        $order = $this->getOrderInfo($cabinet_id, $id);
        //$not_send = array("Заголовок", "Клиент", "Улица", "Дом","Квартира/офис/комментарий");
        switch ($info_id) {
        	case 0:
        		foreach ($order as $info) {
        			/*if($info['name'] == 'Заголовок'){
        		    	$order_text .= ($info['name'].': '.$info['value']."\n");
        		    	break;
        			}*/
                    if ($info['is_first']) $order_text .= ($info['name'].': '.$info['value']."\n");
        		}
        		break;
        	case 1:
        	    //Отсылаем если подтвердил, то более подробный заказ
        		foreach ($order as $info) {
        			//if(array_search($info['name'], $not_send) or $info['name'] == "Телефон клиента" or $info['name'] == 'Заголовок') continue;
        		   	//$order_text .= ($info['name'].': '.$info['value']."\n");
                    if ($info['is_accept']) $order_text .= ($info['name'].': '.$info['value']."\n");
        		}
        		break;
            case 2:
                foreach ($order as $info){
                    /*if($info['name'] == 'Заголовок') $order_text .= $info['value']."\n";
                    if($info['name'] == 'Улица') $order_text .= $info['value'].' ';
                    if($info['name'] == 'Дом') $order_text .= $info['value'];*/
                    if ($info['is_2hours']) $order_text .= ($info['name'].': '.$info['value']."\n");
                }
                break;
            case 3:
                foreach ($order as $info){
                    /*if($info['name'] == "Телефон клиента") 'Телефон клиента: '.$order_text .= $info['value']."\n";
                    if($info['name'] == "Клиент") 'Клиент: '.$order_text .= $info['value']."\n";
                    if($info['name'] == 'Заголовок') $order_text .= $info['value']."\n";
                    if($info['name'] == 'Улица') $order_text .= $info['value'].' ';
                    if($info['name'] == 'Дом') $order_text .= $info['value'].', ';
                    if($info['name'] == 'Квартира/офис/комментарий') $order_text .= $info['value']."\n";*/
                    if ($info['is_30minutes']) $order_text .= ($info['name'].': '.$info['value']."\n");
                }
                break;
        }
        return $order_text;
    }

    //Геттеры клавиатур
    private function getWorkerKeyboard($id_chat, $order_id){
        $kb=[];
        foreach ($this->get_kbArr() as $btn) $kb[]=[ ['text' => $btn['button'], 'callback_data' => json_encode([ 'mtd'=> 'setOrderStatus', 'ui'=> $id_chat, 'bi'=> $btn['id'], 'oi'=> $order_id ])] ];
        return ["inline_keyboard"=>$kb];
    }
    private function get_yesNo_kb($answer1, $answer2, $num, $id, $order_id, $ext = null){
        $kb=[];
        if ($ext) {
            $kb[]=[
                ['text' => $answer1['txt'], 'callback_data' => json_encode(['mtd'=>'YesNo', 'num'=>$num, 'answer'=>$answer1['bool'], 'ci'=>$id, 'oi'=>$order_id ])],
                ['text' => $answer2['txt'], 'callback_data' => json_encode(['mtd'=>'YesNo', 'num'=>$num, 'answer'=>$answer2['bool'], 'ci'=>$id, 'oi'=>$order_id ])],
                ['text' => $ext['txt'], 'callback_data' => json_encode(['mtd'=>'YesNo', 'num'=>$num, 'answer'=>$ext['bool'], 'ci'=>$id, 'oi'=>$order_id ])]
            ];
        } else {
            $kb[]=[
                ['text' => $answer1['txt'], 'callback_data' => json_encode(['mtd'=>'YesNo', 'num'=>$num, 'answer'=>$answer1['bool'], 'ci'=>$id, 'oi'=>$order_id ])],
                ['text' => $answer2['txt'], 'callback_data' => json_encode(['mtd'=>'YesNo', 'num'=>$num, 'answer'=>$answer2['bool'], 'ci'=>$id, 'oi'=>$order_id ])]
            ];
        }
        return ["inline_keyboard"=>$kb];
    }

    //Функции для базы
    private function setOrderUsersStatus( $orderId, $clientId, $status ){
        query('UPDATE `orders_users` SET `status` = '.((int) $status).' WHERE `id_chat` = '.$clientId.' AND `order_id` = '.$orderId);
    }

    private function setOrderUsers( $userId, $chatId, $orderId, $status = 1 ){
        query('DELETE FROM `orders_users` WHERE `user_id`=? AND `id_chat`=? AND `order_id`=?', [ $userId, $chatId, $orderId ]);
        query('INSERT INTO `orders_users` (`user_id`, `id_chat`, `order_id`, `brigadier`, `status`) VALUES(?, ?, ?, 0, ?)',
            [ $userId, $chatId, $orderId, $status ]);
    }

    private function get_kbArr(){ return arrayQuery('SELECT * FROM _kb '); }
    private function getBot_key($bot_id){ return singleQuery("SELECT `bot_key` FROM `bot` WHERE id = ?", [ $bot_id ])['bot_key']; }
    private function getNews() { return arrayQuery("SELECT * FROM `news` WHERE `status` = 0 AND `active` = 1 AND `type_send` >= 1"); }
    private function order_users($order_id){ return arrayQuery("SELECT `id_chat`, `brigadier` FROM `orders_users` WHERE `order_id` = ? AND `status` = 4"); }
    private function users($bot_id){ return arrayQuery("SELECT `id`, `status`, `id_chat`, `bot_id` FROM `users` WHERE `bot_id` = {$bot_id} AND `id_cms_privileges` = 2 AND `is_deleted` != 1 AND `status` >= -10"); }
    private function admin_users($order_id) {
        global $Telegram_botkey;
        $users = [];
        $order = singleQuery('SELECT * FROM `orders` WHERE id=?', [$order_id]);
        if ($order && $cabinet = singleQuery('SELECT * FROM `cabinet` WHERE id = ?', [$order['cabinet_id']])) {
            $users = arrayQuery("SELECT * FROM `users` WHERE id = ?",[$cabinet['users_id']]);
        }

        return $users; //arrayQuery("SELECT `id_chat`, `users`.`bot_id` FROM `users` JOIN `orders` ON `orders`.`id` = {$order_id} AND `users`.`cabinet_id` = `orders`.`cabinet_id` AND `users`.`id_cms_privileges` = 3 AND `users`.`status` = 1 AND `users`.`is_deleted` != 1");
    }

    private function getOrder( $id ){
        return  singleQuery("SELECT * FROM `orders` WHERE id = ?", [ $id ]);
    }

    private function getOrderInfo($cabinet_id, $id){
        return arrayQuery('SELECT f.*, v.value
                           FROM `orders_fields` f
                           JOIN `orders_values` v ON v.orders_fields_id = f.id
                           WHERE f.cabinet_id = '.((int) $cabinet_id).' AND v.orders_id = '.((int) $id).' ORDER BY `sort` ASC');
    }

}
