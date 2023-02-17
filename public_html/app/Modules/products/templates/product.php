<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
//$data=$db->arrayQuery('SELECT * FROM `table`');
$asset->regCss(Bt::getAlias("@root/system/modules/products/templates/style.css"));

class marketItemData
{
    public static $LIST=[];
    public static $TRANSLATES;
    public static $CATEGORY_ITEMS;
    public static $FILES;
    public static $CURRENT;
    
    public function __construct($v){
        self::$CURRENT = $v['item'];
        self::$TRANSLATES = $v['translates'];
        self::$FILES=$v['files'];
        
        $files=self::$FILES;
        $goods=[];

        self::$CURRENT['files']=[];
        self::$CURRENT['preview']='';
        if($img=self::$CURRENT['files_id']) {
            $filesFilter=array_values(array_filter($files, function($it)use($img){ return $it['id_group']==$img; }));
            self::$CURRENT['preview']=count($filesFilter)?($filesFilter[0]['medium_size']?:$filesFilter[0]['large_size']):'';
            self::$CURRENT['files']=$filesFilter;
        }

        
        //foreach($v['cats'] as $r)
        //self::$LIST[$r['id']]=$r;
//
        //self::$FILES=$v['files'];
        //$url=explode('/', trim($_SERVER['REQUEST_URI'],' /'));
        //
        //if($slug=mb_strtolower(end($url))) {
        //    $cr=array_values(array_filter(self::$LIST, function($it)use($slug){ return mb_strtolower($it['slug'])===$slug;  }));
        //    $current=count($cr)?$cr[0]:false;
        //}
//
        //if($current){
        //    $id=$current['id'];
        //    $current['chainOfParentsСats']=$this->parentsСats($id);
        //    self::$CURRENT = $current;
        //    self::$SUBCATS = array_filter(self::$LIST, function($it)use($id){ return mb_strtolower($it['parent_id'])===$id;  });
        //}
//
        //if($v['data']['goods']??false)
         $this-> category_items();
    }

    public function parentsСats($id){
        $t=[];
        foreach(self::$LIST as $r){
            if($r['id']===$id) {
                if($r['visible'])$t[]=['id'=> $r['id'], 'name'=> $r['name'], 'slug'=> $r['slug']];
                if($r['parent_id']>0) $t=array_merge($this->parentsСats($r['parent_id']), $t);

                return $t;
            }
        }
        return [];
    }

