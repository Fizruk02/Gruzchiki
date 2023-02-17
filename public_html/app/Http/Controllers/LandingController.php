<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use BTBooster;

	class LandingController extends \App\Constructor\controllers\BTController {

	    public $model;

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "id";
			$this->limit = "20";
			$this->orderby = "id,desc";
			$this->global_privilege = true;
			//$this->button_table_action = true;
			//$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			//$this->button_add = true;
			//$this->button_edit = true;
			//$this->button_delete = true;
			//$this->button_detail = false;
			//$this->button_show = false;
			//$this->button_filter = true;
			$this->button_import = FALSE;
			$this->button_export = FALSE;
			$this->table = "cabinet";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			//$this->col[] = ["label"=>"Администратор","name"=>"user_id","join"=>"users,id"];
			//$this->col[] = ["label"=>"Заканчивается","name"=>"finish_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
            $fields = json_decode($this->model->land_title, true);
            if (!$fields) {
                $fields = [
                    'title' => 'Услуги ОТВЕТСТВЕННЫХ разнорабочих г.',
                    'block' => 'Лучшее соотношение цена-качество в городе!
Звоните с 7.00 до 23.00 - ЕЖЕДНЕВНО!
Работаем во всех районах!
Работаем с юр.лицами по Безналу на особых условиях!',
                    'title2' => 'ПОЧЕМУ МЫ?',
                    'block2' => 'ОПЫТ
Большой опыт в данной сфере услуг
ЛУЧШИЕ ЦЕНЫ
Мы мониторим цены в городе, и постоянно стараемся соответствовать лучшему соотношению цена-качество
ОПЕРАТИВНОЕ РАЗМЕЩЕНИЕ ЗАЯВКИ
Наша команда незамедлительно получает задание
ДОГОВОР
При вашем желании, мы можем работать по договору
ОПЛАТА ПО БЕЗНАЛУ
Принимаем оплату как наличным, так и безналичным путем с предоставлением всех документов.',
                    'title3' => 'УСЛУГИ РАЗНОРАБОЧИХ ОТ НАШЕЙ КОМПАНИИ',
                    'block3' => 'Черновые, подсобные, общестроительные и вспомогательные работы.
Промышленный демонтаж, работа с отбойниками.
Строительство фундаментов, сборка домов и бань.
Бетонные работы, арматура, опалубка.
Гипсокартонные работы.
Кровельные работы.
Штробление, сверление.',
                ];
            }
			$this->form = [];
            $this->form[] = ['label'=>'Заголовок','name'=>'title','type'=>'text','validation'=>'string|min:10|max:255','width'=>'col-sm-10', 'value' => $fields['title']];
            $this->form[] = ['label'=>'Первый блок','name'=>'block','type'=>'textarea','validation'=>'string|min:10|max:1000','width'=>'col-sm-10', 'value' => $fields['block']];
            $this->form[] = ['label'=>'Заголовок 2','name'=>'title2','type'=>'text','validation'=>'string|min:10|max:255','width'=>'col-sm-10', 'value' => $fields['title2']];
            $this->form[] = ['label'=>'Второй блок','name'=>'block2','type'=>'textarea','validation'=>'string|min:10|max:1000','width'=>'col-sm-10', 'value' => $fields['block2']];
            $this->form[] = ['label'=>'Заголовок 3','name'=>'title3','type'=>'text','validation'=>'string|min:10|max:255','width'=>'col-sm-10', 'value' => $fields['title3']];
            $this->form[] = ['label'=>'Третий блок','name'=>'block3','type'=>'textarea','validation'=>'string|min:10|max:1000','width'=>'col-sm-10', 'value' => $fields['block3']];
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ["label"=>"Finish At","name"=>"finish_at","type"=>"datetime","required"=>TRUE,"validation"=>"required|date_format:Y-m-d H:i:s"];
			//$this->form[] = ["label"=>"User Id","name"=>"user_id","type"=>"select2","required"=>TRUE,"validation"=>"required|integer|min:0","datatable"=>"user,id"];
			# OLD END FORM

			/*
	        | ----------------------------------------------------------------------
	        | Sub Module
	        | ----------------------------------------------------------------------
			| @label          = Label of action
			| @path           = Path of sub module
			| @foreign_key 	  = foreign key of sub table/module
			| @button_color   = Bootstrap Class (primary,success,warning,danger)
			| @button_icon    = Font Awesome Class
			| @parent_columns = Sparate with comma, e.g : name,created_at
	        |
	        */
	        $this->sub_module = array();


	        /*
	        | ----------------------------------------------------------------------
	        | Add More Action Button / Menu
	        | ----------------------------------------------------------------------
	        | @label       = Label of action
	        | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
	        | @icon        = Font awesome class icon. e.g : fa fa-bars
	        | @color 	   = Default is primary. (primary, warning, succecss, info)
	        | @showIf 	   = If condition when action show. Use field alias. e.g : [id] == 1
	        |
	        */
	        $this->addaction = array();


	        /*
	        | ----------------------------------------------------------------------
	        | Add More Button Selected
	        | ----------------------------------------------------------------------
	        | @label       = Label of action
	        | @icon 	   = Icon from fontawesome
	        | @name 	   = Name of button
	        | Then about the action, you should code at actionButtonSelected method
	        |
	        */
	        $this->button_selected = array();


	        /*
	        | ----------------------------------------------------------------------
	        | Add alert message to this module at overheader
	        | ----------------------------------------------------------------------
	        | @message = Text of message
	        | @type    = warning,success,danger,info
	        |
	        */
	        $this->alert        = array();



	        /*
	        | ----------------------------------------------------------------------
	        | Add more button to header button
	        | ----------------------------------------------------------------------
	        | @label = Name of button
	        | @url   = URL Target
	        | @icon  = Icon from Awesome.
	        |
	        */
	        $this->index_button = array();



	        /*
	        | ----------------------------------------------------------------------
	        | Customize Table Row Color
	        | ----------------------------------------------------------------------
	        | @condition = If condition. You may use field alias. E.g : [id] == 1
	        | @color = Default is none. You can use bootstrap success,info,warning,danger,primary.
	        |
	        */
	        $this->table_row_color = array();


	        /*
	        | ----------------------------------------------------------------------
	        | You may use this bellow array to add statistic at dashboard
	        | ----------------------------------------------------------------------
	        | @label, @count, @icon, @color
	        |
	        */
	        $this->index_statistic = array();



	        /*
	        | ----------------------------------------------------------------------
	        | Add javascript at body
	        | ----------------------------------------------------------------------
	        | javascript code in the variable
	        | $this->script_js = "function() { ... }";
	        |
	        */
	        $this->script_js = NULL;


            /*
	        | ----------------------------------------------------------------------
	        | Include HTML Code before index table
	        | ----------------------------------------------------------------------
	        | html code to display it before index table
	        | $this->pre_index_html = "<p>test</p>";
	        |
	        */
	        $this->pre_index_html = null;



	        /*
	        | ----------------------------------------------------------------------
	        | Include HTML Code after index table
	        | ----------------------------------------------------------------------
	        | html code to display it after index table
	        | $this->post_index_html = "<p>test</p>";
	        |
	        */
	        $this->post_index_html = null;



	        /*
	        | ----------------------------------------------------------------------
	        | Include Javascript File
	        | ----------------------------------------------------------------------
	        | URL of your javascript each array
	        | $this->load_js[] = asset("myfile.js");
	        |
	        */
	        $this->load_js = array();



	        /*
	        | ----------------------------------------------------------------------
	        | Add css style at body
	        | ----------------------------------------------------------------------
	        | css code in the variable
	        | $this->style_css = ".style{....}";
	        |
	        */
	        $this->style_css = NULL;



	        /*
	        | ----------------------------------------------------------------------
	        | Include css File
	        | ----------------------------------------------------------------------
	        | URL of your css each array
	        | $this->load_css[] = asset("myfile.css");
	        |
	        */
	        $this->load_css = array();


	    }


	    /*
	    | ----------------------------------------------------------------------
	    | Hook for button selected
	    | ----------------------------------------------------------------------
	    | @id_selected = the id selected
	    | @button_name = the name of button
	    |
	    */
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here

	    }


	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate query of index result
	    | ----------------------------------------------------------------------
	    | @query = current sql query
	    |
	    */
	    public function hook_query_index(&$query) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate row of index table html
	    | ----------------------------------------------------------------------
	    |
	    */
	    public function hook_row_index($column_index,&$column_value) {
	    	//Your code here
	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before add data is execute
	    | ----------------------------------------------------------------------
	    | @arr
	    |
	    */
	    public function hook_before_add(&$postdata) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after add public static function called
	    | ----------------------------------------------------------------------
	    | @id = last insert id
	    |
	    */
	    public function hook_after_add($id) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for manipulate data input before update data is execute
	    | ----------------------------------------------------------------------
	    | @postdata = input post data
	    | @id       = current id
	    |
	    */
	    public function hook_before_edit(&$postdata,$id) {
	        //Your code here
            $fields = [
                'land_title' => json_encode([
                    'title' => $postdata['title'],
                    'block' => $postdata['block'],
                    'title2' => $postdata['title2'],
                    'block2' => $postdata['block2'],
                    'title3' => $postdata['title3'],
                    'block3' => $postdata['block3'],
                ])
            ];
            $postdata = $fields;
	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after edit public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	    public function hook_after_edit($id) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command before delete public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	    public function hook_before_delete($id) {
	        //Your code here

	    }

	    /*
	    | ----------------------------------------------------------------------
	    | Hook for execute command after delete public static function called
	    | ----------------------------------------------------------------------
	    | @id       = current id
	    |
	    */
	    public function hook_after_delete($id) {
	        //Your code here

	    }



	    //By the way, you can still create your own method in here... :)


	}