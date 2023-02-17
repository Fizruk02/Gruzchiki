$(function () {
    $("[kbrow]").each(function (i, elem) {
        sort(elem);
    });
});

var keyboard = false;

function kbselect(id) {
    keyboard = false;
    $('#kbcontainer').html("");
    qw.post("p.php?q=kbGet", {id: id}, (res)=> {
        t = '';
        res.data.buttons.forEach(function (item, i) {
            t += kbTemplate(item);
        });
        t += kbTemplate();
        keyboard = id;
        $('[keyboard]').removeClass('active');
        $(`[keyboard="${id}"]`).addClass('active');
        $('#kbcontainer').html(t);
        $("[kbrow]").each(function (i, elem) {
            sort(elem);
        });
    }, "json", "kbselect");
}


function kbTemplate(keysarr = []) {
    let keys = '';
    keysarr.forEach(function (it) {
        keys += keyTemplate(it);
    });
    return `<div class="row px-3 justify-content-md-center  row-cols-8" kbrow>${keys}</div>`;
}

function keyTemplate(t) {
    return `<div class="col p-3" kbpanel>
                      <button type="button" style="" class="btn btn-outline-info w-100" old="${escapeHtml(t.text)}" onclick="renameKey(this)">${escapeHtml(t.text)}</button>
                      <i style="position: absolute;top: 0px; right: 0;cursor:pointer" visible="${t.visible}" class="text-info bi ${t.visible == 1 ? 'bi-eye' : 'bi-eye-slash'}" onclick="vis(this)"></i>


                  </div>`
}

async function newkey() {
    if (!keyboard)
        return toast('Добавление', 'Клавиатура не выбрана', 'warning');

    promptmodcreate({'title': 'Текст кнопки', 'btnOk': 'Ok', 'btnNo': 'Отмена'}, [{}]);
    let result = await promptmod;
    if (!result) return;
    $('[kbrow]').last().append(keyTemplate({text: result[0], visible: "1"}));

    saveKb()
}

async function renameKey(th) {
    promptmodcreate({
        'title': 'Текст кнопки',
        'btnOk': 'Сохранить',
        'btnNo': 'Отмена'
    }, [{value: $(th).html()}]);
    let result = await promptmod;
    if (!result) return;
    $(th).html(result[0]);
    saveKb();
    if (result[0] === "") $(th).parent().remove();
}

function vis(th) {
    if ($(th).attr('visible') === "1") {
        $(th).removeClass('bi-eye');
        $(th).addClass('bi-eye-slash');
        $(th).attr('visible', "0");
    } else {
        $(th).removeClass('bi-eye-slash');
        $(th).addClass('bi-eye');
        $(th).attr('visible', "1");
    }
    saveKb();
}

function saveKb() {
    let kb = [];
    $("[kbrow]").each(function (i, elem) {
        let row = [];
        $(elem).find('[kbpanel]').each(function (q, panel) {
            let btn = $(panel).find('button').html();
            let old = $(panel).find('button').attr('old');
            let vis = $(panel).find('[visible]').attr('visible');
            $(panel).find('button').attr('old', btn)
            if (btn != "") row.push({val: btn, old: old, vis: vis});
        });
        if (row.length > 0) kb.push(row);
    });
    if ($('[kbrow]').last().children().length > 0) {
        $('#kbcontainer').append(kbTemplate());
        sort($('[kbrow]').last());
    }
    qw.post("p.php?q=kbSave", {id: keyboard, kb: JSON.stringify(kb)}, (r)=> { }, "json", "saveKb");
}

function sort(el) {
    $(el).sortable({
        connectWith: "[kbrow]",
        revert: 100,
        //handle: "[kbpanel]",
        stop: saveKb

    }).disableSelection();
}
function escapeHtml(text) {
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}