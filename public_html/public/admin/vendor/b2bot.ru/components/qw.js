/**
 * 
 * gpars - get parameters. by default url, you can pass your link
 * addClass(selector*, class*, {from whom to delete})
 * removeClass(selector*, class*, {who to add})
 * frameHeight(selector, return function)
 * frameAutoHeight(selector, overflowY=hidden)
 *
 *
 *
 * arr - методы работы с ассоциативными массивами
 *      rm - удалить элемент по ключу и значению / qw.qrr.rm("id", id, object)
 *      ind - поиск индекса элемента по ключу и значению / qw.qrr.ind("id", id, object)
 *      get - получить элемент по ключу и значению / qw.qrr.get("id", id, object)
 *      format - форматирование массива для promptmodcreate / qw.arr.format(object, "name", "id")
 *      clone - копия ассоциативного массива qw.arr.clone(object)
 * 
 * cookie
 *      qw.cookie.set:(cookie_name, cookie_value, days)
 *      qw.cookie.get:(cookie_name)
 *      qw.cookie.delete:(cookie_name)
 *
 * cloud
 *      qw.cloud.set:(var_name, cookie_value, (r)=>{})
 *      qw.cloud.get:(var_name)
 *      qw.cloud.delete:(var_name)
 */
 
qw={
    uploaded:[],
    qs:(e)=> {return qw.qsa(e)[0]},
    qsa:(e)=> {try {return document.querySelectorAll(e)} catch (v){return []} },
    doc:(e)=> {e=qw.obj(e); return e.contentDocument || e.contentWindow.document},
    obj:(e)=> {return (typeof(e)==="string"?qw.qs(e):e)},
    append:(e,t)=> {qw.qsa(e).forEach((e)=> {e.insertAdjacentHTML('beforeend', t)})},
    show:(e,x,s=1)=> {qw.qsa(e).forEach((e)=> {e.style.display=s?(x||""):"none"})},
    hide:(e)=> {qw.show(e,false,0)},
    d:(e, t)=> {let s;(s = qw.qs(e))&&(s.style.display = t)},
    event:(e, v, n)=>qw.lstnr(e, v, n),
    lstnr:(e, t, s)=> {qw.qsa(e).forEach((x)=> {x.addEventListener(t, s)})},
    addClass:(e,c,o)=> {qw.qsa(e).forEach((e)=> {e.classList.add(c)});if(o)o.classList.remove(c)},
    removeClass:(e,c,o)=> {qw.qsa(e).forEach((e)=> {e.classList.remove(c)});if(o)o.classList.add(c)},
    click:(e, t)=> {qw.lstnr(e, "click", t)},
    post:(...a)=> { qw.ajax("POST",...a) },
    get:(...a)=> { qw.ajax("GET",...a) },
    ajax:(z, u, t, s, n, r=false)=> {
        var o,a,c=[],g=z==="GET";
        try {o = new ActiveXObject("Msxml2.XMLHTTP")} catch (e) {try {o = new ActiveXObject("Microsoft.XMLHTTP")} catch (e) {o = !1}}
        o || "undefined" == typeof XMLHttpRequest || (o = new XMLHttpRequest);
        for (let e in t) c.push(e + "=" + encodeURIComponent(typeof(t[e])==="object"?JSON.stringify(t[e]):t[e]));
        o.open(z, u+(g?(u.indexOf("?")>-1?"&":"?")+c.join("&"):""), 1), o.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"), o.send(c.join("&")), o.onreadystatechange = function() {
            if (4 == o.readyState && 200 == o.status){
                if("json" === n)
                try { a = JSON.parse(o.responseText); } catch(e) { a={err:e+"<hr><div>"+o.responseText+"</div>"}; }
                else a=o.responseText;
                if(r&&(a.err||!a.success)) return toast(r||'Error',a.err,"e");
                if(r&&a.warning) toast(r||'Warning',a.warning,"w");
                if(s) return s(a);
            }
        }
    },
    gpars:(s)=> {let e; return (e = (s?s:document.URL).split("?")[1]) ? JSON.parse('{"' + e.replace(/&/g, '","').replace(/=/g, '":"') + '"}') : []},
    load:(e,f)=> { let a = qw.obj(e);if(!a) return;
        if (qw.doc(a).readyState==='complete') { a.contentWindow.onload = ()=>{  qw.uploaded.push(a); }; qw.rload(a,f); }
    },
    rload:(a,f)=> { if(qw.uploaded.indexOf(a)===-1) return window.setTimeout(()=>{qw.rload(a,f)}, 100); f&&f(a); },
    frameHeight:(s,f)=> {s=qw.obj(s);
        qw.load(s,(x)=> {if(!x)return;
        let d = qw.doc(x),m=0,h,t;
        d.body&&d.body.childNodes.forEach((it)=>{
        if((t=it.offsetTop)!==undefined&&(h=(it.offsetHeight+t))>m) m=h; });
        f&&f(m,x) });
    },
    frameAutoHeight:(s,o)=> { qw.frameHeight(s,(h,e)=> {if(e){e.style.height=(h+9)+"px";if(o)qw.doc(e).body.style.overflowY="hidden";}}) },
    files: {
        group:{ get:(gr)=> { let f="";if(!Array.isArray(gr))gr=[];
                gr.forEach((it)=> { if(it.id_group!=="0"&&it.id_group!=="false"&&it.id_group){
                        if(it.type==='img') f+= qw.files.tmplt.img(it); if(it.type==='doc') f+= qw.files.tmplt.doc(it); if(it.type==='video') f+= qw.files.tmplt.video(it); } });
                return f===""?qw.files.tmplt.img({preview:"/files/systems/no_photo_100_100.jpg"}):f; }
        },
        tmplt:{
            img:(d)=>{return `<img src="${d.preview}" class="qw_image" style="margin: 2px;max-width:40px;max-height:27px;border-radius: .25rem;">`;},
            doc:(d)=>{return `<img src="//b2bot.ru/components/upload/icons/${d.ext}.png"
                onError="this.src='//b2bot.ru/components/upload/icons/empty.png'" class="qw_video" style="max-width:40px;max-height:27px;margin: 2px;border-radius: .25rem;">`;},
            video:(d)=>{return `<img src="//b2bot.ru/components/upload/icons/video.png" class="qw_video" style="max-width:40px;max-height:27px;margin: 2px;border-radius: .25rem;">`;},
        }
    },
    modal(e){return new bootstrap.Modal(qw.qs(e))},
    arr:{
        rm:(d,e,l)=>{ l.splice(qw.arr.ind(d,e,l), 1); },
        get:(g,e,t)=>{ return (i=qw.arr.ind(g,e,t))===false?false:t[i]; },
        ind:(i,n,d)=>{ let x=false; d.forEach((t, q)=> { if(t[i]==n) return x=q; }); return x; },
        format:(a,t,v)=>{ return a.map(function (it) { return {text: it[t], value: it[v]} }); },
        copy:(o)=>{ if (null == o || "object" != typeof o) return o;
            let c = o.constructor();
            for (var a in o) if (o.hasOwnProperty(a)) c[a] = qw.arr.copy(o[a]);
            return c;
        }
    },
    lang:{
        get:(s)=>{ let res={}; qw.qsa(s).forEach((el)=>{ res[el.dataset.iso]=el.value; }); return JSON.stringify(res); },
        post:(d)=>{ let x=d.fields; for(let f in x) for(l in x[f]) if(e=qw.qs(`[data-field="${f}"][data-iso="${l}"]`)) e.value=x[f][l]; }
    },
    cookie:{
        ch:()=>{if(navigator.cookieEnabled === false) return console.warn('Cookies are disabled!');return 1;},
        set:(n, v, p=30)=>{ if(!qw.cookie.ch()) return;
            let d = new Date();
            d.setTime(d.getTime() + (p * 24 * 60 * 60 * 1000));
            document.cookie = n + "=" + JSON.stringify({v:v}) + "; expires=" + d.toGMTString() + "; path=/"; 
        },
        get:(n, arr=1)=>{ if(!qw.cookie.ch()) return;
            let m = n + "=",a = document.cookie.split(';'),i,c;
            for (i = 0; i < a.length; i++)
                if ((c = a[i].trim()).indexOf(m) == 0) {
                    let f=c.substring(m.length, c.length);
                    return arr?JSON.parse(f).v:f
                };
            return "";
        },
        delete:(n)=>{ if(!qw.cookie.ch()) return;
            document.cookie = n+"=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
        }
    },
    cloud:{
        onchangefunc:[],
        change:(d)=>{qw.cloud.onchangefunc.forEach((x)=>x(d))},
        set:(v,d,f=false)=> { qw.post("/ajax/qw.cloud.php", {v:v,d:d,cmd:"set"},(r)=>{ qw.cloud.change(r.res) },"json");
        },
        get: (v)=> {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/ajax/qw.cloud.php', false);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send("cmd=get&v="+v);
            return JSON.parse(xhr.response)['res'];
        },
        delete:(v,f=false)=>{
            qw.post("/ajax/qw.cloud.php", {v:v,cmd:"delete"},(r)=>{ f&&f(r);  },"json");
        }
    }
}










