<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();

$user_id=467899715;
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon

$houses=$db->arrayQuery('SELECT h.*, g.locality, g.address, g.region, g.street, gr.title chatTitle FROM `w_houses` h
                         JOIN geo_points g ON g.id=h.geo_id
                         JOIN _groups gr ON gr.group_id=h.chat_id
                         WHERE h.user_id=?', [ $user_id ]);

$asset->regCss("/templates/sections/project_admin_houses/style.css");
?>
<div data-template="">
    <a type="button" class="btn btn-primary" href="/adm/new-house">Добавить дом</a>
<table class="table">
  <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">City</th>
        <th scope="col">ЖК</th>
        <th scope="col">Address</th>
        <th scope="col">Chat</th>
        <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($houses as $key=> $house){?>
    <tr>
		<th scope="row"><?php echo $key+1; ?></th>
		<td><?php echo $house['locality']; ?></td>
		<td><?php echo $house['jk']; ?></td>
		<td><?php echo $house['address']; ?></td>
		<td><?php echo $house['chatTitle']; ?></td>
		<td></td>
    </tr>
    <?php }?>
  </tbody>
</table>
    
</div>
<?php $asset->regJs("/templates/sections/project_admin_houses/script.js"); ?>
