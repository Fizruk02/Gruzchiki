<?php
namespace project\modules\linked_tables;

class linked_tables
{
    public function start( array $par=[] )
    {   
        global $original, $chat_id, $username, $message_id, $text_message, $user_settings;
        if(!$par = echo_message_from_par($par)) return false;
        $settings = json_decode($par['linked_tables'], true);
        foreach($settings as &$tSetting)
            $tSetting = text()->variables($tSetting, $par);
        
        $settings['queries'] = text()->substituteVariablesInAnArray($settings['queries'], $par);
        set_pos('linked_tables_listener', $par);
        $this-> controller( $settings );
        
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
        
        //$par[$par['script_step']] = ""; # передача данных текущего шага дальше
        //set_pos($par['step'], $par);
        //the_distribution_module($par['script_source'], $par);
        
        return true;
    }
    
    private function controller($par, $dir='forward'){
        global $original;
        $last = last_step(true);
        $ind=-1;
        
        $qr=&$par['queries'];
        foreach($qr as $k=>$q)
        if(isset($q['current'])) {
            $ind=$k;
            unset($qr[$k]['current']);
        }
        if($dir==='forward')  $ind++; else $ind--;
        if($ind>=count($qr)) {
            $lastpar=json_decode($last['parameters'],1);

            $title=$this-> breadcrumbs($qr).PHP_EOL;

            $k=array_keys($lastpar);

            if(end($k)!=='linked_tables') return tgmess('Ошибка! Потерян сценарий');
 
            $res=[];
            $ls=$lastpar['script_step'];
            foreach($par['queries'] as $key=>$q){
                $res[]=$q['data'];
                $lastpar[$ls.'_'.$key]=$q['data'];
                $lastpar[$ls.'_text_'.$key]=$q['text'];
            }
            
            $title=$this-> breadcrumbs($qr,PHP_EOL, '✔️ ') ;
            if(isset($original['callback_query']))
                methods()-> editMsg(['text'=>$title]);
            else
                tgmess($title);

            $lastpar[$ls] = $res; # передача данных текущего шага дальше
            set_pos($lastpar['step'], $lastpar);
            the_distribution_module($lastpar['script_source'], $lastpar);
            return;
        }

        if($ind<0) $ind=0;
        
        $qr[$ind]['current']=true;
        
        $current=$qr[$ind];
        
        $query=$current['query'];
        
        if($ind>0&&strpos($query, '[prev]'))
            $query=str_replace('[prev]', $qr[$ind-1]['data'], $query);

        $arr=arrayQuery($query);
        if(!count($arr)) return notification('Пусто', 0);
        if(count($arr)===1&&$current['autoselect']??0) {
            $qr[$ind]['data']=$arr[0][$current['keyval']];
            $qr[$ind]['text']=$arr[0][$current['keytext']];
            $this-> controller([ 'queries'=> $qr, 'dataId'=> $par['dataId'] ], $dir);
            return;
        }
        

        $lowerKeys=false;
        if($ind&&isset($par['dataId'])){
            $lowerKeys=[ [ ['text' => '« Назад', 'callback_data' => json_encode(['mtd'=> 'lnktblBack', 'd'=> $par['dataId'] ]) ] ]];
        }
        
        $title=$this-> breadcrumbs($qr).PHP_EOL;
        $title .= $current['caption']?:'список';
        
         $data = [
              'mask' => 'linked_tables_mask_inline'
            , 'limit' => $settings['limit']??10
            , 'columns' => $current['columns']?:1
            , 'class' => get_class()
            , 'title' => $title
            , 'payload' => $par
            ,'lowerKeys'=> $lowerKeys
            //, 'staticQuery' => $staticQuery
            , 'query' => $query
            , 'update'=> isset($par['dataId'])
        ];

        if(isset($par['dataId'])) {
            $dataId=$par['dataId'];
            updateData($dataId, $data);
        } else {
            $dataId = setData($data);
        }
        
        lists()->_inline_list(['dataId' => $dataId]);
    }
    
    private function breadcrumbs($qr,$div=' ➤ ', $prefix=''){
        $t=[];
        foreach($qr as $r) if(isset($r['text'])) $t[]=$prefix.'<i>'.$r['text'].'</i>';else break;
        return implode($div, $t);
        
    }
    
    public function lnktbl6($par){
        global $obj;
        $data = getData($par['d']);
        $queries=$data['payload']['queries'];
        $r=$this-> getCurrent($queries);
        $queries[$r]['data']=$par['v'];
        $queries[$r]['text']=str_replace('▼','', keyboards()->textPressedInlinekey()['text']);
        $this-> controller([ 'queries'=> $queries, 'dataId'=> $par['d'] ]);
    }
    
    public function lnktblBack($par){
        $data = getData($par['d']);
        $queries=$data['payload']['queries'];
        $ind=$this-> getCurrent($queries);
        unset($queries[$ind]['data']);
        unset($queries[$ind]['text']);
        return $this-> controller([ 'queries'=> $queries, 'dataId'=> $par['d'] ], 'back');
    }
    
    public function linked_tables_mask_inline($par){
        $q=$par['payload']['queries'];
        $r=$this-> getCurrent($q);
        $text=trim($q[$r]['keytext']);
        $val=trim($q[$r]['keyval']);
        
        $ch = count($q)-1>$r?'▼':'';
        //if ($children = singleQuery('SELECT * FROM `array_of_buttons` WHERE listId = :listId AND parent = :id', ['id' => $par['itemId'], ':listId' => $par['listId']]))
        //    $ch = '▼';
//
        //if ($children || $par['payload']['array_of_buttons']['silentmode'])
        //    $cb = json_encode(['mtd' => 'arrayOfButnsInlineKb', 'd' => $par['_dataId'], 'id' => $par['itemId']]);
        //else
        //    $cb = $par['itemVal'];

        $kb = [ ['text' => $ch.($par[$text]??'field not found'), 'callback_data' => json_encode(['mtd'=> 'lnktbl6', 'd'=> $par['_dataId'], 'v'=> $par[$val]??'not found']) ] ];

        return $kb;
    }
    
    
    public function linked_tables_listener( $par ){
        global $chat_id, $message_id, $text_message;
        methods()-> delete_mess($chat_id, $message_id);
        $sm=send_mess(['body' =>'Выберите значение из списка']);
        sleep(2);
        methods()-> delete_mess($chat_id, $sm[0]['message_id']);
        
    }
    
    
    private function getCurrent($arr){
        foreach($arr as $k=>$r)
            if(isset($r['current'])) return $k;
        return false;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}