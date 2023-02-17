<?php

class cl
{
    
    
    public function getMailingList( $input ){

        $repeats = arrayQuery('SELECT m.*, f.name filterName FROM mailings m
                LEFT JOIN mailings_filters f ON f.id = m.filter
                WHERE m.`status` IN(10,11) AND m.`repeat` <> ""
                ORDER BY m.date_begin');
                            
        $data = arrayQuery('SELECT m.*, IFNULL(f.name, "") filterName,
                (SELECT count(*) FROM mailing_address WHERE id_mailing=m.id AND status=0) st_waitings,
                (SELECT count(*) FROM mailing_address WHERE id_mailing=m.id AND status=1) st_success,
                (SELECT count(*) FROM mailing_address WHERE id_mailing=m.id AND status=-1) st_error
                FROM mailings m
                LEFT JOIN mailings_filters f ON f.id = m.filter
                WHERE status IN(0,1,2,3) ORDER BY m.date_begin DESC');
        
        $arr = array_map(function($it) {
            $d=json_decode($it['data'],1);
            $it['poll']=isset($d['poll_data'])&&$d['poll_data']<>'[]'?1:0;
            $it['event_notify_wkdays']=json_decode($it['event_notify_wkdays']?:'[]',1);
            return $it;
        }, array_merge($repeats, $data));
        
        
        $arr = array_map(function($it) {
            switch($it['filter']){
                case -1:

                    $countUsers = singleQuery('SELECT count(*) c FROM `mailing_address` WHERE id_mailing = ? AND id_chat >0', [ $it['id'] ])['c']*1;
                    $countGroups = singleQuery('SELECT count(*) c FROM `mailing_address` WHERE id_mailing = ? AND id_chat <0', [ $it['id'] ])['c']*1;

                    $countUsersText=text()->num_word($countUsers, ['пользователь', 'пользователя', 'пользователей']);
                    $countGroupsText=text()->num_word($countGroups, ['группа', 'группы', 'групп']);

                    if($countUsers&&$countGroups)
                        $it['filterName']= $countUsersText.' и '.$countGroupsText;
                    elseif($countUsers&&!$countGroups)
                        $it['filterName']= $countUsersText;
                    elseif(!$countUsers&&$countGroups)
                        $it['filterName']= $countGroupsText;
                break;
                case 0:
                    $it['filterName']='ВСЕМ';
                break;
            }
            return $it;
        }, $arr);


        
        


        return [
             'success'=> 'ok'
            ,'data'=> $arr
        ];
    
    }
    
    public function getList( $input ){

        $query = singleQuery('SELECT `query` FROM `mailings_filters` WHERE id=?', [ $input['filter'] ])['query']?:'SELECT id, chat_id, username, first_name, 1 `check` FROM `usersAll`';
        $data = arrayQuery($query, [], true);

        return [
             'success'=> 'ok'
            ,'data'=> $data
        ];
    
    }

    public function getUsers( $input ){

        $data = arrayQuery('SELECT m.id_mailing, m.id_chat, m.status st, DATE_FORMAT(m.`t_date`, "%d.%m.%Y %H:%i") f_date, u.first_name, u.username
                            FROM `mailing_address` m
                            LEFT JOIN usersAll u ON u.chat_id = m.id_chat
                            WHERE m.id_mailing=?', [ $input['mid'] ], true);
        $data = array_map(function($it){
            switch($it['st']){
                case -1: $s='ошибка';break;
                case 0: $s='ожидает отправки';break;
                case 1: $s='отправлено';break;
                default: $s='не установлен';
            }
            $it['status']=$s;
            return $it;
        }, $data);
        return [
             'success'=> 'ok'
            ,'data'=> $data
        ];
    
    }
    
    public function pollGet( $input ){
        if (!$id = $input["id"])
            return bterr('не передан id');
        $data = arrayQuery('SELECT v.variant, (SELECT count(*) FROM `mailing_voting_answers` WHERE id_variant = v.id) `sum` FROM mailing_voting_variants v WHERE v.id_mailing = ?', [ $id ]);
        $cstms = singleQuery('SELECT count(*) `c` FROM `mailing_voting_answers` WHERE id_mailing=? AND id_variant=0',[ $id ])['c'];
        if($cstms) $data[]=['variant'=> 'Свой вариант', 'sum'=> $cstms];
        $users = arrayQuery('SELECT IFNULL(v.id,0) id, u.chat_id, u.username, u.first_name, IFNULL(v.variant, a.custom_answer) variant
                            FROM mailing_voting_answers a
                            JOIN usersAll u ON u.chat_id = a.id_user
                            LEFT JOIN mailing_voting_variants v ON v.id = a.id_variant
                            WHERE a.id_mailing = ?', [ $id ]);
        return [
             'success'=> 'ok'
            ,'data'=> $data
            ,'users'=> $users
        ];
    }


    
    public function eventEnd( $input ){
        if (!$id = $input["id"]) return bterr('не передан id');
        query('UPDATE mailings SET event_end = ? WHERE id = ?', [ date("Y-m-d H:i:s", strtotime($input['d'])), $id ]);
        return ['success'=> 'ok'];
    }
    
    public function pause( $input ){
        if (!$id = $input["id"])
            return bterr('не передан id');
        query('UPDATE mailings SET status = ? WHERE id = ?', [ $input['s']==1?"11":"10", $id ]);

        return ['success'=> 'ok'];
    }
    
    public function remove( $input ){
        if (!$id = $input["id"])
            return bterr('не передан id');
        deleteQuery('DELETE FROM mailings WHERE id = ?', [ $id ]);
        deleteQuery('DELETE FROM mailing_address WHERE id_mailing = ?', [ $id ]);
        deleteQuery('DELETE FROM mailing_voting_answers WHERE id_mailing = ?', [ $id ]);
        deleteQuery('DELETE FROM mailing_voting_variants WHERE id_mailing = ?', [ $id ]);
        return ['success'=> 'ok'];
    }

    public function get( $input ){

        if (!$id = $input["id"])
            return bterr('не передан id');
  
        $row = singleQuery("SELECT name, body, DATE_FORMAT(date_begin, '%Y-%m-%dT%H:%i') date_begin, type_mailing, mailing_date_end, status, files, id_message_in_chat, keyboard FROM mailings WHERE id = :id;", [':id'=> $id], true);
        $res = [];
        $res['name'] = $row['name'];
        $res['body'] = urldecode($row['body']);
        $res['date_begin'] = $row['date_begin'];
        $res['type_mailing'] = $row['type_mailing'];
        $res['mailing_date_end'] = $row['mailing_date_end'];
        $res['status'] = $row['status'];
        $res['filesGroup'] = $row['files'];
        $res['files'] = loadFiles()->getFilesforweb( $row['files'] );
        $res['id_message_in_chat'] = $row['id_message_in_chat'];
        $res['keyboard'] = urldecode($row['keyboard']);
        $res['blockchains'] = [];
        $blockchains = arrayQuery("SELECT blockchain, text FROM `mailing_blockchains` WHERE id_mailing = :id", [':id'=> $id]);
        foreach($blockchains as $rowBlock) array_push($res['blockchains'], ['blockchain' => $rowBlock['blockchain'], 'text' => $rowBlock['text']]);
    
        
        $res['success'] = "ok";
        return $res;
    }


    public function set( $input ){

        $id = $input['id'];
        $name = $input['name'];
        $date_begin = date("Y-m-d H:i:s", strtotime($input['date_begin']?:$input['date']));
        $body = isset($input['text'])?$input['text']:$input['t'];
        $filegroup = $input['filegroup']?:0;
        $address = $input['address'];
        $repeat = $input['repeat']?:'';
        $repeat_src = $input['repeat_src']?:0;
        $filter = $input['filter'];
        $weekdays = $input['weekdays'];
        $interval = $input['interval'];

        if($filter=='false') $filter=0;
        if ($id) {
            if (!$row = singleQuery("SELECT status FROM `mailings` WHERE id = ?", [ $id ]))
                return bterr('рассылка не найдена в базе, возможно она была удалена');
        
            if ($row['status'] == 2)
                return bterr('рассылка уже закончилась');
        
            query('UPDATE mailings SET name=?, body=?, date_begin=?, files=? WHERE id = ?', [ $name, $body, $date_begin, $filegroup, $id ]);
     
        } else {
            
            $address = json_decode($address, true);
    
            if(!$address||(is_array($address)&&!count($address)))
                return bterr('Список получателей пуст');
            
            $findChat = array_fill(0, count($address)-1, 0);
            foreach($address as $addr){
                foreach(array_values($addr) as $key=> $row){
                    if(is_numeric($row) && strlen($row)>8&&strlen($row)<20){
                        $findChat[$key]++;
                    }
                }
    
            }
    
            if(max($findChat)==0)
                return bterr('Не найдена колонка с telegram id получателя');
            $chatIdcol = array_keys($findChat, max($findChat));
            if(is_array($chatIdcol)&&count($chatIdcol)>1){
                // найдено несколько потенциальных столбцов с контактами
            }
    
            $chatIdcol = array_keys($address[0])[$chatIdcol[0]];
            
            $id = query('INSERT INTO mailings (name,body,`repeat`,files, date_begin,`filter`,`repeat_src`,`status`,`keyboard`,`weekdays`,`interval`)
                                                VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                                                [ $name,$body,$repeat,$filegroup,$date_begin,$filter?:0,$repeat_src,$repeat?10:0,$kb,$weekdays,$interval ]);
            if($e=db()->err()) return $e;
            /**
             * клавиатура
             */
            $poll_data = isset($input['poll_data'])?json_decode($input['poll_data']?:'[]', 1):[];
            $plcol = (int) $input['plcol'];
            $inline = json_decode($input['inline'],1);
            if($plcol<1) $plcol=1;
            if($plcol>8) $plcol=8;

            $data = [
                'poll_data'=> $input['poll_data']
               ,'ds'=> $input['ds']
               ,'plcol'=> $input['plcol']
               ,'inline'=> $input['inline']
               ,'custom'=> $input['plcustom']
            ];
            $kb=[];
            if(is_array($poll_data)&&count($poll_data) && $id){
                $input['mailing_id']=$id;
                $kb=array_map(function($i) use($input) {
                    $aid = query('INSERT INTO `mailing_voting_variants` (`id_mailing`, `variant`) VALUES (?,?)', [ $input['mailing_id'], $i ]);
                    return  ['text' => $i, 'callback_data' => json_encode(['mtd'=> 'mlngCntr', 'n'=> $aid, 'tp'=> 2, 'mid'=> $input['mailing_id'], 'ds'=> $input['ds']])];
                }, $poll_data);
            
                $kb = array_chunk($kb, $input['plcol']?:1);
            }

            if($input['plcustom']){
                $kb[] =  [['text' => 'Свой вариант', 'callback_data'=> json_encode([ 'mtd'=> 'mailingCustomvariant', 'mid'=> $input['mailing_id'] ]) ]];
            }

            if($e=db()->err()) return $e;
            foreach($inline as $key)
                $kb[] =  [['text' => $key[0], strpos($key[1], '//')?'url':'callback_data' => $key[1]]];
        
            if($kb){
                $kb=json_encode(["inline_keyboard"=>$kb]);
                query('UPDATE `mailings` SET `keyboard` = ?,`data` = ? WHERE id = ?', [ $kb,$data?json_encode($data):'', $id ]);
            }

            foreach($address as $a){
                query('INSERT INTO mailing_address (id_chat, source, id_mailing, status, data) VALUES (:id_chat, "telegram", :id, 0, :data);',
                                                                    [':id_chat'=> $a[$chatIdcol], ':id'=> $id, ':data'=> json_encode($a)]);
                if($e=db()->err()) return $e;
            } 
            
        }


        if(isset($input['event'])&&($ev=$input['event'])&&$ev['status']&&$id){
            if(strlen($ev['ntftime'])<6)$ev['ntftime'] .=':00';
            query('UPDATE `mailings` SET `event_status` = ?, `event_end` = ?, `event_notify` = ?, `event_notify_text` = ?, `event_notify_time` = ?, 
                      `event_notify_wkdays` = ?, `event_notify_type` = ?, `hoursto` = ?  WHERE id=?',
                [ $ev['status'], $ev['end'], $ev['ntf'], $ev['ntftext'], $ev['ntftime'], json_encode($ev['ntfwkdays']), $ev['endtype'], $ev['hoursto'], $id ]);
        }



        /*
        if ($mailingType == 'voting') {
            $mailing_variants = json_decode($mailing_variants, true);
            foreach ($mailing_variants as $r) query("INSERT INTO mailing_voting_variants (id_mailing, variant) VALUES (:t1, :t2);", [':t1'=> $id, ':t2'=> $r]);
        }
        # если пользовательская клавиатура
        if ($mailingType == 'custom') {
            updateQuery("UPDATE mailings SET `keyboard` = :kb WHERE id = :id", [':kb'=> $keyboard, ':id'=> $id]);
        }
        if ($mailingType == 'blockchain') {
            deleteQuery("DELETE FROM mailing_blockchains WHERE id_mailing = :id", [':kb'=> $keyboard, ':id'=> $id]);
            $mailing_blockchains = json_decode($mailing_blockchains, true);
            $kb = [];
            foreach ($mailing_blockchains as $b) {
                array_push($kb, [["text" => $b['label'], "callback_data" => json_encode(['s' => $b['blockchain']]) ]]);
                query("INSERT INTO `mailing_blockchains` (`id_mailing`, `blockchain`, `text`) VALUES (:t1, :t2, :t3)", [':t1'=> $id, ':t2'=> $b['blockchain'], ':t3'=> $b['label']]);
            }
            if (is_array($kb)&&count($kb)) {
                $kb = ["inline_keyboard" => $kb];
                updateQuery("UPDATE mailings SET `keyboard` = :kb' WHERE id = :id", [':kb'=> json_encode($kb), ':id'=> $id]);
            }
        }
        */
        
        return [
             'success'=> 'ok'
            ,'data'=> singleQuery('SELECT m.* FROM mailings m WHERE id = ?', [ $id ])
        ];
}


    function newTemplate( $input ){
        if (!$input["n"])
            return bterr('не передано имя шаблона');
        if(!$id = query('INSERT INTO `mailings_templates` (`name`,`parameters`, `parent`) VALUES (?,?,0)', [ $input['n'],json_encode($input['d']) ]))
            return db()->err()?:bterr('Ошибка при создании шаблона');
        
        return ['success'=> 'ok', 'id'=> $id];
    }

    function saveTemplate( $input ){
        if (!$input["id"])
            return bterr('не передан id');
        query('UPDATE `mailings_templates` SET `parameters`=? WHERE `id`=?',[ json_encode($input['d']),$input["id"] ]);
        if($e=db()->err()) return $e;
        return ['success'=> 'ok', 'id'=> $id];
    }

    function deleteTemplate( $input ){
        if (!$input["id"])
            return bterr('не передан id');
        query('DELETE FROM `mailings_templates` WHERE id=?', [ $input['id'] ]);
        query('UPDATE `mailings_templates` SET `parent` = 0 WHERE `parent`=?',[ $input['id'] ]);
        return ['success'=> 'ok'];
    }

    function getTemplate( $input ){
        if (!$input["id"])
            return bterr('не передан шаблон');
        if(!$t=singleQuery('SELECT * FROM `mailings_templates` WHERE id=:t OR name=:t',[ ':t'=>$input["id"] ]))return bterr('фильтр не найден');
        $d=json_decode($t['parameters'],1);
        $d['files'] = loadFiles()->getFilesforweb( $d['filegroup'] );

        return [
            'success'=> 'ok',
            'name'=> $t['name'],
            'data'=> $d,
        ];
    }

    function saveFilter( $input ){
        if (!$input["id"])
            return bterr('не передан id');
        if($input['query']&&!singleQuery('EXPLAIN '.$input['query']))
            return bterr('Некорректный sql запрос');
        if($e=db()->err()) return $e;
        query('UPDATE `mailings_filters` SET `name` = ?, `query`=?, parent=? WHERE id=?', [ $input['name'], $input['query'], $input['parent'], $input['id'] ]);
        if($e=db()->err()) return $e;
        return ['success'=> 'ok'];
    }

    function newFilter( $input ){
        if (!$input["name"])
            return bterr('не передано имя фильтра');
       
        if(!$id = query('INSERT INTO `mailings_filters` (`name`,`query`, `parent`) VALUES (?,"",0)', [ $input['name'] ]))
            return db()->err()?:bterr('Ошибка при создании фильтра');
        
        return ['success'=> 'ok', 'id'=> $id];
    }
    
    function deleteFilter( $input ){
        if (!$input["id"])
            return bterr('не передан id');
        
        query('DELETE FROM `mailings_filters` WHERE id=?', [ $input['id'] ]);
        query('UPDATE `mailings_filters` SET `parent` = 0 WHERE `parent`=?',[ $input['id'] ]);
        return ['success'=> 'ok'];
    }
    
    
}

















