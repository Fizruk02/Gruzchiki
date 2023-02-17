/**
    appUpload.form({
         id:id,
        ,classes:''//'btn btn-outline-secondary btn-sm  m-0'
        ,group:fileGroup // (int) id of the file group from the files table
        ,style: 'width:100%;border:2px;' // (str) btn styles
        
        ,uploadFunc:'uploadFile' // (function)function on successful download. The group id is passed
        // uploadFunc(id form, groupFilesId);
        ,deleteFunc:'deleteFile' // (function)function when deleting a file. The group id is passed
        // deleteFunc(id form, groupFilesId
        
        //,(uploadFunc / deleteFunc) OR callback:'callback'
        // callback(p={}){    if(p.act==="remove"){};if(p.act==="upload"){}}
    })
 
 
    appUpload.container({
         id:id
        ,files:files // (array) [{id_group: "32", preview: "/files/loaded/file.jpg", file: "/files/loaded/fileXL.jpg", fileid: "78", type: "img", ext:"jpg" }] //ext - lower case
    })


	SINGLEFILE
 	$("#fdiv").html(flsingle.create("","callbackfunction"));
 
 	function callbackfunction(data){
 		data: {success: "ok"
 		type: "img"
		ext: "png"
		file: ""
		preview: ""
		name: "new.png"
		src: "old.png
		warning: ""
 		elem:input
 		}
 	}

 */
