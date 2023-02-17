<?php

namespace system\modules\products\models;

use system\core\Model;
use Bt;

class Products extends Model
{

    public function translates(){
        return $this->db->arrayQuery('SELECT `field_name`, `row_id`, `text` FROM `s_translates` WHERE `table_name`="s_categories"  AND iso=? ORDER BY `row_id`', [ Bt::$config->lang ]);
    }
    
    public function files(){
        return $this->db->arrayQuery('SELECT id_group, small_size, medium_size, large_size, type_file `type` FROM `files`
                                      WHERE id_group AND id_group IN(SELECT image FROM `s_categories`) OR id_group IN(SELECT files FROM `market_items`)');
    }
    
    public function item()
    {
        $url=explode('/', trim($_SERVER['REQUEST_URI'],' /'));
        $slug=end($url);
        $itemDATA=$this->db->singleQuery('SELECT * FROM `market_items_seo` WHERE var="slug" AND val=?', [ $slug ]);
        
        $itemId=$itemDATA?$itemDATA['item_id']:false;
        $item=false;
        if($itemId) {
            $tr= $this->db->arrayQuery('SELECT `field_name`, `row_id`, `text` FROM `s_translates` WHERE `table_name`="market_items" AND row_id=? AND iso=?', [ $itemId, Bt::$config->lang ]);
            $item=$this->db->singleQuery('SELECT i.id, i.name, i.short_description, i.description, i.display visible, i.priority, i.files files_id,
                           c.id_category, ct.category, s.val slug,
                           IFNULL(pt.name, "") price_type, IFNULL(pt.id, "") price_type_id,
                           IFNULL(pr.id, "") price_id, pr.price, pr.unit, pr.currency
                           FROM `market_item_categories` c
                           JOIN `s_categories` ct ON ct.id=c.id_category
                           JOIN `market_items` i ON i.id=c.id_item
                           LEFT JOIN `market_items_prices` pr ON pr.id_item=i.id AND pr.by_default=1
                           LEFT JOIN `market_items_prices_type` pt ON pt.id=pr.id_type
                           LEFT JOIN `market_items_seo` s ON s.item_id = i.id AND s.var="slug"
                           WHERE i.id=?', [ $itemId ]);
            
            foreach ($tr as $t) $item[$t['field_name']]=$t['text'];
        }

        return $item;
    }

    public function _item()
    {
        $url=explode('/', trim($_SERVER['REQUEST_URI'],' /'));
        $slug=end($url);
        $itemDATA=$this->db->singleQuery('SELECT * FROM `market_items_seo` WHERE var="slug" AND val=?', [ $slug ]);

        $itemId=$itemDATA?$itemDATA['item_id']:false;
        $item=false;
        if($itemId) {
            $tr= $this->db->arrayQuery('SELECT `field_name`, `row_id`, `text` FROM `s_translates` WHERE `table_name`="market_items" AND row_id=? AND iso=?', [ $itemId, Bt::$config->lang ]);
            $item=$this->db->singleQuery('SELECT i.*, c.category, mc.id_category, c.display_in_the_link FROM `market_items` i
                                          LEFT JOIN `market_item_categories` mc ON mc.id_item=i.id
                                          LEFT JOIN `s_categories` c ON c.id=mc.id_category WHERE i.id=?', [ $itemId ]);

            $item['pricedata']= $this->db->singleQuery('SELECT t.name typename, id_type, price, unit, currency FROM `market_items_prices` p
                                                        JOIN market_items_prices_type t ON t.id=p.id_type
                                                        WHERE p.id_item=? AND p.by_default=1', [ $itemId ]);
            $item['price']=$item['pricedata']['price']??0;
            $item['currency']=$item['pricedata']['currency']??"";
            foreach ($tr as $t) $item[$t['field_name']]=$t['text'];
        }

        return $item;
    }

}