<?php
$SCRIPT_FILENAME = $_SERVER['SCRIPT_FILENAME'];
$r=arrayQuery('SELECT `id`, `item` name, `link` href, `link` pos, `owner` FROM `s_menu` WHERE menu = "admin" AND display = 1 ORDER BY `sort`, `item`');
//var_dump($r);die('ddd');
function recr($arr,$ch,$id,$pr){
    foreach($arr as $k=>$a)
        if($ch==$a[$pr]) $l[]=count($cn=recr($arr,$a[$id],$id,$pr))?array_merge($a,['sub'=>$cn]):$a;
    return $l?:[];
}

?>
<style>
    body {display:none;}
    .dropdown-menu .dropdown-menu {
        top: auto;
        left: 100%;
        transform: translateY(-2rem);
    }
    .dropdown-item + .dropdown-menu {
        display: none;
    }
    .dropdown-item.submenu::after {
        content: '▸';
        margin-left: 0.5rem;
    }
    .dropdown-item:hover + .dropdown-menu,
    .dropdown-menu:hover {
        display: block;
    }
</style>
<div class="mb-1">
    <nav class="navbar navbar-dark bg-dark navbar-expand-lg" id="navbar">
        <div class="container-xxl">
            <a class="navbar-brand p-0 me-2 ml-2" href="/admin/<?=$GLOBALS['permission_to_use']['user_status']=='99'?'_dev/':''?>">
                <div class="logo" style="padding-left:3px;">
                    <div class="icon-area" style="margin-right:3px;"></div><?=setting('company')?:'Bot'?>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bdNavbar" aria-controls="bdNavbar" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="bdNavbar">
                <ul class="navbar-nav me-auto bd-navbar-nav mb-2 mb-lg-0 navbar-nav-scroll w-100" id="nav-ul" style="max-width: 68vw;"> <!--  -->
                    <?php
                    function submenutempl($it){
                        if($it['sub']){
                            return '<li class="dropdown-item submenu"  style="cursor: pointer" href="#">'.$it['name'].'</li>'.
                                '<ul class="dropdown-menu">'.implode("",array_map(function ($t){return submenutempl($t);},$it['sub'])).'</ul>';
                        } else
                            return '<li><a class="dropdown-item" href="'.$it['href'].'">'.$it['name'].'</a></li>';
                    }
//var_dump($r);die('OK');
                    foreach(recr($r,0,'id','owner') as $key=> $navLink)
                        if(isset($navLink['sub'])){
                            $subm = '';
                            $st = '';
                            foreach($navLink['sub'] as $submenu){
                                $subm.= submenutempl($submenu);
                                $st = strpos($SCRIPT_FILENAME, $submenu['pos'])===false?:'active';
                            }

                            ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?=$st?>" href="#" id="menuitem-<?=$key?>" style="white-space: nowrap;" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?=$navLink['name']?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="menuitem-<?=$key?>">
                                    <?=$subm ?>
                                </ul>
                            </li>
                        <?} else {?>
                            <li class="nav-item">
                                <?='<a class="nav-link '.(strpos($SCRIPT_FILENAME, $navLink['pos'])!==false ? 'active' : '').'" style="white-space: nowrap;" href="'.$navLink['href'].'" title="'.$navLink['name'].'">'.$navLink['name'].'</a>'?>
                            </li>
                        <?}?>
                </ul>
                <div class="d-flex">
                    <button class="btn border-0" style="display:none" type="button" data-bs-toggle="collapse" data-bs-target="#additionalContainer" aria-expanded="false" aria-controls="additionalContainer"><span class="navbar-toggler-icon"></span></button>
                    <button class="btn btn-outline-light border-0" type="submit" style="white-space: nowrap;" onclick="btdlcc();document.location.href = '/admin/login.php';"><i class="bi bi-box-arrow-left"></i> <?=mb_strtoupper($GLOBALS['permission_to_use']['user'])?></button>

                </div>
            </div>

        </div>
    </nav>
    <div class=" navbar-dark bg-dark " id="nav-additional-container" style="display:none">
        <div class="collapse navbar-collapse container" id="additionalContainer">
            <ul class="navbar-nav me-auto bd-navbar-nav mb-2 mb-lg-0 navbar-nav-scroll w-100" id="nav-ul" style="max-width: 68vw;">

            </ul>
        </div>
    </div>
</div>
<script>
    function btdlcc(){
        $.ajax({url:"/logout"});
        var cookies = document.cookie.split(/;/);
        for (var i = 0, len = cookies.length; i < len; i++) {
            var cookie = cookies[i].split(/=/);
            document.cookie = cookie[0] + "=;max-age=-1";
        }
    }
    $( document ).ready(function() {
        $('body').show();
        let l=$('#nav-ul li:last'),
            p=$('#nav-ul');
        let r = p.width()+p.position().left-(l.position().left+l.width());
        let st=false;
        $('#nav-ul').children().each(function(i,elem) {
            l=$(elem);
            if(p.width()+p.position().left-(l.position().left+l.width())<0){
                $(elem).prependTo($('#additionalContainer>ul'));
                st=true;
            };
            if(st){
                $('#nav-additional-container').show();
                $('[aria-controls="additionalContainer"]').show();
            }
        });

        //if(r<0) $('#navbar').removeClass('navbar-expand-lg');
    })
</script>