<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Models\WebPages;


class PagesController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function main(Request $request)
    {
        $webpage = WebPages::where('slug', '/')->firstOrFail();
        return $this->item($request, $webpage);
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WebPages  $webpage
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function item(Request $request, WebPages $webpage = null)
    {
        $role_id = Auth::guest() ? 0 : Auth::user()->role_id;
        if ($webpage) {
            if (!$webpage->isAccess($role_id))
                throw new AccessDeniedHttpException('Доступ запрещен');

            return view($webpage->layout->name, [
                'page' => $webpage,
            ]);
        }
        throw new NotFoundHttpException('Файл не найден');
    }

    public function getBlocks() {
        return [
            'blocks' => [],
            'css' => '',
        ];
    }
}
