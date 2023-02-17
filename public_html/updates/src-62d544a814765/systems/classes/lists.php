<?php

namespace systems\classes\lists;

class lists
{
    public function _message_list( array $par=[] ){
        /**
         * передаем id набора данных - dataId, получить его можно через setData
         * данные:
         *   mask  - имя функции, которая выводит информацию
         *   class - имя класса с namespace, которому принадлежит функция, получить можно с помощью get_class()
         *   query - sql запрос
         *   limit - количество строк вывода
         *   payload - дополнительные параметры, если нужно
         *   ifEmpty - сообщение, если список пуст
         *   deleteShowKey - удалять кнопку, вызвавшую список
         *   prepareData - массив значений для подстановки в препарацию
         *
         * пример:
         *
        # use systems\classes\lists\lists as lists;
        $data=[
        'mask'=> 'mask'
        ,'query'=> $sql
        ,'limit'=> 5
        ,'class'=> get_class()
        ,'payload'=> $par
        ];
        lists()->_message_list(['dataId'=> setData($data)]);
         *
         * чтобы подключить кнопку пагинации, в маске добавьте 'callback_data' => json_encode($par['pagination_key'])
         * пример:
         *
        $kb = [];
        if($par['pagination_key'])
        array_push($kb, [
        ['text' => 'Далее', 'callback_data' => json_encode($par['pagination_key'])]
        ]);
        $kb=["inline_keyboard"=>$kb];
        send_mess([ 'id_chat'=> $chat_id, 'body'=>$text, 'kb'=> $kb ]);
         *
         */

        # удаляем кнопку
        $obj = json_decode(input_data);
        if($par['deleteShowKey'] && $cb = $obj-> callback_query){
            $rm = $obj-> callback_query-> message-> reply_markup;
            foreach($rm-> inline_keyboard as $key=> $val)
                if($val[0]-> callback_data == $cb-> data){
                    unset($rm-> inline_keyboard[$key]);
                    $message_id = $obj-> callback_query-> message-> message_id;
                    $chat_id = $obj-> callback_query-> message-> chat-> id;
                    methods()->edit_inline_keyboard($chat_id, $message_id, $rm);
                }
        }

        $dataId = $par['dataId'];
        $data = getData($dataId);
        $mask  = $data['mask'];
        $query = $data['query'];
        $prepareData = $data['prepareData'];
        $limit = isset($data['limit'])?$data['limit']:5;
        $limit_begin   = isset($data['limit_begin'])?$data['limit_begin']:0;
        $arr = explode('\\', $data['class']);
        loadModule($arr[count($arr)-1]);
        $class  = new $data['class'];

        $list = arrayQuery($query.' LIMIT '.$limit_begin.','.$limit,$prepareData?:[]);
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return notification($stmtErr);
        $ifEmpty = $data['ifEmpty'] ? $data['ifEmpty'] : '🤷🏻‍♂️ the list is empty';

        if(count($list)>=$limit){
            $count = singleQuery('SELECT count(*) cc FROM '.substr($query, strpos( mb_strtolower($query), 'from')+4),$prepareData?:[] ) ['cc'];
            if(!$count)
                $count = count(arrayQuery($query,$prepareData?:[]));
            if(!$count)
                $count = 0;
        }

        if(!count($list))
            tgMess( $ifEmpty );

        # данные с таблицы в базу
        foreach($list as $key=> $item){
            if(count($list)-1===$key && $count>$limit_begin+$limit){
                $item['pagination_key']=[ 'system'=> '_message_list',  'dataId'=> $dataId, 'deleteShowKey'=> 1 ];
            }

            if(!isset($item['payload']) && isset($data['payload']))
                $item['payload'] = $data['payload'];
            $class->$mask($item);

        }

        $data['limit'] = $limit;
        $data['limit_begin'] = $limit_begin+$limit;
        updateData($dataId, $data);
    }




