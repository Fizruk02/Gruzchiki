
    <?php
    include_once("resources/_phpparsite.php");
    res("_ass.php");

    $groups = arrayQuery('SELECT id AS id_group, `name`, `type`
                            FROM `dialogue_group` 
                            WHERE  id IN(SELECT id_group FROM `dialogue`)
                            ORDER BY `type`, `name`');
    array_unshift($groups, ['name' => 'ОСНОВНЫЕ СООБЩЕНИЯ', 'id_group' => 0]);
    $groups = array_map(function ($it){
        $it['items']=arrayQuery("SELECT d.id, d.t_sort, d.description, d.name dial_name,
                    (SELECT GROUP_CONCAT(body) FROM `dialogue_translate` WHERE id_dial = d.id) messages_for_search, 
                    (SELECT COUNT(*) FROM dialogue_translate WHERE id_dial = d.id) t_count
                    FROM `dialogue` d
                    WHERE d.id_group = :groupId
                    ORDER BY d.t_sort", [':groupId' => $it['id_group']], true);
        return $it;
    }, $groups);
    
    $languages = arrayQuery("SELECT `id` id_lan, `iso`, `name` FROM `s_langs` ORDER BY `iso`");

    $lanCodes = '[{"code":"","ru":"выбрать..."},{"ru":"\u0410\u0431\u0445\u0430\u0437\u0441\u043a\u0438\u0439","code":"ab"},{"ru":"\u0410\u0432\u0430\u0440\u0441\u043a\u0438\u0439","code":"av"},{"ru":"\u0410\u0432\u0435\u0441\u0442\u0438\u0439\u0441\u043a\u0438\u0439","code":"ae"},{"ru":"\u0410\u0437\u0435\u0440\u0431\u0430\u0439\u0434\u0436\u0430\u043d\u0441\u043a\u0438\u0439","code":"az"},{"ru":"\u0410\u0439\u043c\u0430\u0440\u0430","code":"ay"},{"ru":"\u0410\u043a\u0430\u043d","code":"ak"},{"ru":"\u0410\u043b\u0431\u0430\u043d\u0441\u043a\u0438\u0439","code":"sq"},{"ru":"\u0410\u043c\u0445\u0430\u0440\u0441\u043a\u0438\u0439","code":"am"},{"ru":"\u0410\u043d\u0433\u043b\u0438\u0439\u0441\u043a\u0438\u0439","code":"en"},{"ru":"\u0410\u0440\u0430\u0431\u0441\u043a\u0438\u0439","code":"ar"},{"ru":"\u0410\u0440\u043c\u044f\u043d\u0441\u043a\u0438\u0439","code":"hy"},{"ru":"\u0410\u0441\u0441\u0430\u043c\u0441\u043a\u0438\u0439","code":"as"},{"ru":"\u0410\u0444\u0430\u0440\u0441\u043a\u0438\u0439","code":"aa"},{"ru":"\u0410\u0444\u0440\u0438\u043a\u0430\u0430\u043d\u0441","code":"af"},{"ru":"\u0411\u0430\u043c\u0431\u0430\u0440\u0430","code":"bm"},{"ru":"\u0411\u0430\u0441\u043a\u0441\u043a\u0438\u0439","code":"eu"},{"ru":"\u0411\u0430\u0448\u043a\u0438\u0440\u0441\u043a\u0438\u0439","code":"ba"},{"ru":"\u0411\u0435\u043b\u043e\u0440\u0443\u0441\u0441\u043a\u0438\u0439","code":"be"},{"ru":"\u0411\u0435\u043d\u0433\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"bn"},{"ru":"\u0411\u0438\u0440\u043c\u0430\u043d\u0441\u043a\u0438\u0439","code":"my"},{"ru":"\u0411\u0438\u0441\u043b\u0430\u043c\u0430","code":"bi"},{"ru":"\u0411\u043e\u043b\u0433\u0430\u0440\u0441\u043a\u0438\u0439","code":"bg"},{"ru":"\u0411\u043e\u0441\u043d\u0438\u0439\u0441\u043a\u0438\u0439","code":"bs"},{"ru":"\u0411\u0440\u0435\u0442\u043e\u043d\u0441\u043a\u0438\u0439","code":"br"},{"ru":"\u0412\u0430\u043b\u043b\u0438\u0439\u0441\u043a\u0438\u0439","code":"cy"},{"ru":"\u0412\u0435\u043d\u0433\u0435\u0440\u0441\u043a\u0438\u0439","code":"hu"},{"ru":"\u0412\u0435\u043d\u0434\u0430","code":"ve"},{"ru":"\u0412\u043e\u043b\u0430\u043f\u044e\u043a","code":"vo"},{"ru":"\u0412\u043e\u043b\u043e\u0444","code":"wo"},{"ru":"\u0412\u044c\u0435\u0442\u043d\u0430\u043c\u0441\u043a\u0438\u0439","code":"vi"},{"ru":"\u0413\u0430\u043b\u0438\u0441\u0438\u0439\u0441\u043a\u0438\u0439","code":"gl"},{"ru":"\u0413\u0430\u043d\u0434\u0430","code":"lg"},{"ru":"\u0413\u0435\u0440\u0435\u0440\u043e","code":"hz"},{"ru":"\u0413\u0440\u0435\u043d\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439","code":"kl"},{"ru":"\u0413\u0440\u0435\u0447\u0435\u0441\u043a\u0438\u0439 (\u043d\u043e\u0432\u043e\u0433\u0440\u0435\u0447\u0435\u0441\u043a\u0438\u0439)","code":"el"},{"ru":"\u0413\u0440\u0443\u0437\u0438\u043d\u0441\u043a\u0438\u0439","code":"ka"},{"ru":"\u0413\u0443\u0430\u0440\u0430\u043d\u0438","code":"gn"},{"ru":"\u0413\u0443\u0434\u0436\u0430\u0440\u0430\u0442\u0438","code":"gu"},{"ru":"\u0413\u044d\u043b\u044c\u0441\u043a\u0438\u0439","code":"gd"},{"ru":"\u0414\u0430\u0442\u0441\u043a\u0438\u0439","code":"da"},{"ru":"\u0414\u0437\u043e\u043d\u0433-\u043a\u044d","code":"dz"},{"ru":"\u0414\u0438\u0432\u0435\u0445\u0438 (\u041c\u0430\u043b\u044c\u0434\u0438\u0432\u0441\u043a\u0438\u0439)","code":"dv"},{"ru":"\u0417\u0443\u043b\u0443","code":"zu"},{"ru":"\u0418\u0432\u0440\u0438\u0442","code":"he"},{"ru":"\u0418\u0433\u0431\u043e","code":"ig"},{"ru":"\u0418\u0434\u0438\u0448","code":"yi"},{"ru":"\u0418\u043d\u0434\u043e\u043d\u0435\u0437\u0438\u0439\u0441\u043a\u0438\u0439","code":"id"},{"ru":"\u0418\u043d\u0442\u0435\u0440\u043b\u0438\u043d\u0433\u0432\u0430","code":"ia"},{"ru":"\u0418\u043d\u0442\u0435\u0440\u043b\u0438\u043d\u0433\u0432\u0435","code":"ie"},{"ru":"\u0418\u043d\u0443\u043a\u0442\u0438\u0442\u0443\u0442","code":"iu"},{"ru":"\u0418\u043d\u0443\u043f\u0438\u0430\u043a","code":"ik"},{"ru":"\u0418\u0440\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439","code":"ga"},{"ru":"\u0418\u0441\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439","code":"is"},{"ru":"\u0418\u0441\u043f\u0430\u043d\u0441\u043a\u0438\u0439","code":"es"},{"ru":"\u0418\u0442\u0430\u043b\u044c\u044f\u043d\u0441\u043a\u0438\u0439","code":"it"},{"ru":"\u0419\u043e\u0440\u0443\u0431\u0430","code":"yo"},{"ru":"\u041a\u0430\u0437\u0430\u0445\u0441\u043a\u0438\u0439","code":"kk"},{"ru":"\u041a\u0430\u043d\u043d\u0430\u0434\u0430","code":"kn"},{"ru":"\u041a\u0430\u043d\u0443\u0440\u0438","code":"kr"},{"ru":"\u041a\u0430\u0442\u0430\u043b\u0430\u043d\u0441\u043a\u0438\u0439","code":"ca"},{"ru":"\u041a\u0430\u0448\u043c\u0438\u0440\u0438","code":"ks"},{"ru":"\u041a\u0435\u0447\u0443\u0430","code":"qu"},{"ru":"\u041a\u0438\u043a\u0443\u0439\u044e","code":"ki"},{"ru":"\u041a\u0438\u043d\u044c\u044f\u043c\u0430","code":"kj"},{"ru":"\u041a\u0438\u0440\u0433\u0438\u0437\u0441\u043a\u0438\u0439","code":"ky"},{"ru":"\u041a\u0438\u0442\u0430\u0439\u0441\u043a\u0438\u0439","code":"zh"},{"ru":"\u041a\u043b\u0438\u043d\u0433\u043e\u043d\u0441\u043a\u0438\u0439","code":"\u2013"},{"ru":"\u041a\u043e\u043c\u0438","code":"kv"},{"ru":"\u041a\u043e\u043d\u0433\u043e","code":"kg"},{"ru":"\u041a\u043e\u0440\u0435\u0439\u0441\u043a\u0438\u0439","code":"ko"},{"ru":"\u041a\u043e\u0440\u043d\u0441\u043a\u0438\u0439","code":"kw"},{"ru":"\u041a\u043e\u0440\u0441\u0438\u043a\u0430\u043d\u0441\u043a\u0438\u0439","code":"co"},{"ru":"\u041a\u043e\u0441\u0430","code":"xh"},{"ru":"\u041a\u0443\u0440\u0434\u0441\u043a\u0438\u0439","code":"ku"},{"ru":"\u041a\u0445\u043c\u0435\u0440\u0441\u043a\u0438\u0439","code":"km"},{"ru":"\u041b\u0430\u043e\u0441\u0441\u043a\u0438\u0439","code":"lo"},{"ru":"\u041b\u0430\u0442\u0438\u043d\u0441\u043a\u0438\u0439","code":"la"},{"ru":"\u041b\u0430\u0442\u044b\u0448\u0441\u043a\u0438\u0439","code":"lv"},{"ru":"\u041b\u0438\u043d\u0433\u0430\u043b\u0430","code":"ln"},{"ru":"\u041b\u0438\u0442\u043e\u0432\u0441\u043a\u0438\u0439","code":"lt"},{"ru":"\u041b\u0443\u0431\u0430-\u043a\u0430\u0442\u0430\u043d\u0433\u0430","code":"lu"},{"ru":"\u041b\u044e\u043a\u0441\u0435\u043c\u0431\u0443\u0440\u0433\u0441\u043a\u0438\u0439","code":"lb"},{"ru":"\u041c\u0430\u043a\u0435\u0434\u043e\u043d\u0441\u043a\u0438\u0439","code":"mk"},{"ru":"\u041c\u0430\u043b\u0430\u0433\u0430\u0441\u0438\u0439\u0441\u043a\u0438\u0439","code":"mg"},{"ru":"\u041c\u0430\u043b\u0430\u0439\u0441\u043a\u0438\u0439","code":"ms"},{"ru":"\u041c\u0430\u043b\u0430\u044f\u043b\u0430\u043c","code":"ml"},{"ru":"\u041c\u0430\u043b\u044c\u0442\u0438\u0439\u0441\u043a\u0438\u0439","code":"mt"},{"ru":"\u041c\u0430\u043e\u0440\u0438","code":"mi"},{"ru":"\u041c\u0430\u0440\u0430\u0442\u0445\u0438","code":"mr"},{"ru":"\u041c\u0430\u0440\u0448\u0430\u043b\u043b\u044c\u0441\u043a\u0438\u0439","code":"mh"},{"ru":"\u041c\u0435\u0440\u044f\u043d\u0441\u043a\u0438\u0439","code":"me"},{"ru":"\u041c\u043e\u043d\u0433\u043e\u043b\u044c\u0441\u043a\u0438\u0439","code":"mn"},{"ru":"\u041c\u044d\u043d\u0441\u043a\u0438\u0439 (\u041c\u044d\u043d\u043a\u0441\u043a\u0438\u0439)","code":"gv"},{"ru":"\u041d\u0430\u0432\u0430\u0445\u043e","code":"nv"},{"ru":"\u041d\u0430\u0443\u0440\u0443","code":"na"},{"ru":"\u041d\u0434\u0435\u0431\u0435\u043b\u0435 \u0441\u0435\u0432\u0435\u0440\u043d\u044b\u0439","code":"nd"},{"ru":"\u041d\u0434\u0435\u0431\u0435\u043b\u0435 \u044e\u0436\u043d\u044b\u0439","code":"nr"},{"ru":"\u041d\u0434\u0443\u043d\u0433\u0430","code":"ng"},{"ru":"\u041d\u0435\u043c\u0435\u0446\u043a\u0438\u0439","code":"de"},{"ru":"\u041d\u0435\u043f\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"ne"},{"ru":"\u041d\u0438\u0434\u0435\u0440\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439 (\u0413\u043e\u043b\u043b\u0430\u043d\u0434\u0441\u043a\u0438\u0439)","code":"nl"},{"ru":"\u041d\u043e\u0440\u0432\u0435\u0436\u0441\u043a\u0438\u0439","code":"no"},{"ru":"\u041d\u044c\u044f\u043d\u0434\u0436\u0430","code":"ny"},{"ru":"\u041d\u044e\u043d\u043e\u0440\u0441\u043a (\u043d\u043e\u0432\u043e\u043d\u043e\u0440\u0432\u0435\u0436\u0441\u043a\u0438\u0439)","code":"nn"},{"ru":"\u041e\u0434\u0436\u0438\u0431\u0432\u0435","code":"oj"},{"ru":"\u041e\u043a\u0441\u0438\u0442\u0430\u043d\u0441\u043a\u0438\u0439","code":"oc"},{"ru":"\u041e\u0440\u0438\u044f","code":"or"},{"ru":"\u041e\u0440\u043e\u043c\u043e","code":"om"},{"ru":"\u041e\u0441\u0435\u0442\u0438\u043d\u0441\u043a\u0438\u0439","code":"os"},{"ru":"\u041f\u0430\u043b\u0438","code":"pi"},{"ru":"\u041f\u0435\u043d\u0434\u0436\u0430\u0431\u0441\u043a\u0438\u0439","code":"pa"},{"ru":"\u041f\u0435\u0440\u0441\u0438\u0434\u0441\u043a\u0438\u0439","code":"fa"},{"ru":"\u041f\u043e\u043b\u044c\u0441\u043a\u0438\u0439","code":"pl"},{"ru":"\u041f\u043e\u0440\u0442\u0443\u0433\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"pt"},{"ru":"\u041f\u0443\u0448\u0442\u0443","code":"ps"},{"ru":"\u0420\u0435\u0442\u043e\u0440\u043e\u043c\u0430\u043d\u0441\u043a\u0438\u0439","code":"rm"},{"ru":"\u0420\u0443\u0430\u043d\u0434\u0430","code":"rw"},{"ru":"\u0420\u0443\u043c\u044b\u043d\u0441\u043a\u0438\u0439","code":"ro"},{"ru":"\u0420\u0443\u043d\u0434\u0438","code":"rn"},{"ru":"\u0420\u0443\u0441\u0441\u043a\u0438\u0439","code":"ru"},{"ru":"\u0421\u0430\u043c\u043e\u0430\u043d\u0441\u043a\u0438\u0439","code":"sm"},{"ru":"\u0421\u0430\u043d\u0433\u043e","code":"sg"},{"ru":"\u0421\u0430\u043d\u0441\u043a\u0440\u0438\u0442","code":"sa"},{"ru":"\u0421\u0430\u0440\u0434\u0438\u043d\u0441\u043a\u0438\u0439","code":"sc"},{"ru":"\u0421\u0432\u0430\u0437\u0438","code":"ss"},{"ru":"\u0421\u0435\u0440\u0431\u0441\u043a\u0438\u0439","code":"sr"},{"ru":"\u0421\u0438\u043d\u0433\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"si"},{"ru":"\u0421\u0438\u043d\u0434\u0445\u0438","code":"sd"},{"ru":"\u0421\u043b\u043e\u0432\u0430\u0446\u043a\u0438\u0439","code":"sk"},{"ru":"\u0421\u043b\u043e\u0432\u0435\u043d\u0441\u043a\u0438\u0439","code":"sl"},{"ru":"\u0421\u043e\u043c\u0430\u043b\u0438","code":"so"},{"ru":"\u0421\u043e\u0442\u043e \u044e\u0436\u043d\u044b\u0439","code":"st"},{"ru":"\u0421\u0443\u0430\u0445\u0438\u043b\u0438","code":"sw"},{"ru":"\u0421\u0443\u043d\u0434\u0430\u043d\u0441\u043a\u0438\u0439","code":"su"},{"ru":"\u0422\u0430\u0433\u0430\u043b\u044c\u0441\u043a\u0438\u0439","code":"tl"},{"ru":"\u0422\u0430\u0434\u0436\u0438\u043a\u0441\u043a\u0438\u0439","code":"tg"},{"ru":"\u0422\u0430\u0439\u0441\u043a\u0438\u0439","code":"th"},{"ru":"\u0422\u0430\u0438\u0442\u044f\u043d\u0441\u043a\u0438\u0439","code":"ty"},{"ru":"\u0422\u0430\u043c\u0438\u043b\u044c\u0441\u043a\u0438\u0439","code":"ta"},{"ru":"\u0422\u0430\u0442\u0430\u0440\u0441\u043a\u0438\u0439","code":"tt"},{"ru":"\u0422\u0432\u0438","code":"tw"},{"ru":"\u0422\u0435\u043b\u0443\u0433\u0443","code":"te"},{"ru":"\u0422\u0438\u0431\u0435\u0442\u0441\u043a\u0438\u0439","code":"bo"},{"ru":"\u0422\u0438\u0433\u0440\u0438\u043d\u044c\u044f","code":"ti"},{"ru":"\u0422\u043e\u043d\u0433\u0430\u043d\u0441\u043a\u0438\u0439","code":"to"},{"ru":"\u0422\u0441\u0432\u0430\u043d\u0430","code":"tn"},{"ru":"\u0422\u0441\u043e\u043d\u0433\u0430","code":"ts"},{"ru":"\u0422\u0443\u0440\u0435\u0446\u043a\u0438\u0439","code":"tr"},{"ru":"\u0422\u0443\u0440\u043a\u043c\u0435\u043d\u0441\u043a\u0438\u0439","code":"tk"},{"ru":"\u0423\u0437\u0431\u0435\u043a\u0441\u043a\u0438\u0439","code":"uz"},{"ru":"\u0423\u0439\u0433\u0443\u0440\u0441\u043a\u0438\u0439","code":"ug"},{"ru":"\u0423\u043a\u0440\u0430\u0438\u043d\u0441\u043a\u0438\u0439","code":"uk"},{"ru":"\u0423\u0440\u0434\u0443","code":"ur"},{"ru":"\u0424\u0430\u0440\u0435\u0440\u0441\u043a\u0438\u0439","code":"fo"},{"ru":"\u0424\u0438\u0434\u0436\u0438","code":"fj"},{"ru":"\u0424\u0438\u043b\u0438\u043f\u043f\u0438\u043d\u0441\u043a\u0438\u0439","code":"fl"},{"ru":"\u0424\u0438\u043d\u0441\u043a\u0438\u0439\u00a0(Suomi)","code":"fi"},{"ru":"\u0424\u0440\u0430\u043d\u0446\u0443\u0437\u0441\u043a\u0438\u0439","code":"fr"},{"ru":"\u0424\u0440\u0438\u0437\u0441\u043a\u0438\u0439","code":"fy"},{"ru":"\u0424\u0443\u043b\u0430\u0445","code":"ff"},{"ru":"\u0425\u0430\u0443\u0441\u0430","code":"ha"},{"ru":"\u0425\u0438\u043d\u0434\u0438","code":"hi"},{"ru":"\u0425\u0438\u0440\u0438\u043c\u043e\u0442\u0443","code":"ho"},{"ru":"\u0425\u043e\u0440\u0432\u0430\u0442\u0441\u043a\u0438\u0439","code":"hr"},{"ru":"\u0426\u0435\u0440\u043a\u043e\u0432\u043d\u043e\u0441\u043b\u0430\u0432\u044f\u043d\u0441\u043a\u0438\u0439\u00a0(\u0421\u0442\u0430\u0440\u043e\u0441\u043b\u0430\u0432\u044f\u043d\u0441\u043a\u0438\u0439)","code":"cu"},{"ru":"\u0427\u0430\u043c\u043e\u0440\u0440\u043e","code":"ch"},{"ru":"\u0427\u0435\u0447\u0435\u043d\u0441\u043a\u0438\u0439","code":"ce"},{"ru":"\u0427\u0435\u0448\u0441\u043a\u0438\u0439","code":"cs"},{"ru":"\u0427\u0436\u0443\u0430\u043d\u0441\u043a\u0438\u0439","code":"za"},{"ru":"\u0427\u0443\u0432\u0430\u0448\u0441\u043a\u0438\u0439","code":"cv"},{"ru":"\u0428\u0432\u0435\u0434\u0441\u043a\u0438\u0439","code":"sv"},{"ru":"\u0428\u043e\u043d\u0430","code":"sn"},{"ru":"\u042d\u0432\u0435","code":"ee"},{"ru":"\u042d\u0441\u043f\u0435\u0440\u0430\u043d\u0442\u043e","code":"eo"},{"ru":"\u042d\u0441\u0442\u043e\u043d\u0441\u043a\u0438\u0439","code":"et"},{"ru":"\u042f\u0432\u0430\u043d\u0441\u043a\u0438\u0439","code":"jv"},{"ru":"\u042f\u043f\u043e\u043d\u0441\u043a\u0438\u0439","code":"ja"}]';


    ?>
    <style>
        dn {
            display: none !IMPORTANT;
        }

        [kbpanel] {
            max-width: 600px;
            border: 1px solid aliceblue;
            margin: 1px;
        }

        [kbpanel]:hover {
            background: aliceblue;
            cursor: move;
        }

        [kbrow] {
            height: 74px;
        }
        
    </style>

<div class="container">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="pills-messages-tab" data-bs-toggle="pill" href="#pills-messages" role="tab"
               aria-controls="pills-messages" aria-selected="true">Сообщения</a>
        </li>

        <li class="nav-item" role="presentation">
            <a class="nav-link" id="pills-keyboards-tab" data-bs-toggle="pill" href="#pills-keyboards" role="tab"
               aria-controls="pills-keyboards" aria-selected="false">Клавиатуры</a>
        </li>
        <?php if ($GLOBALS['permission_to_use']['user_status'] == 99) { ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="pills-translate-tab" data-bs-toggle="pill" href="#pills-translate" role="tab"
                   aria-controls="pills-translate" aria-selected="false">Перевод</a>
            </li>
        <?php } ?>

    </ul>
    <div class="tab-content" id="pills-tabContent">


        <div class="tab-pane fade" id="pills-keyboards" role="tabpanel" aria-labelledby="pills-keyboards-tab">
            <div class="row">
                <div class="col-auto border-end">
                    <div class="list-group">
                        <?php $kbarr = arrayQuery('SELECT name, techname, buttons, resize_keyboard FROM `s_keyboards` WHERE t_type = "keyboard" ORDER BY name');
                        foreach ($kbarr as $kb) {
                            ?>
                            <button type="button" class="list-group-item list-group-item-action"
                                    keyboard=<?= $kb['techname'] ?> onclick="kbselect('<?= $kb['techname'] ?>')"><?= $kb['name'] ?></button>
                        <?php } ?>
                    </div>
                </div>

                <div class="col-sm">
                    <div class="border rounded shadow p-3 mb-3" id="kbcontainer" style="max-width: 1000px;">


                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-5" onclick="newkey()"><i
                                    class="bi bi-plus"></i>Button
                        </button>
                    </div>

                </div>

            </div>
        </div>



        <div class="tab-pane fade show active" id="pills-messages" role="tabpanel" aria-labelledby="pills-messages-tab">
            <div class="form-group">
                <div class="input-group mb-1">
                    <input type="text" class="form-control" placeholder="search message..." aria-label="" aria-describedby="button-main-search-message" id="input-main-search-message">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="open-groups">groups</button>
                    </div>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="open-languages">lan</button>
                    </div>
                </div>
                <div class="accordion" id="accordion">
                    <?php
                    $pr=0;
                    foreach ($groups as $group) {
                        $groupId = $group['id_group'];
                        $groupType = (int) $group['type'];
                        $divider=false;
                        if(!$pr&&$pr!==$groupType) $divider=true;
                        $pr=$groupType;
                        if($divider) echo '<hr><i class="ms-1">системные сообщения</i>';
                        ?>
                        <div class="search-row accordion-item <?php echo $divider? 'border-top':''; ?>">
                            <h2 class="accordion-header" id="heading<?= $groupId ?>">
                                <h5 class="mb-0">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse<?= $groupId ?>" aria-expanded="false"
                                            aria-controls="collapse<?= $groupId ?>">
                                        <?php
                                            echo $groupType===0 ? '<b>'.$group['name'].'</b>' : $group['name'];
                                        ?>
                                    </button>
                                </h5>
                            </h2>
                            <div id="collapse<?= $groupId ?>" class="collapse accordion-collapse"
                                 aria-labelledby="heading<?= $groupId ?>" data-bs-parent="#accordion">
                                <div class="accordion-body">
                                    <ul class="list-group dialog-list" id="dialog-list-<?= $groupId ?>">
                                        <?php

                                        foreach ($group['items'] as $row) {
                                            $d = $row['id'];
                                            ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center mb-0"
                                                search-desc="<?= $row['description'] ?>"
                                                messages-for-search="<?= $row['messages_for_search'] . ',' . $d . ',' . $row['dial_name'] ?>"
                                                style="cursor: pointer;" data-name="<?= $row['description'] ?>"
                                                data-rid="<?= $d ?>" data-group-id="<?= $groupId ?>"
                                                onclick="dial.open(this, '<?= $d ?>')">
                                                <div>
                                                    <div data-type="name" id="dialog-name-from-list-<?= $d ?>"
                                                         style="display: inline-block;"><?= $row['description'] ?></div>
                                                </div>
                                                <span class="badge badge-primary badge-pill"><?= $row['t_count'] ?></span>
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
        <div class="tab-pane fade" id="pills-translate" role="tabpanel" aria-labelledby="pills-translate-tab">
            <div class="row">
                <div class="col-sm">
                    <textarea class="form-control" aria-label="" rows="1" style="display:none"
                              id="translate-source-area"></textarea>
                    <div class="input-group mb-3" style="width: fit-content;">
                        <div class="input-group-prepend">
                            <label class="input-group-text" for="selectTranslateLanFromArea">выберите язык</label>
                        </div>
                        <select class="custom-select" id="selectTranslateLanFromArea">
                            <?php $lanCodesJsonDec = json_decode($lanCodes, true);
                            foreach ($lanCodesJsonDec as $tLanCode) {
                                echo "<option value='{$tLanCode['code']}'>{$tLanCode['ru']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <span>Нажмите на кнопку "Создать ссылку", скопируйте текст перевода, добавьте его в поле и нажмите "Добавить перевод в базу"</span><br>
                    <button class="btn btn-outline-success mb-1" type="button" id="get_the_text_in_the_parent_field">
                        Создать ссылку
                    </button>
                    <br>
                    <div class="mb-3" id="link-area"></div>
                    <span>Скопируйте переведенный текст из яндекс переводчика, добавьте его в поле и нажмите "Добавить перевод в базу"</span><br>
                    <button class="btn btn-outline-success mb-3" type="button" id="send_translate_from_area">Добавить
                        перевод в базу
                    </button>
                    <br>
                </div>
            </div>
            <div class="row">
                <div class="col-sm">
                    <textarea class="form-control" aria-label="" rows="30" id="translate-area"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-translate" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel"
     aria-hidden="true">
    <div class="modal-dialog" style="max-width:800px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">варианты перевода диалога </h5>
                <h5 class="modal-title" style="padding-left: 10px;color:#0549af; cursor:pointer;"
                    id="translateModalName"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="translates">
                    
                </div>
                <div id="uploadbtn" class="mt-2"></div>
                <div id="uploadfiles" style="margin-top: 0px;"></div>
            </div>
            <div>

            </div>
            <div class="modal-footer">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <label class="input-group-text" for="inputGroupSelect01">group</label>
                    </div>
                    <select class="custom-select" id="selectGroupFromModalMessage">
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-languages" tabindex="-2" role="dialog" aria-labelledby="staticBackdropLabel"
     aria-hidden="true">
    <div class="modal-dialog" style="max-width:800px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">языки</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="languages-container">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="new-language">New</button>
                <button type="button" class="btn btn-outline-success" id="save-languages">Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-groups" tabindex="-2" role="dialog" aria-labelledby="staticBackdropLabel"
     aria-hidden="true">
    <div class="modal-dialog" style="max-width:800px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">группы</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="groups-container">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="new-group">New</button>
                <button type="button" class="btn btn-outline-success" id="save-groups">Save</button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    var middial;
    var groups = <?=json_encode($groups) ?>;
    var languages = <?=json_encode($languages) ?>;
    
    var dial = {
        template: {
            htmleditor: (d,e=false)=> {
                let id="bteditor_"+rand();
                let editor = btEditor.html(id);
                let h = `<div class="${e?'':'mb-1'}">`+editor+
          
                        "</div>";
                return {
                    b:h,
                    id:id,
                    val:d.text||""
                }
            },
        },
        save:lan=> {
            let id="bteditor_"+lan;
            qw.post("p.php?q=sendText", {iddial: middial, idlan: lan, body: btEditor.get(id)}, r=> {}, "json", "save");
        },
        open: (th, mid)=> {
            let name = $(th).data('name'),
                groupId = $(th).data('group-id');
                middial = mid;
            
            qw.post("p.php?q=get", {id: mid}, res=> {
                qw.qs("#translateModalName").innerHTML = name;
                let lan, text, id, editor, h, ids;
                res.var.forEach(it=>{
                    lan=it.id_lan;
                    text=it.body;
                    id="bteditor_"+lan;
                    editor = `<div class="translate-item">
                    `+btEditor.html(id)+`
                    <div class="w-100 text-end mt-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-5" onclick="dial.save(${lan})">SAVE</button>
                    </div>
                    </div>
                    <hr>`;
                    qw.append("#translates", editor);
                 
                    btEditor.init(id, text||"");
                })
    
                
                // 
                for (var i = 0; i < 100; i++)
                    $('#body-var' + i).val('');
                for (var i = 0; i < res.var.length; i++)
                    $('#body-var' + res.var[i]['id_lan']).val(res.var[i]['body']);
    
                
                if (typeof res.filesGroup === "number" || res.filesGroup == "") {
                    $("#uploadbtn").html(appUpload.form({
                        id: 1
                        , group: res.filesGroup
                        , style: ''
                        , classes: 'btn btn-outline-secondary'
                        , uploadFunc: 'uploadFile'
                        , deleteFunc: 'uploadFile'
                    }));
    
                    $("#uploadfiles").html(appUpload.container({
                        id: 1
                        , files: res.files
                    }));
                } else {
                    $("#uploadbtn").html('<span>Редактирование файлов невозможно, так как здесь переменная</span>');
                    $("#uploadfiles").html('');
                }
    
    
                $html = '<option value="0">Основные</option>';
                groups.forEach(function (item, i) {
                    $html += `<option value="${item.id_group}">${item.name}</option>`;
                });
    
    
                $('#selectGroupFromModalMessage').html($html);
                $('#selectGroupFromModalMessage').val(groupId);
    
    
                $('#modal-translate').modal('show');
    
    
            }, "json", "dialogue get");

        }
    }
    


    $(document).ready(function () {

        $('#input-main-search-message').on('input', function () {
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


        /****************************************************************************** LANGUAGES */
        $('#open-languages').on('click', function () {

            $html = '';
            languages.forEach(function (item, i) {
                $html += language_template(item.id_lan, item.iso, item.name);
            });

            $('#languages-container').html($html);
            $('#modal-languages').modal('show');

        });


        $('#new-language').on('click', function () {
            $('#languages-container').append(language_template('', '', ''));
        });


        function language_template(lanId, lanIso, lanName) {
            let lanCodes = <?=$lanCodes?>;


            let lanCodesText = '';

            lanCodes.forEach(function (item, i) {
                lanCodesText += `<option value="${item['code']}" ${item['code'] == lanIso.toLowerCase() ? 'selected' : ''}>${item['ru']} ${item['code'] !== '' ? ' (' + item['code'] + ')' : ''}</option>`;
            });


            return `
              <div class="input-group mb-1" action-type="row-lan-edit" lanId="${lanId}">

                <select class="custom-select" id="lan-name-edit-${lanId}">
                ${lanCodesText}
                </select>

                <input type="text" class="form-control" placeholder="lan name (english)" aria-label="lan name" id="lan-desc-edit-${lanId}" value="${lanName}">

                <div class="input-group-append">
                <!-- <button class="btn btn-outline-secondary" type="button" action-type="lan-translate"  lanId="${lanId}"><i class="fa fa-language"> </i> translate</button> -->
                  <button class="btn btn-outline-secondary" type="button" action-type="lan-delete"  lanId="${lanId}">delete</button>
                </div>
              </div>
          `;
        }


        $('#send_translate_from_area').on('click', function () {
            $('#send_translate_from_area').hide();

            let lang = $('#selectTranslateLanFromArea').val();
            if (lang == '')
                return toast('Ошибка', 'Выберите язык из списка', 'error');

            let text = $('#translate-area').val();
            
            qw.post("p.php?q=sendTranslation", {text: text, lang: lang}, (res)=> {
                    $('#send_translate_from_area').show();
                        if (res.notFoundVar.length > 0) {
                            let notFoundVar = '';
                            res.notFoundVar.forEach(function (item, i) {
                                notFoundVar += '<br><b>' + item.var + '</b><br><i>' + item.text + '</i>';
                            });

                            toast('Статус', 'не найденные переменные в переведенном тексте (сохранено в консоли)' + notFoundVar, 'err');
                            console.log('не найденные переменные в переведенном тексте ');
                            console.log(res.notFoundVar);
                        }



                }, "json", "dialogue sendTranslation");

        });


        $('#get_the_text_in_the_parent_field').on('click', function () {
            let lang = $('#selectTranslateLanFromArea').val();
            if (lang === '')
                return toast('Ошибка', 'Выберите язык из списка', 'error');
            
            qw.post("p.php?q=getTextForTranslation", {lang: lang}, (res)=> {
                        let h = '';
                        res.rows.forEach(function (item, i) {
                            console.log(item.length)
                            h += `<a href="${"https://translate.yandex.ru/?lang=ru-" + lang + "&text=" + encodeURIComponent(item)}" target="_blank">Ссылка ${i + 1}</a><br>`;
                        });
                        $('#link-area').html(h);
                }, "json", "error");

        });


        $('body').on('click', 'button[action-type="lan-translate"]', function () {
            let lanId = $(this).attr('lanId');
            
            qw.post("p.php?q=autoTranslate", {lanId: lanId}, (r)=> { }, "json", "dialogue sendTranslation");

        });


        $('body').on('click', 'button[action-type="lan-delete"]', function () {
            if (prompt('введите 1234 для подтверждения') != '1234')
                return;

            let lanId = $(this).attr('lanId');
            
            qw.post("p.php?q=deleteLan", {lanId: lanId}, (r)=> {
                        $(`div[action-type="row-lan-edit"][lanId="${lanId}"]`).remove();
                        window.location.href = window.location.href;
                }, "json", "dialogue deleteLan");

        });


        $('#save-languages').on('click', function () {
            let lanList = [];
            $('#languages-container').children().each(function (i, item) {
                let id = $(item).attr('lanId');
                let iso = qw.qs('#lan-name-edit-' + id).value;
                let name = qw.qs('#lan-desc-edit-' + id).value;

                if (iso && name)
                    lanList.push({id: id, name: name, iso: iso});
            })
            
            qw.post("p.php?q=sendLanlist", {languages: JSON.stringify(lanList)}, (res)=> {
                        languages = res.languages;
                        $('#modal-languages').modal('hide');
                }, "json", "dialogue sendLanlist");

        });


        /****************************************************************************** GROUPS */
        $('#open-groups').on('click', function () {

            $html = '';
            groups.forEach(function (item, i) {
                $html += group_template(item.id_group, item.name);
            });

            $('#groups-container').html($html);
            $('#modal-groups').modal('show');

        });


        $('#new-group').on('click', function () {
            $('#groups-container').append(group_template('', ''));
        });


        $('#save-groups').on('click', function () {
            let groupList = [];
            $('#groups-container').children().each(function (i, item) {
                let name = $(item).find('input').val();
                let id = $(item).attr('groupId');

                if (id || name)
                    groupList.push({id: id, name: name});
            })
            
            qw.post("p.php?q=saveGroups", {groups: JSON.stringify(groupList)}, function (res) {
                    groups = res.groups;
                    $('#modal-groups').modal('hide');
                });

        });


        function group_template(groupId, groupName) {
            return `
              <div class="input-group mb-1" action-type="row-group-edit" groupId="${groupId}">
                <input type="text" class="form-control" placeholder="group name" aria-label="group name" id="group-edit-${groupId}" value="${groupName}">
                <div class="input-group-append">
                  <button class="btn btn-outline-secondary" type="button" action-type="group-delete"  groupId="${groupId}">delete</button>
                </div>
              </div>
          `;
        }


        $('body').on('click', 'button[action-type="group-delete"]', function () {
            let groupId = $(this).attr('groupId');
            
            qw.post("p.php?q=deleteGroup", {groupId: groupId}, (r)=> {
                $(`div[action-type="row-group-edit"][groupId="${groupId}"]`).remove();
            }, "json", "dialogue deleteGroup");

        });

        $('#translateModalName').on('click', function () {
            let that = this;
            let name = $(this).html();
            name = prompt(name, name);
            if (name == false || name == null) return;
            
            qw.post("p.php?q=edit", {'var': 'description', val: name, dialogId: middial}, (r)=> {
                    $('#dialog-name-from-list-' + middial).html(name);
                    $(`.list-group-item[data-rid="${middial}"]`).data('name', name);
                    $(that).html(name);
                }, "json", "dialogue edit");

        });


        $('#selectGroupFromModalMessage').on('change', function () {
            let groupId = $(this).val();
            
            qw.post("p.php?q=edit", {
                'var': 'id_group',
                val: groupId,
                dialogId: middial
            }, (r)=> {
                $(`.list-group-item[data-rid="${middial}"]`).prependTo('#dialog-list-' + groupId);
                $(`.list-group-item[data-rid="${middial}"]`).data('group-id', groupId);
            }, "json", "dialogue  edit");
        });

    });

    function uploadFile(id, groupFilesId) {
        qw.post("p.php?q=edit", {
            'var': 'files',
            val: groupFilesId,
            dialogId: middial
        }, (r)=> {}, "json", "dialogue edit");
    }
</script>
