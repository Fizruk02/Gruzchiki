<!doctype html>
<html lang="en">
   <head>
      <title>скрипты</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <? 
         include_once("../resources/_phpparsite.php");
         include_once(__DIR__.'/codemirror.php');
         
         res("_ass.php");
         if(!$permission_to_use['access']) exit;
         
         
         $scripts = arrayQuery('SELECT id, name, parent FROM `script_list` ORDER BY name');
         
         $blockchains = arrayQuery('SELECT techname, name FROM `constructors` ORDER BY name');
         ?> 
         <script>
             var scripts = <?=json_encode($scripts);?>;
             var blockchains = <?=json_encode($blockchains);?>;
         </script>
         <style>
        [selSupselect]:hover {
            transform: scale(1.5);
        } 
         </style>
   </head>
   <body>
    <? res("_nav.php")?>

  
   <div  class="m-3">
    <div class="row">
      <div class="col-sm-3 border-end">
          
      <div class="d-flex">
        <input class="form-control me-2" type="search" placeholder="Search" id="searchscript" oninput="if(this.value =='')  {$('[scripts]').css('display', 'block')} else {  $('[scripts]').css('display', 'none'); $(`[scripts][search*='${this.value}']`).css('display', 'block') }">

        <button class="btn btn-outline-success" type="submit" onclick="addScript()">Новый</button>
        
      </div>
      <hr>
        <div class="list-group" id="scriptslist">
            

          
          <? foreach($scripts as $script){
               if($script['parent']==0)
               echo childrenTemplate($script);

          }
          
          
          function childrenTemplate($script){
              $children='';
              $childrenArr=arrayQuery('SELECT id, name, parent FROM `script_list` WHERE parent = ?', [$script['id']]);
              foreach($childrenArr as $child)
                  $children .= childrenTemplate($child);
               
               return
              '<div class="list-group-item list-group-item-action" style="margin-bottom:1px;" search="'.$script['name'].'" sptnt="'.$script['id'].'">
              <div class="row">
              <div class="col-sm" style="cursor:pointer" scripts="'.$script['id'].'">
              '.$script['name'].'
              </div>
                <div class="col-auto p-0 pe-1" onclick="supselect('.$script['id'].')" supselect style="display:none;"> 
                   <button class="btn btn-outline-secondary border-0 p-0 px-1" type="button"><i class="bi bi-hand-index-thumb-fill"></i></button>
                </div>
                <div class="col-auto p-0" style="'.($children?'':'display:none;').'" id="collapse-btn-'.$script['id'].'">
                 <button class="btn border-0 p-0 px-1" type="button"  onclick="toggle(\''.$script['id'].'\')"> <i class="bi bi-chevron-expand"></i>  </button>
                </div>
              </div>
                <div class="collapse mt-2 list-group" childrenlist id="scriptslist-'.$script['id'].'">
                '.$children.'
                </div>
              </div>';
              
          }
          
          
          
          
          
          ?>
          
          
           
          
        </div>
      </div>
  <script>
  </script>
      <!-- =============================================== -->
      <div class="col-sm">
        <div class="p-1 ui-sortable" style="height:90vh;overflow:auto;background-image: url(bg.jpg);" id="blocks-container" manhtml>
           
        </div>
      </div>
      <!-- =============================================== -->
      
      
      <div class="col-sm-3 border-start">
         <div style="display:none" mandisplay>
            <div class="row mb-1 me-1">
                <div class="col-sm d-flex">
                <span class="fs-5 text" id="scriptname" style="cursor:pointer" onclick="nameScript(this)" manhtml></span>  
                </div>
                <div class="col-auto d-flex pe-0">
                    <div class="form-check form-switch px-2 border-end">
                      <input class="form-check-input" type="checkbox" id="script-status" onclick="$('#lss').text($(this).is(':checked')?'Включен':'Скрыт');$.post('p.php?q=st', {id:script,st: $(this).is(':checked')?1:0})  ">
                      <label class="form-check-label" for="script-status" id="lss"></label>
                    </div>

                    <a href="#" class="px-2 border-end" onclick="getScriptsettings()"><i class="bi bi-gear-fill"></i></a>
                    <a href="#" class="px-2" onclick="deleteScript()"><i class="bi bi-x text-danger"></i></a>
                </div>
                
                
            </div>
            
            <div class="border rounded p-3 pt-1 mb-2 bg-white" style="cursor:pointer" onclick="saveTriggers()">
                <i class="mb-1">Список триггеров</i>
                <div id="triggers">
                    
                </div>
            </div>
            
            
   
