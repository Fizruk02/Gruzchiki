var category = false,startDate,endDate;
async function deleteCategory( id ){
    alertmodcreate({'title':'Удалить категорию?', 'btnOk':'Ok','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;

    qw.post("methods.php?q=deleteCategory", { id:id },function(res) {
        categories.forEach(function(item, i) { // переносим дочерние категории в основную
            if(item.parent===id){
                categories[i]['parent']="0";
                $(`[categoryblock="${item.id}"]`).prependTo( $('#categorieslist') );
            }

        });

        categories.splice(getIndex('id',id,categories), 1);
        $(`[categoryblock="${id}"]`).remove();
        appTable.update(table, "categoryId", id, 'category', ""); //обновляем категорию в таблице
        appTable.update(table, "categoryId", id, 'categoryId', "0"); //обновляем категорию в таблице
    }, "json", "delete category");
}

function formatCat(id){
    let t='';
    for(let i=0;i<categories.length;i++){
        if(categories[i].id==id){
            t=categories[i].name;
            if(categories[i].parent*1>0)t=formatCat(categories[i].parent)+" / "+t;
            return t;
        }
    }
    return "not found";
}

async function editCategory( id ){
    let index = getIndex('id',id,categories);
    if(index===undefined||index===false) return toast('Ошибка', 'Категория не найдена, попробуйте перезагрузить страницу', 'error');
    let items = [];
    let chScr=[id].concat(childCategories(id));
    categories.filter(t => chScr.indexOf(t.id)===-1).forEach(function(it, i) {
        items.push({text:formatCat(it.id),value:it.id});
    });

    promptmodcreate({'title':'Редактирование категории','btnOk':'Сохранить','btnNo':'Отмена'},
        [
            {value:categories[index]['name']}
            ,{label:'Категория',value:categories[index]['parent'],items:items }
        ]);
    let result = await promptmod; if(!result) return;

    let name = result[0];
    let parent = result[1];

    qw.post("methods.php?q=editCategory", { id: id, name: name, parent:parent }, function(res) {

        qw.qs(`[category-name="${id}"]`).innerHTML=name;
        categories[index].name=name;
        categories[index]['parent']=parent;

        if(parent==0) parent='';
        if($(`[categoryblock="${id}"]`).parent().attr('id')!='categorieslist'+parent){
            $(`[categoryblock="${id}"]`).prependTo( $('#categorieslist'+parent) );
            if(parent!=0)
                $('#categorieslist'+parent).show(100);
            checkCollape();
        }

        appTable.update(table, "categoryId", id, 'category', name); //обновляем категорию в таблице

    }, "json", "edit category");
}

async function addCategory( ){

    let items = [];
    categories.forEach(function(it, i) {
        items.push({text:formatCat(it.id),value:it.id});
    });

    promptmodcreate({'title':'Новая категория*','btnOk':'Сохранить','btnNo':'Отмена'},
        [
            {}
            ,{label:'Категория',value:category,items:items }
        ]);

    let result = await promptmod; if(!result) return;

    let name = result[0];
    let parent = result[1];

    qw.post("methods.php?q=addCategory", { name: name, parent:parent }, function(res) {
        categories.push({id:res.id,name:name, parent:parent});
        $('#categorieslist').append( templateCategory(res.id, name) );



        if(parent==0) parent='';
        if($(`[categoryblock="${res.id}"]`).parent().attr('id')!='categorieslist'+parent){
            $(`[categoryblock="${res.id}"]`).prependTo( $('#categorieslist'+parent) );
            if(parent!=0)
                $('#categorieslist'+parent).show(100);
            checkCollape();
        }

        if(parent>0) toast("Новая категория*", "Категория «"+name+"» добавлена в «"+formatCat(parent)+"»");


    }, "json", "add category");
}


function checkCollape(){
    categories.forEach(function(it, i) {
        if($('#categorieslist'+it.id).children().length==0){
            $('#collapse-btn-'+it.id).hide();
            $('#categorieslist'+it.id).hide();
        } else {
            $('#collapse-btn-'+it.id).show();
        }
    });
}
function getCategories(){
    let c=categories.map(function(it) {
        return {text:it.name,value:it.id}
    });
    return c;
}

function childCategories(scr){
    let a=[];
    categories.filter(t => t.parent===scr).forEach(function(it){
        a.push(it.id);
        a=a.concat(childCategories(it.id));
    })
    return a;
}
function getIndex(vr,vl,arr){
    let x=false;
    arr.forEach(function(item, i) {
        if(item[vr]==vl) return x=i;
    });
    return x;
}
function toggle(id){
    if($('#categorieslist'+id).is(':visible'))$("#categorieslist"+id).hide(100);else $("#categorieslist"+id).show(100);
}

function templateCategory( id, name ){
    return `<div class="list-group-item list-group-item-action" style="margin-bottom: 1px;" search="${name}" categoryblock="${id}">
            <div class="row">
                <div class="col-sm cat_name" style="cursor:pointer" category-name="${id}" onclick="category='${id}';list();">${name}</div>
                <div class="col-auto p-0" style="display:none;" id="collapse-btn-${id}">
                    <button class="btn border-0 p-0 px-1" type="button" onclick="toggle('${id}')"> <i class="bi bi-chevron-expand"></i>  </button>
                </div>
                <div class="col-auto ps-0 pe-2">
                    <i class="bi bi-pen text-secondary" onclick="editCategory('${id}')"></i>
                </div>
                <div class="col-auto ps-0">
                    <i class="bi bi-x-lg text-danger" onclick="deleteCategory('${id}')"></i>
                </div>
            </div>
            <div class="collapse mt-2 list-group" childrenlist id="categorieslist${id}"></div>
        </div>`;
}




$(document).ready(function (){
    //startDate=moment("1986-09-19");
    //endDate=moment();
    category="0";
    list();
});

function list(){
    qw.post("methods.php?q=getList", { category:category,/*startDate:startDate.format('YYYY-MM-DD'),endDate:endDate.format('YYYY-MM-DD')*/ },function(res) {
        qw.removeClass(".cat_name.selected", "selected", qw.qs(`[categoryblock="${category}"] .cat_name`));
        appTable.init({
            table:table
            ,list:res.data
            ,template:'template'
            ,listStart:0
            ,search:''
            ,limit:10
            //,range:{
            //     start:startDate
            //    ,end:endDate
            //    ,func:range
            //}
            ,header:{
                buttons:[
                    '<button class="btn btn-outline-secondary" type="button" onclick="add()">Добавить</button>'
                ]
            }
        });
    }, "json", "get list");
}
//function range(s,e){
//    startDate=s;
//    endDate=e;
//    list();
//}

async function add(){
    promptmodcreate({'title':'Добавление','btnOk':'Сохранить','btnNo':'Отмена'},
        [
            {label:"name"},
            {label:"body", text:""},
            {files:false},
            {label:'category', value:category,items:getCategories()}
        ]);
    let result = await promptmod; if(!result) return;
    qw.post("methods.php?q=add", {
        name:result[0],
        body:result[1],
        files:result[2],
        category:result[3],
    },function(res) {
        appTable.insert(table, res.data)
    }, "json", "add");
}

async function rmove(id){
    alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;

    qw.post("methods.php?q=remove", { id:id },function(res) {
        appTable.rmove(table, "id", id)
    }, "json", "remove");
}

async function edit(id){
    let it=appTable.getitems(table, "id", id);
    if(it.length==0) return;
    promptmodcreate({'title':'Редактирование','btnOk':'Сохранить','btnNo':'Отмена'},
        [
            {label:"name", value:it[0].name},
            {label:"body", text:it[0].body},
            {files:it[0].files},
            {label:'category',value:it[0].categoryId,items:getCategories()}
        ]);
    let result = await promptmod; if(!result) return;
    qw.post("methods.php?q=edit", {
        id:id,
        name:result[0],
        body:result[1],
        files:result[2],
        category:result[3],
    },function(res) {
        appTable.update(table, "id", id, {
            'name': result[0],
            'body': result[1],
            'files': result[2],
            'categoryId': result[3],
        });
    }, "json", "edit");
}




function template( data ){
    let i=getIndex('id',data.categoryId,categories)
    let c=i!==false?categories[i]['name']:"";

    let f=qw.files.group.get(data.file);

    return `<tr class="rl">
             <td>${prep(data.line_number)}</td>
             <td>${prep(c)}</td>
             <td nostyle style="cursor:pointer" onclick="listEditFiles('${data.id}')">${f}</td>
             <td>${prep(data.name)}</td>
             <td>${prep(data.body)}</td>
             <td class="text-end">
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="edit('${prep(data.id)}')"><i class="bi bi-pencil"></i></button>
                 <button type="button" class="btn btn-outline-danger btn-sm" onclick="rmove('${prep(data.id)}')"><i class="bi bi-x-lg"></i></button>
             </td>
         </tr>`;
}
function prep(v){return v===undefined||v===false||v===NaN||v===null?'':v;}