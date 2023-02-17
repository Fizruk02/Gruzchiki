<?php
namespace project\modules\project;

class project
{
    public function start( array $par=[] )
    {   
        global $original, $chat_id, $username, $message_id, $text_message, $user_settings;
        if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['project'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);
        
        $par[$par['script_step']] = ""; # передача данных текущего шага дальше
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }

    public function projectSetOrderStatus( $par ){
        global $chat_id, $message_id;
        //notification($par['st']);

        //информация о данном работнике
        $user = singleQuery('SELECT order_num, chat_id_worker FROM status_order_for_worker WHERE chat_id_worker=?', [ $par['id'] ]);
        $order_num = $user['order_num'];
        $chat_id = $par['id'];

        //инофрмация о заказе, работникх и директоре
        $order_info = singleQuery('SELECT *, IF(order_date_time > NOW()-INTERVAL 2 HOUR, 1, 0) AS st FROM `work_info` WHERE `order_num`=?', [ $order_num ]);
        $worker_info = singleQuery('SELECT `id`, `surname`, `name`, `phone` FROM `users` WHERE `id_chat`=?', [ $chat_id ]);
        $admin_info = singleQuery('SELECT `id_chat` FROM `users` WHERE `status`=3');


        switch ($par['st']){
            case 1: //Г. Краснодар, Мкр. Фестивальный, д1.

                $address = $order_info['order_address'];
                if($order_info['st']){
                    send_mess(['body'=> $address, 'id_chat'=> $chat_id]);
                }
                break;
            case 2: //Вы приехали вовремя. Дождитесь других, чтобы идти на заказ.

                //проверка на то что вовремя
                if($order_info['st'] and $chat_id != $admin_info['id_chat']){
                    send_mess(['body'=> 'Вы приехали вовремя. Дождитесь других, чтобы идти на заказ.', 'id_chat'=> $chat_id]);
                }

                //запись в бд что пришёл
                query("UPDATE `_monitor_order` SET worker_location=1 WHERE worker_id_chat=?", [ $chat_id ]);

                //отправка сообщения руководителю
                $text = $worker_info['name'].' '.$worker_info['surname'].' на месте.';
                send_mess(['body'=> $text, 'id_chat'=> $admin_info['id_chat']]);

                break;
            case 3: //Благодарим за выполненный заказ

                //отправка сообщения об окончании заказа руководителю
                send_mess(['body'=> 'Заказ выполнен', 'id_chat'=> $admin_info['id_chat']]);

                //апись в бд о выполненом заказе пришёл
                query("UPDATE `work_info` SET order_status='completed' WHERE order_num=?", [ $order_num ]);

                //сообщения для исполнителя
                send_mess(['body'=> 'Благодарим за выполненный заказ', 'id_chat'=> $chat_id]);
                break;
            case 4: //Благодарим за вашу работу, ждите новых заказов.

                //отправка сообщения о полученной плате руководителю
                $text = $worker_info['name'].' '.$worker_info['surname'].' получил оплату.';
                send_mess(['body'=> $text, 'id_chat'=> $admin_info['id_chat']]);

                //сообщения для исполнителя
                send_mess(['body'=> 'Благодарим за вашу работу, ждите новых заказов.', 'id_chat'=> $chat_id]);
                break;
            case 5: //Ваше сообщение отправлено, с вами свяжутся

                //отправка сообщения об ошибке  руководителю
                $text = $worker_info['name'].' '.$worker_info['surname']." написал о проблеме\nПозвонить: +".$worker_info['phone'];
                send_mess(['body'=> $text, 'id_chat'=> $admin_info['id_chat']]);

                //сообщения для исполнителя
                send_mess(['body'=> 'Ваше сообщение отправлено, с вами свяжутся', 'id_chat'=> $chat_id]);
                break;
        }

        $kb=$this-> projectGetWorkerKeyboard($chat_id, $par['st']-1);
        methods()-> edit_inline_keyboard($chat_id, $message_id, $kb);

    }

    public function projectGetWorkerKeyboard( $user_id, $current=false ){
        //таблица кнопок
        $kb_array = $this-> get_kbArr();

        $kb=[];

        foreach ($kb_array as $k=>$btn){
            $kb[]=[ ['text' => ($k===$current?'✅ ':'').$btn['button'], 'callback_data' => json_encode([ 'mtd'=> 'projectSetOrderStatus', 'id'=> $user_id, 'st'=> $btn['id'] ] )] ];
        }

        return ["inline_keyboard"=>$kb];
    }
    private function get_kbArr(){
        return arrayQuery('SELECT * FROM _kb ');
    }
    
}