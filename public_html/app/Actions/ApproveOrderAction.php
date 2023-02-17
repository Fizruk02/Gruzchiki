<?php

namespace App\Actions;

use App\Models\Bot;
use App\Models\OrdersUsers;
use App\Models\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelViews\Actions\Action;
use LaravelViews\Views\View;
use Livewire\WithPagination;
use LaravelViews\Actions\Confirmable;
use App\Models\User;

class ApproveOrderAction extends Action
{
    use Confirmable;

    /**
     * Any title you want to be displayed
     * @var String
     * */
    public $title = "Подтвердить";

    /**
     * This should be a valid Feather icon string
     * @var String
     */
    public $icon = "mail";

    /**
     * Execute the action when the user clicked on the button
     *
     * @param $model User object of the list where the user has clicked
     * @param $view Current view where the action was executed from
     */
    public function handle(Model $model, View $view)
    {
        $cabinet = \App\Models\Cabinet::curCabinet();
        if ($model->cabinet_id == $cabinet->id) {
            //if (Auth::user()->id_cms_privileges == Users::ROLE_DISPETCHER) {
                $text = 'На заказ #'.$model->number.' назначены'.PHP_EOL;
                $users = OrdersUsers::where('order_id', $model->id)->where('approved', 1)->get();
                foreach ($users as $user) {
                    $text .= $user->user->name;
                    if ($user->brigadier) $text .= ' ('.$user->user->phone.')';
                    $text .= PHP_EOL;
                }
                $bot = Bot::where('id', $model->bot_id)->first();
                $model->cabinet->users->sendMessage($text, $bot->bot_key);
                //dump($model->cabinet->users);
                //dd($bot->bot_key);
            //}
            DB::table('orders_users')->where('order_id', $model->id)->where('approved', 1)->update(['status' => 4, 'is_approved' => 0]);
            $users = OrdersUsers::where('order_id', $model->id)->where(['status' => 3])->get();
            foreach ($users as $user) {
                $user->user->sendMessage('Благодарим, за ваш отклик, но заказ забрали – ожидайте следующих');
            }
            $this->success('Подтверждения разосланы!');
        }
    }

    public function getConfirmationMessage($item = null)
    {
        return 'Желаете разослать подтверждения?';
    }
}
