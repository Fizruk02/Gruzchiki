<?php

namespace project\modules\array_of_buttons;

class array_of_buttons
{
    public static $keyboarsd = [];

    public function start(array $par = [])
    {
        global $original, $chat_id, $username, $message_id, $text_message, $user_settings;

        $settings = json_decode($par['array_of_buttons'], true);
        foreach ($settings as &$tSetting){
            $tSetting = text()->variables($tSetting, $par);
            //$tSetting = text()->shortcodes($tSetting, $par);
        }

        $cols = $settings['cols']??1;
        $text = 'список';
        $kbp=false;
        if (isset($par['script_messages'])&&($messId = $par['script_messages'][0]['message'])) {
            $text = DIALTEXT($messId);
            if($par['script_messages'][0]['type_keyboard']==='keyboard'){
                $kbp=keyboards()-> getKeyboard($par['script_messages'][0]['kb_name']);
                $kbp = text()->substituteVariablesInAnArray( $kbp, $par );
            }
        }


        $par['array_of_buttons'] = $settings;
        $arr = [];
        switch ($settings['type']) {

            case 'keyboard':
                $d=$settings['kbarr'];
                $cols=(int) $settings['kbcols']??0;
                if($cols<1) $cols=1;
                if($cols>12) $cols=12;

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

        query('DELETE FROM `array_of_buttons` WHERE chat_id = :chat_id', [':chat_id' => $chat_id]);

        $listId = uniqid();


        foreach ($arr as $row)
            query('INSERT INTO `array_of_buttons` (`listId`, `itemKey`, `itemVal`, `itemId`, `parent`, `chat_id`) VALUES (:listId, :itemKey, :itemVal, :itemId, :parent, :chat_id)',
                [':listId' => $listId, ':itemKey' => $row['itemKey'], ':itemVal' => $row['itemVal'], ':itemId' => $row['itemId'], ':parent' => $row['parent'], ':chat_id' => $chat_id]);

        $query = 'SELECT * FROM `array_of_buttons` WHERE listId = "' . $listId . '" AND parent = "0"';

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
            , 'staticQuery' => $staticQuery
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

    public function arrayOfButnsInlineKb($par)
    {
        global $chat_id, $message_id;
        $dataId = $par['d'];
        $itemId = $par['id'];
        $data = getData($dataId);
        $payload = $data['payload'];
        $listId = $payload['listId'];
        $title = $data['title'];
        if (!$children = singleQuery('SELECT * FROM `array_of_buttons` WHERE listId = :listId AND parent = :id', ['id' => $itemId, ':listId' => $listId])) {
            $item = singleQuery('SELECT * FROM `array_of_buttons` WHERE listId = :listId AND itemId = :id', ['id' => $itemId, ':listId' => $listId]);

            $itemVal = $item['itemVal'];

            $par[$par['script_step']] = $itemVal;

            if (!intermediate_function($par)) return;

            deleteQuery('DELETE FROM `array_of_buttons` WHERE listId = :listId', [':listId' => $listId]);
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
            , 'limit' => 10
            , 'columns' => $cols
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
        if ($children = singleQuery('SELECT * FROM `array_of_buttons` WHERE listId = :listId AND parent = :id', ['id' => $par['itemId'], ':listId' => $par['listId']]))
            $ch = '▼';

        if ($children || $par['payload']['array_of_buttons']['silentmode'])
            $cb = json_encode(['mtd' => 'arrayOfButnsInlineKb', 'd' => $par['_dataId'], 'id' => $par['itemId']]);
        else
            $cb = $par['itemVal'];

        $kb = [ ['text' => $ch . $par['itemKey'], 'callback_data' => $cb] ];

        return $kb;
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


}










