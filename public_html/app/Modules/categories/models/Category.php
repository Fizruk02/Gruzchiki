<?php

namespace App\Modules\categories\models;

class Category extends \Illuminate\Database\Eloquent\Model
{

    public function translates(){
        return $this->db->arrayQuery('SELECT `field_name`, `row_id`, `text` FROM `s_translates` WHERE `table_name`="s_categories"  AND iso=? ORDER BY `row_id`', [ Bt::$config->lang ]);
    }

    public function files(){
        return $this->db->arrayQuery('SELECT id_group, small_size, medium_size, large_size, type_file `type` FROM `files`
                                      WHERE id_group AND id_group IN(SELECT image FROM `s_categories`) OR id_group IN(SELECT files FROM `market_items`)');
    }

    public function cats()
    {
        $tr=$this->translates();
        $query=$this->db->arrayQuery('SELECT c.id, c.category, c.parent_id, c.display_in_the_link visible, IFNULL(c.`image`, "") files_id, s.val slug, IFNULL(ct.category,"") `parent`
                                        FROM `s_categories` c
                                        LEFT JOIN `s_categories` ct ON ct.id=c.parent_id
                                        LEFT JOIN `s_categories_seo` s ON s.item_id = c.id AND s.var="slug"
                                        ORDER BY c.`category`');
        $files=$this-> files();
        $query=array_map(function ($it) use($tr, $files){
            $st=false;
            foreach ($tr as $t) {
                if($t['row_id']===$it['id']){ $st=1;
                    $it[$t['field_name']]=$t['text'];
                } else if($st) break;
            }
            $it['name']=$it['category'];

            $it['files']=[];
            $it['preview']='';
            if($img=$it['files_id']) {
                $files=array_values(array_filter($files, function($it)use($img){ return $it['id_group']==$img; }));
                $it['preview']=count($files)?($files[0]['medium_size']?:$files[0]['large_size']):'';
                $it['files']=$files;
            }

            unset($it['category']);
            return $it;
        }, $query);
        return $query;
    }

}
