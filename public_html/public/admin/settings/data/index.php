<?php

$groups = arrayQuery('SELECT t_group FROM `settings` GROUP BY t_group');
foreach($groups as $group)
    if(!singleQuery('SELECT * FROM `settings_group` WHERE techname = :techname', [':techname'=> $group['t_group']]))
    {
        insertQuery('INSERT INTO `settings_group` (`techname`, `name`, `owner`, `sort`) VALUES (:techname, :techname, "", :sort  )', [ ':techname'=> $group['t_group'], ':sort'=> count(arrayQuery('(SELECT * FROM `settings_group` WHERE owner = "" )'))  ] );
    }

$files = [];
settingsCategory('');
function settingsCategory($groupOwner){
    global $files;
    $groups_arr = arrayQuery('SELECT g.* FROM settings_group g
                                   WHERE owner = :owner AND (g.techname IN(SELECT t_group FROM settings WHERE visible = 1) OR g.id IN(SELECT owner FROM settings_group))
                                   ORDER BY g.sort', [ ':owner'=> $groupOwner ]);
    if(count($groups_arr)){
        ?>
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <?php //EXISTS(SELECT * FROM settings s WHERE s.t_group = g.techname AND s.visible = 1)
                foreach($groups_arr as $group){
                    echo '<li class="nav-item" role="presentation">
                       <a class="nav-link '.($group['id']==1?'active':'').'" id="tab-key-'.$group['id'].'" data-bs-toggle="tab" href="#tab-'.$group['id'].'" role="tab" aria-controls="home" aria-selected="'.($group['id']==1?'true':'false').'">'.$group['name'].'</a>
                     </li>';
                }
                ?>
            </ul>
        </div>
        <div class="card-body m-0 p-1" index-source="main">
            <div class="tab-content" id="TabContent_<?php echo $groupOwner?>">
                <?php
                $files = [];

                foreach($groups_arr as $group){
                    echo
                        '<div class="tab-pane fade '.($group['id']==1?'show active':'').'" id="tab-'.$group['id'].'" role="tabpanel" aria-labelledby="tab-key-'.$group['id'].'">';
                    $chidren = false;
                    if(singleQuery( 'SELECT * FROM `settings_group` WHERE owner = :owner', [ ':owner'=> $group['id'] ] )){
                        settingsCategory($group['id']);
                        $chidren = true;
                    }

                    $settings = arrayQuery('SELECT * FROM settings WHERE t_group = :group AND visible = 1 ORDER BY type, name', [ ':group'=> $group['techname'] ]);
                    if(!count($settings)){
                        echo '</div>';
                        continue;
                    }

                    if($chidren)
                        echo '<hr class="mt-0">
                           <h5 class="card-title">'.$group['name'].'</h5>';

                    echo '<div class="card-body p-0 mw8">';
                    foreach($settings as $set){

                        switch($set['type']){
                            case 'html':
                                echo $set['value'].'<hr>';
                                break;

                            case 'text':?>
                                <div class="min-input-group-prepend text-left"><?php echo $set['name'] ?></div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><?php echo $set['name'] ?></span>
                                    <input type="text" id="<?php echo $set['t_key'] ?>" class="form-control" placeholder="<?php echo $set['name'] ?>" aria-label="<?php echo $set['name'] ?>" aria-describedby="basic-addon1" value="<?php echo $set['value'] ?>">
                                    <button class="btn btn-outline-secondary" type="button"  key="<?php echo $set['t_key'] ?>" action-type="save">сохранить</button>
                                </div>
                                <? break;
                            case 'multitext': ?>
                                <div class="min-input-group-prepend text-left"><?php echo $set['name'] ?></div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><?php echo $set['name'] ?></span>
                                    <textarea class="form-control" aria-label="описание" id="<?php echo $set['t_key'] ?>" placeholder="<?php echo $set['name'] ?>"  aria-label="<?php echo $set['name'] ?>" rows=2 ><?php echo $set['value'] ?></textarea>
                                    <button class="btn btn-outline-secondary" type="button"  key="<?php echo $set['t_key'] ?>" action-type="save">сохранить</button>
                                </div>
                                <? break;
                            case 'file':
                                array_push($files, ['id'=> $set['t_key'], 'link'=> $set['value'] ]);
                                ?>
                                <span><?php echo $set['name'] ?></span>
                                <div id="form-<?php echo $set['t_key']?>">
                                    <div class="input-file">
                                        <input type="file" name="file" id="<?php echo $set['t_key'] ?>">
                                        <div class="input-overflow" id="content-<?php echo $set['t_key'] ?>" style="background-image: url(<?php echo  $set['value'] ?>)"></div>
                                    </div>
                                </div>
                                <button class="btn btn-outline-secondary mt-1 mb-1" type="button" key="<?php echo $set['t_key'] ?>" action-type="save-file">сохранить</button>
                                <? break;
                            case 'check':?>
                                <div class="custom-control custom-checkbox m-1">
                                    <input type="checkbox" class="custom-control-input" id="<?php echo $set['t_key']?>" <?php echo $set['value']==1?'checked':''?> action-type="save-check">
                                    <label class="custom-control-label" for="<?php echo $set['t_key']?>"><?php echo $set['name'] ?></label>
                                </div>
                                <? break;
                            case 'display':?>
                                <div class="min-input-group-prepend text-left"><?php echo $set['name'] ?></div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><?php echo $set['name'] ?></span>
                                    </div>
                                    <input type="text" id="<?php echo $set['t_key'] ?>" class="form-control" placeholder="<?php echo $set['name'] ?>" aria-label="<?php echo $set['name'] ?>" aria-describedby="basic-addon1" value="<?php echo $set['value'] ?>" disabled>
                                </div>
                                <? break;
                            case 'copy':?>
                                <i><?php echo $set['name'] ?></i>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="text" class="form-control" value="<?php echo $set['value'] ?>" readonly>
                                    <span class="input-group-text"><i class="bi bi-clipboard" style="cursor:pointer;" onclick="copyToclipboard(this)" textcb="<?php echo $set['value'] ?>"></i></span>
                                </div>
                                <? break;
                        }
                    }
                    echo '</div></div>';
                }

                ?>
            </div>
        </div>
    <?} }?>
    
    
<script>
    $( document ).ready(function() {
        var files = <?php echo json_encode($files)?>;

        files.forEach(function(item, i) {
            let id = item.id;
            document.getElementById(id).addEventListener("change", function(event) {

                $(`#form-${id}`).ajaxSubmit({
                    type: 'POST',
                    data: {},
                    url: '/admin/functions/load_file_without_db.php',
                    success: function(data) {

                        var res = JSON.parse(data);
                        $(`#content-${id}`).css('background-image', `url(../${res.public})`);
                        files.forEach(function(item, i) {
                            if(item.id == id)
                                files[i].link = res.public;
                        });
                    }
                });
            }, false);
        });


        $('[action-type="save-check"]').on('click', function(){
            let key = $(this).attr('id');
            let val = $(this).is(':checked')?'1':'0'
            qw.post("p.php?q=save", { key: key, value: val }, ()=> {},"json","save");
        })


        $('[action-type="save"]').on('click', function(){
            let key = $(this).attr('key');
            let val = $('#'+key).val();
            qw.post("p.php?q=save", { key: key, value: val }, r=> {
                if(key=='bot_key'){
                    console.log(r);
                    toast('Webhook', r.data.description);
                } else {
                    toast('Сохранение', "Настройки сохранены");
                }
            },"json","save");
        })

        $('[action-type="save-file"]').on('click', function(){
            let key = $(this).attr('key');
            let link = '';
            files.forEach(function(item, i) {
                if(item.id == key)
                    link = files[i].link;
            });
            qw.post("p.php?q=save", { key: key, value: link }, ()=> {},"json","save file");
        })

    });
</script>