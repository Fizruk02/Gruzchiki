var toastpl = document.createElement("div");
toastpl.innerHTML = '<div aria-live="polite" aria-atomic="true" id="toast-area" style="position: fixed; bottom: 1rem; right: 1rem;z-index: 9999;"></div>';
document.currentScript.parentNode.appendChild(toastpl);
/**
 * toast('success header', 'success!!!');
 * toast('warning header', 'warning!!!', 'ц');
 * toast('error header', 'error!!!', 'e');
 */
function toast(h, txt, t=""){
   let id = 'toast'+Math.random().toString(36).slice(4);
   let hc = 'bg-success';
   switch(t){
       case 'error':case 'e':
           hc = 'bg-danger';txt ??="undefined error";
       break;
       case 'warning':case 'w':
           hc = 'bg-warning';
       break;
   }
   let text = 
   `<div role="alert" aria-live="assertive" aria-atomic="true" class="toast" id="${id}">
     <div class="toast-header ${hc} text-white d-block">
       <strong class="mr-auto">${h}</strong>
       <small class="text-muted"></small>
       <div class="float-end bi bi-x text-white" style="cursor:pointer" data-bs-dismiss="toast" ></div>
     </div>
     <div class="toast-body">
       ${txt}
     </div>
   </div>`;
   document.getElementById("toast-area").innerHTML+=text;
    var bsAlert = new bootstrap.Toast(document.getElementById(id));
    bsAlert.show();
}