<!--           <div class="row mb-2">

   <div class="col">
    <button class="btn btn-outline-success w-100" type="submit" onclick="saveScript()">Сохранить</button>
   </div>
</div>
        <hr> -->
          <div class="row mb-2">
             <div class="col">
              <button class="btn btn-outline-primary w-100" type="submit" style="white-space: nowrap;" onclick="addBlock('message')"><i class="bi bi-chat-dots"></i></button>
             </div>
             <div class="col">
              <button class="btn btn-outline-primary w-100" type="submit" style="white-space: nowrap;" onclick="addBlock('input')"><i class="bi bi-textarea-t"></i></button>
             </div>
          </div>

         <div class="row mb-2">
             <div class="col">
                 <button class="btn btn-outline-primary w-100" type="submit" onclick="addBlock('inputFiles')"><i class="bi bi-images"></i> Файлы</button>
             </div>
             <div class="col">
                 <button class="btn btn-outline-primary w-100" type="submit" onclick="addBlock('goto')"><i class="bi bi-signpost-fill"></i> Переход</button>
             </div>

             <div class="col-auto">
                 <button class="btn btn-outline-primary w-100" type="submit" style="white-space: nowrap;" data-bs-toggle="collapse" aria-expanded="false" data-bs-target="#allBlockspanel" aria-controls="allBlockspanel">
                     <i class="bi bi-list"></i>
                 </button>
             </div>
         </div>



          
        <div class="collapse float-end" id="allBlockspanel" style="max-width:min-content;">

            <button class="btn btn-outline-secondary w-100 mb-2 text-left" type="submit" style="white-space: nowrap;" onclick="addBlock('func')"><i class="bi bi-code-square"></i> PHP функция</button>
<!--             <button class="btn btn-outline-secondary w-100 mb-2 text-left" type="submit" style="white-space: nowrap;" onclick="addBlock('gotoBlock')"><i class="bi bi-signpost"></i> Перейти к скрипту</button> -->
            <button class="btn btn-outline-secondary w-100 mb-2 text-left" type="submit" style="white-space: nowrap;" onclick="addBlock('mainBlockchain')"><i class="bi bi-signpost-fill"></i> Перейти к крипту основного бота</button>
        </div>
        
        </div>
        
        
        
      </div>

      
    </div>
   
   
   </div>

   </body>
   
   
   
   
<div class="modal fade" id="scriptListModal" tabindex="-1" aria-labelledby="scriptListModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="scriptListModalLabel">Выберите скрипт</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="list-group" id="scriptListmodalbody">
        </div>
      </div>
    </div>
  </div>
</div>
   
   
<!-- <div class="modal fade" id="scriptListModal" tabindex="-1" aria-labelledby="scriptListModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="scriptListModalLabel">Выберите скрипт</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="list-group" id="scriptListmodalbody">
        </div>
      </div>
    </div>
  </div>
</div> -->
   
   
<? include(__DIR__.'/blockSettingsmodal.php'); ?>
   
<? res("js.php"); ?>
<script src="scripts.js?v=<?=time()?>"></script>
<script src="blocks.js?v=<?=time()?>"></script>
<script>
var script = false, blocks={};
function getIndex(vr,vl,arr){
let x=false;
arr.forEach(function(item, i) {
if(item[vr]==vl) return x=i;
});
return x;
}

</script>
</html>