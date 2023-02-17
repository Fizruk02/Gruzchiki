<?php

namespace project\modules\input_datetime;

class input_datetime {
    

    private static $simbols = [
            'block'=> '🚫'
            //'block'=> '🔒'
        ];
    
    
    static function _months($lan='ru'): array{
        
        switch($lan){
            case 'ru':
                return ['ЯНВАРЬ', 'ФЕВРАЛЬ', 'МАРТ', 'АПРЕЛЬ', 'МАЙ', 'ИЮНЬ', 'ИЮЛЬ', 'АВГУСТ', 'СЕНТЯБРЬ', 'ОКТЯБРЬ', 'НОЯБРЬ', 'ДЕКАБРЬ'];
            break;
            default:
                return ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
        }
    }
    
    static function _short_months($lan='ru'): array{
        switch($lan){
            case 'ru':
                return ['янв.', 'фев.', 'март', 'апр.', 'май', 'июнь', 'июль', 'авг.', 'сент.', 'окт.', 'нояб.', 'дек.'];
            break;
                default:
                return ['jan.', ' feb.', 'march', ' apr.', 'may', 'june', 'july', ' aug.', ' sept.', 'oct.', ' nov.', 'dec.'];
        }
        
    }
    
    static function _short_days($lan='ru'): array{
        switch($lan){
            case 'ru':
                return ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
            break;
                default:
                return ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
        }
        
    }
    
    
    
