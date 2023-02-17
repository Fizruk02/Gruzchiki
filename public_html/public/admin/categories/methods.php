<?php

class cl
{
    
    public function list(){
        
        $data = arrayQuery('SELECT c.id, IF (c.parent_id = 0, "#", c.parent_id) AS `parent`, c.category as `text`, IFNULL(c.image, "") AS image, c.display_in_the_link,
            IFNULL(s.val, "") slug
            FROM s_categories c
            LEFT JOIN `s_categories_seo` s ON s.var="slug" AND s.item_id=c.id
            ORDER BY `parent`, c.`number`, c.`category`');
        $data = array_map(function($it) {
            if($it['slug']==''&&$it['text']!=='New node'){
                $it['slug']=$this-> createSlug($it['text'], $it['id']);
                query('DELETE FROM `s_categories_seo` WHERE `item_id`=? AND `var`="slug"', [ $it['id'] ]);
                query('INSERT INTO `s_categories_seo` (`var`, `item_id`, `val`) VALUES ("slug",?,?)', [ $it['id'], $it['slug'] ]);
            }
            return $it;
        }, $data);
        
        return [
            'success'=> 'ok',
            'data'=> $data
        ];

    }
    
    public function move( $input ){
        if(!$ids = $input['ids']) return bterr('Не хватает параметров');
        $ids=json_decode($ids, 1);
        
        foreach($ids as $k=>$id) query("UPDATE s_categories SET `number` = ? WHERE id = ?", [  $k, $id ]);
        
        return [ 'success'=> 'ok' ];
    }
    
    public function dlt( $input ){
        if(!$id = $input['id']) return bterr('Не хватает параметров');
        query('DELETE FROM s_categories WHERE id = ?', [ $id ]);
        query('DELETE FROM `s_categories_seo` WHERE `item_id`=?', [ $id ]);
        if($e=db()->err()) return $e;
        language()-> deleteTranslate('s_categories', $id);
        return [ 'success'=> 'ok' ];
    }
    
    function save( $input ){
        if(!$id = $input['id']) return bterr('Не хватает параметров');
        $seo=json_decode($input['seo'],1);
        query('UPDATE s_categories SET `category` = ?, `display_in_the_link` = ? WHERE id = ?', [ $input["name"], $input["dl"], $id ]);
        if($e=db()->err()) return $e;
        if(isset($input["image"]))
            query("UPDATE s_categories SET `image` = ? WHERE id = ?", [  $input["image"], $id ]);
        
        //if($input['name']!=='New node'){
        //    $slug=$this-> createSlug($input['name'], $id);
        //    query('DELETE FROM `s_categories_seo` WHERE `item_id`=? AND `var`="slug"', [ $it['id'] ]);
        //    query('INSERT INTO `s_categories_seo` (`var`, `item_id`, `val`) VALUES ("slug",?,?)', [ $id, $slug ]);
        //}

        query('DELETE FROM `s_categories_seo` WHERE item_id=?', [ $id ]);
        foreach($seo as $s)if($s['val']!==''){
            if($s['var']==='slug'&&$s['val'])$s['val']=$this-> createSlug($s['val'],$id);
            query('INSERT INTO `s_categories_seo` (`item_id`, `var`, `val`) VALUES (?,?,?)', [ $id, $s['var'], $s['val'] ]);
        }
        
        language()-> postTranslate('s_categories', 'category', $id, $input["tr"]);
        language()-> postTranslate('s_categories', 'descr', $id, $input["tr_descr"]);
        return [ 'success'=> 'ok' ];
    }
    
    function add( $input ){
        if($input["name"]==="") return bterr('Некорректные данные');
        
        $maxNumber=singleQuery('SELECT max(number) `n` FROM `s_categories`')['n'];
        $id = query('INSERT INTO `s_categories` (`category`, `parent_id`, `number`, `display_in_the_link`) VALUES ( ?,?,?,1 )', [ $input["name"],$input["parent"],$maxNumber ]); 
        if($e=db()->err()) return $e;
        if(!$id) return bterr('Ошибка при добавлении записи в базу');
        language()-> postSingleTranslate('s_categories', 'category', $id, $input["name"]);
        
        if($input['name']!=='New node'){
            $slug=$this-> createSlug($input['name'], $id);
            query('INSERT INTO `s_categories_seo` (`var`, `item_id`, `val`) VALUES ("slug",?,?)', [ $id, $slug ]);
        }

        
        return [
            'success'=> 'ok',
            'id'=> $id
        ];
    }
    
    function get( $input ){
        if(!$id = $input['id']) return bterr('Не хватает параметров');
        if(!$data=singleQuery('SELECT *, "" img FROM `s_categories` WHERE id=?', [ $id ])) return bterr('Данные не найдены в базе');
        if($data['image']) $data['img']=singleQuery('SELECT small_size s FROM `files` WHERE id_group=?', [ $data['image'] ])['s']??"";
        $data['translates']=language()-> getTranslate('s_categories', $id);
        return [
            'success'=> 'ok',
            'data'=> $data,
            'seo'=> arrayQuery('SELECT * FROM `s_categories_seo` WHERE item_id=?', [ $id ]),
        ];
    }
    
    public function saveSlug( $input ){
        if (!$id = $input["id"]) return bterr('Недостаточно параметров');
        $slug=$this-> createSlug($input['url'], $id);
        return [
            'success'=> 'ok'
            ,'slug'=> $slug
        ];
    }

    public function createSlug($value, $item=false)
    {
        $value=text()->slug($value);

        $ch=$item?' AND item_id<>"'.$item.'"':'';

        $pr=$value;
        for($i=1;$i<10000000;$i++){
            if(singleQuery('SELECT * FROM `s_categories_seo` WHERE `var`="slug" AND `val`=?'.$ch, [ $pr ])){
                $pr=$value.'-'.$i;
            } else break;
        }

        return $pr;
    }
}