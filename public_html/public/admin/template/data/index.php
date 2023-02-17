
<div class="container mt-2" tableform>
   <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
      <table class="table table-hover table-sm" id="table-template">
         <thead class="thead-dark">
            <tr>
                  <th style="width: 60px;">#</th>
                  <th style="min-width: 100px;">id</th>
                  <th style="min-width: 100px;">username</th>
                  <th style="min-width: 100px;">first name</th>
                  <th style="width:78px"></th>
            </tr>
         </thead>
      </table>
   </div>
</div>


<script>
var table = 'table-template';
$(document).ready(function (){
    list("");
});

function list(group){
    $.post("p.php?q=getList", { group:group }).done(function(data) {
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
                '<button class="btn btn-outline-secondary" type="button" onclick="add()">Добавить</button>'
            ]
        }
    });
    });
}

async function add(){
    promptmodcreate({'title':'Добавление','btnOk':'Сохранить','btnNo':'Отмена'},
    [{label:'username'},{label:'first name'}]); 
    let result = await promptmod; if(!result) return;
    $.post("p.php?q=add", { username:result[0],first_name:result[1] }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        appTable.insert(table, res.data)
    });
}

async function rmove(id){
    alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;
    
    $.post("p.php?q=remove", { id:id }).done(function(data) {
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
        {label:'username',value:it[0].username},
        {label:'first name',value:it[0].first_name}
    ]); 
    let result = await promptmod; if(!result) return;
    $.post("p.php?q=edit", { id:id,username:result[0],first_name:result[1] }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        appTable.update(table, "id", id, 'username', result[0]);
        appTable.update(table, "id", id, 'first_name', result[1]);
    });

    
}


function template( data ){
 return `<tr class="rl">
             <th>${data.line_number}</th>
             <td>${prep(data.chat_id)}</td>
             <td>${prep(data.username)}</td>
             <td>${prep(data.first_name)}</td>
             <td class="text-end">
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="edit('${prep(data.id)}')"><i class="bi bi-pencil"></i></button>
                 <button type="button" class="btn btn-outline-danger btn-sm" onclick="rmove('${prep(data.id)}')"><i class="bi bi-x-lg"></i></button>
             </td>
         </tr>`;
}
function prep(v){return v===undefined||v===false||v===NaN||v===null?'':v;}
</script>

