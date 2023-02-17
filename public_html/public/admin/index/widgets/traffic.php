<?php
$days = getDates($date_1, $date_2);

$m=['янв.', 'фев.', 'марта', 'апр.', 'мая', 'июня', 'июля', 'авг.', 'сент.', 'окт.', 'нояб.', 'дек.'];
$sqlReg=[];
foreach(arrayQuery('SELECT count(*) c, DATE(t_date) d  FROM usersAll
                    GROUP BY d HAVING d>=:d1 AND d<=:d2', [ ':d1'=>$date_1, ':d2'=>$date_2 ]) as $r) $sqlReg[$r['d']]=$r['c'];
$sqlAct=[];
foreach(arrayQuery('SELECT count(DISTINCT chat_id) c, DATE(date) d FROM `log_connect`
                    GROUP BY d HAVING d>=:d1 AND d<=:d2', [ ':d1'=>$date_1, ':d2'=>$date_2 ]) as $r) $sqlAct[$r['d']]=$r['c'];

$cols=[];
$dataReg=[];
$dataAct=[];
foreach($days as $day){
    $td=explode('-',$day);
    $cols[]=$td[2].(!count($cols)||$td[2]==='01'?' '.$m[(int)$td[1]-1]:'');
    $dataReg[]=$sqlReg[$day]??0;
    $dataAct[]=$sqlAct[$day]??0;
}


if(count($dataReg)&&count($dataAct)){
    $stReg=round(moving_average($date_2==$nowDate?array_slice($dataReg,0,count($dataReg)-1):$dataReg)*100,1);
    $stAct=round(moving_average($date_2==$nowDate?array_slice($dataAct,0,count($dataAct)-1):$dataAct)*100,1);
}

$stNewUsers=array_sum($dataReg);
$max=max(max($dataReg),max($dataAct));
?>
              
<div class="card" widgetId="<?=$widgetId?>">
  <div class="card-header border-0">
    <div class="d-flex justify-content-between">
      <h3 class="card-title">Трафик</h3>
      <!-- <a href="javascript:void(0);">View Report</a> -->
    </div>
  </div>
  <div class="card-body pt-1">
    <div class="d-flex">
      <p class="d-flex flex-column">
        <span class="text-bold text-lg"><?=$stNewUsers?></span>
        <span><?=text()->num_word($stNewUsers, ['Новый пользователь', 'Новых пользователя', 'Новых пользователей'], false)?> за период</span>
      </p>
      <p class="ml-auto d-flex flex-column text-right">
        
        <span class="text-<?=$stReg<0?'danger':'success'?>"><i class="bi bi-square-fill text-primary"></i><i class="bi bi-arrow-<?=$stReg<0?'down':'up'?>-short"></i> <?=$stReg?>% </span>
        <span class="text-<?=$stAct<0?'danger':'success'?>"><i class="bi bi-square-fill text-gray"></i><i class="bi bi-arrow-<?=$stAct<0?'down':'up'?>-short"></i> <?=$stAct?>% </span>
      </p>
    </div>
    <div class="position-relative mb-4"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
      <canvas id="visitors-chart" height="250" width="715" style="display: block; height: 200px; width: 572px;" class="chartjs-render-monitor"></canvas>
    </div>

    <div class="d-flex flex-row justify-content-end">
      <span class="mr-2">
        <i class="bi bi-square-fill text-primary"></i> Новые пользователи
      </span>

      <span>
        <i class="bi bi-square-fill text-gray"></i> Посещаемость
      </span>
    </div>
  </div>
</div>

<script>
$(function () {
  'use strict'

  var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
  }

  var mode = 'index'
  var intersect = true
  
  
  
  var $visitorsChart = $('#visitors-chart')
  // eslint-disable-next-line no-unused-vars
  var visitorsChart = new Chart($visitorsChart, {
    data: {
      labels: <?=json_encode($cols)?>,
      datasets: [{
        type: 'line',
        data: <?=json_encode($dataReg)?>,
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        pointBorderColor: '#007bff',
        pointBackgroundColor: '#007bff',
        fill: false
      },
      {
        type: 'line',
        data: <?=json_encode($dataAct)?>,
        backgroundColor: 'tansparent',
        borderColor: '#ced4da',
        pointBorderColor: '#ced4da',
        pointBackgroundColor: '#ced4da',
        fill: false
      }]
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        mode: mode,
        intersect: intersect
      },
      hover: {
        mode: mode,
        intersect: intersect
      },
      legend: {
        display: false
      },
      scales: {
        yAxes: [{
          // display: false,
          gridLines: {
            display: true,
            lineWidth: '4px',
            color: 'rgba(0, 0, 0, .2)',
            zeroLineColor: 'transparent'
          },
          ticks: $.extend({
            beginAtZero: true,
            suggestedMax: <?=$max?:0?>,
            stepSize: 1
          }, ticksStyle)
        }],
        xAxes: [{
          display: true,
          gridLines: {
            display: false
          },
          ticks: ticksStyle
        }]
      }
    }
  }) 
}) 
</script>