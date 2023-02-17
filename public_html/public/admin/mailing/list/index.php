<div class="container mt-2" tableform>
   <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
      <table class="table table-hover table-sm" id="table-template" style="min-width: 800px">
         <thead class="thead-dark">
            <tr>
                <th>название</th>
                <th>текст</th>
                <th>дата начала</th>
                <th>дата окончания события</th>
                <th style="width:150px">статус</th>
                <th style="width:220px"></th>
            </tr>
         </thead>
      </table>
   </div>
</div>



<div class="modal fade" id="modal-poll" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
       <div class="modal-header">
          <h5 class="modal-title">Результаты опроса</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">

       <div class="row">
           <div class="col border-end">
               
               
                <div class="container mt-2" tableform>
                   <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
                      <table class="table table-hover table-sm" id="table-poll">
                         <thead class="">
                            <tr>
                                <th>user</th>
                                <th>answer</th>
                                <!-- <th style="width:50px"></th> -->
                            </tr>
                         </thead>
                      </table>
                   </div>
                </div>
               
               
               
           </div>
           <div class="col-3 border-end" id="pollChartArea">
    
           </div>
       </div>
          
       </div>

    </div>
 </div>
</div>


<div class="modal fade" id="users_modal" tabindex="-1" aria-labelledby="users_modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="users_modalLabel">Получатели</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div class="container mt-2" tableform>
           <div n-div='4' style="overflow-x: auto;width:100%;" tablecontainer>
              <table class="table table-hover table-sm" id="table-users">
                 <thead class="thead-dark">
                    <tr>
                          <th style="width: 60px;">#</th>
                          <th style="width: 160px;">дата</th>
                          <th style="width: 160px;">пользователь</th>
                          <th style="min-width: 100px;">статус</th>
                    </tr>
                 </thead>
              </table>
           </div>
        </div>

      </div>
    </div>
  </div>
</div>



<script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/3.4.1/chart.min.js"></script>
<script src="/admin/mailing/mailingjs.js?<?=uniqid()?>"></script>
<script>
var table = 'table-template';
$(document).ready(function (){
    list("");
});

function list(group){
    $.post("p.php?q=getMailingList", { group:group }, function(res) {
    
    if(!res.success) return toast('Ошибка', res.err, "e");

    appTable.init({
         table:table
        ,list:res.data
        ,template:'template'
        ,listStart:0
        ,search:''
        ,limit:10
        ,header:{
            buttons:[
                '<button class="btn btn-outline-secondary" type="button" onclick="document.location.href = \'index.php\';">Создать</button>'
            ]
        }
    });
    }, "json");
}

async function add(){
    promptmodcreate({'title':'Добавление','btnOk':'Сохранить','btnNo':'Отмена',size:'xl'},
    [{label:'username'},{label:'first name'}]); 
    let result = await promptmod; if(!result) return;
    $.post("p.php?q=add", { username:result[0],first_name:result[1] }, function(res) {
    if(!res.success) return toast('Ошибка', res.err, "e");
        appTable.insert(table, res.data)
    }, "json");
}

async function rmove(id){
    alertmodcreate({'title':'Удалить?', 'btnOk':'Да','btnNo':'Отмена'});
    let result = await alertmod; if(!result) return;
    $.post("p.php?q=remove", { id:id }, function(res) {
    if(!res.success) return toast('Ошибка', res.err, "e");
        appTable.rmove(table, "id", id)
    }, "json");
}

/**

date_begin: "ЕЖЕДНЕВНО<br>в 14:55, начиная с 08.11.2021"

filter: "467899715"
id: "32"



repeat: "daily"
repeat_src: "0"
status: "10"


 */

function edit(id){
    let it=appTable.getitems(table, "id", id);
    if(it.length==0) return;
    it=it[0];
    appSendmess.show({
         name:it.name
        ,text:it.body
        ,files:it.files
        ,keyboard:it.keyboard
        ,repeat:it.repeat
        ,filter:it.filter
        ,id:id
        ,date:it.date_begin
        ,kb:false
    });
}

function sendMess(){
    let m=appSendmess.send();
    id=m.id;
	$.post("p.php?q=set", m, function(res) {
    	if(!res.success) return toast('Ошибка', res.err, "e");
        for(row in res.data){
        	appTable.update(table, "id", id, row, res.data[row]);
        }
	}, "json");
}


 
function eventEnd(id){
    let it=appTable.getitems(table, "id", id);
    if(it.length===0) return;
    $.post("p.php?q=eventEnd", { id:id,d:moment().format('YYYY-MM-DD HH:mm:ss') }, function(res) {
    if(!res.success) return toast('Ошибка', res.err, "e");
        appTable.update(table, "id", id, "event_end",moment().subtract('seconds', 60) );
    },"json");
    
}