    public function _strings_list( array $par=[] ){
        global $chat_id, $message_id, $text_message;

        /**
         * передаем id набора данных - dataId, получить его можно через setData
         * данные:
         *   mask  - имя функции, которая выводит информацию
         *   class - имя класса с namespace, которому принадлежит функция, получить можно с помощью get_class()
         *   query - sql запрос
         *   limit - количество строк вывода
         *   payload - дополнительные параметры, если нужно
         *   title - текст сообщения (по умолчанию 'список')
         *   lowerKeys - клавиши снизу (между списком и пагинацией)
         *
         * пример:
         *
        # use systems\classes\lists\lists as lists;
        $data=[
        'mask'=> 'mask'
        ,'query'=> $sql
        ,'limit'=> 5
        ,'class'=> get_class()
        ,'payload'=> $par
        ,'title'=> 'список'
        ];
        lists()->_strings_list(['dataId'=> setData($data)]);
         * mask должна выдать двоичный массив inline клавиш
         * пример:
         *
        $kbKey =
        [
        ['text' => 'Кнпка 1', 'callback_data' =>  '']
        ,['text' => 'Кнпка 2', 'callback_data' =>  '']
        ];

        return $kbKey;
         *
         */


        $dataId = $par['dataId'];
        $data = getData($dataId);
        $mask  = $data['mask'];
        $query = $data['query'];
        $files = $data['files'];
        $limit = isset($par['!ls'])?$par['!ls']:(isset($data['limit'])?$data['limit']: 5);
        $limit_begin = isset($par['!lb'])?$par['!lb']:(isset($data['limit_begin'])?$data['limit_begin']: 0);
        $ifEmpty = $data['ifEmpty'] ? $data['ifEmpty'] : '🤷🏻‍♂️ the list is empty';

        $data['limit'] = $limit;
        $data['limit_begin'] = $limit_begin;

        $text = $data['title']?$data['title'].PHP_EOL:'';

        $lowerKeys = $data['lowerKeys']; # кнопки снизу

        $arr = explode('\\', $data['class']);
        loadModule($arr[count($arr)-1]);
        $class  = new $data['class'];
        $list = arrayQuery($query.' LIMIT '.$limit_begin.','.$limit.';');
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return notification($stmtErr);
        if(!count($list))
            return notification( $ifEmpty );

        $data['!ls'] = $limit;
        $data['!lb'] = $limit_begin+$limit;
        updateData($dataId, $data);

        if(isset($data['payload']))
            $kb = keyboards()->getInlineKeyboardFromSystemMess($data['payload']);

        if(!is_array($kb)) $kb=[];

        $numberingKeys=[];
        foreach($list as $key=> $item){
            if(!isset($item['payload']) && isset($data['payload']))
                $item['payload'] = $data['payload'];
            $item['_dataId'] = $dataId;


            if($data['numbering']){
                $ncb = text()->variables($data['numbering_callback_data'], $item);
                if(is_array($data['payload']))
                    $ncb = text()->variables($ncb, $data['payload']);

                $strnum = '<b>'.str_pad(($key+1), $limit>9?2:1, "0", STR_PAD_LEFT).'.</b> ';
                $numberingKeys[]=["text" => str_pad(($key+1), $limit>9?2:1, "0", STR_PAD_LEFT), "callback_data" => $ncb];
            }

            $text .= ($strnum).($class->$mask($item)).PHP_EOL;
            $files = text()->variables($files, $item);
        }

        if($data['footer'])
            $text .= PHP_EOL.$data['footer'];


        if(count($numberingKeys))
            $kb = array_merge($kb,array_chunk($numberingKeys,8));


        foreach($lowerKeys as $lowerKey)
            $kb[] = $lowerKey;

        $kb = text()->substituteVariablesInAnArray($kb, $item);

        $data['dataId'] = $dataId;


        $kb=keyboards()->deleteEmptyInlkeys($kb);

        if($data['last_keys_to_pagination']){

            $data['last_keys_to_pagination']=end($kb);
            unset($kb[count($kb)-1]);
        }

        $pagination_keys = $this->pagination_keys($data, '_strings_list');
        if($pagination_keys!==false)
            $kb[] = $pagination_keys;

        $kb=["inline_keyboard"=>array_values($kb)];

        $checkInline = json_decode($text_message, true);

        if(is_array($checkInline) && (isset($checkInline['system']) && $checkInline['system'] == '_strings_list') || $data['update'] )
            if($files)
                methods()->editMessageMedia($chat_id, $message_id, $files, $text,$kb);
            else
                methods()->edit_message_text_or_caption($text, $kb, $chat_id, $message_id);
        else
            send_mess([ 'id_chat'=> $chat_id, 'body'=>$text, 'kb'=> $kb, 'files'=> $files ]);
    }




