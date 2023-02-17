<?php namespace App\Http\Controllers;

use App\Models\Cabinet;
use App\Models\UsersProfiles;
use Illuminate\Support\Facades\Auth;
use Session;
use Request;
use DB;
use App\Constructor\helpers\BTBooster;
use App\Constructor\controllers\BTController;

class EmployeeController extends BTController {

    protected $profile = null;

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
		//$this->form[] = array("label"=>"Имя","name"=>"name",'required'=>true,'validation'=>'required|alpha_spaces|min:3');
		//$this->form[] = array("label"=>"Email (Логин)","name"=>"email",'required'=>true,'type'=>'email','validation'=>'required|email|unique:users,email,'.BTBooster::getCurrentId());
        $this->form[] = array("label"=>"Телефон","name"=>"phone",'required'=>true,'validation'=>'required|min:11|max:12');
		//$this->form[] = array("label"=>"Пароль","name"=>"password","type"=>"password","help"=>"Оставьте пустым если не меняете", 'validation' => 'confirmed');
		//$this->form[] = array("label"=>"Подтверждение пароля","name"=>"password_confirmation","type"=>"password","help"=>"Оставьте пустым если не меняете");

        $this->form[] = ["label"=>"Фамилия","name"=>"f",'required'=>true,'validation'=>'required|min:2|max:50',"join"=>"users_profiles,f"];
        $this->form[] = ["label"=>"Имя","name"=>"i",'required'=>true,'validation'=>'required|min:1|max:50', 'join'=>'users_profiles,i'];
        $this->form[] = ["label"=>"Отчество","name"=>"o",'required'=>true,'validation'=>'required|min:1|max:50', 'join'=>'users_profiles,o'];

        $this->form[] = array("label"=>"День рождения","name"=>"birthday_at",'required'=>true,'validation'=>'required', 'type' => 'date',"join"=>"users_profiles,birthday_at", 'callback'=>function($row) { return date('d.m.Y', $row->users_profiles->birthday_at); } );

        $this->form[] = array("label"=>"Специализация","name"=>"special",'required'=>false,"join"=>"users_profiles,special", 'validation'=>'max:200');
        $this->form[] = array("label"=>"Гражданство РФ","name"=>"is_rf",'required'=>false, "join"=>"users_profiles,is_rf", "type"=>"checkbox", 'dataenum' => '');
        $this->form[] = array("label"=>"Трудоустроен","name"=>"is_worker",'required'=>false,"join"=>"users_profiles,is_worker", "type"=>"checkbox", 'dataenum' => '');
        $this->form[] = array("label"=>"Работа","name"=>"work",'required'=>false,"join"=>"users_profiles,work", 'type'=>'textarea', 'validation'=>'max:200');
        $this->form[] = array("label"=>"Семейное положение","name"=>"family",'required'=>false,"join"=>"users_profiles,family", "type"=>"checkbox", 'dataenum' => '');
        $this->form[] = array("label"=>"Дети","name"=>"children",'required'=>false,"join"=>"users_profiles,children", 'validation'=>'max:200');
        $this->form[] = array("label"=>"В какие дни и время вы готовы подрабатывать","name"=>"times",'required'=>false,"join"=>"users_profiles,times", 'validation'=>'max:200');
        $this->form[] = array("label"=>"Опыт работы грузчиком, такелажником, может быть в строительстве","name"=>"experience",'required'=>false,"join"=>"users_profiles,experience", 'validation'=>'max:200');
        $this->form[] = array("label"=>"Наличие судимости","name"=>"is_criminal",'required'=>false,"join"=>"users_profiles,is_criminal", "type"=>"checkbox", 'dataenum' => '');
        $this->form[] = array("label"=>"Наличие автомобиля","name"=>"is_car",'required'=>false,"join"=>"users_profiles,is_car", "type"=>"checkbox", 'dataenum' => '');
        $this->form[] = array("label"=>"Город","name"=>"city",'required'=>false, "join"=>"users_profiles,city", 'validation'=>'max:200');
        $this->form[] = array("label"=>"Район проживания","name"=>"district",'required'=>false,"join"=>"users_profiles,district", 'validation'=>'max:200');
        $this->form[] = array("label"=>"Паспорт","name"=>"passport",'required'=>false,"join"=>"users_profiles,passport", 'validation'=>'max:200');
        $this->form[] = array("label"=>"Снилс","name"=>"snils",'required'=>false,"join"=>"users_profiles,snils", 'validation'=>'max:200');
        $this->form[] = array("label"=>"Комментарий","name"=>"comment",'required'=>false,"join"=>"users_profiles,comment", 'type' => 'textarea', 'validation'=>'max:1000');

        $cabinet = \App\Models\Cabinet::curCabinet();
		# END FORM DO NOT REMOVE THIS LINE

	}

	public function hook_before_edit(&$postdata,$id) {
        $profile = $postdata;

        $postdata = [
            'phone' => $profile["phone"],
            'updated_at' => $profile["updated_at"],
        ];

        unset($profile["phone"]);
        unset($profile["updated_at"]);

        $this->profile = $profile;
	}

    public function hook_after_edit($id)
    {
        $this->profile["birthday_at"] = strtotime($this->profile["birthday_at"].' 00:00');

        $this->profile["is_rf"] = $this->profile["is_rf"] ? '1' : '0';
        $this->profile["family"] = $this->profile["family"] ? '1' : '0';
        $this->profile["is_worker"] = $this->profile["is_worker"] ? '1' : '0';
        $this->profile["is_criminal"] = $this->profile["is_criminal"] ? '1' : '0';
        $this->profile["is_car"] = $this->profile["is_car"] ? '1' : '0';

            //dump($bd);

        UsersProfiles::where('users_id', $id)->update($this->profile);

        //parent::hook_after_edit($id); // TODO: Change the autogenerated stub
    }

    public function hook_before_add(&$postdata) {

	    unset($postdata['password_confirmation']);

        $cabinet = \App\Models\Cabinet::curCabinet();

        $postdata['id_cms_privileges'] = 5;
        $postdata['cabinet_id'] = $cabinet->id;
	}
}
