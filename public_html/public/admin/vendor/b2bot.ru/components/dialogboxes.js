/**
 * Value input window
 * if btnOk or btnNo are not specified, they will not be
 *
 async function add(id){
    promptmodcreate({title:'Edit',btnOk:'Save',btnNo:'Cancel',size:'lg'},  // all parameters are optional. size - sm (small),lg (larger than average),xl (large). Without indication - average
    [
         {label:'user',value:'Ivan',placeholder:'input name',type:"phone"} // all fields are optional. type (by default text) accepts any values of the html type attribute of the input component
        ,{label:'color', datalist:[{text:'red',value:'red'},{text:'green',value:'green'},{text:'blue',value:'blue'}] } // attribute list
        ,{label:'about me',text:'',height:150} // text* - textarea, height default 100px
        ,{label:'color',value:val,items:[{text:'red',value:'red'},{text:'green',value:'green'},{text:'blue',value:'blue'}] } // items* - list
        ,{label:'color',value:val,radioitems:[{text:'red',value:'red'},{text:'green',value:'green'},{text:'blue',value:'blue'}] } // radioitems* - list (old method)
        ,{label:'color',value:"green",radio:[{text:'text red',label:'red', id:'red', placeholder:"select color", type:"text"},{text:'text green',label:'green', id:'green'}] } // radio* - list (new method)
        ,{label:'color',checkitems:[{text:'text red',label:'red', id:'red', checked:1, placeholder:"select color", type:"text"},{text:'text green',label:'green', id:'green', checked:0}] } // checkitems* - a group of checkboxes
        ,{html:'<h5>hello world</h5>'} // html*
        ,{checkbox:true, label:"", checked:1/0}
        ,{color:'#FF476080', label:"select"}
        ,{label:'color',style:"",inputlist:['red', 'green', 'blue']} // inputlist*  - a block of input fields with the "Add (+)" button. Returns an array. Instead of the value, you can add an array [[1,2,3], 'green', 'blue'], then an array of field values will be returned. addlength - the number of columns when adding
        ,{files:false,label:'upload files', accept:".xls"} // files*
        ,{inlinekb:it[0].kb.keys??[], label:'Клавиатура', style:"width: 470px"},
    ]);

    let result = await promptmod; if(!result) return;
    let user = result[0];
    let color = result[1];
}

 *
 */


