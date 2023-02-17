/**
 * <script src="/admin/mailing/mailingjs.js"></script>
 * <button class="btn btn-outline-secondary" style="font-weight: 600;" type="button" onclick="appSendmess.show()"><i class="bi bi-envelope"></i></button>
 * 
    function sendMess(){
        let chats = appTable.data( table ).map(function(it) {
          return it.id_chat;
        });
        appSendmess.send(chats); // отправляет сообщения
        appSendmess.send(); // возвращает данные сообщения
    }
 */

var promptmodModalElement = document.createElement("div");
promptmodModalElement.innerHTML = `<div class="modal fade" id="asm-modal-mailing" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
       <div class="modal-header">
          <h5 class="modal-title" id="asm-modal-mailin-title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
       
        <div class="input-group mb-1">
          <input type="text" class="form-control" placeholder="название" id="asm-name">
        </div>
        
        <div class="input-group mb-2">
          <span class="input-group-text" id="basic-addon1">Дата и время начала</span>
          <input type="datetime-local" class="form-control" id="asm-date">
          
          <!--<button class="btn btn-outline-secondary" type="button" onclick="$('#asm-settings').show(100)"><i class="bi bi-sliders"></i></button>-->
        </div>
        <!--
        <div id="asm-settings" class="border mb-2 px-3 pt-1 pb-2">
            <i>дополнительные условия даты рассылки</i>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="asmrepeat" id="asmrepeat1" value="">
              <label class="form-check-label" for="asmrepeat1">
                Не применять
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="asmrepeat" id="asmrepeat2" value="daily">
              <label class="form-check-label" for="asmrepeat2">
                Ежедневно в это время
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="asmrepeat" id="asmrepeat3" value="monthly">
              <label class="form-check-label" for="asmrepeat3">
                Ежемесячно в это число в это время
              </label>
            </div>
            
            <div class="form-check">
              <input class="form-check-input" type="radio" name="asmrepeat" id="asmrepeat4" value="weekly">
              <label class="form-check-label" for="asmrepeat4">
                Ежедневно в этот день недели в это время
              </label>
            </div>
        </div>
        -->
        <div class="form-floating mb-2">
          <textarea class="form-control" placeholder="Введите текст сообщения" id="asm-mess-text" style="height: 100px"></textarea>
          <label for="asm-mess-text">Сообщение</label>
        </div>
        
        <div class="mb-2" id='asm-upload-form'>
            
        </div>
        
        <div id="asm-kb-area">
            <div class="px-3 py-2 border rounded mb-2"> 
                <div class="row">
                    <div class="col">
                        <span style="margin: 3px 3px 0 0;">Опрос </span>
                        <button type="button" class="btn btn-light btn-sm " onclick="appSendmess.polAdd()"><i class="bi bi-plus"></i> вариант</button>
                    </div>
                    <div class="col-auto">
                    <div class="form-check" id="asm-form-poll-ds">
                      <input class="form-check-input" type="checkbox" value="" id="asm-poll-ds">
                      <label class="form-check-label" for="asm-poll-ds">
                        Разрешать снимать голос
                      </label>
                    </div>
                    </div>
                </div>
    
                <div id="asm-poll-area" class="mt-1">
                </div>
                <div class="input-group input-group-sm mb-3" id="asm-poll-cols" style="display:none">
                  <span class="input-group-text">кнопок в ряд (до 8)</span>
                  <input type="number" min="1" max="8" class="form-control" style="max-width:60px" id="asm-poll-cols-edit">
                </div>
            </div>   
              
              
            <div class="px-3 py-2 border rounded mb-2"> 
                <span style="margin: 3px 3px 0 0;">Inline </span>
                <button type="button" class="btn btn-light btn-sm " onclick="appSendmess.inlineAdd()"><i class="bi bi-plus"></i> кнопка</button>
                <div class="p-2 border rounded mb-2" id="asm-mess-inl-kb"> 
                    
                </div>  
            </div>  
        </div>  
       </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-outline-primary" id="appSendmess.send()" onclick="sendMess()">Отправить</button>
      </div>
    </div>
 </div>
</div>`;
document.currentScript.parentNode.appendChild(promptmodModalElement);

function smUpdateFilegroup(id, group){
    appSendmess.fg(group);
}