    public function start( array $par=[] )
    {
        if(!$par = echo_message_from_par($par)) return;

        $step = $par['script_step'];
        
        $blocked = [];

        $settings = json_decode($par['input_datetime'], true);

        foreach($settings as &$tSetting){
			$tSetting = text()->variables($tSetting, $par);
		//	$tSetting = text()->shortcodes($tSetting, $par);
		}

        $settings['fraction'] = (int) $settings['fraction'];
        $settings['block'] = $par[getStrBetween($settings['block'], '{', '}')];

        if(!is_array($settings['block']))
            $settings['block'] = json_decode($settings['block'], true);
        
        if(is_array($settings['block'])){
            # переводим массив дат блокировки в timestamp в часах
    		$settings['blockhours'] =	array_map(function($value) {
    			return (int) ((is_int($value) ? $value:strtotime($value))/3600);
    		}, $settings['block']);
    
            # переводим массив дат блокировки в timestamp в минутах
    		$settings['blockminute'] =	array_map(function($value) {
    			return (int) ((is_int($value) ? $value:strtotime($value))/60);
    		}, $settings['block']);
        } else $settings['block'] = [];


        $dataId = setData($settings);

        switch($settings['type']){
            case 'date':
                
                if($settings['calendar']){
                    $dateBtns = $this->get_calendar(date('m'),date('Y'));
                    $kb = ["inline_keyboard" =>  $dateBtns];  
                    send_mess(['body'=>DIALTEXT('inputDatetimeLabelCalendar'), 'kb'=>$kb]); # Календарь
                }
            break;
            
            case 'time':
                $arr = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
				$arr =	array_map(function($value) {
								return ['text'=>$value.' ⁰⁰', 'callback_data'=>$value.':00'];
				}, $arr);

                if($settings['timepicker']){
					$kb = array_chunk($arr, 6);
                    
                    $kb = ["inline_keyboard" =>  $kb];  
                    send_mess(['body'=>DIALTEXT('inputDatetimeLabelTimepicker'), 'kb'=>$kb]); #Timepicker
                }
            break;
            
            case 'datetime':
                $kb = $this->datetimePicker(['date'=> '', '_d'=> $dataId]);
                send_mess(['body'=>DIALTEXT('inputDatetimeLabelTimepicker'), 'kb'=>$kb]); 
            break;
            
            case 'set_interval':
                foreach($settings as &$tSetting)
                    $tSetting = text()->variables($tSetting, $par);

                $resDate= $settings['set_interval_date']? strtotime($settings['set_interval_date']):time();
                try {
                    $resDate = $resDate+intval($settings['set_interval_val'])*intval($settings['set_interval_int']);
                } catch (\Throwable $e) {
                    tgMess($e->getMessage());
                }

                $par[$par['script_step']]=date('Y-m-d H:i:s', $resDate);
		        $par[$par['script_step'].'_format']=date("d.m.Y H:i", $resDate);
                set_pos($par['step'], $par);
                the_distribution_module($par['script_source'], $par);

                return;
            break;

        }

        set_pos($par['step'], $par);

    }
    
    
    public function listener( array $par=[] )
    {
        global $chat_id, $message_id, $text_message, $original;

            
        $text=$text_message;
        $l_text = trim(mb_strtolower($text, 'utf-8'));

        $result = '';
        $settings = json_decode($par['input_datetime'],1);
        
     
        if($text==''){
            switch($settings['type']){
                case 'date':
                    notification(DIALTEXT('inputDatetimeSendTheDate')); # Пришлите дату
                break;
                case 'time':
                    notification(DIALTEXT('inputDatetimeSendTheTime')); #Пришлите время
                break;
            }
            return;
        }
        
        
        
        $skip_commands = preg_split("/\\r\\n?|\\n/", $settings['skip_commands']);
        $skip_commands = array_values($skip_commands);
        if (array_search($text_message, $skip_commands) !== false)
        {
            methods()->delete_this_inline_keyboard();
            $par[$par['script_step']] = '';
            unset($par['input_datetime']);
            set_pos($par['step'], $par);
            the_distribution_module($par['script_source'], $par);
            return;
        }
        
        $now_commands = preg_split("/\\r\\n?|\\n/", $settings['now_commands']);
        $now_commands = array_values($now_commands);
        if (array_search($text_message, $now_commands) !== false)
        {
            $unix = time();
        } else {
            $unix = strtotime($text);
        }
        
         
        
        if(in_array($l_text, ['сегодня', 'today', 'сейчас', 'now'])  )   
            $unix = time();
        
        switch($settings['type']){
            case 'date':

                if(!is_numeric($unix))
                    return notification(DIALTEXT('inputDatetimeSendTheDateInTheFormatDdMmYyyy')); # Пришлите дату в формате дд.мм.гггг
                
                 $result = date("Y-m-d", $unix);
                 $format = date("d.m.Y", $unix);
            
    
                if(!$result)
                    return notification(DIALTEXT('inputDatetimeSendTheDateInTheFormatDdMmYyyy')); # Пришлите дату в формате дд.мм.гггг

            break;
            
            case 'time':
                
                if(!is_numeric($unix))
                    return notification(DIALTEXT('inputDatetimeSendTheTimeInTheFormatHhMm')); # Пришлите время в формате чч:мм
                
                 $result = date("H:i:s", $unix);
                 $format = date("H:i", $unix);
            
                if(!$result)
                    return notification(DIALTEXT('inputDatetimeSendTheTimeInTheFormatHhMm')); # Пришлите время в формате чч:мм
            
            break;
            
            case 'datetime':

                if(!is_numeric($unix))
                    return notification('Пришлите дату и время в формате дд.мм.гггг чч:мм'); # Пришлите дату в формате дд.мм.гггг чч:мм
                
                if($unix<time()-3600)
                    return notification('Выберите дату и время не раньше '.date("d.m.Y H:i"));
                
                 $result = date("Y-m-d H:i:s", $unix);
                 $format = date("d.m.Y H:i", $unix);
                 
                 $par[$par['script_step'].'_date'] = date("Y-m-d", $unix);
                 $par[$par['script_step'].'_time'] = date("H:i:s", $unix);
                 $par[$par['script_step'].'_date_format'] = date("d.m.Y", $unix);
                 $par[$par['script_step'].'_time_format'] = date("H:i", $unix);
								 $par[$par['script_step'].'_stamp']=time();
            

                if(!$result)
                    return notification('Пришлите дату м время в формате дд.мм.гггг чч:мм'); # Пришлите дату в формате дд.мм.гггг чч:мм
            
            break;
            
            default:
                return notification(DIALTEXT('inputDatetimeError301')); # Ошибка! Код 301
        }
        


                
        $par[$par['script_step']]=$result;
				$par[$par['script_step'].'_format']=$format;
				$par[$par['script_step'].'_stamp']=strtotime($result.' 00:00');
		
        if(!intermediate_function($par)) return;

        if(isset($original['callback_query'])){
            methods()->edit_message($format, false, $chat_id, $message_id);
            methods()->answerCallbackQuery('', $obj['callback_query']['id']);
        }
            
        
        
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);

