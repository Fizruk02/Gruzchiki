function updateFilegroup(id, group){
    filegroup=group;
}
$(document).ready(function () {

	$('span[action-type="get-var"]').click(function () {
		insertText('input-form', $(this).html());
	});


$('#file').each(function () {
	var $input = $(this);
	$input.on('change', function (element) {

		$("#spinform").show();
		var fileName = '';
		if (element.target.value) fileName = element.target.value.split('\\').pop();
		if (fileName) {


			$('#js-form').ajaxSubmit({
				type: 'POST',
				data: {},
				url: 'loadFromExcel.php',
				success: function (data) {

				$("#spinform").hide();

				var res = jQuery.parseJSON(data);
            	if(res.success!=='ok')
            		return toast('Ошибка', res.error?res.error:'error', 'error');
				let label_form = '';
				let header = `<tr><th style="width:40px"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" checked onclick="check_all($(this).is(':checked')?'1':'0')" id="check-all"><label class="custom-control-label" for="check-all"></label></div></th>`;
				for (let i = 0; i < (res.header).length; i++) {
				    if(res.header[i]!=='id'&&res.header[i]!=='check')
					header += `<th>${res.header[i]}</th>`;
					label_form += `<span class="badge badge-info pointer mr-1" action-type="get-var" onclick="insertText('input-form', $(this).html())">#${res.header[i]}#</span>`;
				}

				header += '</tr>';
				$('#label-form').html(label_form);
				$('#table-mailing-head').html(header);
                    appTable.init({
                         table:'table-mailing'
                        ,list:res.data
                        ,template:'customTmplt'
                        ,listStart:0
                        ,search:''
                        ,limit:10
                    });
				}
			});


		}
		//  else $label.removeClass('has-file').html(labelVal);
	});
});



getData();

});



function create() {
	let name = $('#mailing-name').val();
	let date = $('#date-begin').val()
	let text = $('#input-form').val();
	let mailing_variants = $('#input-variants-form').val();
	let mailing_date_end = $('#date-end-mailing').val();
	let mailing_blockchains = [];

	var table = document.getElementById('table-data');

	let arr = [];
    
    appTable.data('table-mailing').forEach(function(item, i) {
    	if(item.check=="1"||item.check===undefined) arr.push(item);
    });

	if (!name) name = '';
	if (!date) return toast('Рассылка', 'установите дату начала рассылки', 'error');
	if (!text) return toast('Рассылка', 'введите текст сообщения', 'error');




        let poll_data=[]
		ds = $('#poll-ds').is(':checked') ?1:0,
		plcustom = $('#poll-custom').is(':checked') ?1:0,
		plcol=$('#poll-cols-edit').val();

        $("[poll-item]").each(function(i,elem) {
            if($(elem).val()!=="") poll_data.push($(elem).val());
        });
        
        let txt,cb,inl=[];
        $("[inlkbrow]").each(function(i,elem) {
            txt=$(elem).find('[text]').val();
            cb=$(elem).find('[cb]').val();
            if(txt!==""&&cb!=="")inl.push([txt,cb])
        });
        let a=[];
        weekdays.forEach(function(f, i) {
        	if(f!=='0'&&f!==0) a.push(i+1);
        });
        let wkd=a.join('-');
        
        let entfs={};
        [0,1,2,3,4,5,6].forEach(function(i) {
        	entfs[i]=$('#EvNtf'+i).is(':checked') ?1:0
        });

	$.post("p.php?q=set", {
		address: JSON.stringify(arr),
		name: name,
		date: date,
		text: text,
		repeat:repeat,
		weekdays:wkd,
		interval:interval,
		filter:filter,
		filegroup: filegroup,
		inline:JSON.stringify(inl),
		poll_data:JSON.stringify(poll_data),
		ds:ds,
		plcol:plcol,
		plcustom:plcustom,
    	event: {
        	status:eventstatus,
            end: q("#eventEnd").value,
            ntf: $('#eventCheckNotify').is(':checked') ?1:0,
			endtype:eventendtype,
			hoursto:qw.qs("#evNtfHoursTo").value,
            ntftime: q("#evNtfTime").value,
            ntfwkdays: entfs,
            ntftext:q("#eventNotifyText").value,
    	}
	}).done(
		function (data) {

		var res = jQuery.parseJSON(data);
    	if(res.success!=='ok')
    		return toast('Ошибка', res.err?res.err:'error', 'error');
			document.location.href = "list.php";
		});

}


