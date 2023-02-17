<?php

namespace system\modules\products\models;

use system\core\Model;
use Bt;

class ProductsSlider extends Products
{

    public function items()
    {

        $tr= $this->db->arrayQuery('SELECT `field_name`, `row_id`, `text` FROM `s_translates` WHERE `table_name`="market_items"  AND iso=?', [ Bt::$config->lang ]);
        $query= $this->db->arrayQuery('SELECT i.*, c.id_category, ct.category, s.val slug,
                           IFNULL(pt.name, "") price_type, IFNULL(pt.id, "") price_type_id,
                           IFNULL(pr.id, "") price_id, pr.price, pr.unit, pr.currency
                           FROM `market_item_categories` c
                           JOIN `s_categories` ct ON ct.id=c.id_category
                           JOIN `market_items` i ON i.id=c.id_item
                           LEFT JOIN `market_items_prices` pr ON pr.id_item=i.id AND pr.by_default=1
                           LEFT JOIN `market_items_prices_type` pt ON pt.id=pr.id_type
                           LEFT JOIN `market_items_seo` s ON s.item_id = i.id AND s.var="slug"
                           ORDER BY i.priority DESC, REPLACE(i.name, " ","")*1  ASC, TRIM(i.name), IFNULL(pr.price,"");');

        $query=array_map(function ($it) use($tr){
            foreach ($tr as $t) if($t['field_name']==='name' && $t['row_id']===$it['id']){ $it['name']=$t['text'];break 1; }
            return $it;
        }, $query);

        return $query;
    }
    
    public function cats()
    {
        $tr= $this->db->arrayQuery('SELECT `field_name`, `row_id`, `text` FROM `s_translates` WHERE `table_name`="s_categories"  AND iso=?', [ Bt::$config->lang ]);
        $query=$this->db->arrayQuery('SELECT c.*, IFNULL(c.`image`, "") image, s.val slug, IFNULL(ct.category,"") parentName
                                        FROM `s_categories` c
                                        LEFT JOIN `s_categories` ct ON ct.id=c.parent_id
                                        LEFT JOIN `s_categories_seo` s ON s.item_id = c.id AND s.var="slug"
                                        ORDER BY c.`category`');

        $query=array_map(function ($it) use($tr){
            foreach ($tr as $t) if($t['field_name']==='category' && $t['row_id']===$it['id']){ $it['category']=$t['text'];break 1; }
            return $it;
        }, $query);

        return $query;
    }


}