function pause(id,s){
    let it=appTable.getitems(table, "id", id);
    if(it.length===0) return;
    $.post("p.php?q=pause", { id:id,s }, function(res) {
    if(!res.success) return toast('Ошибка', res.err, "e");
        appTable.update(table, "id", id, "status", s===1?"11":"10");
    },"json");
    
}

function poll(id){

    $.post("p.php?q=pollGet", { id:id }, function(res) {
    if(!res.success) return toast('Ошибка', res.err, "e");
        
        appTable.init({
             table:'table-poll'
            ,list:res.users
            ,template:'pollTemplate'
            ,listStart:0
            ,search:''
            ,limit:10
        });

        
     let COLORS = [
       '#4dc9f6',
       '#f67019',
       '#f53794',
       '#537bc4',
       '#acc236',
       '#166a8f',
       '#00a950',
       '#58595b',
       '#8549ba',
       '#4dc9f6',
       '#f67019',
       '#f53794',
     ];

      let chartData = {
        labels: res.data.map(function(it,i) { return it.variant; }),
        datasets: [{
          label: 'My First Dataset',
          data: res.data.map(function(it) { return it.sum; }),
          backgroundColor: res.data.map(function(it,i) { return COLORS[i]; }),
          hoverOffset: 4
        }]
      };

      const config = {
      type: "doughnut",
      data: chartData,
      options: {
      responsive: true,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      stacked: false,
      plugins: {
        title: {
          display: false,
          text: ''
        },
        //legend: {
        //  display: false,
        //}
      },
      
      },
      };
      
     $('#pollChart').remove();
     $('#pollChartArea').append('<canvas id="pollChart"></canvas>');
      var pch = new Chart(pollChart.getContext('2d'), config);

        $('#modal-poll').modal('show');
    }, "json"); 
}
function pollTemplate( data ){
 return `<tr class="rl">
             <td>${ data.first_name+(data.username!==""?" / "+data.username:"") }</td>
             <td>${data.variant}</td>
<!--              <td class="text-end">
    <button type="button" class="btn btn-outline-danger btn-sm" onclick="rmovePoll('${data.id}')"><i class="bi bi-x-lg"></i></button>
</td> -->
         </tr>`;
         
}

function showUsers(mid){

    $.post("p.php?q=getUsers", { mid:mid }, function(res) {
    if(!res.success) return toast('Ошибка', res.err, "e");
    appTable.init({
         table:'table-users'
        ,list:res.data
        ,template:'templateUsers'
        ,listStart:0
        ,search:''
        ,limit:10
    });
    }, "json");
    
    $("#users_modal").modal("show");
}

function templateUsers( data ){
    let us=data.id_chat>0?data.first_name+(data.username===""?"":" / "+data.username) : data.id_chat;
 return `<tr class="rl">
             <th>${data.line_number}</th>
             <td>${prep(data.f_date)}</td>
             <td>${us}</td>
             <td>${prep(data.status)}</td>
         </tr>`;
}

