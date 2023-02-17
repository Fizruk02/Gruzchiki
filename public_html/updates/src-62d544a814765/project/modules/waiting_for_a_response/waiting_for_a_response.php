<?php

namespace project\modules\waiting_for_a_response;

class waiting_for_a_response {
    
    public function start( array $par=[] )
    {   
        if(!$par = echo_message_from_par($par)) return false;
        set_pos($par['step'], $par);
        
        return true;
    }
    
    public function listener( array $par=[] )
    { 
        global $chat_id, $text_message;
 
        $text = $text_message;
        
        $l_text = trim(mb_strtolower($text, 'utf-8'));
        
        if(!intermediate_function($par)) return;
        
        if($this->get_local_trigger($text, $par)) return;
        
        $keywords = arrayQuery("SELECT v.keyword FROM s_waiting_for_response_variants v, s_steps s WHERE v.id_step = s.id AND s.item = :item", [':item'=>$par['script_step']]);  
        $t='';
        foreach($keywords as $key=> $keyword)
            $t = $t?
            $t.($key==count($keywords)-1?' или ':', ')."«{$keyword['keyword']}»":
                "«{$keyword['keyword']}»";
            
        $text = $t? "пришлите $t" :"ответ не верный, попробуйте еще раз";
        
        send_mess(["body"=>$text, 'id_chat'=> $chat_id,  "disable_header"=>true]);

        return true;
    }
    
    
    private function get_local_trigger($message, $par) { # поиск тригеров шага
        $success = false;
        $rows = arrayQuery('SELECT v.keyword, v.script, v.go_back_to_the_current_one, v.output FROM s_waiting_for_response_variants v, s_steps s WHERE v.keyword = :keyword AND v.id_step = s.id AND s.item = :item', [':keyword' => $message, ':item' => $par['script_step']]);
        # mysql некорректно иищет по смайлам и выдает несколько результатов, поэтому ищу циклом
        if (count($rows)){
            foreach($rows as $r)
                if($r['keyword']===$message){ $row = $r;break; }
        }
        else
            $row = singleQuery('SELECT v.script, v.go_back_to_the_current_one, v.output FROM s_waiting_for_response_variants v, s_steps s WHERE v.keyword = "~anytext~" AND v.id_step = s.id AND s.item = :item', [':item' => $par['script_step']]);
        
        if ($row) {
            $script = $row['script'];
            $success = true;
        }
				
        $par[$par['script_step']] = $row['output']?:$message;
        if($row['output'])
            $par[$par['script_step'].'_orig'] = $message;
        
        if ($success) 
            switch ($script) {
            case '#continue#':
                
                the_distribution_module($par['script_source'], $par);
                return true;
            break;
            default:
                if ($row['go_back_to_the_current_one']) {
                    $previousBlockchainList = $par['previousBlockchainList'];
                    if (!$previousBlockchainList) $previousBlockchainList = [];
                    $previousBlockchain = [];
                    $previousBlockchain['script_source'] = $par['script_source'];
                    $previousBlockchain['script_step'] = $par['script_step'];
                    $previousBlockchain['id_step'] = $par['id_step'];
                    array_push($previousBlockchainList, $previousBlockchain);
                    $par['previousBlockchainList'] = $previousBlockchainList;
                }
                unset($par['script_step']);
                the_distribution_module($script, $par);
                return true;
            }
            return false;
    }
    
    
}