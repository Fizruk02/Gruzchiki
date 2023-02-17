<?php

namespace project\modules\input_address;

class input_address {

    private static $cicle = 0;

    public function start( array $par=[] )
    {
		global $obj, $message_id;

        if(!$par = echo_message_from_par($par)) return false;
				$settings = json_decode($par['input_address'], true);
        foreach($settings as &$tSetting){
			$tSetting = text()->variables($tSetting, $par);
			$tSetting = text()->shortcodes($tSetting, $par);
		}
		
		if($address = $settings['address']){
		    $par['input_address']=$settings;
		    $this->getResult($par);
		} elseif(isset($obj['message']['location']) && (!isset($par['input_address_message_check']) || $par['input_address_message_check']!=$message_id )){
            $par['input_address'] = $settings;
            set_pos($par['step'], $par);
            $settings['par']=$par;

		    $this->getResult($par);
		    return;
		}

        $par['input_address'] = $settings;
        set_pos($par['step'], $par);

        return true;
    }

	private function getResult($par){
	global $chat_id, $obj, $message_id;		


        $this->$cicle++;
        if($this->$cicle>5){
            tgmess('Ошибка! Произошел бесконечный цикл');
            return;
        }
		$text = $par['input_address']['address'];
		
		if($text==''){
			$location = $obj['message']['location'];
			if(!$location){
					tgMess(DIALTEXT('inputAddressSendTheAddress')); # Пришлите адрес
					return false;
			}
					
			$latitude = str_replace(',', '.', $location['latitude']);
			$longitude = str_replace(',', '.', $location['longitude']);
			$text = $longitude.','.$latitude;
		}




		$key = setting('yandex_map_key');
		if(!$key)
			return notification(DIALTEXT('inputAddressSpecifyTheYandexMapsKeyInTheSettings')); # Укажите в настройках ключ яндекс карт
				
		$parameters = [
	         'apikey'=> $key
	        ,'lang'=> $par['input_address']['lang']
	        ,'format'=>'json'
	        ,'geocode'=> $text
	    ];
		    
		if($kind = $par['input_address']['kind'])
		$parameters['kind'] = $kind;
		$res = json_decode(curl()->get('https://geocode-maps.yandex.ru/1.x/', $parameters), true);

		$arr = $res['response']['GeoObjectCollection']['featureMember'];


    	//foreach ($arr as $k => $v)
    	//		if($v['GeoObject']['metaDataProperty']['GeocoderMetaData']['precision']!="exact") unset($arr[$k]);

		//if(count($arr)==0){
		//		$arr = $res['response']['GeoObjectCollection']['featureMember'];
		//		foreach ($arr as $k => $v)
		//				if($v['GeoObject']['metaDataProperty']['GeocoderMetaData']['precision']=="other") unset($arr[$k]);
		//}


		foreach ($arr as $k => $v)
		if (empty($v)) unset($arr[$k]);

		if(count($arr)>1){

			$arr2=$arr;

			$max = 0;

			foreach ($arr2 as $k => $v){
    			$c = count($v['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['Components']);
    			if($max<$c) $max = $c;
			}

			foreach ($arr2 as $k => $v){
		        if(count($v['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['Components'])<round($max*0.8)) unset($arr2[$k]);
			}

			foreach ($arr2 as $k => $v)
			if (empty($v)) unset($arr2[$k]);

			if(count($arr2)==1) $arr = $arr2;

		}


        if(count($arr)>1)
            $arr = [$arr[0]];

		if(count($arr)==0)
			$arr = $res['response']['GeoObjectCollection']['featureMember'];

		if(count($arr)==0)
			return notification(DIALTEXT('inputAddressIDidntFindSuchAnAddress')); # Я не нашел такого адреса, введите более точный адрес

		if(count($arr)==1&&!isset($obj['message']['location']))
			tgMess(DIALTEXT('inputAddressClarificationWhenIssuingIfSeveralOptionsAreFound')); # Верно? Если да, нажмите на кнопку под картой, если нет, введите более точный адрес

		if(count($arr)>1)
			tgMess(DIALTEXT('inputAddressClarificationWhenIssuingIfSeveralOptionsAreFound')); # Выберите вариант или введите более точный адрес



        if(count($arr)==1 && isset($obj['message']['location'])){
            $desc = $arr[0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['text'];
            $address = $arr[0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address'];
			$dataArr = [
				 'text'=>$desc
				,'longitude'=>$obj['message']['location']['longitude']
				,'latitude'=>$obj['message']['location']['latitude']

			];
			foreach($address as $addKey=> $addVal)
				$dataArr[mb_strtolower($addKey)] = $addVal;
            $dataArr['name'] = $arr[0]['GeoObject']['name'];
			$data = setData($dataArr);		
			$GLOBALS['text_message'] = json_encode([ 'dataId'=>$data ]); # чтобы в слушателе инициализировался как массив
			
			
			$this->listener($par); //?$par['par']:$par
			return;
        }


		foreach ($arr as $geo){
			$desc = $geo['GeoObject']['metaDataProperty']['GeocoderMetaData']['text'];
			$address = $geo['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address'];
			$components = ['Components'];
			$point = $geo['GeoObject']['Point']['pos'];



			$longitude = false;
			$latitude = false;
			$pointArr = explode(" ", $point);

			if(count($pointArr)==2){
				$longitude = $pointArr[0];
				$latitude = $pointArr[1];
			}

			if($longitude && $latitude){

				$dataArr = [
					 'text'=>$desc
					,'longitude'=>$longitude
					,'latitude'=>$latitude
				];

				foreach($address as $addKey=> $addVal)
					$dataArr[mb_strtolower($addKey)] = $addVal;

                
                $dataArr['name'] = $geo['GeoObject']['name'];//$arr[0]['GeoObject']['name'];
				$data = setData($dataArr);
				$kb = [[['text'=> $desc,"callback_data"=>json_encode([ 'dataId'=>$data ])]]];
				$kb=["inline_keyboard"=>$kb];
				
				
				
				methods()->sendLocation('', $longitude, $latitude, $kb, $chat_id);
			}

		}


	}




    public function listener( array $par=[] )
    {
        global $chat_id, $text_message, $message_id;
        $obj = json_decode(input_data, true);
        
        if(!intermediate_function($par)) return;
        
        $text = $text_message;


        $l_text = trim(mb_strtolower($text, 'utf-8'));

        if($l_text=='пропустить'){
            $par[$par['script_step']]='';
            set_pos($par['step'], $par);
            unset($par['input_address']);
            unset($par['input_address_message_check']);
            if($message_id) $par['input_address_message_check'] = $message_id;
            the_distribution_module($par['script_source'],$par);
            return;
        }

        # если выбрали на inline клавиатуре
        if(is_array(json_decode($text, true))){
   
            $d = json_decode($text);
            $dataId = $d->dataId;
            $data = getData($dataId);
            if(!$data)
                return notification(DIALTEXT('inputAddressDataLostPleaseTryAgain'));
            $desc = $data['text'];
						
					  $latitude = str_replace(',', '.', $data['latitude']);
					  $longitude = str_replace(',', '.', $data['longitude']);
						
            $components = $data['components'];

            foreach($components as $component){
                $par[$par['script_step'].'_'.$component['kind']]=$component['name'];
                $components[$component['kind']]=$component['name'];
            }
                
            $par[$par['script_step']]=$desc;
            //$par[$par['script_step'].'_region']=$region;
            $par[$par['script_step'].'_country_code']=$data['country_code'];
            $par[$par['script_step'].'_postal_code']=$data['postal_code'];
            $par[$par['script_step'].'_address']=$desc;
            $par[$par['script_step'].'_longitude']=$longitude;
            $par[$par['script_step'].'_latitude']=$latitude;
            $par[$par['script_step'].'_name']=$data['name'];

            if($par['input_address']['write_to_the_geo_points']){
                if($gp=singleQuery('SELECT * FROM `geo_points` WHERE longitude=? AND latitude=?', [ $longitude, $latitude ])){
										$db_id=$gp['id'];
								} else {
										$db_id = query('INSERT INTO `geo_points` (`country_code`, `locality`, `address`, `longitude`, `latitude`, `data`) VALUES (?,?,?,?,?,?)',
																					[ $data['country_code'], $data['name'], $desc, $longitude, $latitude, json_encode($data)]);
								}

                 
                $par[$par['script_step'].'_data_id']=$db_id;
            }


            unset($par['method']);
            unset($par['dataId']);
            if(!intermediate_function($par)) return;
            unset($par['input_address']);
            unset($par['input_address_message_check']);
            if($message_id) $par['input_address_message_check'] = $message_id;
            the_distribution_module($par['script_source'], $par);
            return;
        }
				
				
				
				$settings=$par['input_address'];
				$allow_manual_input=$settings['allow_manual_input']??1;
				$coordinates_only=$settings['coordinates_only']??0;
				if((!$allow_manual_input || $coordinates_only) && !isset($obj['message']['location'])) {
						tgMess('Надо прислать локацию');
						return;
				}

				if($coordinates_only){
						$l=$obj['message']['location'];
						$par[$par['script_step']]=$l;
            $par[$par['script_step'].'_longitude']=$l['longitude'];
            $par[$par['script_step'].'_latitude']=$l['latitude'];
						the_distribution_module($par['script_source'], $par);
						return;
				}

        $par['input_address']['address'] = $l_text;
        $this->getResult($par);



        





        return true;
    }

}
