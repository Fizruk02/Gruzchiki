// 'use strict';





var answersLoad = false;
var answersPar = {};
var userid = false;

$('body').on('click', '[usrow]', function(){

})




$( document ).ready(function() {
$('#answersRange').daterangepicker(
  {
    ranges   : {
      'Сегодня'       : [moment(), moment()],
      'Вчера'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Последние 7 дней' : [moment().subtract(6, 'days'), moment()],
      'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
      'Этот месяц'  : [moment().startOf('month'), moment().endOf('month')],
      'Предыдущий месяц'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    startDate: moment().subtract(29, 'days'),
    endDate  : moment()
  },
  function (start, end) {
    answersRange(start, end);
  });
  




answersRange($('#answersRange').data('daterangepicker').startDate, $('#answersRange').data('daterangepicker').endDate);

});


$('[filter]').on('click', function(){
    answersRange($('#answersRange').data('daterangepicker').startDate, $('#answersRange').data('daterangepicker').endDate, $(this).attr('filter'));
})


function answersRange(startDate=false, endDate=false, a={}){
    $('#filterModal').modal('hide');
    if(startDate===false) startDate=$('#answersRange').data('daterangepicker').startDate;
    if(endDate===false) endDate=$('#answersRange').data('daterangepicker').endDate;
    $.post("/api/answers/_list/", { startDate:startDate.format('YYYY-MM-DD'), endDate:endDate.format('YYYY-MM-DD'),a:JSON.stringify(a)  })
     .done(function(data) {
     var res = jQuery.parseJSON(data);
    if(res.success=='ok'){
        $('#table-answers').attr('display', 'whatchat-answers');
        if(a.b!==undefined) $('#table-answers').attr('display', 'whatchat-answers-'+a.b+'-'+a.v);
        appTable.init({
             table:'table-answers'
            ,list:res.data
            ,template:'answersTmplt'
            ,listStart:0
            ,search:''
        });
        let filter = '';
        res.groups.forEach(function(item) {
            item.forEach(function(subitem) {
                filter += filterTmplt(subitem);
            });
        	
        });

        $('#filterbody').html(filter);
        
        
        
    }
    else
        toast('Ошибка', res.err, 'error');
        
    
     });
    
}


function filterTmplt(data){
    let v = '';
    data.list.forEach(function(item) {
    	v += `<a href="#" class="list-group-item list-group-item-action" onclick="answersRange(false,false,{b:'${data.blockId}',v:'${item.answer}'})">${item.answer} <span class="badge bg-secondary rounded-pill float-end">${item.count}</span></a>`;
    });
    
return `<div class="p-2 bg-white shadow rounded mb-3">
    <span class="fs-5 ms-2">${data.script}: ${data.block}</span>
    <div style="max-height:200px;overflow:auto;">
        <div class="list-group list-group-flush">${v}</div>
    </div>
</div>`;
}




function answersTmplt(data){
    let file = data.file;
    if(file!==''){
        file = `<img class="openimg" style="max-width:40px;max-height:40px;" src="/${file}">`;
    }
    let source = data.src;
    switch(data.src){
        case 'wh':
            source = '<i class="bi bi-whatsapp"></i>';
        break;
        case 'tg':
            source = '<i class="bi bi-telegram"></i>';
        break;
    }
    return `<tr class="rl" usrow=${data.user_id}>
               <td>${source}</td>
               <td>${data.date}</td>
               <td>${data.user_id}</td>
               <td>${data.user_name}</td>
               <td>${data.script}</td>
               <td>${data.block}</td>
               <td>${data.answer}</td>
            </tr>`;
}























