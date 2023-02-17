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
 * appTable.data('table-id') - data
 * 
    appTable.init({
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
            left:'<div></div>'
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
    * или массив значений, например
        update(id_table, "id", 32, {
            name:"Martin",
            age:28
        })
    
    * поменять значение у всех отфильтрованных строк
    appTable.data(id_table).forEach(function(item) {
	    appTable.update(id_table,'id',item.id,'check', "0");
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
                         <button class="btn btn-outline-secondary" type="button" search>искать</button>
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
          <span class="page-link" pagination h="begin">
            <i class="bi bi-chevron-double-left"></i>
          </span>
        </li>
        <li class="page-item">
          <span class="page-link" pagination h="previous">
            <i class="bi bi-chevron-left"></i>
          </span>
        </li>
        <li class="page-item">
          <span class="page-link" pagination h="next">
            <i class="bi bi-chevron-right"></i>
          </span>
        </li>
        <li class="page-item">
          <span class="page-link" pagination h="end">
            <i class="bi bi-chevron-double-right"></i>
          </span>
        </li>
      </ul>
    </nav>
    
 */

var appTable = (function($) {

    var data = {};
    function init(par={}){

        data[par.table]={};
        Object.keys(par).forEach(function(it) {
            if(it!=='table')
        	    data[par.table][it]=par[it];
        	if(data[par.table].limit===undefined||!data[par.table].limit) data[par.table].limit=10;
        	if(it==='list') data[par.table]['listLength']=par[it].length;
        });


        
        if(document.getElementById(par.table).closest('[tableform]').innerHTML.indexOf('pagination')===-1){
        	let nav = document.createElement('nav');
        	nav.setAttribute("id", "pagination-"+par.table);
        	nav.innerHTML = `
              <ul class="pagination float-right" style="cursor:pointer">
                ${par.limitbtn!==false?
                `<li class="page-item">
                    <div class="dropdown">
                      <a class="page-link dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        строк
                      </a>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="#" onclick="appTable.limit('${par.table}','5')">5</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appTable.limit('${par.table}','10')">10</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appTable.limit('${par.table}','15')">15</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appTable.limit('${par.table}','20')">20</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appTable.limit('${par.table}','30')">30</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appTable.limit('${par.table}','50')">50</a></li>
                        <li><a class="dropdown-item" href="#" onclick="appTable.limit('${par.table}','1000000')">Все</a></li>
                      </ul>
                    </div>
                </li>`
                :''}
              
                <li class="page-item">
                  <span class="page-link" pagination="${par.table}" h="begin">
                    <i class="bi bi-chevron-double-left"></i>
                  </span>
                </li>
                <li class="page-item">
                  <span class="page-link" pagination="${par.table}" h="previous">
                    <i class="bi bi-chevron-left"></i>
                  </span>
                </li>
                <li class="page-item">
                  <span class="page-link" pagination="${par.table}" h="next">
                    <i class="bi bi-chevron-right"></i>
                  </span>
                </li>
                <li class="page-item">
                  <span class="page-link" pagination="${par.table}" h="end">
                    <i class="bi bi-chevron-double-right"></i>
                  </span>
                </li>
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
                        ${par.range?`<div class="input-group input-group-sm"><input type="text" class="form-control" style="width: 165px;" id="range-${par.table}" value="" /></div>`:''}
                        ${par.header?par.header.left||"":""}
                     </div>
                     <div class="col-sm p-0 ms-1">
                     </div>
                    <div class="col-auto p-0">
                       <div class="input-group input-group-sm w-100">
                          <input type="search" class="form-control" placeholder="Search..." searchtext>
                             <button class="btn btn-outline-secondary" type="button" search>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                             </button>
                             <button class="btn btn-outline-secondary"  type="button" exporttopdf>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                                  <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                  <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                </svg>
                             </button>
                             <button class="btn btn-outline-secondary"  type="button" exporttoexcel>
                                 <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel" viewBox="0 0 16 16">
                                  <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>
                                  <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                </svg>
                            </button>
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
        
        
        create(par.table);
    }
    
    
    var find = function (arr, find) {
        find=find+("");
      return arr.filter(function (value) {
         return (value + "").indexOf(find) != -1 ? true : false;
      });
    };
    
    function create(table){
        $('#'+table).hide();
        $('#'+table).css( "table-layout", "fixed" );
        // data[table].sortrow=false;
        // $('#'+table+' thead tr th').removeClass("bg-primary");
        let arr = filter(table).map(function(it,i) {
            it['line_number']=i+1;return it;
        });

        arr = arr.slice(data[table].listStart, data[table].listStart+data[table].limit);
        let h = '';
        
        arr.forEach(function(item, i) {//console.log(item);
        	h += window[data[table].template](item);
        });

        if(data[table].list.length<=data[table].limit)
            $("[pagination='"+table+"']").hide();
        else
            $("[pagination='"+table+"']").show();
        
        $('#'+table).find('tbody').html(h);
        $('#'+table).find('td').css( "overflow", "hidden" );
        $('#'+table).find('td').css( "white-space", "nowrap" );
        $('#'+table).find('td').css( "text-overflow", "ellipsis" );
        $('#'+table).find('td[nostyle]').removeAttr('style');

        
        
        $('#'+table).show();

    }
    
    function limit(t,l){
        data[t].limit=l;
        create(t);
    }
   
    
    function filter(table){
        let arr = data[table].list;
        if(data[table].search && data[table].search!==''){
            arr = arr.filter(function(item) {
                return Object.values(item).filter(function (value) { return (value + "").toLowerCase().indexOf(data[table].search.toLowerCase()) != -1 }).length>0
            });
        }
        return arr;
    }
    
    $('body').on('click', '[pagination]', function(){
        let table = $(this).closest('[tableform]').find('table').attr('id');
        
        switch($(this).attr('h')){
            case 'begin':
                data[table]['listStart'] = 0;
            break;
            case 'previous':
                data[table]['listStart'] = data[table]['listStart']-data[table].limit<0?0:data[table]['listStart']-data[table].limit;
            break;
            case 'next':
                if(data[table]['listStart']+data[table].limit>=filter(table).length) return;
                data[table]['listStart'] = data[table]['listStart']+data[table].limit;
            break;
            case 'end':
                data[table]['listStart'] = filter(table).length-data[table].limit<0?0:filter(table).length-data[table].limit;
            break;
        }

        create(table);
    })
    
    $('body').on('click', '[search]', function(){
        let searchtext = $(this).closest('[tableform]').find('[searchtext]').val(); 
        let table = $(this).closest('[tableform]').find('table').attr('id');
        if(table){
            data[table]['search'] = searchtext;
            create(table);
        }
    })


    $('body').on('click', 'thead tr th', function(){
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

        function fltr(a, b){
            if(!isNaN(parseFloat(a.sortrow))&&!isNaN(parseFloat(b.sortrow))){a.sortrow*=1;b.sortrow*=1};
            return a.sortrow > b.sortrow? 1:(a.sortrow < b.sortrow?-1:0)}
        function fltv(a, b){
            if(!isNaN(parseFloat(a.sortrow))&&!isNaN(parseFloat(b.sortrow))){a.sortrow*=1;b.sortrow*=1};
            return a.sortrow > b.sortrow?-1:(a.sortrow < b.sortrow? 1:0)}



        arr.sort(oldcol==col?fltv:fltr);
        arr = arr.map(function(it) {
            delete it.sortrow;
            return it;
        });
        create(table);
        data[table].sortrow=col==oldcol?-1:col;
    })
    
    $('body').on('click', '[exporttopdf]', function(){
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
        data[t].list.forEach(function(item,i) {
        	if(item[k]==v){
        	    if(typeof(p)==="object"){
        	        for(x in p) data[t].list[i][x]=p[x];
        	    } else data[t].list[i][p]=s;
        	    
        	     if(x=document.querySelector("#"+t+" tbody").getElementsByTagName("tr")[i-data[t].listStart]) x.outerHTML=window[data[t].template](data[t].list[i]);
        	}
        });
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
    
    $('body').on('click', '[exporttoexcel]', function(){
        
    let table = $(this).closest('[tableform]').find('table').attr('id');
    
    let dName = $('#'+table).attr('display');
    if(dName===undefined||dName==="")dName=table;
    
    let arr = filter(table);
    arr=arr.map(function(it) {
      if(typeof(it)==="object"){delete it['id'];delete it['line_number'];}
      return it;
    });

    let wb = new ExcelJS.Workbook();
    let workbookName = dName+'.xlsx';
    let worksheetName = 'data';
  
    let ws = wb.addWorksheet(worksheetName, 
      {
        properties: {
          tabColor: {argb:'FFFF0000'}
        }
      }
    );

    ws.columns = Object.keys(arr[0]).map(function(it) {
      return {key: it, header: it};
    });
        

    ws.addRows(arr);

    ws.columns.forEach(function (column, i) {
    if(i!==0)
    {
    var maxLength = 0;
    column["eachCell"]({ includeEmpty: true }, function (cell) {
        var columnLength = cell.value ? cell.value.toString().length : 10;
        if (columnLength > maxLength ) {
            maxLength = columnLength;
        }
    });
    column.width = maxLength < 10 ? 10 : maxLength;
    }   
    });
    wb.xlsx.writeBuffer()
      .then(function(buffer) {
        saveAs(
          new Blob([buffer], { type: "application/octet-stream" }),
          workbookName
        );
    });
        

    })
    
    return {
        init: init, data:filter, insert:insert, update:update, rmove:rmove, getitems:getitems, startDate:startDate, endDate:endDate, limit:limit
    }   
    
})(jQuery);