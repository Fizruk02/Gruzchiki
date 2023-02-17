var triggers={
    remove:async el=>{
        let p=el.closest(".trigger");
        let id=p.dataset.id;
        alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Нет'});
        let result = await alertmod; if(!result) return;
        qw.post("/ajax/p.php?q=triggerDelete",{id:id},r=> {
            if(r.success){
                p.remove()
            }
        },"json")
        ;
    },
    add:async s=>{
        promptmodcreate({'title':'Добавление триггера','btnOk':'Сохранить','btnNo':'Отмена'},
        [{}]); 
        let result = await promptmod; if(!result) return;
        let t=result[0];
        qw.post("/ajax/p.php?q=triggerAdd",{type:s,text:t},r=> {
            if(r.success){
                qw.qs(`[data-src-list="${s}"]`).innerHTML+=triggers.templates.item({id:r.id,text:t});
            }
        },"json")
        
    },
    templates:{
        item: it=> {
            return `<div class="list-group-item list-group-item-action trigger" data-id="${it.id}">
    				<span>${it.text}</span>
    				<button type="button" class="btn btn-danger btn-sm float-end" action="delete" onclick="triggers.remove(this)"><i class="bi bi-x-lg"></i></button>
    			</div>`;
        }
    }
}
document.addEventListener("DOMContentLoaded", ()=>{
    qw.click('.trigger [action="delete"]', ev=>{
        triggers.remove(ev.target);
    })
});