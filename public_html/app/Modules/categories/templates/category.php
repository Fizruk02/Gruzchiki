<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
//$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
//$data=$db->arrayQuery('SELECT * FROM `table`');
$asset->regCss(Bt::getAlias("@root/system/modules/categories/templates/category.css"));

$console=$item['console']??0;

class category
{
    public static $LIST=[];
    public static $SUBCATS;
    public static $GOODS;
    public static $FILES;
    public static $CURRENT;
    
    public function __construct($v){
        foreach($v['cats'] as $r)
            self::$LIST[$r['id']]=$r;

        self::$FILES=$v['files'];
            $url=explode('/', trim($_SERVER['REQUEST_URI'],' /'));
        
        if($slug=mb_strtolower(end($url))) {
            $cr=array_values(array_filter(self::$LIST, function($it)use($slug){ return mb_strtolower($it['slug'])===$slug;  }));
            $current=count($cr)?$cr[0]:false;
        }

        if($current){
            $id=$current['id'];
            $current['chainOfParentsСats']=$this->parentsСats($id);
            self::$CURRENT = $current;
            self::$SUBCATS = array_filter(self::$LIST, function($it)use($id){ return mb_strtolower($it['parent_id'])===$id;  });
        }

        if($v['data']['goods']??false)
            $this-> goods();
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

    private function goods(){
        if(!self::$CURRENT) return [];
        $db=DB::getInstance();
        
        $tr= $db->arrayQuery('SELECT `field_name`, `row_id`, `text` FROM `s_translates` WHERE `table_name`="market_items"  AND iso=?', [ Bt::$config->lang ]); 
        
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
                           WHERE c.id_category=?
                           ORDER BY i.priority DESC, REPLACE(i.name, " ","")*1  ASC, TRIM(i.name), IFNULL(pr.price,"");', [ self::$CURRENT['id'] ]);
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

        self::$GOODS = $goods;
    }
}

$_CAT=new category($block['content']);
?>

<div class="center">
	<div class="cat-container">
		<div class="field-goods">
            <?php foreach($_CAT::$SUBCATS as $SUBCAT){?>
                <div class="item" data-id="<?php echo $SUBCAT['id'];?>" onclick="marketCategoryPage.category(this)">
                    <div class="name"> <?php echo $SUBCAT['name'];?></div>
                    <div class="img" style="background:url(/<?php echo $SUBCAT['preview'];?>)">
                        <!--  <img src="/<?php echo $SUBCAT['preview'];?>" draggable="false"> -->
                    </div>
                </div>
            <?php }?>
			<?php foreach($_CAT::$GOODS as $GOOD){?>
                <div class="item" data-id="<?php echo $GOOD['id'];?>" onclick="marketCategoryPage.item(this)">
                    <div class="name"> <?php echo $GOOD['name'];?></div>
                    <div class="price"><span class="cell"></span> <?php echo $GOOD['price']. $GOOD['currency'];?></div>
                    <div class="img" style="background:url(/<?php echo $GOOD['preview'];?>)">
                       <!--  <img src="/<?php echo $GOOD['preview'];?>" draggable="false"> -->
                    </div>

                    <div class="btns">
                        <button class="btn_to_cart plus" type="button" onclick="marketCategoryPage.btns.minus(<?php echo $GOOD['id'];?>)">-</button>
                        <button class="btn_to_cart minus" type="button" onclick="marketCategoryPage.btns.plus(<?php echo $GOOD['id'];?>)">+</button>
                    </div>
                </div>
			<?php }?>
		</div>
		<div class="field-category">
            <?php if($_CAT::$CURRENT['preview']){?>
			    <div class="cat-preview"  style="background:url(/<?php echo $_CAT::$CURRENT['preview'];?>)"></div>
            <?php }?>
            <div class="cat-parents">
                <?php if(count($_CAT::$CURRENT['chainOfParentsСats'])>1){
                    foreach(array_slice($_CAT::$CURRENT['chainOfParentsСats'], 0, sizeof($_CAT::$CURRENT['chainOfParentsСats'])-1) as $pcat){
                        echo '<span onclick="marketCategoryPage.parentcategory(\''.$pcat['slug'].'\')">'.$pcat['name'].'</span>';
                    }
                    ?>


                <?php }?>
            </div>

			<h1 class="cat-name">	<?php echo $_CAT::$CURRENT['name'];?> </h1>
			<div class="cat-descr">	<?php echo $_CAT::$CURRENT['descr']??'';?> </div>
		</div>
	</div>
</div>



<script>
    
    var categoryController={
        console:<?php echo $console; ?>,
        init:()=> {
            if(categoryController.console){
                console.log("$_CAT::$CURRENT");
                console.table(<?php echo json_encode($_CAT::$CURRENT); ?>);
                console.log("$_CAT::$LIST");
                console.table(<?php echo json_encode($_CAT::$LIST); ?>);
                console.log("$_CAT::$GOODS");
                console.table(<?php echo json_encode($_CAT::$GOODS); ?>);
                console.log("$_CAT::$SUBCATS");
                console.table(<?php echo json_encode($_CAT::$SUBCATS); ?>);
            }
            
        }
    }

    categoryController.init();

    var marketCategoryPage={
        cloud:false,
        goods:<?php echo json_encode($_CAT::$GOODS)?>,
        cats:<?php echo json_encode($_CAT::$SUBCATS)?>,
        init:()=> {
            marketCategoryPage.cloud=qw.cloud.get("market_items")||{'items':{},'data':{}};
            if (marketCategoryPage.cloud.length == 0)
                marketCategoryPage.cloud={'items':{},'data':{}};
            for(it in marketCategoryPage.cloud.items)
                if(marketCategoryPage.goods[it]) marketCategoryPage.templates.cell(it,marketCategoryPage.cloud.items[it]);

            marketCategoryPage.btnvisible();
        },
        btns:{
            plus:(id)=> {
                marketCategoryPage.calc(id,+1);
            },
            minus:(id)=> {
                marketCategoryPage.calc(id,-1);
            }
        },
        calc:(id,cl)=> {
            event.stopPropagation();
            let c=marketCategoryPage.cloud.items[id]||0;
            c+=cl;if(c<0)c=0;
            marketCategoryPage.cloud.items[id]=c;
            if(c===0) delete marketCategoryPage.cloud.items[id];

            let l=qw.arr.copy(marketCategoryPage.cloud.items);
            for(n in l)l[n]=marketCategoryPage.goods[n];
            marketCategoryPage.cloud['data']=l;
            marketCategoryPage.templates.cell(id,c);
            qw.cloud.set("market_items", marketCategoryPage.cloud);
            marketCategoryPage.btnvisible();
        },
        btnvisible:()=> {
            let b=qw.qs(".btn-cart");if(!b)return;
            if(Object.keys(marketCategoryPage.cloud.items).length===0) qw.qs(".btn-cart").style.display="none";
            else qw.qs(".btn-cart").style.display="block";
        },
        item:(th)=> {
            location.href="/product/"+marketCategoryPage.goods[th.dataset.id].slug;
        },
        category:(th)=> {
            location.href="/category/"+marketCategoryPage.cats[th.dataset.id].slug;
        },
        parentcategory:(slug)=> {
            location.href="/category/"+slug;
        },
        templates:{
            cell:(id,c)=>{
                qw.qs(`.field-goods .item[data-id="${id}"] .price .cell`).innerHTML=c===0?"":c+"x"
            }
        }
    }
    marketCategoryPage.init();
</script>




<?php //$asset->regJs("/templates/sections/category/script.js"); ?>