    private function category_items(){
        if(!self::$CURRENT) return [];
        $db=DB::getInstance();
       
        $tr= self::$TRANSLATES; 
        
        $query= $db->arrayQuery('SELECT i.id, i.name, i.short_description, i.description, i.display visible, i.priority, i.files files_id,
                           c.id_category, ct.category, s.val slug,
                           IFNULL(pt.name, "") price_type, IFNULL(pt.id, "") price_type_id,
                           IFNULL(pr.id, "") price_id, pr.price, pr.unit, pr.currency
                           FROM `market_item_categories` c
                           JOIN `s_categories` ct ON ct.id=c.id_category
                           JOIN `market_items` i ON i.id=c.id_item
                           LEFT JOIN `market_items_prices` pr ON pr.id_item=i.id AND pr.by_default=1
                           LEFT JOIN `market_items_prices_type` pt ON pt.id=pr.id_type
                           LEFT JOIN `market_items_seo` s ON s.item_id = i.id AND s.var="slug"
                           WHERE c.id_category=? AND i.id<>?
                           ORDER BY i.priority DESC, REPLACE(i.name, " ","")*1  ASC, TRIM(i.name), IFNULL(pr.price,"");', [ self::$CURRENT['id_category'],self::$CURRENT['id'] ]);
        $files=self::$FILES;
        $goods=[];
        foreach($query as $it){
            foreach ($tr as $t) if($t['field_name']==='name' && $t['row_id']===$it['id']){ $it['name']=$t['text'];break 1; }
            $it['files']=[];
            $it['preview']='';
            if($img=$it['files_id']) {
                $filesFilter=array_values(array_filter($files, function($it)use($img){ return $it['id_group']==$img; }));
                $it['preview']=count($filesFilter)?($filesFilter[0]['medium_size']?:$filesFilter[0]['large_size']):'';
                $it['files']=$filesFilter;
            }
            $goods[$it['id']]=$it;
        }
        
        self::$CATEGORY_ITEMS = $goods;
    }
}

$_ITEM=new marketItemData($block['content']);
$item=$_ITEM::$CURRENT;
$files=$item['files'];
$preview=$item['preview'];

$seo=[];
foreach($db->arrayQuery('SELECT * FROM `market_items_seo` WHERE item_id=?', [ $item['id'] ]) as $s) $seo[$s['var']]=$s['val'];

$title=isset($seo['title'])?($seo['title']?:$item['name']): $item['name'];
$description=isset($seo['description'])?($seo['description']?:$item['description']): $item['description'];
if(count($item['files'])&&$item['files'][0]['small_size']) $favicon='/'.$item['files'][0]['small_size'];

$cloudItems=[];
if(isset($_SESSION['project'])&&isset($_SESSION['project']['market_items']))
    $cloudItems=json_decode($_SESSION['project']['market_items'],1)['items']??[];

$itemCell=$cloudItems[$item['id']]??0;



$cartPage='';
if($data['cartPage']??false)
    if($cpd=$db->singleQuery('SELECT * FROM `web_pages` WHERE id=?',[ $data['cartPage'] ])) $cartPage=$cpd['slug'];

$itemPage='';
if($data['itemPage']??false)
    if($cpd=$db->singleQuery('SELECT * FROM `web_pages` WHERE id=?',[ $data['itemPage'] ])) $itemPage=$cpd['slug'];

$CATEGORY_ITEMS = $_ITEM::$CATEGORY_ITEMS;
?>
<div data-template="" class="market_item_container container">
    <div class="market_item">
        <div class="mi_left_col">
            <div class="hover-image">
                <img class="img-fluid" src="/<?php echo $preview; ?>" id="product-detail">
                <!--             <a hre="/<?php echo $preview; ?>">
    <img src="/templates/sections/market_item/img/zoom.png" alt="" />
</a> -->
            </div>
            <div class="dotted_imgs">
                <?php if(count($files)>1)for($i=0;$i<count($files);$i++){ $file=$files[$i];?>
                    <div class="aimg" data-src="/<?php echo $file['large_size'];?>" style="background:url(/<?php echo $file['small_size']?:$file['large_size'];?>)"></div>
                    <!-- <img src="/<?php echo $file['medium_size']?:$file['large_size'];?>"> -->
                <?php }?>
            </div>
        </div>
        <div class="mi_right_col">
            <div class="item_title"><?php echo $item['name']; ?></div>
            <div class="item_description"><?php echo $item['description']; ?></div>

            <div class="bottom_col">
                <div class="mi_sum" onclick="marketitem.func.gotoCart()">
                    <span class="label_to_cart"></span>
                    <div class="price_data"></div>
                </div>


                <div class="to_cart_area">
                    <div class="to_cart_plusminus_area">
                        <button class="btn_to_cart plus" type="button" onclick="marketitem.btns.minus()">-</button>
                        <button class="btn_to_cart minus" type="button" onclick="marketitem.btns.plus()">+</button>
                    </div>
                    <button class="btn_to_cart add" type="button" onclick="marketitem.btns.plus()"><?php echo $data['btnAddCaption']??'ADD'; ?></button>
                </div>
            </div>

