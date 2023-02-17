<?php $item = $block['content']['data'];
use system\lib\Db;
use system\lib\Asset;
$db=DB::getInstance();
$asset = Asset::getInstance();

//$title=''; // - page title
//$description=''; // - page description
//$favicon=''; // - page favicon
//$data=$db->arrayQuery('SELECT * FROM `table`');

$type="2";

$menu=$item['menu']??false;
if($menu)
    $footerMenuItems=$db->arrayQuery('SELECT * FROM `web_menu_items` WHERE menu_id=? ORDER BY sort, `name`', [ $menu ]);


//@vite(['resources/css/main.css', 'resources/js/app.js']);
$asset->regCss(Bt::getAlias("@templates/sections/footer/style.css", ['bootstrap']));
?>

<div class="footer-area">
		<div></div>
<div class="container">
	<footer class="pt-3 mt-4">


        <?php if($type==1&&$menu&&count($footerMenuItems)){?>
        <ul class="nav justify-content-center">
            <?php foreach($footerMenuItems as $r) echo '<li class="nav-item"><a href="'.$r['link'].'" class="nav-link px-2 text-muted">'.$r['name'].'</a></li>'?>
        </ul>
        <?php }?>

       <?php if($type==2){?>

        <div class="row top_row">

<?php if($menu&&count($footerMenuItems)){?>
  <div class="col-3">
    <h5>Страницы</h5>
    <ul class="nav flex-column">
    <?php foreach($footerMenuItems as $r) echo '<li class="nav-item mb-2"><a href="'.$r['link'].'" class="nav-link p-0 text-muted">'.$r['name'].'</a></li>'?>
    </ul>
  </div>
<?php }?>


<div class="col-3">
  <h5>Контакты</h5>
  <ul class="nav flex-column">
			<?php foreach($item['contacts']??[] as $key=>$row){ ?>
				<li class="nav-item mb-2">
						<?php echo $row['text']; ?>
			</li>
			<?php }?>
<!--     Email: <a class="nav-link p-0 text-muted" href="mailto:<?=$item['email']?>"><?=$item['email']?></a> -->
  </ul>
</div>



<!--           <div class="col-4 offset-1">
  <form>
    <h5>Subscribe to our newsletter</h5>
    <p>Monthly digest of whats new and exciting from us.</p>
    <div class="d-flex w-100 gap-2">
      <label for="newsletter1" class="visually-hidden">Email address</label>
      <input id="newsletter1" type="text" class="form-control" placeholder="Email address">
      <button class="btn btn-primary" type="button">Subscribe</button>
    </div>
  </form>
</div> -->
        </div>
    <?php }?>

		<div class="d-flex justify-content-between py-4 mt-4 border-top">
			<p><?php echo '© '.date('Y').' «'.($item['name']??'Название сайта').'»' ?></p>
			<ul class="list-unstyled d-flex">
            <?php if($item['tg']??""){?>
            	<li class="ms-3">
            		<a class="link-dark" target="_blank" href="<?php echo $item['tg']?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
													<ellipse ry="238.844305" rx="240.898879" cy="255.730341" cx="256.000016" fill-opacity="null" stroke-opacity="null" stroke-width="1.5" stroke="#000" fill="#fff"/>
                          <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.426 1.426 0 0 0-.013-.315.337.337 0 0 0-.114-.217.526.526 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09z"/>
                        </svg>
            		</a>
            	</li>
            <?php }?>

            <?php if($item['vk']??""){?>
            	<li class="ms-3">
            		<a class="link-dark" target="_blank" href="<?php echo $item['vk']?>">
                        <svg class="footer-social-icon" width="24" height="24" fill="currentColor" viewBox="0 0 512 512">
                        <ellipse ry="238.844305" rx="240.898879" cy="255.730341" cx="256.000016" fill-opacity="null" stroke-opacity="null" stroke-width="1.5" stroke="#000" fill="#fff"/>
                        <path d="M256,0C114.615,0,0,114.615,0,256S114.615,512,256,512,512,397.385,512,256,397.385,0,256,0ZM392.363,342.9H359.878a23.41,23.41,0,0,1-18.318-8.8c-9.742-12.231-28.934-33.918-49.085-43.233a7.666,7.666,0,0,0-10.916,6.928v32.128A12.974,12.974,0,0,1,268.585,342.9H253.564c-19.534,0-61.6-11.891-95.119-60.719-28.56-41.6-41.291-73.84-48.715-99.98a10.3,10.3,0,0,1,9.922-13.093h32.862a15.226,15.226,0,0,1,14.6,10.861c6.111,20.439,21.939,64.53,49.917,86.486a5.788,5.788,0,0,0,9.371-4.54V210.449c0-10.171-4.408-20.347-11.288-28.3a7.878,7.878,0,0,1,5.946-13.046h50.666a9.838,9.838,0,0,1,9.838,9.837v69.325a5.468,5.468,0,0,0,8.636,4.456c9.3-6.62,17.265-16.4,24.591-27.393,9.22-13.828,20.471-36.686,26.115-48.549A13.457,13.457,0,0,1,353.06,169.1H388.9a8.788,8.788,0,0,1,7.873,12.7c-9.044,18.14-26.659,51.418-43.235,70.942a13.877,13.877,0,0,0,1.623,19.54c10.805,9.232,27.673,26.3,45.859,54.729A10.305,10.305,0,0,1,392.363,342.9Z"/></svg>
            		</a>
            	</li>
            <?php }?>
<!--             <?php if($item['email']??""){?>
    <li class="ms-3">
        <a href="mailto:<?php echo $item['email']?>">Связаться с нами</a>
    </li>
<?php }?> -->
			</ul>
		</div>
	</footer>
</div>
</div>



<?php $asset->regJs(Bt::getAlias('@templates/sections/footer/script.js')); ?>
