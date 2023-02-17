<?php

namespace systems\classes\text;

class text
{

    public function variables( $body, array $par=[], $global=true ){
        if(!is_string($body)) return $body;
        if(strpos($body, '{')===false || strpos($body, '}')===false)
            return $body;

        # ищем переменные, которые обнулять, если не найдено значение
        $ifEmptyVarList = [];
        for($i=0;$i<20;$i++){
            if(!$ifEmptyVar = getStrBetween($body, '!{', '}')) break;
            if(in_array($ifEmptyVar, $ifEmptyVarList)===false)
                $ifEmptyVarList[] = $ifEmptyVar;
            $body = str_replace('!{'.$ifEmptyVar.'}', '{'.$ifEmptyVar.'}', $body);
        }

        if($global)  $par = array_merge($GLOBALS,$par);

        foreach($par as $key=> $val){
            if(is_string($val) || is_numeric($val))
                $body = str_replace('{'.$key.'}', $val, $body);

            if(strpos($body, '{')===false || strpos($body, '}')===false)
                return $body;
        }

        foreach($ifEmptyVarList as $ifEmptyVar)
            $body = str_replace('{'.$ifEmptyVar.'}', '', $body);

        return $body;
    }

    function entitlesEncode($text, array $entities /* массив entities, возвращаемый телегой*/){
        $ec=['italic'=> 'i', 'bold'=> 'b', 'strikethrough'=> 's', 'underline'=> 'u', 'code'=> 'code', 'text_link'=> 'a' ];
        for($i=0;$i<count($entities); $i++){
            $ent = $entities[$i];
            if(!$tag=$ec[$ent['type']]) continue;
            $s = '<'.$tag.($tag==='a'?' href="'.$ent['url'].'"':'').'>';$scl = '</'.$tag.'>';
            $text = substr($text, 0, $ent['offset']).$s.substr($text, $ent['offset'], 10000);
            $strl=strlen($s);
            foreach($entities as $k=>&$st)
                if($k>$i)$st['offset']+=$strl;
            $text = substr($text, 0, ($sx=$ent['offset']+$strl+$ent['length'])).$scl.substr($text, $sx, 10000);
            $strl=strlen($scl);
            foreach($entities as $k=>&$st)
                if($k>$i && $st['offset']+$strl>$sx)$st['offset']+=$strl;
        }
        return $text;
    }

    public function substituteVariablesInAnArray( $array, $par = [] ){
        # подставить параметры в массив
        $res = [];
        foreach($array as $key=> $val){
            if(is_array($val)){
                $res[$key] = $this->substituteVariablesInAnArray( $val, $par );
            } else {
                $val = $this->variables($val, $par);
                $val = $this->shortcodes($val, $par);
                $res[$key] = $val;
            }
        }
        return $res;
    }


