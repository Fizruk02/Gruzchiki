<nav id="sidebar">
   <div class="p-4 pt-5">
      <div id="logo-menu">

         <a href="../admin/account.php" class="img logo rounded-circle mb-5" style="background-image: url(<?=$GLOBALS['AccountImageFromPermission']?>);"></a>
      </div>
      <ul class="list-unstyled components mb-5">
         <!-- <li class="active"> -->
            <?
               $items = arrayQuery('SELECT id, item, link FROM `s_menu` WHERE menu = "admin" AND display = 1 AND owner = 0 ORDER BY `sort`, `item`');
               foreach($items as $item){
                   if($item['link']==''){
                        ?>
                            <li class="nav-item dropdown">
                            <a href="#menuItem<?=$item['id']?>" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><?=$item['item']?></a>
                            <ul class="collapse list-unstyled" id="menuItem<?=$item['id']?>">
                                <?
                                    $subItems = arrayQuery('SELECT item, link FROM `s_menu` WHERE menu = "admin" AND display = 1 AND owner = :owner ORDER BY `sort`, `item`', [ ':owner'=> $item['id'] ]);
                                    foreach($subItems as $subItem){
                                ?>
                                    <li>
                                       <a href="<?=$subItem['link']?>"><?=$subItem['item']?></a>
                                    </li>
                                <?}?>
                            </ul>
                            </li>
                        <?
                   }
                   else
                   {
                       ?>  <li>
                	          <a href="<?=$item['link']?>"><?=$item['item']?></a>
                	       </li>
                	   <?
                   }
               }
               ?>
         

         
      </ul>

   </div>
</nav>


<!--       <div class="footer">
</div> -->