<?php

namespace project\modules\array_of_buttons;

class array_of_buttons
{
    public static $keyboarsd = [];

    public function start(array $par = [])
    {
        global $original, $chat_id, $username, $message_id;

        $chat_id*=1;
        $message_id*=1;

        $settings = json_decode($par['array_of_buttons'], true);
        foreach ($settings as &$tSetting){
            $tSetting = text()->variables($tSetting, $par);
            //$tSetting = text()->shortcodes($tSetting, $par);
        }

        $settings['silentmode']=1;

        $cols = $settings['cols']??1;
        $text = 'список';
        $kbp=false;
        if (isset($par['script_messages'])&&($messId = $par['script_messages'][0]['message'])) {
            $text = DIALTEXT($messId);
            if($par['script_messages'][0]['type_keyboard']==='keyboard'){
                $kbp=keyboards()-> getKeyboard($par['script_messages'][0]['kb_name']);
                $kbp = text()->substituteVariablesInAnArray( $kbp, $par );
            }
            $text = text()->variables($text, $par);
            $text = text()->shortcodes($text, $par);
        }

        $this->clearTableArrayButtons();

        $par['array_of_buttons'] = $settings;
        $arr = [];
        switch ($settings['type']) {

            case 'continue':

                /**
                 * Продолжить клавиатуру из другого сообщения
                 */
                $mess=$settings['continue_message_id'];

                $dt=singleQuery('SELECT * FROM `s_data_before_the_update` WHERE id_chat='.$chat_id.' AND id_mess='.$mess.' ORDER BY id DESC');

                query('DELETE FROM `s_data_before_the_update` WHERE id='.$dt['id']);

                $body=json_decode($dt['body'],1);
                $message=$body['callback_query']['message'];
                $cbData=json_decode($body['callback_query']['data'],1);

                $kb=$message['reply_markup'];
                $text=$message['text'];

                $text = text()->variables($text, $par);
                $text = text()->shortcodes($text, $par);

                $sm=send_mess([ 'body'=> $text, 'kb'=> $kb ]);

                if(isset($sm[0])) {
                    query('UPDATE `s_data_before_the_update` SET `id_mess` = '.$sm[0]['message_id'].' WHERE id_chat='.$chat_id.' AND id_mess='.$message['message_id']);
                }

                $dataId=$cbData['d'];

                $data=getData($dataId);
                $payload=$data['payload'];

                $listId=$payload['listId'];

                $arr = arrayQuery($payload['array_of_buttons']['sql']);




                foreach ($arr as $row)
                    query('INSERT INTO `array_of_buttons` (`listId`, `itemKey`, `itemVal`, `itemId`, `parent`, `chat_id`) VALUES (:listId, :itemKey, :itemVal, :itemId, :parent, '.$chat_id.')',
                        [':listId' => $listId, ':itemKey' => $row['itemKey'], ':itemVal' => $row['itemVal'], ':itemId' => $row['itemId'], ':parent' => $row['parent']]);

                $par['array_of_buttons']['dataId']=$dataId;
                if($settings['silentmode'])
                    set_pos($par['step'], $par);
                else
                    set_pos('array_of_buttons_inline_kb_listener', $par);

                return;

            case 'keyboard':
                $d=$settings['kbarr'];
                $cols=(int) $settings['kbcols']??0;
                if($cols<1) $cols=1;
                if($cols>12) $cols=12;

                # ищем sql запрос
                if(!is_array($d) && strpos( mb_strtolower($d), 'select' )===0 ){
                    $d=arrayQuery($d);
                    $d=array_map(function ($it){
                        return array_values($it)[0];
                    }, $d);
                }

                if(!is_array($d)) $d=json_decode($d,1);
                if(!is_array($d)) $d=explode(',', $settings['kbarr']);

                $keys=[];
                $kb = array("keyboard" => [], "one_time_keyboard" => false, "resize_keyboard" => true );
                foreach($d as $r) $keys[] = ['text'=> $r];

                if($settings['kbadaptive']??0)
                    $kb['keyboard']=keyboards()-> array_strlen_chunk($keys, 20);
                else
                    $kb['keyboard']=array_chunk($keys, $cols);

                if(count($kbp['keyboard'])) $kb['keyboard']=array_merge($kb['keyboard'],$kbp['keyboard']);

                send_mess([ 'body'=> $text, 'kb'=> $kb ]);

                $par['array_of_buttons']['kbarrform']=$d;
                set_pos('array_of_buttons_kb_listener', $par);
            return;


            case 'sql':

                foreach (['itemKey', 'itemVal', 'itemId', 'parent'] as $s)
                    if (!strpos($settings['sql'], $s)) return tgMess('В запросе не хватает параметра "' . $s . '"');


                $arr = arrayQuery($settings['sql']);

                break;

            case 'json':
                $arr = json_decode($settings['array_area'], true);
                if (!is_array($arr)) {
                    return tgMess('the input must be an array');
                }
                $arr = $this->convertAnArray($arr);

                break;

            case 'categories':
                $cat = $settings['id_cat'];

                $categories = categories()->get_categories_from_db(
                    categories()->recursion_categories($cat)
                );

                foreach ($categories as $category) {
                    if ($category['parent_id'] == $cat)
                        $category['parent_id'] = 0;

                    $arr[] = [
                        'itemKey' => $category['category']
                        , 'itemVal' => $category['id']
                        , 'itemId' => $category['id']
                        , 'parent' => $category['parent_id']
                    ];
                }


                break;
        }


        if (!count($arr))
            return tgMess('Список пуст');

        //$this->clearTableArrayButtons();

        $listId = uniqid();


        foreach ($arr as $row)
            query('INSERT INTO `array_of_buttons` (`listId`, `itemKey`, `itemVal`, `itemId`, `parent`, `chat_id`) VALUES (:listId, :itemKey, :itemVal, :itemId, :parent, '.$chat_id.')',
                [':listId' => $listId, ':itemKey' => $row['itemKey'], ':itemVal' => $row['itemVal'], ':itemId' => $row['itemId'], ':parent' => $row['parent']]);
        $initial_parent=(int) $settings['initial_parent']??0;
        $query = 'SELECT * FROM `array_of_buttons` WHERE listId = "' . $listId . '" AND parent = "'.$initial_parent.'"';


        
        /**
         * если установлена категория по умолчанию и в ней нет подкатегорий, то передаем дальше её
         */
        if($initial_parent){
            $filter=array_filter($arr, function($it) use($initial_parent){
                return $it['parent']==$initial_parent;
            });

            if(!count( $filter )) {
                //set_pos($par['step'], $par);
                
                //$data=singleQuery('SELECT count(*) `c` WHERE listId = "' . $listId . '" AND itemId = "'.$initial_parent.'"');
                
                $filter=array_filter($arr, function($it) use($initial_parent){
                    return $it['itemId']==$initial_parent;
                });
                
                if(!count($filter)) {
                    tgMess('Список пуст');
                } else {
                    $filter=array_values($filter);
                    unset($par['array_of_buttons']);
                    $par[$par['script_step']] = $filter[0]['itemVal'];
                    $par[$par['script_step'].'_text'] = $filter[0]['itemKey'];
                    set_pos($par['step'], $par);
                    the_distribution_module($par['script_source'], $par);
                }
                
                
                
                return;
            }
        }


        $parMess = $par;
        $parMess['listId'] = $listId;

        $data = [
              'mask' => 'array_of_buttons_mask_inline'
            , 'limit' => $settings['limit']??10
            , 'columns' => $cols
            , 'class' => get_class()
            , 'title' => $text
            , 'payload' => $parMess
            //,'lowerKeys'=> $lowerKeys
            //, 'staticQuery' => $staticQuery
            , 'query' => $query
            //,'update'=>  true
        ];


        $dataId = setData($data);
        lists()->_inline_list(['dataId' => $dataId]);
        $par['array_of_buttons']['dataId']=$dataId;
        
        if($settings['silentmode'])
        set_pos($par['step'], $par);
        else
        set_pos('array_of_buttons_inline_kb_listener', $par);
    }

