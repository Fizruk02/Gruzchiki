<?php

class cl
{

    function get($input){
        if(!$input['id']) return bterr('Не передан id');
        $item=singleQuery('SELECT * FROM `web_text` WHERE id=?',[ $input['id'] ]);
        $data=arrayQuery('SELECT * FROM `web_translate` WHERE tr_id=?',[ $input['id'] ]);
        $langs=arrayQuery('SELECT * FROM `s_langs`');
        return [
            'success'=> 'ok',
            'data'=> $data,
            'item'=> $item,
            'langs'=> $langs,
        ];
    }

    function save($input){
        if(!$input['id']) return bterr('Не передан id');
        if(!$input['lan']) return bterr('Не передан lan');
        query('DELETE FROM `web_translate` WHERE tr_id=? AND lan=?', [ $input['id'], $input['lan'] ]);
        query('INSERT INTO `web_translate` (`tr_id`, `lan`, `text`) VALUES (?,?,?)', [ $input['id'], $input['lan'], $input['text'] ]);
        return [ 'success'=> 'ok'];
    }

    function name($input){
        if(!$input['id']) return bterr('Не передан id');
        query('UPDATE `web_text` SET `name` = ? WHERE id=?', [ $input['name'],$input['id'] ]);
        return [ 'success'=> 'ok'];
    }

    function deleteLan($input){
        if(!$input['lan']) return bterr('Не передан iso');
        query('DELETE FROM `web_translate`  WHERE lan LIKE(?)', [ $input['lan'] ]);
        query('DELETE FROM `s_langs` WHERE iso LIKE(?)', [ $input['lan'] ]);
        return [ 'success'=> 'ok'];
    }
    
    function defaultLan($input){
        if(!$input['iso']) return bterr('Не передан iso');
        query('UPDATE `s_langs` SET `default` = 0');
        query('UPDATE `s_langs` SET `default` = 1 WHERE iso=?', [ $input['iso'] ]);
        return [ 'success'=> 'ok'];
    } 
    
    function addLan($input){
        if(!$lan=$input['iso']) return bterr('Не передан iso');
        if(!$name=$input['name']) $name=$lan;
        if(singleQuery('SELECT * FROM `s_langs` WHERE iso=?', [ $lan ])) return bterr('Этот iso ('.$lan.') уже есть в базе');
        query('INSERT INTO `s_langs` (`name`, `iso`, `default`) VALUES (?,?,0)', [ $name, $lan ]);
        return [
            'success'=> 'ok',
            'data'=> singleQuery('SELECT * FROM `s_langs` WHERE iso=?', [ $lan ])
        ];
    }

}

















