
        

        
        $('body').on('click', '[scripts]', function(){
            $('[scripts]').removeClass('active');
            $(this).addClass('active');
            script = $(this).attr('scripts');
        
            $('[manhtml]').html('');
            $('[manval]').val('');
            b2.spinner();
            $.post("p.php?q=get", { id:script }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
                blocks={};
                res.blocks.forEach(function(item, i) {
                    blocks[item.id]=item;

                   switch(item.type){
                        case 'message':
                            $('#blocks-container').append( templateBlockmessage( item ) );
                        break;
                        
                        case 'input':
                            $('#blocks-container').append( templateBlockinput( item ) );
                        break;
                        
                        case 'inputFiles':
                            $('#blocks-container').append( templateInputfiles( item ) );
                        break;
                        
                        case 'func':
                            $('#blocks-container').append( templateBlockfunction( item ) );
                            codemirror(item.id);
                            $(`#block-${item.id}`).collapse('toggle');
                        break;
                        
                        case 'mainBlockchain':
                            $('#blocks-container').append( templateBlockmainblockchain( item ) );
                        break;
                        
                        case 'gotoBlock':
                            $('#blocks-container').append( templateBlockgotoblock( item ) );
                        break;

                       case 'goto':
                           $('#blocks-container').append( templateBlockgoto( item ) );
                       break;
                   }
                });
                
            	$('[mandisplay]').show();
                $('#scriptname').html(res.data.name);
                $('#script-status').prop("checked", res.data.status==1 ? true :  false);
                $('#lss').text(res.data.status==1 ? 'Включен':'Скрыт');
                $('#triggers').html(res.data.triggers);
                
                

                
            });
        })
        
        

        
        async function nameScript(e){
            if(script===false)
                return toast('Ошибка', 'Выберите скрипт', 'error');
            let _name = $(e).html();
            promptmodcreate({'title':'Имя скрипта','btnOk':'Ok','btnNo':'Отмена'},
            [{label:'', value:_name }]);
            let result = await promptmod; if(!result || result[0]==='' ) return;
            let name = result[0];
            
            b2.spinner();
            $.post("p.php?q=save", { name:name, id:script }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
                $(e).html(name);  
                $(`[scripts="${script}"]`).html(name);
                scripts.forEach(function(item, i) {
                    if(item.id==script)
                	scripts[i].name=name;
                });    
            });
        }
        
        
        
        async function saveTriggers(){
            if(script===false)
                return toast('Ошибка', 'Выберите скрипт', 'error');
            let arr = $('#triggers').html().replace(/,/gi, '\n').split('\n');
            arr=arr.map(val => val.trim()).filter(val => val !== '');
            promptmodcreate({'title':'Список триггеров','btnOk':'Ok','btnNo':'Отмена'},
            [{ inputlist:arr }]);
            let result = await promptmod; if(!result || result[0]==='' ) return;

            let par = { id:script,triggers: JSON.stringify(result[0]) };
            b2.spinner();
            $.post("p.php?q=save", par).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');

                $('#triggers').html(res.data.triggers);
                toast('Сохранение', 'Триггеры сохранены');
                
                if(res.data.duplicateTriggers)
                    toast('Сохранение', 'Следующие триггеры не были записаны, так как они закреплены за другими скриптами:<hr>'+res.data.duplicateTriggers, 'warning');

                
            });
            
        }
      
        async function deleteScript(){
            alertmodcreate({'title':'Удалить скрипт?', 'btnOk':'Ok','btnNo':'Отмена'});
            let result = await alertmod; if(!result) return;
            if(script===false)
                return toast('Ошибка', 'Выберите скрипт', 'error');
            b2.spinner();
            $.post("p.php?q=remove", { id:script }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
            		
                scripts.forEach(function(item, i) { // переносим дочерние категории в основную
                    if(item.parent==script){
                        scripts[i]['parent']="0";
                        $(`[sptnt="${item.id}"]`).prependTo( $('#scriptslist') );
                    }
                });  
                $(`[sptnt="${script}"]`).remove();
                $('[manhtml]').html('');
                $('[manval]').val('');
                $('[mandisplay]').hide();
                
                scripts.forEach(function(item, i) {
                    if(item.id==script) scripts.splice(i, 1);
                });   
                
            	script = false;
                checkCollape();
            });
            
            
            
        }
      
  
    function childScripts(scr){
        let a=[];
        scripts.filter(t => t.parent===scr).forEach(function(it){
            a.push(it.id);
            a=a.concat(childScripts(it.id));
        })
        return a;
    }
    
    /**
        function scriptParents($id){
            if(!$c = singleQuery('SELECT * FROM `constructors` WHERE id = ?', [ $id ])) return [];
            return !$c['parent']?[$c['name']]:array_merge([$c['name']],scriptParents($c['parent']));
        }
        
{id: '1', name: '/start', parent: '0'}
1: {id: '4', name: 'Мне нужен инвестор', parent: '2'}
        
        
     */
    
    
    function scriptParents(id){
        let b=0,i=0;
        if((i=getIndex('id',id,scripts))===false) return[];
        let t=scripts[i];
        return t.parent=="0"?[t.name]:[t.name].concat(scriptParents(t.parent));
    }
    async function getScriptsettings(){
            let index = getIndex('id',script,scripts);
            if(index==undefined) return toast('Ошибка', 'Скрипт не найден, попробуйте перезагрузить страницу', 'error');
            let items = [];
            let chScr=[script].concat(childScripts(script));
            
            scripts.filter(t => chScr.indexOf(t.id)===-1).forEach(function(it, i) {
            	items.push({text:scriptParents(it.id).reverse().join("<span class='text-primary'> / </span>"),value:it.id});
            });
            promptmodcreate({'title':'Настройки','btnOk':'Сохранить','btnNo':'Отмена'},  // все параметры не обязательные. size - sm (маленькое),lg (больше среднего),xl (большое). Без указания - среднее
            [
                {label:'Группа',value:scripts[index]['parent'],items:items   } 
            ]);
            let result = await promptmod; if(!result) return;
            let parent=result[0];
            b2.spinner();
            $.post("p.php?q=saveScriptpar", { id:script, par: JSON.stringify({parent:parent}) }).done(
            function(data) {
            	let res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.err?res.err:'error', 'error');
            	scripts[index]['parent']=parent;
            	if(parent==0) parent='';else parent='-'+parent;
            	$(`[sptnt="${script}"]`).prependTo( $('#scriptslist'+parent) );
                if(parent!=0)  
                $('#scriptslist'+parent).show(100);
                checkCollape();
            });
    }
      
    async function addScript(){
        promptmodcreate({'title':'Новый скрипт','btnOk':'Ok','btnNo':'Отмена'},
        [{label:'Название'}]);
        let result = await promptmod; if(!result) return;
        let name = result[0];
        b2.spinner();
        $.post("p.php?q=add", { name:name }).done(
        function(data) {
        	let res = jQuery.parseJSON(data);
        	if(res.success!=='ok')
        		return toast('Ошибка', res.err?res.err:'error', 'error');
            $('#scriptslist').append(rowTemplate( res.data ))
        	scripts.push({id:res.data.id, name:name, parent:0})
        });
    }
  
    function rowTemplate(t){
        
        
      return `  
      <div class="list-group-item list-group-item-action" search="${t.name}" sptnt="${t.id}">
      <div class="row">
      <div class="col-sm" style="cursor:pointer" scripts="${t.id}">
      ${t.name}
      </div>
        <div class="col-auto p-0" onclick="supselect(${t.id})" supselect style="display:none;"> 
           <button class="btn btn-outline-secondary border-0 p-0 px-1" type="button"><i class="bi bi-hand-index-thumb-fill"></i></button>
        </div>
        <div class="col-auto p-0" style="display:none;" id="collapse-btn-${t.id}">
         <button class="btn border-0 p-0 px-1" type="button" onclick="toggle('${t.id}')"> <i class="bi bi-chevron-expand"></i>  </button>
         </div>
      </div>
      <div class="collapse mt-2 list-group" childrenlist id="scriptslist-${t.id}"></div>
      </div>
      `;  
    }

    
    
    function checkCollape(){
        scripts.forEach(function(it, i) {
            if($('#scriptslist-'+it.id).children().length==0){
                $('#collapse-btn-'+it.id).hide();
                $('#scriptslist-'+it.id).hide();
            } else {
                $('#collapse-btn-'+it.id).show();
            }
        });
    }
    
    function toggle(id){
      if($('#scriptslist-'+id).is(':visible'))$("#scriptslist-"+id).hide(100);else $("#scriptslist-"+id).show(100);
    } 
    
    
        
        
        
        
        
        
        
        
        
        