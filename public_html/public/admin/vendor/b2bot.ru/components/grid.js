/**
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.71/pdfmake.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.71/vfs_fonts.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
 * 
 * 
 * 
 * 
 * template:
 * 
 * 
 * appGrid.data('table-id') - data
 * 
    appGrid.init({
         table:'table-users'
        ,list:res.data
        ,template:'usersTmplt'
        ,listStart:0
        ,search:''
        ,limit:10
        ,range:{
             start:startDate // moment("1986-09-19")
            ,end:endDate // moment()
            ,func:range
        }
        ,header:{
            //visible:false,
            buttons:[
                '<button class="btn btn-outline-secondary" type="button" onclick="add()">Добавить</button>'
            ],
        }
        //,limitbtn:false // скрыть кнопку лимитов
    });
    
    function usersTmplt(data){
        // data.line_number - номер строки
        return `<tr class="rl">
                   <td>${data.chat_id}</td>
                   <td>${data.name}</td>
                </tr>`;
    }
    
    function range(s,e){
        startDate=s;
        endDate=e;
        list();
    }
    
    * update - обновить ячейку. Надо передать ячейку, по которой искать и ячейку, которую менять
    * например поменяем имя у строки с id = 32
    update(id_table, "id", 32, "name", "Martin")
    
    * поменять значение у всех отфильтрованных строк
    appGrid.data(id_table).forEach(function(item) {
	    appGrid.update(id_table,'id',item.id,'check', "0");
    });
    
    *
    * пагинация и строка поиска с кнопками экспорта добавляются автоматически, если их нет на форме
    * вы можете добавить их вручную, чтобы, наприме, добавить еще кнопки или изменить внешний вид
    * 
    * строка поиска с кнопками экспорта
    * 
    
    <div class="form-inline" style="padding-bottom: 4px;width:100%;position:sticky;top:10px;background-color: white;">
          <div class="container" style="max-width: 100%;">
             <div class="row">
                 <div class="col-auto p-0 d-flex">
       
                 </div>
                 <div class="col-sm p-0 ms-1">
                 </div>
                <div class="col-auto p-0">
                   <div class="input-group w-100">
                      <input type="search" class="form-control" placeholder="поиск..." searchtext>
                         <button class="btn btn-outline-secondary" type="button" grdsearch>искать</button>
                         <button class="btn btn-outline-secondary" style="color: #b90605;font-weight: 600;" type="button" exporttopdf>pdf</button>
                         <button class="btn btn-outline-secondary" style="color: #1f7244;font-weight: 600;" type="button" exporttoexcel>excel</button>
                   </div>
                </div>
             </div>
          </div>
       </div>
    
    
    *
    * пагинация
    * 
    <nav>
      <ul class="pagination float-right" style="cursor:pointer">
        <li class="page-item">
          <span class="page-link" gridpgntn h="begin">
            <i class="bi bi-chevron-double-left"></i>
          </span>
        </li>
        <li class="page-item">
          <span class="page-link" gridpgntn h="previous">
            <i class="bi bi-chevron-left"></i>
          </span>
        </li>
        <li class="page-item">
          <span class="page-link" gridpgntn h="next">
            <i class="bi bi-chevron-right"></i>
          </span>
        </li>
        <li class="page-item">
          <span class="page-link" gridpgntn h="end">
            <i class="bi bi-chevron-double-right"></i>
          </span>
        </li>
      </ul>
    </nav>
    
 */