    public function wildcard_data($body, $wildcardData){
        $resp=[];
        $resp_var=[];
        $isArray=false;
        if(is_array($body)){
            $body=json_encode($body);
            $isArray=true;
        }


        foreach($wildcardData as $rwd){
            $rwd['res_var'] = str_replace("'", '"', $rwd['res_var']);



            $variables=[
                ['var'=>$rwd['name_var'], 'mask'=>"{{$rwd['name_var']}}", 'val'=>$rwd['res_var']],
                ['var'=>$rwd['name_var'], 'mask'=>urlencode("{{$rwd['name_var']}}"), 'val'=>$rwd['res_var']]
            ];



            $type=$rwd['type'];

            switch($type){
                // case 'text':
                //         $body = str_replace($nameVar, $rwd['res_var'], $body);
                //     break;
                case 'group_of_files':


                    foreach($variables as $r)
                        if(strpos(' '.$body, $r['mask'])){
                            $body = str_replace($r['mask'], '', $body);
                            array_push($resp_var, [ 'var'=>$r['var'], 'val'=>'', 'type'=>$type ]);
                            $resp['files']=$r['val'];
                            break;
                        }

                    break;

                case 'category':
                    foreach($variables as $r)
                        if(strpos(' '.$body, $r['mask'])){

                            if($r['srcData'])
                                $category = get_category($r['srcData']);
                            else
                                $category = get_category(['id_cat'=>$r['val'], 'display'=>'one']);

                            $body = str_replace($r['mask'], $category, $body);
                            array_push($resp_var, [ 'var'=>$r['var'], 'val'=>$r['val'], 'type'=>$type ]);
                            break;
                        }
                    break;

                case 'contents_of_the_data':
                    foreach($variables as $r)
                        if(strpos(' '.$body, $r['mask'])){
                            $data = get_data_text_message($rwd['res_var']);
                            if($data['success']==1){
                                $tbody = $data['body'];
                                if($data['files']) $files = $data['files'];
                            }
                            else $tbody = '!'.$data['err'];

                            $body = str_replace($r['mask'], $tbody, $body);
                            array_push($resp_var, [ 'var'=>$r['var'], 'val'=>$r['val'], 'type'=>$type ]);
                            break;
                        }
                    break;


                case 'link_of_the_data':
                    foreach($variables as $r)

                        foreach($variables as $r)
                            if(strpos(' '.$body, $r['mask'])){

                                $data = get_data_text_message($r['val']);

                                if($data['success']==1){
                                    $tbody = $data['link'];
                                }
                                else $tbody = '!'.$data['err'];

                                $body = str_replace($r['mask'], $tbody, $body);
                                array_push($resp_var, [ 'var'=>$r['var'], 'val'=>$r['val'], 'type'=>$type ]);
                                break;
                            }
                    break;

                default:

                    foreach($variables as $r)
                        if(strpos(' '.$body, $r['mask'])){
                            $body = str_replace($r['mask'], $r['val'], $body);
                            array_push($resp_var, [ 'var'=>$r['var'], 'val'=>$r['val'], 'type'=>$type ]);
                            break;
                        }

            }


        }


        if($isArray){
            $body = str_replace(["\r\n", "\r", "\n"], '\n', $body);
            $body = json_decode($body, true);
        }


        $resp['body']=$body;
        $resp['variables']=$resp_var;
        return $resp;

    }










############################## WILDCARD_DATA #
    public function from_variable_data($par){

    }


############################## THE DECLENSION OF WORDS #
    function num_word($value, $words, $show = true)
    {
        # text()->num_word($period_banned, ['день', 'дня', 'дней']);
        $num = $value % 100;
        if ($num > 19) {
            $num = $num % 10;
        }

        $out = ($show) ?  $value . ' ' : '';
        switch ($num) {
            case 1:  $out .= $words[0]; break;
            case 2:
            case 3:
            case 4:  $out .= $words[1]; break;
            default: $out .= $words[2]; break;
        }

        return $out;
    }

############################## SHORTCODES #
    function shortcodes($text, $parameters=[]) {
        #$b=false;
        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[singleimage]', '[singleimage]');
            if ($var!=="") {
                $id_group = $var;
                $file = singleQuery("SELECT * FROM files WHERE id_group = :id_group", [':id_group'=> $id_group]);
                $link = $file['medium_size'] ? $file['medium_size'] : ($file['large_size'] ? $file['large_size'] : $file['small_size']);
                if ($link && !strpos(' ' . $link, 'http')) $link = _dir_ . '/' . $link;
                $text = str_replace('[singleimage]' . $var . '[singleimage]', $link, $text);
            } else break;
        }
        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[match]', '[match]');
            if ($var!=="") {
                $var2 = calculate($var);
                $text = str_replace('[match]' . $var . '[match]', $var2, $text);
            } else break;
        }
        for ($i = 0;$i < 20;$i++) {
            # round padding округление с дополнением нолей
            $var = getStrBetween($text, '[roundpadding', '[roundpadding]');
            if ($var!=="") {
                $l = getStrBetween($var, '=', ']');
                $var2 = str_replace("=$l]", '', $var);
                $var2 = str_replace(',', '.', $var2);
                $var2 = str_replace(',', '.', round($var2, $l) );
                $explodeDigits = explode('.', (string)$var2);
                $div = str_pad($explodeDigits[1]?$explodeDigits[1]:'', $l, '0');

                $text = str_replace('[roundpadding' . $var . '[roundpadding]', $explodeDigits[0] . ($div?'.' . $div : ''), $text);
            } else break;
        }
        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[round', '[round]');
            if ($var!=="") {
                $l = getStrBetween($var, '=', ']');
                $var2 = str_replace("=$l]", '', $var);
                $var2 = str_replace(',', '.', $var2);
                $var2 = round($var2, $l);
                $text = str_replace('[round' . $var . '[round]', $var2, $text);
            }
        }
        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[phone]', '[phone]');
            if ($var!=="") {
                $var2 = $this->globalVar($var);
                $text = str_replace('[phone]' . $var . '[phone]', $this->phone($var2), $text);
                #$b=true;
                $text = str_replace('[phone][phone]', '', $text);
            } else break;
        }

        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[if]', '[if]');
            if ($var!=="") {
                $p = explode('?', $var);
                $condition=trim($p[0]);
                $pp = explode(':', $p[1]);
                $conditionRes=false;

                if(count($t=explode('===', $condition))==2) $conditionRes = $t[0]===$t[1];
                elseif(count($t=explode('==',  $condition))==2) $conditionRes = $t[0]==$t[1];
                elseif(count($t=explode('!==', $condition))==2) $conditionRes = $t[0]!==$t[1];
                elseif(count($t=explode('!=',  $condition))==2) $conditionRes = $t[0]!=$t[1];
                elseif(count($t=explode('>=',  $condition))==2) $conditionRes = $t[0]>=$t[1];
                elseif(count($t=explode('<=',  $condition))==2) $conditionRes = $t[0]<=$t[1];
                elseif(count($t=explode('<>',  $condition))==2) $conditionRes = $t[0]<>$t[1];
                elseif(count($t=explode('>',   $condition))==2) $conditionRes = $t[0]>$t[1];
                elseif(count($t=explode('<',   $condition))==2) $conditionRes = $t[0]<$t[1];
                elseif(count($t=explode('<=>', $condition))==2) $conditionRes = $t[0]<=>$t[1];
                else $conditionRes = $condition;


                $text = str_replace('[if]' . $var . '[if]', $conditionRes? trim($pp[0]):trim($pp[1]), $text);
            } else break;
        }


        //[] 1=2?yes:no []
        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[limit', '[limit]');
            if ($var!=="") {
                $l = getStrBetween($var, '=', ']');
                $var2 = str_replace("=$l]", '', $var);
                $var2 = mb_strimwidth($var2, 0, $l, "...");
                $text = str_replace('[limit' . $var . '[limit]', $var2, $text);
            } else break;
        }



        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[chat=', ']');
            if ($var!=="") {
                $var2 = singleQuery('SELECT id_chat FROM `chats` WHERE techname = :techname', [ ':techname'=> $var ])['id_chat'];
                $text = str_replace('[chat=' . $var . ']', $var2, $text);

            } else break;
        }

        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[date', '[date]');
            if ($var!=="") {
                $l = substr($var, 0, strpos($var, ']'));

                $attr = getStrBetween($l, 'attr="', '"');
                $attr = str_replace('\n', "\n", $attr);
                $attr = explode(';', $attr);
                $tPar = [];
                foreach ($attr as $a) {
                    $a = explode(':', $a);
                    $tPar[$a[0]] = $a[1];
                }

                $format = $tPar['format'] ? $tPar['format'] : 'd-m-Y';

                $var2 = substr($var, strpos($var, ']')+1);
                $strtotime = strtotime($var2);
                $text = str_replace('[date' . $var . '[date]', date($format, $strtotime), $text);
            } else break;
        }
        # добывает ссылку из данных на указанный чат
        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[chatLink', '[chatLink]');
            if ($var!=="") {
                $l = getStrBetween($var, '', ']');
                $id_data = str_replace("$l]", '', $var);
                $par = getStrBetween($var, '', ']');
                $par = $this->remove_spaces($par);
                $par = $this->replace_quotation_marks($par);
                //$ifempty = getStrBetween($par, 'ifempty="', '"'); # если чат не найден
                $target = getStrBetween($par, 'target="', '"'); # целевой чат

                $target = $this->globalVar($target);
                $dt = get_data_text_message($id_data, ['linkFromChat' => $target]);
                $link = $dt['link'];
                //   if(!$dt['channel_name']){
                //       if($ifempty)
                //           $link = "https://t.me/$ifempty/{$dt['mesgId']}";
                //           else
                //           $link = '';
                //
                //   }
                if (isset($dt['err'])) $link = $dt['err'];
                $text = str_replace('[chatLink' . $var . '[chatLink]', $link, $text);
                #$b=true;

            } else break;
        }
        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[global]', '[global]');
            if ($var!=="") {
                $text = str_replace('[global]' . $var . '[global]', $GLOBALS[$var], $text);
                #$b=true;

            } else break;
        }
        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[category', '[category]');
            if ($var!=="") {
                # [category attr="separator:/"]var[category]
                $l = substr($var, 0, strpos($var, ']'));
                $var = substr($var, strpos($var, ']') + 1, 100);
                $attr = getStrBetween($l, 'attr=\'', '\'');
                if(!$attr) $attr = getStrBetween($l, 'attr="', '"');
                $attr = str_replace('\n', "\n", $attr);
                $attr = explode(';', $attr);
                $catPar = ['id_cat' => $var];
                foreach ($attr as $a) {
                    $a = explode(':', $a);
                    $catPar[$a[0]] = $a[1];
                }

                $text = str_replace("[category$l]" . $var . "[category]", categories()->get_category_func($catPar), $text);
                #$b=true;

            } else break;
        }
        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[settings]', '[settings]');
            if ($var!=="") {
                $text = str_replace('[settings]' . $var . '[settings]', setting($var), $text);
                #$b=true;

            } else break;
        }

        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[setting=', ']');
            if ($var!=="") {
                $var2 = setting($var);
                $text = str_replace('[setting=' . $var . ']', $var2, $text);

            } else break;
        }

        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[array]', '[array]');
            if ($var!=="") {
                $arrRows = explode('->', $var);

                $tVal = '';
                foreach($arrRows as $arrRow)
                    $tVal = $tVal ? $tVal[$arrRow] : $parameters[$arrRow];


                if(is_array($tVal)){
                    $tText = '';
                    foreach($tVal as $tValKey=> $tValrow)
                        $tText .= $tValKey.'. '.$tValrow.PHP_EOL;
                    $tVal = $tText;
                }


                $text = str_replace('[array]' . $var . '[array]', $tVal, $text);
                #$b=true;

            } else break;
        }


        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[translate=', ']');
            if ($var!=="") {

                $text = str_replace('[translate=' . $var . ']', DIALTEXT($var), $text);
                $text = $this->variables($text, $parameters);
                $text = $this->shortcodes($text, $parameters);
            } else break;
        }


        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[htag=', ']');
            if ($var!=="") {
                $h = str_replace(' ', '_', $var);
                $h = '#'.preg_replace('/[^a-zA-Zа-яА-ЯёЁ0-9_]/ui', '',$h );
                $text = str_replace('[htag=' . $var . ']', $h, $text);

            } else break;
        }


        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[progress=', ']');
            if ($var!=="") {
                $s1='🟩';
                $s2='⬜️';
                $res=array_pad(array_fill(0, round($var/10), $s1),10,$s2);

                $text = str_replace('[progress=' . $var . ']', implode('',$res).' <b>'.round($var,1).'%</b>', $text);

            } else break;
        }


        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[sqlmatch]', '[sqlmatch]');
            if ($var!=="") {
                $text = str_replace('[sqlmatch]' . $var . '[sqlmatch]', $this->text_to_match($var), $text);
                #$b=true;

            } else break;
        }

        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[html]', '[html]');
            if ($var!=="") {

                $text = str_replace('[html]' . $var . '[html]', $this->html($var), $text);
                #$b=true;

            } else break;
        }




        for ($i = 0;$i < 20;$i++) {
            # флаг страны из двузначого кода страны
            $var = getStrBetween($text, '[flag=', ']');
            if ($var!=="") {

                $text = str_replace('[flag=' . $var . ']', $this->unicodeFlagCharsConverter($var), $text);

            } else break;
        }


        for ($i = 0;$i < 20;$i++) {
            # флаг страны из двузначого кода страны
            $var = getStrBetween($text, '[user=', ']');
            if ($var!=="") {


                $text = str_replace('[user=' . $var . ']', $this->userLink($var), $text);

            } else break;
        }


        for ($i = 0;$i < 20;$i++) {
            # поиск и замена по регулярному выражению (preg_replace)
            $var = getStrBetween($text, '[mask', '[mask]');
            if ($var!=="") {
                $m = substr($var, 0, strpos($var, '"]')).'"';
                $attr = getStrBetween($m, 'attr="', '"');
                $attr = str_replace('\n', "\n", $attr);
                $attr = explode(';', $attr);
                $tPar = [];
                foreach ($attr as $a) {
                    $a = explode(':', $a);
                    $tPar[$a[0]] = $a[1];
                }
                if(!isset($tPar['replacement']))
                    $tPar['replacement'] = '';

                $var2 = substr($var, strpos($var, '"]')+2);

                $text = str_replace('[mask' . $var . '[mask]', preg_replace($tPar['pattern'], $tPar['replacement'], $var2), $text);
            } else break;
        }


        for ($i = 0;$i < 20;$i++) {
            $var = getStrBetween($text, '[f=', ']');
            if ($var!=="") {
                //$fp=explode(',',$var);
                //$f=$fp[0];
                $f=$var;
                if(get_custom_function($f))
                    $var2=$f( $obj );
                elseif ($func = findMethod($f))
                    $var2=$func->$f( $obj );
                else $var2='';

                $text = str_replace('[f=' . $var . ']', $var2, $text);

            } else break;
        }


        return $text;
    }


    public function userLink($id){
        $userLink= 'user id: '.$id;
        if($user = singleQuery('SELECT * FROM `usersAll` WHERE chat_id = ?', [$id]))
            $userLink = $user['username'] ? '@'.$user['username'] : "<a href=\"tg://user?id=$id\">{$user['first_name']}</a>";
        return $userLink;
    }

    public function html($text){
        return htmlspecialchars($this->specialchars(strip_tags($text, '<i><a><b><u><s><code>')));
    }

    public function text_to_match($text, $numbers = true, $required=false) {

        $par = [];
        if ($numbers) $par['numbers'] = true;
        $text = str_replace('*', '_matchstar', $text);
        $words_arr = explode(" ", $text);


        for ($i = 0;$i < count($words_arr);$i++) {
            $r = $words_arr[$i];
            if ($this->numbers($r) != '') continue;
            $words_arr[$i] = mb_strlen($r) > 4 ? mb_substr($r, 0, mb_strlen($r) - 1).'*' : $r;
        }

        $t = "";
        foreach ($words_arr as $r) {
            $t .= ' ' . ($required?'+':'').$r;
        }

        return trim($t);
    }


    public function numbers($text) {
        return preg_replace("/[^0-9]/", '', $text);
    }

    public function camelString($text) {
        $text = preg_replace('#\w\K(?=[A-Z])#u', ' ', $text);
        $text = preg_replace('/[^a-zA-Z]/', ' ', $text);
        $text = lcfirst(ucwords($text));
        $text = preg_replace('/[^a-zA-Z]/', '', $text);
        return $text;
    }



    function mb_str_pad($input, $pad_length = 58, $pad_string = "                                          ⁣", $pad_type = STR_PAD_RIGHT)
    {
        $diff = strlen($input) - mb_strlen($input);
        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
    }


