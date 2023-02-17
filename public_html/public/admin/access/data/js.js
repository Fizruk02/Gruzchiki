var role = false,startDate,endDate;
async function deleterole( id ){
    alertmodcreate({'title':'Удалить категорию?', 'btnOk':'Ok','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;
    
    $.post("methods.php?q=deleterole", { id:id }).done(
    function(data) {
    	let res = jQuery.parseJSON(data);
    	if(res.success!=='ok')
    		return toast('Ошибка', res.err?res.err:'error', 'error');
    
        roles.forEach(function(item, i) { // переносим дочерние категории в основную
            if(item.parent===id){
                roles[i]['parent']="0";
                $(`[roleblock="${item.id}"]`).prependTo( $('#roleslist') );
            }
           
        });
    
    	roles.splice(getIndex('id',id,roles), 1);
        $(`[roleblock="${id}"]`).remove();
        appTable.update(table, "roleId", id, 'role', ""); //обновляем категорию в таблице
        appTable.update(table, "roleId", id, 'roleId', "0"); //обновляем категорию в таблице
    }); 
}

async function editrole( id ){
    let index = getIndex('id',id,roles);
    if(index===undefined||index===false) return toast('Ошибка', 'Категория не найдена, попробуйте перезагрузить страницу', 'error');
    let items = [];
    let chScr=[id].concat(childroles(id));
    roles.filter(t => chScr.indexOf(t.id)===-1).forEach(function(it, i) {
    	items.push({text:it.name,value:it.id});
    });
    
    promptmodcreate({'title':'Редактирование категории','btnOk':'Сохранить','btnNo':'Отмена'},
    [
         {value:roles[index]['name']}
        ,{label:'Категория',value:roles[index]['parent'],items:items } 
    ]); 
    let result = await promptmod; if(!result) return;
    
    let name = result[0];
    let parent = result[1];
    
    $.post("methods.php?q=editrole", { id: id, name: name, parent:parent }).done(
    function(data) {
    	let res = jQuery.parseJSON(data);
    	if(res.success!=='ok')
    		return toast('Ошибка', res.err?res.err:'error', 'error');
    		
        $(`[group-name="${id}"]`).html( name );
    	
    	roles[index].name=name;
    	roles[index]['parent']=parent;
    	
        if(parent==0) parent='';
        if($(`[roleblock="${id}"]`).parent().attr('id')!='roleslist'+parent){
            $(`[roleblock="${id}"]`).prependTo( $('#roleslist'+parent) ); 
            if(parent!=0)  
            $('#roleslist'+parent).show(100);
            checkCollape();
        }

        appTable.update(table, "roleId", id, 'role', name); //обновляем категорию в таблице
    		
    });
}

async function addrole( ){
    promptmodcreate({'title':'Новая категория*','btnOk':'Сохранить','btnNo':'Отмена'},
    [{}]); 
    let result = await promptmod; if(!result) return;
    
    let name = result[0];
    
    $.post("methods.php?q=addrole", { name: name }).done(
    function(data) {
    	let res = jQuery.parseJSON(data);
    	if(res.success!=='ok')
    		return toast('Ошибка', res.err?res.err:'error', 'error');
        
    	roles.push({id:res.id,name:name});
        $('#roleslist').append( templaterole(res.id, name) );
    });
}


function checkCollape(){
    roles.forEach(function(it, i) {
        if($('#roleslist'+it.id).children().length==0){
            $('#collapse-btn-'+it.id).hide();
            $('#roleslist'+it.id).hide();
        } else {
            $('#collapse-btn-'+it.id).show();
        }
    });
}      
function getroles(){
    let c=roles.map(function(it) {
        return {text:it.name,value:it.id}
    });
    return c;
}

function childroles(scr){
    let a=[];
    roles.filter(t => t.parent===scr).forEach(function(it){
        a.push(it.id);
        a=a.concat(childroles(it.id));
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
  if($('#roleslist'+id).is(':visible'))$("#roleslist"+id).hide(100);else $("#roleslist"+id).show(100);
}

function templaterole( id, name ){
    return `<div class="list-group-item list-group-item-action" style="margin-bottom: 1px;" search="${name}" roleblock="${id}">
            <div class="row">
                <div class="col-sm" style="cursor:pointer" role-name="${id}" onclick="role='${id}';list();">${name}</div>
                <div class="col-auto p-0" style="display:none;" id="collapse-btn-${id}">
                    <button class="btn border-0 p-0 px-1" type="button" onclick="toggle('${id}')"> <i class="bi bi-chevron-expand"></i>  </button>
                </div>
                <div class="col-auto ps-0 pe-2">
                    <i class="bi bi-pen text-secondary" onclick="editrole('${id}')"></i>
                </div>
                <div class="col-auto ps-0">
                    <i class="bi bi-x-lg text-danger" onclick="deleterole('${id}')"></i>
                </div>
            </div>
            <div class="collapse mt-2 list-group" childrenlist id="roleslist${id}"></div>
        </div>`;
}




$(document).ready(function (){
    //startDate=moment("1986-09-19");
    //endDate=moment();
    role="";
    list();
});

function list(){
    $.post("methods.php?q=getList", { id:role }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');

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
    });
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
        {label:'role',items:getroles()}
    ]);
    let result = await promptmod; if(!result) return;
    $.post("methods.php?q=add", {
        name:result[0],
        body:result[1],
        files:result[2],
        role:result[3],
    }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        appTable.insert(table, res.data)
    });
}

async function rmove(id){
    alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;
    
    $.post("methods.php?q=remove", { id:id }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        appTable.rmove(table, "id", id)
    });
}

async function edit(id){
    let it=appTable.getitems(table, "id", id);
    if(it.length==0) return;
    promptmodcreate({'title':'Редактирование','btnOk':'Сохранить','btnNo':'Отмена'},
    [
         {label:"name", value:it[0].name},
         {label:"body", text:it[0].body},
         {files:it[0].files},
         {label:'role',value:it[0].roleId,items:getroles()}
    ]);
    let result = await promptmod; if(!result) return;
    $.post("methods.php?q=edit", {
        id:id,
        name:result[0],
        body:result[1],
        files:result[2],
        role:result[3],
    }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        appTable.update(table, "id", id, 'name', result[0]);
        appTable.update(table, "id", id, 'body', result[1]);
        appTable.update(table, "id", id, 'files', result[2]);
        appTable.update(table, "id", id, 'roleId', result[3]);
    });
}




function template( data ){console.log(data);
let i=getIndex('id',data.role_id,roles)
 let c=i!==false?roles[i]['name']:"";
 return `<tr class="rl">
             <td>${prep(data.line_number)}</td>
             <td>${prep(c)}</td>
             <td>${prep(data.controller)}</td>
             <td>${prep(data.actions)}</td>
             <td class="text-end">
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="edit('${prep(data.id)}')"><i class="bi bi-pencil"></i></button>
                 <button type="button" class="btn btn-outline-danger btn-sm" onclick="rmove('${prep(data.id)}')"><i class="bi bi-x-lg"></i></button>
             </td>
         </tr>`;
}
function prep(v){return v===undefined||v===false||v===NaN||v===null?'':v;}