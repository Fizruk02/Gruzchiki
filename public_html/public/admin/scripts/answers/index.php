<div class="container mt-2" tableform>
   <div class="form-inline" style="padding-bottom: 4px;width:100%;position:sticky;top:10px;background-color: white;">
      <div class="container" style="max-width: 100%;">
         <div class="row">
             <div class="col-auto p-0 d-flex">
                   <input type="text" class="form-control" style="width: 201px;" id="answersRange" value="" />
                   <button class="btn btn-outline-secondary ms-2" type="button" onclick="$('#filterModal').modal('show')">Фильтр</button>
             </div>
             <div class="col-sm p-0 ms-1">
             </div>
            <div class="col-auto p-0">
               <div class="input-group w-100">
                  <input type="search" class="form-control" placeholder="поиск..." searchtext>
                  <div class="input-group-append" style="height: 38px;">
                     <button class="btn btn-outline-secondary" type="button" search>искать</button>
                     <button class="btn btn-outline-secondary" style="color: #b90605;font-weight: 600;" type="button" exporttopdf>pdf</button>
                     <button class="btn btn-outline-secondary" style="color: #1f7244;font-weight: 600;" type="button" exporttoexcel>excel</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div n-div='4' style="overflow-x: auto;width:100%;">
      <table class="table table-hover table-sm" id="table-answers" display="whatchat-answers">
         <thead class="thead-dark" id="table-answers-head">
            <tr>
               <th>src</th>
               <th>Дата</th>
               <th>id</th>
               <th>Имя</th>
               <th>Скрипт</th>
               <th>Вопрос</th>
               <th>Ответ</th>
               <!-- <th style="width:50px;">Файл</th> -->
            </tr>
         </thead>
         <tbody id="table-answers-tbody">

         </tbody>
      </table>
   </div>
   
    <nav>
      <ul class="pagination float-right">

        <li class="page-item">
          <a class="page-link" href="#" pagination h="begin">
            <i class="bi bi-chevron-double-left"></i>
          </a>
        </li>
        <li class="page-item">
          <a class="page-link" href="#" pagination h="previous">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>
        <li class="page-item">
          <a class="page-link" href="#" pagination h="next">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>
        <li class="page-item">
          <a class="page-link" href="#" pagination h="end">
            <i class="bi bi-chevron-double-right"></i>
          </a>
        </li>
        
      </ul>
    </nav>
   
</div>

<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body" id="filterbody">
      </div>
    </div>
  </div>
</div>