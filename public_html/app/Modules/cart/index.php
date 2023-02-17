<?php
$item = $block['content'];
$data=$item['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
//$data=$db->arrayQuery('SELECT * FROM `table`');
$asset->regCss(Bt::getAlias("@templates/sections/cart/style.css"));
$cartitems=[];


if(isset($_SESSION['project']) && isset($_SESSION['project']['market_items']) ){
    $itms=json_decode($_SESSION['project']['market_items'],1);
    $cartitems=$itms['items'];
}

$items=[];

foreach ($item['items'] as $it)
    if(isset($cartitems[$it['id']])){
        $files=$db->arrayQuery('SELECT * FROM `files` WHERE id_group=?', [ $it['files'] ]);
        $it['cell']=$cartitems[$it['id']];
        $it['preview']=count($files)?($files[0]['medium_size']?:$files[0]['large_size']):'';
        $items[]=$it;
    }


class fields {
     private function wrap($id,$t,$r,$c){
        return '<div class="fields_type_input data_fields" data-id="'.$id.'" data-type="'.$t.'" data-required="'.$r.'">'.PHP_EOL.
            $c.PHP_EOL.
            '</div>'.PHP_EOL;
     }

     public function input($it){
             return $this-> wrap($it['id'],$it['type'],$it['required'],
                 '<span>'.$it['title'].'</span>'.PHP_EOL.
                 '<input class="fields_val" type="text" placeholder="'.$it['placeholder'].'">');
     }
}

$fields= new fields;

$endPage='';
if($data['end_page']??false)
    if($cpd=$db->singleQuery('SELECT * FROM `web_pages` WHERE id=?',[ $data['end_page'] ])) $endPage=$cpd['slug'];

$itemPage='';
if($data['itemPage']??false)
    if($cpd=$db->singleQuery('SELECT * FROM `web_pages` WHERE id=?',[ $data['itemPage'] ])) $itemPage=$cpd['slug'];

$a_delivery=false;
$a_fields=count($data['fields']);
$a_delivery=$data['delivery'];
?>

<style>
.cart_container {
    <?php if($a_delivery&&$a_fields){?>
        grid-template-areas: "field_cart_items field_cart_delivery" "field_cart_fields null";
        grid-template-columns: 1fr 400px;
    <?php }
    if(!$a_delivery&&$a_fields){?>
        grid-template-areas: "field_cart_items field_cart_fields";
        grid-template-columns: 1fr 400px;
    <?php }
    if($a_delivery&&!$a_fields){?>
        grid-template-areas: "field_cart_items field_cart_delivery";
        grid-template-columns: 1fr 400px;
    <?php }
    if(!$a_delivery&&!$a_fields){?>
        grid-template-areas: "field_cart_items";
        grid-template-columns: 1fr;
    <?php }?>
}

<?php if($itemPage) {?>  
    .cart-item img, .cart-item .cart-item-title {
        cursor: pointer;
    }
<?php }?>
</style>
<div data-template="">
    <div class="cart_container">
        <div class="field_cart_items">
        <?php if(!count($cartitems)){ ?>
        <h1>Корзина пуста</h1>
        <?php } else { ?>
        <div class="cart_items_title">
            <div class="cart_delivery_title">Заказ</div>
        </div>
            
            <div class="cart_items">
                <?php foreach ($items as $item){
                    $bg='';
                    if($item['preview']) {
                        $bg='<div style="background-image:url(/'.$item['preview'].');" class="me-1 img"></div>';
                    } else {
                        $bg='<div style="background-color:#363636" class="me-1 img">'.mb_strtoupper(mb_substr($item['name'],0,1,'UTF-8')).'</div>';
                    }
                    $price= $item['price']?'x '.$item['price'].$item['currency']:'';


                    ?>
                    <div id="cart-item-<?php echo $item['id'];?>" class="cart-item" data-slug="<?php echo $item['slug']; ?>">
                        <?php echo $bg;?>
                        <div class="cart-item-title">
                            <div><b><?php echo $item['name'];?></b></div>
                            <!-- <small><i class="bi bi-x-lg float-end" onclick="cartitemdelete(${it.id})"></i></small> -->
                            <div><span style="color:#F8A917"><span class="cell"><?php echo $item['cell'];?></span> <?php echo $item['unit'];?></span> <?php echo $price;?></div>
                        </div>
                        <div class="minus" onclick="cart.func.minus(<?php echo $item['id'];?>)"><div class="cart_minus_block"></div></div>
                        <div class="plus" onclick="cart.func.plus(<?php echo $item['id'];?>)"><div class="cart_plus_block"></div></div>
                    </div>
                <?php } ?>
            </div>

						<?php if($data['sendBtn']=="1"){?>
            <div class="cart_send_btn_area">
                <button class="cart_send_btn" onclick="cart.func.send()">OK</button>
            </div>
						<?php } ?>

        <?php } ?>
        </div>
        
        <?php if($a_delivery){?>
        
        <div class="field_cart_delivery">
            <div class="cart_delivery_title">Доставка</div>
            
            <div class="address_group">
                <input type="text" class="form-control" id="address" list="addresslist" placeholder="Введите адрес" oninput="cart.suggest.get()">
                <label for="address">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-house-heart" viewBox="0 0 16 16">
                      <path d="M8 6.982C9.664 5.309 13.825 8.236 8 12 2.175 8.236 6.336 5.309 8 6.982Z"/>
                      <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.707L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.646a.5.5 0 0 0 .708-.707L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                    </svg>
                </label>
                
            </div>
            <datalist id="addresslist" onselect="console.log(this.value)"></datalist>
            
            <div class="address_details">
                <div class="form-floating">
                  <input type="text" class="form-control" id="address_details_app" placeholder="кв.">
                  <label for="address_details_app">Кв.</label>
                </div>
                <div class="form-floating">
                  <input type="text" class="form-control" id="address_details_entr" placeholder="подъезд">
                  <label for="address_details_entr">Подъезд</label>
                </div>
            </div>
            
            <div class="form-floating address_area_comment">
                <input type="text" class="form-control" id="address_details_comment" placeholder="комментарий">
                <label for="address_details_comment">Комментарий</label>
            </div>
            
        </div>
        <?php } ?>
        
        
        
        
        
        <?php if($a_fields){?>
        <div class="field_cart_fields">
            <div class="cart_fields_title">Поля</div>
                <div class="field_cart_fields_area">
                    <?php foreach ($data['fields'] ?? [] as $key => $field){
                        if(method_exists($fields, ($m=$field['type']))) echo $fields-> $m($field);
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php } ?>
        
        
        
        
        
        
        
        
        
        
        
    </div>
</div>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/css/suggestions.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@20.3.0/dist/js/jquery.suggestions.min.js"></script>

 -->
<script>

    //$("#address").suggestions({
    //    token: "bbe06250ea4236a1fa030b7f662db48e0ccb4fe8",
    //    type: "ADDRESS",
    //    /* Вызывается, когда пользователь выбирает одну из подсказок */
    //    onSelect: function(suggestion) {
    //        console.log(suggestion);
    //    }
    //});

var cart={
        data:{
            endPage:"<?php echo $endPage; ?>",
            itemPage:"<?php echo $itemPage; ?>",
            cloud:false,
            get:()=> {
                let p=[],s=1;
                document.querySelectorAll(".data_fields").forEach(function (it){
                    let v=it.querySelector(".fields_val").value.trim();
                    if(it.dataset.required==="1"&&v==="")it.classList.add("fields_empty"),s=false;else it.classList.remove("fields_empty");
                    p.push({
                        id:it.dataset.id,
                        val:v
                    });
                    
                })
                return s?p:false;
            }
        },
		init:()=> {
			cart.listener.validate();
			cart.func.getcloud();
			let e;
			if(cart.data.cloud.fields&&Array.isArray(cart.data.cloud.fields))
				cart.data.cloud.fields.forEach((it)=> {
						if(e=qw.qs(`.data_fields[data-id="${it.id}"] .fields_val`)) e.value=it.val;
				})
			qw.click(".cart-item img, .cart-item .cart-item-title", el=>{
			    if(cart.data.itemPage) document.location.href=cart.data.itemPage+"/"+el.target.closest(".cart-item").dataset.slug;
			    
			});
				
		},
        listener:{
            validate:()=>{
                qw.lstnr('.data_fields[data-required="1"] .fields_val','input',(it)=>{
                    let cl=it.target.closest(".data_fields").classList;
                    if(it.target.value.trim()==="") cl.add("fields_empty"); else cl.remove("fields_empty");
                })
            }
        },
        suggest: {
            data:[],
            get:()=> {
                let a=qw.qs("#address").value;
                qw.post("/admin/dadataru/suggest.php",{address:a},(r)=> {
                    if(r.success){
                        let h="";
                        cart.suggest.data=r.data;
                        r.data.forEach((it,i)=>{
                            h+=`<option data-value="${i}">${it.value}</option>`;
                        });
                        qw.qs("#addresslist").innerHTML=h;
                        console.log(cart.suggest.data);
                    }
                },"json")
                
            }
        },
        func:{
            getcloud:()=>{
                cart.data.cloud=qw.cloud.get("market_items")||{items:{}};
                if(!cart.data.cloud["items"])cart.data.cloud["items"]={};
            },
            send:(id)=>{
                let fields=cart.data.get();
                if(!fields) return;
				cart.data.cloud['fields']=fields;
				qw.cloud.set("market_items",cart.data.cloud,1)
                qw.cloud.delete("market_items");
                location.href="/"+cart.data.endPage;
            },
            plus:(id)=>{
                cart.func.getcloud();
                let cell=(cart.data.cloud.items[id]||0)+1;
                cart.func.edit(id,cell);
            },
            minus:(id)=>{
                cart.func.getcloud();
                let cell=(cart.data.cloud.items[id]||0)-1;
                if(cell<0) cell=0;
                cart.func.edit(id,cell);
            },
            edit:(id,cell)=>{
                qw.qs(`.cart_items #cart-item-${id} .cell`).innerHTML=cell;
                if(cell>0)
                cart.data.cloud.items[id]=cell;
                else delete cart.data.cloud.items[id];
                qw.cloud.set("market_items",cart.data.cloud,1)
            },
        }
    }
    
    
    cart.init();
    
    
</script>


<?php $asset->regJs(Bt::getAlias("@/templates/sections/cart/script.js")); ?>
