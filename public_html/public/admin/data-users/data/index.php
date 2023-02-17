
<div class="container mt-2" tableform>
    <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
        <table class="table table-hover table-sm" id="table-template">
            <thead class="thead-dark">
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 140px;">date</th>
                <th style="width: 140px;">id</th>
                <th style="min-width: 100px;">first name / username</th>
                <th style="width:180px"></th>
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

<script>
    var table = 'table-template';
    $(document).ready(function (){
        list("");
    });

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
                //,header:{
                //    buttons:[
                //        '<button class="btn btn-outline-secondary" type="button" onclick="add()">Добавить</button>'
                //    ]
                //}
            });
        });
    }

    async function mess(id){
        let it=appTable.getitems(table, "chat_id", id);
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

    async function ban(id){
        promptmodcreate({'title':'Бан','btnOk':'Сохранить','btnNo':'Отмена'},
            [{label:'Срок бана в часах',value:24,type:"number"},{label:'Причина'},{label:'Уведомить пользователя',checkbox:true}]);
        let result = await promptmod; if(!result) return;
        $.post("methods.php?q=ban", { id:id,term:result[0],comment:result[1],notification:result[2] }, function(res) {
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            appTable.update(table, "chat_id", id, 'banned', "1");
        },"json");
    }
    function unban(id){
        $.post("methods.php?q=unban", { id:id }, function(res) {
            if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            appTable.update(table, "chat_id", id, 'banned', "0");
        },"json");
    }
    async function edit(id){
        let it=appTable.getitems(table, "id", id);
        if(it.length===0) return;
        promptmodcreate({'title':'Редактирование','btnOk':'Сохранить','btnNo':'Отмена'},
            [{label:'username',value:it[0].username},{label:'first name',value:it[0].first_name}]);
        let result = await promptmod; if(!result) return;
        $.post("methods.php?q=edit", { id:id,username:result[0],first_name:result[1] }).done(function(data) {
            var res = jQuery.parseJSON(data);
            if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
            appTable.update(table, "id", id, 'username', result[0]);
            appTable.update(table, "id", id, 'first_name', result[1]);
        });
    }

    function template( data ){
        let ban="";
        if(data.ban===1){
            ban=data.banned==="1"?`<button type="button" class="btn btn-danger btn-sm" onclick="unban('${prep(data.chat_id)}')"> разбанить</button>`:
                `<button type="button" class="btn btn-outline-danger btn-sm" onclick="ban('${prep(data.chat_id)}')"> забанить</button>`;
        }
        return `<tr class="rl">
             <td>${data.line_number}</td>
             <td>${prep(data.f_date)}</td>
             <td>${prep(data.chat_id)}</td>
             <td>${prep(data.first_name+(data.username===""?"":" / "+data.username))}</td>
             <td class="text-end">
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="mess('${prep(data.chat_id)}')"> <i class="bi bi-envelope"></i></button>
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="edit('${prep(data.id)}')"><i class="bi bi-pencil"></i></button>
                 ${ban}
                 <!--<button type="button" class="btn btn-outline-danger btn-sm" onclick="rmove('${prep(data.id)}')"><i class="bi bi-x-lg"></i></button>-->
             </td>
         </tr>`;
    }
    function prep(v){return v===undefined||v===false||v===NaN||v===null?'':v;}
</script>

