var appTelegramEditor = (function($) {


var clientPC = navigator.userAgent.toLowerCase();
var clientVer = parseInt(navigator.appVersion);
var theSelection = false;
var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));

helplines = new Array(
 "Жирный текст: &lt;b&gt;текст&lt;/b&gt;",
 "Наклонный текст: &lt;i&gt;текст&lt;/i&gt;",
 "Подчёркнутый текст: &lt;u&gt;текст&lt;/u&gt;",
 "Перечёркнутый текст: &lt;s&gt;текст&lt;/s&gt;",
 "Код: &lt;code&gt;текст&lt;/code&gt;",
 //"Листинг (программа): &lt;pre&gt;код&lt;/pre&gt;",
 "Вставить ссылку: &lt;a href=http://url&gt;текст ссылки&lt;/a&gt;",
);

bbcode = new Array();
bbtags = new Array(
 '<b>','</b>',
 '<i>','</i>',
 '<u>','</u>',
 '<s>','</s>',
 '<code>','</code>',
 //'<pre>','</pre>',
 '<a href="">','</a>',
);

function not_closed_tags(n) {
 var r=false;
 if (n==0 || n==2 || n==32 || n==34) r=true;
 return r;
}



function getarraysize(thearray) {
 for (i = 0; i < thearray.length; i++) {
  if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null)) return i;
 }
 return thearray.length;
}

function arraypop(thearray) {
 thearraysize = getarraysize(thearray);
 retval = thearray[thearraysize - 1];
 delete thearray[thearraysize - 1];
 return retval;
}

function bbplace(text, id) {
var txtarea = document.getElementById(id);
 var scrollTop = (typeof(txtarea.scrollTop) == 'number' ? txtarea.scrollTop : -1);
 if (txtarea.createTextRange && txtarea.caretPos) {
  var caretPos = txtarea.caretPos;
  caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
  txtarea.focus();
 } 
 else if (txtarea.selectionStart || txtarea.selectionStart == '0') {
  var startPos = txtarea.selectionStart;
  var endPos = txtarea.selectionEnd;
  txtarea.value = txtarea.value.substring(0, startPos) + text + txtarea.value.substring(endPos, txtarea.value.length);
  txtarea.focus();
  txtarea.selectionStart = startPos + text.length;
  txtarea.selectionEnd = startPos + text.length;
 }
 else {
  txtarea.value  += text;
  txtarea.focus();
 }
 if (scrollTop >= 0 ) { txtarea.scrollTop = scrollTop; }
}

function bbstyle(bbnumber, id) {
var txtarea = document.getElementById(id);
 txtarea.focus();
 donotinsert = false;
 theSelection = false;
 bblast = 0;
 if (bbnumber == -1) { //Закрыть все теи
  while (bbcode[0]) {
   butnumber = arraypop(bbcode) - 1;
   txtarea.value += bbtags[butnumber + 1];
  }
  txtarea.focus();
  return;
 }
 if ((clientVer >= 4) && is_ie && is_win) {
  theSelection = document.selection.createRange().text; //Получить выделение для IE
  if (theSelection) { //Добавить теги вокруг непустого выделения
   document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
   txtarea.focus();
   theSelection = '';
   return;
  }
 }
 else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0)) {
  //Получить выделение для Mozilla
  mozWrap(txtarea, bbtags[bbnumber], bbtags[bbnumber+1]);
  return;
 }
 for (i = 0; i < bbcode.length; i++) {
  if (bbcode[i] == bbnumber+1 && !not_closed_tags(bbnumber)) { 
   bblast = i;
   donotinsert = true;
  }
 }
 if (donotinsert) {
  while (bbcode[bblast]) {
   butnumber = arraypop(bbcode) - 1;
   if (!not_closed_tags(butnumber)) bbplace(bbtags[butnumber + 1], id);
  }
  txtarea.focus();
  return;
 } 
 else { //Открыть тег
  bbplace(bbtags[bbnumber], id);
  bbcode[ getarraysize(bbcode) ] = bbnumber+1;
  txtarea.focus();
  return;
 }

}

function mozWrap(txtarea, open, close) {
 if (txtarea.selectionEnd > txtarea.value.length) { txtarea.selectionEnd = txtarea.value.length; }
 var oldPos = txtarea.scrollTop;
 var oldHght = txtarea.scrollHeight;
 var selStart = txtarea.selectionStart;
 var selEnd = txtarea.selectionEnd+open.length;
 txtarea.value = txtarea.value.slice(0,selStart)+open+txtarea.value.slice(selStart); 
 txtarea.value = txtarea.value.slice(0,selEnd)+close+txtarea.value.slice(selEnd);
 txtarea.selectionStart = selStart+open.length;
 txtarea.selectionEnd = selEnd;
 var newHght = txtarea.scrollHeight - oldHght;
 txtarea.scrollTop = oldPos + newHght;
 txtarea.focus();
}

function init (idTxtarea) {
 var l=bbtags.length;
 let h='';
 for (i=0; i<l; i+=2) {
  var p = bbtags[i].indexOf(' ');
  if (p<0) p = bbtags[i].indexOf('>');
  if (p<0) p = bbtags[i].indexOf(';');
  var tagname = bbtags[i].substring (1,p);
  var i2= i/2;
  var alter= helplines[i2];
  h+= '<img class="px-1" src="//b2bot.ru/components/tgeditor/tags/'+tagname+'.png"  height="16" hspace="0" vspace="0" alt="'+alter+'" title="'+alter+'"  onClick="appTelegramEditor.bbstyle('+i+',\''+idTxtarea+'\')">';

 }
 return h;
}

    return {
        init: init, bbstyle: bbstyle
    }   
    
})(jQuery);