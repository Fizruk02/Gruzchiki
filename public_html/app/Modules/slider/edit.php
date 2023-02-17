<?php 
use system\lib\Db;
$db=DB::getInstance();
//$contentPages = $db->arrayQuery(''); ?>
<head>
<link href="//cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
<!--  <script src="/admin/js/jquery.min.js"></script> -->
<script src="//b2bot.ru/components/qw.js"></script>
<script src="//b2bot.ru/components/upload/js.js"></script>
<!-- <script src="//b2bot.ru/components/toast.js"></script> -->
<script src="//cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .item_row {
        display: grid;
        grid-template-columns: 103px 1fr 15px;
        gap: 10px;
    }
    #slides {
        display: grid;
        grid-auto-columns: auto;
        grid-template-columns: repeat(auto-fit, minmax(300px,1fr));
        gap: 6px;
    }

    }
</style>

</head>
<body>



<div class="p-1">

<!-- <div class="input-group mb-3">
    <span class="input-group-text" id="basic-addon1">Title</span>
  <input type="text" class="form-control" placeholder="" id="title">
</div> -->

<div class="form-check">
  <input class="form-check-input" type="radio" name="r-type" id="r-type-1" value="1">
  <label class="form-check-label" for="r-type-1">
    С одним активным слайдом без зацикливания
  </label>
</div>

<div class="ms-2 ps-2 border-start border-primary">
<button type="button" class="btn btn-light btn-sm ms-1" onclick="cl.add()">Слайды <i class="bi bi-plus"></i></button>
<div id="slides"></div>
</div>
		

		
</div>

</body>

<script>
var frameid,
slidesImg={};
var _translatedFields={
		 //"title":{"name":"Title"},

		 }

var cl={
		add:(t,c)=>{
				qw.qs("#slides").insertAdjacentHTML('beforeend', cl.tmpl(t||"",c||""));
				
				resize();
		},
		tmpl:(t,img)=>{ 
				let num=qw.qs("#slides").childElementCount;
				let cimg=flsingle.create(img,"cbimg");
				return `<div class="mb-1 dt-slides border rounded p-1" style="width: fit-content" data-num=${num}>

	<div class="item_row">
        <div class="border-end pe-1">${cimg}</div>
        <div class="">
            <span>подпись</span>
            <input type="text" class="form-control title" placeholder="">
        </div>
        <i class="bi bi-x text-danger"  onclick="this.parentNode.parentNode.remove()"></i>

    </div>
	
<div>`
		},
}

var flimg="";
function cbimg(data){
    let p=data.elem.closest(".dt-slides");

	slidesImg[p.dataset.num]=data.file;

}

function _getData( res ){
    let data=res.data;
    //qw.qs("#title").value=data.title||"";
	qw.qs(`input[name="r-type"][value="${data.type}"]`).checked=true;
	
    if(data.slides&&Array.isArray(data.slides))data.slides.forEach((it,i)=>{
        slidesImg[i]=it.img;
		cl.add("",it.img);
    })

    
}

function _sendData(){
    
    let slides=[];
    qw.qsa(".dt-slides").forEach((it,i)=>{
    		//let t=it.querySelector("textarea").value;
    		slides.push({ img:slidesImg[i]||"" })
    })
    
    let res =
    {
         _translatedFields:_translatedFields
        //,title    : qw.qs("#title").value
		,type: qw.qs('input[name="r-type"]:checked').value
		,slides:slides
    }
    return res;
}
function resize(){window.parent.postMessage({ method:"blockResize", id:frameid }, "*");}
</script>
