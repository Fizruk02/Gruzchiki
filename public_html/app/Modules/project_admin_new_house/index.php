<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();

$user_id=467899715;


//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
$chats=$db->arrayQuery('SELECT * FROM `_groups` WHERE creator_id=?', [ $user_id ]);
$asset->regCss("/templates/sections/project_admin_new_house/style.css");
?>
<div class="container">
    <div class="address_group mb-1 data-field" data-required="1" data-id="addressValue">
        <input type="text" class="form-control fields_val" id="address" list="addresslist" placeholder="Введите адрес" oninput="newhouse.suggest.get()" onblur="newhouse.suggest.detailed()">
        <label for="address">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-house-heart" viewBox="0 0 16 16">
              <path d="M8 6.982C9.664 5.309 13.825 8.236 8 12 2.175 8.236 6.336 5.309 8 6.982Z"/>
              <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.707L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.646a.5.5 0 0 0 .708-.707L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
            </svg>
        </label>
                
    </div>
    <datalist id="addresslist" onselect="console.log(this.value)"></datalist>
    
    <div class="detailed-address mb-1"> </div>
    
    <div class="detailed-house mb-1"> 
        <div class="input-group data-field" data-required="1" data-id="floors">
          <span class="input-group-text">этажей</span>
          <input type="text" class="form-control fields_val">
        </div>
        
        <div class="input-group data-field" data-required="1" data-id="chat">
          <label class="input-group-text" for="s-chat">Чат</label>
          <select class="form-select fields_val" id="s-chat">
            <option selected value="">Выберите...</option>
            <?php foreach($chats as $chat) echo '<option value="'.$chat['group_id'].'">'.$chat['title'].'</option>'; ?>
          </select>
        </div>
        
        <div class="input-group data-field" data-required="1" data-id="jk">
          <span class="input-group-text">ЖК</span>
          <input type="text" class="form-control fields_val">
        </div>
    </div>
    
    <button type="button" class="btn btn-primary" onclick="newhouse.save()">Сохранить</button>
    
</div>
<?php $asset->regJs("/templates/sections/project_admin_new_house/script.js"); ?>