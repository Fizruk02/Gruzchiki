
var startDate,endDate,filter=false;


$('[filter]').on('click', function(){
    //answersRange( $(this).attr('filter'));
    console.log($(this).attr('filter'));
})

$(document).ready(function (){
    category="";
    startDate=moment("1986-09-19");
    endDate=moment();
    list();
});

function range(s,e){
    startDate=s;
    endDate=e;
    list();
}


function list(){
    
    $.post("post/answersList/", { startDate:startDate.format('YYYY-MM-DD'), endDate:endDate.format('YYYY-MM-DD'),a:JSON.stringify(filter)  })
     .done(function(data) {
     var res = jQuery.parseJSON(data);
    if(res.success=='ok'){
        $('#table-answers').attr('display', 'whatchat-answers');
        //if(filter.b!==undefined) $('#table-answers').attr('display', 'whatchat-answers-'+filter.b+'-'+filter.v);
        appTable.init({
             table:table
            ,list:res.data
            ,template:'answersTmplt'
            ,listStart:0
            ,search:''
            ,limit:10
            ,range:{
                 start:startDate
                ,end:endDate
                ,func:range
            }
            ,header:{
                buttons:[
                    '<button class="btn btn-outline-secondary ms-2" type="button" onclick="$(\'#filterModal\').modal(\'show\')">Фильтр</button>'
                ]
            }
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
    	v += `<a href="#" class="list-group-item list-group-item-action" onclick="filter={b:'${data.blockId}',v:'${item.answer}'};list();$('#filterModal').modal('hide');">${item.answer} <span class="badge bg-secondary rounded-pill float-end">${item.count}</span></a>`;
    });
    
return `<div class="p-2 bg-white shadow rounded mb-3">
    <span class="fs-5 ms-2">${data.script}: ${data.block}</span>
    <div style="max-height:200px;overflow:auto;">
        <div class="list-group list-group-flush">${v}</div>
    </div>
</div>`;
}




function answersTmplt(data){
    return `<tr class="rl" usrow=${prep(data.user_id)}>
               <td>${prep(data.date)}</td>
               <td>${prep(data.user_id)}</td>
               <td>${prep(data.user_name)}</td>
               <td>${prep(data.script)}</td>
               <td>${prep(data.block)}</td>
               <td>${prep(data.answer)}</td>
            </tr>`;
}

function prep(v){return v===undefined||v===false||v===NaN||v===null?'':v;}





















