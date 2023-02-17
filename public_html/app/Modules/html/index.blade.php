<?php
/**
 * @var $item array
 */

$tag = 'livewire:';
$block = $item['code'];
preg_match_all('#<'.$tag.'(.*?)\/>#is', $block, $matches);
//dump($matches);
$page_params = [];
$page_values = [];
if (isset($page) && isset($page->params)) {
    foreach ($page->params as $key => $p) {
        $page_params[] = '{'.$key.'}';
        $page_values[] = $p;
    }
}
$tag = null;
foreach ($matches[0] as $key => $value) {
    $lives = explode(' ', trim($matches[1][$key]));
    //dump($lives);
    $pars = [];
    foreach ($lives as $ind => $live) {
        if (!$ind) $tag = trim($live);
        else {
            $ps = explode('=', trim($live));
            //dd($page->params);
            if(!empty($page_params)) $pars[$ps[0]] = str_replace($page_params, $page_values, $ps[1]);
            else $pars[$ps[0]] = $ps[1];
        }
        //dump($pars);
    }
    $html = \Livewire\Livewire::mount($tag, $pars)->html();
    //dd($matches[1][$key]);
    //$html = \Livewire\Livewire::mount(trim($matches[1][$key], [':model' => 1]))->html();
    //$html = \Livewire\Livewire::mount('user-detail-view', ['model' => 1])->html();
    $block = str_replace($value, $html, $block);
}

echo $block;
//echo '<livewire:users-table-view />';
//echo '<livewire:user-detail-view :model="1" />';
//$html = \Livewire\Livewire::mount('users-table-view')->html();
//livewire('users-table-view')
?>