var promptmodModalElement = document.createElement("div");
promptmodModalElement.innerHTML = `<div class="modal fade" id="promptmodModal" tabindex="-1" style="z-index:9999;" aria-labelledby="promptmodModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered" id="promptmodModalpreheader">
    <div class="modal-content">
      <div class="modal-header py-1" id="promptmodModalHeader">
        <h5 class="modal-title" id="promptmodModalLabel"></h5>
        
        <button type="button" style="margin: -0.5rem -0.5rem -0.5rem auto;opacity: 0.7;" class="btn" expand="0" onclick="promptmodModalexpand(this)"><i class="bi bi-arrows-angle-expand"></i></button>
        <button type="button" style="margin:0" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" promptmodModal>
        <div id="promptmodModalArea">
        </div>
        <div id="promptmodModalCustomArea">
        </div>
      </div>
      <div class="modal-footer p-0">
        <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="promptmodModalBtnNo" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-outline-primary px-5" id="promptmodModalBtnOk" onclick="promptmodModalOk()">Ok</button>
      </div>
    </div>
  </div>
</div>`;
document.currentScript.parentNode.appendChild(promptmodModalElement);
var promptmodModal = document.getElementById('promptmodModal');
var promptmodModalB = new bootstrap.Modal(promptmodModal, {});
var listenerPromptmod = 0;
var promptmod;
function promptmodModalOk(){
    promptmodModalB.hide();
    listenerPromptmod = true;
}
function promptmodcreate(attr={},data=[]){
    let h="",v="",clrs=[];
    data.forEach(function(item, i) {

        if(item.html!==undefined) v='<div class="mb-1">'+item.html+'</div>';
        else if(item.items!==undefined) v=promptmodModalTemplateselect(item, data.length-1==i?true:false);
        else if(item.color!==undefined) {xx=promptmodModalTemplateСolor(item, data.length-1==i?true:false);v=xx.b;clrs.push(xx.id);}
        else if(item.text!==undefined) v=promptmodModalTemplateTextarea(item, data.length-1==i?true:false);
        else if(item.checkbox!==undefined) v=promptmodModalTemplatecheckbox(item, data.length-1==i?true:false);
        else if(item.radioitems!==undefined) v=promptmodModalTemplateradioitems(item, data.length-1==i?true:false);
        else if(item.radio!==undefined) v=promptmodModalTemplateradio(item, data.length-1==i?true:false);
        else if(item.checkitems!==undefined) v=promptmodModalTemplatecheckitems(item, data.length-1==i?true:false);
        else if(item.inputlist!==undefined) v=promptmodModalTemplateInputlist(item, data.length-1==i?true:false);
        else if(item.inlinekb!==undefined) v=promptmodModalTemplateInlinekb(item, data.length-1==i?true:false);
        else if(item.files!==undefined) v=promptmodModalTemplateimage(item, data.length-1==i?true:false);

        else v=promptmodModalTemplate(item, data.length-1==i?true:false);

        h+=v;
    });
    document.getElementById('promptmodModalArea').innerHTML=h?h:promptmodModalTemplate([]);

    document.getElementById('promptmodModalpreheader').classList.remove("modal-sm","modal-lg","modal-xl","modal-fullscreen");

    if(attr.size!==undefined)
        document.getElementById('promptmodModalpreheader').classList.add("modal-"+attr.size);


    if(attr.title===''||!attr.title||attr.title===undefined)
        document.getElementById('promptmodModalHeader').style.display = "none";
    else {
        document.getElementById('promptmodModalLabel').innerText = attr.title;
        document.getElementById('promptmodModalHeader').style.display = "";
    }

    document.getElementById('promptmodModalBtnOk').innerText=attr.btnOk===''||!attr.btnOk?'OK':attr.btnOk;
    if(attr.btnOk===undefined) document.getElementById('promptmodModalBtnOk').style.display = "none";

    document.getElementById('promptmodModalBtnNo').innerText=attr.btnNo===''||!attr.btnNo||attr.btnNo===undefined?'Close':attr.btnNo;
    if(attr.btnOk===undefined) document.getElementById('promptmodModalBtnOk').style.display = "none";
    if(attr.btnNo===undefined) document.getElementById('promptmodModalBtnNo').style.display = "none";
    if(clrs.length>0){
        clrs.forEach(function(it) {
            var myPicker = new JSColor('#jsc-'+it);
            myPicker.option({
                'backgroundColor': '#333',
                'zIndex':10000,
                'previewSize': 56
            });
        });

    }
    promptmodModalB.show();
    setTimeout(() => {
        let inp = document.querySelectorAll("[promptmoditems]")[0];
        if(inp!==undefined &&(inp.nodeName==="INPUT"||inp.nodeName==="TEXTAREA")){
            inp.focus();
            inp.select();
        }

    }, 500);
    promptmod = new Promise((resolve, reject) => {
        listenerPromptmod = 0;
        let timer = window.setInterval( () => {
            if(listenerPromptmod!==0){
                if(listenerPromptmod===true){
                    let arr=[];
                    let li = document.querySelectorAll('[promptmoditems]');
                    for (let i = 0, len = li.length; i < len; i++){
                        let v=li[i].value;
                        if((radiocontainer=li[i].getAttribute("radiocontainer"))!==undefined&&radiocontainer!==null) v=getradiocheck(radiocontainer);
                        else if((radio=li[i].getAttribute("radio"))!==undefined&&radio!==null) v=getradiocheck(radio,1);
                        else if((checkitems=li[i].getAttribute("checkitems"))!==undefined&&checkitems!==null) v=getcheckitems(checkitems);
                        else if((filesgroupid=li[i].getAttribute("filesgroupid"))!==undefined&&filesgroupid!==null) v=filesgroupid;
                        else if((inputlistId=li[i].getAttribute("inputlist"))!==undefined&&inputlistId!==null){
                            v=[];
                            [].forEach.call(document.querySelectorAll(`[inputlistitem="${inputlistId}"]`), function(el) {
                                let val=[];
                                for (var i = 0; i < el.childNodes.length; i++)
                                    if(el.childNodes[i].nodeName=="INPUT"){ val.push(el.childNodes[i].value); }
                                v.push(val.length===1?val[0]:val);
                            });
                        }
                        else if((selectlistId=li[i].getAttribute("selectlist"))!==undefined&&selectlistId!==null){
                            v=[];
                            [].forEach.call(document.querySelectorAll(`[selectlistitem="${selectlistId}"]`), function(el) { v.push(el.value); });
                        }
                        else if((inlinekbId=li[i].getAttribute("dbinlinekblist"))!==undefined&&inlinekbId!==null){
                            v={keys:[],cols:2};
                            [].forEach.call(document.querySelectorAll(`[inlinekbitem="${inlinekbId}"]`), function(el) {
                                if((txt=el.querySelector('[txt]').value)!=="")v.keys.push([txt,el.querySelector('[cb]').value]);
                            });
                        }


                        arr.push(v);
                    }
                    resolve(arr);
                } else{
                    resolve(false);
                }

                clearTimeout(timer);
                promptmod = null;
            }
        },100);
    });
}

