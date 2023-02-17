<?php
/* @var $block array */

use system\lib\Db;
use system\lib\Asset;

//$item = $block['content']['data'];
$db=DB::getInstance();
$asset = Asset::getInstance();

$langs = $db->arrayQuery('SELECT * FROM `s_langs` ORDER BY `default` DESC, `name`');

$categories = $db->arrayQuery('SELECT id, `parent_id` `parent`, `category` `name` FROM `s_categories`');

function formatCat($id, $categories){
    $t='';
    for($i=0;$i<count($categories);$i++){
        if($categories[$i]['id']==$id){
            $t=$categories[$i]['name'];
            if($categories[$i]['parent']*1>0)$t=formatCat($categories[$i]['parent'], $categories)." / ".$t;
            return $t;
        }
    }
    return "not found";
}

$cats=array_map(function ($it) USE ($categories){
    return [
        'title'=> formatCat($it['id'], $categories),
        'id'=> $it['id']
    ];
}, $categories);

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
        "//b2bot.ru/components/tgeditor/js.js"
    ], ['bootstrap']);
$asset->regJs('/admin/js/jquery.min.js', ['jquery'], 'admin');
$asset->regJs(Bt::getAlias("@www/common/jsTree/jstree.min.js"), ['jquery'], 'jstree');
$asset->regJs(Bt::getAlias("@root/system/modules/products/templates/admin/script.js"), ['jquery', 'jstree', 'admin'], 'product_script');

?>
<div class="container mt-2" tableform>
    <div style="overflow-x: auto;width:100%;" tablecontainer>
        <table class="table table-hover table-sm" id="tableItems" style="min-width:1200px">
            <thead class="thead-dark">
            <tr>
                <th style="width:60px">#</th>
                <th style="width:100px">приоритет</th>
                <th style="width:160px"></th>
                <th>название</th>
                <th>категория</th>
                <th><!-- цена --></th>
                <th>url</th>
                <th style="width:78px"></th>
            </tr>
            </thead>
        </table>
    </div>
</div>


<div class="modal fade" id="modalDir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDirTitle">Список</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDirBody">

            </div>
        </div>
    </div>
</div>


