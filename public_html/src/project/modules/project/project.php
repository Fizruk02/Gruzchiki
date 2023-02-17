<?php
namespace project\modules\project;

class project{
    public function start( array $par=[] ){   
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

    public function sendOrder(){
        $order_data = arrayQuery("SELECT `id` FROM `orders` WHERE `status` = 1 AND `active` = 1");
        foreach ($order_data as $order) {
            print_r($order);
            $sql = "SELECT `orders_fields`.`name`, `orders_values`.`value`
                    FROM `orders_fields` JOIN `orders_values` 
                    WHERE `orders_fields`.`id` = `orders_values`.`orders_fields_id`
                    AND `orders_fields`.`cabinet_id` = {$order['cabinet_id']} 
                    AND `orders_values`.`orders_id` = {$order['id']}";
            $order_info = arrayQuery($sql);
            print_r($order_info);
        }
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