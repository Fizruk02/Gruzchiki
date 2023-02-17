<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
$admins=$db->arrayQuery('SELECT u.id, u.username, u.first_name,
                         h.jk, g.locality, g.region, g.street, g.address
                         FROM `users` u
                         JOIN `w_houses` h ON h.user_id=u.id_chat
                         JOIN geo_points g ON g.id=h.geo_id
                         WHERE u.`status`=1');
$asset->regCss("/templates/sections/project_superadmin_admins/style.css");
?>
<div data-template="">
<h3>АДМИНИСТРАТОРЫ ГРУПП</h3>
<table class="table">
  <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">City</th>
        <th scope="col">ЖК</th>
        <th scope="col">Address</th>
        <th scope="col">First/User name</th>
        <th scope="col">Status</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($admins as $key=> $admin){?>
    <tr>
		<th scope="row"><?php echo $key+1; ?></th>
		<td><?php echo $admin['locality']; ?></td>
		<td><?php echo $admin['jk']; ?></td>
		<td><?php echo $admin['address']; ?></td>
		<td><?php echo $admin['first_name'].($admin['username']?' / @'.$admin['username']:''); ?></td>
		<td></td>
    </tr>
    <?php }?>
  </tbody>
</table>
</div>
<?php $asset->regJs("/templates/sections/project_superadmin_admins/script.js"); ?>
