<?php

namespace system\modules\products\models;

use system\core\Model;
use system\core\Text;
use Bt;

class ProductsAdmin extends Products
{
    public function getList( $input ){

        $cat='';
        if(isset($input['cat']) && $input['cat']) $cat=' AND c.id_category='.$input['cat'];

        $sql='SELECT m.*, IFNULL(pt.name, "") price_type, IFNULL(pt.id, "") price_type_id,
              IFNULL(pr.id, "") price_id, pr.price, pr.unit, pr.currency, IFNULL(s.val, "") slug,
              IFNULL(c.id_category, "") id_category
              FROM `market_items` m
              LEFT JOIN `market_item_categories` c ON c.id_item=m.id
              LEFT JOIN `market_items_prices` pr ON pr.id_item=m.id AND pr.by_default=1
              LEFT JOIN `market_items_prices_type` pt ON pt.id=pr.id_type
              LEFT JOIN `market_items_seo` s ON s.var="slug" AND s.item_id=m.id
              WHERE true '.$cat.'
              GROUP BY m.id
              ORDER BY m.name;';

        $tp=$this->db->singleQuery('SELECT * FROM `market_items_prices_type` ORDER BY id LIMIT 1');
        $res = array_map(function($it) use($tp) {
            $it['category']=$this->get_category_func(['id_cat' => $it['id_category']]);
            $it['file']=$this->getFilesforweb( $it['files'] );
            //$it['tr_name']=language()->getTranslate('market_items', $it['id'])['fields']['name']??'{}';
            $it['tr_name']=Bt::$config->translator->getTranslate('market_items', $it['id'])['fields']['name']??'{}';
            if($it['price_type_id']===""){
                //$it['price_type_id']=$tp['id'];
                $it['price_type']=$tp['name'];
            }

            if($it['slug']==''){
                $it['slug']=$this->createSlug($it['name'], $it['id']);
                $this->db->query('DELETE FROM `market_items_seo` WHERE `item_id`=? AND `var`="slug"', [ $it['id'] ]);
                $this->db->query('INSERT INTO `market_items_seo` (`var`, `item_id`, `val`) VALUES ("slug",?,?)', [ $it['id'], $it['slug'] ]);
            }

            return $it;
        }, $this->db->arrayQuery($sql, [], true));
        //qwe($res);
        return [
            'success'=> 'ok'
            ,'data'=> $res
        ];
    }


    public function listEditFiles( $input ){
        if (!$id = $input["id"]) return Bt::err('Недостаточно параметров');
        $this->db->query('UPDATE `market_items` SET files=? WHERE id=?', [ $input['gr'],$id ]);
        return [
            'success'=> 'ok'
            ,'data'=> $this->getFilesforweb( $input['gr'] )
        ];
    }

    public function listEditPrice( $input ){
        if (!$input["id"]||!$input["typeid"]) return Bt::err('Недостаточно параметров');
        $this->db->query('UPDATE `market_items_prices` SET price=? WHERE id_item=? AND id_type=?', [ $input['price'],$input["id"],$input["typeid"] ]);
        return [ 'success'=> 'ok' ];
    }

    public function listEditName( $input ){
        if (!$id = $input["id"]) return Bt::err('Недостаточно параметров');
        //query('UPDATE `market_items` SET `name`=? WHERE `id`=?', [ $input['name'],$id ]);
        Bt::$config->translator->postTranslate('market_items', 'name', $id, $input["tr"]);
        return [ 'success'=> 'ok' ];
    }

    public function listEditPriority( $input ){
        if (!$id = $input["id"]) return Bt::err('Недостаточно параметров');
        $this->db->query('UPDATE `market_items` SET priority=? WHERE id=?', [ $input['priority']?:0,$id ]);
        return [ 'success'=> 'ok' ];
    }

