<?php $data = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();
//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
//$data=$db->arrayQuery('SELECT * FROM `table`');
$type=$data['type'] ?? null;
$asset->regCss(Bt::getAlias('@templates/sections/slider/styles/style.css'));
if ($type) $asset->regCss(Bt::getAlias('@templates/sections/slider/styles/'.$type.'.css'));
?>

<div data-template="<?php echo $block['content']['id']?>">

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const slider = new Slider('.slider', {
        loop: true,
        autoplay: true,
        interval: 5000,
        refresh: true,
      });
    });
  </script>

  <div class="container">

    <div class="slider">
      <div class="slider__container">
        <div class="slider__wrapper">
          <div class="slider__items">

            <?php foreach($data['slides']??[] as $key=>$row){ ?>
                <div class="slider__item" > <!-- style="background:url(<?php echo $row['img']?>) no-repeat;background-size:cover;height:400px" -->
                    <img class="slider__content_img" src="<?php echo $row['img']?>" alt="..." loading="lazy">  <!-- width="350" height="250" -->
                </div>
            <?php }?>




          </div>
        </div>
        <a href="#" class="slider__control" data-slide="prev"></a>
        <a href="#" class="slider__control" data-slide="next"></a>
        <ol class="slider__indicators">
            <?php foreach($data['slides']??[] as $key=>$row) echo '<li data-slide-to="'.$key.'"></li>'; ?>
        </ol>
      </div>
    </div>
  </div>



</div>
<?php $asset->regJs(Bt::getAlias("@templates/sections/slider/script.js")); ?>










