<?php
define('PATH', $_SERVER['DOCUMENT_ROOT']);
ini_set('display_errors', 1);

//include $_SERVER['DOCUMENT_ROOT'].'/system/core/Config.php';
//include $_SERVER['DOCUMENT_ROOT'].'/system/core/Model.php';
//include $_SERVER['DOCUMENT_ROOT'].'/system/lib/Db.php';
//$class='system\core\Model';
//if (class_exists($class)) {
//    $obj = new $class();
//    $actions = $obj->getActionsSections();
//}
//

    $controllers=[ 'main', 'account', 'pages' ];

    $roles = arrayQuery('SELECT id, name, title, parent FROM `us_roles`');
        
    function roleTemplate($role){
        $children='';
        $childrenArr=arrayQuery('SELECT id, name, title, parent FROM `us_roles` WHERE parent = ?', [$role['id']]);
        foreach($childrenArr as $child)
            $children .= roleTemplate($child);
        
        return '<div class="list-group-item list-group-item-action" style="margin-bottom: 1px;" search="'.$role['name'].'" roleblock="'.$role['id'].'">
                <div class="row">
                    <div class="col-sm" style="cursor:pointer" role-name="'.$role['id'].'" onclick="role=\''.$role['id'].'\';list();">'.$role['name'].'</div>
                    <div class="col-auto p-0" style="'.($children?'':'display:none;').'" id="collapse-btn-'.$role['id'].'">
                     <button class="btn border-0 p-0 px-1" type="button" onclick="toggle(\''.$role['id'].'\')"> <i class="bi bi-chevron-expand"></i>  </button>
                    </div>
                    <div class="col-auto ps-0 pe-2">
                        <i class="bi bi-pen text-secondary" onclick="editrole(\''.$role['id'].'\')"></i>
                    </div>
                    <div class="col-auto ps-0">
                        <i class="bi bi-x-lg text-danger" onclick="deleterole(\''.$role['id'].'\')"></i>
                    </div>
                </div>
              <div class="collapse mt-2 list-group" childrenlist id="roleslist'.$role['id'].'">
              '.$children.'
              </div>
            </div>';
    }


?>
<div class="row">
   
      <div class="col-sm-3 border-end">
          
          <div class="d-flex mb-1">
            <input class="form-control me-2" type="search" placeholder="Search" oninput="if(this.value =='')  {$('[scripts]').css('display', 'block')} else {  $('[scripts]').css('display', 'none'); $(`[scripts][search*='${this.value}']`).css('display', 'block') }">
    
            <button class="btn btn-outline-success" type="submit" onclick="addrole()">Новая</button>
          </div>
            <div class="list-group-item list-group-item-action" style="margin-bottom: 1px;" search="" groupblock="">
                <div class="row">
                    <div class="col-sm" style="cursor:pointer" group-name="" onclick="list('');">Все</div>
                </div>
            </div>
            <div class="list-group" id="roleslist">
              <? 
                  foreach($roles as $role)
                      if($role['parent']==0)
                      echo roleTemplate($role);
              ?>
            </div>
      </div>

    <div class="col-sm">

        <div class="container mt-2" tableform>
           <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
              <table class="table table-hover table-sm" id="table">
                 <thead class="thead-dark">
                  <th style="width: 60px;">#</th>
                  <th style="min-width: 100px;">роль</th>
                  <th style="min-width: 100px;">контроллер</th>
                  <th style="min-width: 100px;">методы</th>
                  <th style="width:78px"></th>
                 </thead>
              </table>
           </div>
        </div>
    </div>
</div>

<script>
var table = 'table',
roles = <?=json_encode($roles)?>;
</script>

