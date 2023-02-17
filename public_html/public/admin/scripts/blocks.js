        $(function() {
        	$('#blocks-container').sortable({
        	    containment: "#blocks-container",
        	    handle: "[blockheader]",
                stop: function() {
                    let blocks = [];
                    $("#blocks-container").children().each(function(i,elem) {
                    	blocks.push($(elem).attr('block'));
                    });
                    
                    $.post("p.php?q=sortBlock", { blocks: JSON.stringify(blocks) }).done(
                    function(data) {
                    	let res = jQuery.parseJSON(data);
                    	if(res.success!=='ok')
                    		return toast('Ошибка', res.err?res.err:'error', 'error');
                    });
                    
                }
        	});

        });

        var codemirrorList = {};
        var supselectVar = false;
        
        function codemirror(id){
            codemirrorList[id] = CodeMirror.fromTextArea(document.getElementById(`block-text-${id}`), {
                styleActiveLine: true,
                lineWrapping: true,
            	matchBrackets: true,
            	mode: "application/x-httpd-php",
            });
            
        }
        
        async function addBlock(type){
            let name='';
            switch(type){
                case 'message': name='Сообщение'; break;
                case 'input': name='Ввод текста'; break;
                case 'func': name='Функция'; break;
                case 'operator': name='Оператор'; break;
                case 'goto': name='Переход'; break;
                case 'faq': name='Справка'; break;
            }
            promptmodcreate({'title':'Название блока','btnOk':'Ok','btnNo':'Отмена'},
            [{label:'', value:name}]);
            let result = await promptmod; if(!result || result[0]==='' ) return;
            b2.spinner();
            $.post("p.php?q=addBlock", {
                 type:type
                ,name:result[0]
                ,id_script:script
                ,num: $('#blocks-container').children().length
            }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
            	blocks[res.data.id]=res.data;console.log(blocks);
               switch(res.data.type){
                    case 'message':
                        $('#blocks-container').append( templateBlockmessage( res.data ) );
                    break;
                    
                    case 'input':
                        $('#blocks-container').append( templateBlockinput( res.data ) );
                    break;
                    
                    case 'inputFiles':
                        $('#blocks-container').append( templateInputfiles( res.data ) );
                    break;
                    
                    case 'func':
                        $('#blocks-container').append( templateBlockfunction( res.data ) );
                        codemirror(res.data.id);
                    break;

                   case 'goto':
                       $('#blocks-container').append( templateBlockgoto( res.data ) );
                   break;

                    case 'mainBlockchain':
                        $('#blocks-container').append( templateBlockmainblockchain( res.data ) );
                    break;
                    
                    case 'gotoBlock':
                        $('#blocks-container').append( templateBlockgotoblock( res.data ) );
                    break;
               }
                
            });
        }
        
        
        
        async function deleteBlock( id, type ){
            alertmodcreate({'title':'Удалить блок?', 'btnOk':'Ok','btnNo':'Отмена'});
            let result = await alertmod; if(!result) return;
            b2.spinner();
            $.post("p.php?q=deleteBlock", { id:id }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
            
            	$(`[block="${id}"]`).remove();
            });
            
        }
        
        
        function saveBlock( id, type ){
            let par = {};
            let answers = [];
            let text = '';
            
            switch(type){
                case 'input':
                    $(`[ansers-variants="${id}"]`).children().each(function(i,elem) {
                    	answers.push({
                    	     answer: $(elem).find('[answer]').val()
                    	    ,action: $(elem).find('[action]').attr('action')
                    	    ,actionblock: $(elem).find('[action]').attr('actionblock')
                    	    ,status: $(elem).find('[statusVariant]').is(':checked') ?1:0
                    	});
                    });
                    text = $('#block-text-'+id).val();
                break;
                
                case 'message':
                    let arr = [];
                    $(`[messagetext="${id}"]`).each(function(i,elem) {
                        if(elem.value!=='')
                    	    arr.push(elem.value);
                    });
                    
                    
                    text = arr.join('||');
                break;
             
                case 'inputFiles':
                    text = $('#block-text-'+id).val();
                break;

                case 'goto':
                    text = $('#block-text-'+id).val();
                break;

                case 'func':
                    text = codemirrorList[id].getValue();
                break;
            }
            b2.spinner();
            $.post("p.php?q=saveBlock", {
                 id:id
                ,par:JSON.stringify( par )
                ,answers:JSON.stringify( answers )
                ,text:text
            }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
               
               toast('Сохранение', 'Блок сохранён');
                
            });
        }
        
        function sendBlocksetting( id, par, val ){
            b2.spinner();
            $.post("p.php?q=sendBlocksetting", {
                 id:id
                ,par:par
                ,val:val
            }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
               
                
            });
        }
        
        
        
        
        
        
        function templateInputfiles( par ){
            let body = '';
            return `
            <div class="card move-card mb-1" block="${par.id}">
            ${templateBlockheader( par, 'inputFiles', '<i class="bi bi-images"></i>' )}
                <div class="card-body collapse show"  id="block-${par.id}">
                    <div class="form-floating mb-2">
                      <textarea class="form-control" placeholder="Сообщение" id="block-text-${par.id}" style="height: 62px">${par.text!==undefined? par.text:'' }</textarea>
                      <label>Текст</label>
                    </div>
                </div> 
            </div>`;
        }

        function templateBlockgoto( par ){
            let c = par.caption===""||par.caption===undefined?"<i class='bi bi-plus'></i> Выбрать скрипт":par.caption;
            return `
    <div class="card move-card mb-1" block="${par.id}">

      <h5 class="card-header pr-1 pl-1" blockheader>
        <div class="row">
            <div class="col-sm">
                <button class="btn btn-outline-secondary btn-sm" id="btn-goto-${par.id}" onclick="blockId=${par.id};scriptListmodal('${par.id}','goto')">${c}</button>
            </div>
            <div class="col-auto d-flex">
                <div class="form-check form-switch" style="margin-top: -2px;">
                  <input class="form-check-input" type="checkbox" id="activate-block" onclick="sendBlocksetting(${par.id}, 'activate', $(this).is(':checked')? 1:0 )" ${par.activate==1? 'checked':'' }>
                  <label class="form-check-label" for="activate-block"></label>
                </div>
                <a href="#" onclick="$('#block-${par.id}').collapse('toggle');"><i class="bi bi-chevron-expand"></i></a>
                <a href="#" onclick="saveBlock(${par.id}, '${par.type}')"><i class="bi bi-check"></i></a>
                <a href="#" onclick="deleteBlock(${par.id})"><i class="bi bi-x text-danger"></i></a>
            </div>
            <div class="col-auto p-0" onclick="supselect(${par.id_script}, ${par.id})" supselect style="display:none">
               <button class="btn btn-outline-secondary border-0 p-0 px-1" type="button"><i class="bi bi-hand-index-thumb-fill"></i></button>
            </div>
            
        </div>
        
        
      </h5>


      <div class="card-body collapse"  id="block-${par.id}">
         
      </div>
      
   </div>`;
        }
        
        function templateBlockmessage( par ){
            let body = '';
            if(par.text===undefined || par.text.length<=1)
            body = `<div class="form-floating mb-2">
              <textarea class="form-control" placeholder="Сообщение" messagetext=${par.id} style="height: 100px">${par.text!==undefined? par.text[0]:'' }</textarea>
              <label>Текст</label>
            </div>`;
            if(par.text.length>1)
                par.text.forEach(function(item, i) {
                	body += templateMessagevariant(par.id, item, i);
                });
            

            return `
            <div class="card move-card mb-1" block="${par.id}">
              ${templateBlockheader( par, 'message', '<i class="bi bi-chat-dots"></i>' )}
              <div class="card-body collapse show"  id="block-${par.id}">
                 <div messages-variants="${par.id}">
                 ${body}
                 </div>
                 <div style="display:inline-flex">
                 <button class="btn btn-outline-secondary btn-sm me-1" type="submit" onclick="$('[messages-variants=${par.id}]').append( templateMessagevariant(${par.id}) ) "><i class="bi bi-plus"></i> Вариант для рандома</button>
                 ${appUpload.form({ id:par.id, uploadFunc:'updateFilegroup', deleteFunc:'updateFilegroup', 'files':par.files, group:par.group })}
                 </div>
                 ${appUpload.container({ id:par.id,  'files':par.files})}
              </div>
              
           </div>`;
        }
        
        function updateFilegroup(id, group){
            blockSettingset("files",group,id);
        }
        

        
        
        function templateMessagevariant(id, text='', num=0){
            
            return `<div class="input-group mb-1">
                      <textarea class="form-control" messagetext=${id}>${text}</textarea>
                      <button class="btn btn-warning" type="submit" style="display:none" onclick="$(this).closest('.input-group').remove()" answerdelete=${id+"_"+num}>Да</button>
                      <button class="btn btn-outline-secondary" type="submit" style="display:none" onclick="$('[answerdelete=${id+"_"+num}]').hide();$('[answerdeletestart=${id+"_"+num}]').show()" answerdelete=${id+"_"+num}>Нет</button>
                      <button class="btn btn-outline-secondary" type="button" onclick="$(this).hide();$('[answerdelete=${id+"_"+num}]').show()" answerdeletestart=${id+"_"+num}><i class="bi bi-x text-danger"></i></button>
                    </div>`;
        } 
        
        
        
        
        function templateBlockinput( par ){
            
            let answers = '';
            
            if(par.answers!==undefined)
            par.answers.forEach(function(item, i) {
            	answers+= templateAnswervariant(item.answer, item.action, item.actionblock, item.actionName, item.actionBlockname, item.status);
            });

            return `
            <div class="card move-card mb-1" block="${par.id}">
              ${templateBlockheader( par, 'input', '<i class="bi bi-textarea-t"></i>' )}
              <div class="card-body collapse show" id="block-${par.id}">
                 
                <div class="form-floating mb-2">
                  <textarea class="form-control" placeholder="Сообщение" id="block-text-${par.id}" style="height: 62px">${par.text!==undefined? par.text:'' }</textarea>
                  <label>Текст</label>
                </div>
                <div ansers-variants="${par.id}">
                    ${answers}
                </div>
                
                <button class="btn btn-outline-secondary btn-sm" type="submit" onclick="$('[ansers-variants=${par.id}]').append( templateAnswervariant() ) "><i class="bi bi-plus"></i> Вариант ответа</button>
                 <div style="display:inline-flex">
                    ${appUpload.form({ id:par.id, uploadFunc:'updateFilegroup', deleteFunc:'updateFilegroup', 'files':par.files, group:par.group })}
                 </div>
                 ${appUpload.container({ id:par.id,  'files':par.files})}
                
              </div>
           </div>`;
        }
        
        
        function templateAnswervariant(answer='', action='', actionblock='', actionName='Продолжить', actionBlockname = '', status=0){
            let uniqId = 'variant_'+Math.round(Math.random()*10000000000);
            if(actionBlockname!=='')
                actionName += ' / '+actionBlockname;
                
            return `<div class="input-group mb-1">
            
                      <input type="text" class="form-control" placeholder="Вариант ответа" answer value="${answer}">
                      <button class="btn btn-outline-secondary" type="button" id="${uniqId}" action="${action}" actionblock="${actionblock}" onclick="scriptListmodal('${uniqId}', 'input')">${actionName}</button>
                    <!--  <button class="btn btn-outline-secondary px-1" type="button" onclick="selSupselect('${uniqId}')" selSupselect="${uniqId}"><i class="bi bi-hand-index-thumb"></i></button>
                      <div class="input-group-prepend">
                        <div class="input-group-text border-secondary" style="background: white;border-right: 0px !IMPORTANT;padding-right: 30px;">
                          <input type="checkbox" class="form-check-input m-0 border-secondary" statusVariant ${status==1?'checked':''}>
                        </div>
                      </div>  --> 
                      <button class="btn btn-warning" type="submit" style="display:none" onclick="$(this).closest('.input-group').remove()" answerdelete=${uniqId}>Да</button>
                      <button class="btn btn-outline-secondary" type="submit" style="display:none" onclick="$('[answerdelete=${uniqId}]').hide();$('[answerdeletestart=${uniqId}]').show()" answerdelete=${uniqId}>Нет</button>
                      <button class="btn btn-outline-secondary" type="button" onclick="$(this).hide();$('[answerdelete=${uniqId}]').show()" answerdeletestart=${uniqId}><i class="bi bi-x text-danger"></i></button>
                    </div>`;
        }
        
               
        function templateBlockfunction( par ){
            return `
            <div class="card move-card mb-1" block="${par.id}">
                ${templateBlockheader( par, 'func', '<i class="bi bi-code-square"></i>' )}
              <div class="card-body collapse show"  id="block-${par.id}">
                    <textarea id="block-text-${par.id}" name="code" rows="5">${par.text!==undefined? par.text:'' }</textarea>
              </div>
           </div>`;
        }
        
        
        
        
        function templateBlockmainblockchain( par ){
            par['nosave']=true;
            let b='<option value="">Выберите сценарий</option>';
            blockchains.forEach(function(item, i) {
            	b+= `<option value="${item.techname}" ${par.par!==undefined&&par.par.techname!==undefined&&item.techname==par.par.techname?'selected':''}>${item.name}</option>`;
            });
            return `
            <div class="card move-card mb-1" block="${par.id}">
                ${templateBlockheader( par, 'mainBlockchain', '<i class="bi bi-box-arrow-left"></i>' )}
              <div class="card-body"  id="block-${par.id}">
                <div class="form-floating">
                  <select class="form-select" onchange="blockSettingset('techname',this.value,${par.id})">
                    ${b}
                  </select>
                  <label for="floatingSelect">Скрипт в основном боте</label>
                </div>
              </div>
           </div>`;
        }
        
        
        function templateBlockgotoblock( par ){
            par['nosave']=true;
            let b='<option value="">Выберите сценарий</option>';
            blockchains.forEach(function(item, i) {
            	b+= `<option value="${item.techname}" ${par.par!==undefined&&par.par.techname!==undefined&&item.techname==par.par.techname?'selected':''}>${item.name}</option>`;
            });
            return `
            <div class="card move-card mb-1" block="${par.id}">
                ${templateBlockheader( par, 'mainBlockchain', '<i class="bi bi-box-arrow-left"></i>' )}
              <div class="card-body"  id="block-${par.id}">
              <span>пока не работает</span>
                <div class="form-floating">
                  <select class="form-select" onchange="blockSettingset('techname',this.value,${par.id})">
                    ${b}
                  </select>
                  <label for="floatingSelect">Скрипт в основном боте</label>
                </div>
              </div>
           </div>`;
        }
        
        
        
        
        
        
        function templateBlockheader( par, type, icon='' ){
            return `
              <h5 class="card-header ps-4 pe-3" blockheader>
                <div class="row">
                    <div class="col-sm">
                        <span class="text-secondary" style="font-size: small;margin-right: 1px;">ID:</span> <span class="text-info fs-6 me-1" editvr=${par.id}>${par.display}</span> ${icon} ${par.name}
                    </div>
                    <div class="col-auto d-flex pe-0">
                        <div class="form-check form-switch px-2 border-end" style="margin-top: -2px;">
                          <input class="form-check-input" type="checkbox" id="activate-block" onclick="sendBlocksetting(${par.id}, 'activate', $(this).is(':checked')? 1:0 )" ${par.activate==1? 'checked':'' }>
                          <label class="form-check-label" for="activate-block"></label>
                        </div>
                        
                        <a class="px-2 border-end" href="#" onclick="blockControl(${par.id})"><i class="bi bi-stopwatch"></i></a>
                        `+(type=='input'||type=='message' ? `<a href="#" class="px-2 border-end"  onclick="getKeyboard(${par.id})"><i class="bi bi-grip-horizontal" style="font-size: large;"></i></a>`:'' )+`
                        `+(type=='input' ? `<a href="#" class="px-2 border-end"  onclick="getBlock(${par.id})"><i class="bi bi-gear-fill" style="font-size: large;"></i></a>`:'' )+`
                        
                        <a href="#" class="px-2 border-end" onclick="$('#block-${par.id}').collapse('toggle');"><i class="bi bi-chevron-expand"></i></a>
                        ${ par.nosave===undefined? `<a href="#" class="px-2 border-end" onclick="saveBlock(${par.id}, '${par.type}')"><i class="bi bi-check"></i></a>`:"" }
                        <a href="#" class="px-2" onclick="deleteBlock(${par.id})"><i class="bi bi-x text-danger"></i></a>
                    </div>
                    <div class="col-auto ps-0  border-start" onclick="supselect(${par.id_script}, ${par.id})" supselect style="display:none">
                       <button class="btn btn-outline-secondary border-0 p-0 px-1" type="button"><i class="bi bi-hand-index-thumb-fill"></i></button>
                    </div>
                    
                </div>
                
                
              </h5>
            `;
        }
        
        async function blockControl(id){
            
            data=blocks[id].par.blockControl;
            if(data==undefined||data=="") data="{}";
            data=jQuery.parseJSON(data);
            promptmodcreate({'title':'Контроль блока','btnOk':'Ok','btnNo':'Отмена'},
            [
                {value:data.type===undefined||data.type===""?"stop":data.type,radioitems:[{text:"Если пользователь остановился на этом шаге",value:"stop"},{text:"Если пользователь дошел до этого шага",value:"reach"}]},
                {label:'отправляем ему скрипт',value:data.script===undefined?"":data.script,items:scripts.map(function(it) {return {text:it.name,value:it.id}})},
                {label:'через (минут)', value:data.timeout===undefined?60*24:data.timeout},
            ]);
            let result = await promptmod; if(!result || result[0]==='' ) return;
            console.log(result);
            blockSettingset('blockControl',JSON.stringify({ type:result[0], script:result[1], timeout:result[2] }),id)
        }

        
        function selSupselect(ansId){
            supselectVar = ansId;
            
            $(`[selSupselect]`).removeClass('btn-success');
            $(`[selSupselect]`).addClass('btn-outline-secondary');
            
            $(`[selSupselect="${ansId}"]`).removeClass('btn-outline-secondary');
            $(`[selSupselect="${ansId}"]`).addClass('btn-success');
            $('[supselect]').show();
        }
        
        function supselect(script, block=false){
            $('[supselect]').hide();
            $(`[selSupselect]`).removeClass('btn-success');
            $(`[selSupselect]`).addClass('btn-outline-secondary');
            
            b2.spinner();
            $.post("p.php?q=supselectinfo", { script:script, block:block }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
            	
            	if(res.block != null && res.block!= undefined) res.script+=' / '+res.block;
            	$('#'+supselectVar).html(res.script);	
                $('#'+supselectVar).attr('action', script);
                $('#'+supselectVar).attr('actionblock', block);
            });
            
        }
        
        
        
        
        $('body').on('click', '[editvr]', async function(){
            let text = $(this).html();
            let th = this;
            promptmodcreate({'title':'Введите название переменной','btnOk':'Ok','btnNo':'Отмена'},
            [{label:'Название', value:text}]);
            let result = await promptmod; if(!result) return;
            let name = result[0];
            b2.spinner();
            $.post("p.php?q=setDisplay", { id:$(this).attr('editvr'), name:name }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');

            	$(th).html(name);	

            });
        })


        function blockSettingset(vr,vl,id){
            b2.spinner();
            $.post("p.php?q=blockSettingset", {vr:vr,vl: vl,id:id}).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
            	blocks[id].par=res.par;
            });
            
        }
        var tscriptsArr=[];
        function scriptListmodal(idAnswer,src){

            $.post("p.php?q=scriptsList", { }).done(
                function(data) {
                    let res = jQuery.parseJSON(data);
                    if(res.success!=='ok')
                        return toast('Ошибка', res.err?res.err:'error', 'error');
                    let html = '';
                    if(src==='input'){
                        html+= templateListmodalrow('', 'Продолжить', idAnswer, 'text-success');
                        html+= templateListmodalrow('exit', 'Закончить', idAnswer, 'text-danger');
                    }

                    res.data.forEach(function(item, i) {
                        html+= templateListmodalexpandrow( item.id, item.name, item.blocks, idAnswer, src );
                    });
                    tscriptsArr=res.data;
                    $('#scriptListmodalbody').html( html );
                    $('#scriptListModal').modal('show');

                });

        }

        
        function templateListmodalrow(scriptId, scriptName, idAnswer, _class=''){
            return `<a href="#" class="list-group-item list-group-item-action ${_class}" scriptid="${scriptId}"
            onclick="$('#${idAnswer}').attr('action', '${scriptId}');$('#${idAnswer}').attr('actionblock', '');$('#${idAnswer}').text('${scriptName}');$('#scriptListModal').modal('hide');">
            
                ${scriptName}
            </a>`;
        }
        
        function templateListmodalblock(idAnswer, item, src){
            return `<a href="#" class="list-group-item list-group-item-action"
            onclick="mdlblockclick('${idAnswer}', '${item.id_script}', '${item.id}', '${src}')">
                ${item.name}
            </a>`;
        }
   
        function templateListmodalexpandrow(scriptId, scriptName, blocks, idAnswer, src){
            
            let b='';
            blocks.forEach(function(item){
                b+= templateListmodalblock(idAnswer, item, src);
            })
            
            
            return `<div class="list-group-item list-group-item-action">
          <div class="row">
          <div class="col-sm" style="cursor:pointer"
          onclick="mdlblockclick('${idAnswer}', '${scriptId}', '', '${src}')" >
          ${scriptName}
          </div>
            <div class="col-auto p-0">
            <button class="btn border-0 p-0 px-1" type="button" style="${b===""?"display:none;":""}" data-bs-toggle="collapse" data-bs-target="#modal-scriptslist-${scriptId}" aria-expanded="false" aria-controls="modal-scriptslist-${scriptId}"> <i class="bi bi-chevron-expand"></i>  </button>
            </div>
          </div>
          <div class="collapse mt-2 list-group" childrenlist id="modal-scriptslist-${scriptId}">${b}</div>
            
          </div>`;

        }

        function mdlblockclick(idAnswer, idScript, idBlock, src){
            let sNm="",bNm="";
            tscriptsArr.forEach(function(item){
                if(item.id==idScript){
                    sNm=item.name;
                    item.blocks.forEach(function(block){
                        if(block.id==idBlock) bNm=block.name;
                    })
                    return;
                }
            })
            if(idBlock != null && idBlock!= undefined && idBlock!== "") sNm+=' / '+bNm;

            if(src==="input"){
                $('#'+idAnswer).html(sNm);
                $('#'+idAnswer).attr('action', idScript);
                $('#'+idAnswer).attr('actionblock', idBlock);
            }

            if(src==="goto"){
                $.post("p.php?q=gotoSet", { b:blockId,ids:idScript,idb:idBlock }).done(
                    function(data) {
                        let res = jQuery.parseJSON(data);
                        if(res.success!=='ok')
                            return toast('Ошибка', res.err?res.err:'error', 'error');
                        $("#btn-goto-"+blockId).html(sNm);
                    });
            }

            if(src==="faq"){
                blockSettingset('script_if_empty',idScript,blockId);
                blockSettingset('block_if_empty',idBlock,blockId);
                $("#btn-faq-"+blockId).html(sNm);
            }


            $('#scriptListModal').modal('hide');

        }

        
        
        
        
        
        
        
        
        
        
        
        
        