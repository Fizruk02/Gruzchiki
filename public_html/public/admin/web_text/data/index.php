
    <?php
    include_once("resources/_phpparsite.php");
    res("_ass.php");

    $groups = arrayQuery('SELECT p.title name, p.id id_group FROM `web_text` t
                          JOIN web_pages p ON p.id=t.page_id
                          GROUP BY t.page_id
                          ORDER BY p.title', [], 1);

    //array_shift($groups, ['name' => '<b>ОСНОВНЫЕ СООБЩЕНИЯ</b>', 'id_group' => 0]);
    $groups = array_map(function ($it){
        $it['items']=arrayQuery('SELECT `id`, `name` FROM `web_text` WHERE page_id=?',
                                 [ $it['id_group'] ], 1);
        return $it;
    }, $groups);


    $layouts = arrayQuery('SELECT p.name, p.id id_group FROM `web_text` t
                          JOIN web_layouts p ON p.id=t.layout_id
                          GROUP BY t.layout_id
                          ORDER BY p.name', [], 1);

    //array_shift($groups, ['name' => '<b>ОСНОВНЫЕ СООБЩЕНИЯ</b>', 'id_group' => 0]);
    $layouts = array_map(function ($it){
        $it['name']='Шаблон '.$it['name'];

        $it['items']=arrayQuery('SELECT `id`, `name` FROM `web_text` WHERE layout_id=?',
            [ $it['id_group'] ], 1);
        $it['id_group']='template_'.$it['id_group'];
        return $it;
    }, $layouts);

    $groups=array_merge($groups, $layouts);

    $languages = arrayQuery("SELECT `iso`, `name`, `default` FROM `s_langs` ORDER BY `iso`");

    $lanCodes = '[{"ru":"\u0410\u0431\u0445\u0430\u0437\u0441\u043a\u0438\u0439","code":"ab"},{"ru":"\u0410\u0432\u0430\u0440\u0441\u043a\u0438\u0439","code":"av"},{"ru":"\u0410\u0432\u0435\u0441\u0442\u0438\u0439\u0441\u043a\u0438\u0439","code":"ae"},{"ru":"\u0410\u0437\u0435\u0440\u0431\u0430\u0439\u0434\u0436\u0430\u043d\u0441\u043a\u0438\u0439","code":"az"},{"ru":"\u0410\u0439\u043c\u0430\u0440\u0430","code":"ay"},{"ru":"\u0410\u043a\u0430\u043d","code":"ak"},{"ru":"\u0410\u043b\u0431\u0430\u043d\u0441\u043a\u0438\u0439","code":"sq"},{"ru":"\u0410\u043c\u0445\u0430\u0440\u0441\u043a\u0438\u0439","code":"am"},{"ru":"\u0410\u043d\u0433\u043b\u0438\u0439\u0441\u043a\u0438\u0439","code":"en"},{"ru":"\u0410\u0440\u0430\u0431\u0441\u043a\u0438\u0439","code":"ar"},{"ru":"\u0410\u0440\u043c\u044f\u043d\u0441\u043a\u0438\u0439","code":"hy"},{"ru":"\u0410\u0441\u0441\u0430\u043c\u0441\u043a\u0438\u0439","code":"as"},{"ru":"\u0410\u0444\u0430\u0440\u0441\u043a\u0438\u0439","code":"aa"},{"ru":"\u0410\u0444\u0440\u0438\u043a\u0430\u0430\u043d\u0441","code":"af"},{"ru":"\u0411\u0430\u043c\u0431\u0430\u0440\u0430","code":"bm"},{"ru":"\u0411\u0430\u0441\u043a\u0441\u043a\u0438\u0439","code":"eu"},{"ru":"\u0411\u0430\u0448\u043a\u0438\u0440\u0441\u043a\u0438\u0439","code":"ba"},{"ru":"\u0411\u0435\u043b\u043e\u0440\u0443\u0441\u0441\u043a\u0438\u0439","code":"be"},{"ru":"\u0411\u0435\u043d\u0433\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"bn"},{"ru":"\u0411\u0438\u0440\u043c\u0430\u043d\u0441\u043a\u0438\u0439","code":"my"},{"ru":"\u0411\u0438\u0441\u043b\u0430\u043c\u0430","code":"bi"},{"ru":"\u0411\u043e\u043b\u0433\u0430\u0440\u0441\u043a\u0438\u0439","code":"bg"},{"ru":"\u0411\u043e\u0441\u043d\u0438\u0439\u0441\u043a\u0438\u0439","code":"bs"},{"ru":"\u0411\u0440\u0435\u0442\u043e\u043d\u0441\u043a\u0438\u0439","code":"br"},{"ru":"\u0412\u0430\u043b\u043b\u0438\u0439\u0441\u043a\u0438\u0439","code":"cy"},{"ru":"\u0412\u0435\u043d\u0433\u0435\u0440\u0441\u043a\u0438\u0439","code":"hu"},{"ru":"\u0412\u0435\u043d\u0434\u0430","code":"ve"},{"ru":"\u0412\u043e\u043b\u0430\u043f\u044e\u043a","code":"vo"},{"ru":"\u0412\u043e\u043b\u043e\u0444","code":"wo"},{"ru":"\u0412\u044c\u0435\u0442\u043d\u0430\u043c\u0441\u043a\u0438\u0439","code":"vi"},{"ru":"\u0413\u0430\u043b\u0438\u0441\u0438\u0439\u0441\u043a\u0438\u0439","code":"gl"},{"ru":"\u0413\u0430\u043d\u0434\u0430","code":"lg"},{"ru":"\u0413\u0435\u0440\u0435\u0440\u043e","code":"hz"},{"ru":"\u0413\u0440\u0435\u043d\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439","code":"kl"},{"ru":"\u0413\u0440\u0435\u0447\u0435\u0441\u043a\u0438\u0439 (\u043d\u043e\u0432\u043e\u0433\u0440\u0435\u0447\u0435\u0441\u043a\u0438\u0439)","code":"el"},{"ru":"\u0413\u0440\u0443\u0437\u0438\u043d\u0441\u043a\u0438\u0439","code":"ka"},{"ru":"\u0413\u0443\u0430\u0440\u0430\u043d\u0438","code":"gn"},{"ru":"\u0413\u0443\u0434\u0436\u0430\u0440\u0430\u0442\u0438","code":"gu"},{"ru":"\u0413\u044d\u043b\u044c\u0441\u043a\u0438\u0439","code":"gd"},{"ru":"\u0414\u0430\u0442\u0441\u043a\u0438\u0439","code":"da"},{"ru":"\u0414\u0437\u043e\u043d\u0433-\u043a\u044d","code":"dz"},{"ru":"\u0414\u0438\u0432\u0435\u0445\u0438 (\u041c\u0430\u043b\u044c\u0434\u0438\u0432\u0441\u043a\u0438\u0439)","code":"dv"},{"ru":"\u0417\u0443\u043b\u0443","code":"zu"},{"ru":"\u0418\u0432\u0440\u0438\u0442","code":"he"},{"ru":"\u0418\u0433\u0431\u043e","code":"ig"},{"ru":"\u0418\u0434\u0438\u0448","code":"yi"},{"ru":"\u0418\u043d\u0434\u043e\u043d\u0435\u0437\u0438\u0439\u0441\u043a\u0438\u0439","code":"id"},{"ru":"\u0418\u043d\u0442\u0435\u0440\u043b\u0438\u043d\u0433\u0432\u0430","code":"ia"},{"ru":"\u0418\u043d\u0442\u0435\u0440\u043b\u0438\u043d\u0433\u0432\u0435","code":"ie"},{"ru":"\u0418\u043d\u0443\u043a\u0442\u0438\u0442\u0443\u0442","code":"iu"},{"ru":"\u0418\u043d\u0443\u043f\u0438\u0430\u043a","code":"ik"},{"ru":"\u0418\u0440\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439","code":"ga"},{"ru":"\u0418\u0441\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439","code":"is"},{"ru":"\u0418\u0441\u043f\u0430\u043d\u0441\u043a\u0438\u0439","code":"es"},{"ru":"\u0418\u0442\u0430\u043b\u044c\u044f\u043d\u0441\u043a\u0438\u0439","code":"it"},{"ru":"\u0419\u043e\u0440\u0443\u0431\u0430","code":"yo"},{"ru":"\u041a\u0430\u0437\u0430\u0445\u0441\u043a\u0438\u0439","code":"kk"},{"ru":"\u041a\u0430\u043d\u043d\u0430\u0434\u0430","code":"kn"},{"ru":"\u041a\u0430\u043d\u0443\u0440\u0438","code":"kr"},{"ru":"\u041a\u0430\u0442\u0430\u043b\u0430\u043d\u0441\u043a\u0438\u0439","code":"ca"},{"ru":"\u041a\u0430\u0448\u043c\u0438\u0440\u0438","code":"ks"},{"ru":"\u041a\u0435\u0447\u0443\u0430","code":"qu"},{"ru":"\u041a\u0438\u043a\u0443\u0439\u044e","code":"ki"},{"ru":"\u041a\u0438\u043d\u044c\u044f\u043c\u0430","code":"kj"},{"ru":"\u041a\u0438\u0440\u0433\u0438\u0437\u0441\u043a\u0438\u0439","code":"ky"},{"ru":"\u041a\u0438\u0442\u0430\u0439\u0441\u043a\u0438\u0439","code":"zh"},{"ru":"\u041a\u043b\u0438\u043d\u0433\u043e\u043d\u0441\u043a\u0438\u0439","code":"\u2013"},{"ru":"\u041a\u043e\u043c\u0438","code":"kv"},{"ru":"\u041a\u043e\u043d\u0433\u043e","code":"kg"},{"ru":"\u041a\u043e\u0440\u0435\u0439\u0441\u043a\u0438\u0439","code":"ko"},{"ru":"\u041a\u043e\u0440\u043d\u0441\u043a\u0438\u0439","code":"kw"},{"ru":"\u041a\u043e\u0440\u0441\u0438\u043a\u0430\u043d\u0441\u043a\u0438\u0439","code":"co"},{"ru":"\u041a\u043e\u0441\u0430","code":"xh"},{"ru":"\u041a\u0443\u0440\u0434\u0441\u043a\u0438\u0439","code":"ku"},{"ru":"\u041a\u0445\u043c\u0435\u0440\u0441\u043a\u0438\u0439","code":"km"},{"ru":"\u041b\u0430\u043e\u0441\u0441\u043a\u0438\u0439","code":"lo"},{"ru":"\u041b\u0430\u0442\u0438\u043d\u0441\u043a\u0438\u0439","code":"la"},{"ru":"\u041b\u0430\u0442\u044b\u0448\u0441\u043a\u0438\u0439","code":"lv"},{"ru":"\u041b\u0438\u043d\u0433\u0430\u043b\u0430","code":"ln"},{"ru":"\u041b\u0438\u0442\u043e\u0432\u0441\u043a\u0438\u0439","code":"lt"},{"ru":"\u041b\u0443\u0431\u0430-\u043a\u0430\u0442\u0430\u043d\u0433\u0430","code":"lu"},{"ru":"\u041b\u044e\u043a\u0441\u0435\u043c\u0431\u0443\u0440\u0433\u0441\u043a\u0438\u0439","code":"lb"},{"ru":"\u041c\u0430\u043a\u0435\u0434\u043e\u043d\u0441\u043a\u0438\u0439","code":"mk"},{"ru":"\u041c\u0430\u043b\u0430\u0433\u0430\u0441\u0438\u0439\u0441\u043a\u0438\u0439","code":"mg"},{"ru":"\u041c\u0430\u043b\u0430\u0439\u0441\u043a\u0438\u0439","code":"ms"},{"ru":"\u041c\u0430\u043b\u0430\u044f\u043b\u0430\u043c","code":"ml"},{"ru":"\u041c\u0430\u043b\u044c\u0442\u0438\u0439\u0441\u043a\u0438\u0439","code":"mt"},{"ru":"\u041c\u0430\u043e\u0440\u0438","code":"mi"},{"ru":"\u041c\u0430\u0440\u0430\u0442\u0445\u0438","code":"mr"},{"ru":"\u041c\u0430\u0440\u0448\u0430\u043b\u043b\u044c\u0441\u043a\u0438\u0439","code":"mh"},{"ru":"\u041c\u0435\u0440\u044f\u043d\u0441\u043a\u0438\u0439","code":"me"},{"ru":"\u041c\u043e\u043d\u0433\u043e\u043b\u044c\u0441\u043a\u0438\u0439","code":"mn"},{"ru":"\u041c\u044d\u043d\u0441\u043a\u0438\u0439 (\u041c\u044d\u043d\u043a\u0441\u043a\u0438\u0439)","code":"gv"},{"ru":"\u041d\u0430\u0432\u0430\u0445\u043e","code":"nv"},{"ru":"\u041d\u0430\u0443\u0440\u0443","code":"na"},{"ru":"\u041d\u0434\u0435\u0431\u0435\u043b\u0435 \u0441\u0435\u0432\u0435\u0440\u043d\u044b\u0439","code":"nd"},{"ru":"\u041d\u0434\u0435\u0431\u0435\u043b\u0435 \u044e\u0436\u043d\u044b\u0439","code":"nr"},{"ru":"\u041d\u0434\u0443\u043d\u0433\u0430","code":"ng"},{"ru":"\u041d\u0435\u043c\u0435\u0446\u043a\u0438\u0439","code":"de"},{"ru":"\u041d\u0435\u043f\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"ne"},{"ru":"\u041d\u0438\u0434\u0435\u0440\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439 (\u0413\u043e\u043b\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439)","code":"nl"},{"ru":"\u041d\u043e\u0440\u0432\u0435\u0436\u0441\u043a\u0438\u0439","code":"no"},{"ru":"\u041d\u044c\u044f\u043d\u0434\u0436\u0430","code":"ny"},{"ru":"\u041d\u044e\u043d\u043e\u0440\u0441\u043a (\u043d\u043e\u0432\u043e\u043d\u043e\u0440\u0432\u0435\u0436\u0441\u043a\u0438\u0439)","code":"nn"},{"ru":"\u041e\u0434\u0436\u0438\u0431\u0432\u0435","code":"oj"},{"ru":"\u041e\u043a\u0441\u0438\u0442\u0430\u043d\u0441\u043a\u0438\u0439","code":"oc"},{"ru":"\u041e\u0440\u0438\u044f","code":"or"},{"ru":"\u041e\u0440\u043e\u043c\u043e","code":"om"},{"ru":"\u041e\u0441\u0435\u0442\u0438\u043d\u0441\u043a\u0438\u0439","code":"os"},{"ru":"\u041f\u0430\u043b\u0438","code":"pi"},{"ru":"\u041f\u0435\u043d\u0434\u0436\u0430\u0431\u0441\u043a\u0438\u0439","code":"pa"},{"ru":"\u041f\u0435\u0440\u0441\u0438\u0434\u0441\u043a\u0438\u0439","code":"fa"},{"ru":"\u041f\u043e\u043b\u044c\u0441\u043a\u0438\u0439","code":"pl"},{"ru":"\u041f\u043e\u0440\u0442\u0443\u0433\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"pt"},{"ru":"\u041f\u0443\u0448\u0442\u0443","code":"ps"},{"ru":"\u0420\u0435\u0442\u043e\u0440\u043e\u043c\u0430\u043d\u0441\u043a\u0438\u0439","code":"rm"},{"ru":"\u0420\u0443\u0430\u043d\u0434\u0430","code":"rw"},{"ru":"\u0420\u0443\u043c\u044b\u043d\u0441\u043a\u0438\u0439","code":"ro"},{"ru":"\u0420\u0443\u043d\u0434\u0438","code":"rn"},{"ru":"\u0420\u0443\u0441\u0441\u043a\u0438\u0439","code":"ru"},{"ru":"\u0421\u0430\u043c\u043e\u0430\u043d\u0441\u043a\u0438\u0439","code":"sm"},{"ru":"\u0421\u0430\u043d\u0433\u043e","code":"sg"},{"ru":"\u0421\u0430\u043d\u0441\u043a\u0440\u0438\u0442","code":"sa"},{"ru":"\u0421\u0430\u0440\u0434\u0438\u043d\u0441\u043a\u0438\u0439","code":"sc"},{"ru":"\u0421\u0432\u0430\u0437\u0438","code":"ss"},{"ru":"\u0421\u0435\u0440\u0431\u0441\u043a\u0438\u0439","code":"sr"},{"ru":"\u0421\u0438\u043d\u0433\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"si"},{"ru":"\u0421\u0438\u043d\u0434\u0445\u0438","code":"sd"},{"ru":"\u0421\u043b\u043e\u0432\u0430\u0446\u043a\u0438\u0439","code":"sk"},{"ru":"\u0421\u043b\u043e\u0432\u0435\u043d\u0441\u043a\u0438\u0439","code":"sl"},{"ru":"\u0421\u043e\u043c\u0430\u043b\u0438","code":"so"},{"ru":"\u0421\u043e\u0442\u043e \u044e\u0436\u043d\u044b\u0439","code":"st"},{"ru":"\u0421\u0443\u0430\u0445\u0438\u043b\u0438","code":"sw"},{"ru":"\u0421\u0443\u043d\u0434\u0430\u043d\u0441\u043a\u0438\u0439","code":"su"},{"ru":"\u0422\u0430\u0433\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"tl"},{"ru":"\u0422\u0430\u0434\u0436\u0438\u043a\u0441\u043a\u0438\u0439","code":"tg"},{"ru":"\u0422\u0430\u0439\u0441\u043a\u0438\u0439","code":"th"},{"ru":"\u0422\u0430\u0438\u0442\u044f\u043d\u0441\u043a\u0438\u0439","code":"ty"},{"ru":"\u0422\u0430\u043c\u0438\u043b\u044c\u0441\u043a\u0438\u0439","code":"ta"},{"ru":"\u0422\u0430\u0442\u0430\u0440\u0441\u043a\u0438\u0439","code":"tt"},{"ru":"\u0422\u0432\u0438","code":"tw"},{"ru":"\u0422\u0435\u043b\u0443\u0433\u0443","code":"te"},{"ru":"\u0422\u0438\u0431\u0435\u0442\u0441\u043a\u0438\u0439","code":"bo"},{"ru":"\u0422\u0438\u0433\u0440\u0438\u043d\u044c\u044f","code":"ti"},{"ru":"\u0422\u043e\u043d\u0433\u0430\u043d\u0441\u043a\u0438\u0439","code":"to"},{"ru":"\u0422\u0441\u0432\u0430\u043d\u0430","code":"tn"},{"ru":"\u0422\u0441\u043e\u043d\u0433\u0430","code":"ts"},{"ru":"\u0422\u0443\u0440\u0435\u0446\u043a\u0438\u0439","code":"tr"},{"ru":"\u0422\u0443\u0440\u043a\u043c\u0435\u043d\u0441\u043a\u0438\u0439","code":"tk"},{"ru":"\u0423\u0437\u0431\u0435\u043a\u0441\u043a\u0438\u0439","code":"uz"},{"ru":"\u0423\u0439\u0433\u0443\u0440\u0441\u043a\u0438\u0439","code":"ug"},{"ru":"\u0423\u043a\u0440\u0430\u0438\u043d\u0441\u043a\u0438\u0439","code":"uk"},{"ru":"\u0423\u0440\u0434\u0443","code":"ur"},{"ru":"\u0424\u0430\u0440\u0435\u0440\u0441\u043a\u0438\u0439","code":"fo"},{"ru":"\u0424\u0438\u0434\u0436\u0438","code":"fj"},{"ru":"\u0424\u0438\u043b\u0438\u043f\u043f\u0438\u043d\u0441\u043a\u0438\u0439","code":"fl"},{"ru":"\u0424\u0438\u043d\u0441\u043a\u0438\u0439\u00a0(Suomi)","code":"fi"},{"ru":"\u0424\u0440\u0430\u043d\u0446\u0443\u0437\u0441\u043a\u0438\u0439","code":"fr"},{"ru":"\u0424\u0440\u0438\u0437\u0441\u043a\u0438\u0439","code":"fy"},{"ru":"\u0424\u0443\u043b\u0430\u0445","code":"ff"},{"ru":"\u0425\u0430\u0443\u0441\u0430","code":"ha"},{"ru":"\u0425\u0438\u043d\u0434\u0438","code":"hi"},{"ru":"\u0425\u0438\u0440\u0438\u043c\u043e\u0442\u0443","code":"ho"},{"ru":"\u0425\u043e\u0440\u0432\u0430\u0442\u0441\u043a\u0438\u0439","code":"hr"},{"ru":"\u0426\u0435\u0440\u043a\u043e\u0432\u043d\u043e\u0441\u043b\u0430\u0432\u044f\u043d\u0441\u043a\u0438\u0439\u00a0(\u0421\u0442\u0430\u0440\u043e\u0441\u043b\u0430\u0432\u044f\u043d\u0441\u043a\u0438\u0439)","code":"cu"},{"ru":"\u0427\u0430\u043c\u043e\u0440\u0440\u043e","code":"ch"},{"ru":"\u0427\u0435\u0447\u0435\u043d\u0441\u043a\u0438\u0439","code":"ce"},{"ru":"\u0427\u0435\u0448\u0441\u043a\u0438\u0439","code":"cs"},{"ru":"\u0427\u0436\u0443\u0430\u043d\u0441\u043a\u0438\u0439","code":"za"},{"ru":"\u0427\u0443\u0432\u0430\u0448\u0441\u043a\u0438\u0439","code":"cv"},{"ru":"\u0428\u0432\u0435\u0434\u0441\u043a\u0438\u0439","code":"sv"},{"ru":"\u0428\u043e\u043d\u0430","code":"sn"},{"ru":"\u042d\u0432\u0435","code":"ee"},{"ru":"\u042d\u0441\u043f\u0435\u0440\u0430\u043d\u0442\u043e","code":"eo"},{"ru":"\u042d\u0441\u0442\u043e\u043d\u0441\u043a\u0438\u0439","code":"et"},{"ru":"\u042f\u0432\u0430\u043d\u0441\u043a\u0438\u0439","code":"jv"},{"ru":"\u042f\u043f\u043e\u043d\u0441\u043a\u0438\u0439","code":"ja"}]';


    ?>
    <style>
        .text_item {
            cursor: pointer;
        }
        #modal-translate .title_name {
            padding-left: 10px;
            color:#0549af;
            cursor:pointer;
        }
    </style>

<div class="container">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="pills-messages-tab" data-bs-toggle="pill" href="#pills-messages" role="tab"
               aria-controls="pills-messages" aria-selected="true">Текст</a>
        </li>

    </ul>
    <div class="tab-content" id="pills-tabContent">

        <div class="tab-pane fade show active" id="pills-messages" role="tabpanel" aria-labelledby="pills-messages-tab">
            <div class="form-group">
                <div class="input-group mb-1">
                    <input type="text" class="form-control" placeholder="search..." id="search">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="func.search()">search</button>
                    </div>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="func.langs.show()">langs</button>
                    </div>
                </div>
                <div class="accordion" id="accordion">
                    <?php
                    foreach ($groups as $group) {?>
                        <div class="search-row accordion-item">
                            <h2 class="accordion-header">
                                <h5 class="mb-0">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse<?php echo $group['id_group'] ?>" aria-expanded="false"> <?= $group['name'] ?> </button>
                                </h5>
                            </h2>
                            <div id="collapse<?php echo $group['id_group'] ?>" class="collapse accordion-collapse" data-bs-parent="#accordion">
                                <div class="accordion-body">
                                    <ul class="list-group dialog-list">
                                        <?php foreach ($group['items'] as $row) {?>
                                            <li class="list-group-item text_item" data-id="<?php echo $row['id'] ?>" onclick="func.get(this.dataset.id)">
                                                <div class="name"><?= $row['name'] ?></div>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="modal-translate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="max-width:800px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">варианты перевода</h5>
                <h5 class="modal-title title_name" contenteditable="true" onblur="func.name()"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"> </div>
            <div>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-languages" tabindex="-2" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">языки</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="func.langs.add()">New</button>
            </div>
        </div>
    </div>
</div>
</div>

<script>

    var project={
        id:false,
        langs: <?php echo json_encode($languages) ?>,
        codes: <?php echo $lanCodes?>,
        templates:{
            translate:(lan)=>{
                return `
                    <div class="input-group mb-3 translate_item" data-iso="${lan.iso}">
                        <span class="input-group-text" style="width:45px;">${lan.iso}</span>
                        <textarea class="form-control" rows=4 placeholder="${lan.name}" onblur="func.save('${lan.iso}')"></textarea>
                    </div>`;
            },
            lan:(lan)=>{console.log(lan);
                    return `
                      <div class="input-group mb-1 lan_item" data-iso="${lan.iso}">
                          <div class="input-group-text">
                            <input class="form-check-input" type="radio" name="default" onclick="func.langs.default('${lan.iso}')" ${lan.default==="1"?"checked":""}>
                          </div>
                          <input type="text" class="form-control" placeholder="lan name" value="${lan.name}">
                          <button class="btn btn-outline-secondary" type="button" onclick="func.langs.delete(this.closest('.lan_item'))">delete</button>
                      </div>
                  `;
            }
        }

    }

    var func={
        get:(id)=>{
            project.id=id;
            qw.post("p.php?q=get", {id: id},(res)=> {
                let h="";
                res.langs.forEach((it)=>{ h+= project.templates.translate(it); });
                qw.qs("#modal-translate .modal-body").innerHTML=h;
                qw.qs("#modal-translate .title_name").innerHTML=res.item.name;
                res.data.forEach((it)=>{ qw.qs(`.translate_item[data-iso="${it.lan}"] textarea`).value=it.text; });
                qw.modal("#modal-translate").show();
            }, "json", "dialogue get");
        },
        save:(iso)=>{
            let t=qw.qs(`.translate_item[data-iso="${iso}"] textarea`).value;
            qw.post("p.php?q=save", {id:project.id,text:t, lan:iso}, ()=> {}, "json", "save");
        },
        name: (n)=>{
            let el=qw.qs("#modal-translate .title_name");
            let name =el.innerHTML
            qw.post("p.php?q=name", {id:project.id,name:name}, (r)=> {
                qw.qs(`.text_item[data-id="${project.id}"] .name`).innerHTML=name;
                el.innerHTML=name;
            }, "json", "editing the translation name");
        },
        search:()=>{
            let t=qw.qs("#search").value.toLowerCase(),s,gr={},gid;

            if(t===""){
                $('button.accordion-button[aria-expanded="true"]').click();
                qw.show(".text_item");
                return;
            }
            qw.qsa(".text_item").forEach((el)=>{
                s=el.querySelector(".name").innerHTML;
                gid=el.closest(".accordion-collapse").id;
                if(!gr[gid])gr[gid]=false;
                if (s.indexOf(t) === -1) el.style.display="none";
                 else {
                    el.style.display="block";
                    gr[gid]=true;
                }
            })
            let btn='',exp;
            for (id in gr){
                btn=qw.qs(`button[data-bs-target="#${id}"]`);
                exp=btn.getAttribute("aria-expanded")==="true";
                if(gr[id]&&!exp) qw.qs(`button[data-bs-target="#${id}"]`).click();
                if(!gr[id]&&exp) qw.qs(`button[data-bs-target="#${id}"]`).click();
            }
        },
        langs:{
            show:()=>{
                let h = '';
                project.langs.forEach(function (it, i) { h += project.templates.lan(it); });
                qw.qs("#modal-languages .modal-body").innerHTML=h;
                qw.modal("#modal-languages").show();
            },
            add:async ()=>{

                promptmodcreate({'title':'Выберите язык','btnOk':'Добавить','btnNo':'Отмена'},
                    [ {items:qw.arr.format(project.codes, "ru","code")} ]);
                let result = await promptmod; if(!result) return;
                let iso=result[0];
                let lan=qw.arr.get("code",iso,project.codes);
                qw.post("p.php?q=addLan", {iso: lan.code, name:lan.ru}, (r)=> {
                    project.langs.push(r.data);
                    qw.qs("#modal-languages .modal-body").innerHTML+=project.templates.lan(r.data.iso, r.data.name);
                }, "json", "language add");

            },
            default:(iso)=>{
                qw.post("p.php?q=defaultLan", {iso: iso}, ()=> {
                    project.langs.forEach((it)=>{
                        it.default=it.iso===iso?"1":"0";
                    })
                }, "json", "select the default language");
            },
            delete:async (el)=>{
                let iso=el.dataset.iso;
                alertmodcreate({'title':"Удалить "+iso+"?", 'btnOk':'Да','btnNo':'Отмена'});
                let result = await alertmod; if(!result) return;
                qw.post("p.php?q=deleteLan", {lan: iso}, ()=> {
                    el.remove();
                    qw.arr.rm('iso',iso,project.langs);
                }, "json", "language removal");
            }
        }
    }


    /*
    $(document).ready(function () {

        $('#button-search-message').on('click', function () {
            let text = $('#input-main-search-message').val().toLowerCase();

            let groupList = [];

            $('.dialog-list').children().each(function (i, item) {
                let desc = $(item).attr('search-desc').toLowerCase();
                let messages = $(item).attr('messages-for-search').toLowerCase();

                let str = ' ' + desc + ' ' + messages;
                if (str.indexOf(text) == -1)
                    $(item).attr('style', 'display:none !IMPORTANT');
                else {
                    $(item).show();
                    groupList.push($(item).data('group-id'));
                }


            });

            groupList = groupList.sort().reduce(function (a, b) {
                if (b != a[0]) a.unshift(b);
                return a
            }, [])

            $('.search-row').hide();


            if (text != '') {
                groupList.forEach(function (item, i) {
                    $("#collapse" + item).closest('.search-row').show();
                    $("#collapse" + item).addClass('show');
                });
            } else {
                $('.collapse').removeClass('show');
                $('.search-row').show();
            }


        });



*/
</script>