    public function array_of_buttons_kb_listener($par){
        global $text_message;

        $arr=$par['array_of_buttons']['kbarrform']??[];
        if(!is_array($arr))$arr=[];

        if(array_search($text_message,$arr)===false) return tgmess('Выберите значение из списка');


        unset($par['array_of_buttons']);
        $par[$par['script_step']] = $text_message;
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
    }

    public function array_of_buttons_inline_kb_listener($prm){
        global $text_message, $original;
        $data=getData($prm['array_of_buttons']['dataId']);
        $listid=$data['payload']['listId'];
        if(isset($original['callback_query'])) {
            if(!$r=singleQuery('SELECT * FROM `array_of_buttons` WHERE listId=? AND itemVal LIKE(?)', [ $listid, $text_message ]))
                return tgmess('Выберите значение из списка');
        } else {
            if(!$r=singleQuery('SELECT * FROM `array_of_buttons` WHERE listId=? AND itemKey LIKE(?)', [ $listid, $text_message ]))
                return tgmess('Выберите значение из списка');
        }

        if(($f=$data['payload']['array_of_buttons']['function']??false)&&get_custom_function($f)) return $f($r);

        if(isset($original['callback_query'])) {
            $title=$data['title'].PHP_EOL.
            '✔️ ' . $r['itemKey'];
            methods()-> editMsg(['text'=> $title]);
        } else {
            tgmess('✔️ ' . $r['itemKey']);
        }
        

        $par=$data['payload'];
        
        $par[$par['script_step']] = $r['itemVal'];
        $par[$par['script_step'].'_text'] = $r['itemKey'];
        unset($par['array_of_buttons']);
        unset($par['mtd']);
        unset($par['d']);
        unset($par['id']);
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
    }