        return true;
    }
    
    //'Y-m-d H:i:s'
    private function datetimePicker($par){
        $dataId = $par['_d'];
        $data = getData( $dataId ); # настройки
        $block = $data['blockhours'];
        
        $fraction = $data['fraction'];
        
        $date = $par['date'] ? strtotime( $par['date'] ) : time();

        $arr = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];

        /**
         * dt_times
         * если hour 24, значит занят весь день
         * 
         */

        
        /**
         * Если в модуле стоит чекбокс "Учитывать заблокированные даты", берем даты из таблицы dt_times
         */
	    // $block_dt_times=[];
	    // if(($data['block_dt_days']??0))
	    //     $block_dt_times = arrayQuery('SELECT * FROM `dt_times` WHERE date(date)="'.date('Y-m-d', $date).'"');
        
        /**
         * Данные из таблицы dt_periods
         */
        
        $intervals = $this->getDayPeriods($date);

        $fractionsArr = [];
        if($fraction) {
            for($i=0;$i<=(int)(59/$fraction);$i++)
            $fractionsArr[] = str_pad($i*$fraction, 2, '0', STR_PAD_LEFT);
        }

		$arr =	array_map(function($value) use($date, $dataId, $block, $fraction, $fractionsArr, $block_dt_days, $intervals) {
		    
		    $current = date('Y-m-d', $date).' '.$value.':00:00';
		    $strtotime = strtotime($current);
		    
		    
		    /* ищем интервалы */
		    $filterIntervals = array_filter($intervals, function($it) use($strtotime){
		        return $strtotime>=$it['from'] && $strtotime<=$it['to'];
		    });
		    
		    /* если этот час есть в списке интервалов */
		    if(count($filterIntervals)) {
		        $st=false;
		        if($fraction){
		            /* если есть фракции, то ищем, если ли свободное время внутри ячейки.
		               сначала $st = 1, если $searchFreeFraction хоть раз будетне пустой, значит есть свободная фракция,
		               ставим $st = 0 и прекращаем цикл*/
		            $st=1;
		            foreach($fractionsArr as $fr){
		                $ct=$strtotime+$fr*60;
		                /* если есть незанятая фракция, то не блокируем */
		                $searchFreeFraction=array_filter($filterIntervals,function($it) use($ct){
		                    return ($ct>=$it['from'] && $ct<$it['to']);
		                });
		                if(!count($searchFreeFraction)){
		                    $st=0;
		                    break;
		                }
		            }
		        } else {
		            /* если франкий нет, то блокируем ячейку */
		            $st=1;
		        }
		        
		        if($st) return [ 'text'=> self::$simbols['block'], 'callback_data'=>  json_encode(['mtd'=> 'dt01',  'dt'=> false]) ];
		        
		    }
		    
		    $b = $strtotime>time()-3600 ? true : false;

		    if(is_array($block) && array_search((int) $strtotime/3600, $block)!==false)
		        return [ 'text'=> self::$simbols['block'], 'callback_data'=>  json_encode(['mtd'=> 'dt01',  'dt'=> false]) ];
		    

		    if($fraction) # если выбраны фракции, то при клике открываем фракции, если нет, то передаем значение
		    return [ 'text'=> $b?$value.' ⁰⁰':' ', 'callback_data'=>  json_encode(['mtd'=> 'dt01',  'dt'=> $b?$current:false, '_d'=> $dataId]) ];
		    else
		    return [ 'text'=> $b?$value.' ⁰⁰':' ', 'callback_data'=>  $b? $current:json_encode(['mtd'=> 'dt01',  'dt'=> false]) ];
		    
			
		}, $arr);


        $formatDate = date("d.m.Y", $date);
        
        $previous = ['text'=>'«', 'callback_data'=> json_encode(['mtd'=> 'dt03', 'np'=>'-', 'd'=> date('Y-m-d', $date-86400), '_d'=> $dataId ])];
        
        if($date <= time())
            $previous = ['text'=>' ', 'callback_data'=> json_encode(['mtd'=> 'dt03', '_d'=> $dataId])];
            
        $days = [
                    [
                        $previous,
                        ['text'=>$formatDate, 'callback_data'=> json_encode(['mtd'=> 'dt03', '_d'=> $dataId])],
                        ['text'=>'»', 'callback_data'=> json_encode(['mtd'=> 'dt03', 'np'=>'+', 'd'=> date('Y-m-d', $date+86400), '_d'=> $dataId])],
                    ]
                ];
        
		$timepicker = array_chunk($arr, 6);
        
        $kb = array_merge($days, $timepicker);

        $kb = ["inline_keyboard" =>  $kb];
        return $kb;
        
    }
    

    public function dt03( array $par=[] ){ # inptdttimeSetdate
        global $chat_id, $message_id;
        $dataId = $par['_d'];
        $data = getData( $dataId ); # настройки
        
        if(!$date = $par['d']) return;
        if( $par['np']==='-' &&  $date < date('Y-m-d')) return;
        
        $kb = $this->datetimePicker([ 'date'=> $date, '_d'=> $dataId ]);
        methods()->edit_inline_keyboard($chat_id, $message_id, $kb);
    }
    