    public function remove( $input ){
        if(!$id = $input['id'])
            return Bt::err('не хватает параметров');

        $this->db->query('DELETE FROM `market_items` WHERE id = ?', [ $id ]);
        $this->db->query('DELETE FROM `market_item_categories` WHERE id_item = ?', [ $id ]);
        $this->db->query('DELETE FROM `market_items_prices` WHERE `id_item` = ?', [ $id ]);
        $this->db->query('DELETE FROM `market_items_seo` WHERE `item_id`=?', [ $id ]);

        return [
            'success'=> 'ok'
        ];
    }

    public function save( $input ){
        $name              = $input["name"];
        $files             = $input["files"];
        $display           = $input["display"];
        $id_item           = $input["id_item"];
        $techname          = $input["techname"];
        $description       = $input["description"];
        $group_public      = $input["group_public"];
        $availability      = isset($input["availability"])&&$input["availability"]!=='undefined'?$input["availability"]:0;
        $preview_image     = $input["preview_image"];
        $short_description = $input["short_description"];
        $prices            = json_decode($input["prices"], true);
        //$scripts           = json_decode($input["scripts"], true);
        $seo               = json_decode($input["seo"], true);
        $categories        = json_decode($input["categories"], true);

        $lang=$this->db->singleQuery('SELECT iso FROM `s_langs` ORDER BY `default` DESC, `id`')['iso']??'en';
        $name=json_decode($name,1)[$lang];
        $description=json_decode($description,1)[$lang];
        $short_description=json_decode($short_description,1)[$lang];

        $lower_name = mb_strtolower($name);
        $files = $files?:0;
        $preview_image = $preview_image?:0;

        $newSlug=false;

        if (!$name)
            return Bt::err('Недостаточно параметров');

        if(!$this->db->singleQuery('SELECT * FROM `market_items` WHERE id = :id', [ ':id'=> $id_item ])){
            $id_item = $this->db->query('INSERT INTO `market_items` (`name`, `techname`, `short_description`, `description`, `files`, `preview_image`, `display`, `availability`, `lower_name`)
                                                    VALUES (:name, :techname, :short_description, :description, :files, :preview_image, :display, :availability, :lower_name)',
                [':name'=> $name, ':techname'=> $techname, ':short_description'=> $short_description, ':description'=> $description, ':files'=> $files,
                    ':preview_image'=> $preview_image, ':display'=> $display, ':availability'=> $availability, ':lower_name'=> $lower_name ]);
            $newSlug=$this-> createSlug($name, $id_item);
        }
        else
            $this->db->query('UPDATE `market_items` SET `name` = :name, `short_description` = :short_description, `description` = :description, `lower_name` = :lower_name,
                  `files` = :files, `preview_image` = :preview_image, `display` = :display, `techname` = :techname, `availability` = :availability
                  WHERE id = :id_item',
                [ ':id_item'=> $id_item, ':name'=> $name, ':techname'=> $techname, ':short_description'=> $short_description, ':description'=> $description, ':files'=> $files,
                    ':preview_image'=> $preview_image, ':display'=> $display, ':availability'=> $availability, ':lower_name'=> $lower_name ]);
        if($stmtErr=$this->db->err()) return Bt::err($stmtErr);
        if(!$id_item)
            return Bt::err('при добавлении записи произошла ошибка');

        $this->db->query('DELETE FROM `market_item_categories` WHERE id_item = :id_item', [ ':id_item'=> $id_item ]);
        foreach($categories as $r)
            if($this->db->singleQuery('SELECT * FROM `s_categories` WHERE id=?', [ $r ]))
                $this->db->query('INSERT INTO market_item_categories (id_item, id_category) VALUES(?,?)', [ $id_item, $r ]);
        if($stmtErr=$this->db->err()) return Bt::err($stmtErr);
        //query('DELETE FROM `market_item_scripts` WHERE id_item = :id_item', [ ':id_item'=> $id_item ]);
        //foreach($scripts as $r)
        //    query('INSERT INTO market_item_scripts (id_item, script_techname, key_text, id_output_chat, instant_launch) VALUES(:id_item, :script_techname, key_text, id_output_chat, instant_launch)',
        //        [ ':id_item'=> $id_item, ':script_techname'=> $r['script'], ':key_text'=> $r['key_name'], ':id_output_chat'=> $r['chat'], ':instant_launch'=> $r['instant_launch'] ]);

        foreach($prices as $price){
            $pr = $this->db->singleQuery('SELECT * FROM `market_items_prices` WHERE id_type = :id_type AND id_item = :id_item', [ ':id_type'=> $price['id'], ':id_item'=> $id_item ]);
            if($pr)
                $this->db->query("UPDATE `market_items_prices` SET `price`=?, `unit`=?, `currency`=?, `by_default`=? WHERE id = ?", [ $price['cell'],$price['unit'],$price['currency'], $price['by_default'], $pr['id'] ]);
            else
                $this->db->query("INSERT INTO `market_items_prices` (`id_item`, `id_type`, `by_default`, `price`, `unit`, `currency`) VALUES ( ?,?,?,?,?,? )",
                    [ $id_item, $price['id'], $price['by_default'], $price['cell'], $price['unit'], $price['currency'] ]);
            if($stmtErr=$this->db->err()) return Bt::err($stmtErr);
        }

        if($group_public) {
            if($this->db->singleQuery('SELECT * FROM `settings` WHERE t_key="marketItemGroupPublicId"'))
                $this->db->query('UPDATE `settings` SET `value` = ? WHERE t_key="marketItemGroupPublicId"', [ $group_public ]);
            else
                $this->db->query('INSERT INTO `settings` (`t_key`, `value`, `name`, `visible`, `type`, `t_group`) VALUES ("marketItemGroupPublicId", ?, "id группы для постов", 0, "text", "market_items")', [ $group_public ]);


            foreach($this->db->arrayQuery('SELECT * FROM `market_items_posts` WHERE id_item=?', [ $id_item ]) as $m) methods()->delete_mess($m['id_group'], $m['id_message']);
            $this->db->query('DELETE FROM `market_items_posts` WHERE id_item=?', [ $id_item ]);
            $sm=send_mess([ 'id_chat'=> $group_public, 'body'=> $description, 'files'=> $files, 'disable_notification'=> true ]);
            foreach($sm as $s)
                $this->db->query('INSERT INTO `market_items_posts` (`id_item`, `id_group`, `id_message`, `group_name`, `type`) VALUES (?,?,?,?,"public")', [ $id_item, $s['channel_id'], $s['message_id'], $s['channel_name'] ]);


        }

        $this->db->query('DELETE FROM `market_items_seo` WHERE item_id=?', [ $id_item ]);
        foreach($seo as $s)if($s['val']!==''){
            if($s['var']==='slug'&&$s['val'])$s['val']=$this-> createSlug($s['val']);

            $this->db->query('INSERT INTO `market_items_seo` (`item_id`, `var`, `val`) VALUES (?,?,?)', [ $id_item, $s['var'], $s['val'] ]);
        }

        if($newSlug) $this->db->query('INSERT INTO `market_items_seo` (`var`, `item_id`, `val`) VALUES ("slug",?,?)', [ $id_item, $newSlug ]);

        //foreach($this->db->arrayQuery('SELECT * FROM `market_items` WHERE uniqid=""') as $r)
        //    $this->db->query('UPDATE `market_items` SET `uniqid` = "'.randhash().'" WHERE id='.$r['id']);

        Bt::$config->translator->postTranslate('market_items', 'name', $id_item, $input["name"]);
        Bt::$config->translator->postTranslate('market_items', 'lower_name', $id_item, mb_strtolower($input["name"]));
        Bt::$config->translator->postTranslate('market_items', 'description', $id_item, $input["description"]);
        Bt::$config->translator->postTranslate('market_items', 'short_description', $id_item, $input["short_description"]);

        return [
            'success'=> 'ok',
            'itemId'=> $id_item,
            'slug'=> $newSlug
        ];
    }


