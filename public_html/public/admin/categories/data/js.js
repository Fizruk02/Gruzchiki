var tree_items = [];
var active_node = false;
var tree_multiple = false;
var tree_plugins = ['dnd', 'wholerow', 'contextmenu'];
var fl = '';

qw.event(".seo_slug", "blur", (el)=>{
    if (!active_node) {
        el.target.value="";
        return toast("Сохранение", "Выберите категорию", "w");
    }
    let u=el.target.value;
    qw.post("p.php?q=saveSlug", {id:active_node.id, url:u},(r)=>{
        el.target.value=r.slug;
    }, "json", "save slug")
})



function filesForm(){

    $.post("/admin/upload/getfiles.php", { group:fl }).done(
        function(data) {
            let res = JSON.parse(data);

            let btn=appUpload.form({
                id:1
                ,classes:'btn btn-outline-secondary'
                ,group:fl
                ,uploadFunc:'uploadFunc'
                ,deleteFunc:'uploadFunc'
            })

            let area = appUpload.container({
                id:1
                ,files:res.data
            })

            $('#fielsarea').html(btn+area);

        });
}

function uploadFunc(id, groupFilesId){
    fl=groupFilesId;
}


const func={
    get:(id)=>{
        qw.post("p.php?q=get", { id:id }, (res)=> {
            qw.qsa('[data-seo-item="1"]').forEach((el)=>el.value="");
            qw.qsa('[data-field="category"]').forEach((el)=>el.value="");
            qw.qsa('[data-field="descr"]').forEach((el)=>el.value="");
            if(res.seo)res.seo.forEach((it)=> { qw.qs(".seo_"+it.var).value=it.val; })
            
            fl=res.data.image||"";
            filesForm();
            
            qw.lang.post(res.data.translates);
            qw.qs("#display").checked=res.data.display_in_the_link==="1";

        }, 'json', "get data");
    },
    save:()=>{
        if (!active_node) return toast("Сохранение", "Выберите категорию", "e");
        let tr_name = qw.lang.get('input[data-field="category"]');
        let tr_descr = qw.lang.get('textarea[data-field="descr"]');
        let name=JSON.parse(tr_name)[project.lang.list[0].iso];
        if (!name) return toast("Сохранение", "Введите название", "e");
        let seo=[
            { "var":"description",val:qw.qs(".seo_description").value },
            { "var":"slug",val:qw.qs(".seo_slug").value },
            { "var":"title",val:qw.qs(".seo_title").value },
        ]
        qw.post("p.php?q=save", {
            id:active_node.id,
            seo:seo,
            image:fl,
            name:name,
            dl:qw.qs("#display").checked?1:0,
            tr:tr_name,
            tr_descr:tr_descr,
        }, (r)=> {
            project.stopEvent=true;
            $("#categories").jstree('rename_node', active_node, name);
            project.stopEvent=false;
        }, 'json', "get data");
    },
    add:(node=false,newSelect=true)=>{
        let name = node?node.text:qw.qs("#new-category-edit").value;
        if (!name) return toast("Создание", "Введите название", "e");
        qw.post("p.php?q=add", {
            name:name,
            parent:active_node.id||0
        }, (r)=> {
            project.stopEvent=true;
            
            if(node) {
                active_node = node;
                $("#categories").jstree(true).set_id(node,r.id);
                $("#categories").jstree().deselect_all(true);
            }
            else
                $("#categories").jstree().create_node(active_node.id||"#", { "id": r.id, "text": name }, "last");

            project.stopEvent=false;
            qw.qs("#new-category-edit").value="";
            newSelect&&func.get(r.id)
        }, 'json', "get data");

    },
    delete:async ()=>{
        if (!active_node) return alert('выберите категорию');
        alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Отмена'});
        let result = await alertmod; if(!result) return;

        qw.post("p.php?q=dlt", {id:active_node.id},()=>{
            $("#categories").jstree().delete_node(active_node);
            active_node = false;
            qw.qsa('[data-field="category"]').forEach((el)=>el.value="");
        },"json","category delete");
    },
    move:()=> {
        qw.post("p.php?q=move", { ids:func.ids() },()=>{ },"json","category move");
    },
    ids:()=> {
        let a=$('#categories').jstree(true).get_json('#', { flat : true}).map(it=> { return it.id });
        return a;
    }

}


$(document).ready(function() {

    $('#new-category-edit').keydown(function(e) {
        if (e.keyCode === 13) {
            $("#new_category").click();
        }
    });

    app_categories.init({
        id_categories: '#categories'
    });

});

var app_categories = (function($) {
    var id_categories;
    
    function init(c) {
        id_categories = c.id_categories;
        qw.post("p.php?q=list", false,(r)=>{
            _initTree(r.data);
        }, "json", "Get categories list")
    }

    function _initTree(dt) {

        var category;
        $(id_categories).jstree({
                core: {
                    check_callback: true,
                    multiple: tree_multiple,
                    data: dt
                },
                checkbox: {
                    'deselect_all': true,
                    'three_state': false,
                },
                plugins: tree_plugins,
                contextmenu: {
                    'items': context_menu
                }
            }).bind('changed.jstree', function(e, data) {
                if(data.action==="delete_node") return;
                active_node = data.node;
                func.get(active_node.id);
                if (tree_multiple) {
                    let selected = data.node.state.selected;
                    let id = data.node.id;
                    var index = tree_items.indexOf(id);
                    if (index !== -1) tree_items.splice(index, 1);
                    if (selected)
                        tree_items.push(id);
                }

            }).bind('move_node.jstree', function(e, data) {
                func.move();
                //var params = {
                //    id: +data.node.id,
                //    old_parent: +data.old_parent,
                //    new_parent: +data.parent,
                //    old_position: +data.old_position,
                //    new_position: +data.position
                //};
                //_moveCategory(params);
            })
            .on('rename_node.jstree', function(e, data) {
                if(project.stopEvent) return;
                if(data.node.id.charAt(0)==="j"){
                    //active_node=data.node;
                    func.add(data.node,false)
                    //func.get(data.node.parent)
                } else {
                    qw.qs(`input[data-field="category"][data-iso="${project.lang.list[0].iso}"]`).value=data.node.text;
                    func.save();
                }
            })
    }


    function context_menu(node) {
        let items = $.jstree.defaults.contextmenu.items(node);
        delete items.remove;
        delete items.ccp;
        items['create'].label = 'Создать';
        items['rename'].label = 'Переименовать';
        items['Remove'] = {
            "separator_before": false,
            "separator_after": false,
            "label": "Удалить",
            "action": function(obj) {
                active_node=node;
                func.delete();
            }
        };
        return items;
    }

    return { init: init }

})(jQuery);