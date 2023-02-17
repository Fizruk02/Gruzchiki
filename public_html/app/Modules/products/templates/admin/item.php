<?php
/* @var $block array */

use system\lib\Db;
use system\lib\Asset;

//$item = $block['content']['data'];
$db=DB::getInstance();
$asset = Asset::getInstance();

$langs = $db->arrayQuery('SELECT * FROM `s_langs` ORDER BY `default` DESC, `name`');

//$availabilityList = arrayQuery('SELECT id, name FROM `market_item_status_availability`');

$itemId = $_GET['itemId'] ?? null;

if($itemId){
    $row = $db->singleQuery('SELECT `name`, `techname`, `display`, `short_description`, `description`, `files`, `preview_image`, `availability` FROM `market_items` WHERE id = :itemId', [':itemId'=> $itemId], true);

    if ($row){
        $name = $row['name'];
        //$availability = $row['availability'];
    }

}


$prices = $db->arrayQuery('SELECT name, id FROM market_items_prices_type');

function opt($d){
    return implode('', array_map(function($it) { return "<option value=\"{$it['name']}\">{$it['name']}</option>"; }, $d));
}
$units=$db->arrayQuery('SELECT name FROM `market_items_units` ORDER BY name');
$currencies=$db->arrayQuery('SELECT name FROM `market_items_currencies` ORDER BY name');
$langs=$db->arrayQuery('SELECT * FROM `s_langs` ORDER BY `default` DESC, `name`');

$settings = $db->singleQuery('SELECT * FROM `settings` WHERE `t_key` = "marketItemGroupPublicId"');
$marketItemGroupPublicId = $settings['value'] ?? '';