var appSendmess = (function($) {
    var filegroup, repeat, filter, id;
    function show(par={}){
        

         $.post("/admin/upload/getfiles.php", { group:par.files }).done(
        function(data) {
        	let res = jQuery.parseJSON(data);
        	if(res.success!=='ok')
        		return toast('Ошибка', res.err?res.err:'error', 'error');
        
            let form=appUpload.form({
                 id:1
                ,group:par.files 
                ,uploadFunc:'smUpdateFilegroup'
                ,deleteFunc:'smUpdateFilegroup'
            })
            
            let container = appUpload.container({
                 id:1
                ,files:res.data
            })
            
            $('#asm-upload-form').html(form+container);
        });
        
        filegroup=par.files;
        repeat = par.repeat;
        filter = par.filter;
        id = par.id;
        $('#asm-modal-mailin-title').html("Рассылка");

        
        
        if(par.name===undefined){
            $('#asm-name').val("");
            $('#asm-name').hide();
        } else {
            $('#asm-name').val(par.name);
            $('#asm-name').show();
        }
        
        $('#asm-poll-system').prop("checked", false);
        $('#asm-poll-ds').prop("checked", false);
        $('#poll-cols-edit').val(1);
        $('#asm-c-poll-system').show();
        $('#asm-poll-cols').hide();
        $('#asm-poll-area').html('');
        
        $('#asm-mess-text').val(par.text===undefined?"":par.text);
        
        $('#imagespanel').html('');
        $('#asm-settings').hide();
        //$('[name="asmrepeat"][value=""]').prop('checked', true);
        
        $('#file-label').html('Загрузить файл');
        $('#asm-mess-inl-kb').html('');
        $('#asm-date').val(par.date!==undefined&&par.date!==""? moment(par.date).format('YYYY-MM-DDTHH:mm'): moment().format('YYYY-MM-DDTHH:mm'));
        
        $('#asm-kb-area').show();
        if(par.kb===false) $('#asm-kb-area').hide();
        
        
        $('#asm-modal-mailing').modal('show');
    }
    
    function fg(f){
        filegroup=f;
    }
    
    function inlineAdd(vl1="",vl2=""){
        
        $('#asm-mess-inl-kb').append(`<div class="input-group mb-1" asminlkbrow> <input type="text" placeholder="Текст" class="form-control" text value="${vl1}"> <input type="text" placeholder="Значение" class="form-control" cb value="${vl2}">  </div>`);
    }
	
    function polAdd(val=""){
        if(!$('#asm-poll-system').is(':checked') && $("[asm-poll-item]").length>0) $('#asm-poll-cols').show(100); else $('#asm-poll-cols').hide(100);
        $(`<input type='text' class='form-control mb-1' placeholder='Текст' asm-poll-item value="${val}">`).appendTo('#asm-poll-area').show('slow');
    }


    function send(chats=false){
        let t=$('#asm-mess-text').val(),
            poll_data=[]
            ds = $('#asm-poll-ds').is(':checked') ?1:0,
            plcol=$('#asm-poll-cols-edit').val(),
            date=$('#asm-date').val();
            
        //let repeat = $('[name="asmrepeat"]:checked').val();   
            
        $("[asm-poll-item]").each(function(i,elem) {
            if($(elem).val()!=="") poll_data.push($(elem).val());
        });
        
        let txt,cb,inl=[];
        $("[asminlkbrow]").each(function(i,elem) {
            txt=$(elem).find('[text]').val();
            cb=$(elem).find('[cb]').val();
            if(txt!==""&&cb!=="")inl.push([txt,cb])
        });
        
        if(t=="" && (filegroup===undefined||filegroup===""||filegroup===false||filegroup===0||filegroup==="0")) return toast('Ошибка', 'Напишите сообщение или приложите файлы', 'warning');
        
        let pr={ d:JSON.stringify(chats),t:t,inline:JSON.stringify(inl),poll_data:JSON.stringify(poll_data),ds:ds,plcol:plcol,filegroup:filegroup,date:date,repeat:repeat,filter:filter,name:$('#asm-name').val(),id:id };
        if(chats===false){
            $('#asm-modal-mailing').modal('hide');
            return pr;
        }
        if(chats.length===0) return toast('Ошибка', 'Список чатов пуст', 'warning');

        $.post("/admin/mailing/appsend.php", pr).done(function(data) {
        var res = jQuery.parseJSON(data);
        if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
            $('#asm-modal-mailing').modal('hide');
        });

    }
    
    return {
        show:show, send:send, fg:fg, inlineAdd:inlineAdd, polAdd:polAdd
    }
    
})(jQuery);