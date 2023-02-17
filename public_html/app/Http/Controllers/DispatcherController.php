<?php namespace App\Http\Controllers;

use App\Models\Cabinet;
use Illuminate\Support\Facades\Auth;
use Session;
use Request;
use DB;
use App\Constructor\helpers\BTBooster;
use App\Constructor\controllers\BTController;

class DispatcherController extends BTController {

    public $zones = [
        'Europe/Kaliningrad|Калининград (-1)',
        'Europe/Moscow|Москва',
        'Europe/Samara|Самара (+1)',
        'Asia/Yekaterinburg|Екатеринбург (+2)',
        'Asia/Omsk|Омск (+3)',
        'Asia/Krasnoyarsk|Красноярск (+4)',
        'Asia/Irkutsk|Иркутск (+5)',
        'Asia/Yakutsk|Якутск (+6)',
        'Asia/Vladivostok|Владивосток (+7)',
        'Asia/Magadan|Магадан (+8)',
        'Asia/Kamchatka|Петропавловск-Камчатский (+9)'
    ];

	public function cbInit() {
		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->table               = 'users';
		$this->primary_key         = 'id';
		$this->title_field         = "name";
		$this->button_action_style = 'button_icon';
		$this->button_import 	   = FALSE;
		$this->button_export 	   = FALSE;
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = array();
		$this->col[] = array("label"=>"Имя","name"=>"name");
		$this->col[] = array("label"=>"Email","name"=>"email");
		$this->col[] = array("label"=>"Аватар","name"=>"photo","image"=>1);
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = array();
		$this->form[] = array("label"=>"Имя","name"=>"name",'required'=>true,'validation'=>'required|alpha_spaces|min:3|max:100');
		$this->form[] = array("label"=>"Email (Логин)","name"=>"email",'required'=>true,'type'=>'email','validation'=>'required|email|max:200|unique:users,email,'.BTBooster::getCurrentId());
        $this->form[] = array("label"=>"Телефон","name"=>"phone",'required'=>true,'validation'=>'required|unique:users|min:11|max:12');
        $this->form[] = array("label"=>"Временная зона","name"=>"timezone",'required'=>true,'type'=>'select','dataenum' => $this->zones);
		$this->form[] = array("label"=>"Пароль","name"=>"password","type"=>"password","help"=>"Оставьте пустым если не меняете", 'validation' => 'confirmed');
		$this->form[] = array("label"=>"Подтверждение пароля","name"=>"password_confirmation","type"=>"password","help"=>"Оставьте пустым если не меняете");
        $cabinet = \App\Models\Cabinet::curCabinet();
        $this->form[] = [
            'label'=>'Боты',
            'name'=>'bot_id',
            'type'=>'checkbox',
            'validation'=>'',
            'width'=>'col-sm-10',
            'datatable'=>'bot,name',
            'datatable_where'=>'`bot`.cabinet_id = '.$cabinet->id,
            'relationship_table'=>'bot_dispetcher'
        ];
		# END FORM DO NOT REMOVE THIS LINE

	}

	public function hook_before_edit(&$postdata,$id) {
		unset($postdata['password_confirmation']);
        unset($postdata['cabinet_id']);
        unset($postdata['id_cms_privileges']);
	}

	public function hook_before_add(&$postdata) {
	    //dd($postdata);
	    unset($postdata['password_confirmation']);

        $cabinet = \App\Models\Cabinet::curCabinet();

        $postdata['id_cms_privileges'] = 5;
        $postdata['cabinet_id'] = $cabinet->id;
	}
}