function promptmodModalexpand(th){
    let e=document.getElementById('promptmodModalpreheader');
    if(th.getAttribute('expand')==="0"){
        th.setAttribute('expand', "1");
        e.classList.add("modal-fullscreen");
    } else {
        th.setAttribute('expand', "0");
        e.classList.remove("modal-fullscreen");
    }

}
function promptmodModalTemplate(d,e){
    let datalist='';
    let datalistId='';
    if(d.datalist!==undefined&&Array.isArray(d.datalist)){
        datalist= d.datalist.map(function(it) {
            return `<option value="${it.value}" label="${it.text}">`;
        });
        datalistId=rand();
        datalist = `<datalist id="${datalistId}">${datalist.join()}</datalist>`;
    }

    return `<div class="input-group ${e?'':'mb-1'}">`+
        (d.label?`<span class="input-group-text">${d.label}</span>`:'')+
        `<input type="${d.type?d.type:"text"}" class="form-control" list="${datalistId}" placeholder="${d.placeholder?d.placeholder:''}" value="${d.value?d.value:''}" promptmoditems onkeydown="if (event.keyCode === 13) {promptmodModalOk();}">
</div>
${datalist}
`;
}


var b2prompt = {
    textarea: {
        counter:text=> {
            //text = text.replace(/<(.|\n)*?>/g, '');
            let l=text.length;
            return `
          
            <div class="d-flex" style="gap: .1rem;">
                <div>символов: ${l}</div>
            </div>
            <div class="d-flex" style="gap: .1rem;">
                <div>лимит в сообщениии без файлов: 4096 ${l>0?"("+(4096-l)+")":""}</div>
            </div>
            <div class="d-flex" style="gap: .1rem;">
                <div>лимит в сообщениии с файлом: 1000 ${l>0?"("+(1000-l)+")":""}</div>
            </div>
          </div>
          `;
        },
        oninput:t=> {
            let id=t.getAttribute("id");
            //let text=t.value;
            //document.querySelector("#counter_"+id).innerHTML=b2prompt.textarea.counter(text);
            document.querySelector("#counter_"+id).innerHTML=b2prompt.textarea.counter(t.value);
        }
    }
}


function promptmodModalTemplateСolor(d,e){
    let id=rand();
    return {b: `${d.label}<div class=" ${e?'':'mb-1'}">
 <input class="form-control" value="${d.color}" id="jsc-${id}" style="padding-bottom: 10px;font-size: 20pt;" promptmoditems>
</div>`,id:id};
}

