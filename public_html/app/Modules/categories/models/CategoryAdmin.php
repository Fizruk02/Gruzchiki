<?php

namespace App\Modules\categories\models;

use Illuminate\Support\Facades\DB;

class CategoryAdmin extends Category
{
    /**
     * Таблица БД, ассоциированная с моделью.
     *
     * @var string
     */
    protected $table = 's_categories as c';

    protected $text = null;

    public function getItems(){
        $items = parent::select([
            'c.id',
            DB::raw('IF (parent_id = 0, "#", parent_id) AS parent'),
            'category as text',
            DB::raw('IFNULL(image, "") as image'),
            'display_in_the_link',
            DB::raw('IFNULL(val, "") slug')
        ])
        //->leftJoin('s_categories_seo as s', 's_categories.id', '=', 's.item_id')
        ->leftJoin('s_categories_seo as s', function ($join) {
            $join->on('c.id', '=', 's.item_id')
                ->where('s.var', '=', '"slug"');
        })
        ->get()->toArray();
        return $items;
    }

    public function list(){

        $data = $this->db->arrayQuery('SELECT c.id, IF (c.parent_id = 0, "#", c.parent_id) AS `parent`, c.category as `text`, IFNULL(c.image, "") AS image, c.display_in_the_link,
            IFNULL(s.val, "") slug
            FROM s_categories c
            LEFT JOIN `s_categories_seo` s ON s.var="slug" AND s.item_id=c.id
            ORDER BY `parent`, c.`number`, c.`category`');
        $data = array_map(function($it) {
            if($it['slug']==''&&$it['text']!=='New node'){
                $it['slug']=$this->createSlug($it['text'], $it['id']);
                $this->db->query('DELETE FROM `s_categories_seo` WHERE `item_id`=? AND `var`="slug"', [ $it['id'] ]);
                $this->db->query('INSERT INTO `s_categories_seo` (`var`, `item_id`, `val`) VALUES ("slug",?,?)', [ $it['id'], $it['slug'] ]);
            }
            return $it;
        }, $data);

        return [
            'success'=> 'ok',
            'data'=> $data
        ];

    }

    public function dlt( $input ){
        if(!$id = $input['id']) return Bt::err('Не хватает параметров');
        $this->db->query('DELETE FROM s_categories WHERE id = ?', [ $id ]);
        $this->db->query('DELETE FROM `s_categories_seo` WHERE `item_id`=?', [ $id ]);
        if($e=$this->db->err()) return Bt::err($e);
        //language()->deleteTranslate('s_categories', $id);
        Bt::$config->translator->deleteTranslate('s_categories', $id);
        return [ 'success'=> 'ok' ];
    }

    function savem( $input ){
        if(!$id = $input['id']) return Bt::err('Не хватает параметров');
        $seo=json_decode($input['seo'],1);
        $this->db->query('UPDATE s_categories SET `category` = ?, `display_in_the_link` = ? WHERE id = ?', [ $input["name"], $input["dl"], $id ]);
        if($e=$this->db->err()) return Bt::err($e);
        if(isset($input["image"]))
            $this->db->query("UPDATE s_categories SET `image` = ? WHERE id = ?", [  $input["image"], $id ]);

        //if($input['name']!=='New node'){
        //    $slug=$this-> createSlug($input['name'], $id);
        //    query('DELETE FROM `s_categories_seo` WHERE `item_id`=? AND `var`="slug"', [ $it['id'] ]);
        //    query('INSERT INTO `s_categories_seo` (`var`, `item_id`, `val`) VALUES ("slug",?,?)', [ $id, $slug ]);
        //}

        $this->db->query('DELETE FROM `s_categories_seo` WHERE item_id=?', [ $id ]);
        foreach($seo as $s)if($s['val']!==''){
            if($s['var']==='slug'&&$s['val'])$s['val']=$this-> createSlug($s['val'],$id);
            $this->db->query('INSERT INTO `s_categories_seo` (`item_id`, `var`, `val`) VALUES (?,?,?)', [ $id, $s['var'], $s['val'] ]);
        }

        //language()->postTranslate('s_categories', 'category', $id, $input["tr"]);
        //language()->postTranslate('s_categories', 'descr', $id, $input["tr_descr"]);
        Bt::$config->translator->postTranslate('s_categories', 'category', $id, $input["tr"]);
        Bt::$config->translator->postTranslate('s_categories', 'descr', $id, $input["tr_descr"]);
        return [ 'success'=> 'ok' ];
    }

    function add( $input ){
        if($input["name"]==="") return Bt::err('Некорректные данные');

        $id = $this->db->query('INSERT INTO `s_categories` (`category`, `parent_id`, `number`, `display_in_the_link`) VALUES ( ?,?, 0, 1 )', [ $input["name"],$input["parent"] ]);
        if($e=$this->db->err()) return Bt::err($e);
        if(!$id) return Bt::err('Ошибка при добавлении записи в базу');
        //language()->postSingleTranslate('s_categories', 'category', $id, $input["name"]);
        Bt::$config->translator->postSingleTranslate('s_categories', 'category', $id, $input["name"]);

        if($input['name']!=='New node'){
            $slug=$this->createSlug($input['name'], $id);
            $this->db->query('INSERT INTO `s_categories_seo` (`var`, `item_id`, `val`) VALUES ("slug",?,?)', [ $id, $slug ]);
        }


        return [
            'success'=> 'ok',
            'id'=> $id
        ];
    }

    public function gets( $input ){
        //return bterr('Не хватает параметров');
        if(!$id = $input['id']) return Bt::err('Не хватает параметров');
        if(!$data=$this->db->singleQuery('SELECT *, "" img FROM `s_categories` WHERE id=?', [ $id ])) return Bt::err('Данные не найдены в базе');
        if($data['image']) $data['img']=$this->db->singleQuery('SELECT small_size s FROM `files` WHERE id_group=?', [ $data['image'] ])['s']??"";
        //$data['translates']=language()->getTranslate('s_categories', $id);
        $data['translates'] = Bt::$config->translator->getTranslate('s_categories', $id);
        return [
            'success'=> 'ok',
            'data'=> $data,
            'seo'=> $this->db->arrayQuery('SELECT * FROM `s_categories_seo` WHERE item_id=?', [ $id ]),
        ];
    }

    public function saveSlug( $input ){
        if (!$id = $input["id"]) return Bt::err('Недостаточно параметров');
        $slug=$this->createSlug($input['url'], $id);
        return [
            'success'=> 'ok'
            ,'slug'=> $slug
        ];
    }

    public function createSlug($value, $item=false)
    {
        if (!$this->text) $this->text = new Text();
        $value=$this->text->slug($value);

        $ch=$item?' AND item_id<>"'.$item.'"':'';

        $pr=$value;
        for($i=1;$i<10000000;$i++){
            if($this->db->singleQuery('SELECT * FROM `s_categories_seo` WHERE `var`="slug" AND `val`=?'.$ch, [ $pr ])){
                $pr=$value.'-'.$i;
            } else break;
        }

        return $pr;
    }

    // Вставка категории по ее id, родителю и позиции number
    function includePosition($categoryId, $parentId, $position) {
        $this->db->query('UPDATE s_categories SET `number` = `number` + 1 WHERE `parent_id` = :parent_id AND `number` >= :number', [ ':parent_id'=> $parentId, ':number'=> $position ] );
        $this->db->query('UPDATE s_categories SET `parent_id` = :parent_id, `number` = :number WHERE id = :id', [ ':parent_id'=> $parentId, ':number'=> $position, ':id'=> $categoryId ] );
    }

    // Исключение категории по ее родителю и позиции number
    function excludePosition($parentId, $position) {
        $this->db->query('UPDATE s_categories SET `number` = `number` - 1 WHERE `parent_id` = :parent_id AND `number` > :number', [ ':parent_id'=> $parentId, ':number'=> $position ] );
    }

    // Перемещение категории
    function moveCategory( $params ) {
        $categoryId  = (int)$params['id'];
        $oldParentId = (int)$params['old_parent'];
        $newParentId = (int)$params['new_parent'];
        $oldPosition = (int)$params['old_position'];
        $newPosition = (int)$params['new_position'];

        $this->excludePosition($oldParentId, $oldPosition);
        $this->includePosition($categoryId, $newParentId, $newPosition);

        return [
            'code' => 'success'
        ];
    }

}
