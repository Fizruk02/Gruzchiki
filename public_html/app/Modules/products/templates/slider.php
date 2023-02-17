<?php $item = $block['content'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
//$data=$db->arrayQuery('SELECT * FROM `table`');
$asset->regCss(Bt::getAlias("@root/system/modules/products/templates/slider.css"));

$dbCats=$item['cats'];

if (!function_exists('childCats')){
    function childCats ($dbCats,$id_cat,$arr_cat=[]){
        $result = array_filter($dbCats, function ($it) use($id_cat){
            return $it['parent_id']==$id_cat;
        });
        foreach($result as $row){
            array_push($arr_cat, $row['id']);

            $arr_cat = childCats($dbCats,$row['id'], $arr_cat);
        }
        return $arr_cat;
    };

    function miGetPageSlug($id){
        $db=DB::getInstance();
        if($cpd=$db->singleQuery('SELECT slug FROM `web_pages` WHERE id=?',[ $id ])) $slug=$cpd['slug'];
        return $slug??false;
    }

}
$catPage=false;
if($item['data']['ch_cat']&&$item['data']['catPage'])
    $catPage=miGetPageSlug($item['data']['catPage']);

$itemPage=false;
if($item['data']['ch_items']&&$item['data']['itemPage'])
    $itemPage=miGetPageSlug($item['data']['itemPage']);


$categories =  [];

$accessCats=[];
foreach($item['data']['cats'] as $c) {
    $accessCats[]=$c;
    $accessCats=array_merge($accessCats,childCats($dbCats,$c));

}

# находим дочерние (1 уровень, без подкатегорий) категории в тех, которые указаны в настройках модуля в админке.
$tcats=$item['data']['cats'];
if($item['data']['ch_cat']) $categories=array_merge($categories, $result = array_filter($dbCats, function ($it) use($tcats){
    return in_array($it['parent_id'], $tcats) !==false;
}));


$dbFiles=$db->arrayQuery('SELECT * FROM `files` WHERE id_group AND id_group IN(SELECT image FROM `s_categories`) OR id_group IN(SELECT files FROM `market_items`);');

$res=[];

if($item['data']['ch_cat'])
    foreach($categories as $c){
        if($img=$c['image']) {
            $files=array_values(array_filter($dbFiles, function($it)use($img){ return $it['id_group']==$img; }));
            $img=count($files)?($files[0]['medium_size']?:$files[0]['large_size']):'';
        }

        $res[]=[
            'type'=>  'cat',
            'img'=> $img?:'',
            'page'=> $catPage,
            'slug'=> $c['slug'],
            'name'=> $c['category'],
            'category'=> $c['parentName'],
        ];
    }


if($item['data']['ch_items'])
    foreach($item['items'] as $c)
        if(in_array($c['id_category'], $accessCats)){
        if($img=$c['files']) {
            $files=array_values(array_filter($dbFiles, function($it)use($img){ return $it['id_group']==$img; }));
            $img=count($files)?($files[0]['medium_size']?:$files[0]['large_size']):'';
        }

        $res[]=[
            'type'=>  'item',
            'img'=> $img?:'',
            'page'=> $itemPage,
            'slug'=> $c['slug'],
            'name'=> $c['name'],
            'category'=> $c['category']
        ];
    }


//ddd($item);
$item['id'] = 1;
?>

<style>

    .market_item_slider[data-id="<?php echo $item['id'];?>"] .market_item_slider__items {
        transition: transform <?php echo $item['data']['transform_speed']??500?>ms ease
    }


    .market_item_slider__wrapper {
        overflow: hidden;
        margin-left: -5px;
        margin-right: -5px;
    }

    @media (min-width: 768px) {
        .market_item_slider__item {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (min-width: 1200px) {
        .market_item_slider__item {
            flex: 0 0 33.3333333333%;
            max-width: 33.3333333333%;
        }
    }

    .market_item_slider__item-content {
        padding-left: 5px;
        padding-right: 5px;
    }

    .market_item_slider__content_header {
        position: relative;
    }

    .market_item_slider__content_img {
        display: block;
        height: auto;
        width: 100%;
    }

    .market_item_slider__content_section {
        position: absolute;
        bottom: 6px;
        left: 10px;
        background-color: rgba(0, 0, 0, 0.5);
        color: #fff;
        padding: 6px 10px;
        font-size: 14px;
        border-radius: 12px;
        line-height: 1;
    }

    .market_item_slider__content_title {
        background: #fafafa;
        color: #424242;
        white-space: normal;
        font-weight: 700;
        font-size: 20px;
        line-height: 1.3;
        padding: 10px 15px;
        margin: 0;
        height: 52px;
    }

    .market_item_slider__control {
        background-color: unset;
    }

    .market_item_slider__control:hover,
    .market_item_slider__control:focus {
        background-color: rgba(0, 0, 0, 0.1);
    }

    .market_item_slider__indicators {
        bottom: -35px;
    }

    .market_item_slider__indicators li {
        background-color: #eee;
    }

    .market_item_slider__indicators li.active {
        background-color: #757575;
    }
</style>

<div data-template="<?php echo $item['id'];?>">

<script>


    document.addEventListener('DOMContentLoaded', function () {
        const market_item_slider = new _market_item_slider('.market_item_slider[data-id="<?php echo $item['id'];?>"]', {
            loop: true,
            autoplay: true,
            interval: <?php echo $item['data']['interval']??5000?>,
            refresh: true,
        });
    });
</script>


    <div class="container">
        
        <div class="market_item_slider" data-id="<?php echo $item['id'];?>">
            <div class="market_item_slider__container">
                
                <div class="market_item_slider__title">
                    <?php echo $item['data']['title']??""?>
                </div>
                
                <div class="market_item_slider__wrapper">
                    <div class="market_item_slider__items">

                        <?php foreach($res as $key=>$row){ ?>
                            <a href="<?php echo $row['page']?('/'.$row['page'].'/'.$row['slug']??""):'#'?>" class="market_item_slider__item"
                                        data-type="<?php echo $row['type'];?>">
                                <div class="market_item_slider__item-container">
                                    <div class="market_item_slider__item-content">
                                        <div class="market_item_slider__content_header">
                                            <img class="market_item_slider__content_img" src="/<?php echo $row['img']??""?>" alt="..." width="350" height="250" loading="lazy">
                                            <span class="market_item_slider__content_section"><?php echo $row['category']??""?></span>
                                        </div>
                                        <h2 class="market_item_slider__content_title"><?php echo $row['name']??""?></h2>
                                    </div>
                                </div>
                            </a>
                        <?php }?>




                    </div>
                </div>
                <a href="#" class="market_item_slider__control" data-slide="prev"></a>
                <a href="#" class="market_item_slider__control" data-slide="next"></a>
                <ol class="market_item_slider__indicators">
                    <?php foreach($res as $key=>$row) echo '<li data-slide-to="'.$key.'"></li>'; ?>

                </ol>
            </div>
        </div>
    </div>


</div>

<?php $asset->regJs(Bt::getAlias("@root/system/modules/products/templates/slider.js")); ?>

