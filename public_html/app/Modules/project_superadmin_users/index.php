<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
$users=$db->arrayQuery('SELECT i.username, i.first_name, i.id_chat user_id, h.jk, g.address, i.phone FROM `__info_tenant` i
                        JOIN w_houses h ON h.id=i.house_id
                        JOIN geo_points g ON g.id=h.geo_id
                        ORDER BY first_name;');
$asset->regCss("/templates/sections/project_superadmin_users/style.css");
?>
<div data-template="">

<table class="table">
  <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Id</th>
        <th scope="col">Имя/username</th>
        <th scope="col">Регистрация</th>
        <th scope="col">ЖК</th>
        <th scope="col">Телефон</th>
        <th scope="col">Тег</th>
        <th scope="col">Блок</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($users as $key=> $user){?>
    <tr>
		<th scope="row"><?php echo $key+1; ?></th>
		<td><?php echo $user['user_id']; ?></td>
		<td><?php echo $user['first_name'].($user['username']?'/@'.$user['username']:''); ?></td>
		<td><?php echo $user['address']; ?></td>
		<td><?php echo $user['jk']; ?></td>
		<td>+<?php echo $user['phone']; ?></td>
		<td></td>
		<td></td>
    </tr>
    <?}?>

	  
  </tbody>
</table>

</div>
<?php $asset->regJs("/templates/sections/project_superadmin_users/script.js"); ?>
