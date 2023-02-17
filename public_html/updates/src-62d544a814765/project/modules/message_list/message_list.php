<?php

namespace project\modules\message_list;

class message_list
{

    public function start(array $par = [])
    {
        $settings = json_decode($par['message_list'], true);
        foreach ($settings as &$tSetting) {
            $tSetting = text()->variables($tSetting, $par);
            $tSetting = text()->shortcodes($tSetting, $par);
        }
        $par['message_list']=$settings;
        $data = $settings;

        if ($settings['developer_mode'])
            tgMess('REQUEST AFTER PROCESSING' . PHP_EOL . '-------' . PHP_EOL . $data['query']);

        if (!$data['limit'])
            $data['limit'] = 5;


        $data['class'] = get_class();
        $data['payload'] = $par;

        //if(isset())
        //$data

        $mess = $par['script_messages'][0]['message'];
        $messData = dial($mess);
        $mess = $messData['body'];
        $data['files'] = $messData['files'];


        switch ($settings['type']) {
            case 'messages_list':
                $data['mask'] = 'message_list_mask';
                $dataId = setData($data);
                lists()->_message_list(['dataId' => $dataId]);

                break;

            case 'inline_list':
                if ($mess) {
                    $mess = text()->variables($mess, $par);
                    $mess = text()->shortcodes($mess, $par);
                } else
                    $mess = 'список';
                $data['title'] = $mess;
                $data['columns'] = $settings['inl_list_columns'];
                $dataId = setData($data);


                /**
                 * мультивыбор
                 */
                if ($settings['inlinemultiselect']) {
                    $data['inlinemultiselectLimit'] = (int)$settings['inlinemultiselectLimit'];
                    if ($data['inlinemultiselectLimit']) {
                        $kb = [
                            [
                                ['text' => '❎ Все',
                                    'callback_data' => json_encode(['mtd' => 'message_list_inline_ms_deselect_all', 'dataId' => $dataId])]
                                , ['text' => 'Закончить выбор', 'callback_data' => json_encode(['mtd' => 'message_list_inline_ms_ending', 'dataId' => $dataId])]
                            ]
                        ];
                    } else {
                        $kb = [
                            [
                                ['text' => '✅ Все',
                                    'callback_data' => json_encode(['mtd' => 'message_list_inline_ms_select_all', 'dataId' => $dataId])]
                                , ['text' => '❎ Все',
                                'callback_data' => json_encode(['mtd' => 'message_list_inline_ms_deselect_all', 'dataId' => $dataId])]
                            ],
                            [
                                ['text' => 'Закончить выбор', 'callback_data' => json_encode(['mtd' => 'message_list_inline_ms_ending', 'dataId' => $dataId])]
                            ]
                        ];
                    }


                    $data['lowerKeys'] = $kb;
                    $data['mask'] = 'message_list_inline_multiselect_mask';
                    $data['disable_keyboard_from_payload'] = true;

                    updateData($dataId, $data);
                    lists()->_inline_list(['dataId' => $dataId]);

                    set_pos('message_list_inline_multiselect', $par);
                    return;
                }
                $kb = [];
                if ($settings['buttons_under_pagination'] && ($bupkb = keyboards()->getKeyboard($settings['buttons_under_pagination'])['inline_keyboard'] ?? '')) {
                    $kb = array_merge($kb, $bupkb);
                    //tgmess($settings['buttons_under_pagination']);
                }

                $data['lowerKeys'] = $kb;
                $data['mask'] = false;
                updateData($dataId, $data);
                lists()->_inline_list(['dataId' => $dataId]);


                break;


            case 'list_in_one_message':
                $dataId = setData($data);
                if ($settings['excelbtn']) {
                    $kb = [
                        [
                            ['text' => '❎ Save to excel',
                                'callback_data' => json_encode(['mtd' => 'messageListSaveToExcel', 'dataId' => $dataId])]
                        ]
                    ];

                    $data['lowerKeys'] = $kb;
                }


                $data['mask'] = 'strings_list_mask';
                updateData($dataId, $data);
                lists()->_strings_list(['dataId' => $dataId]);
                break;


        }
        unset($par['script_messages']);
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
    }


