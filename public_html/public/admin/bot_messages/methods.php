<?php

class cl
{

    public function get( $inputPar ){

        if(!$id_dial = $inputPar['id'])
            return bterr('недостаточно данных');

        $var = [];
        $result = arrayQuery('SELECT * FROM `dialogue_translate` WHERE id_dial = :id_dial', [ ':id_dial'=> $id_dial ]);

        foreach($result as $row)
            $var[] = ['id_lan'=>$row['id_lan'], 'body'=>$row['body']];
        $filesGroup=singleQuery('SELECT files FROM `dialogue` WHERE id=?',[ $id_dial ])['files'];
        if(is_numeric($filesGroup)) $filesGroup=$filesGroup*1;
        return [
            'success'=> 'ok'
            ,'var'=> $var
            ,'filesGroup' => $filesGroup
            ,'files'=> loadFiles()->getFilesforweb( $filesGroup )
        ];
    }

    public function kbGet( $input ){

        if(!$id = $input['id'])
            return bterr('недостаточно данных');
        $res =singleQuery('SELECT * FROM `s_keyboards` WHERE techname = ?', [$id]);

        $res['buttons'] = array_map(function($it) {
            return array_map(function($it) {
                return is_array($it)? $it: ['text'=> $it, 'visible'=> 1];
            }, $it);


        }, json_decode($res['buttons'],1));


        return [
            'success'=> 'ok'
            ,'data'=> $res
            ,'resize_keyboard'=> $res['resize_keyboard']
        ];
    }


    public function kbDelete( $par ){
        if(!$keyboard = $par['id'])
            return bterr('Не передан параметр "id"');
        $id = singleQuery('SELECT id FROM `s_keyboards` WHERE techname = ?', [$keyboard])['id'];
        query('DELETE FROM `s_keyboards` WHERE techname = ?', [$keyboard]);

        if($id)
            query('UPDATE `s_steps_messages` SET `id_keyboard` = 0 WHERE id_keyboard=?', [$id]);

        $filename = $_SERVER['DOCUMENT_ROOT'] . "/SECRETFOLDER/keyboards/$keyboard.php";
        unlink($filename);
        return json_encode([
            'success'=> 'ok'
        ]);
    }

