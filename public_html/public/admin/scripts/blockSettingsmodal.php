<div class="modal fade" id="inputSettingsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="input-type" value="text">
          <label class="form-check-label">
            Текст
          </label>
        </div>
        <div class="row">
         <div class="col-auto">
            <div class="form-check mt-1">
              <input class="form-check-input" type="radio" name="input-type" value="number">
              <label class="form-check-label">
                Число
              </label>
            </div>
         </div>   
         <div class="col-auto">
            <div class="input-group input-group-sm">
              <span class="input-group-text">от</span>
              <input type="number" class="form-control" placeholder="0" style="max-width:80px" id="inputdiapasonFrom" oninput="if(this.value!==0) $(`[name='input-type'][value='number']`).prop('checked', true)">  
              <span class="input-group-text">до</span>
              <input type="number" class="form-control" placeholder="0" style="max-width:80px" id="inputdiapasonTo" oninput="if(this.value!==0) $(`[name='input-type'][value='number']`).prop('checked', true)">
            </div>
         </div>    
        </div>
        <hr>
        
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="dataRecord">
          <label class="form-check-label" for="dataRecord">
            Записать полученные данные в базу
          </label>
        </div>
        
        <hr>
        
<!--         
 
    <div class="form-check mt-1">
      <input class="form-check-input" type="checkbox" value="" id="inlineKeyboard">
      <label class="form-check-label" for="inlineKeyboard">
        Отправить клавиатуру с ответами
      </label>
    </div>
   
  
 -->
        <i>клавиатура на основе вариантов ответов</i>
        <div class="row mb-1">
        <div class="col-auto border-end">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="answersKeyboard" style="margin-top:5px" id="answersInlinekeyboard" value="answersInlinekeyboard">
              <label class="form-check-label" for="answersInlinekeyboard" >inline клавиатура</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="answersKeyboard" style="margin-top:5px" id="answersKeyboard" value="answersKeyboard">
              <label class="form-check-label" for="answersKeyboard">подвальная клавиатура</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="answersKeyboard" style="margin-top:5px" id="answersNokeyboard" value="answersNokeyboard">
              <label class="form-check-label" for="answersKeyboard">без клавиатуры</label>
            </div>
        </div>  
        <div class="col-auto">
         <div class="col-auto">
            <div class="input-group input-group-sm">
              <span class="input-group-text">колонок</span>
              <input type="number" min="1" max="8" class="form-control" placeholder="0" style="max-width:80px" id="inputinlineCols" oninput="$('#inlineKeyboard').prop('checked', true)">  
            </div>
         </div>
        </div>
        </div>
        
        
        <hr>
        
      </div>
      <div class="modal-footer p-1">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-sm" bid="" onclick="inputSavepar(this.getAttribute('bid'))" id="inptsv">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
    function inputSavepar(id){

        let par = {
             type: $('input[name="input-type"]:checked').val()
            ,from: $('#inputdiapasonFrom').val()
            ,to: $('#inputdiapasonTo').val()
            ,record: $('#dataRecord').is(':checked') ?1:0
            ,answersKeyboard: $('input[name="answersKeyboard"]:checked').val()
            ,inlineCols: $('#inputinlineCols').val()
        }
        
        $.post("p.php?q=saveBlockpar", { id:id, par: JSON.stringify(par) }).done(
        function(data) {
        	let res = jQuery.parseJSON(data);
        	if(res.success!=='ok')
        		return toast('Ошибка', res.err?res.err:'error', 'error');
            $('#inputSettingsModal').attr('onclick', "");
            $('#inputSettingsModal').modal('hide');
        });
    }
    
    function getBlock(id){
        $.post("p.php?q=getBlock", { id:id }).done(
        function(data) {
        	let res = jQuery.parseJSON(data);
        	if(res.success!=='ok')
        		return toast('Ошибка', res.err?res.err:'error', 'error');
        	let p = res.data.par;
            switch(res.data.type){
            
                case 'input':
                    $(`[name="answersKeyboard"][value="${p.answersKeyboard!=undefined ?p.answersKeyboard:'answersInlinekeyboard' }"]`).prop("checked", true);
                    $(`[name="input-type"][value="${p.type!=undefined ?p.type:'text' }"]`).prop("checked", true);
                    $('#inputdiapasonFrom').val(p.from!=undefined? p.from:0 );
                    $('#inputdiapasonTo').val(p.to!=undefined? p.to:0 );
                    $('#dataRecord').prop("checked", p.record===1? true:false );
                    $('#inputinlineCols').val(p.inlineCols!=undefined? p.inlineCols:1 );
                    $('#inptsv').attr('bid', id);
                    $('#inputSettingsModal').modal('show');
                break;
            }
        	
        });
    }
    
    
</script>






<div class="modal fade" id="keyboardModal" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
       <div class="modal-header">
          <h5 class="modal-title" id="asm-modal-mailin-title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
  
        <div class="px-3 py-2 border rounded mb-2"> 
            <span style="margin: 3px 3px 0 0;">Inline </span>
            <button type="button" class="btn btn-light btn-sm " onclick="kbinlineAdd()"><i class="bi bi-plus"></i> кнопка</button>
            <div class="p-2 border rounded mb-2" id="mess-inl-kb"> </div>  
        </div>  
          
          
        <div class="input-group input-group-sm mb-3" id="asm-poll-cols" >
          <span class="input-group-text">кнопок в ряд (до 8)</span>
          <input type="number" min="1" max="8" class="form-control" style="max-width:60px" id="kb-poll-cols-edit">
        </div>
          
          
       </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-outline-primary" bid="" onclick="keyboardSave(this.getAttribute('bid'))" id="kbsv">Сохранить</button>
      </div>
    </div>
 </div>
</div>

<script>
    function kbinlineAdd(vl1="",vl2=""){
        
        $('#mess-inl-kb').append(`<div class="input-group mb-1" inlkbrow> <input type="text" placeholder="Текст" class="form-control" text value="${vl1}"> <input type="text" placeholder="Значение" class="form-control" cb value="${vl2}">  </div>`);
    }
    
    function keyboardSave(id){

        let txt,cb,inl=[];
        $("[inlkbrow]").each(function(i,elem) {
            txt=$(elem).find('[text]').val();
            cb=$(elem).find('[cb]').val();
            if(txt!==""&&cb!=="")inl.push([txt,cb])
        });

        let par = {
             cols: $('#kb-poll-cols-edit').val()
            ,keys: inl
        }

        $.post("p.php?q=saveKeyboard", { id:id, keyboard: JSON.stringify(par) }).done(
        function(data) {
        	let res = jQuery.parseJSON(data);
        	if(res.success!=='ok')
        		return toast('Ошибка', res.err?res.err:'error', 'error');

            $('#keyboardModal').modal('hide');
        });
    }
    
    function getKeyboard(id){
        $.post("p.php?q=getBlock", { id:id }).done(
        function(data) {
        	let res = jQuery.parseJSON(data);
        	if(res.success!=='ok')
        		return toast('Ошибка', res.err?res.err:'error', 'error');
        	        
        	let kb = res.data.keyboard;

        	$('#mess-inl-kb').html("");
        	$('#kb-poll-cols-edit').val(kb.cols===undefined?1:kb.cols);
            if(kb.keys!==undefined){
                kb.keys.forEach(function(item, i) {
                	$('#mess-inl-kb').append(kbinlineAdd(item[0],item[1]));
                });
            }
        	
        	
            $('#kbsv').attr('bid', id);
            $('#keyboardModal').modal('show');
        });
    }
</script>





















