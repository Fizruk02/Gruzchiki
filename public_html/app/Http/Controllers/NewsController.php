<?php namespace App\Http\Controllers;

use App\Models\Cabinet;
use App\Models\OrdersBalance;
use App\Models\OrdersFields;
use App\Models\OrdersValues;
use App\Models\UsersProfiles;
use Illuminate\Support\Facades\Auth;
use Session;
use Request;
use DB;
use App\Constructor\helpers\BTBooster;
use App\Constructor\controllers\BTController;

class NewsController extends BTController {

    public $fields = [];
    public $baseFields = [];
    public $valueFields = [];
    protected $postValues = [];
    protected $profile = null;

	public function cbInit() {
		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->table               = 'news';
		$this->primary_key         = 'id';
		$this->title_field         = "title";
		$this->button_action_style = 'button_icon';
		$this->button_import 	   = FALSE;
		$this->button_export 	   = FALSE;
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = $this->fields;
		# END FORM DO NOT REMOVE THIS LINE

        $this->button_exts = [
            'Сохранить и разослать сейчас' => [
                'type' => 'submit',
                'class' => 'bg-yellow-700',
                'name' => 'send_now',
            ],
            'Отправить по рассписанию' => [
                'id' => 'send_time',
                'type' => 'button',
                'class' => 'bg-orange-700',
                'name' => 'send_time',
                'modal' => [
                    'id' => 'timesend-modal',
                    'component' => 'components.send_time',
                ]
            ]
        ];
	}

	public function hook_before_edit(&$postdata,$id) {
	    $this->postValues = $postdata;
        $postdata = [
            'id' => $id,
            'title' => $this->postValues['title'],
            'description' => $this->postValues['description'],
            'updated_at' => $this->postValues['updated_at'],
        ];
        if (request()->request->get('send_now')) {
            $postdata['type_send'] = 1;
        }
        if (request()->request->get('mail_at')) {
            $postdata['type_send'] = 2;
            $postdata['send_at'] = strtotime(request()->request->get('mail_at'));
        }
	}

    public function hook_before_add(&$postdata) {

        $cabinet = \App\Models\Cabinet::curCabinet();

        $this->postValues = $postdata;
        $postdata = [
            'bot_id' => $postdata['bot_id'],
            'cabinet_id' => $cabinet->id,
            'title' => $this->postValues['title'],
            'description' => $this->postValues['description'],
            'created_at' => $this->postValues['created_at'],
        ];

        if (request()->request->get('send_now')) {
            $postdata['type_send'] = 1;
        }
        if (request()->request->get('mail_at')) {
            $postdata['type_send'] = 2;
            $postdata['send_at'] = strtotime(request()->request->get('mail_at'));
        }
	}
}
