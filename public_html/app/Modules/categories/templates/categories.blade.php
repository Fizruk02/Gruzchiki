<?php
use App\Models\WebPages;
use Illuminate\Support\Facades\DB;

//$asset->regCss(Bt::getAlias("@root/system/modules/categories/templates/style.css"));

$catPage=false;
if($data)
    //if($cpd=$db->singleQuery('SELECT slug FROM `web_pages` WHERE id=?',[ $item['data']['catPage'] ])) $catPage=$cpd['slug'];
    if($cpd = WebPages::find($data['catPage'])) $catPage=$cpd->slug;

$dbCats=$cats;
if (!function_exists('childCats')){
    function childCats ($dbCats,$id_cat,$arr_cat=[]){
        $result = array_filter($dbCats, function ($it) use($id_cat){
            return $it['parent']==$id_cat;
        });

        foreach($result as $row){
            array_push($arr_cat, $row['id']);

            $arr_cat = childCats($dbCats,$row['id'], $arr_cat);
        }
        return $arr_cat;
    };
}

$categories =  [];

$accessCats=[];
foreach($data['cats'] as $c) {
    $accessCats[]=$c;
    $accessCats=array_merge($accessCats,childCats($dbCats,$c));
}

# находим дочерние (1 уровень, без подкатегорий) категории в тех, которые указаны в настройках модуля в админке.
$tcats=$data['cats'];
$categories=array_merge($categories, $result = array_filter($dbCats, function ($it) use($tcats){
    return in_array($it['parent'], $tcats) !==false;
}));


//$dbFiles=$db->arrayQuery('SELECT * FROM `files` WHERE id_group AND id_group IN(SELECT image FROM `s_categories`) OR id_group IN(SELECT files FROM `market_items`);');
$dbFiles=DB::table('files')
    ->where(DB::raw('id_group AND id_group IN(SELECT image FROM `s_categories`) OR id_group IN(SELECT files FROM `market_items`)'))
    ->get()->toArray();

foreach($categories as &$c){
    if($img=$c['image']) {
        $files=array_values(array_filter($dbFiles, function($it)use($img){ return $it->id_group==$img; }));
        $img=count($files)?($files[0]->medium_size?:$files[0]->large_size):'';
    }
    $c=[
        'img'=> $img?:'',
        'slug'=> $c['slug'],
        'name'=> $c['text'],
        //'category'=> $c['parentName'],
    ];
}
?>
<div data-template="<?php echo $id; ?>">
    <div class="field-categories">
        <?php foreach ($categories as $SUBCAT) { ?>
            <a class="item" href="<?php echo $catPage?'/'.$catPage.'/'.$SUBCAT['slug']:''; ?>">
                <div class="name"> <?php echo $SUBCAT['name']; ?></div>
                <div class="img" style="background: url(/<?= $SUBCAT['img']; ?>);"></div>
            </a>
        <?php } ?>
    </div>
</div>
<?php
//$asset->regJs(Bt::getAlias("@templates/modules/categories/templates/script.js"));
?>
@vite(['app/modules/categories/templates/style.css', 'app/modules/categories/templates/script.js'])
