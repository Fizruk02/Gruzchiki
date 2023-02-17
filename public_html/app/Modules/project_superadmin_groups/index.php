<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon

$cities=$db->arrayQuery('SELECT DISTINCT locality name FROM `geo_points` ORDER BY locality');
$chats=$db->arrayQuery('SELECT g.group_id, g.username group_name, g.title group_title, g.member_count, g.invite_link, g.id, u.username, u.first_name
                        FROM `_groups` g
                        JOIN `users` u ON u.id_chat=g.creator_id
                        ORDER BY g.title');

$asset->regCss("/templates/sections/project_superadmin_groups/style.css");
?>
<div data-template="">

    <div class="row">
        <div class="col-sm">
        <table class="table">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Title</th>
              <th scope="col">Link</th>
              <th scope="col">Users</th>
        		<th scope="col">Admin</th>
        		<th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
              <?php foreach($chats as $key=> $chat){?>
            <tr>
        		<th scope="row"><?php echo $key+1; ?></th>
        		<td><?php echo $chat['group_title']; ?></td>
        		<td><?php echo '<a href="'.$chat['invite_link'].'" target="_blank">'.$chat['invite_link'].'</a>'; ?></td>
        		<td><span class="badge text-bg-info"><?php echo $chat['member_count']; ?></span></td>
        		<td><?php echo $chat['first_name'].($chat['username']?' / @'.$chat['username']:''); ?></td>
        		<td></td>
            </tr>
            <?php }?>
	  
          </tbody>
        </table>
        
        </div>
        
        
        <div class="col-sm-3 border-start ms-4 ps-4">
    		<h5>Города</h5>
    		<div class="list-group mb-2">
    			<?php foreach($cities as $city){ ?>
    			<div class="list-group-item list-group-item-action">
    				<span><?php echo $city['name'] ?></span>
    			</div>
    			<?php }?>

    		</div>
        </div>
    </div>
</div>
<?php $asset->regJs("/templates/sections/project_superadmin_groups/script.js"); ?>
