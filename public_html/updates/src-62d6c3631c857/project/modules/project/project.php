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

        send_mess(['body'=>'Модуль <b>project</b>', 'id_chat'=> $chat_id]);
        
        /**  данные из test формы
        $var1 = $settings['textarea'];
        $var2 = $settings['input'];

        $kb=[];
        array_push($kb, [
            ['text' => $var2, 'callback_data' => $var2]
        ]); 
        $kb=["inline_keyboard"=>$kb];
        
        send_mess(['body'=>$var1, 'id_chat'=> $chat_id, 'kb'=> $kb]);
        */
        
        $par[$par['script_step']] = ""; # передача данных текущего шага дальше
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }

    public function projectSetOrderStatus( $par ){
        //notification($par['st']);
        /*что-то подгрузится из базы*/
        $order_num = $par['order_num'];
        $sql = 'SELECT * FROM `status_order_for_worker` WHERE order_num=?';
        $order_info = singleQuery($sql, [ $order_num ]);
        switch ($par['st']){
            case 1:
                //Г. Краснодар, Мкр. Фестивальный, д1.
                break;
            case 2:
                //Вы приехали вовремя. Дождитесь других, чтобы идти на заказ:
                break;
            case 3:
                //Благодарим за выполненный заказ
                break;
            case 4:
                //Благодарим за вашу работу, ждите новых заказов.
                break;
            case 5:
                //Ваше сообщение отправлено, с вами свяжутся
                break;
        }

    }
    
}