    public function kbSave( $par ){
        if(!$keyboard = $par['id'])
            return bterr('Не передан параметр "id"');
        $buttons = json_decode($par["kb"], true);
        $resize_keyboard = 1;

        $systemMessages = [];

        $kbData = singleQuery('SELECT * FROM `s_keyboards` WHERE techname = :techname', [ ':techname'=> $keyboard ]);

        $i=0;

        if (!$keyboard)
            return bterr('недостаточно параметров');

        $filename = $_SERVER['DOCUMENT_ROOT'] . "/SECRETFOLDER/keyboards/$keyboard.php";
        $text = '<?' . PHP_EOL;
        $text.= 'function ' . $keyboard . '($par=[]){' . PHP_EOL;
        $text.= '$keyboard = array("keyboard" => array(), "one_time_keyboard" => false, "resize_keyboard" => ' . ($resize_keyboard == 1 ? 'true' : 'false') . ' );' . PHP_EOL;
        $remove_keyboard = false;
        $btnssrc=[];
        foreach ($buttons as $keyRow => $row) {
            $btnssrc[]= array_map(function($it) {
                return ['text'=> $it['val'], 'visible'=> $it['vis']];
            }, $row);
            $c = [];
            foreach ($row as $keyCol => $colArr) if($colArr['vis']){
                $col=$colArr['val'];
                $i++;
                switch ($col)
                {
                    case "[contacts]":
                        array_push($c, array("text" => '📞 Send contacts', 'request_contact' => true));
                        break;
                    case "[location]":
                        array_push($c, array("text" => '📍 Send location', 'request_location' => true));
                        break;
                    case "[remove]":
                        $remove_keyboard = true;
                        break;
                    default:
                        $systemMessages[] =
                            [
                                'uniqId'=> self::kb_name($keyboard, $keyRow, $keyCol)
                                ,'name'=> $kbData['name'].' /'.$i
                                ,'body'=> $col
                                ,'group'=> 'Клавиатуры'
                                ,'lan'=> 'ru'
                            ];

                        array_push($c, [ 'text' => '[translate='.self::kb_name($keyboard, $keyRow, $keyCol).']' ]);
                }
            }


            $text.= 'array_push($keyboard["keyboard"], ' . var_export($c, true) . ');' . PHP_EOL;

        }

        /**
         * Ищем и меняем триггеры
         */
        foreach ($buttons as $row) {
            foreach ($row as $col)
                if($col['val']!=='' && $col['old']!=$col['val']){
                    if(singleQuery('SELECT * FROM `s_triggers` WHERE name = ?', [$col['val']])) continue;
                    $trgrs = arrayQuery('SELECT t.priority, t.type_of_search, t.lang, s.id_script
                                     FROM s_triggers t
                                     JOIN s_triggers_steps s ON s.id_trigger = t.id
                                     WHERE name = ?', [$col['old']]);
                    foreach($trgrs as $trg){
                        $newTr = query('INSERT INTO `s_triggers` (`name`, `priority`, `type_of_search`, `lang`) VALUES (?,?,?,?)',[ $col['val'], $trg['priority'],$trg['type_of_search'],$trg['lang'] ]);
                        query('INSERT INTO `s_triggers_steps` (`id_script`, `id_trigger`) VALUES (?,?)', [ $trg['id_script'],$newTr ]);
                    }
                }
        }

        for($i=0;$i<20;$i++)
            for($q=0;$q<20;$q++)
                self::deleteMessage( self::kb_name($keyboard, $i, $q) );
        self::addSystemMessages($systemMessages);

        if ($remove_keyboard) $text.= '$keyboard = ["remove_keyboard" => true ];' . PHP_EOL;
        $text.= 'return $keyboard;' . PHP_EOL;
        $text.= '}' . PHP_EOL;
        $f_hdl = fopen($filename, 'w');
        fwrite($f_hdl, $text);

        file_put_contents($filename, $text);
        updateQuery('UPDATE s_keyboards SET `buttons` = :buttons, `resize_keyboard` = :resize_keyboard WHERE techname = :keyboard',
            [
                ':buttons' => json_encode($btnssrc)
                ,':keyboard' => $keyboard
                ,':resize_keyboard'=> $resize_keyboard
            ]);

        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return bterr($stmtErr);
        return
            [
                'success'=> 'ok'
            ];

    }

    private function kb_name($kb, $r, $c){
        return $kb.'_'.$r.'_'.$c;
    }



    private function addSystemMessages($systemMessages){
        $uniqIdList = [];

        foreach($systemMessages as $s){

            $uniqIdList[] = $s['uniqId'];
            if(!singleQuery('SELECT * FROM `dialogue` WHERE `name` = ?', [ $s['uniqId'] ])){

                # 1. Ищем группу, если не находим, то создаем её
                if(!$groupSQL = singleQuery('SELECT * FROM `dialogue_group` WHERE `name` = ?', [ $s['group'] ]))
                    $groupId = query('INSERT INTO `dialogue_group` (`name`, `type`) VALUES (?, 50)', [ $s['group'] ]);
                else
                    $groupId = $groupSQL['id'];
                # 1. Ищем язык, если не находим, то ставим по умолчанию 1
                if(!$lanSQL = singleQuery('SELECT * FROM `s_langs` WHERE `name` = :lan OR `iso` = :lan', [ ':lan'=> $s['lan'] ]))
                    $lanId = 1;
                else
                    $lanId = $lanSQL['id'];

                # добавляем в таблицу информацию о сообщении
                $dialId = query('INSERT INTO `dialogue` (`name`, `description`, `id_group`, `files`,`t_sort`) VALUES (?,?,?,0,0)', [ $s['uniqId'], $s['name'], $groupId ]);
                # добавляем вариант перевода
                query('INSERT INTO `dialogue_translate` (`id_dial`, `id_lan`, `body`) VALUES (?,?,?)', [ $dialId, $lanId, $s['body'] ]);
            }
        }

    }

    private function deleteMessage($message){

        if(!$mess = singleQuery('SELECT id, id_group FROM `dialogue` WHERE name = :name', [ ':name'=> $message ])) return;

        query('DELETE FROM `dialogue` WHERE name = :name', [ ':name'=> $message ]);
        query('SELECT * FROM `dialogue_translate` WHERE id_dial = :id', [ ':id'=> $mess['id'] ]);

        if(!singleQuery('SELECT id FROM `dialogue` WHERE id_group = :id_group', [ ':id_group'=>  $mess['id_group']]))
            query('DELETE FROM `dialogue_group` WHERE id = :id', [ ':id'=>  $mess['id_group'] ]);

    }










    public function edit( $inputPar ){
        $dialogId = $inputPar['dialogId'];
        $val = $inputPar['val'];
        $var = $inputPar['var'];
        if (!$var || !$dialogId)
            return bterr('Недостаточно параметров');

        updateQuery("UPDATE `dialogue` SET `$var` = :val WHERE `id` = :id", [':val' => $val, ':id'=> $dialogId]);

        return json_encode(['success'=> 'ok']);
    }

    public function saveGroups( $inputPar ){
        $groups = json_decode($inputPar["groups"], true);

        if(!count($groups))
            return bterr('передан пустой массив');

        foreach($groups as $group){
            $name=$group['name'];
            $id=$group['id'];
            if($id)
                updateQuery('UPDATE `dialogue_group` SET `name` = :name WHERE `id` = :id', [ ':name'=> $name, ':id'=> $id ]);
            else
                if($name!='')
                    query('INSERT INTO `dialogue_group` (`name`) VALUES (:name)', [ ':name'=> $name ]);
        }

        $resSql = arrayQuery('SELECT id id_group, name FROM dialogue_group ORDER BY name');

        return json_encode([
            'success'=> 'ok'
            ,'groups'=> $resSql
        ]);
    }


    private function variants( $text ){
        $arr = array_map(function($value) {return trim($value);}, explode(' ', $text));

        $resArr = [];
        for($q = 0; $q<count($arr); $q++)
            for($i = 0; $i<count($arr); $i++){
                $arrn=$arr;
                foreach($arrn as $key=> $r)
                    if($key==$i)
                        for($z = $key; $z<=$q; $z++)
                            if(isset($arrn[$z]))
                                $arrn[$z] .= ' ';
                $resArr[] = trim(implode('', $arrn));
            }

        return array_unique($resArr);
    }

    public function sendTranslation( $inputPar ){
        $text = $inputPar['text'];
        $lang = $inputPar['lang'];

        if(!$text || !$lang)
            return bterr('недостаточно параметров');

        if(!$langId = singleQuery('SELECT id FROM `s_langs` WHERE iso LIKE(?)', [ $lang ])['id']){
            $langId = query('INSERT INTO `s_langs` (`iso`, `name`) VALUES (:lan, :lan)', [ ':lan'=> $lang ]);
        }



        /**
         * восстанавливаем сломанные переводчиком теги
         */
        $vararr = [ ['var'=> '[ / # ]', 'val' => '[/#]'] ];
        $varr = ['code', 'b', 'strong', 'i', 'u', 's', 'em', 'ins', 'strike', 'pre'];
        foreach($varr as $varrow){
            $vararr[] = ['var'=> '< '.$varrow.' >', 'val' => '<'.$varrow.'>'];
            $vararr[] = ['var'=> '< / '.$varrow.' >', 'val' => '</'.$varrow.'>'];
        }
        foreach($vararr as $res)
            $text = str_replace(self::variants($res['var']), $res['val'], $text);




        $resSql = arrayQuery('SELECT * FROM `dialogue_translate` WHERE id_lan = 1 ORDER BY id_dial');

        $notFoundVar = [];
        foreach($resSql as $resRow){
            $translate = getStrBetween($text, '[#'.$resRow['id'].']', '[/#]');

            if($translate){
                # ищем переменные
                $body = singleQuery('SELECT body FROM `dialogue_translate` WHERE id_dial = :id_dial AND id_lan = 1', [ ':id_dial'=> $resRow['id_dial'] ])['body'];

                $tVarList = [];
                for($i=0;$i<100;$i++){
                    if(!$tVar = getStrBetween($body, '{', '}')) break;

                    $charsVar = preg_replace("/[^a-zA-Zа-яА-Я0-9]/ui", '', $tVar);
                    if(in_array($tVar, $tVarList)===false)
                        $tVarList[$charsVar] = $tVar;
                    $body = str_replace('{'.$tVar.'}', '', $body);
                }

                $tempTranslate = $translate;
                $tItogList = [];
                for($i=0;$i<100;$i++){
                    if(!$tVar = getStrBetween($tempTranslate, '{', '}')) break;
                    $charsVar = preg_replace("/[^a-zA-Zа-яА-Я0-9]/ui", '', $tVar);
                    if(in_array($tVar, $tItogList)===false)
                        $tItogList[$charsVar] = $tVar;
                    $tempTranslate = str_replace('{'.$tVar.'}', '', $tempTranslate);
                }

                $summaryList = [];

                foreach($tItogList as $key=> $itog)
                    if($itog <> '' && isset($tVarList[$key])){
                        $summaryList[$itog] = $tVarList[$key];
                        unset($tItogList[$key]);
                        unset($tVarList[$key]);
                    }


                if(count($tItogList)){
                    if(count($tItogList)==1 && count($tVarList)==1)
                        $summaryList[reset($tItogList)] = reset($tVarList);
                    else {
                        foreach($tItogList as $key=> $itog)
                            $notFoundVar[$resRow['id_dial']] = ['var'=> $itog, 'text'=> $translate];
                    }


                }

                foreach($summaryList as $key=> $val){

                    $translate = str_replace('{'.$key.'}', '{'.$val.'}', $translate);

                    if(strpos($translate, '{')===false || strpos($translate, '}')===false)
                        break;
                }
                $this->sendText([ 'iddial'=> $resRow['id_dial'], 'idlan'=> $langId, 'body'=> $translate ]);

            }

        }

        return  json_encode([
            'notFoundVar'=> $notFoundVar
            ,'success'=> 'ok'
        ]);
    }


    public function getTextForTranslation( $inputPar ){
        $lang = singleQuery('SELECT id FROM `s_langs` WHERE iso = ?', [ $inputPar['lang'] ])['id'];

        $resSql = arrayQuery('SELECT id, body FROM `dialogue_translate`
                              WHERE id_lan = 1 AND id_dial NOT IN(SELECT id_dial FROM `dialogue_translate` WHERE id_lan = ?)
                              ORDER BY id_dial', [ $lang ]);




        $t = '';
        $arr = [];
        foreach($resSql as $key=> $resRow){
            $t .= '[#'.$resRow['id'].']'.$resRow['body'].'[/#]'.PHP_EOL;
            if(mb_strlen($t)>7000){
                $arr[]=$t;
                $t = '';
            }
            if($key==(count($resSql)-1) && $t<>''){
                $arr[]=$t;
                $t = '';
            }
        }

        return json_encode([
            'success'=> 'ok'
            ,'rows'=> $arr
        ]);
    }


    public function sendLanlist( $inputPar ){
        $languages = json_decode($inputPar['languages'], 1);

        if(!count($languages))
            return bterr('передан пустой массив');

        foreach($languages as $l){

            if($l['id'])
                query('UPDATE `s_langs` SET `name` = ?, `iso` = ? WHERE `id` = ?', [ $l['name'],$l['iso'],$l['id'] ]);
            else
                if($l['name'])
                    query('INSERT INTO `s_langs` (`name`, `iso`) VALUES (?,?)', [ $l['name'],$l['iso'] ]);
        }

        $resSql = arrayQuery('SELECT id id_lan, `name`, `iso` FROM `s_langs` ORDER BY `iso`');

        return [
            'languages'=> $resSql
            ,'success'=> 'ok'
        ];
    }


    public function deleteLan( $inputPar ){
        if(!$lanId = $inputPar['lanId'])
            return bterr('не передан id языка');

        $code = singleQuery('SELECT iso FROM `s_langs` WHERE id = ?', [ $lanId ])['iso'];
        query('DELETE FROM `s_langs` WHERE id = ?', [ $lanId ]);
        query('DELETE FROM `dialogue_translate` WHERE id_lan = ?', [ $lanId ]);
        query('DELETE FROM `s_triggers` WHERE `lang` = ?', [ $code ]);
        return ['success'=>'ok'];
    }




    public function deleteGroup( $inputPar ){
        $groupId = $inputPar['groupId'];

        if(!$groupId)
            return bterr('не передан id группы');
        /*
        $resSql = arrayQuery('SELECT * FROM `dialogue` WHERE id_group = :id', [ ':id'=> $groupId ]);
        if(count($resSql))
            return bterr('в данной группе есть сообщения ('.count($resSql).'), сначала переместите их в другую группу'); */

        query('DELETE FROM `dialogue_group` WHERE id = :id', [ ':id'=> $groupId ]);
        query('UPDATE `dialogue` SET `id_group` = 0 WHERE id_group = :id_group', [ ':id_group'=> $groupId ]);

        return json_encode([ 'success'=> 'ok' ]);
    }



    public function sendText( $inputPar ){
        $id_lan  = $inputPar['idlan'];
        $id_dial = $inputPar['iddial'];
        $body    = $inputPar['body'];

        if(!$id_dial ||!$id_lan)
            return bterr('недостаточно данных');
        $body = trim($body);
        query('DELETE FROM `dialogue_translate` WHERE id_dial = ? AND id_lan = ?', [ $id_dial, $id_lan ]);

        if($body)
            query('INSERT INTO `dialogue_translate` SET id_dial = ?, id_lan = ?, body = ?', [ $id_dial, $id_lan, $body ]);

        $lanCode = singleQuery('SELECT iso FROM `s_langs` WHERE id = ?', [ $id_lan ])['iso'];

        # если клавиатура, то добавляем в триггеры
        $group = singleQuery('SELECT g.type FROM `dialogue` d
                              JOIN `dialogue_group` g ON g.id = d.id_group
                              WHERE d.id = ?', [ $id_dial ])['type'];
        if($group==100 && !singleQuery('SELECT * FROM `s_triggers` WHERE `name` = ?', [ $body ])){

            $sourceTranslate = singleQuery('SELECT body FROM `dialogue_translate` WHERE id_dial = :id_dial AND id_lan = 1', [ ':id_dial'=> $id_dial ])['body'];
            if($triggerScript = singleQuery('SELECT s.id_script, t.* FROM `s_triggers` t
                                       JOIN s_triggers_steps s ON s.id_trigger = t.id
                                       WHERE t.name = :name', [ ':name'=> $sourceTranslate ])){

                $id_trigger = query('INSERT INTO `s_triggers` (`name`, `priority`, `type_of_search`, `lang`) VALUES (:name, :priority, :type_of_search, :lang)',
                    [ ':name'=> $body, ':priority'=> $triggerScript['priority'], ':type_of_search'=> $triggerScript['type_of_search'], ':lang'=> $lanCode ]);
                if($id_trigger)
                    query('INSERT INTO `s_triggers_steps` (`id_script`, `id_trigger`) VALUES (:id_script, :id_trigger)', [ ':id_script'=> $triggerScript['id_script'], ':id_trigger'=> $id_trigger ]);
            }

        }

        return ['success'=>'ok'];
    }

    public function dialogue_translate( $inputPar ){
        /**

        require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';

        $to_lang = $inputPar['lanId'];

        $key = 'trnsl.1.1.20170824T151703Z.f5469b9d85a95548.d7c83b58ccd54b612d17fd21849dcf1069362dd7';

        $res = [];




        post('https://translate.api.cloud.yandex.net/translate/v2/translate', [ 'sourceLanguageCode' ]);

        return;

        if(!$to_lang)
        return bterr('не передан id языка');

        if($to_lang==1)
        return bterr('нельзя перевести основной язык');


        if (!($gethash = permission_to_use())['access']) {
        return json_encode($gethash['mess']);
        return;
        }

        if( !$codeLang = singleQuery('SELECT iso FROM `s_langs` WHERE id = ?', [ $to_lang ])['iso'] )
        return bterr('язык не найден в базе');

        $sourceLang = singleQuery('SELECT iso FROM `s_langs` WHERE id = 1')['iso'];

        $i = 0;
        $source = arrayQuery('SELECT * FROM `dialogue_translate` WHERE id_lan = 1 LIMIT 1');
        foreach($source as $sourceDial)
        if($textSource = $sourceDial['body'])
        {
        $id_dial = $sourceDial['id_dial'];
        if( !$searchLan = singleQuery('SELECT * FROM `dialogue_translate` WHERE id_dial = :id_dial AND id_lan = :id', [ ':id'=> $to_lang, ':id_dial'=> $id_dial ] ) ){
        $i++;
        if($translateRes = get_translate( ['text'=> $textSource, 'from_lang'=> $sourceLang, 'to_lang'=> $codeLang ] )){
        $textTranslate = $translateRes['text'];

        //query('INSERT INTO `dialogue_translate` (`id_dial`, `id_lan`, `body`) VALUES ( :id_dial, :id_lan, :body )', [ ':id_dial'=> $id_dial, ':id_lan'=> $to_lang, ':body'=> $textTranslate ]);
        }


        }
        }



        return json_encode( [ 'success'=> 1, 'res'=> $textTranslate] );



        function get_translate($par){
        global $key;
        $text = $par['text'];
        $from_lang = $par['from_lang'];
        $to_lang = $par['to_lang'];
        $text = urlencode($text);
        $to_lang = mb_strtolower($to_lang);
        $from_lang = mb_strtolower($from_lang);

        //$res = json_decode(file_get_contents('https://translate.yandex.net/api/v1.5/tr.json/detect?text=$text&key=$key'.($possible_lang ? "&hint=$possible_lang" : '')), true);
        //
        //if($res[code] != 200)
        //    return [ success => false, text => 'unable to determine language' ];

        $res_t = file_get_contents("https://translate.yandex.net/api/v1.5/tr.json/translate?text=$text&lang=$from_lang-$to_lang&key=$key");

        t_log("https://translate.yandex.net/api/v1.5/tr.json/translate?text=$text&lang=$from_lang-$to_lang&key=$key");
        t_log($res_t, true);
        $res_t = json_decode($res_t, true);

        if($res[code] != 200)
        return [ success => false, text => 'unable to translate text' ];

        return [ success => true, text => $res_t['text'] ];

        }


        return;





        $from_lang = $res[lang];

        if($from_lang == $to_lang)
        {

        if($to_lang != 'ru') $to_lang = 'ru'; else $to_lang = 'en';
        }

        // if(!lang_pair($from_lang, $to_lang))
        // return json_encode(array(success => 3, from_lang => $from_lang, to_lang => $to_lang));
        // else
        {
        $res_t = json_decode(file_get_contents("https://translate.yandex.net/api/v1.5/tr.json/translate?text=$text&lang=$from_lang-$to_lang&key=$key"), true);

        if($res[code] != 200)
        {
        return json_encode(array(success => 102, text => 'unable to translate text'));
        exit;
        }

        $text_arr = $res_t[text];

        if(count($text_arr) == 0)
        {
        return json_encode(array(success => 103, text => 'returned an empty result'));
        exit;
        }

        $check_text = urlencode($text_arr[0]);

        $check_res = json_decode(file_get_contents("https://translate.yandex.net/api/v1.5/tr.json/detect?text=$check_text&key=$key&hint=$to_lang"), true);
        if($check_res[code] != 200)
        {
        return json_encode(array(success => 104, text => 'the language of the final translation does not match'));
        exit;
        }

        if($check_res[lang] != $to_lang)
        {
        return 'mismatch of languages';
        exit;
        }


        return json_encode(['success' => 'ok', 'text' => $text_arr]);
        }

         */
    }

}

