var appGrid = (function($) {

    var data = {};
    function init(par={}){

        data[par.table]={};
        Object.keys(par).forEach(function(it) {
            if(it!=='table')
        	    data[par.table][it]=par[it];
        	if(data[par.table].limit===undefined||!data[par.table].limit) data[par.table].limit=10;
        	data[par.table].offset=0;
        	data[par.table].search="";
        	//if(it==='list') data[par.table]['listLength']=par[it].length;
        });


        
        if(document.getElementById(par.table).closest('[tableform]').innerHTML.indexOf('pagination')===-1){
        	let nav = document.createElement('nav');
        	nav.setAttribute("id", "gridpgntn-"+par.table);
        	nav.innerHTML = `
              <ul class="pagination float-right" style="cursor:pointer">
                ${par.limitbtn!==false?
                `<li class="page-item">
                    <div class="dropdown">
                      <a class="page-link dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        строк
                      </a>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="#" onclick="appGrid.limit('${par.table}','5')">5</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appGrid.limit('${par.table}','10')">10</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appGrid.limit('${par.table}','15')">15</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appGrid.limit('${par.table}','20')">20</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appGrid.limit('${par.table}','30')">30</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appGrid.limit('${par.table}','50')">50</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appGrid.limit('${par.table}','1000000')">Все</a></li>
                      </ul>
                    </div>
                </li>`
                :''}
              
                <li class="page-item">
                  <span class="page-link" gridpgntn="${par.table}" h="begin">
                    <i class="bi bi-chevron-double-left"></i>
                  </span>
                </li>
                <li class="page-item">
                  <span class="page-link" gridpgntn="${par.table}" h="previous">
                    <i class="bi bi-chevron-left"></i>
                  </span>
                </li>
                <li class="page-item">
                  <span class="page-link" gridpgntn="${par.table}" h="next">
                    <i class="bi bi-chevron-right"></i>
                  </span>
                </li>
                <!-- <li class="page-item">
                  <span class="page-link" gridpgntn="${par.table}" h="end">
                    <i class="bi bi-chevron-double-right"></i>
                  </span>
                </li> -->
              </ul>`;
            document.getElementById(par.table).closest('[tableform]').append(nav);
            
            //document.getElementById(par.table).closest('[tableform]').insertBefore(nav,document.querySelector('[tablecontainer]'));
        }
      
        if(document.getElementById(par.table).closest('[tableform]').innerHTML.indexOf('searchtext')===-1){
        	let div = document.createElement('div');
        	div.innerHTML = `
            <div class="form-inline" style="padding-bottom: 4px;width:100%;position:sticky;top:10px;${par.header!==undefined&&par.header.visible===false?"display:none":""}">
              <div class="container" style="max-width: 100%;padding-left: 12px;padding-right: 14px;">
                 <div class="row">
                     <div class="col-auto p-0 d-flex">
                        ${par.range!==undefined?`<div class="input-group input-group-sm"><input type="text" class="form-control" style="width: 165px;" id="range-${par.table}" value="" /></div>`:''}
                     </div>
                     <div class="col-sm p-0 ms-1">
                     </div>
                    <div class="col-auto p-0">
                       <div class="input-group input-group-sm w-100">
                          <input type="search" class="form-control" placeholder="Search..." searchtext>
                             <button class="btn btn-outline-secondary" type="button" grdsearch>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                             </button>

<!--                             <button class="btn btn-outline-secondary"  type="button" gridexporttoexcel>-->
<!--                                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel" viewBox="0 0 16 16">-->
<!--                                  <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>-->
<!--                                  <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>-->
<!--                                </svg>-->
<!--                            </button>-->
                            ${par.header!==undefined && par.header.buttons!==undefined?par.header.buttons.join(""):"" }
        
                       </div>
                    </div>
                 </div>
              </div>
           </div>`;

            document.getElementById(par.table).closest('[tableform]').insertBefore(div,document.getElementById(par.table).closest('[tablecontainer]'));
        }

        if(document.getElementById(par.table).innerHTML.indexOf('<tbody')===-1){
            document.getElementById(par.table).append(document.createElement('tbody'));
        }

        if(par.range!==undefined){
            $('#range-'+par.table).daterangepicker(
            {
              ranges   : {
                'Сегодня'       : [moment(), moment()],
                'Вчера'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Последние 7 дней' : [moment().subtract(6, 'days'), moment()],
                'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
                'Этот месяц'  : [moment().startOf('month'), moment().endOf('month')],
                'Предыдущий месяц'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
              },
              locale : {
                format: 'DD.MM.YYYY'
              },
              startDate: par.range.start===undefined? moment():par.range.start,
              endDate  : par.range.end===undefined? moment():par.range.end
            },
            function (start, end) {
                if(par.range.func!==undefined && par.range.func.length==2)
                par.range.func(start, end);
            }); 
        }
        
        
        //create(par.table);
    }
    
    
    async function create(t){
        let p={ofs:data[t].offset,lmt:data[t].limit,srch:data[t].search,ordrfld:"",sort:""};
        $.post("p.php?q=getList", p,function(res) {
        if(res.success!=='ok') return toast('Ошибка', res.err, 'e');
            list(t,res.data);
        },'json');
        
        
        //window[data[t].list](p);
    }
    
    async function getdata(p){
        let r = await fetch('p.php?q=getList', { method: 'POST', body: JSON.stringify(p) });
        let rs = await r.json();
        if(rs.success!=='ok') return toast('Ошибка', rs.err, 'e');
        //return rs.data;
    }
    
    function list(t,d){
        let h = '';
        d.forEach(function(item, i) {
        	h += window[data[t].template](item);
        });

        //if(data[t].list.length<=data[t].limit)
        //    $("[gridpgntn='"+t+"']").hide();
        //else
        //    $("[gridpgntn='"+t+"']").show();
        
        $('#'+t).hide();
        $('#'+t).css( "table-layout", "fixed" );
        $('#'+t).find('tbody').html(h);
        $('#'+t).find('td').css( "overflow", "hidden" );
        $('#'+t).find('td').css( "white-space", "nowrap" );
        $('#'+t).find('td').css( "text-overflow", "ellipsis" );
        
        $('#'+t).find('td[nostyle]').removeAttr('style');
        $('#'+t).show();
    }
    
    function limit(t,l){
        data[t].limit=l;
        create(t);
    }
   

    
    $('body').on('click', '[gridpgntn]', function(){
        let table = $(this).closest('[tableform]').find('table').attr('id');
        let dt=data[table];
        console.log(data[table]);
        switch($(this).attr('h')){
            case 'begin':
                dt.offset = 0;
            break;
            case 'previous':
                dt.offset =(x=(dt.offset-dt.limit))<0?0:x;
            break;
            case 'next':
                dt.offset +=dt.limit;
            break;
            case 'end':
                
            break;
        }

        create(table);
    })
    
    $('body').on('click', '[grdsearch]', function(){
        if(t = $(this).closest('[tableform]').find('table').attr('id')){
            data[t].offset = 0;
            data[t].search = $(this).closest('[tableform]').find('[searchtext]').val();
            create(t);
        }
    })

    /*
    $('body').on('click', 'test thead tr th', function(){
        let table = $(this).closest('[tableform]').find('table').attr('id');
        let col = this.cellIndex;
        let arr = filter(table);
        let h = '';
        let oldcol = data[table].sortrow;
        $('#'+table+' thead tr th').removeClass("bg-primary");
        $(this).addClass("bg-primary");
        arr.forEach(function(item, i) {
            h += window[data[table].template](item);
        });


        let tab = document.createElement('table');
        tab.innerHTML="<tbody>"+h+"</tbody>";


        for (let row of tab.rows) {
            arr[row.rowIndex]['sortrow']=row.cells[col].innerText;
        }

        function fltr(a, b){return a.sortrow > b.sortrow? 1:(a.sortrow < b.sortrow?-1:0)}
        function fltv(a, b){return a.sortrow > b.sortrow?-1:(a.sortrow < b.sortrow? 1:0)}
        arr.sort(oldcol==col?fltv:fltr);
        arr = arr.map(function(it) {
            delete it.sortrow;
            return it;
        });
        create(table);
        data[table].sortrow=col==oldcol?-1:col;
    })
    */
    $('body').on('click', '[gridexporttopdf]', function(){
        let table = $(this).closest('[tableform]').find('table').attr('id');
        
        let arr = filter(table);

        
        let headers = Object.keys(arr[0]).map(function(it) {
          return {text: it, style: 'tableHeader', bold: true};
        });
        
        arr.forEach(function(item, i) {
        	arr[i]=Object.values(item);
        });
        arr.unshift(headers);        
        
        let widths = Array.apply(null, Array(arr[0].length)).map(function() { return 'auto' })
        
        let pathname = window.location.pathname.trim();
        if(pathname.charAt(0)=='/')
            pathname = pathname.slice(1);
        if(pathname.toString().slice(-1)=='/')
            pathname = pathname.substring(0, pathname.length - 1);
        if(pathname==='')
            pathname = 'table';
        var docInfo = {
    
         info: {
         title: pathname,
         },
         
         pageSize:'A4',
         pageOrientation: arr[0].length>4?'landscape':'portrait',
         
         content: [{
             
                 table:{
                     widths:widths,
                     body:arr,
                     headerRows:1
                 },
                 layout: {
    				hLineWidth: function (i, node) {
    					return (i === 0 || i === node.table.body.length) ? 2 : 1;
    				},
    				vLineWidth: function (i, node) {
    					return (i === 0 || i === node.table.widths.length) ? 2 : 1;
    				},
    				hLineColor: function (i, node) {
    					return (i === 0 || i === node.table.body.length) ? 'black' : 'gray';
    				},
    				vLineColor: function (i, node) {
    					return (i === 0 || i === node.table.widths.length) ? 'black' : 'gray';
    				},
    			}
                     
        
         }]
        };
        
        pdfMake.createPdf(docInfo).download(pathname+'.pdf');
        
        
    })
    
    function startDate(t){
        return $('#range-'+t).data('daterangepicker').startDate;
    }
    function endDate(t){
        return $('#range-'+t).data('daterangepicker').endDate;
    }
    
    function update(t,k,v,p,s){
        
    }
    
    
    function insert(t,d){
        let b=false;
        data[t].list.push(d);
        create(t)
    }
    
    
    function rmove(t,k,v){
        let b=false;
        data[t].list.forEach(function(item,i) {
        	if(item[k]==v){
        	     data[t].list.splice(i,1);
        	     b=true;
        	}
        });
        if(b===true) create(t)
    }
    
    function getitems(t,k,v){
        let a=[];
        data[t].list.forEach(function(item) {
        	if(item[k]==v) a.push(item);
        });
        return a;
    }
    
    
    
    
    
    
    $('body').on('click', '[gridexporttoexcel]', function(){
        

    })
    
    return {
        init: init, insert:insert, update:update, rmove:rmove, getitems:getitems, startDate:startDate, endDate:endDate, create:create, list:list
    }   
    
})(jQuery);