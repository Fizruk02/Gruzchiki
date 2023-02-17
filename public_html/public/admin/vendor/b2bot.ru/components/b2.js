/**
 * loadspinner(s) - s:1(по умолчанию) - отображает спинер по координатам клика, 0 - скрывает. Используется для ajax запросов
 */
document.querySelector('body').innerHTML += '<div class="spinner-border text-dark" style="position: absolute;z-index: 9999;display:none" role="status" id="b2loadspinner"><span class="visually-hidden"></span></div>';
document.createElement('div');

var b2 = (function($) {
let st=0;
function spinner(s=1){
	if(st===s)return;
	st=s;
    let el = document.getElementById('b2loadspinner');
    if(s===1){
		let e = window.event;
		if(e===undefined) return;
		let x,y;
		if (e.pageX || e.pageY){
			x = e.pageX;
			y = e.pageY;
		} else if (e.clientX || e.clientY){
			x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
			y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
		}
		if(!x||!y) return;
		el.style.top = y+'px';
		el.style.left = x+'px';
	}
	el.style.display = s===1?'block':'none';
}
    return {spinner:spinner};
})(jQuery);

$(document).ajaxSuccess(function(e) {b2.spinner(0);});
$(document).ajaxStart(function(e) {b2.spinner(1);});
$(document).ajaxError(function(ev, xhr, opt) {toast('Ошибка загрузки', "Ошибка запроса к «"+opt.url+"»",'e');});