    public function _inline_list( array $par=[] ){
        global $chat_id, $message_id, $text_message;
        /**
         * передаем id набора данных - dataId, получить его можно через setData
         * данные:
         *   mask  - имя функции, которая выводит информацию (обязательно)
         *   class - имя класса с namespace, которому принадлежит функция, получить можно с помощью get_class() (обязательно)
         *   query - sql запрос (обязательно)
         *   limit - количество строк вывода
         *   payload - дополнительные параметры
         *   title - текст сообщения (по умолчанию 'список')
         *   lowerKeys - клавиши снизу (между списком и пагинацией)
         *   columns - количество столбцов
         *   update - true/false - обновлять сообщение
         *   ifEmpty - сообщение, если список пуст
         *
         * пример:
        # use systems\classes\lists\lists as lists;
        $data=[
        'mask'=> 'mask'
        ,'query'=> $sql
        ,'limit'=> 5
        ,'class'=> get_class()
        ,'payload'=> $par
        ,'title'=> 'список'
        ,'update'=> false // если обновить предыдущее сообщение, то true
        ];
        lists()->_inline_list(['dataId'=> setData($data)]);
         *
         * mask должна выдать массив inline клавиш
         * пример без параметра columns:
        $kbKey =
        [
        [
        ['text' => 'Кнопка 1', 'callback_data' =>  'key 1']
        ,['text' => 'Кнопка 2', 'callback_data' =>  'key 2']
        ]
        ,[
        ['text' => 'Кнопка 3', 'callback_data' =>  'key 3']
        ]
        ];

        return $kbKey;
         *
         * пример с параметром columns:
         *
        $kbKey =
        [
        ['text' => 'Кнопка 1', 'callback_data' =>  'key 1']
        ];

        return $kbKey;
         *
         */


        $dataId = $par['dataId'];
        $data = getData($dataId);
        $mask  = $data['mask'];
        $query = $data['query'];
        $chunkLimit=$data['chunkLimit']??0;
        $columns = (int) $data['columns'];
        $ifEmpty = $data['ifEmpty'] ?? '🤷🏻‍♂️ the list is empty';
        $limit = $par['!ls']??($data['limit']?? 5);
        $limit_begin = $par['!lb']??($data['limit_begin']??0);

        $data['limit'] = $limit;
        $data['limit_begin'] = $limit_begin;

        if($text = $data['title'])
            $text = text()->mb_str_pad($text, 40);

        $lowerKeys = $data['lowerKeys']; # кнопки снизу

        $arr = explode('\\', $data['class']);
        loadModule($arr[count($arr)-1]);
        $class  = new $data['class'];
        $list = arrayQuery($query.' LIMIT '.$limit_begin.','.$limit.';');
        if($stmtErr=$GLOBALS['stmt']->errorInfo()[2]) return notification($stmtErr);
        //if(!count($list))
        //    notification();

        $data['!ls'] = $limit;
        $data['!lb'] = $limit_begin+$limit;
        updateData($dataId, $data);

        if(isset($data['payload']) && !$data['disable_keyboard_from_payload']){
            $additionalKeyboard = keyboards()->getInlineKeyboardFromSystemMess($data['payload']);
        }


        if(!count($list))
            return notification( $ifEmpty );

        $kb = [];
        $tKb2 = []; // прогоняем через array_chunk
        //$tKb3 = []; // добавляем как есть без array_chunk, так как он уже готовый
        qwe('');
        foreach($list as $item){
            if(!isset($item['payload']) && isset($data['payload']))
                $item['payload'] = $data['payload'];
            $item['_dataId'] = $dataId;

            if($mask){
                $tKb = $class->$mask($item);
                if(is_array($tKb) && count($tKb))
                    foreach($tKb as $tKbRow){
                        $tKb2[]=  $tKbRow;
                    }

            }


            if($additionalKeyboard)
                foreach($additionalKeyboard as $additionalKeyboardKey){

                    foreach($additionalKeyboardKey as &$additionalKeyboardKey1)
                        foreach($additionalKeyboardKey1 as &$additionalKeyboardKey2){
                            $additionalKeyboardKey2 = text()->variables($additionalKeyboardKey2, $item);
                            $additionalKeyboardKey2 = text()->variables($additionalKeyboardKey2, $data['payload']);
                        }

                    if(count($additionalKeyboardKey))
                        $kb = array_merge($kb, $additionalKeyboardKey);
                }

            /*
             * если нет маски и клавиатуры в сообщении, то создаем обычную кнопку с callback
             */
            if(!$additionalKeyboard&&!$mask&&count($item)){
                $v=array_values($item)[0];
                $tKb2[] = ['text'=> $v, 'callback_data'=> 1];
            }

        }

        $kb = array_chunk($kb, $columns?:1);

        $data['dataId'] = $dataId;
        if($columns&&!$chunkLimit){
            $tKb2 = array_chunk($tKb2, $columns);
        }
        if($chunkLimit)
        {
            $tKb2=keyboards()-> array_strlen_chunk($tKb2, $chunkLimit);
        }

        $kb = array_merge($kb, $tKb2);

        foreach($lowerKeys as $lowerKey)
            $kb[] = $lowerKey;

        $kb=keyboards()->deleteEmptyInlkeys($kb);
        $tKb=[];
        foreach($kb as $k){
            $d=false;
            foreach($k as $r)
                if(is_array($r[0])){
                    $d=true;
                    $tKb[]=$r;

                }
            if(!$d) $tKb[]=$k;
        }
        $kb=$tKb;

        $pagination_keys = $this->pagination_keys($data, '_inline_list');
        if($pagination_keys!==false)
            $kb[] = $pagination_keys;

        $kb=["inline_keyboard"=>$kb];

        $checkInline = json_decode($text_message, true);
        if(is_array($checkInline) && (isset($checkInline['system']) && $checkInline['system'] == '_inline_list') || $data['update'] ){
            if($text==''){
                methods()->error(methods()->edit_inline_keyboard($chat_id, $message_id, $kb));
            } else {
                methods()->error(methods()->edit_message_text_or_caption($text, $kb, $chat_id, $message_id));
            }
        }

        else {
            if($text=='') $text= 'Список';
            return send_mess([ 'id_chat'=> $chat_id, 'body'=>$text, 'kb'=> $kb ]);
        }

    }



