<?php
/* @var $page \app\models\WebPages */
/* @var $code string */

$sections_code = null;
foreach($page->layout->sections as $section) {
    if($section->section_tpl == 'content') {
        $code = null;
        foreach($page->sections as $section) {
            $code .= $section->render();
        }
        $sections_code .= $code;
    } else {
        $sections_code .= $section->render();
    }
}

$sections_code = \App\Models\Module::translate($sections_code, $page);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>{{$page->title}}</title>
    <meta name="description" content="{{strip_tags($page->description)}}">
    <meta name="viewport" content="width=device-width,height=device-height,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="<?php echo $favicon??'' ?>">
    <meta property="og:image" content="{{$page->photo}}">
    <meta property='og:description' content="{{strip_tags($page->ogdescr)}}"/>
    <meta property='og:title' content="{{strip_tags($page->ogtitle)}}"/>
    <meta property='og:url' content="https://{{$_SERVER['HTTP_HOST']}}"/>
    <meta property='og:type' content="website"/>
    <meta name="theme-color" content="#ffff"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" href="https://{{$page->favicon}}">
    @push('scripts')
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    @endpush
    @stack('css')
    {{--@vite(['resources/css/main.css', 'resources/js/app.js'])--}}
    <script src="/build/assets/app.js"></script>
    <?php
    //$asset->regJs('https://b2bot.ru/components/qw.js');
    //$asset->regJs('/templates/layouts/default/js/script.js');
    //$asset->regJs('https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js', null, 'jquery');
    //$asset->regJs(Bt::getAlias('@templates/sections/marketcategorypage/jquery.loupe.min.js', ['query']));
    ?>
    @laravelViewsStyles
</head>
<body @if(!empty($user))class="class-for-edit" @endif @if($page->id)data-case="{{$page->id}}"@endif>
{!!$sections_code!!}
@stack('scripts')
@laravelViewsScripts
</body>
</html>
