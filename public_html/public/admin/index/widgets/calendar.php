<?php

query('CREATE TABLE `dt_days` (
  `id` int NOT NULL AUTO_INCREMENT,
  `d` int NOT NULL,
  `m` int NOT NULL,
  `y` int NOT NULL,
  `udate` int NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

$dt_days=array_map(function($it){
    return $it['date'];
}, arrayQuery('SELECT `date` FROM `dt_days`'));
?>


<link rel="stylesheet" href="/admin/index/widgets/calendar/style.css">
<style>

    .datepicker.custom_calendar {
        display: block;
    }

    .datepicker.custom_calendar table th {
        color: #888;
    }

    .datepicker.custom_calendar .datepicker__wrapper {
        background: #333;
        width: 100%;
        padding: 0;
    }

    .datepicker.custom_calendar .datepicker__pane {
        float: left;
        width: 33.333%;
        padding: 0.5rem;
    }

    .datepicker.custom_calendar .datepicker__pane:not(:first-child) .datepicker__prev,
    .datepicker.custom_calendar .datepicker__pane:not(:last-child) .datepicker__next {
        display: none;
    }

    .datepicker.custom_calendar .datepicker__day div:hover,
    .datepicker.custom_calendar .datepicker__day.is-highlighted:not(.is-selected) div {
        background: #444;
    }

    .datepicker.custom_calendar .datepicker__daynum {
        color: white;
    }

    .datepicker.custom_calendar .datepicker__day.is-selected div:hover {
        background: #2196F3;
    }

    .datepicker.custom_calendar .datepicker__day.is-disabled.is-selected div,
    .datepicker.custom_calendar .datepicker__day.is-otherMonth.is-selected div,
    .datepicker.custom_calendar .datepicker__day.is-disabled.is-selected + .is-selected div::before,
    .datepicker.custom_calendar .datepicker__day.is-otherMonth.is-selected + .is-selected div::before {
        background: #444;
    }

    .datepicker.custom_calendar .datepicker__day.is-disabled .datepicker__daynum,
    .datepicker.custom_calendar .datepicker__day.is-otherMonth .datepicker__daynum {
        color: #444;
    }

    .datepicker.custom_calendar .datepicker__day.is-disabled.is-selected .datepicker__daynum,
    .datepicker.custom_calendar .datepicker__day.is-otherMonth.is-selected .datepicker__daynum {
        color: rgba(255,255,255,0.1);
    }

</style>


<div class="card" widgetId="<?=$widgetId?>">
    <div class="card-header border-0 ui-sortable-handle" style="cursor: move;">

        <h3 class="card-title">
            <i class="bi bi-calendar3"></i>
            Выходные
        </h3>
    </div>

    <div class="card-body pt-0">
        <input type="hidden" id="custom_calendar">
    </div>

</div>


<script src="/admin/index/widgets/calendar/script.js"></script>
<script>

    $(document).ready(function (){

        var clndrSt=0;
        var datepicker = new Datepicker('#custom_calendar', {
            multiple: true,
            inline: true,

            classNames: {
                node: 'datepicker custom_calendar'
            },
            i18n: {
                weekdays: ['вс','пн','вт','ср','чт','пт','сб'],
                months: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            },

            templates: {
                container: [
                    '<div class="datepicker__container">',
                    '<% for (var i = -1; i <= 1; i++) { %>',
                    '<div class="datepicker__pane">',
                    '<%= renderHeader(i) %>',
                    '<%= renderCalendar(i) %>',
                    '</div>',
                    '<% } %>',
                    '</div>'
                ].join('')
            },

            onChange: function(d) {
                if(clndrSt===0) return;
                let dt=d.map(function(t){ return t.getTime() / 1000 })
                $.post("/admin/index/widgets/calendar/p.php?q=set", { dt:dt }, function(res) {
                    if(res.success!=='ok') return toast('Ошибка', res.err, 'error');
                },'json');

            }

        });
        datepicker.addDate(<?php echo json_encode($dt_days);?>)

        datepicker.render();
        clndrSt=1;

    });


</script>