function promptmodModalTemplateTextarea(d,e){
    let id="txrarea_"+rand();
    let editor= d.editor?appTelegramEditor.init(id):"";

    let esc = document.createElement('textarea');
    esc.innerHTML = d.text?d.text:'';
    let prText=esc.value;
    return `
<div class="${d.editor?"mb-1 text-end":""}">${editor}</div>
<div class="form-floating ${e?'':'mb-1'}">
  <textarea class="form-control" placeholder="text" id="${id}" style="height: ${d.height?d.height:'100'}px" ${d.editor?'oninput="b2prompt.textarea.oninput(this)"':""} promptmoditems>${d.text?d.text:''}</textarea>`+
        (d.label?`<label>${d.label}</label>`:'')+
        `<div id="counter_${id}" style="font-size: 10pt;color: #5e5e5e;font-style: italic;">`+(d.editor?b2prompt.textarea.counter(prText):"")+"</div>"+
        "</div>";
}
function promptmodModalTemplateselect(d,e){
    if(d.values!=undefined) return promptmodModalTemplateselectlist(d,e);
    let it='<option value=""></option>';
    d.items.forEach(function(itm) {
        it += `<option value="${itm.value}" ${itm.value==d.value?'selected':''}>${itm.text}</option>`;
    });
    return `<div class="input-group ${e?'':'mb-1'}">`+
        (d.label?`<span class="input-group-text">${d.label}</span>`:'')+
        `<select class="form-control" promptmoditems>${it}</select>
</div>
`;
}
var slctlstitmsv={};
function promptmodModalTemplateselectlist(d,e){
    let h="", btn="", id='select-list-'+rand(), it='<option value=""></option>';
    d.items.forEach(function(itm) {it += `<option value="${itm.value}">${itm.text}</option>`;});
    slctlstitmsv[id]=it;
    d.values.forEach(function(vl) {
        h+= `<div class="input-group mb-1">`+
            `<select class="form-control" selectlistitem="${id}">${it.replace((r=`value="${vl}"`),r+" selected")}</select>
${d.addbtn?'<button class="btn btn-outline-secondary" type="button" onclick="this.parentNode.remove()"><i class="bi bi-x"></i></button>':""}
</div>
`;
    });
    if(d.addbtn)btn=`<button type="button" class="btn btn-outline-secondary btn-sm ${e?'':'mb-1'}" style="padding: 1px 30px 1px 30px;" onclick="selectlistAdd('${id}')"><i class="bi bi-plus"></i></button>`;
    return `<div class="ps-3 ms-1 border-start" selectlist="${id}" promptmoditems>${promptmodModalLabel(d.label)}<div id="${id}" class="mb-1">${h}</div></div>
    <div class="text-end">${btn}</div>`;
}

function selectlistAdd(id){
    document.getElementById(id).insertAdjacentHTML('beforeend', `<div class="input-group mb-1">
<select class="form-control" selectlistitem="${id}">${slctlstitmsv[id]}</select>
<button class="btn btn-outline-secondary" type="button" onclick="this.parentNode.remove()"><i class="bi bi-x"></i></button>
</div>`);
}
function promptmodModalTemplateradioitems(d,e){
    let name=rand(),it="";
    d.radioitems.forEach(function(itm) {
        let r=rand();
        it += `<div class="form-check">
  <input class="form-check-input" type="radio" name="${name}" id="r-item-${r}" ${itm.value==d.value?'checked':''} value="${itm.value}">
  <label class="form-check-label" for="r-item-${r}">
    ${itm.text}
  </label>
</div>`;
    });
    return `<div class="${e?'':'mb-1'}" promptmoditems radiocontainer="${name}">${promptmodModalLabel(d.label)}${it}</div>`;
}
function getradiocheck(name,arr=false)
{
    let s,inp=document.querySelector(`input[name="${name}"]:checked`);
    if(!inp) return false;
    if(!arr) return inp.value;
    s={id:inp.getAttribute("eid")}
    if(t=document.getElementById('text-'+inp.getAttribute("id"))) s['text']=t.value;
    return s;
}
function promptmodModalTemplateradio(d,e){
    let name="dbrch-"+rand(),it="";
    d.radio.forEach(function(itm) {
        let r="rdgr-ch-item-"+rand(),st=itm.id==(d.value??"")?"checked":"";
        it += itm.text!==undefined? `<div class="input-group mb-1" rbgname="${name}">
    <label class="input-group-text" for="${r}">${itm.label}</label>
    <div class="input-group-text">
    <input class="form-check-input" type="radio" name="${name}" id="${r}" eid="${itm.id}" ${st}>
    </div>
    <input  type="${itm.type?itm.type:"text"}" class="form-control" placeholder="${itm.placeholder===undefined?"":itm.placeholder}" id="text-${r}" value="${itm.text}">
    </div>` :
            `<div class="form-check" rbgname="${name}" rdgid="${r}" eid="${itm.id}">
      <input class="form-check-input" type="radio" name="${name}" eid="${itm.id}" id="${r}" ${st}>
      <label class="form-check-label" for="${r}">${itm.label}</label>
    </div>`;
    });
    return `<div class="${e?'':'mb-1'}" promptmoditems radio="${name}">${promptmodModalLabel(d.label)}${it}</div>`;
}

