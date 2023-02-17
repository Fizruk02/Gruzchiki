<?php

namespace systems\classes\categories;

class categories
{


    public function initial_categories($id_cat,$arr_cat=[]){
        
        $result = arrayQuery('SELECT parent_id, category FROM `s_categories` WHERE id = :id LIMIT 1', [':id'=> $id_cat]);
        foreach($result as $row)
            if($row['parent_id']>0){
            $resultCat = singleQuery('SELECT category FROM `s_categories` WHERE id = :id', [':id'=> $row['parent_id']]);    
            array_push($arr_cat, [ 'id'=> $row['parent_id'], 'name'=> $resultCat['category'] ]);
            $arr_cat = $this->initial_categories($row['parent_id'], $arr_cat);
        }  

        return $arr_cat;
    }
    
    public function get_categories_from_db($arr_cat=[]){
        return array_map(function($it){
            $it['category']=text()->shortcodes($it['category'], []);
            return $it;
        }, arrayQuery('SELECT * FROM `s_categories` WHERE id IN('.implode(',',$arr_cat ).') ORDER BY number'));
    }
    
    public function recursion_categories($id_cat,$arr_cat=[]){
        $result = arrayQuery('SELECT id FROM `s_categories` WHERE parent_id = ?', [ $id_cat ]);
        foreach($result as $row){
            array_push($arr_cat, $row['id']);
            $arr_cat = $this->recursion_categories($row['id'], $arr_cat);
        }
        return $arr_cat;
    }

    # все конечные категории
    public function ending_categories($id_cat){
        $categories = $this->recursion_categories($id_cat); # ищем все дочерние категории
        
        $categories = implode(',', $categories);
        if($categories=='') return [];
        
        $result = arrayQuery("SELECT id FROM `s_categories` WHERE id IN($categories) AND id NOT IN(SELECT parent_id FROM `s_categories`  ORDER BY number)");
        
        $arr_cat=[];
        foreach($result as $row)
            array_push($arr_cat, $row['id']);
        
        return $arr_cat;
    }
    
    
    public function availability_of_the_category_in_the_table($id_cat, $par){
        if(!$par['link_data']['status']) return true;
      $link_to_the_previous_category='';
      if(isset($par['link_to_the_previous_category']))
          foreach($par['link_to_the_previous_category'] as $r){
              if($r['table']==$par['link_data']['table'])
                  $link_to_the_previous_category .=" AND `{$r['col']}` IN( {$r['val']} ) ";
          }
        $categories = $this->recursion_categories($id_cat,[$id_cat]); # ищем все дочерние категории
        $categories = implode(',', $categories);
        $result = singleQuery("SELECT * FROM `{$par['link_data']['table']}` t WHERE t.{$par['link_data']['cell']} IN($categories) $link_to_the_previous_category");
        if ($result)
            return true;
        else
            return false;
    }
    
