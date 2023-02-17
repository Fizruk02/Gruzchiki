<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
//$data=$db->arrayQuery('SELECT * FROM `table`');
$asset->regCss("/templates/sections/account/style.css");
?>
<div data-template="">

</div>
<?php $asset->regJs("/templates/sections/account/script.js"); ?>