document.addEventListener("DOMContentLoaded", ()=>{
    if(appUpload.loadLibStatus) return;
    appUpload.loadLibStatus=1;
    let head=document.head.innerHTML,body=document.body.innerHTML,h="";
    [
        ['components/upload/style.css', '<link rel="stylesheet" href="//b2bot.ru/components/upload/style.css" type="text/css"/>'],
        ['bootstrapcdn.com/font-awesome/4.5.0','<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" type="text/css">'],
    ].forEach((it)=>{
        if( head.indexOf(it[0])===-1&&body.indexOf(it[0])===-1) h += it[1];
    })
    document.head.innerHTML+=h;
//if(typeof qw == 'undefined') document.write('<script src="//b2bot.ru/components/qw.js"></script>');
if(typeof toast == 'undefined'){
        var script = document.createElement('script');
        script.src = 'https://b2bot.ru/components/toast.js';
        document.head.appendChild(script);
        console.log(script);
}

//if(typeof qw == 'undefined') document.write('<script src="//b2bot.ru/components/qw.js"></script>');
//if(typeof toast == 'undefined') document.write('<script src="//b2bot.ru/components/toast.js"></script>');

    

});
var appUpload ={
    loadLibStatus:false,
    upload:(th)=> {
        let $inp = $(th),
        $lbl = $inp.next('[jsLabelFile]'),
        labelVal = $lbl.html(),
        $prnt = $(th).closest('[fl-container]');
        let elId = $prnt.attr('fl-container');
        if($inp[0].files[0]===undefined) return;
        
    	let size = ($inp[0].files[0].size / 1024 / 1024).toFixed(1);
    	if(size>50)
    	    return toast('Ошибка',  "Размер файла "+size+"мб, телеграм разрешает отправлять файлы до 50мб", 'error');    
        let fileName = $inp[0].files[0].name;
        $prnt.find("[fl-spinform]").show();$prnt.find("[fl-fileloadform]").hide();
        if(!fileName){
            $lbl.removeClass('has-file').html(labelVal);
            $prnt.find("[fl-spinform]").hide();$prnt.find("[fl-fileloadform]").show();
            return toast('Ошибка', 'Не удалось получить файл', 'error');
        }
    
    
    	let formData = new FormData();
        let fgroup=$prnt.attr('group')
    	formData.append('file', $inp[0].files[0]);
    	formData.append('group', fgroup==="false"?0:fgroup);
    
    	$("#fl-icon-default").hide();
    	$("#fl-icon-success").hide();
    	$("#fl-icon-error").hide();
        $.ajax({
    		type: "POST",
    		url: '/admin/upload/',
    		cache: false,
    		contentType: false,
    		processData: false,
    		data: formData,
    		xhr: function(){
    			var xhr = $.ajaxSettings.xhr();
    			xhr.upload.addEventListener('progress', function(evt){
    				if(evt.lengthComputable) {
    					$(".sr-progress").html(Math.ceil(evt.loaded / evt.total * 100) + '%');
    				}
    			}, false);
    			return xhr;
    		},
        	success: function(data){
    			$lbl.find('[fl-filename]').html(fileName);
        		$prnt.find("[fl-spinform]").hide();$prnt.find("[fl-fileloadform]").show();
        		var res = jQuery.parseJSON(data);
            	if(res.success!=='ok') {
            	    $("#fl-icon-error").show();
            	    return toast('Ошибка', res.err, 'e');
            	}
            	$("#fl-icon-success").show();	
    
            	if(((fnc = $prnt.attr('uploadFunc'))&&fnc!=="")||((fnc = $prnt.attr('callback'))&&fnc!=="")){
            	    if(window[fnc].length==1) window[fnc]({act:'upload',elId:$prnt.attr('fl-container'),file:res});
            	    if(window[fnc].length==2) window[fnc]($prnt.attr('fl-container'), res.id_group);
            	}
            	
                if(res.warning!=="" && res.warning!==undefined)
                    toast('Внимание', res.warning, 'warning');
                
                $prnt.attr('group', res.id_group);
                if(res.type=='img')
        		    $(`[fl-imagespanel="${elId}"]`).append(appUpload.pr(res));
                if(res.type=='doc')
        		    $(`[fl-imagespanel="${elId}"]`).append(appUpload.prdoc(res));
                if(res.type=='video')
        		    $(`[fl-imagespanel="${elId}"]`).append(appUpload.prv(res));
        		$("#fl-img-" + res.fileid).show(100);
        	},
    		error: function(xhr, textStatus, errorThrown) {
    			$("#fl-icon-error").show();
    			$prnt.find("[fl-spinform]").hide();$prnt.find("[fl-fileloadform]").show();
    			$lbl.addClass('has-file').find('[fl-filename]').html(fileName);
    		},
    	});
    },
    form:(par)=> {
        let id=Math.floor(Math.random() * 100000);
        let _class = par.classes?par.classes:'btn btn-outline-secondary btn-sm  m-0';
        
        return `<div fl-container="${par.id}" group="${par.group===undefined?"":par.group}" uploadFunc="${par.uploadFunc===undefined?"":par.uploadFunc}" deleteFunc="${par.deleteFunc===undefined?"":par.deleteFunc}" callback="${par.callback===undefined?"":par.callback}">
            <form method="post" style="margin: 0;"  fl-form>
               <div class="file-load-form">
                  <div fl-fileloadform style="margin: -2px !IMPORTANT;">
                     <input type="file" id="fl-file-${id}"  name="file" class="fl-input-file" ${par.accept&&par.accept!==false?`accept="${par.accept}"`:""} onchange="appUpload.upload(this)">
                     <label for="fl-file-${id}" class="${_class}" style="max-width: 200px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;${par.style===undefined?'':par.style}" jsLabelFile>
        			 <i class="bi bi-download" id="fl-icon-default"></i>
                     <i class="bi bi-check-lg text-success" style="display: none" id="fl-icon-success"></i>
                     <i class="bi bi-exclamation-circle-fill text-danger" style="display: none" id="fl-icon-error"></i>
                     <span fl-filename>Загрузить файл</span>
                     </label>
                  </div>
                  <div style="text-align: center;display:none;" fl-spinform>
        			<div style="display: -webkit-inline-box;">
        				<div class="spinner-border" role="status" >
        					<span class="sr-only"></span> 
        				</div>
        				<div class="sr-progress ms-1" style="font-size: 16pt;"></div>
        			</div>
        
                  </div>
               </div>
            </form>
        </div>`;
    },
    container:(par)=>{
        let f='';
        if(par.files!==undefined)
        par.files.forEach(function(it) {
        if(it.id_group!=="0"&&it.id_group!=="false"&&it.id_group){
        	if(it.type=='img') f+= appUpload.pr(it);
        	if(it.type=='doc') f+= appUpload.prdoc(it);
        	if(it.type=='video') f+= appUpload.prv(it);
        }
        });
        return `<div class="container mx-1 overflow-auto" style="width:100%;">
        <div class="row" fl-imagespanel="${par.id}"l>
        ${f}
        </div>
        </div>`;
    },
    
    remove:(fid)=> {
        let elId = $('#fl-img-'+fid).closest('[fl-imagespanel]').attr('fl-imagespanel');
        let parent = $(`[fl-container="${elId}"]`);
        $.post("/admin/upload/delete.php", {fid:fid,gid:$(parent).attr('group')},function(res) {
            if(res.success!=='ok') return toast('Error', res.err, 'e');
        	if(((fnc = $(parent).attr('deleteFunc'))&&fnc!=="")||((fnc = $(parent).attr('callback'))&&fnc!=="")){
        	    if(window[fnc].length==1) window[fnc]({act:'remove',elId:$(parent).attr('fl-container'),file:{id_group:res.gid,fileid:fid}});
        	    if(window[fnc].length==2) window[fnc]($(parent).attr('fl-container'), res.gid);
        	}   
            $('#fl-img-'+fid).hide(100);
            $(parent).attr('group', res.gid); 
        }, "json");
    },
    pr:(d)=>{
    return appUpload.hv(d.fileid,`<img ttype="minimized" src="${d.preview}" data-medium="${d.file}" onclick="window.open('${d.file}', '_blank');" class="rounded float-left" style="float: none!important;margin-top: 3px;max-width:128px;cursor:pointer;">`);
    },
    prdoc:(d)=>{
    return appUpload.hv(d.fileid,`<img ttype="minimized" src="//b2bot.ru/components/upload/icons/${d.ext}.png" onError="this.src='//b2bot.ru/components/upload/icons/empty.png'" onclick="window.open('${d.file}', '_blank');" class="rounded float-left" style="cursor:pointer;float: none!important;max-width:128px;margin-top: 3px;">`);
    },
    prv:(d)=>{
    return appUpload.hv(d.fileid,`<div class="embed-responsive embed-responsive-1by1"><iframe class="embed-responsive-item" src="${d.file}" allowfullscreen style="float: none!important;width:128px;margin-top: 3px;"></iframe></div>`);
    },
    hv:(id,b)=>{
    return `<div class="col-sm" style="flex-grow:0;width:fit-content;padding: 1px;text-align: center;" id="fl-img-${id}">
    <button act="filedelete" type="button" style='width:128px;display:block;' class="btn btn-danger mt-1" onclick="appUpload.remove('${id}')">удалить</button>${b}</div>`; 
    }
    
}




