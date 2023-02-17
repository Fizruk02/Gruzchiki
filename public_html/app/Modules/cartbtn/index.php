<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
//$data=$db->arrayQuery('SELECT * FROM `table`');

$cartPage='';
if($data['cartPage']??false)
    if($cpd=$db->singleQuery('SELECT * FROM `web_pages` WHERE id=?',[ $data['cartPage'] ])) $cartPage=$cpd['slug'];

$asset->regCss(Bt::getAlias("@templates/sections/cartbtn/style.css"));
?>


<script>
    var cartbtn={
        label:"КОРЗИНА",
        btn:false,
        cloud:false,
        cartPage:"<?php echo $cartPage; ?>",
        init:()=> {
            if((cartbtn.btn=qw.qsa("._cartbtn")).length===0) return;
            cartbtn.btn.forEach(x=> { x.innerHTML=cartbtn.label;x.style.display="block"});
            qw.click("._cartbtn",e=>{ if(cartbtn.cartPage!=="")document.location.href="/"+cartbtn.cartPage; });
            qw.cloud.onchangefunc.push(cartbtn.change);
            cartbtn.cloud=qw.cloud.get("market_items");
            cartbtn.edit();
        },
        change:(r)=> {
            if(r.v!=="market_items") return;
            let d=JSON.parse(r.d);
            cartbtn.cloud=d;
            cartbtn.edit();
        },
        edit:()=>{

            if(!cartbtn.cloud||!cartbtn.cloud.items||!cartbtn.cloud.data) return;
            let sum=cartbtn.sumcart(1),
            t=sum.length>0?" · "+sum.join(" · "):"";
            cartbtn.btn.forEach(x=> x.innerHTML=cartbtn.label+t)
        },
        sumcart(arr=false){
            let d=cartbtn.cloud;
            let i,l,price=0,t={},v,vl,res=[];
            for(s in d.items){
                l=Number(d.data[s].price)*d.items[s];
                if(arr){
                    vl=d.data[s].currency;
                    t[vl]=(t[vl]||0)+l;
                }
                if(i!==false) price+=l;
            }
        
            if(arr)
            for(k in t) t[k]>0&&res.push(t[k]+k)
            return arr?res:price;
        }
    }
    cartbtn.init();
</script>

<?php $asset->regJs(Bt::getAlias("@templates/sections/cartbtn/script.js")); ?>
