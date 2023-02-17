<?php namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Cabinet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Session;
use Request;
use DB;
use App\Constructor\helpers\BTBooster;
use App\Constructor\controllers\BTController;

class BotController extends BTController {

    protected $old_hook = null;

	public function cbInit() {
		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->table               = 'bot';
		$this->primary_key         = 'id';
		$this->title_field         = "name";
		$this->button_action_style = 'button_icon';
		$this->button_import 	   = FALSE;
		$this->button_export 	   = FALSE;
		# END CONFIGURATION DO NOT REMOVE THIS LINE

		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = array();
		$this->col[] = array("label"=>"Имя","name"=>"name");
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = array();
		$this->form[] = array("label"=>"Имя","name"=>"name",'required'=>true,'validation'=>'required|min:3|max:255');
        $this->form[] = array("label"=>"Ключ","name"=>"bot_key",'required'=>true,'validation'=>'required|min:3|max:255');
        //$this->form[] = ['label'=>'Диспетчеры','name'=>'name','type'=>'select2','validation'=>'required|integer|min:0','width'=>'col-sm-10','datatable'=>'bot_dispetcher,bot_id'];

        $cabinet = \App\Models\Cabinet::curCabinet();
        $this->form[] = [
            'label'=>'Диспетчеры',
            'name'=>'disp_id',
            'type'=>'checkbox',
            'validation'=>'',
            'width'=>'col-sm-10',
            'datatable'=>'users,name',
            'datatable_where'=>'`users`.cabinet_id = '.$cabinet->id.' AND `users`.id_cms_privileges = 5',
            'relationship_table'=>'bot_dispetcher'
        ];
		# END FORM DO NOT REMOVE THIS LINE

	}

	public function hook_before_edit(&$postdata,$id) {
        $bot = Bot::where('id', $id)->first();
        $old_hook = $bot->hash;

        unset($postdata['cabinet_id']);
        unset($postdata['id_cms_privileges']);
	}

	public function hook_before_add(&$postdata) {
        $cabinet = \App\Models\Cabinet::curCabinet();
        $postdata['hash'] = md5(Str::random(12));
        $postdata['cabinet_id'] = $cabinet->id;
	}

    public function hook_after_add($id) {
	    $bot = Bot::where('id', $id)->first();
        $value = $bot->bot_key;
        /* @TODO Убрать проверку после тестирования */
        if(strlen($value) > 10) {
            $dsf = $_SERVER['DOCUMENT_ROOT'].'/SECRETFOLDER/';
            $files = array_diff(scandir($dsf), []);

            $num = 0;
            $botfile = '';
            for($i=0;$i<count($files);$i++){
                $file = $files[$i];
                if(strpos(' '.$file, 'tgbot'))
                    $botfile = $file;

            }

            $url = 'https://api.telegram.org/bot'.$value.'/setWebhook?url=https://'.$_SERVER['HTTP_HOST'].'/SECRETFOLDER/'.$botfile.'?bot_hash='.$bot->hash; //.'&drop_pending_updates=1'

            $resp = json_decode( @file_get_contents( $url ), 1);

            /**
             * Если вебхук установлен
             */
            if( @$resp['ok'] ){
                /* @TODO Выдать сообщение об успехе */
            } else {
                /* @TODO Выдать сообщение об ошибке */
            }
        }
    }

    public function hook_after_edit($id)
    {
        $bot = Bot::where('id', $id)->first();
        $value = $bot->bot_key;
        /* @TODO Убрать проверку после тестирования */
        if(strlen($value) > 10 /*&& $this->old_hook != $bot->hook*/) {
            $dsf = $_SERVER['DOCUMENT_ROOT'].'/SECRETFOLDER/';
            $files = array_diff(scandir($dsf), []);

            $num = 0;
            $botfile = '';
            for($i=0;$i<count($files);$i++){
                $file = $files[$i];
                if(strpos(' '.$file, 'tgbot'))
                    $botfile = $file;

            }

            $url = 'https://api.telegram.org/bot'.$value.'/setWebhook?url=https://'.$_SERVER['HTTP_HOST'].'/SECRETFOLDER/'.$botfile.'?bot_hash='.$bot->hash; //.'&drop_pending_updates=1'

            $resp = json_decode( @file_get_contents( $url ), 1);

            /**
             * Если вебхук установлен удаляем старый вебхук
             */
            /*if( $resp['ok'] && $this->old_hook != $bot->hook){
                @file_get_contents('https://api.telegram.org/bot'.$this->old_hook.'/deleteWebhook');
            }*/

            $data = $resp;
        }
    }
}