############################## REMOVE_SPACES #
    public function remove_spaces($text) {
        while (strpos($text, ' ') !== false) $text = str_replace(' ', '', $text);
        return $text;
    }
############################## REPLACE_QUOTATION_MARKS #
    public function replace_quotation_marks($text) {
        while (strpos($text, '\'') !== false) $text = str_replace('\'', '"', $text);
        return $text;
    }



    public function globalVar($body) {
        $res = $this->substitution_of_the_global_variables($body);
        return $res['body'];
    }
    public function substitution_of_the_global_variables($body) {
        $arr = [];
        for ($i = 0;$i < 50;$i++) {
            $var = getStrBetween($body, '{', '}');
            if (!$var || $i == 50) break;
            if (isset($GLOBALS[$var])){
                $body = str_replace('{' . $var . '}', $GLOBALS[$var], $body);
                $arr[$var] = $GLOBALS[$var];
            }

            //$body = str_replace( "~$var~", $GLOBALS[$var], $body);

        }
        return ['body' => $body, 'variables' => $arr];
    }


    public function phone($phone) {
        $res = $phone;
        $phone = numbers($phone);
        if (substr($phone, 0, 1) == 8) $phone = '7' . substr($phone, 1, 20);
        if (substr($phone, 0, 1) == 9) $phone = "7$phone";
        if (strlen($phone) == 7) {
            return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
        } elseif (strlen($phone) == 10) {
            return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);
        } elseif (strlen($phone) == 11) {
            return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "+$1 ($2) $3-$4-$5", $phone);
        }
        if (substr($phone, 0, 1) == 7) $res = sprintf("+%s (%s) %s-%s-%s", substr($phone, 0, 1), substr($phone, 1, 3), substr($phone, 4, 3), substr($phone, 7, 2), substr($phone, 9));
        return $res;
    }













    public function unicodeFlagCharsConverter($text){
        return strtr( strtolower($text), [ 'a'=> '🇦', 'b'=> '🇧', 'c'=> '🇨', 'd'=> '🇩', 'e'=> '🇪', 'f'=> '🇫', 'g'=> '🇬', 'h'=> '🇭', 'i'=> '🇮', 'j'=> '🇯', 'k'=> '🇰', 'l'=> '🇱', 'm'=> '🇲',
                'n'=> '🇳', 'o'=> '🇴', 'p'=> '🇵', 'q'=> '🇶', 'r'=> '🇷', 's'=> '🇸', 't'=> '🇹', 'u'=> '🇺', 'v'=> '🇻', 'w'=> '🇼', 'x'=> '🇽', 'y'=> '🇾', 'z'=> '🇿']
        );
    }

    function numToSmileConverter($text) {
        return strtr( strtolower($text), [1 => '1️⃣', 2 => '2️⃣', 3 => '3️⃣', 4 => '4️⃣', 5 => '5️⃣', 6 => '6️⃣', 7 => '7️⃣', 8 => '8️⃣', 9 => '9️⃣', 0 => '0️⃣']);
    }


    function translit($text, $lan = 'ru')
    {
        $L['ru'] = array(
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё',
            'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М',
            'Н', 'О', 'П', 'Р', 'С', 'Т', 'У',
            'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ы',
            'Ъ', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё',
            'ж', 'з', 'и', 'й', 'к', 'л', 'м',
            'н', 'о', 'п', 'р', 'с', 'т', 'у',
            'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ы',
            'ъ', 'ь', 'э', 'ю', 'я'
        );

        $L['en'] = array(
            "A",  "B",  "V",  "G",  "D",  "E",   "YO",
            "ZH", "Z",  "I",  "J",  "K",  "L",   "M",
            "N",  "O",  "P",  "R",  "S",  "T",   "U",
            "F" , "X" , "CZ", "CH", "SH", "SHH", "Y'",
            "''", "'",  "E'", "YU", "YA",
            "a",  "b",  "v",  "g",  "d",  "e",   "yo",
            "zh", "z",  "i",  "j",  "k",  "l",   "m",
            "n",  "o",  "p",  "r",  "s",  "t",   "u",
            "f" , "x" , "cz", "ch", "sh", "shh", "y'",
            "''", "'",  "e'", "yu", "ya"
        );



        if($lan == "ru")
            $translated = str_replace($L['ru'], $L['en'], $text);

        // ...или наоборот
        elseif($lan == "en")
            $translated = str_replace($L['en'], $L['ru'], $text);

        // Возвращаем получателю.
        return $translated;

    }


    public function slug($value, $item=false)
    {
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
        );

        $value = mb_strtolower($value);
        $value = strtr($value, $converter);
        $value = mb_ereg_replace('[^-0-9a-z]', '-', $value);
        $value = mb_ereg_replace('[-]+', '-', $value);
        $value = trim($value, '-');

        return $value;
    }


    public function specialchars($text){
        $arr=['&nbsp;'=>'','&pound;'=>'£','&euro;'=>'€','&para;'=>'¶','&sect;'=>'§','&copy;'=>'©','&reg;'=>'®','&trade;'=>'™','&deg;'=>'°','&plusmn;'=>'±','&frac14;'=>'¼','&frac12;'=>'½','&frac34;'=>'¾','&times;'=>'×'
            ,'&divide;'=>'÷','&fnof;'=>'ƒ','&Alpha;'=>'Α','&Beta;'=>'Β','&Gamma;'=>'Γ','&Delta;'=>'Δ','&Epsilon;'=>'Ε','&Zeta;'=>'Ζ','&Eta;'=>'Η','&Theta;'=>'Θ','&Iota;'=>'Ι','&Kappa;'=>'Κ','&Lambda;'=>'Λ',
            '&Mu;'=>'Μ','&Nu;'=>'Ν','&Xi;'=>'Ξ','&Omicron;'=>'Ο','&Pi;'=>'Π','&Rho;'=>'Ρ','&Sigma;'=>'Σ','&Tau;'=>'Τ','&Upsilon;'=>'Υ','&Phi;'=>'Φ','&Chi;'=>'Χ','&Psi;'=>'Ψ','&Omega;'=>'Ω','&alpha;'=>'α',
            '&beta;'=>'β','&gamma;'=>'γ','&delta;'=>'δ','&epsilon;'=>'ε','&zeta;'=>'ζ','&eta;'=>'η','&theta;'=>'θ','&iota;'=>'ι','&kappa;'=>'κ','&lambda;'=>'λ','&mu;'=>'μ','&nu;'=>'ν','&xi;'=>'ξ','&omicron;'=>'ο',
            '&pi;'=>'π','&rho;'=>'ρ','&sigmaf;'=>'ς','&sigma;'=>'σ','&tau;'=>'τ','&upsilon;'=>'υ','&phi;'=>'φ','&chi;'=>'χ','&psi;'=>'ψ','&omega;'=>'ω','Стрелки'=>'','&larr;'=>'←','&uarr;'=>'↑','&rarr;'=>'→','&darr;'=>'↓',
            '&harr;'=>'↔','&spades;'=>'♠','&clubs;'=>'♣','&hearts;'=>'♥','&diams;'=>'♦','&quot;'=>'"','&amp;'=>'&','&lt;'=>'<','&gt;'=>'>','&hellip;'=>'…','&prime;'=>'′','&Prime;'=>'″','&ndash;'=>'–','&mdash;'=>'—',
            '&lsquo;'=>'‘','&rsquo;'=>'’','&sbquo;'=>'‚','&ldquo;'=>'“','&rdquo;'=>'”','&bdquo;'=>'„','&laquo;'=>'«','&raquo;'=>'»','&#160;'=>'','&#163;'=>'£','&#8364;'=>'€','&#182;'=>'¶','&#167;'=>'§','&#169;'=>'©',
            '&#174;'=>'®','&#8482;'=>'™','&#176;'=>'°','&#177;'=>'±','&#188;'=>'¼','&#189;'=>'½','&#190;'=>'¾','&#215;'=>'×','&#247;'=>'÷','&#402;'=>'ƒ','&#913;'=>'Α','&#914;'=>'Β','&#915;'=>'Γ','&#916;'=>'Δ','&#917;'=>'Ε'
            ,'&#918;'=>'Ζ','&#919;'=>'Η','&#920;'=>'Θ','&#921;'=>'Ι','&#922;'=>'Κ','&#923;'=>'Λ','&#924;'=>'Μ','&#925;'=>'Ν','&#926;'=>'Ξ','&#927;'=>'Ο','&#928;'=>'Π','&#929;'=>'Ρ','&#931;'=>'Σ','&#932;'=>'Τ','&#933;'=>'Υ',
            '&#934;'=>'Φ','&#935;'=>'Χ','&#936;'=>'Ψ','&#937;'=>'Ω','&#945;'=>'α','&#946;'=>'β','&#947;'=>'γ','&#948;'=>'δ','&#949;'=>'ε','&#950;'=>'ζ','&#951;'=>'η','&#952;'=>'θ','&#953;'=>'ι','&#954;'=>'κ','&#955;'=>'λ'
            ,'&#956;'=>'μ','&#957;'=>'ν','&#958;'=>'ξ','&#959;'=>'ο','&#960;'=>'π','&#961;'=>'ρ','&#962;'=>'ς','&#963;'=>'σ','&#964;'=>'τ','&#965;'=>'υ','&#966;'=>'φ','&#967;'=>'χ','&#968;'=>'ψ','&#969;'=>'ω','&#8592;'=>'←'
            ,'&#8593;'=>'↑','&#8594;'=>'→','&#8595;'=>'↓','&#8596;'=>'↔','&#9824;'=>'♠','&#9827;'=>'♣','&#9829;'=>'♥','&#9830;'=>'♦','&#34;'=>'"','&#38;'=>'&','&#60;'=>'<','&#62;'=>'>','&#8230;'=>'…','&#8242;'=>'′'
            ,'&#8243;'=>'″','&#8211;'=>'–','&#8212;'=>'—','&#8216;'=>'‘','&#8217;'=>'’','&#8218;'=>'‚','&#8220;'=>'“','&#8221;'=>'”','&#8222;'=>'„','&#171;'=>'«','&#187;'=>'»'];


        return str_replace(array_keys($arr), array_values($arr), $text);
    }




}


















