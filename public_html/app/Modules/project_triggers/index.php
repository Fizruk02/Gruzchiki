<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
$black=$db->arrayQuery('SELECT * FROM `_triggers` WHERE `type`=3 ORDER BY text');
$gray=$db->arrayQuery('SELECT * FROM `_triggers` WHERE `type`=2 ORDER BY text');
$white=$db->arrayQuery('SELECT * FROM `_triggers` WHERE `type`=1 ORDER BY text');
$asset->regCss("/templates/sections/project_triggers/style.css");
?>
<div data-template="">
    <div class="row">
    	
    	<div class="col p-1">
    		<h5>Черный список</h5>
    		<div class="list-group mb-2" data-src-list="3">
    			<?php foreach($black as $r){?>
    			<div class="list-group-item list-group-item-action trigger" data-id="<?php echo $r['id']; ?>">
    				<span><?php echo $r['text']; ?></span>
    				<button type="button" class="btn btn-danger btn-sm float-end" action="delete"><i class="bi bi-x-lg"></i></button>
    			</div>
    			<?php }?>
    		</div>
    		<button type="button" class="btn btn-success" onclick="triggers.add(3)">ДОБАВИТЬ</button>
    	</div>
    	
    	<div class="col p-1">
    		<h5>Серый список</h5>
    		<div class="list-group mb-2" data-src-list="2">
    			<?php foreach($gray as $r){?>
    			<div class="list-group-item list-group-item-action trigger" data-id="<?php echo $r['id']; ?>">
    				<span><?php echo $r['text']; ?></span>
    				<button type="button" class="btn btn-danger btn-sm float-end" action="delete"><i class="bi bi-x-lg"></i></button>
    			</div>
    			<?php }?>
    		</div>
    		<button type="button" class="btn btn-success" onclick="triggers.add(2)">ДОБАВИТЬ</button>
    	</div>
    	
    	<div class="col p-1">
    		<h5>Белый список</h5>
    		<div class="list-group mb-2" data-src-list="1">
    			<?php foreach($white as $r){?>
    			<div class="list-group-item list-group-item-action trigger" data-id="<?php echo $r['id']; ?>">
    				<span><?php echo $r['text']; ?></span>
    				<button type="button" class="btn btn-danger btn-sm float-end" action="delete"><i class="bi bi-x-lg"></i></button>
    			</div>
    			<?php }?>
    		</div>
    		<button type="button" class="btn btn-success" onclick="triggers.add(1)">ДОБАВИТЬ</button>
    	</div>
    	
    	
    <div>
</div>
<?php $asset->regJs("/templates/sections/project_triggers/script.js"); ?>





