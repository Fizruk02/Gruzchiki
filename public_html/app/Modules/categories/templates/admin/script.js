var tree_items = [];
var active_node = false;
var tree_multiple = false;
var tree_plugins = ['dnd', 'wholerow', 'contextmenu'];
var fl = '';
console.log('bt_path = ' + bt_path);

const func = {
    get: (id) => {
        //qw.post("p.php?q=get", { id:id }, (res)=> {
        qw.post(bt_path + "/modules/categories/categories-admin/get", {id: id}, (res) => {
            qw.qsa('[data-seo-item="1"]').forEach((el) => el.value = "");
            qw.qsa('[data-field="category"]').forEach((el) => el.value = "");
            qw.qsa('[data-field="descr"]').forEach((el) => el.value = "");
            if (res.seo) res.seo.forEach((it) => {
                qw.qs(".seo_" + it.var).value = it.val;
            })
            fl = res.data.image;
            qw.lang.post(res.data.translates);
            qw.qs("#display").checked = res.data.display_in_the_link === "1";
            qw.qs("#content-file-1").style.backgroundImage = `url(${res.data.img === "" ? "/files/systems/no_photo_100_100.jpg" : "/" + res.data.img})`;
        }, 'json', "get data");
    },
    save: () => {
        if (!active_node) return toast("Сохранение", "Выберите категорию", "e");
        let tr_name = qw.lang.get('input[data-field="category"]');
        let tr_descr = qw.lang.get('textarea[data-field="descr"]');
        let name = JSON.parse(tr_name)[project.lang.list[0].iso];
        if (!name) return toast("Сохранение", "Введите название", "e");
        let seo = [
            {"var": "description", val: qw.qs(".seo_description").value},
            {"var": "slug", val: qw.qs(".seo_slug").value},
            {"var": "title", val: qw.qs(".seo_title").value},
        ]
        qw.post(bt_path + "/modules/categories/categories-admin/save", {
            id: active_node.id,
            seo: seo,
            image: fl,
            name: name,
            dl: qw.qs("#display").checked ? 1 : 0,
            tr: tr_name,
            tr_descr: tr_descr,
        }, (r) => {
            project.stopEvent = true;
            $("#categories").jstree('rename_node', active_node, name);
            project.stopEvent = false;
        }, 'json', "get data");
    },
    add: (node = false) => {
        let name = node ? node.text : qw.qs("#new-category-edit").value;
        if (!name) return toast("Создание", "Введите название", "e");
        qw.post(bt_path + "/modules/categories/categories-admin/add", {
            name: name,
            parent: active_node.id || 0
        }, (r) => {
            project.stopEvent = true;
            if (node) {
                active_node = node;
                $("#categories").jstree(true).set_id(node, r.id);
            } else
                $("#categories").jstree().create_node(active_node.id || "#", {"id": r.id, "text": name}, "last");

            project.stopEvent = false;
            qw.qs("#new-category-edit").value = "";
            func.get(r.id)
        }, 'json', "get data");

    },
    delete: async () => {
        if (!active_node) return alert('выберите категорию');
        alertmodcreate({'title': 'Удалить?', 'btnOk': 'Да', 'btnNo': 'Отмена'});
        let result = await alertmod;
        if (!result) return;

        qw.post(bt_path + "/modules/categories/categories-admin/dlt", {id: active_node.id}, () => {
            $("#categories").jstree().delete_node(active_node);
            active_node = false;
            qw.qsa('[data-field="category"]').forEach((el) => el.value = "");
        }, "json", "category delete");
    }

}


document.addEventListener("DOMContentLoaded", () => {
    qw.event(".seo_slug", "blur", (el) => {
        if (!active_node) {
            el.target.value = "";
            return toast("Сохранение", "Выберите категорию", "w");
        }
        let u = el.target.value;
        qw.post(bt_path + "/modules/categories/categories-admin/saveSlug", {id: active_node.id, url: u}, (r) => {
            el.target.value = r.slug;
        }, "json", "save slug")
    })

    $('#new-category-edit').keydown(function (e) {
        if (e.keyCode === 13) {
            $("#new_category").click();
        }
    });


    var app_categories = (function ($) {
        var id_categories;
        var ajaxUrl = bt_path + '/modules/categories/categories-admin/move';

        function init(c) {
            id_categories = c.id_categories;
            //qw.post("p.php?q=list", false,(r)=>{
            qw.post(bt_path + "/modules/categories/categories-admin/list", false, (r) => {
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
            }).bind('changed.jstree', function (e, data) {
                if (data.action === "delete_node") return;

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

            }).bind('move_node.jstree', function (e, data) {
                var params = {
                    id: +data.node.id,
                    old_parent: +data.old_parent,
                    new_parent: +data.parent,
                    old_position: +data.old_position,
                    new_position: +data.position
                };
                _moveCategory(params);
            })
                .on('rename_node.jstree', function (e, data) {
                    if (project.stopEvent) return;
                    if (data.node.id.charAt(0) === "j") {
                        func.add(data.node)
                    } else {
                        qw.qs(`input[data-field="category"][data-iso="${project.lang.list[0].iso}"]`).value = data.node.text;
                        func.save();
                    }
                })
        }


        function context_menu(node) {
            var tree = $('#cat_tree').jstree(true);
            var items = $.jstree.defaults.contextmenu.items(node);
            delete items.remove;
            delete items.ccp;
            items['create'].label = 'Создать';
            items['rename'].label = 'Переименовать';
            items['Remove'] = {
                "separator_before": false,
                "separator_after": false,
                "label": "Удалить",
                "action": function (obj) {
                    active_node = node;
                    func.delete();
                }
            };
            return items;
        }


        // Перемещение категории
        function _moveCategory(params) {
            var data = $.extend(params, {
                action: 'move_category'
            });

            $.ajax({
                url: ajaxUrl,
                data: data,
                dataType: 'json',
                success: function (resp) {
                    if (resp.code === 'success') {

                    } else {
                        console.error('Ошибка получения данных с сервера: ', resp.message);
                    }
                },
                error: function (error) {
                    console.error('Ошибка: ', error);
                }
            });
        }

        return {init: init}

    })(jQuery);


    app_categories.init({
        id_categories: '#categories'
    });

    document.getElementById('file-1').addEventListener("change", function (event) {
        if (!active_node) return toast('Ошибка', 'выберите категорию', 'e');

        $(`#form-file-1`).ajaxSubmit({
            type: 'POST',
            data: {filegroup: fl},
            //url: '/admin/upload/index.php',
            url: bt_path + '/images/load',
            success: function (data) {
                var res = JSON.parse(data);
                $(`#content-file-1`).css('background-image', `url(${res.preview})`);
                fl = res.id_group;
                console.log(fl);
            }
        });
    }, false);

})