/**
     * Выбор дроби часа
     */
    public function dt01( array $par=[] ){ # inptdttimeSetdatetime
        global $chat_id, $message_id;

        $dataId = $par['_d'];
        $data = getData( $dataId ); # настройки

        if(!$datetime = $par['dt']) return;
        
        $dt=strtotime($datetime);
        if($dt<time()-3600)
            return notification('Нельзя выбрать прошлое...');
            
            

        $kb = $this->fractionKeyboard([ 'datetime'=>$dt, '_d'=> $dataId ]);
        methods()->edit_inline_keyboard($chat_id, $message_id, $kb);
    }
    

    public function dt02( array $par=[] ){ # inptdttimeSetFrctn set fraction
        global $chat_id, $message_id;
        
        $dataId = $par['d'];
        $data = getData( $dataId ); # настройки
        
        if(!$datetime = $par['fr']) return;
        $dt=strtotime($datetime);
        if($par['np']==='-' && $dt<time()-3600)
            return notification('Нельзя выбрать прошлое...');
        $kb = $this->fractionKeyboard([ 'datetime'=>$dt, '_d'=> $dataId ]);
        methods()->edit_inline_keyboard($chat_id, $message_id, $kb);
    }
    
    private function fractionKeyboard( array $par=[] ){
        $dt=$par['datetime'];
        
        $dataId = $par['_d'];
        $data = getData( $dataId ); # настройки
        
        $previous = ['text'=>'« '.date('H', $dt-3600), 'callback_data'=> json_encode(['mtd'=> 'dt02', 'np'=>'-', 'fr'=> date('Y-m-d H:00:00', $dt-3600), 'd'=> $dataId ])];
        if($dt <= time())
            $previous = ['text'=>' ', 'callback_data'=> json_encode(['mtd'=> 'dt02', 'd'=> $dataId])];
        $hours = [
                    [
                         $previous
                        ,['text'=>' 🔝', 'callback_data'=> json_encode(['mtd'=> 'dt03', 'd'=> date('Y-m-d', $dt), '_d'=> $dataId ])]
                        ,['text'=>date('H', $dt+3600).' »', 'callback_data'=> json_encode(['mtd'=> 'dt02', 'np'=>'+', 'fr'=> date('Y-m-d H:00:00', $dt+3600), 'd'=> $dataId ])]
                    ]
                ];
            
        
        $arr = ['00', '10', '20', '30', '40', '50'];

        if($fraction = $data['fraction']){
            $arr = [];
            for($i=0;$i<=(int)(59/$fraction);$i++)
            $arr[] = str_pad($i*$fraction, 2, '0', STR_PAD_LEFT);
        }
        
        $intervals = $this->getDayPeriods( $dt );

		$arr =	array_map(function($value) use($dt, $intervals) {
		    $tempdt=$dt+$value*60;
		    $filterIntervals = array_filter($intervals, function($it) use($tempdt){
		        return $tempdt>=$it['from'] && $tempdt<$it['to'];
		    });
		   
		   if(count($filterIntervals))
		       return [ 'text'=> self::$simbols['block'], 'callback_data'=>  json_encode(['mtd'=> 'dt01',  'dt'=> false]) ];
		       
            $current = date('Y-m-d H', $dt).':'.$value.':00';
            $b = strtotime($current)>time()-10 ? true : false;$b=true;
            return ['text'=> $b?date('H:', $dt).$value:' ', 'callback_data'=>  $current ];
		}, $arr);
		
		$chunk=3;
		for($i=5;$i>0;$i--)
	    if(count($arr)%$i==0){
	        $chunk = $i;
	        break;
		}
		
		$kb = array_chunk($arr, $chunk);
        
        $kb = array_merge($hours, $kb);
        
        $kb = ["inline_keyboard" =>  $kb];
        return $kb;
    }
    
  
    public function getCalendar($par){
        $obj = json_decode(input_data);
        if($cb = $obj-> callback_query)
            $text = $cb-> data;
        $chat_id = $obj-> callback_query-> message-> chat->id;
        $message_id = $obj-> callback_query-> message-> message_id;
        $data = json_decode($cb-> data);
        
        $m = $data-> m;
        $y = $data-> y;
        
        $dateBtns = $this->get_calendar($m,$y);
        $kb = ["inline_keyboard" =>  $dateBtns]; 
        methods()->edit_inline_keyboard($chat_id, $message_id, $kb);
        return;
    }

    
    
    
    public function getCalendarMonthsList($par){ 
        $obj = json_decode(input_data);
        if($cb = $obj-> callback_query)
            $text = $cb-> data;
        $chat_id = $obj-> callback_query-> message-> chat->id;
        $message_id = $obj-> callback_query-> message-> message_id;
        $data = json_decode($cb-> data);
        $y = $data-> y;
        $dateBtns = $this->get_months_list($y);
        $kb = ["inline_keyboard" =>  $dateBtns]; 
        methods()->edit_inline_keyboard($chat_id, $message_id, $kb);
        return;
    }
    
    
    
    public function getCalendarYearsList($par){ 
        $obj = json_decode(input_data);
        if($cb = $obj-> callback_query)
            $text = $cb-> data;
        $chat_id = $obj-> callback_query-> message-> chat->id;
        $message_id = $obj-> callback_query-> message-> message_id;
        $data = json_decode($cb-> data);
        $y = $data-> y;
        $dateBtns = $this->get_years_list($y);
        $kb = ["inline_keyboard" =>  $dateBtns]; 
        methods()->edit_inline_keyboard($chat_id, $message_id, $kb);
        return;
    }
    
    
