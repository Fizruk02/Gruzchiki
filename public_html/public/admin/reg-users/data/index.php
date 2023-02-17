<?php
$roles = json_encode([['text'=>'superadmin','value'=>"100"],['text'=>'admin','value'=>"90"],['text'=>'сотрудник','value'=>"50",'default'=>1]]);
$roles = json_encode(arrayQuery('SELECT title as text, role_id as `value` FROM `us_roles` ORDER BY name'));
?>
<div class="container mt-2" tableform>
   <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
      <table class="table table-hover table-sm" id="table-users">
         <thead class="thead-dark">
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 140px;">id</th>
                <th style="width: 140px;">name</th>
                <th style="min-width: 100px;">first name / username</th>
                <th style="width: 140px;">role</th>
                <th style="width: 140px;">login</th>
                <th style="width:120px"></th>
            </tr>
         </thead>
      </table>
   </div>
</div>


<div class="modal fade" id="modal-mailing" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Сообщение</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">


                <div class="form-floating mb-2">
                    <textarea class="form-control" placeholder="Введите текст сообщения" id="mess-text" style="height: 100px"></textarea>
                    <label for="mess-text">Текст сообщения</label>
                </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary" onclick="sendMess()">Отправить</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modal-roles" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Список ролей</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="container mt-2" tableform>
                   <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
                      <table class="table table-hover table-sm" id="table_role">
                         <thead class="thead-dark">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>slug</th>
                                <th>name</th>
                                <th style="width: 140px;">id</th>
                                <th style="width:80px"></th>
                            </tr>
                         </thead>
                      </table>
                   </div>
                </div>
     

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary" onclick="sendMess()">Отправить</button>
            </div>
        </div>
    </div>
</div>


<script>
var table = 'table-users';

$(document).ready(function (){
    list("");
});

var roles = {
    data:<?=$roles?>,
    table:"table_role",
    show:()=> {
        qw.modal("#modal-roles").show();
        
        qw.post("methods.php?q=getRolesList", {},r=> {
            appTable.init({
                 table:roles.table
                ,list:r.data
                ,template:'template_role'
                ,listStart:0
                ,search:''
                ,limit:10
                ,header:{
                    buttons:[
                        '<button class="btn btn-outline-secondary" type="button" onclick="roles.add()">Добавить</button>'
                    ]
                }
            });
        },"json","list of roles");
        
    },
    edit: async(id)=> {
        let it=appTable.getitems(roles.table, "id", id);
        if(it.length==0) return;
        promptmodcreate({"title":"Редактировать роль","btnOk":"Сохранить","btnNo":"Отмена"},
            [{label:"Имя", value:it[0].title},
            {label:"slug (не обязательно)", value:it[0].name},
            {label:"id",type:"number", value:it[0].role_id},
        ]); 
        let result = await promptmod; if(!result) return;
        qw.post("methods.php?q=editRole", { id:id,name:result[0],slug:result[1],role_id:result[2] }, r=> {
            roles.data=r.roles
            appTable.update(roles.table, "id", id, 'title', result[0]);
            appTable.update(roles.table, "id", id, 'name', result[1]);
            appTable.update(roles.table, "id", id, 'role_id', result[2]);
        },"json","edit role");
    },
    rm: async(id)=> {
        alertmodcreate({'title':'Удалить роль?', 'btnOk':'Да','btnNo':'Отмена'});
        let result = await alertmod; if(!result) return;
        qw.post("methods.php?q=removeRole", { id:id }, r=> {
            roles.data=r.roles
            appTable.rmove(roles.table, "id", id)
        },"json","delete role");
    },
    add: async()=> {
        promptmodcreate({"title":"Новая роль*","btnOk":"Сохранить","btnNo":"Отмена"},
            [{label:"Имя"},
            {label:"slug (не обязательно)"},
            {label:"id",type:"number"},
        ]); 
        let result = await promptmod; if(!result) return;
        qw.post("methods.php?q=addRole", { name:result[0],slug:result[1],role_id:result[2] }, r=> {
            roles.data=r.roles
            appTable.insert(roles.table, r.data)
        },"json","new role");
    }
}


function template_role( data ){
 return `<tr class="rl">
             <td>${data.line_number}</td>
             <td>${prep(data.title)}</td>
             <td>${prep(data.name)}</td>
             <td>${prep(data.role_id)}</td>
             <td class="text-end">
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="roles.edit('${prep(data.id)}')"><i class="bi bi-pencil"></i></button>
                 <button type="button" class="btn btn-outline-danger btn-sm" onclick="roles.rm('${prep(data.id)}')"><i class="bi bi-x-lg"></i></button>
             </td>
         </tr>`;
}


function list(group){
    $.post("methods.php?q=getList", { group:group }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');

    appTable.init({
         table:table
        ,list:res.data
        ,template:'template'
        ,listStart:0
        ,search:''
        ,limit:10
        ,header:{
            buttons:[
                '<button class="btn btn-outline-secondary" type="button" onclick="roles.show()"><i class="bi bi-filter"></i> Роли</button>'
            ]
        }
    });
    });
}

async function mess(id){
    let it=appTable.getitems(table, "id_chat", id);
    promptmodcreate({'title':"Send a message to the user «"+it[0].first_name+"»",'btnOk':'Отправить','btnNo':'Отмена'},
    [
        {label:"text message", text:""}
    ]);
    let result = await promptmod; if(!result) return;
    $.post("methods.php?q=mess", { id_chat:id, text:result[0] }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        return toast("Сообщение", "Сообщение пользователю «"+it[0].first_name+"» отправлено");
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
    [{label:'name',value:it[0].name},
    {label:'login',value:it[0].login},
    {html:"<i style='font-size: 9pt;'>в целях безопасности пароль не хранится на сервере, сохраните его у себя. При редактировании, если поле пароля пустое, то он остается прежним</i>"},
    {label:'pass',placeholder:it[0].pass==""?"":"***"},
    {label:'role',value:it[0].role_id,items:roles.data }
    ]); 
    let result = await promptmod; if(!result) return;
    $.post("methods.php?q=edit", { id:id,name:result[0],login:result[1],pass:result[2],role_id:result[3] }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        toast('Редактирование пользователя', 'Данные сохранены');
        appTable.update(table, "id", id, 'name', result[0]);
        appTable.update(table, "id", id, 'login', result[1]);
        appTable.update(table, "id", id, 'pass', result[2]);
        appTable.update(table, "id", id, 'role_id', result[3]);
    });
}

function template( data ){
 let role='';
 roles.data.forEach(function(it) {
	if(data.role_id==it.value) role=it.text;
});
 return `<tr class="rl">
             <td>${data.line_number}</td>
             <td>${prep(data.id_chat)}</td>
             <td>${prep(data.name)}</td>
             <td>${prep(data.first_name+(data.username===""?"":" / "+data.username))}</td>
             <td>${role}</td>
             <td>${prep(data.login)}</td>
             <td class="text-end">
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="mess('${prep(data.id_chat)}')"> <i class="bi bi-envelope"></i></button>
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="edit('${prep(data.id)}')"><i class="bi bi-pencil"></i></button>
                 <button type="button" class="btn btn-outline-danger btn-sm" onclick="rmove('${prep(data.id)}')"><i class="bi bi-x-lg"></i></button>
             </td>
         </tr>`;
}
function prep(v){return v===undefined||v===false||v===NaN||v===null?'':v;}
</script>

