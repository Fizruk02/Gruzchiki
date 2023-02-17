<?
/**
query("CREATE TABLE `_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `files` int(10) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");

query("CREATE TABLE `_data_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_item` int(12) NOT NULL,
  `id_cat` int(12) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`id_item`,`id_cat`) USING BTREE,
  KEY `id_item` (`id_item`),
  KEY `id_cat` (`id_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");


query("CREATE TABLE `_data_categories_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent` int(10) NOT NULL,
  `num` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");

*/
/**
query("DROP TABLE `_data`;");
query("DROP TABLE `_data_categories`;");
query("DROP TABLE `_data_categories_list`;");
*/




    $categories = arrayQuery('SELECT id, name, parent FROM `_data_categories_list`');
        
    function categoryTemplate($category){
        $children='';
        $childrenArr=arrayQuery('SELECT id, name, parent FROM `_data_categories_list` WHERE parent = ?', [$category['id']]);
        foreach($childrenArr as $child)
            $children .= categoryTemplate($child);
        
        return '<div class="list-group-item list-group-item-action" style="margin-bottom: 1px;" search="'.$category['name'].'" categoryblock="'.$category['id'].'">
                <div class="row">
                    <div class="col-sm cat_name" style="cursor:pointer" category-name="'.$category['id'].'" onclick="category=\''.$category['id'].'\';list();">'.$category['name'].'</div>
                    <div class="col-auto p-0" style="'.($children?'':'display:none;').'" id="collapse-btn-'.$category['id'].'">
                     <button class="btn border-0 p-0 px-1" type="button" onclick="toggle(\''.$category['id'].'\')"> <i class="bi bi-chevron-expand"></i>  </button>
                    </div>
                    <div class="col-auto ps-0 pe-2">
                        <i class="bi bi-pen text-secondary" onclick="editCategory(\''.$category['id'].'\')"></i>
                    </div>
                    <div class="col-auto ps-0">
                        <i class="bi bi-x-lg text-danger" onclick="deleteCategory(\''.$category['id'].'\')"></i>
                    </div>
                </div>
              <div class="collapse mt-2 list-group" childrenlist id="categorieslist'.$category['id'].'">
              '.$children.'
              </div>
            </div>';
    }




?>
<div class="row">
   
      <div class="col-sm-3 border-end">
          
          <div class="d-flex mb-1">
            <input class="form-control me-2" type="search" placeholder="Search" oninput="if(this.value =='')  {$('[scripts]').css('display', 'block')} else {  $('[scripts]').css('display', 'none'); $(`[scripts][search*='${this.value}']`).css('display', 'block') }">
    
            <button class="btn btn-outline-success" type="submit" onclick="addCategory()">Новая</button>
          </div>
            <div class="list-group-item list-group-item-action" style="margin-bottom: 1px;" search="" categoryblock="0">
                <div class="row">
                    <div class="col-sm cat_name selected" style="cursor:pointer" category-name="0" onclick="category=0;list();">Все</div>
                </div>
            </div>
            <div class="list-group" id="categorieslist">
              <? 
                  foreach($categories as $category)
                      if($category['parent']==0)
                      echo categoryTemplate($category);
              ?>
            </div>
      </div>

    <div class="col-sm">

        <div class="container mt-2" tableform>
           <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
              <table class="table table-hover table-sm" id="table">
                 <thead class="thead-dark">
                  <th style="width: 60px;">#</th>
                  <th style="min-width: 100px;">category</th>
                  <th style="width: 60px;">files</th>
                  <th style="min-width: 100px;">name</th>
                  <th style="min-width: 100px;">body</th>
                  <th style="width:78px"></th>
                 </thead>
              </table>
           </div>
        </div>
    </div>
</div>

<script>
var table = 'table',
categories = <?=json_encode($categories)?>;
</script>