$asset->regCss(Bt::getAlias("@root/system/modules/products/templates/admin/style.css"));
$asset->regCss(Bt::getAlias('@www/common/jsTree/themes/default/style.min.css'));
$asset->regCss('//stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
$asset->regCss('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css');
$asset->regJs(
    [
        "//ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/jquery-ui.min.js",
        "//cdnjs.cloudflare.com/ajax/libs/exceljs/3.8.2/exceljs.js",
        "//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.71/pdfmake.min.js",
        "//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.71/vfs_fonts.js",
        "//cdn.jsdelivr.net/momentjs/latest/moment.min.js",
        "//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js",
        "//snipp.ru/cdn/jQuery-Form-Plugin/dist/jquery.form.min.js",
        "//b2bot.ru/js/jscolor.min.js",
        "//b2bot.ru/components/table.js",
        "//b2bot.ru/components/grid.js",
        "//b2bot.ru/components/toast.js",
        "//b2bot.ru/components/upload/js.js",
        "//b2bot.ru/components/b2.js",
        "//b2bot.ru/components/qw.js",
        "//b2bot.ru/components/dialogboxes.js",
        "//b2bot.ru/components/tgeditor/js.js",
        '/admin/categories/category.js?v=0.1'
    ], ['bootstrap']);
$asset->regJs('/admin/js/jquery.min.js', ['jquery'], 'admin');
$asset->regJs(Bt::getAlias("@www/common/jsTree/jstree.min.js"), ['jquery'], 'jstree');
//$asset->regJs(Bt::getAlias("@root/system/modules/products/templates/script.js"), ['jquery', 'jstree', 'admin'], 'product_script');
?>
<style>
    .pointer {
        cursor:pointer;
    }
</style>
<div class="container">
    <div class="row">
        <div class="col-sm  border-right">
            <div>

                <div class="d-flex align-items-start">
                    <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <?php foreach($langs as $lan){?>
                        <button class="nav-link <?php echo $lan['default']?'active':''?>" id="v-pills-<?php echo $lan['iso']?>-tab" data-bs-toggle="pill" data-bs-target="#v-pills-<?php echo $lan['iso']?>" type="button" role="tab" aria-controls="v-pills-<?php echo $lan['iso']?>" aria-selected="true"><?php echo $lan['iso']?></button>
                        <?php }?>
                    </div>
                    <div class="tab-content w-100" id="translates-content">
                        <?php foreach($langs as $lan){?>
                        <div class="tab-pane fade <?php echo $lan['default']?'show active':''?>" id="v-pills-<?php echo $lan['iso']?>" role="tabpanel" aria-labelledby="v-pills-<?php echo $lan['iso']?>-tab" tabindex="0">
            
                            <div class="input-group mb-1">
                                <span class="input-group-text" style="width:150px" >название</span>
                                <input type="text" class="form-control item_name" data-field="name" data-iso="<?php echo $lan['iso']?>" placeholder="введите название">
                            </div>
            
                            <div class="input-group mb-1">
                                <span class="input-group-text" style="width:150px">краткое описание</span>
                                <input type="text" class="form-control item_short_desc" data-field="short_description" data-iso="<?php echo $lan['iso']?>" placeholder="краткое описание">
                            </div>
                            <div class="text-end mb-1" id="editor"></div>
                            <div class="form-floating">
                                <textarea class="form-control item_desc" data-field="description" data-iso="<?php echo $lan['iso']?>" style="height: 160px" placeholder="Описание товара"></textarea>
                                <label for="description_editor">Описание</label>
                            </div>
            
                        </div>
                        <?php }?>
            
                    </div>
                </div>






                <div class="my-2" id="fielsarea">

                </div>


                <div class="container p-0 mb-1">
                    <div class="row">
                        <div class="col-3 border-right">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <div class="nav-link pointer active" id="v-pills-item-data-main-tab" data-bs-toggle="pill" href="#v-pills-item-data-main" role="tab" aria-controls="v-pills-item-data-main" aria-selected="true"> <i class="bi bi-currency-exchange"></i> Цены</div>
                                <!-- <img src="../images/ic/tools.svg"> -->
                                 <div class="nav-link pointer" id="v-pills-item-data-seo-tab" data-bs-toggle="pill" href="#v-pills-item-data-seo" role="tab" aria-controls="v-pills-item-data-seo" aria-selected="true"><i class="bi bi-graph-up-arrow"></i> SEO</div>
                                <!-- <div class="nav-link pointer" id="v-pills-item-data-script-tab" data-bs-toggle="pill" href="#v-pills-item-data-script" role="tab" aria-controls="v-pills-item-data-script" aria-selected="true"><i class="fa fa-comment"> </i> Скрипт заказа</div>-->
                                <!-- <div class="nav-link pointer" id="v-pills-item-data-reserves-tab" data-bs-toggle="pill" href="#v-pills-item-data-reserves" role="tab" aria-controls="v-pills-item-data-reserves" aria-selected="false"><img src="images/ic/bag-check-fill.svg">  Запасы</div> -->
                                <!--       <div class="nav-link pointer" id="v-pills-item-data-delivery-tab" data-bs-toggle="pill" href="#v-pills-item-data-delivery" role="tab" aria-controls="v-pills-item-data-delivery" aria-selected="false"> <img src="images/ic/truck.svg">  Доставка</div>
                                   <div class="nav-link pointer" id="v-pills-item-data-accompanying-tab" data-bs-toggle="pill" href="#v-pills-item-data-accompanying" role="tab" aria-controls="v-pills-item-data-accompanying" aria-selected="false"> <img src="images/ic/link-45deg.svg">  Сопутствующие</div>
                                   <div class="nav-link pointer" id="v-pills-item-data-options-tab" data-bs-toggle="pill" href="#v-pills-item-data-options" role="tab" aria-controls="v-pills-item-data-options" aria-selected="false"> <img src="images/ic/credit-card-2-front.svg">  Опции</div>
                                   <div class="nav-link pointer" id="v-pills-item-data-additionally-tab" data-bs-toggle="pill" href="#v-pills-item-data-additionally" role="tab" aria-controls="v-pills-item-data-additionally" aria-selected="false"> <img src="images/ic/gear.svg">  Дополнительно</div>
                                   -->
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="tab-content" id="v-pills-tabContent">

                                <div class="tab-pane fade show active" id="v-pills-item-data-main" role="tabpanel" aria-labelledby="v-pills-item-data-main-tab">
                                  
                                    <span>Цены</span>
                                    <div class="card">
                                        <div class="card-body" id="prices">
                                            <?php
                                            foreach($prices as $price){
                                                $id = $price['id'];
                                                ?>
                                                <div class="input-group mb-1 price_item" data-id="<?= $id ?>">
                                                 
                                                    <label class="input-group-text"><?= $price['name']?></label>
                                                    <input type="number" min=0 class="form-control price_value" placeholder="0.00" oninput="func.price.change('<?= $id ?>')">
                                                    
                                                    <select class="form-select price_currencies">
                                                        <option selected value="">Валюта не выбрана</option>
                                                        <?php echo opt($currencies)?>
                                                    </select>
                                                    
                                                    <label class="input-group-text">за</label>
                                                    <select class="form-select price_units">
                                                        <option selected value="">Ед. изм. не выбрана</option>
                                                        <?php echo opt($units)?>
                                                    </select>

                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            <input type="radio" class="price_default" name="price-default" id="price-default-<?= $id ?>">
                                                            <label class="form-check-label ml-1" for="price-default-<?= $id ?>">по умолчанию</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?}?>
                                        </div>
                                    </div>
                                </div>



                                <div class="tab-pane fade" id="v-pills-item-data-seo" role="tabpanel" aria-labelledby="v-pills-item-data-seo-tab">

                                    <div class="input-group mb-3">
                                        <span class="input-group-text">title</span>
                                        <input type="text" class="form-control seo_title" placeholder="если не указано, то title = название товара (<?php echo $langs[0]['iso']; ?>)">
                                    </div>

                                    <div class="input-group mb-3">
                                        <span class="input-group-text">URL (ЧПУ)</span>
                                        <input type="text" class="form-control seo_slug" placeholder="Человекопонятный URL">
                                    </div>

                                    <div class="form-floating">
                                        <textarea class="form-control seo_description" id="seo_description" style="height: 160px" ></textarea>
                                        <label for="seo_description">Description (если не указано, то description = описание товара (<?php echo $langs[0]['iso']; ?>))</label>
                                    </div>


                                </div>

                                <!--
<div class="tab-pane fade" id="v-pills-item-data-delivery" role="tabpanel" aria-labelledby="v-pills-item-data-delivery-tab">...</div>
<div class="tab-pane fade" id="v-pills-item-data-accompanying" role="tabpanel" aria-labelledby="v-pills-item-data-accompanying-tab">...</div>
<div class="tab-pane fade" id="v-pills-item-data-options" role="tabpanel" aria-labelledby="v-pills-item-data-options-tab">...</div>
<div class="tab-pane fade" id="v-pills-item-data-additionally" role="tabpanel" aria-labelledby="v-pills-item-data-additionally-tab">...</div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-auto">
            <div class="card mb-2 p-3" style="width: 18rem;">
                <button type="button" class="btn btn-outline-success m-1" id="save-market-item">Сохранить</button>
                <button type="button" class="btn btn-outline-secondary m-1" id="new-market-item">Создать новый</button>
                <button type="button" class="btn btn-outline-danger m-1" id="delete-market-item">Удалить</button>
                <div id="confirmation-market-item-delete" style="display:none;" class="m-1 w-100 row">
                    <div class="col-auto p-0  pr-1 pt-1">
                        <span class="ml-1 mt-2"> Удалить? </span>
                    </div>
                    <div class="col-auto p-0 pr-1">
                        <button type="button" class="btn btn-outline-secondary"  id="btn-delete-market-item-yes">Да</button>
                    </div>
                    <div class="col p-0 w-100">
                        <button type="button" class="btn btn-outline-secondary w-100" id="btn-delete-market-item-no">Нет</button>
                    </div>
                </div>
                <div class="custom-control custom-checkbox" style="padding-left: 30px;">
                    <input type="checkbox" class="custom-control-input" id="check-display" checked>
                    <label class="custom-control-label" for="check-display">отображать в каталоге</label>
                </div>
            </div>
            <div class="card mb-2 p-3" style="width: 18rem;overflow-x: auto;">
                <div id="categories" class="col-auto">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            
            <div class="card mb-2" style="width: 18rem">
                <div class="card-body pt-0">
                <span>Группа или канал для отображения постов, комментариев</span> 
                <input type="text" class="form-control" placeholder="id группы или канала" id='group_public' value="<?=$marketItemGroupPublicId?>">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/admin/categories/category.js?v=0.1"></script>
<script>

    var filegroup = "";
    var preview_image ="";
    var itemId = <?= $itemId?:"''";?>;
    
    project={
        units: <?php echo json_encode($units);?>,
        currencies: <?php echo json_encode($currencies);?>,
    }
    
    templates={
        price:(it)=> { // не используется
            let id=it.id;
            let units = templates.options(project.units,it.unit);
            let currencies = templates.options(project.currencies,it.currency);
            return `
                <div class="input-group mb-1" id="${id}">
                    <label class="input-group-text">${it.type_name}</label>
                    <input type="number" min=0 class="form-control" placeholder="0.00" id="price-${id}" value="<?= $price['price']?>" oninput="func.price.change(${id})">
                    <select class="form-select" id="currency-${id}" value="<?=$price['currency']?>">
                    <option selected value="">Валюта не выбрана</option>
                    ${currencies}
                    </select>
                    <label class="input-group-text" for="unit-${id}">за</label>
                    <select class="form-select" id="unit-${id}" value="<?= $price['unit']?>">
                    <option selected value="">Ед. изм. не выбрана</option>
                    ${units}
                    </select>
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <input type="radio" aria-label="" name="price-default" id="price-default-${id}" ${it.by_default==="1"?"checked":""}>
                            <label class="form-check-label ml-1" for="price-default-${id}">по умолчанию</label>
                        </div>
                    </div>
                </div>
            `;
        },
        options:(d,sel)=>{
            return d.map((it)=>{ return `<option ${sel===it.name?"selected":""} value="${it.name}">${it.name}</option>` }).join("");
        }
    }
   
    //return implode('', array_map(function($it) use($sel){ return "<option ".($sel===$it['name']?'selected':'')." value=\"{$it['name']}\">{$it['name']}</option>"; }, $d));

    func={
        price:{
            change:(id)=> {
                if(!qw.qs(".price_default:checked"))qw.qs("#price-default-"+id).checked=true;
            }
        }
    }
    
    $(document).ready(function() {

        qw.event(".seo_slug", "blur", (el)=>{
            let u=el.target.value;
            qw.post(bt_path + "/modules/products/products-admin/saveSlug", {id:itemId, url:u},(r)=>{
                el.target.value=r.slug;
            }, "json", "save slug")
        })

        $("#editor").html(appTelegramEditor.init('description_editor'));

        if(itemId) $.post(bt_path + "/modules/products/products-admin/getData", { id_item: itemId }, (r)=> {
            let el='';
            r.prices.forEach((it)=>{
                el=qw.qs(`.price_item[data-id="${it.id_type}"]`);
                el.querySelector(".price_value").value=it.price;
                el.querySelector(".price_units").value=it.unit;
                el.querySelector(".price_currencies").value=it.currency;
                el.querySelector(".price_default").checked=it.by_default==="1";
            });

            if(r.seo)r.seo.forEach((it)=> {
                qw.qs(".seo_"+it.var).value=it.val;
            })
            
            qw.lang.post(r.translates);
            filegroup=r.item.files;

            
            app_categories.init({
                id_categories: '#categories',
                selected:r.categories
            })
            
            qw.qs("#check-display").checked=r.item.display==="1";

            filesForm();
            
            
            
        },"json", "get data");

        if(!itemId) {
            filesForm();
            app_categories.init({
                id_categories: '#categories'
            })
        }

        $('#new-market-item').click(function() {
            document.location.href = "?itemId=";
        })
        
        $('#save-market-item').click(function() {
            let description = qw.lang.get(".item_desc");
            let name = qw.lang.get(".item_name");
            let group_public = $('#group_public').val();
            let techname = $('#techname_editor').val();
            let display = $('#check-display').is(':checked') ? 1 : 0;
            let availability = $('#availability').val();
            let short_description = qw.lang.get(".item_short_desc");
            let categories = $("#categories").jstree().get_selected()||[]
            let scripts = [];
            $("#scripts-container").children().each(function(i, elem) {
                if($('#check-script-mode-' + i).is(':checked') || $('#check-instant-launch-' + i).is(':checked')) {
                    let key_name = $('#script-key-name-' + i).val();
                    let script = $('#select-script-from-script-item-' + i).val();
                    let chat = $('#script-chat-' + i).val();
                    let instant_launch = $('#check-instant-launch-' + i).is(':checked') ? 1 : 0;
                    if((key_name != '') || (instant_launch)) scripts.push({
                        key_name: key_name,
                        script: script,
                        chat: chat,
                        instant_launch: instant_launch
                    });
                }
            });
            let prices = [];
            
            qw.qsa(".price_item").forEach((el)=>{
                prices.push({
                    id: el.dataset.id,
                    cell: el.querySelector(".price_value").value,
                    unit: el.querySelector(".price_units").value,
                    currency: el.querySelector(".price_currencies").value,
                    by_default: el.querySelector(".price_default").checked?1:0
                });
            });

            let seo=[
                { "var":"description",val:qw.qs(".seo_description").value },
                { "var":"slug",val:qw.qs(".seo_slug").value },
                { "var":"title",val:qw.qs(".seo_title").value },
            ]



            
            let par = {
                seo:seo,
                name: name,
                id_item: itemId,
                display: display,
                files: filegroup,
                techname: techname,
                description: description,
                availability: availability,
                group_public: group_public,
                preview_image: preview_image,
                prices: JSON.stringify(prices),
                scripts: JSON.stringify(scripts),
                short_description: short_description,
                categories: JSON.stringify(categories)
            };

            qw.post("/modules/products/products-admin/save", par, (res)=> {
                    itemId = res.itemId;
                    let newUrl = window.location.pathname + '?itemId=' + itemId;
                    history.pushState('', '', newUrl);
                    if(res.slug) qw.qs(".seo_slug").value=res.slug||"";
                    
            }, "json", "save item");
        });
        $('input[name="exampleRadios"]').click(function() {
            let actChbx = $(this).attr('id');
            $("#scripts-container").children().each(function(i, elem) {
                if('check-instant-launch-' + i != actChbx) $('#check-instant-launch-' + i).prop('checked', false);
            });
        });








        $('body').on('click', '#btn-delete-market-item-no', function() {
            $('#confirmation-market-item-delete').hide();
            $('#delete-market-item').show();
        });
        $('body').on('click', '#delete-market-item', function() {
            $('#confirmation-market-item-delete').show();
            $('#delete-market-item').hide();
        });
        $('body').on('click', '#btn-delete-market-item-yes', function() {
            $.post(bt_path + "/modules/products/products-admin/remove", {
                id: itemId
            }).done(function(data) {
                document.location.href = bt_path + "/admin/market_items";
            });
        });

    });



    function filesForm(){

        $.post(bt_path + "/admin/upload/getfiles.php", { group:filegroup }).done(
            function(data) {
                let res = JSON.parse(data);

                let btn=appUpload.form({
                    id:1
                    ,classes:'btn btn-outline-secondary'
                    ,group:filegroup
                    ,uploadFunc:'uploadFunc' // (function)function on successful download. The group id is passed
                    ,deleteFunc:'uploadFunc' // (function)function when deleting a file. The group id is passed
                })

                let area = appUpload.container({
                    id:1
                    ,files:res.data
                })

                $('#fielsarea').html(btn+area);

            });
    }

    function uploadFunc(id, groupFilesId){
        filegroup=groupFilesId;
    }

</script>
