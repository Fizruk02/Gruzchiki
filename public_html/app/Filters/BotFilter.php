<?php

namespace App\Filters;

use App\Models\Bot;
use App\Models\Cabinet;
use App\Models\Orders;
use App\Models\Users;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LaravelViews\Filters\Filter;

class BotFilter extends Filter
{
    public $title =  "Бот";

    /**
     * Modify the current query when the filter is used
     *
     * @param Builder $query Current query
     * @param $value Value selected by the user
     * @return Builder Query modified
     */
    public function apply(Builder $query, $value, $request)
    {
        $user_p = Auth::user()->id_cms_privileges;
        $cabinet = Cabinet::curCabinet();
        if ($user_p == Users::ROLE_DISPETCHER) {
            $ds = [];
            $dbs = DB::table('bot_dispetcher')->where('users_id', Auth::user()->id)->get();
            foreach ($dbs as $db) $ds[] = $db->bot_id;
            if (!in_array($value, $ds)) abort(404);
        }
        return $query->where('orders.bot_id', $value)->where('cabinet_id', $cabinet->id);
    }

    /**
     * Defines the title and value for each option
     *
     * @return Array associative array with the title and values
     */
    public function options()
    {
        $user_p = Auth::user()->id_cms_privileges;
        $cabinet = Cabinet::curCabinet();

        $bots = Bot::where('cabinet_id', $cabinet->id)->get();
        $ds = [];
        if ($user_p == Users::ROLE_DISPETCHER) {
            $dbs = DB::table('bot_dispetcher')->where('users_id', Auth::user()->id)->get();
            foreach ($dbs as $db) $ds[] = $db->bot_id;
        }

        $filters = [];
        foreach ($bots as $bot) {
            if ($user_p == Users::ROLE_DISPETCHER && !in_array($bot->id, $ds))
                continue;
            $count = Orders::where('bot_id', $bot->id)->count();
            $filters[$bot->name.' ('.$count.')'] = $bot->id;
        }
        return $filters;
    }
}
?>