    /**
     * {item}_back - текст кнопки "Назад", если кнопки назад нет, то отправляется пустота
     * {item}_message_id - $message_id
     * 
     */

    public function arrayOfButnsInlineKb($par)
    {
        global $chat_id, $message_id, $original;
        $dataId = $par['d'];
        $itemId = $par['id'];
        $data = getData($dataId);
        $payload = $data['payload'];
        $listId = $payload['listId'];
        $title = $data['title'];


        
        if ( !$this->searchChildren( $itemId ) ) {
            $item = $this->getItem( $itemId );
            
            /**
             * Если есть кнопка "Назад", то передаем о ней информацию дальше
             */
            $backIsset=false;
            $keys=$original['callback_query']['message']['reply_markup']['inline_keyboard'];
            foreach($keys as $k){
                foreach($k as $r){
                    $d=json_decode($r['callback_data'], 1);
                    if($d['system'] === 'return_the_keyboard'){
                        $backIsset=true;
                        break 2;
                    }
                }
            }
            
            
            $par[$par['script_step'].'_back']=$backIsset? '« Назад':'';
            $par[$par['script_step'].'_message_id']=$message_id;

            $itemVal = $item['itemVal'];

            $par[$par['script_step']] = $itemVal;

            if (!intermediate_function($par)) return;

            if(($f=$payload['array_of_buttons']['function']??false)&&get_custom_function($f)) return $f($item);

            $this->clearTableArrayButtons();

            methods()->edit_message_text_or_caption('✔️ ' . $item['itemKey'], [], $chat_id, $message_id);

            unset($par['array_of_buttons']);
            unset($par['mtd']);
            unset($par['d']);
            unset($par['id']);

            set_pos($par['step'], $par);
            the_distribution_module($par['script_source'], $par);
            return;
        }


        $query = 'SELECT * FROM `array_of_buttons` WHERE listId = "' . $listId . '" AND parent = "' . $itemId . '"';

        $parMess = $par;
        $parMess['listId'] = $listId;
//
        $kbBack = keyboards()->inlineKeyGoBack();

        $data = [
              'mask' => 'array_of_buttons_mask_inline'
            , 'limit' => $data['limit']
            , 'columns' => $data['columns']
            , 'class' => get_class()
            , 'title' => $title
            , 'payload' => $payload
            , 'query' => $query
            , 'update' => true
            , 'lowerKeys' => [$kbBack]
        ];

        $dataId = setData($data);
        lists()->_inline_list(['dataId' => $dataId]);

    }

    public function array_of_buttons_mask_inline($par)
    {
        $ch = ''; 
        if ($children = $this->searchChildren( $par['itemId'] ))
            $ch = '▼';

        if ($children || $par['payload']['array_of_buttons']['silentmode'])
            $cb = json_encode(['mtd' => 'arrayOfButnsInlineKb', 'd' => $par['_dataId'], 'id' => $par['itemId']]);
        else
            $cb = $par['itemVal'];

        return  [ ['text' => $ch . $par['itemKey'], 'callback_data' => $cb] ];
    }


    private function convertAnArray(array $item, $parent = 0, $num = 0)
    {
        $retval = [];
        foreach ($item as $key => $value) {

            $num++;
            $id = $num;
            //$id = uniqid();
            if (\is_array($value) === true) {
                $valSubArr = $this->convertAnArray($value, $id, $num);
                foreach ($valSubArr as $iKey => $iValue) {
                    $num++;
                    $retval[] = $iValue;
                }
            }

            $retval[] = [
                'itemKey' => $key
                , 'itemVal' => $value
                , 'itemId' => $id
                , 'parent' => $parent
            ];


        }
        return $retval;

    }

    /**
     * МОДЕЛИ
     */

    private function clearTableArrayButtons(){
        global $chat_id;
        query('DELETE FROM `array_of_buttons` WHERE chat_id = '.$chat_id);
    }

    private function searchChildren( $id ){
        global $chat_id;
        $id=$id*1;
        $res=singleQuery('SELECT * FROM `array_of_buttons` WHERE chat_id = '.$chat_id.' AND parent = '.$id.' LIMIT 1');
        return $res;
    }

    private function getItem( $id ){
        global $chat_id;
        $id=$id*1;
        $res=singleQuery('SELECT * FROM `array_of_buttons` WHERE chat_id = '.$chat_id.' AND itemId = '.$id.' LIMIT 1');
        return $res;
    }
}