    public function getData( $input ){
        if (!$id = $input["id_item"]) return Bt::err('Не передан id');
        return [
            'success'=> 'ok',
            'translates'=> Bt::$config->translator->getTranslate('market_items', $id),
            'item'=> $this->db->singleQuery('SELECT * FROM `market_items` WHERE id=?', [ $id ]),
            'seo'=> $this->db->arrayQuery('SELECT * FROM `market_items_seo` WHERE item_id=?', [ $id ]),
            'prices'=> $this->db->arrayQuery('SELECT * FROM `market_items_prices` WHERE id_item=?', [ $id ]),
            'attributes'=> $this->db->singleQuery('SELECT * FROM `market_items_attributes` WHERE id_item=?', [ $id ]),
            'categories'=> array_map(function($it){return $it['c'];}, $this->db->arrayQuery('SELECT id_category c FROM `market_item_categories` WHERE id_item=?', [ $id ])),
        ];
    }


    /********************************************************** ТИПЫ ЦЕН */
    public function getListDir( $POST ){
        if(!$table=$this->getTable($POST['src'])) return Bt::err('Таблица не найдена');
        $res = $this->db->arrayQuery('SELECT * FROM `'.$table.'` ORDER BY name', [], true);
        if($e=$this->db->err()) return $e;
        return [
            'success'=> 'ok'
            ,'data'=> $res
        ];

    }