<script>

    const template = {
        categories: `       <select class="form-select form-select-sm" onclick="func.selectcat(this)">
           <option value="">Все</option>
           <?php foreach($cats as $cat) echo '<option value="'.$cat['id'].'">'.$cat['title'].'</option>' ?>
       </select>`,

        row:( data )=> {
            let f="",st_tr=true;
            data.file.forEach(function(it) {
                if(it.id_group!=="0"&&it.id_group!=="false"&&it.id_group){
                    if(it.type==='img') f+= hpr(it);
                    if(it.type==='doc') f+= hprdoc(it);
                    if(it.type==='video') f+= hprdoc(it);
                }
            });

            project.langs.forEach((it)=>{
                if(!data.tr_name[it.iso]) st_tr=false;
            })

            if(f==="") f=hpr({preview:bt_path + "/files/systems/no_photo_100_100.jpg"});

            let price=data.price_type+": "+(data.price_id===""?"-":data.price+" "+data.currency+(data.unit===""?"":"/"+data.unit));

            return `<tr class="rl">
                 <td>${data.line_number}</td>
                 <td onclick="listEditPriority('${data.id}')">${data.priority==="0"?"-":data.priority}</td>
                 <td nostyle style="cursor:pointer" onclick="listEditFiles('${data.id}')">${f}</td>
                 <td onclick="listEditName('${data.id}')">${st_tr?"":'<i class="bi bi-exclamation-triangle text-danger me-1"></i>'}${data.name}</td>
                 <td>${data.category}</td>
                 <td onclick="listEditPrice('${data.id}', '${data.price_type_id}')">${price}</td>
                 <td><span class="text-secondary">/</span>${data.slug}</td>
                 <td>
                     <button type="button" class="btn btn-outline-secondary btn-sm" onclick="edit('${data.id}')"><i class="bi bi-pencil"></i></button>
                     <button type="button" class="btn btn-outline-danger btn-sm" onclick="rmove('${data.id}')"><i class="bi bi-x-lg"></i></button>
                 </td>
             </tr>`;
        }

    }

    const project = {
        langs:<?php echo json_encode($langs);?>,
    };

    const func={
        selectcat:(th)=>{
            list(th.value);
        }
    }

    const table = 'tableItems', tableDir = "tableDir";
    var templ=template.row;
    function list(cat){
        qw.post(bt_path + "/modules/products/products-admin/list", {cat:cat}, (res)=> {
            appTable.init({
                table:table
                ,list:res.data
                ,template:'templ'
                ,listStart:0
                ,search:''
                ,limit:10
                ,header:{
                    left: [
                        template.categories
                    ],
                    buttons:[
                        `<div class="dropdown">
                  <button class="btn btn-outline-secondary dropdown-toggle btn-sm" type="button" id="dropdownMenuButtonDir" data-bs-toggle="dropdown" aria-expanded="false">
                    Справочники
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtonDir">
                    <li><button  class="dropdown-item" onclick="directory('prices_type')">Типы цен</button ></li>
                    <li><button  class="dropdown-item" onclick="directory('units')">Единицы измерения</button ></li>
                    <li><button  class="dropdown-item" onclick="directory('currency')">Валюта</button ></li>
                  </ul>
                </div>`,
                        '<button class="btn btn-outline-secondary" type="button" onclick="add()">Добавить</button>'
                    ]
                }
            });
        },"json","Get list");
    }



    async function add(){
        document.location.href = "?itemId=";
    }

    async function rmove(id){
        alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Отмена'});
        let result = await alertmod; if(!result) return;

        $.post(bt_path + "/modules/products/products-admin/remove", { id:id }).done(function(data) {
            var res = jQuery.parseJSON(data);
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            appTable.rmove(table, "id", id)
        });
    }

    function edit(id){
        document.location.href = "?itemId="+id;
    }
    async function listEditFiles(id){
        let it=appTable.getitems(table, "id", id);
        if(it.length===0) return;
        promptmodcreate({'title':'Редактирование файлов','btnOk':'Сохранить','btnNo':'Отмена'},
            [ {files:it[0].files} ]);
        let result = await promptmod; if(!result) return;
        $.post(bt_path + "/modules/products/products-admin/listEditFiles", { id:id,gr:result[0]  }, function(res) {
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            appTable.update(table, "id", id, 'files', result[0]);
            appTable.update(table, "id", id, 'file', res.data);
        }, "json");
    }

    async function listEditPrice(id,typeid){
        if(typeid==="") return toast("Редактирование цены", "Установите цену в карточке товара", 'w');
        let it=appTable.getitems(table, "id", id);
        if(it.length===0) return;
        promptmodcreate({'title':it[0].price_type,'btnOk':'Сохранить','btnNo':'Отмена',size:"sm"}, [ {value:it[0].price} ]);
        let result = await promptmod; if(!result) return;
        $.post(bt_path + "/modules/products/products-admin/listEditPrice", { id:id,typeid:typeid,price:result[0] }, function(res) {
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            appTable.update(table, "id", id, 'price', result[0]);
        }, "json");
    }
    async function listEditName(id){
        let it=appTable.getitems(table, "id", id);
        if(it.length===0) return;
        let v=[];
        project.langs.forEach((lan)=>{
            v.push({
                value:it[0].tr_name[lan.iso]||"",
                label:lan.default==="1"?"<b>"+lan.iso+"</b>":lan.iso
            });
        })
        promptmodcreate({'title':it[0].name,'btnOk':'Сохранить','btnNo':'Отмена'}, v);
        let result = await promptmod; if(!result) return;
        let n={};
        project.langs.forEach((it,i)=>{
            n[it.iso]=result[i]
        })
        qw.post(bt_path + "/modules/products/products-admin/listEditName", { id:id,name:result[0], tr:n,sd:[1,2] },(r)=> {
            appTable.update(table, "id", id, {
                tr_name:n,name:result[0]
            });
        }, "json","name edit");
    }
    async function listEditPriority(id){
        let it=appTable.getitems(table, "id", id);
        if(it.length===0) return;
        promptmodcreate({'title':it[0].name+", приоритет: "+it[0].priority,'btnOk':'Сохранить','btnNo':'Отмена'}, [ {value:it[0].priority} ]);
        let result = await promptmod; if(!result) return;
        $.post(bt_path + "/modules/products/products-admin/listEditPriority", { id:id,priority:result[0]*1 }, function(res) {
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            appTable.update(table, "id", id, 'priority', result[0]*1);
        }, "json");
    }


    function hpr(d){
        return `<img src="${d.preview}" class="rounded" style="margin: 2px;max-width:40px;max-height:27px;" alt="">`;
    }
    function hprdoc(d){
        return `<img src="//b2bot.ru/components/upload/icons/${d.ext}.png"  alt=""
            onError="this.src='//b2bot.ru/components/upload/icons/empty.png'" class="rounded"
                style="max-width:40px;max-height:27px;margin: 2px;">`;
    }




    /**********************************************************************/
    var dirSrc;
    function directory(src) {
        dirSrc = src;
        $("#modalDirBody").html(`        <div class="container mt-2" tableform>
           <div style="overflow-x: auto;width:100%;" tablecontainer>
              <table class="table table-hover table-sm" id="tableDir">
                 <thead class="thead-dark">
                    <tr>
                      <th>название</th>
                      <th style="width:78px"></th>
                    </tr>
                 </thead>
              </table>
           </div>
        </div>`);

        switch (src) {
            case "prices_type":
                qw.qs("#modalDirTitle").innerHTML = "Типы цен";
                break;
            case "units":
                qw.qs("#modalDirTitle").innerHTML = "Единицы измерения";
                break;
            case "currency":
                qw.qs("#modalDirTitle").innerHTML = "Валюта";
                break;
        }
        listDir();
        $("#modalDir").modal("show");
    }

    function listDir(){
        $.post(bt_path + "/modules/products/products-admin/getListDir", {src:dirSrc}).done(function(data) {
console.log({src:dirSrc});
console.log(data);
            var res = jQuery.parseJSON(data);
console.log(res);
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');

            appTable.init({
                table:tableDir
                ,list:res.data
                ,template:'templateDir'
                ,listStart:0
                ,search:''
                ,limit:10
                ,header:{
                    buttons:[
                        `<button class="btn btn-outline-secondary" type="button" onclick="addDir()">Добавить</button>`
                    ]
                }
            });
        });
    }


    async function addDir(){
        promptmodcreate({'title':'Добавление','btnOk':'Сохранить','btnNo':'Отмена'},
            [{}]);
        let result = await promptmod; if(!result) return;
        $.post(bt_path + "/modules/products/products-admin/addDir", { name:result[0], src:dirSrc }, function(res) {
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            appTable.insert(tableDir, res.data)
        }, "json");
    }

    async function rmovePrice(id){
        alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Отмена'});
        let result = await alertmod; if(!result) return;

        $.post(bt_path + "/modules/products/products-admin/removeDir", { id:id, src:dirSrc }, function(res) {
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            appTable.rmove(tableDir, "id", id)
        }, "json");
    }

    async function editPrice(id){
        let it=appTable.getitems(tableDir, "id", id);
        if(it.length===0) return;
        promptmodcreate({'title':'Редактирование','btnOk':'Сохранить','btnNo':'Отмена'},
            [ {value:it[0].name} ]);
        let result = await promptmod; if(!result) return;
        $.post(bt_path + "/modules/products/products-admin/editDir", { id:id,name:result[0], src:dirSrc }, function(res) {
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            appTable.update(tableDir, "id", id, 'name', result[0]);
        }, "json");
    }

    function templateDir( data ){
        return `<tr class="rl">
             <td>${data.name}</td>
             <td>
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="editPrice('${data.id}')"><i class="bi bi-pencil"></i></button>
                 <button type="button" class="btn btn-outline-danger btn-sm" onclick="rmovePrice('${data.id}')"><i class="bi bi-x-lg"></i></button>
             </td>
         </tr>`;
    }

</script>
