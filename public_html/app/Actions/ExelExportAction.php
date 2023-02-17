<?php

namespace App\Actions;

use App\Models\ExelExport;
use Illuminate\Support\Facades\Storage;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Maatwebsite\Excel\Facades\Excel;

class ExelExportAction extends Action
{
    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Выгрузить в Exel";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "save";

    /**
     * Execute the action when the user clicked on the button
     *
     * @param Array $selectedModels Array with all the id of the selected models
     * @param $view Current view where the action was executed from
     */
    public function handle($selectedModels, View $view)
    {
        $data = $view->getExportData();
        $export = [];
        $export[] = $data['headers'];
        $exel = new ExelExport(array_merge($export, $data['items']));
        //dump($exel);
        ob_clean();
        //flush();
        //dd($exel);
        $file = Excel::download($exel, 'users.xlsx');
        $file_name = 'users_'.date('Y_m_d_H_i_s').'.xlsx';
        //$content = file_get_contents($file->getFile()->getLinkTarget());
        $content = $file->getFile()->getContent();
        Storage::disk('public')->put($file_name, $content);
        //dd($file_name);
        //dump($file);
        $path = asset('storage/'.$file_name);
        //dump($path);
        //dd('ddd');
        //response()->file($path);
        //response()->away($path);
        return redirect()->away($path);
        //return;
        //return response()->file($file->getFile()->)
        $file->headers->add([
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Description' => 'File Transfer',
            'Content-Transfer-Encoding' => 'binary',
            //'Content-Length' => filesize
        ]);
        //dd($file);
        //dump($file);
        //response()->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $file->send();
        die();
        dd('OK');
        dd(response()->header()->file($exel->file));
        return $exel;

        //$this->success('Список выгружен!');
        return redirect()->route('export', $data);

        $file = Excel::download($data, 'users.xlsx');
        dd($file);
        response()->file($file->getFile());
        return $file;
        dd($file);
        return redirect()->route('export');
        dump(Excel::download($data, 'users.xlsx'));
        return Excel::download($data, 'users.xlsx')->getContent();
        /*
        $data = $view->getExportData();
        dd($data);

        /* @var $items \Illuminate\Pagination\LengthAwarePaginator /
        $items = $data['items'];
        dd($items);
        //dd($view->all());
        dd($selectedModels); //Тут массив айдишников
        // Your code here
        User::whereKey($selectedModels)->update([
            'type' => 'admin'
        ]);*/
    }

}
