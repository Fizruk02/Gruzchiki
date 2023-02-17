<style>
    .img-content{
        background: white;
        border: 1px solid #ced4da;
        padding: 16px;

    }
    .input-overflow{
        width: 100px;
        height: 100px;
        z-index: 0;
        background-image: url(/files/systems/no_photo_100_100.jpg);
        background-repeat: no-repeat;
        background-size: auto;
        background-position: center;
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
<div class="row">
    <div class="col-sm">
        <?php foreach ($langs as $lang) { ?>
            <div class="input-group mb-1">
                <label class="input-group-text"
                       for="tr_<?php echo $lang['iso'] ?>"><?php echo $lang['default'] == "1" ? "<b>" . $lang['iso'] . "</b>" : $lang['iso'] ?> </label>
                <input type="text" class="form-control" data-field="category" data-iso="<?php echo $lang['iso'] ?>"
                       id="tr_<?php echo $lang['iso'] ?>"
                       placeholder="<?php echo 'lang: ' . mb_strtolower($lang['name']); ?>">
            </div>
            <span>Описание</span>
            <div class="input-group mb-1">
                <span class="input-group-text"><?php echo $lang['default'] == "1" ? "<b>" . $lang['iso'] . "</b>" : $lang['iso'] ?></span>
                <textarea class="form-control" id="tr_descrt_<?php echo $lang['iso'] ?>" data-field="descr"
                          data-iso="<?php echo $lang['iso'] ?>"></textarea>
            </div>
        <?php } ?>

        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="display">
            <label class="custom-control-label" for="display">Отображение в цепочке категорий в сообщении</label>
        </div>
    </div>
    <div class="col-auto">
        <div class="img-content">
            <div id="form-file-1">
                <div class="input-file">
                    <input type="file" name="file" id="file-1">
                    <div class="input-overflow" id="content-file-1"></div>
                </div>
            </div>
        </div>
    </div>
</div>