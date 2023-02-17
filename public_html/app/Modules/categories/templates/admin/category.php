<?php
/* @var $block array */

use system\lib\Db;
use system\lib\Asset;

//$item = $block['content']['data'];
$db=DB::getInstance();
$asset = Asset::getInstance();

$langs=$db->arrayQuery('SELECT * FROM `s_langs` ORDER BY `default` DESC, `name`');

$asset->regCss(Bt::getAlias("@root/system/modules/categories/templates/admin/style.css"));
$asset->regCss(Bt::getAlias('@www/common/jsTree/themes/default/style.min.css'));
$asset->regJs('//b2bot.ru/components/dialogboxes.js', ['bootstrap']);
$asset->regJs('//b2bot.ru/components/toast.js', ['bootstrap']);
$asset->regJs('//b2bot.ru/components/upload/js.js', ['bootstrap']);
$asset->regJs("//snipp.ru/cdn/jQuery-Form-Plugin/dist/jquery.form.min.js", ['jquery']);
$asset->regJs(Bt::getAlias("@root/system/modules/categories/templates/admin/script.js"), ['jquery', 'jstree'], 'category_script');
$asset->regJs(Bt::getAlias("@www/common/jsTree/jstree.min.js"), ['jquery'], 'jstree');
?>

<div class="container">
   <div class="row">
      <div id="categories" class="col-auto border-right">
         <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
         </div>
      </div>
      <div class="col-sm" style="min-width: 600px;">
         <div class="row">
            <div class="col-sm">
               <div class="input-group mb-3">
                  <input type="text" class="form-control" placeholder="новая категория" aria-label="новая категория" aria-describedby="button-addon2" id="new-category-edit">
                  <div class="input-group-append">
                     <button class="btn btn-outline-secondary" type="button" onclick="func.add()">создать</button>
                  </div>
               </div>
            </div>
         </div>
         <hr>
         
         
         
         
<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-tab-pane" type="button" role="tab" aria-controls="content-tab-pane" aria-selected="true">content</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane" type="button" role="tab" aria-controls="seo-tab-pane" aria-selected="false">seo</button>
  </li>

</ul>
<div class="tab-content">
  <div class="tab-pane fade p-1 show active" id="content-tab-pane" role="tabpanel" aria-labelledby="content-tab" tabindex="0">
      <?php include(__DIR__.'/content.php')?>
  </div>
  <div class="tab-pane fade p-1" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
      <?php include(__DIR__.'/seo.php')?>
  </div>

</div> 
<button class="btn btn-outline-secondary" type="button" onclick="func.save()">сохранить</button>
<button type="button" class="btn btn-outline-danger" onclick="func.delete()">удалить</button>   
         

         
         
         
         
         
         
         
         
         
         
         
         
         

      </div>
   </div>
</div>

<script>
    const project={
        lang:{
            list:<?php echo json_encode($langs);?>,
        },
        stopEvent:false
    }
</script>