    public function messageListSaveToExcel($par)
    {
        global $chat_id;
        $data = getData($par['dataId']);
        $arrayQuery = arrayQuery($data['query']);
        if (count($arrayQuery)) {


            $data = [];
            $arr = [];
            foreach ($arrayQuery[0] as $key => $val)
                array_push($arr, $key);

            array_push($data, $arr);

            foreach ($arrayQuery as $row) {
                $arr = [];
                foreach ($row as $val)
                    array_push($arr, $val);

                array_push($data, $arr);
            }

            $res = post(_dir_ . '/admin/functions/save_array_in_excel_get_link.php', ['data' => json_encode($data)]);
            $res = json_decode($res);


            keyboards()->changeThePressedKey([
                'text' => 'Excel файл можно скачать по ссылке'
                , 'callback_data' => _dir_ . $res->link
                , 'edit' => true
                , 'type' => 'url'
            ]);
            //$kb = [ [ ['text' => 'Скачать', 'url'=> _dir_.$res->link] ] ];
            //$kb=["inline_keyboard"=>$kb];

            //send_mess([ 'body'=> 'Excel файл можно скачать по ссылке', 'kb'=> $kb ]);
        } else {
            return notification('Список пуст');
        }
    }


    public function message_list_inline_ms_ending($par)
    {
        global $chat_id, $message_id;
        $data = getData($par['dataId'])['payload'];
        $arr = $this->getMultiselectData();


        $par[$par['script_step']] = $arr;
        $par[$par['script_step'] . '_json'] = json_encode($arr);
        $par[$par['script_step'] . '_implode'] = implode(', ', $arr);
        if (!intermediate_function($par)) return;

        $title = 'Выбрано: ' . count($arr);
        methods()->edit_message_text_or_caption($title, false, $chat_id, $message_id);

        $this->deleteMultiselectData();

        unset($data['message_list']);
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
    }


    public function message_list_inline_multiselect($par)
    {
        global $original;

        if (!$arrItems = $this->getMultiselectData()) {
            $arrItems = [];
            # массив будем хранить в таблице s_data_before_the_update
            $this->setMultiselectData($arrItems);
        }

        $itemCB = $original['callback_query']['data'];

        if (($num = array_search($itemCB, $arrItems)) === false) {
            $settings = json_decode($par['message_list'], 1);
            $limit = $settings['inlinemultiselectLimit'];
            if ($limit && count($arrItems) >= $limit) {
                return notification('Можно выбрать только ' . text()->num_word($limit, ['значение', 'значения', 'значений']));
            }
            $mask = 'message_list_inline_multiselect_key_check';
            $arrItems[] = $itemCB;
        } else {
            $mask = 'message_list_inline_multiselect_key_uncheck';
            unset($arrItems[$num]);
            $arrItems = array_values($arrItems);
        }

        $this->setMultiselectData($arrItems);

        keyboards()->changeThePressedKey([
            'edit' => true
            , 'class' => get_class()
            , 'mask' => $mask
        ]);


    }


    public function message_list_inline_multiselect_key_check($par)
    {
        $check = '✔️';
        $par['text'] = $check . ' ' . trim(str_replace($check, '', $par['text']));
        return $par;
    }

    public function message_list_inline_multiselect_key_uncheck($par)
    {
        $check = '✔️';
        $par['text'] = trim(str_replace($check, '', $par['text']));
        return $par;
    }

    public function message_list_inline_multiselect_mask($par)
    {
        //$settings=$par['payload']['message_list'];
        //
        //foreach ($settings as &$tSetting) {
        //    $tSetting = text()->variables($tSetting, $par);
        //}
        //!$settings['inlinemultiselectCheckvar']&&
        
        $kbKey = keyboards()->getInlineKeyboardFromSystemMess($par['payload']);

        $kbKey = text()->substituteVariablesInAnArray($kbKey, $par);

        if (!$arrItems = $this->getMultiselectData()) {
            $arrItems = [];
        }
        
        if (array_search($kbKey[0][0]['callback_data'], $arrItems) === false) {
            $kbKey[0][0] = $this->message_list_inline_multiselect_key_uncheck($kbKey[0][0]);
        } else {
            $kbKey[0][0] = $this->message_list_inline_multiselect_key_check($kbKey[0][0]);
        }

        return $kbKey;
    }


