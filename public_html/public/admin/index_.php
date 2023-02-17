<!doctype html>
<html lang="en">
<head>
<title>Главная</title>
<?include_once("resources/_phpparsite.php");
res("_ass.php");
$nowDate=date("Y-m-d");
$date_1=$_GET['d1']??date("Y").'-01-01';
$date_2=$_GET['d2']??$nowDate;
$minDate=singleQuery('SELECT DATE(t_date) d FROM `usersAll` ORDER BY id LIMIT 1')['d'];

if(strtotime($date_1)<strtotime($minDate))$date_1=$minDate;
if(strtotime($date_2)>strtotime($nowDate))$date_2=$nowDate;


query("CREATE TABLE `s_dashboard` (
  `id` int NOT NULL AUTO_INCREMENT,
  `widget` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `num` int NOT NULL DEFAULT '0',
  `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `expand` tinyint(1) NOT NULL DEFAULT '1',
  `display` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `widget` (`widget`),
  KEY `col` (`parent`),
  KEY `display` (`display`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

setWdgt('traffic','include',0,['dir'=>'admin/index/widgets/traffic.php'],'main');
setWdgt('last-users','include',0,['dir'=>'admin/index/widgets/last-users.php'],'right');
//setWdgt('calendar','include',1,['dir'=>'admin/index/widgets/calendar.php'],'right');
function setWdgt($w,$t,$n,$s,$p){
if(!singleQuery('SELECT * FROM `s_dashboard` WHERE widget=?',[$w]))
    query('INSERT INTO `s_dashboard` (`widget`, `type`, `num`, `settings`, `parent`) VALUES (?,?,?,?,?)', [$w,$t,$n,is_array($s)?json_encode($s):$s,$p]);
}

?> 
  <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  

  
</head>
<body style='overflow-x:hidden;'>
<? res("_nav.php")?>
<div class="content pt-1">

    <div class="container-fluid">
        <div class="row">
            <section class="col-lg-8 connectedSortable" wdgts-cntnr="main"> <? widgets('main');?> </section>
            <section class="col-lg-4 connectedSortable" wdgts-cntnr="right"> 
            <div class="mb-2 d-flex">
                <input type="text" class="form-control" style="width: 165px;" id="main_range" value="">
    
                <div class="input-group ms-2">
                    <input type="text" class="form-control" placeholder="search..." aria-label="" aria-describedby="search" id="input-search">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary px-3" type="button" id="search"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>
            <? widgets('right');?> 
            </section>
        </div>
    </div>
</div>
</body>
<? res("js.php"); ?>
<!-- <script src="data/js.js?v=0.1"></script> -->
<!-- <script src="https://adminlte.io/themes/v3/dist/js/adminlte.js"></script> -->

<script src="https://adminlte.io/themes/v3/plugins/chart.js/Chart.min.js"></script>
<script>
    
  
  
<?php

function widgets($col){
    global $date_1, $date_2, $nowDate;
    foreach(arrayQuery('SELECT * FROM `s_dashboard` WHERE parent=? AND display=1 ORDER BY num',[$col]) as $w){
        $d=json_decode($w['settings'],1);
        $widgetId=$w['id'];
        if($w['type']==='include') {
            include $_SERVER['DOCUMENT_ROOT'].'/'.$d['dir'];
            
        }
    }
}

function getDates($startTime, $endTime) {
    $day = 86400;
    $format = 'Y-m-d';
    $startTime = strtotime($startTime);
    $endTime = strtotime($endTime);
    $numDays = round(($endTime - $startTime) / $day)+1; // без +1

    $days = array();

    for ($i = 0; $i < $numDays; $i++) { 
        $days[] = date($format, ($startTime + ($i * $day)));
    }

    return $days;
}

function moving_average($array) {
    if(!is_array($array)||count($array)<2)return 0;
    for ($i = 1; $i < sizeof($array); $i++) { $result[] = $array[$i] - $array[$i-1]; } return array_sum($result)/count($result);
    
} 
?>


      
$(function () {
    
    $('#main_range').daterangepicker(
    {
      ranges   : {
        'Сегодня'       : [moment(), moment()],
        'Вчера'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Последние 7 дней' : [moment().subtract(6, 'days'), moment()],
        'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
        'Этот месяц'  : [moment().startOf('month'), moment().endOf('month')],
        'Предыдущий месяц'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      locale : {
        format: 'DD.MM.YYYY'
      },
      startDate: moment(<?='"'.$date_1.'"';?>),
      endDate  : moment(<?='"'.$date_2.'"';?>)
    },
    function (start, end) {
        window.location =location.origin+location.pathname+'?d1='+moment(start).format('YYYY-MM-DD')+'&d2='+moment(end).format('YYYY-MM-DD');
        //par.range.func(start, end);
    }); 
    

$('.connectedSortable').sortable({
    placeholder: 'sort-highlight',
    connectWith: '.connectedSortable',
    handle: '.card-header, .nav-tabs',
    forcePlaceholderSize: true,
    zIndex: 999999,
    beforeStop: function() {
    let d=[];
    $("[wdgts-cntnr]").each(function(i,el){
        let p=$(el).attr('wdgts-cntnr');
        $(el).children().each(function(q,ch){
        if(id=$(ch).attr("widgetId"))
        d.push({prnt:p,id:id,num:q});
        });  
    });
    b2.spinner();
    $.post("/admin/index/methods.php?q=wsort", {items:d}, function(res) {
        if(res.success!=='ok') return toast('Error', res.err, 'e');
    }, "json");

    },
  })
  $('.connectedSortable .card-header').css('cursor', 'move')




//$('.connectedSortable').sortable({
//    placeholder: 'sort-highlight',
//    connectWith: '.connectedSortable',
//    forcePlaceholderSize: true,
//    zIndex: 999999,
//    revert: 100,
//    activate: function() {
//        $('#activateContainer').css({
//            border: "medium double #007bff",
//            backgroundColor: "#007bff"
//        });
//    },
//    deactivate: function() {
//        $('#activateContainer').css("border", "").css("background-color", "");
//    },
//    over: function() {
//        $('#activateContainer').css({
//            border: "medium double lightgray",
//            backgroundColor: "lightgray"
//        });
//    },
//    out: function() {
//        $('#droppable').css("border", "").css("background-color", "");
//    },
//    beforeStop: function() {
//        let items = getitems(active_script);
//        b2.spinner();
//        $.post("post.php?q=blockchain/sorted/", {items:JSON.stringify(items), id:active_script}, function(res) {
//            if(res.success!=='ok') return toast('Error', res.err, 'e');
//        }, "json");
//    },
//
//})


  //$('.todo-list').sortable({
  //  placeholder: 'sort-highlight',
  //  handle: '.handle',
  //  forcePlaceholderSize: true,
  //  zIndex: 999999
  //})

    
    
    
    
    
}) 



</script>

</html>