    public function _category($par=[]){
        global $chat_id, $obj, $message_id;

        $categoryId =  $par['id_category'];
        
        $script_step=$par['script_step'];
        $par_category=$par['variable_data'][$script_step];
        
        if($par_category['id_source']=='variable'){
            $var = getStrBetween($categoryId, '{', '}');
            if($var!=='')
            $categoryId = $par[$var];
        }
        
        
        if(!$categoryId) return tgMess('не передан id категории');
        
        $keysNum = 0;

        # если в предыдущем выборе категорий нажали отображение всех категорий, то здесь выводим текущую и все дочерние категории
        if($par['select_all_children_categories']){
            
            $categories= $this->ending_categories($categoryId);
            array_push($categories, $categoryId);
            $categories = implode(',', $categories);
            $par[$par['script_step']] = $categories;
            $par['variable_data'][$par['script_step']]['id_cat'] = $categories;

            set_pos($par['step'], $par, false); # listener
            if($par_category['hide_if_the_latter'])
                methods()->delete_this_inline_keyboard();
                
            the_distribution_module($par['script_source'], $par);
            return;
        }
        
        
        $result = arrayQuery('SELECT category, id FROM `s_categories` WHERE parent_id = :id ORDER BY number, category', [':id'=> $categoryId]);
        if (count($result)==0) {
            $par[$par['script_step']] = $categoryId;
            $par['variable_data'][$par['script_step']]['id_cat'] = $categoryId;
            
            set_pos($par['step'], $par); # listener
            if($par_category['hide_if_the_latter'])
                methods()->delete_this_inline_keyboard();
            the_distribution_module($par['script_source'],$par);
            
            return;
        }
  
        
        
       

  
        
        $the_presence_of_subcategories = false; # наличие подкатегорий, потом проверяем, если есть покатегории, то ...
        if(!isset($par['link_to_the_previous_category']))
            $link_to_the_previous_category=[];
        foreach($par as $k => $p)
            if(!in_array($k, ['variable_data', $script_step, 'script_source', '_category', 'id_category', 'type_step', 'script_step', 'step', 'id_step', ]))
            {
                
                if( isset($par['variable_data'][$k]['link_data']['status']) &&
                        $par['variable_data'][$k]['link_data']['status']==1 &&
                    isset($par['variable_data'][$script_step]['link_data']['status']) &&
                        $par['variable_data'][$script_step]['link_data']['status']==1 &&
                        $par['variable_data'][$k]['link_data']['table']==$par['variable_data'][$script_step]['link_data']['table']
                         ){
                             array_push($link_to_the_previous_category, ['col'=>$par['variable_data'][$k]['link_data']['cell'],
                                                                         'table'=>$par['variable_data'][$k]['link_data']['table'],
                                                                         'val'=>$par[$k]
                                                                         ]);
                         }
                    
            
            }
    
        $kb = [];
        $count = count($result);
        for($q = 0; $q<$count;$q++){
            $row = $result[$q];
            if($link_to_the_previous_category)
                $par_category['link_to_the_previous_category']=$link_to_the_previous_category;
               
            if($this->availability_of_the_category_in_the_table($row['id'], $par_category)){

                $new_id_cat = $row['id'];
                
                # children categories
                $t_cat = $row['category'];
                if ( singleQuery('SELECT id FROM `s_categories` WHERE parent_id = :id', [':id'=> $new_id_cat]) )
                    $t_cat = "▼$t_cat";
                    
                  
                $kb[] = ['type'=>'category', "text"=>$t_cat,"callback_data"=>json_encode([ 'system' => 'category_select', 'id' => $new_id_cat ])];
                $keysNum++;
 
                $the_presence_of_subcategories=true;
            }
            
        }
        
        $kb = array_chunk($kb, 2);
       
        $table = $par_category['link_data'];
        if($table['status']==1 && $table['table']=='market_item_categories'){
            
            $result_market = arrayQuery('SELECT m.name, m.id FROM `market_items` m
                                         JOIN `market_item_categories` c ON c.id_item = m.id
                                         WHERE c.id_category = :id', [':id'=> $categoryId]);
            $item = '®';
            $item .= ' ';
            if (count($result_market))
            foreach($result_market as $row_market_item){
                $keysNum++;
                array_push($kb, [['type'=>'item', "text"=>$item.$row_market_item['name'], "callback_data"=>json_encode(['method'=> 'market_item', 'id_market_item' => $row_market_item['id']])]]);
            }

        }
        
   
        
        # проверяем еще раз после исключений
        if (!count($kb)) {
            $par[$par['script_step']] = $categoryId;
            $par['variable_data'][$par['script_step']]['id_cat'] = $categoryId;
            set_pos($par['step'], $par); # listener
            
            if($par_category['hide_if_the_latter'])
                        methods()->delete_this_inline_keyboard();
            
            the_distribution_module($par['script_source'],$par);
            return;
        }
        
   
     # if there is only one option, then click automatically
     if($par_category['auto_click_if_one'] && $keysNum===1) {
         $temp = $kb[0][0]['callback_data'];
         $temp = json_decode($temp, true);
         if($temp['method']!='market_item'){
             $par['_category'] = $new_id_cat;
             return $this->_category($par);
         }

     }
        
        

        
        # если поиск товаров по категориям, то добавляем кнопку "все"
        if($table['status']==1 && $the_presence_of_subcategories){
            $all='📋';
            array_push($kb, [["text"=>"$all ВСЕ","callback_data"=>json_encode([ 'system' => 'select_all_children_categories', 'id' => $categoryId ])]]);
        }
        
        
        # если не начальная категория, и до этого не нажимали отобразить всё то добавляем кнопку "назад"
        if($par['_category']!=$par_category['_category']){
            insertQuery('INSERT INTO `s_data_before_the_update` (`id_mess`, `id_chat`, `body`) VALUES (:id_mess, :id_chat, :body)',
                            [':id_mess'=> $message_id, ':id_chat'=> $chat_id, ':body'=> json_encode($obj)]);
            $back_symb = '🔙';
            array_push($kb, [["text"=>"$back_symb назад","callback_data"=>'return_the_keyboard']]);

        }
        
        
        $kb=["inline_keyboard"=>$kb];
        

        $update_source_message = $par_category['update_source_message'];
        if(isset($obj['data'])){
            if($update_source_message)
            methods()->edit_inline_keyboard($chat_id, $message_id, $kb);
            else {
                send_mess(['body'=>'≡ Категории', 'kb' => $kb]);
                $par['variable_data'][$script_step]['update_source_message']=1;
            }
            
            
        }
        else
            send_mess(['body'=>$par_category['title'] ? $par_category['title'] : '≡ Категории', 'kb' => $kb]);
            
        
        set_pos($par['step'], $par); # listener
        //the_distribution_module($par['script_source'],$par);
    }
    
    ############################### GET CATEGORY #
    public function get_category_func($par=[/* передаваемые данные $par */]){
        
        /*
            ends - категории по краям
            limit - количество категорий с конца
            first - первая категория
            divider - разделитель
        */

        if(!$par['id_cat']) return '';

        $divider = isset($par['divider'])?$par['divider']:'/';
        $id_cat = $par['id_cat'];
        $cats = [];

        while(true){
            $row = singleQuery('SELECT category, parent_id, display_in_the_link FROM `s_categories` WHERE id = :id', [':id'=> $id_cat]);
            if (!$row) break;

            $id_cat = $row['parent_id'];
            if(!isset($par['source_category']) && !$id_cat) break; # если источник не указан, то не указываем самую коренвую категорию
            if($row['display_in_the_link']==1)
                $cats[]=$row['category'];

            if(isset($par['source_category']) && $par['source_category']==$id_cat) break; # если копать до источника

        }
        if(!count($cats)) return '';
        if($par['limit']) $cats=array_slice($cats, 0, $par['limit']);
        if($par['ends'])
            if(count($cats)>0) $cats = [current($cats),end($cats)];
        if($par['first'])
            $cats = [end($cats)];
        if($par['last'])
            $cats = [current($cats)];
            
        return implode( $divider, array_reverse($cats) );
    }
    

    
    
    public function category_select( $callback_data ){
        global $last, $chat_id;

        $last_par = json_decode($last['parameters'], true);
        
        $id_cat = $callback_data['id'];
        $par = $last_par;

        $last_message_par = singleQuery('SELECT parameters FROM `steps` WHERE id_chat = ? AND position = "category_group" ORDER BY `id`  DESC LIMIT 1', [ $chat_id ]);
        if ($last_message_par) {
            $par = json_decode($last_message_par['parameters'], true);
        }

        set_pos($par['step'], $par, true); # listener
        $par['id_category'] = $id_cat;
        $this->_category($par);
    }    


    
    
    public function select_all_children_categories( $callback_data ){
        global $last, $chat_id;

        $last_par = json_decode($last['parameters'], true);
        
        $id_cat = $callback_data['id'];
        $par = $last_par;

        $last_message_par = singleQuery('SELECT parameters FROM `steps` WHERE id_chat = ? AND position = "category_group" ORDER BY `id`  DESC LIMIT 1', [ $chat_id ]);
        if ($last_message_par) $par = json_decode($last_message_par['parameters'], true);

        $categories = $this->ending_categories($id_cat);
        array_push($categories, $id_cat);
        $categories = implode(',', $categories);
        $par[$par['script_step']] = $categories;
        $par['variable_data'][$par['script_step']]['id_cat'] = $categories;
        $par['select_all_children_categories'] = true;
        set_pos($par['step'], $par, true); # listener
        if ($last_par['hide_if_the_latter']) methods()->delete_this_inline_keyboard();
        the_distribution_module($par['script_source'], $par);
    }
    
    
    
    
    
    
    
    
    
}