private function get_calendar(int $month, int $year): array { 
    //$prevMonthCallback = 'calendar-month-';
    if ($month === 1) {
        //$prevMonthCallback .= '12-'.($year-1);
        $prevMonth = 12;
        $prevYear = $year-1;
    } else {
        //$prevMonthCallback .= ($month-1).'-'.$year;
        $prevMonth = $month-1;
        $prevYear = $year;
    }
    
    //$nextMonthCallback = 'calendar-month-';
    if ($month === 12) {
        //$nextMonthCallback .= '1-'.($year+1);
        $nextMonth = 1;
        $nextYear = $year+1;
    } else {
        $nextMonth = $month+1;
        $nextYear = $year;
        //$nextMonthCallback .= ($month+1).'-'.$year;
    }

    $start = new \DateTime(sprintf('%d-%d-01', $year, $month));

    $calendarMap = [
        [
            ['text' => '<', 'callback_data' => json_encode(['mtd'=>'getCalendar', 'm'=>$prevMonth, 'y'=>$prevYear])],
            ['text' => $this->_short_months('en')[$month-1].' '.$year, 'callback_data' => json_encode(['mtd'=>'getCalendarMonthsList', 'y'=>$year])],
            ['text' => '>', 'callback_data' => json_encode(['mtd'=>'getCalendar', 'm'=>$nextMonth, 'y'=>$nextYear])],
        ],
        [
            ['text' => $this->_short_days('en')[1], 'callback_data' => 'blockanswer'],
            ['text' => $this->_short_days('en')[2], 'callback_data' => 'blockanswer'],
            ['text' => $this->_short_days('en')[3], 'callback_data' => 'blockanswer'],
            ['text' => $this->_short_days('en')[4], 'callback_data' => 'blockanswer'],
            ['text' => $this->_short_days('en')[5], 'callback_data' => 'blockanswer'],
            ['text' => $this->_short_days('en')[6], 'callback_data' => 'blockanswer'],
            ['text' => $this->_short_days('en')[0], 'callback_data' => 'blockanswer'],
        ],
    ];


    $end = clone $start;
    $end->modify('last day of this month');
    $iterEnd = clone $start;
    $iterEnd->modify('first day of next month');
    $row = 2;
    foreach (new \DatePeriod($start, new \DateInterval("P1D"), $iterEnd) as $date) {
        /** @var \DateTime $date */

        if (!isset($calendarMap[$row])) {
            $calendarMap[$row] = array_combine([1, 2, 3, 4, 5, 6, 7], [[], [], [], [], [], [], []]);
        }

        $dayIterator = (int)$date->format('N');
        if ($dayIterator != 1 && $start->format('d') === $date->format('d')) {
            for ($i = 1; $i < $dayIterator; $i++){
                $calendarMap[$row][$i] = ['text' => ' ', 'callback_data' => 'blockanswer'];
            }
        }

        $day = $date->format('d');
        if($date->format("Y-m-d")==date("Y-m-d")) $day = '✅';
        
        
        $calendarMap[$row][$dayIterator] = ['text' => $day, 'callback_data' => sprintf('%d-%d-%d', $date->format('d'), $month, $year)];

        if ($dayIterator < 7 && $end->format('d') === $date->format('d')) {
            for ($i = $dayIterator+1; $i <= 7; $i++){
                $calendarMap[$row][$i] = ['text' => ' ', 'callback_data' => 'blockanswer'];
            }
            $calendarMap[$row] = array_values($calendarMap[$row]);
            break;
        }

        if ($dayIterator === 7) {
            $calendarMap[$row] = array_values($calendarMap[$row]);
            $row++;
        }
    }

    return $calendarMap;
} 