    private function pagination_keys($data, $callFunction) {
        $sql = $data['query'];

        $limit_begin = $data['!lb'];
        $limit_step = $data['!ls'];
        $dataId = $data['dataId'];

        $q='SELECT count(*) t_count FROM (SELECT 1 FROM '.substr($sql, strpos( mb_strtolower($sql), 'from')+4).') t';

        $row_count = singleQuery($q) ['t_count'];

        if(!$row_count)
            $row_count = count(arrayQuery($sql));

        if(!$row_count)
            $row_count = 0;

        if ($row_count > $limit_begin + $limit_step) $more = true;
        else $more = false;

        $count = $limit_step;
        $position = $limit_begin;



        $prev = $position - $count * 2;
        if ($prev < 0) $prev = 0;
        $page = ceil($position / $count);
        if($page < 999)
            $page = $this->unicodeNumConverter($page);
        $to_begin = json_encode(['system'=> $callFunction, '!lb' => 0, '!ls' => $count, 'dataId' => $dataId]);
        $next = json_encode(['system'=> $callFunction, '!lb' => $position, '!ls' => $count, 'dataId' => $dataId]);
        $previous = json_encode(['system'=> $callFunction, '!lb' => $prev, '!ls' => $count,  'dataId' => $dataId]);
        $to_end = json_encode(['system'=> $callFunction, '!lb' => $row_count - $count, '!ls' => $count, 'dataId' => $dataId]);
        $arrow = ['◀', '⏮️', '⏭️', '▶', ];
        $hash = '▫️️';
        $block = ["text" => $hash, "callback_data" => 'blockanswer'];
        $pagination = [];
        if ($position - $count > 0) {
            if(!$data['only_next_previous'])
                array_push($pagination, ["text" => $arrow[1], "callback_data" => $to_begin]);
            array_push($pagination, ["text" => $arrow[0], "callback_data" => $previous]);
        } else {
            if(!$data['only_next_previous'])
                array_push($pagination, $block);

            if($data['reverse'])
                array_push($pagination, ["text" => $arrow[0], "callback_data" => $to_end]);
            else
                array_push($pagination, $block);
        }

        if(!$data['only_next_previous'] && !$data['last_keys_to_pagination'])
            array_push($pagination, ["text" => $page, "callback_data" => 'blockanswer']);

        if( is_array($data['last_keys_to_pagination']) && count($data['last_keys_to_pagination']) )
            $pagination = array_merge($pagination, $data['last_keys_to_pagination']);

        if ($row_count - $position > 0) {
            array_push($pagination, ["text" => $arrow[3], "callback_data" => $next]);



            if(!$data['only_next_previous'])
                array_push($pagination, ["text" => $arrow[2], "callback_data" => $to_end]);
        } else {

            if($data['reverse'])
                array_push($pagination, ["text" => $arrow[3], "callback_data" => $to_begin]);
            else
                array_push($pagination, $block);


            if(!$data['only_next_previous'])
                array_push($pagination, $block);
        }

        if ($count >= $row_count)
            return false;

        return $pagination;
    }

    private function unicodeNumConverter($text) {
        return strtr( strtolower($text), [1 => '1️⃣', 2 => '2️⃣', 3 => '3️⃣', 4 => '4️⃣', 5 => '5️⃣', 6 => '6️⃣', 7 => '7️⃣', 8 => '8️⃣', 9 => '9️⃣', 0 => '0️⃣'] );
    }









}