function promptmodModalTemplatecheckitems(d,e){
    let name=rand(),it="";
    d.checkitems.forEach(function(itm) {
        let r=rand();
        it += itm.text!==undefined? `<div class="input-group mb-1"  name="${name}" chgid="${r}" eid="${itm.id}">
    <label class="input-group-text" style="max-width: 300px;" for="chgr-ch-item-${r}">${itm.label}</label>
    <div class="input-group-text">
        <input class="form-check-input" type="checkbox" id="chgr-ch-item-${r}" ${[1,"1",true].includes(itm.checked)?"checked":""}>
    </div>
    <input  type="${itm.type?itm.type:"text"}" class="form-control" placeholder="${itm.placeholder===undefined?"":itm.placeholder}" id="chgr-text-item-${r}" value="${dbEscapeHtml(itm.text)}">
    </div>` :
            `<div class="form-check" name="${name}" chgid="${r}" eid="${itm.id}">
      <input class="form-check-input" type="checkbox" id="chgr-ch-item-${r}" ${[1,"1",true].includes(itm.checked)?"checked":""}>
      <label class="form-check-label" for="chgr-ch-item-${r}">${itm.label}</label>
    </div>`;
    });
    return `<div class="${e?'':'mb-1'}" promptmoditems checkitems="${name}">${promptmodModalLabel(d.label)}
    <div class="${d.checkitems.length>1?"ps-3 border-start":""}" style="max-height: 300px;overflow-y: auto; overflow-x: hidden;">${it}</div>
    </div>`;
}

function getcheckitems(name)
{   let s=[];
    var inp = document.getElementsByName(name);
    let eid,id,ch,t,p;
    for (var i = 0; i < inp.length; i++){
        id=inp[i].getAttribute("chgid");
        eid=inp[i].getAttribute("eid");
        ch=document.getElementById('chgr-ch-item-'+id).checked?1:0;
        t=document.getElementById('chgr-text-item-'+id);
        p={ id:eid,check:ch };
        if(t)p['text']=t.value;
        s.push(p);
    }
    return s;
}

function promptmodModalTemplatecheckbox(d,e){
    let r=rand();
    return `<div class="form-check">
  <input class="form-check-input ${e?'':'mb-1'}" type="checkbox" value=${d.checked?1:0} id="check-${r}" ${[1,"1",true].includes(d.checked)?"checked":""} onclick="this.value=this.checked?1:0" promptmoditems>
  <label class="form-check-label" for="check-${r}">${d.label}</label>
</div>`
}
function promptmodModalTemplateimage(d,e){
    let r=rand();

    $.post("/admin/upload/getfiles.php", { group:d.files }).done(
        function(data) {
            let res = jQuery.parseJSON(data);
            if(res.success!=='ok')
                return toast('Ошибка', res.err?res.err:'error', 'error');

            let btn=appUpload.form({
                id:r
                ,group:d.files
                ,uploadFunc:'promptmodModalTemplateimageUploadFile' // (function)function on successful download. The group id is passed
                ,deleteFunc:'promptmodModalTemplateimageUploadFile' // (function)function when deleting a file. The group id is passed
                ,accept:d.accept??false
            })

            let area = appUpload.container({
                id:r
                ,files:res.data
            })

            document.getElementById('files-'+r).innerHTML=btn+area;
        });

    return `${promptmodModalLabel(d.label)}
 <div class="${e?'':'mb-1'}" id="files-${r}" promptmoditems filesgroupid="${d.files}"></div>`;
}

