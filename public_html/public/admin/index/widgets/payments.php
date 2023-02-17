<?php
$sqlNoBillsPaid=[];
$sqlBillsPaid=[];
$sqlBillsExpired=[];
$sumPayment=0;
foreach(arrayQuery('SELECT SUM(IF(`status`=0 AND date_end>NOW(),1,0)) not_paid,
                    SUM(IF(`status`=0 AND date_end<=NOW(),1,0)) expired,
                    SUM(IF(`status`=2,1,0)) paid,SUM(IF(`status`=2,`sum`,0)) `sum`, DATE(t_date) d
                    FROM `payments` WHERE test=0
                    GROUP BY d HAVING d>=:d1 AND d<=:d2', [ ':d1'=>$date_1, ':d2'=>$date_2 ]) as $r){
                        $sqlBillsPaid[$r['d']]=$r['paid'];
                        $sqlNoBillsPaid[$r['d']]=$r['not_paid'];
                        $sqlBillsExpired[$r['d']]=$r['expired'];
                        $sumPayment+=$r['sum'];
                    }
$sumBillsPaid=array_sum($sqlBillsPaid);
$sumNoBillsPaid=array_sum($sqlNoBillsPaid);
$sumBillsExpired=array_sum($sqlBillsExpired);

?>
              
<div class="card" widgetId="<?=$widgetId?>">
  <div class="card-header border-0">
    <div class="d-flex justify-content-between">
      <span class="">Оплаты за период </span> <span class="text-bold text-lg"><?=$sumPayment?>Р.</span>
      <!-- <a href="javascript:void(0);">View Report</a> -->
    </div>
  </div>
  <div class="card-body pt-1">

    <div class="position-relative mb-4"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
       <canvas id="billsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
    </div>


  </div>
</div>

<script>
$(function () {
    var donutChartCanvas = $('#billsChart').get(0).getContext('2d')
    var donutData        = {
      labels: [
          'Оплаченые счета',
          'Не оплаченные счета',
          'Истекшие счета',
      ],

      datasets: [
        {
          data: [<?=$sumBillsPaid?>,<?=$sumNoBillsPaid?>,<?=$sumBillsExpired?>],
          backgroundColor : [ '#00a65a', '#00c0ef','#d2d6de'], //'#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'
          //, '',
        }
      ]
    }
    var donutOptions     = {
      maintainAspectRatio : false,
      responsive : true,
        legend: { position: 'right'}
    }
    //Create pie or douhnut chart
    // You can switch between pie and douhnut using the method below.
    new Chart(donutChartCanvas, {
      type: 'doughnut',
      data: donutData,
      options: donutOptions
    })
}) 
</script>