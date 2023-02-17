<?php

namespace project\modules\transfer_data_from_the_database;

class transfer_data_from_the_database
{
    public function start( array $par=[] )
    {

        $settings = json_decode($par['transfer_data_from_the_database']);
        $obj = json_decode(input_data);
        $text = $obj-> message->text;
        if($cb = $obj-> callback_query)
            $text = $cb-> data;
        $chat_id = $obj-> message-> chat->id;

        foreach($settings as &$setting){
            $setting = text()->variables($setting, $par);
            $setting = text()->shortcodes($setting, $par);
        }
				
        $query = $settings-> query;
        $blockchain_if_empty = $settings-> blockchain_if_empty;
				$if_empty = $settings-> if_empty;
        $sqlRes = [];
        if($query){

            $sqlRes = arrayQuery($query);
            if(count($sqlRes)==1)
            foreach($sqlRes as $row)
                foreach($row as $rowCol=> $rowVal)
                    $par[ $par['script_step'].'_'.$rowCol ] = $rowVal;

            if(count($sqlRes)>1)
            foreach($sqlRes as $num=> $row){
                if($num==10)
                    break;

                foreach($row as $rowCol=> $rowVal)
                    $par[ $par['script_step'].'_'.($num+1).'_'.$rowCol ] = $rowVal;
            }
        }

        if(!count($sqlRes)){
            tgMess($if_empty?:DIALTEXT('transferDataFromTheDatabaseDataEmpty'));
            if($blockchain_if_empty){
                go_to_blockchain($blockchain_if_empty, []);
                return;
            }

        }

        if(!$par = echo_message_from_par($par)) return false;

        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return true;
    }

}
