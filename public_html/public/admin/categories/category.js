var app_categories = {
    init:(s)=> {
        $.post("/admin/categories/p.php?q=list", {},(r)=>{
            $(s.id_categories).jstree({
                core: {
                    multiple:1, data: r.data,
                },
            }).on('loaded.jstree', function(e) {
                s.selected&&Array.isArray(s.selected)&&s.selected.forEach((i)=>{
                    $(e.target).jstree("select_node", `#${i}`);
                })
            });
        }, "json")

    }
}
        