        </div>
        <?php if(count($CATEGORY_ITEMS)){ ?>
            <div class="mi_items_filter_category">
                <div class="mi_title">Товары в категории «<?php echo mb_strtoupper($item['category']);?>»</div>

                <ul class="mi_items">
                    <?php foreach($CATEGORY_ITEMS as $GOOD){ ?>

                        <li class="item" data-slug="<?php echo $GOOD['slug'];?>" onclick="marketitem.gotoitem(this)">
                            <div class="name"> <?php echo $GOOD['name'];?></div>
                            <div class="price"><span class="cell"></span> <?php echo $GOOD['price']. $GOOD['currency'];?></div>
                            <div class="img" style="background:url(/<?php echo $GOOD['preview'];?>)">
                                <!--  <img src="/<?php echo $GOOD['preview'];?>" draggable="false"> -->
                            </div>
                        </li>


                    <?php }?>
                </ul>

            </div>
        <?php }?>
    </div>
</div>

<?php $asset->regJs("https://atuin.ru/demo/intense/intense.min.js");?>
<?php $asset->regJs(Bt::getAlias("@templates/sections/marketitemdata/script.js")); ?>

<script>
    var marketitem={
        data:<?php echo json_encode($item)?>,
        cell:"<?php echo $itemCell?>"*1,
        cloud:false,
        cartPage:"<?php echo $cartPage?>",
        itemPage:"<?php echo $itemPage?>",
        init:()=>{
            document.addEventListener("DOMContentLoaded", ()=>{
                let pr=marketitem.data||[];
                if(pr.price)
                    qw.qs(".price_data").innerHTML=pr.price+""+pr.currency+(pr.unit&&pr.unit!==""?" / "+pr.unit:"");
                marketitem.cloud=qw.cloud.get("market_items")||{"items":{},"data":{}};
                marketitem.btns.label();
                marketitem.btns.display();
                qw.click(".dotted_imgs .aimg", el=>{
                    qw.qs(".img-fluid").src=el.target.dataset.src;
                })

                var elements = document.querySelectorAll( '.img-fluid' );
                Intense( elements );

            });
        },
        gotoitem:th=> {
            if(marketitem.itemPage&&th.dataset.slug)document.location.href="/"+marketitem.itemPage+"/"+th.dataset.slug;
        },
        btns:{
            display:()=>{
                if(marketitem.cell>0) {qw.show(".to_cart_plusminus_area","grid");qw.hide(".btn_to_cart.add");}
                else {qw.hide(".to_cart_plusminus_area", "none");qw.show(".btn_to_cart.add","block");}
            },
            plus:()=>{
                marketitem.cell+=1;
                marketitem.func.calc();
            },
            minus:()=>{
                marketitem.cell-=1;
                if(marketitem.cell<0)marketitem.cell=0;
                marketitem.func.calc();
            },
            label:()=>{
                //let l="<?php echo $data['btnAddTopPrefix']??"";?>";
                //qw.qs(".label_to_cart").innerHTML=marketitem.cell?marketitem.btns.carticon+" "+l+" "+marketitem.cell:"";
                qw.qs(".label_to_cart").innerHTML=marketitem.cell?marketitem.cell+" x ":"";
            },
            carticon:`<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart4" viewBox="0 0 16 16">
                      <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l.5 2H5V5H3.14zM6 5v2h2V5H6zm3 0v2h2V5H9zm3 0v2h1.36l.5-2H12zm1.11 3H12v2h.61l.5-2zM11 8H9v2h2V8zM8 8H6v2h2V8zM5 8H3.89l.5 2H5V8zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/>
                    </svg>`,

        },
        func:{
            calc:()=>{
                if (marketitem.cloud == '[]') marketitem.cloud = {"items":{},"data":{}};
                if(marketitem.cell>0){
                    marketitem.cloud.items[marketitem.data.id]=marketitem.cell;
                    marketitem.cloud.data[marketitem.data.id]=marketitem.data;
                }
                else {
                    delete(marketitem.cloud.items[marketitem.data.id]);
                    delete(marketitem.cloud.data[marketitem.data.id]);
                }

                qw.cloud.set("market_items", marketitem.cloud);

                marketitem.btns.display();
                marketitem.btns.label();
            },
            gotoCart:()=>{
                marketitem.cloud['showcart']=1;
                qw.cloud.set("market_items", marketitem.cloud);
                document.location.href="/"+marketitem.cartPage;
            }
        }
    }



    marketitem.init();

</script>