function promptmodModalTemplateimageUploadFile(id, gr){
    document.getElementById('files-'+id).setAttribute('filesgroupid', gr);
}

function promptmodModalLabel(t){
    if(t===undefined||!t||t===null||t==="") return "";
    return `<i class="ms-1">${t}</i><br>`;
}



function promptmodModalTemplateInlinekb(d,e){
    let id='inlinekb-list-'+rand(),it="";
    d.inlinekb.forEach(function(itm) {
        it += dbInlineKeyAddTemplate(itm[0],itm[1],id);
    });
    return `<div class="border px-3 py-2 rounded mb-1" promptmoditems dbinlinekblist="${id}" style="${d.style??""}">
 ${promptmodModalLabel()}
<span style="margin: 3px 3px 0 0;">${d.label??"Inline keyboard"}</span>
<button type="button" class="btn btn-light btn-sm " onclick="dbInlineKeyAdd('${id}')"><i class="bi bi-plus"></i> кнопка</button>
<div class="p-2 border rounded mb-2" id="${id}">${it}</div> 
</div>`
}

function dbInlineKeyAdd(id){
    document.getElementById(id).insertAdjacentHTML('beforeend', dbInlineKeyAddTemplate("","",id));
}
function dbInlineKeyAddTemplate(t,v,id){
    return `<div class="input-group mb-1" inlinekbitem="${id}">
    <input type="text" placeholder="Текст" class="form-control" txt value="${dbEscapeHtml(t)}">
    <input type="text" placeholder="Значение" class="form-control" cb value="${dbEscapeHtml(v)}">
    </div>`

}
function promptmodModalTemplateInputlist(d,e){
    let id='input-list-'+rand();
    it="";
    d.inputlist.forEach(function(itm) {
        if(!Array.isArray(itm))itm = [itm];
        let c = "";
        itm.forEach(function(it,i) { c += `<input type="text" class="form-control" value="${it}">`; });
        it += `<div class="input-group mb-1" inputlistitem="${id}">
              ${c}
              <button class="btn btn-outline-secondary" type="button" onclick="this.parentNode.remove()"><i class="bi bi-x"></i></button>
            </div>`;

    });

    return `<div class="border p-2 rounded mb-1" promptmoditems inputlist="${id}" style="${d.style??""}">
 ${promptmodModalLabel(d.label)}
<div id="${id}" class="mb-1"> ${it} </div>
    <div class="text-end">
    <button type="button" class="btn btn-outline-secondary btn-sm" style="padding: 1px 30px 1px 30px;" onclick="inputlistAdd('${id}', ${d.addlength*1<1?1:d.addlength*1})"><i class="bi bi-plus"></i></button>
    </div>
</div>`
}