function customTmplt( d ){
let rows = '';    
for(i in d){if(i!=='id'&& i!=='check')rows+=`<td>${d[i]}</td>`;}
 return `<tr class="rl">
          <td class="border-left-0 text-center" cl="1" style="width:40px" id="tl-${d.id}">
             <div class="custom-control custom-switch">
                <input type="checkbox" action-type="contact-check" class="custom-control-input" id="check-${d.id}" ${d.check=="1"?'checked':''} onchange="checkrow(${d.id},this)">
                <label class="custom-control-label" for="check-${d.id}"></label>
             </div>
          </td>
          ${rows}
       </tr>`;
}

function filedelete(fid, butpar) {
	$.post("/admin/functions/taskDeleteFile.php", {
		gid: filegroup,
		fid: fid
	})
	.done(function (data) {
		var res = jQuery.parseJSON(data);
	if(res.success!==1)
		return toast('Ошибка', res.err?res.err:'error', 'error');

	butpar.hide(100);
	filegroup = res.gid;
	});
}

function getData() {
	list("");
    $('#loadbtn').html(appUpload.form({ id:"1", uploadFunc:'updateFilegroup', deleteFunc:'updateFilegroup',  group:"" }));
    $('#loadform').html(appUpload.container({ id:"1"}));
}




function insertText(id, text) {
	//ищем элемент по id
	var txtarea = document.getElementById(id);
	//ищем первое положение выделенного символа
	var start = txtarea.selectionStart;
	//ищем последнее положение выделенного символа
	var end = txtarea.selectionEnd;
	// текст до + вставка + текст после (если этот код не работает, значит у вас несколько id)
	var finText = txtarea.value.substring(0, start) + text + txtarea.value.substring(end);
	// подмена значения
	txtarea.value = finText;
	// возвращаем фокус на элемент
	txtarea.focus();
	// возвращаем курсор на место - учитываем выделили ли текст или просто курсор поставили
	txtarea.selectionEnd = (start == end) ? (end + text.length) : end;
}


function filterSelect(id){
    $('#filterBtn').removeClass('btn-outline-warning');
    $('#filterBtn').addClass('btn-outline-secondary'); 
    filter=id;
    list();
}


function list(){
    $.post("p.php?q=getList", { filter:filter }).done(function(data) {
    var res = jQuery.parseJSON(data);
    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');

    if(res.data && res.data.length>0){
        
	let label_form = '';
	let header = `<tr><th style="width:40px"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" checked onclick="check_all($(this).is(':checked')?'1':'0')" id="check-all"><label class="custom-control-label" for="check-all"></label></div></th>`;
    for(col in res.data[0]) if(col!=="check"&&col!=="id") {

		header += `<th>${col}</th>`;
		label_form += `<span class="badge badge-info pointer mr-1" action-type="get-var" onclick="insertText('input-form', $(this).html())">#${col}#</span>`;
	}

	header += '</tr>';
	$('#label-form').html(label_form);
	$('#table-mailing-head').html(header);
        
        
        

    }

    appTable.init({
         table:'table-mailing'
        ,list:res.data
        ,template:'mailingTmplt'
        ,listStart:0
        ,search:''
        ,limit:10
    });
    });
    
}
function getIndex(vr,vl,arr){
let x=false;
arr.forEach(function(item, i) {
if(item[vr]==vl) return x=i;
});
return x;
}
function checkrow(id,th){
    filter=-1;
    if(repeat!==""){
        $('#filterBtn').removeClass('btn-outline-secondary');
        $('#filterBtn').addClass('btn-outline-warning');
    }
    $('#filterBtn').html('фильтр не выбран');
    appTable.update('table-mailing','id',id,'check',$(th).is(':checked')?"1":"0");
}
function check_all(st){
	event.stopPropagation();
	$(`[action-type='contact-check']`).prop('checked', st=='1'?true:false);
    appTable.data('table-mailing').forEach(function(item, i) {
	    appTable.update('table-mailing','id',item.id,'check',st);
    });
}
function mailingTmplt( d ){
    let cells = "";
    for(col in d) if(col!=="check"&&col!=="id"&&col!=="line_number") cells+=`<td>${d[col]}</td>`;
    
    if(d.check===1||d.check===undefined) d.check = "1";
    
    return `<tr class="rl">
          <td class="border-left-0 text-center" cl="1" style="width:40px" id="tl-${d.id}">
             <div class="custom-control custom-switch">
                <input type="checkbox" action-type="contact-check" class="custom-control-input" id="check-${d.id}" ${d.check==="1"?'checked':''} onchange="checkrow(${d.id},this)">
                <label class="custom-control-label" for="check-${d.id}"></label>
             </div>
          </td>
          ${cells}
       </tr>`;
}

