function template( data ){
    let status="",btn="", style, sendingstatus=[];
    switch(data.status){
        case "0": status = 'в ожидании'; break;
        case "1": status = 'в процессе'; break;
        case "2": status = 'Завершена'; break;
        case "10": btn = `<button type="button" class="btn btn-outline-primary btn-sm" onclick="pause(${data.id},1)">на паузу</button>`; style="text-primary"; break;
        case "11": btn = `<button type="button" class="btn btn-warning btn-sm" onclick="pause(${data.id},0)">запустить</button>`; style="text-danger"; break;
    }

    if(data.st_waitings>0)sendingstatus.push("ожидают: "+data.st_waitings);
    if(data.st_success>0)sendingstatus.push("отправлено: "+data.st_success);
    if(data.st_error>0)sendingstatus.push("ошибок: "+data.st_error);
    if(sendingstatus.length>0) sendingstatus="<br><span class='text-secondary'><i>"+sendingstatus.join("<br>")+"</i></span>";

    let event="";/* 1 - событие в процессе, 2 - событие завершено */
    if(["1",1,"2",2].indexOf(data.event_status)>-1) {
        let eventend=moment()>moment(data.event_end);
        event=`<div class="${eventend?"":"text-primary"}">`+moment(data.event_end).format('DD.MM.YY HH:mm')
            +(eventend?"":`<button type="button" class="btn btn-outline-secondary py-0 btn-sm ms-2" style="margin-top: -5px !IMPORTANT;" onclick="eventEnd('${data.id}')">ЗАВЕРШИТЬ</button>`)
            +"</div>";
        if(data.event_notify==="1") {
        let nwk=data.event_notify_wkdays.map(function(it,i){
            return it==='1'?["ПН","ВТ","СР","ЧТ","ПТ","СБ","ВС"][i]:'';
        });
        nwk=nwk.filter(function(it,i){
            return it!=='';
        }).join(',');
            event += '<br>Уведомлять о завершении<br>в '+nwk+'<br>в '+data.event_notify_time;
        }
    }



    if(data.status==2||data.status==3){
        if(data.poll===1)
        btn = `<button class="btn btn-outline-secondary btn-sm" type="button" onclick="poll(${data.id})""><i class="bi bi-ui-checks-grid"></i> Опрос</button>`;
    }
    
    let r = data.repeat !== "";
    let name = (r ?'<i class="bi bi-arrow-repeat"></i> ':"")+ data.name ;

    let repeatdata="";
    let date = moment(data.date_begin);
    switch(data.repeat){
        case 'daily':
            let weekdays = data.weekdays===""?[]:data.weekdays.split("-"); let f,s;
            switch(weekdays.length){
                case 0: f='<span class="text-danger">НЕ ВЫБРАНЫ ДНИ НЕДЕЛИ!</span>';break;
                case 7: f="ЕЖЕДНЕВНО";break;
                default:
                    if((ind=weekdays.indexOf('1'))>-1) weekdays[ind]='8';
                    weekdays.sort();
                    f=weekdays.map(function(it){
                        return ["","ПН","ВТ","СР","ЧТ","ПТ","СБ","ВС"][it-1];
                    }).join(',');
                    switch(weekdays[0]-1){
                        case 1:case 2:case 4: s='Каждый'; break;
                        case 3:case 5:case 6: s='Каждую'; break;
                        case 7: s='Каждое'; break;
                    }
            }
            repeatdata = (s??"")+" "+f+'<br>в '+date.format('HH:mm');
        break;
        case 'weekly':
            repeatdata = ['КАЖДОЕ ВОСКРЕСЕНИЕ', 'КАЖДЫЙ ПОНЕДЕЛЬНИК', 'КАЖДЫЙ ВТОРНИК', 'КАЖДУЮ СРЕДУ', 'КАЖДЫЙ ЧЕТВЕРГ', 'КАЖДУЮ ПЯТНИЦУ', 'КАЖДУЮ СУББОТУ'][date.day()]+'<br>в '+date.format('HH:mm');
        break;
        case 'monthly':
            repeatdata = 'ЕЖЕМЕСЯЧНО КАЖДОЕ '+date.format('D')+' ЧИСЛО'+'<br>в '+date.format('HH:mm');
        break;
        case 'interval':
            repeatdata = 'КАЖДЫЕ '+data.interval+' Ч.';
        break;
    }
    if(r)
    repeatdata = repeatdata+', начиная с '+date.format('DD.MM.YY')+"<br>получатели: "+data.filterName;
    else
    repeatdata = date.format('DD.MM.YY HH:mm')+(data.filterName===""?"":"<br>получатели: "+data.filterName);
    
 return `<tr class="rl ${style}">
             <td>${ name }</td>
             <td>${data.body??""}</td>
             <td nostyle>${repeatdata}</td>
             <td>${event}</td>
             <td>${status+sendingstatus}</td>
             <td class="text-end">
                  ${btn}
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showUsers('${prep(data.id)}')"><i class="bi bi-people"></i></button>
                 <button type="button" class="btn btn-outline-secondary btn-sm" onclick="edit(${data.id})"><i class="bi bi-pencil"></i></button>
                 <button type="button" class="btn btn-outline-danger btn-sm" onclick="rmove('${prep(data.id)}')"><i class="bi bi-x-lg"></i></button>
             </td>
         </tr>`;
         
    //document.location.href = "index.php?copyFrom="+$(this).attr('mid');
         
}
function prep(v){return v===undefined||v===false||v===NaN||v===null?'':v;}
</script>