function inputlistAdd(id,r){
    let c = '';
    if(r==0||r==undefined||!r) r=1;

    for(let i=0;i<r;i++) {c+= '<input type="text" class="form-control" value="">'};
    document.getElementById(id).insertAdjacentHTML('beforeend',`<div class="input-group mb-1" inputlistitem="${id}">
    ${c}
    <button class="btn btn-outline-secondary" type="button" onclick="this.parentNode.remove()"><i class="bi bi-x"></i></button>
    </div>`);
}
promptmodModal.addEventListener('hidden.bs.modal', function (event) {

    setTimeout(function(){listenerPromptmod = false;}, 300);
})
function dbEscapeHtml(t) {return (typeof t === 'string' || t instanceof String) ? t.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;"):t}
/**
 * ALERT
 *
 * Окно подтверждения
 *
 alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Нет'});
 let result = await alertmod; if(!result) return;
 */

var alertmodModalElement = document.createElement("div");
alertmodModalElement.innerHTML = `<div class="modal fade" id="alertmodModal" tabindex="-1" style="z-index:9999;" aria-labelledby="alertmodModalLabel" aria-hidden="true" onkeydown="if (event.keyCode === 13) {alertmodModalOk()}">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
        <div class="modal-body" id="alertmodModalbody">
           <h5 class="modal-title text-end" id="alertmodModalLabel"></h5>
        </div>
      <div class="modal-footer p-0">
        <button type="button" class="btn btn-sm btn-outline-secondary" style="width:47%" id="alertmodModalBtnNo" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-outline-primary" style="width:47%" id="alertmodModalBtnOk" onclick="alertmodModalOk()">Ok</button>
      </div>
    </div>
  </div>
</div>`;
document.currentScript.parentNode.appendChild(alertmodModalElement);
var alertmodModal = document.getElementById('alertmodModal');
var alertmodModalB = new bootstrap.Modal(alertmodModal, {});
var listenerAlertmod = 0;
var alertmod;
function alertmodModalOk(){
    alertmodModalB.hide();
    listenerAlertmod = true;
}
function alertmodcreate(attr={}){
    let h='';


    if(attr.title===''||!attr.title||attr.title===undefined){
        $('#alertmodModalbody').hide();
    } else
        document.getElementById('alertmodModalLabel').innerText = attr.title;

    document.getElementById('alertmodModalBtnOk').innerText=attr.btnOk===''||!attr.btnOk||attr.btnOk===undefined?'OK':attr.btnOk;
    document.getElementById('alertmodModalBtnNo').innerText=attr.btnNo===''||!attr.btnNo||attr.btnNo===undefined?'Close':attr.btnNo;
    alertmodModalB.show();
    alertmod = new Promise((resolve, reject) => {
        listenerAlertmod = 0;
        let timer = window.setInterval( () => {
            if(listenerAlertmod!==0){
                resolve(listenerAlertmod);
                clearTimeout(timer);
                alertmod = null;
            }
        },100);
    });
}
alertmodModal.addEventListener('hidden.bs.modal', function (event) {
    setTimeout(function(){listenerAlertmod = false;}, 300);

})

/**
 * SELECT
 */

var selectmodModalElement = document.createElement("div");
selectmodModalElement.innerHTML = `<div class="modal fade" id="selectmodModal" tabindex="-1" style="z-index:9999;" aria-labelledby="selectmodModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-1" id="selectmodModalHeader">
        <h5 class="modal-title" id="selectmodModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" selectmodModal>
        <div id="selectmodModalArea">
        </div>
        <div id="selectmodModalCustomArea">
        </div>
      </div>
    </div>
  </div>
</div>`;
document.currentScript.parentNode.appendChild(selectmodModalElement);
var selectmodModal = document.getElementById('selectmodModal');
var selectmodModalB = new bootstrap.Modal(selectmodModal, {});
var listenerselectmod = 0;
var selectvariant=false;
var selectmod;
function selectmodModalOk(){
    selectmodModalB.hide();
    listenerselectmod = true;
}
function selectmodcreate(attr={},data=[]){
    let h='';
    data.forEach(function(item, i) {
        h+=selectmodModalTemplate(item, data.length-1==i?true:false);
    });
    document.getElementById('selectmodModalArea').innerHTML=h?h:selectmodModalTemplate([]);

    if(attr.title===''||!attr.title||attr.title===undefined)
        document.getElementById('selectmodModalHeader').style.display = "none";
    else {
        document.getElementById('selectmodModalLabel').innerText = attr.title;
        document.getElementById('selectmodModalHeader').style.display = "";
    }

    selectmodModalB.show();
    selectmod = new Promise((resolve, reject) => {
        listenerselectmod = 0;
        let timer = window.setInterval( () => {
            if(listenerselectmod!==0){
                if(listenerselectmod===true){
                    selectmodModalB.hide();
                    resolve(selectvariant);
                } else
                    resolve(false);
                clearTimeout(timer);
                selectmod = null;
            }
        },100);
    });
}

function selectmodModalTemplate(d,e){return `<button type="button" class="btn btn-outline-secondary mb-1 w-100" onclick="selectvariant='${d.value}';listenerselectmod=true;">${d.label}</button>`;}
selectmodModal.addEventListener('hidden.bs.modal', function (event) {listenerselectmod = false;})
function rand(){return Math.floor(Math.random()*100000);}








