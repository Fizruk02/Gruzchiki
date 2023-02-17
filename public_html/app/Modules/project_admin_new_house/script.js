var newhouse={
    suggest: {
        data:[],
        current:false,
        get:()=> {
            let a=qw.qs("#address").value;
            qw.post("/admin/dadataru/suggest.php",{address:a},(r)=> {
                if(r.success){
                    let h="";
                    newhouse.suggest.data=r.data;
                    r.data.forEach((it,i)=>{
                        h+=`<option data-value="${i}">${it.value}</option>`;
                    });
                    qw.qs("#addresslist").innerHTML=h;
                }
            },"json")
            
        },
        detailed:()=>{
            let address=qw.qs("#address").value;
            let current=false;
            newhouse.suggest.data.forEach(it=>{
                if(it.value===address) current=it;
            })
            if(!current) {
                qw.qs("#address").value="";
                qw.qs(".detailed-address").innerHTML="";
                return;
            }
            let h="";
            ["country","city","street","house", "block"].forEach(it=>{
                if(current.data[it])h+=`<div class="address-item">${current.data[it]}</div>`;
            })
            qw.qs(".detailed-address").innerHTML=h;
            newhouse.suggest.current=current;
        },
        
    },
    save:()=>{
        let par=newhouse.get();
        if(!par) return false;
        par["addressDetailed"]=newhouse.suggest.current;
        
        qw.post("/ajax/p.php?q=newHouseAdd",par,r=>{
            document.location.href="/adm/chess";
        },"json")
    },
    get:()=> {
        let p={},s=1;
        document.querySelectorAll(".data-field").forEach(it=>{
            let v=it.querySelector(".fields_val").value.trim();
            if(it.dataset.required==="1"&&v==="")it.classList.add("fields_empty"),s=false;else it.classList.remove("fields_empty");
            p[it.dataset.id]=v;
        })
        return s?p:false;
    }
    
}