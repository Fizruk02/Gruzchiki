<?
//set_time_limit(0);

class cl
{
    
    
    
    public function answersList( $POST ){
       $startDate = $POST['startDate'];
       $endDate = $POST['endDate'];
        $filter='';
        $filterAttr = json_decode($POST['a'], true);
        $parQuery=[ ':startDate'=> $startDate, ':endDate'=> $endDate ];
        if($filterAttr['b']){
            $filter = ' AND ua.block = :block AND ua.answer = :answer';
            $parQuery[':block']=$filterAttr['b'];
            $parQuery[':answer']=$filterAttr['v'];
        }
        
        
        $dataQuery = arrayQuery('SELECT DATE_FORMAT(ua.date, "%d.%m.%Y %H:%i") `date`, id_chat user_id, IF(u.username <> "", u.username, u.first_name) user_name, s.name AS script, b.name AS block, ua.answer
             FROM script_user_answers ua
             JOIN script_blocks b ON b.id = ua.block
             JOIN usersAll u ON u.chat_id = ua.id_chat
             JOIN script_list s ON s.id = b.id_script
             WHERE date(ua.date) >= :startDate AND date(ua.date) <= :endDate '.$filter.'
             ORDER BY ua.date DESC',
        $parQuery, true);

        $answersResp = arrayQuery('SELECT s.id scriptId, s.name AS script, b.id blockId, b.name AS block, ua.answer, count(*) `count`
           FROM script_user_answers ua
           JOIN script_blocks b ON b.id = ua.block
           JOIN script_list s ON s.id = b.id_script
           WHERE date(ua.date) >= :startDate AND date(ua.date) <= :endDate
           GROUP BY s.id, b.id, ua.answer
           ORDER BY s.id, b.id',
        [ ':startDate'=> $startDate, ':endDate'=> $endDate ], true);
        
        $answers=[];
        foreach($answersResp as $a){
        if(!isset($answers[$a['scriptId']][$a['blockId']]))
            $answers[$a['scriptId']][$a['blockId']]=$a;
            $answers[$a['scriptId']][$a['blockId']]['list'][] = [ 'answer'=> $a['answer'], 'count'=> $a['count'] ];
        }

        $answers = array_map(function($value) {
            return array_values($value);
        }, array_values($answers));

        return json_encode( [
                 'success'=> 'ok'
                ,'data'=> $dataQuery
                ,'groups'=> $answers
                
            ] );
    }



    public function gotoSet($input){
        $par = [ 'script'=> $input['ids'], 'block'=> $input['idb'] ];
        query('UPDATE `script_blocks` SET `par` = ? WHERE id = ?', [ json_encode($par), $input['b'] ]);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return response_if_error($stmtErr);
        return [ 'success'=> 'ok' ];
    }
    
    
    function add( $POST ){
        $id = insertQuery('INSERT INTO `script_list` (`name`) VALUES (?)', [ $POST['name']]);
        
        return [
             'success'=> 'ok'
            ,'data'=> [ 'id'=> $id ,'name'=> $POST['name'] ]
        ];
    }
    
    function scriptsList( $POST ){
        $data = arrayQuery('SELECT * FROM `script_list`');
        foreach($data as &$d)
            $d['blocks'] = arrayQuery('SELECT * FROM `script_blocks` WHERE id_script = ? AND activate = 1 ORDER BY num', [ $d['id'] ]);
        return [
             'success'=> 'ok'
            ,'data'=> $data
        ];
    }
    