private function get_months_list(int $year): array {

    $listMap = [
        [
            ['text' => '<', 'callback_data' => json_encode(['mtd'=>'getCalendarMonthsList', 'y'=>$year-1])],
            ['text' => $year, 'callback_data' => json_encode(['mtd'=>'getCalendarYearsList', 'y'=>$year])],
            ['text' => '>', 'callback_data' => json_encode(['mtd'=>'getCalendarMonthsList', 'y'=>$year+1])],
        ],
    ];

    $row = 1;

    for($month = 1; $month <= 12; $month++) {
        $listMap[$row][] = ['text' => $this->_months('en')[$month-1], 'callback_data' => json_encode(['mtd'=>'getCalendar', 'm'=>$month, 'y'=>$year])];

        if ($month === 3 || $month === 6 || $month === 9) {
            $row++;
        }
    }

    return $listMap;
}

private function get_years_list(int $centerYear): array {
    $prevYear = $centerYear-25;
    $nextYear = $centerYear+25;
    $listMap = [
        [
            $prevYear <= 76 ? ['text' => ' ', 'callback_data' => 'null_callback'] : ['text' => '<', 'callback_data' => json_encode(['mtd'=>'getCalendarYearsList', 'y'=>$prevYear])],
//            ['text' => ' ', 'callback_data' => 'null_callback'],
            $nextYear >= 10024 ? ['text' => ' ', 'callback_data' => 'null_callback'] : ['text' => '>', 'callback_data' => json_encode(['mtd'=>'getCalendarYearsList', 'y'=>$nextYear])],
        ],
    ];

    $row = 1;
    $i = 0;

    for ($year = ($centerYear - 12); $year <= ($centerYear+12); $year++) {
        if ($year >= 100 && $year <= 9999) {
            $listMap[$row][] = ['text' => $year, 'callback_data' => json_encode(['mtd'=>'getCalendarMonthsList', 'y'=>$year])];
            $i++;
        } else {
//            $listMap[$row][] = ['text' => ' ', 'callback_data' => sprintf('calendar-months_list-%d', $year)];
        }

        if ($i === 5 || $i === 10 || $i === 15 || $i === 20) {
            $row++;
        }
    }


    return $listMap;
}






/**
 ************** МОДЕЛИ **************
 */

private function getDayPeriods( $datetime ){
    $dt=date('Y-m-d', $datetime);
    $intervals = arrayQuery('SELECT * FROM `dt_periods` WHERE "'.$dt.'">=date(date_from) AND "'.$dt.'"<=date(date_to)');
    

    return array_map(function($it){
        $it['from']=strtotime($it['date_from']);
        $it['to']=strtotime($it['date_to']);
        return $it;
    }, $intervals);
}













}









