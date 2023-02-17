<?php namespace App\Http\controllers;

use App\Models\Cabinet;
use Session;
use Request;
use DB;
use App\Constructor\helpers\BTBooster;
use App\Constructor\controllers\BTController;

class AdminCmsUsersController extends BTController {

    public $city = null;
    public $domain = null;
    public $land_title = null;

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
		//$this->col[] = array("label"=>"Привелегии","name"=>"id_cms_privileges","join"=>"cms_privileges,name");
		$this->col[] = array("label"=>"Аватар","name"=>"photo","image"=>1);
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = array();
		$this->form[] = array("label"=>"Имя","name"=>"name",'required'=>true,'validation'=>'required|alpha_spaces|min:3|max:100');
		$this->form[] = array("label"=>"Email (Логин)","name"=>"email",'required'=>true,'type'=>'email','validation'=>'required|email|max:200|unique:users,email,'.BTBooster::getCurrentId());
        $this->form[] = array("label"=>"Телефон","name"=>"phone",'required'=>true,'validation'=>'required|unique:users|min:11|max:12');
        $this->form[] = array("label"=>"Город","name"=>"city",'required'=>true,'validation'=>'required|min:3|max:200', "value"=>$this->city);
        $this->form[] = array("label"=>"Лендинг","name"=>"domain",'required'=>true,'validation'=>'required|min:3|max:200', "value"=>$this->domain);
		//$this->form[] = array("label"=>"Аватар","name"=>"photo","type"=>"upload","help"=>"Recommended resolution is 200x200px",'required'=>true,'validation'=>'required|image|max:1000','resize_width'=>90,'resize_height'=>90);
		//$this->form[] = array("label"=>"Привелегии","name"=>"id_cms_privileges","type"=>"select","datatable"=>"cms_privileges,name",'required'=>true);
		// $this->form[] = array("label"=>"Password","name"=>"password","type"=>"password","help"=>"Please leave empty if not change");
		$this->form[] = array("label"=>"Пароль","name"=>"password","type"=>"password","help"=>"Оставьте пустым если не меняете", 'validation' => 'confirmed');
		$this->form[] = array("label"=>"Подтверждение пароля","name"=>"password_confirmation","type"=>"password","help"=>"Оставьте пустым если не меняете");

        $this->form[] = array("label"=>"Заголовок для лендинга","name"=>"land_title","type"=>"textarea","help"=>"", "value"=>$this->land_title);
		# END FORM DO NOT REMOVE THIS LINE

	}

	public function getProfile() {
		$this->button_addmore = FALSE;
		$this->button_cancel  = FALSE;
		$this->button_show    = FALSE;
		$this->button_add     = FALSE;
		$this->button_delete  = FALSE;
		$this->hide_form 	  = ['id_cms_privileges'];

		$data['page_title'] = cbLang("label_button_profile");
		//$data['row']        = BTBooster::first('cms_users',BTBooster::myId());
        $data['row']        = BTBooster::first('users',BTBooster::myId());
//dump(BTBooster::myId());
//dump($data);
        return $this->view('crudbooster::default.form',$data);
        //return $this->view('crudbooster::default.form_body',$data);
	}

	public function hook_before_edit(&$postdata,$id) {
	    $this->city = $postdata['city'];
        $this->domain = $postdata['domain'];
        $this->land_title = $postdata['land_title'];

		unset($postdata['password_confirmation']);
        unset($postdata['city']);
        unset($postdata['domain']);
        unset($postdata['land_title']);
	}

	public function hook_after_edit($id)
    {
        $cabinet = Cabinet::where('users_id', $id)->first();
        $cabinet->city = $this->city;
        $cabinet->domain = $this->domain;
        $cabinet->land_title = $this->land_title;
        $cabinet->save();
    }

    public function hook_before_add(&$postdata) {
        $this->city = $postdata['city'];

	    unset($postdata['password_confirmation']);
        unset($postdata['city']);
        unset($postdata['domain']);
        unset($postdata['land_title']);

        $postdata['id_cms_privileges'] = 3;
	}

    public function hook_after_add($id) {
        $cabinet = new Cabinet();
        $cabinet->users_id = $id;
        $cabinet->city = $this->city;
        $cabinet->domain = $this->domain;
        $cabinet->land_title = $this->land_title;
        $cabinet->finish_at = date('Y-m-d H:i:s');
        $cabinet->save();

        $fields = \App\Models\OrdersFields::query()->whereRaw('cabinet_id IS NULL')->orderBy('sort')->get();
        foreach ($fields as $f) {
            $data = $f->toArray();
            $data['id'] = null;
            $data['cabinet_id'] = $cabinet->id;
            //\App\Models\OrdersFields::insertGetId($data);
            \App\Models\OrdersFields::insert($data);
        }

        $fields = \App\Models\RequestFields::query()->whereRaw('cabinet_id IS NULL')->orderBy('sort')->get();
        foreach ($fields as $f) {
            $data = $f->toArray();
            $data['id'] = null;
            $data['cabinet_id'] = $cabinet->id;
            //\App\Models\OrdersFields::insertGetId($data);
            \App\Models\RequestFields::insert($data);
        }
    }
}