var flsingle = {
	create:(i,f)=>{
	    if(i){
	        let ext=i.split('.').pop();
	        if(["jpg","jpeg", "jfif", "jp2", "png", "gif", "bmp", "dib", "rle", "webp"].indexOf(ext)===-1)
	            i="//b2bot.ru/components/upload/icons/"+ext+".png";
	    }
	    
		return `<div class="file-load-single-form"><input data-func="${f}" type="file" name="file" onchange="flsingle.load(this)"> <div class="preview" style="background-image: url(${i||"/files/systems/no_photo_100_100.jpg"})"></div></div>`;
	},
	load:(th)=>{
		var fd = new FormData,f = th.files[0],o = new XMLHttpRequest();
		fd.append("file", f, f.name);
		o.open("POST", '/admin/upload/single.php'), o.send(fd), o.onreadystatechange = function () {
			if (4 === o.readyState && 200 === o.status) {
				let j=JSON.parse(o.responseText);
				if(!j.success) return toast("Загрузка", j.err,"e");
				toast("Загрузка", "Файл "+f.name+" загружен");
				th.parentNode.querySelector(".preview").style.backgroundImage=`url(${j.preview})`;
				let  fnc=th.dataset.func;j['elem']=th;
				if(fnc&&window[fnc].length==1)window[fnc](j);
			}
		}
	}
}
