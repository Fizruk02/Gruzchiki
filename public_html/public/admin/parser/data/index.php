
<div class="container mt-2" tableform>
   <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
      <table class="table table-hover table-sm" id="table-parser">
         <thead class="thead-dark">
            <tr>
                  <th style="width: 100px;">id</th>
                  <th style="min-width: 100px;">name</th>
                  <th style="width:78px"></th>
            </tr>
         </thead>
      </table>
   </div>
</div>


<script>
var table = 'table-parser';
$(document).ready(function (){
    list("");
});

function list(group){
    $.post("post/getList", { group:group }).done(function(data) {
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
    [{label:'name'}]); 
    let result = await promptmod; if(!result) return;
    $.post("post/add", { name:result[0] }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        appTable.insert(table, res.data)
    });
}

async function rmove(id){
    alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;
    
    $.post("post/remove", { id:id }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        appTable.rmove(table, "id", id)
    });
}

async function edit(id){
    let it=appTable.getitems(table, "id", id);
    if(it.length==0) return;
    promptmodcreate({'title':'Редактирование','btnOk':'Сохранить','btnNo':'Отмена','size':'xl'},
    [{label:'name',value:it[0].name},{label:'code',text:it[0].code,height:500}]); 
    let result = await promptmod; if(!result) return;
    $.post("post/edit", { id:id,name:result[0],code:result[1] }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
        appTable.update(table, "id", id, 'name', result[0]);
        appTable.update(table, "id", id, 'code', result[1]);
    });

    
}


function template( data ){
 return `<tr class="rl">
             <td>${data.id}</td>
             <td>${data.name}</td>
             <td>
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="edit('${data.id}')"><i class="bi bi-pencil"></i></button>
                 <button type="button" class="btn btn-outline-danger btn-sm" onclick="rmove('${data.id}')"><i class="bi bi-x-lg"></i></button>
             </td>
         </tr>`;
}
</script>

