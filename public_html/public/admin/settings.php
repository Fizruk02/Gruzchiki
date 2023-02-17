<!doctype html>
<html lang="en">
<head>
    <title>настройки</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style>
        .mw8{max-width:800px}
        .min-input-group-prepend{
            display:none !IMPORTANT;
        }
        @media screen and (max-width: 820px) {
            .input-group-prepend, #div-check-sms{
                display:none !IMPORTANT;
            }
            .min-input-group-prepend, #div-check-sms{
                display:block !IMPORTANT;
            }
        }
        @media screen and (max-width: 480px) {
        }
        .input-overflow{
            width: 400px;
            height: 200px;
            z-index: 0;
            background-repeat: no-repeat;
            background-size: auto;
            background-position: center;
            background-size: cover;
        }
        input[type="file"]{
            width: 100px;
            height: 100px;
            cursor: pointer;
            position: absolute;
            z-index: 1;
            opacity: 0;
        }
    </style>
    <?
    include_once("resources/_phpparsite.php");
    res("_ass.php");
    if(!$permission_to_use['access']) return;

    ?>
</head>
<body>
<? res("_nav.php")?>
<?

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
                <? //EXISTS(SELECT * FROM settings s WHERE s.t_group = g.techname AND s.visible = 1)
                foreach($groups_arr as $group){
                    echo '<li class="nav-item" role="presentation">
                       <a class="nav-link '.($group['id']==1?'active':'').'" id="tab-key-'.$group['id'].'" data-bs-toggle="tab" href="#tab-'.$group['id'].'" role="tab" aria-controls="home" aria-selected="'.($group['id']==1?'true':'false').'">'.$group['name'].'</a>
                     </li>';
                }
                ?>
            </ul>
        </div>
        <div class="card-body m-0 p-1" index-source="main">
            <div class="tab-content" id="TabContent_<?=$groupOwner?>">
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
                                <div class="min-input-group-prepend text-left"><?=$set['name'] ?></div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><?=$set['name'] ?></span>
                                    <input type="text" id="<?=$set['t_key'] ?>" class="form-control" placeholder="<?=$set['name'] ?>" aria-label="<?=$set['name'] ?>" aria-describedby="basic-addon1" value="<?=$set['value'] ?>">
                                    <button class="btn btn-outline-secondary" type="button"  key="<?=$set['t_key'] ?>" action-type="save">сохранить</button>
                                </div>
                                <? break;
                            case 'multitext': ?>
                                <div class="min-input-group-prepend text-left"><?=$set['name'] ?></div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><?=$set['name'] ?></span>
                                    <textarea class="form-control" aria-label="описание" id="<?=$set['t_key'] ?>" placeholder="<?=$set['name'] ?>"  aria-label="<?=$set['name'] ?>" rows=2 ><?=$set['value'] ?></textarea>
                                    <button class="btn btn-outline-secondary" type="button"  key="<?=$set['t_key'] ?>" action-type="save">сохранить</button>
                                </div>
                                <? break;
                            case 'file':
                                array_push($files, ['id'=> $set['t_key'], 'link'=> $set['value'] ]);
                                ?>
                                <span><?=$set['name'] ?></span>
                                <div id="form-<?=$set['t_key']?>">
                                    <div class="input-file">
                                        <input type="file" name="file" id="<?=$set['t_key'] ?>">
                                        <div class="input-overflow" id="content-<?=$set['t_key'] ?>" style="background-image: url(<?= $set['value'] ?>)"></div>
                                    </div>
                                </div>
                                <button class="btn btn-outline-secondary mt-1 mb-1" type="button" key="<?=$set['t_key'] ?>" action-type="save-file">сохранить</button>
                                <? break;
                            case 'check':?>
                                <div class="custom-control custom-checkbox m-1">
                                    <input type="checkbox" class="custom-control-input" id="<?=$set['t_key']?>" <?=$set['value']==1?'checked':''?> action-type="save-check">
                                    <label class="custom-control-label" for="<?=$set['t_key']?>"><?=$set['name'] ?></label>
                                </div>
                                <? break;
                            case 'display':?>
                                <div class="min-input-group-prepend text-left"><?=$set['name'] ?></div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><?=$set['name'] ?></span>
                                    </div>
                                    <input type="text" id="<?=$set['t_key'] ?>" class="form-control" placeholder="<?=$set['name'] ?>" aria-label="<?=$set['name'] ?>" aria-describedby="basic-addon1" value="<?=$set['value'] ?>" disabled>
                                </div>
                                <? break;
                            case 'copy':?>
                                <i><?=$set['name'] ?></i>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="text" class="form-control" value="<?=$set['value'] ?>" readonly>
                                    <span class="input-group-text"><i class="bi bi-clipboard" style="cursor:pointer;" onclick="copyToclipboard(this)" textcb="<?=$set['value'] ?>"></i></span>
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
</body>

<?php res('js.php')?>

<script>
    $( document ).ready(function() {
        var files = <?=json_encode($files)?>;

        files.forEach(function(item, i) {
            let id = item.id;
            document.getElementById(id).addEventListener("change", function(event) {

                $(`#form-${id}`).ajaxSubmit({
                    type: 'POST',
                    data: {},
                    url: '../admin/functions/load_file_without_db.php',
                    success: function(data) {

                        var res = jQuery.parseJSON(data);
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
            qw.post("/post/?request=settings/save/", { key: key, value: val }, function(r) {},"json","save");
        })


        $('[action-type="save"]').on('click', function(){
            let key = $(this).attr('key');
            let val = $('#'+key).val();
            qw.post("/post/?request=settings/save/", { key: key, value: val }, function(res) {
                if(key=='bot_key'){
                    $.get(res.url, {  }).done(function(e) {
                        toast('Webhook', e.description);
                        console.log(e);
                    });
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
            qw.post("/post/?request=settings/save/", { key: key, value: link }, function(res) {},"json","save file");
        })

    });
</script>
</html>