    public function message_list_inline_ms_select_all($par)
    {
        $data = getData($par['dataId']);
        $items = [];
        $arr = arrayQuery($data['query']);
        $callbackSrc = keyboards()->getInlineKeyboardFromSystemMess($data['payload'])[0][0]['callback_data'];

        foreach ($arr as $row) {
            $items[] = text()->variables($callbackSrc, $row);
        }

        $this->setMultiselectData($items);
        $this->editMultiselectMassCheck('message_list_inline_multiselect_key_check');
    }

    public function message_list_inline_ms_deselect_all($par)
    {

        $data = getData($par['dataId']);
        $this->setMultiselectData([]);
        $this->editMultiselectMassCheck('message_list_inline_multiselect_key_uncheck');

    }

    private function editMultiselectMassCheck($mask)
    {
        global $chat_id, $message_id, $original;
        $reply_markup = $original['callback_query']['message']['reply_markup'];
        foreach ($reply_markup['inline_keyboard'] as &$kbrow) {
            foreach ($kbrow as &$key) {
                if (strpos($key['callback_data'], 'message_list_inline_ms_select_all')) break 2;
                $key['text'] = $this->$mask($key)['text'];
            }
        }

        methods()->edit_inline_keyboard($chat_id, $message_id, $reply_markup);
    }


    private function deleteMultiselectData()
    {
        global $chat_id, $message_id;
        query('DELETE FROM `s_data_before_the_update` WHERE id_chat = :id_chat AND id_mess = :id_mess', [':id_mess' => $message_id, ':id_chat' => $chat_id]);
    }

    private function setMultiselectData($arr)
    {
        global $chat_id, $message_id;
        $this->deleteMultiselectData();
        query('INSERT INTO `s_data_before_the_update` (`id_mess`, `id_chat`, `body`) VALUES (:id_mess, :id_chat, :data)', [':id_mess' => $message_id, ':id_chat' => $chat_id, ':data' => json_encode($arr)]);
    }

    private function getMultiselectData()
    {
        global $chat_id, $message_id;
        $resp = singleQuery('SELECT body FROM `s_data_before_the_update` WHERE id_chat = :id_chat AND id_mess = :id_mess', [':id_mess' => $message_id, ':id_chat' => $chat_id])['body'];
        return $resp ? json_decode($resp, true) : false;
    }


    public function message_list_mask($par)
    {
        global $chat_id;

        $payload = $par['payload'];
        $mess = $payload['script_messages'][0]['message'];
        qwe($payload);
        $messData = dial($mess);
        $mess = $messData['body'];
        $files = $messData['files'];

        $par = array_merge($par, $payload);
        $mess = text()->variables($mess, $par);
        $mess = text()->shortcodes($mess, $par);

        $files = text()->variables($files, $par);
        $files = text()->shortcodes($files, $par);
        $mlpar= $payload['message_list']??[];

        $payload = array_merge($payload, $par);

        $kb = [];
        $tKb = keyboards()->getInlineKeyboardFromSystemMess($payload);
        if ($tKb)
            $kb = $tKb;

        if ($par['pagination_key'])
            array_push($kb, [
                ['text' => ($mlpar['moreBtnLabel']??'')?: 'Далее', 'callback_data' => json_encode($par['pagination_key'])]
            ]);

        $parMess = ['id_chat' => $chat_id, 'body' => $mess, 'files' => $files];

        if (count($kb)) {
            $kb = ["inline_keyboard" => $kb];
            $parMess['kb'] = $kb;
        }

        send_mess($parMess);
    }


    public function strings_list_mask($par)
    {
        global $chat_id;
        $payload = $par['payload'];
        $mess = $payload['script_messages'][0]['message'];
        $mess = DIALTEXT($mess);
        $par = array_merge($par, $payload);
        $mess = text()->variables($mess, $par);
        $mess = text()->shortcodes($mess, $par);

        return $mess;


    }


}