    function remove( $POST ){
        if(!singleQuery('SELECT * FROM `script_list` WHERE id = :id', [ ':id'=> $POST['id'] ]))
            return bterr('Скрипт не найден');

        query('DELETE FROM `script_list` WHERE id = ?', [ $POST['id'] ]);
        query('DELETE FROM `script_triggers` WHERE id_script = ?', [ $POST['id'] ]);
        query('DELETE FROM `script_blocks` WHERE id_script = ?', [ $POST['id'] ]);
        query('DELETE FROM `script_position` WHERE id_script = ?', [ $POST['id'] ]);
        query('DELETE FROM `script_answers` WHERE action = ?', [ $POST['id'] ]);
        
        query('UPDATE `script_list` SET `parent` = 0 WHERE `parent` = ?', [ $POST['id'] ]);
        return [
            'success'=> 'ok'
        ];
    }
    
    function getScriptSettings( $POST ){
        if(!$data = arrayQuery('SELECT * FROM `script_list` WHERE id=?', [$POST['id']]))
            return bterr('Скрипт не найден');
        
        return [
             'success'=> 'ok'
            ,'data'=> $data
            ,'scriptList'=> $this->scriptsList([])['data']
        ];
    }
    
    function get( $POST ){
        $data = singleQuery('SELECT *, IFNULL((SELECT GROUP_CONCAT(`trigger` SEPARATOR ", ") FROM `script_triggers` WHERE id_script = :id), "") `triggers` FROM `script_list` WHERE id = :id', [ ':id'=> $POST['id'] ]);
        $blocks = arrayQuery('SELECT * FROM `script_blocks` WHERE id_script = :id ORDER BY num', [ ':id'=> $POST['id']  ]);
        foreach($blocks as &$block){
            $block['answers'] = arrayQuery('SELECT a.`answer`, a.`action`, a.`actionblock`, a.`status`,
                                            IFNULL(s.name, IF(`action`="exit","Закончить", "Продолжить")) actionName,
                                            IFNULL(b.name, "") actionBlockname
                                            FROM `script_answers` a
                                            LEFT JOIN `script_list` s ON s.id = a.action
                                            LEFT JOIN script_blocks b ON b.id = a.actionblock
                                            WHERE a.id_block = :id_block', [':id_block'=> $block['id']]);
                                             
          

            $par = json_decode($block['par'], true);
            $block['par']=$par;
            if($par['files']){
                $block['group']=$par['files'];
                $block['files'] = loadFiles()->getFilesforweb( $par['files'] );
            } 
            switch($block['type']){
                case 'func':
                    $block['text'] = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/admin/scripts/functions/'.$par['name'].'.php');;
                break;
                case 'message':
                    $block['text'] = explode('||', $block['text']);
                break;
                case 'goto':
                    $sc = singleQuery('SELECT name FROM `script_list` WHERE id=?', [ $par['script'] ])['name'];
                    $bl = singleQuery('SELECT name FROM `script_blocks` WHERE id=?', [ $par['block'] ])['name'];
                    $block['caption']= $sc.($bl?' / '.$bl:'');
                break;
                
            }
        }
        return [
             'success'=> 'ok'
            ,'data'=> $data
            ,'blocks'=> $blocks
            
        ];
    }
    
    
    function st( $POST ){
        query('UPDATE `script_list` SET `status` = :st WHERE id = :id', [ ':id'=> $POST['id'], ':st'=> $POST['st'] ]);
    }
    
    
    function saveScriptpar($POST){
        $par = json_decode($POST['par'], true);
        foreach($par as $key=> $p){
            if($key=='parent')
                query('UPDATE `script_list` SET `parent` = ? WHERE `id` = ?', [$p,$POST['id']]);
        }
        return ['success'=> 'ok'];
    }
    
    function save( $POST ){
        $id = $POST['id'];
        $triggers = $POST['triggers'];

        if(!singleQuery('SELECT * FROM `script_list` WHERE id = :id', [ ':id'=> $id ]))
            return bterr('Скрипт не найден');
        
        
        $par=['success'=> 'ok'];
        
        if(isset($POST['name'])){
            query('UPDATE `script_list` SET `name` = :name WHERE id = :id', [ ':id'=> $id, ':name'=> $POST['name'] ]);
        }
        

        if(isset($POST['triggers'])){
            $triggers = json_decode($triggers, true);
            $triggers =  array_map('trim', $triggers );
            
            query('DELETE FROM `script_triggers` WHERE id_script = :id', [ ':id'=> $id ]);
            
            $duplicateTriggers='';
            $successTriggers=[];
            foreach($triggers as $trigger)
            if($trigger!=''){
                if(singleQuery('SELECT * FROM `script_triggers` WHERE `trigger` = :trigger AND id_script IN (SELECT id FROM `script_list`)', [ ':trigger'=> $trigger ])){
                    $duplicateTriggers .= $trigger.PHP_EOL;
                    continue;
                }
                $successTriggers[]=$trigger;
                query('INSERT INTO `script_triggers` (`id_script`, `trigger`) VALUES (?,?)', [ $id, $trigger ]);
            }
            $par['data']=[
                 'id'=> $id
                ,'duplicateTriggers'=> $duplicateTriggers ? $duplicateTriggers:false
                ,'triggers'=> implode(', ', $successTriggers)
            ];
        }
        
        
        

        
        return $par;
    }
    
    
    
    /**
     * 
     * 
     * ================== BLOCKS
     * 
     * 
     */
    
    
    
    function getBlock( $POST ){
        if(!$id = $POST['id'])
            return bterr('Не передан id блока');
        if(!$data = singleQuery('SELECT id, name, id_script, text, type, par, keyboard FROM `script_blocks` WHERE id = ?', [ $id ]))
            return bterr('Блок не найден в базе');
        
        $data['par'] = json_decode($data['par'], 1);
        $data['keyboard'] = json_decode($data['keyboard'], 1)?:'[]';
        
        return [
             'success'=> 'ok'
            ,'data'=> $data
        ];
    }
    
    
    function addBlock( $POST ){
        $type = $POST['type'];
        $name = $POST['name'];
        $id_script = $POST['id_script'];
        $num = $POST['num'];
        $text = $POST['text']? $POST['text']:$name;
        $par = [];
        switch($type){
            case 'func':
                $funcDir = $_SERVER['DOCUMENT_ROOT'].'/admin/scripts/functions/';
                if(!is_dir($funcDir)) mkdir($funcDir);
                $text = '';
                $functionName = $this->techname($name);
                $par['name'] = $functionName;
                $body =
                '<?'.PHP_EOL.
                '# не меняйте имя функции'.PHP_EOL.
                'function '.$functionName.'($par){'.PHP_EOL.
                '    '.PHP_EOL.
                "# sendMess(['text'=> 'hello']); # отправить сообщение".PHP_EOL.
                "# goTo( 'script name or id', \$par ); # перейти к указанному скрипту".PHP_EOL.
                "# return 'stop'; # не продолжать сценарий после функции".PHP_EOL.
                '}';
                file_put_contents($funcDir.'/'.$functionName.'.php', $body);
            break;
        }
        
        $id = insertQuery('INSERT INTO `script_blocks` (`name`, `text`, `id_script`, `type`, `num`, `activate`, `par`) VALUES (:name, :text, :id_script, :type, :num, 1, :par)',
        [
             ':num'=> $num
            ,':name'=> $name
            ,':type'=> $type
            ,':text'=> $text
            ,':id_script'=> $id_script
            ,':par'=> json_encode($par)
        ]);
        
        query('UPDATE `script_blocks` SET display = :id WHERE id = :id', [':id'=> $id]);
        
            switch($type){
                case 'func':
                    $text = $body;
                break;
                case 'message':
                    $text = explode('||', $text);
                break;
                
            }
           
        
        return [
             'success'=> 'ok'
            ,'data'=> [
                 'id'=> $id
                ,'name'=> $name
                ,'display'=> $id
                ,'text'=> $text
                ,'type'=> $type
                ,'activate'=> 1
                ,'par'=> $par
            ]
        ];
    }
    
    
    function saveKeyboard( $POST ){
        if(!$id = $POST['id'])
            return bterr('Не передан id блока');
            

        query('UPDATE `script_blocks` SET `keyboard` = :keyboard WHERE id = :id',
            [
                 ':id'=> $POST['id']
                ,':keyboard'=> $POST['keyboard']?$POST['keyboard']:'{}'
            ]);

        return [
             'success'=> 'ok'
        ];
    }
    
    function saveBlockpar( $POST ){
        if(!$id = $POST['id'])
            return bterr('Не передан id блока');
            

        query('UPDATE `script_blocks` SET `par` = :par WHERE id = :id',
            [
                 ':id'=> $POST['id']
                ,':par'=> $POST['par']?$POST['par']:'{}'
            ]);
        $par=singleQuery('SELECT par FROM `script_blocks` WHERE id=?',[ $id ]);
        $par=json_decode($par, true);
        return [
             'success'=> 'ok'
            ,'par'=> $par
        ];
    }
    
    public function blockSettingset($inputPar){
        $val = $inputPar['vl'];
        $var = $inputPar['vr'];
        $block = singleQuery('SELECT * FROM `script_blocks` WHERE id = ?', [ $inputPar['id'] ]);
        $par = json_decode($block['par'], true);
        if(!is_array($par)) $par = [];
        $par[$var]=$val;
        
        query('UPDATE `script_blocks` SET `par` = :par WHERE id = :id', [
            ':id'=> $inputPar['id'],':par'=> json_encode($par)
            ]);
        return json_encode([ 
             'success'=> 'ok'
             ,'par'=> $par
        ]);
    }
    
    
    function saveBlock( $POST ){
        $id = $POST['id'];
        $text = $POST['text'];
        $parameters = $POST['par'];
        $answers = json_decode($POST['answers'], true);

        if(!$block = singleQuery('SELECT * FROM `script_blocks` WHERE id = ?', [ $POST['id']] ))
            return bterr('Блок не найден');
        $parameters = $block['par'];
        $blockPar = json_decode($block['par'], true);
        switch($block['type']){
            case 'func':
                
                $blockCommands = [
                    'pdo','__dir__','__file__','query','$_server', '$globals',
                    'basename','chgrp','chmod','chown','clearstatcache','copy','delete','dirname','disk_free_space','disk_total_space','diskfreespace','fclose','feof','fflush','fgetc','fgetcsv','fgets','fgetss','file_exists','file_put_contents','file','fileatime','filectime','filegroup','fileinode','filemtime','fileowner','fileperms','filesize','filetype','flock','fnmatch','fopen','fpassthru','fputcsv','fputs','fread','fscanf','fseek','fstat','ftell','ftruncate','fwrite','glob','is_dir','is_executable','is_file','is_link','is_readable','is_uploaded_file','is_writable','is_writeable','lchgrp','lchown','link','linkinfo','lstat','mkdir','move_uploaded_file','parse_ini_file','parse_ini_string','pathinfo','pclose','popen','readfile','readlink','realpath_cache_get','realpath_cache_size','realpath','rename','rewind','rmdir','set_file_buffer','stat','symlink','tempnam','tmpfile','touch','umask','unlink'
                    ];
                $blockCommands=[];
                $block=[];
                foreach($blockCommands as $blockCommand)
                if(strpos(strtolower($text), $blockCommand)!==false)  $block[] = '«'.$blockCommand.'»';
                
                if(count($block))
                    return bterr('Недопустимо:<br>'.implode(', ', $block));
                
                file_put_contents($_SERVER['DOCUMENT_ROOT'].'/admin/scripts/functions/'.$blockPar['name'].'.php', $text);
                $text = '';
            break;
        }
        query('DELETE FROM `script_answers` WHERE `id_block` = :id_block', [ ':id_block'=> $id ]);
        foreach($answers as $answer)
            query('INSERT INTO `script_answers` (`id_block`, `answer`, `action`, `actionblock`, `status`) VALUES (?,?,?,?,?)', [ $id, $answer['answer'], $answer['action'], $answer['actionblock']?:0, $answer['status'] ]);
        
        query('UPDATE `script_blocks` SET `text` = :text, `par` = :par WHERE id = :id', [ ':id'=> $POST['id'], ':text'=> $text, ':par'=> $parameters ]);
        
        return [
            'success'=> 'ok'
        ];
    }
    
    
    function setDisplay( $POST ){
        if(trim($name = $POST['name'])==="")
            return bterr('Название переменной не может быть пустым');


        if(in_array($name, ['id_script', 'id', 'display', 'name', 'text', 'par', 'id_block', 'send']))
            return bterr('«'.$name.'» является системной переменной, дайте другое имя');

        if(!$block = singleQuery('SELECT * FROM `script_blocks` WHERE id = ?', [ $POST['id'] ] ))
            return bterr('Блок не найден');
            
        if(singleQuery('SELECT * FROM `script_blocks` WHERE display = ? AND id<>?', [ $name, $POST['id'] ] ))
            return bterr('Такая переменная уже существует');
        query('UPDATE `script_blocks` SET `display`=? WHERE id=?', [ $name, $POST['id'] ]);
            
        return [
            'success'=> 'ok'
        ];
    }
    
    
    
    

    function deleteBlock( $POST ){
        if(!$POST['id'])
            return bterr('Не передан id блока');
 
        if(!$block = singleQuery('SELECT * FROM `script_blocks` WHERE id = ?', [ $POST['id'] ] ))
            return bterr('Блок не найден');
        
        $blockPar = json_decode($block['par'], true);    
        switch($block['type']){
            case 'func':
                unlink($_SERVER['DOCUMENT_ROOT'].'/admin/scripts/functions/'.$blockPar['name'].'.php');
            break;
        }   
            
        query('DELETE FROM `script_blocks` WHERE id = ?', [ $POST['id'] ]);
        query('DELETE FROM `script_position` WHERE id_block = ?', [ $POST['id']]);
        query('DELETE FROM `script_answers` WHERE id_block = ?', [ $POST['id'] ]);
        return [
            'success'=> 'ok'
        ];
    }

    function sortBlock( $POST ){
        $blocks = json_decode($POST['blocks'], true);
        foreach($blocks as $key=> $block)
        query('UPDATE `script_blocks` SET `num` = :num WHERE id = :id', [ ':id'=> $block, ':num'=> $key]);
        
        return [
            'success'=> 'ok'
        ];
    }

    
    function sendBlocksetting( $POST ){

        query('UPDATE `script_blocks` SET `'.$POST['par'].'` = :val WHERE id = :id', [ ':id'=> $POST['id'], ':val'=> $POST['val']]);
        
        return [
            'success'=> 'ok'
        ];
    }
    
    
    
    function supselectinfo( $POST ){

        
        return [
             'success'=> 'ok'
            ,'script'=> singleQuery('SELECT name FROM `script_list` WHERE id = ?', [ $POST['script'] ])['name']
            ,'block'=> singleQuery('SELECT name FROM `script_blocks` WHERE id = ?', [ $POST['block'] ])['name']
        ];
    }
    
    

    
    
    
    
    
    
    
    
    private function techname($value)
    {
    	$converter = array(
    		'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
    		'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
    		'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
    		'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
    		'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
    		'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
    		'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
     
    		'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
    		'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
    		'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
    		'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
    		'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
    		'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
    		'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
    	);
     
    	$value = strtr($value, $converter);
    	$value = preg_replace('/[^a-zA-Z0-9 ]/ui', '', $value);
    	$value = preg_replace("/ {2,}/", " ", $value);
    	$value = strtolower($value);
    	$value = preg_replace('/[^a-zA-Z0-9]/ui', '_', $value);
    	return $value;
    }
    
    
    

    
}