    public function editDir( $POST ){

        if(!$id = $POST['id'])
            return Bt::err('не хватает параметров');
        if(!$table=$this->getTable($POST['src'])) return Bt::err('Таблица не найдена');

        $this->db->query('UPDATE `'.$table.'` SET name=? WHERE id=?', [ $POST['name'], $id  ]);
        if($e=$this->db->err()) return $e;
        return [
            'success'=> 'ok'
        ];

    }

    public function removeDir( $POST ){

        if(!$id = $POST['id'])
            return Bt::err('не хватает параметров');
        if(!$table=$this->getTable($POST['src'])) return Bt::err('Таблица не найдена');

        $this->db->query('DELETE FROM `'.$table.'` WHERE id=?', [ $id ]);
        if($POST['src']==='prices_type')
            $this->db->query('DELETE FROM `market_items_prices` WHERE id_type=?', [ $id ]);

        if($e=$this->db->err()) return $e;
        return [
            'success'=> 'ok'
        ];

    }

    public function addDir( $POST ){
        if(!$table=$this->getTable($POST['src'])) return Bt::err('Таблица не найдена');
        $id=$this->db->query('INSERT INTO `'.$table.'` (`name`) VALUES (?)', [ $POST['name'] ]);
        $data=$this->db->singleQuery('SELECT * FROM `'.$table.'` WHERE id=?',[ $id ]);
        if(!$data)
            return Bt::err('Ошибка при добавлении данных');
        if($e=$this->db->err()) return $e;
        return [
            'success'=> 'ok'
            ,'data'=> $data
        ];

    }

    private function getTable($src){
        switch($src){
            case 'prices_type':  return 'market_items_prices_type';
            case 'units':  return 'market_items_units';
            case 'currency':  return 'market_items_currencies';
        }
    }

    public function saveSlug( $input ){
        if (!$id = $input["id"]) return Bt::err('Недостаточно параметров');
        $slug=$this-> createSlug($input['url'], $id);
        return [
            'success'=> 'ok'
            ,'slug'=> $slug
        ];
    }

    public function createSlug($value, $item=false)
    {
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
        );

        $value = mb_strtolower($value);
        $value = strtr($value, $converter);
        $value = mb_ereg_replace('[^-0-9a-z]', '-', $value);
        $value = mb_ereg_replace('[-]+', '-', $value);
        $value = trim($value, '-');

        if($value==='') return '';

        $ch=$item?' AND item_id<>"'.$item.'"':'';

        $pr=$value;
        for($i=1;$i<10000000;$i++){
            if($this->db->singleQuery('SELECT * FROM `market_items_seo` WHERE `var`="slug" AND `val`=?'.$ch, [ $pr ])){
                $pr=$value.'-'.$i;
            } else break;
        }

        return $pr;
    }

}