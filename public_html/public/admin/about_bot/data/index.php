<div class="p-2 pt-4 pb-4 text-left" style='background-color: white;'>


<?php
function recursion($stepId, $arr=[]){
    if(isset($arr[$stepId]) || !$stepId) return $arr;
    $arr[$stepId]=[];
    $textArr=[];
    $rowSteps = arrayQuery("SELECT *, cs.id stepId, cs.item stepTechName, cs.name stepName, cs.type_step stepType
FROM s_steps cs WHERE cs.source = $stepId AND cs.status = 1 ORDER BY cs.t_sort");
    foreach($rowSteps as $rowStep){
        $stepType = $rowStep['stepType'];
        $chainId=$rowStep['stepId'];
        $parameters = json_decode($rowStep['parameters'], true);
        $textArr[$rowStep['stepId']]=[];
        $txtStId=&$textArr[$rowStep['stepId']];
        $txtStId= [
            'title'=> $rowStep['stepName'],
            'type'=> $rowStep['stepType'],
            'description'=>'',
        ];
        $txtDesc=&$txtStId['description'];
        $txtStId['messages']=[];
        $msgs = arrayQuery("SELECT selected_keyboard, message, id_keyboard, conditions, id_inline_keyboard, type_public FROM `s_steps_messages` WHERE id_step = $chainId ORDER BY t_sort");
        foreach($msgs as $msg){
            $msgTxt=getMess($msg['message']);
            $MSG=[];
            $msgTxt = str_replace("\n", '<br>', $msgTxt);
//$MSG['body']='отправляем сообщение <br>'.$msgTxt;
            $MSG['body']=$msgTxt;
            $selected_keyboard = $msg['selected_keyboard'];
            switch($selected_keyboard){
                case 'keyboard':
                    $btns=get_keyboard_buttons($msg['id_keyboard']);

                    $MSG['keyboard']=$btns;
                    foreach($btns as $btn){
                        foreach($btn as $b){
                            if($b['visible'])
                                $arr = get_recursion_to_trigger($b['text'], $arr);
                        }

                    }
                    break;
                case 'inline_keyboard':
                    $btns=get_keyboard_buttons($msg['id_inline_keyboard']);
                    $MSG['inlineKeyboard']=$btns;
                    foreach($btns as $btn)
                        foreach($btn as $b){
                            $arr = get_inl_btns($b, $arr);
                        }
                    break;
            }
            $conditions = $msg['conditions'];
            if($conditions){
                $condition = get_conditions($conditions, $arr, $msgTxt);
                $arr = $condition['arr'];
                $MSG['body']=$condition['body'];
            }
            array_push($txtStId['messages'], $MSG);
        }
        switch($stepType){
            case 'message': break;
            case 'input_text': break;
            case 'input_email': break;
            case 'input_image':
######################
                break;
            case 'custom_function':
######################
                break;
            case 'waiting_for_a_response':
######################
                break;
            case 'input_phone':
###################### SMS
                break;
            case 'blockchain':
                $blockchain = $parameters['name'];
                $arr = get_recursion_to_techname($blockchain, $arr);
                $txtDesc .= ' переход к сценарию '.get_block_name($blockchain);
                break;
            case 'data_record':
######################
                break;
            case 'product_category':
######################
                break;
            case 'data_edit':  break;
            case 'market_items':
######################
                break;
            case 'local_payment':
######################
                break;
            case '':
######################
                break;
            case '':
######################
                break;
        }
    }
    $arr[$stepId]['content']=$textArr;
    return $arr;
}
function get_inl_btns($btn, $arr){
    if(!$btn['status']) return $arr;
    switch($btn['action']){
        case 'script':
            $arr = get_recursion_to_techname($btn['action_name'], $arr);
            break;
        case 'function':
######################
            break;
        case 'custom':
######################
            break;
    }
    return $arr;
}
function get_triggers_for_text($blockchain){
    $r = singleQuery("SELECT GROUP_CONCAT(CONCAT('«', ct.name, '»') SEPARATOR ' или ') t_trigger, ts.name search, ct.priority FROM s_triggers ct, s_triggers_steps s, s_triggers_type_search ts
WHERE s.id_trigger = ct.id AND ct.type_of_search = ts.techname AND s.id_script = $blockchain");
    if($r['search'])
        $t = ($r['priority']?'Приоритетный! ':'')."срабатывает, если {$r['search']} {$r['t_trigger']} ";
    else
        $t=false;
    return $t;
}
function getMess($mess){
    $messQuery = singleQuery("SELECT dt.id id_translate, dt.body, l.iso lang
FROM dialogue d, dialogue_translate dt, s_langs l
WHERE dt.id_dial = d.id AND dt.id_lan = 1 AND l.id = dt.id_lan AND d.name = '$mess'");
    $lang = $messQuery['lang'];
    return $messQuery['body'];

}
function get_recursion_to_techname($techname, $arr){
    $bid = get_block_from_techname($techname);
    return recursion($bid, $arr);
}
function get_recursion_to_trigger($trigger, $arr){
    $bid = get_block_from_trigger($trigger);
    return recursion($bid, $arr);
}
function get_keyboard_buttons($id){
    $arr=[];
    $rowKb = singleQuery("SELECT name, techname, buttons FROM `s_keyboards` WHERE id=$id");
    $buttons=$rowKb['buttons'];
    $buttons=json_decode(urldecode($buttons), true);
    foreach($buttons as $button)
        array_push($arr, $button);
    return $arr;
}
function get_conditions($condition, $arr, $msgTxt){
    singleQuery("SET @num=0;");
    $cndtns = singleQuery("   
SELECT GROUP_CONCAT(CONCAT(IF((@num:=@num+1)>1, CONCAT(' ', l.name, ' '), ''), c.variable, ' ', o.name, ' «', c.value, '»') SEPARATOR ' ') text, script FROM s_conditions c
JOIN s_condition_operators o ON c.operator = o.techname
JOIN s_condition_logic_operators l ON c.logic = l.techname
WHERE c.id_group = $condition AND c.check_mode = 1 ORDER BY c.id");
    $tScript=$cndtns['script'];
    $scrptName=get_block_name($cndtns['script']);
    switch($cndtns['script']){
        case '#break#':
            $desc = "если {$cndtns['text']}, то прерываем действие и выводим сообщение $msgTxt";
            break;
        case '#ignore#':
            $desc = "если {$cndtns['text']}, то выводим сообщение $msgTxt и продолжаем";
            break;
        default:
            $desc = "если {$cndtns['text']}, то " . ($cndtns['script']=='#continue#'?'продолжаем': "переходим к сценарию $scrptName");
    }
    $arr = get_recursion_to_techname($tScript, $arr);
    return ['arr'=>$arr, 'body' => $desc];
}
function get_block_from_trigger($trigger){
    global $mysqli;
    $r = singleQuery("SELECT c.name, c.id, c.techname FROM `s_triggers` ct
JOIN `s_triggers_steps` cts ON cts.id_trigger = ct.id
JOIN constructors c ON c.id = cts.id_script
WHERE ct.name = '$trigger'");
    return $r ? $r['id'] : '';
}
function get_block_name($blockchain){
    global $mysqli;
    $r = singleQuery("SELECT c.display_name FROM constructors c WHERE c.techname = '$blockchain' OR c.id = '$blockchain'");
    return $r ? "«{$r['display_name']}»" : '';
}
function get_block_from_techname($techname){
    global $mysqli;
    $r = singleQuery("SELECT c.name, c.id FROM `constructors` c WHERE c.techname = '$techname'");
    return $r ? $r['id'] : '';
}







            $stepId = get_block_from_trigger('/start');

            $arr = recursion($stepId);

            $navMenu='';
            $content='';

            foreach($arr as $constKey=>$constRow){

                $res = singleQuery("SELECT c.display_name, c.id FROM `constructors` c WHERE c.id = '$constKey'");
                $id='blckChn-'.$res['id'];
                $i=$i?$i+1:1;
                $navMenu .= "<a class='nav-link nav-link-th text-info p-0' action='nm-item' num='$i' href='#$id' >$i. {$res['display_name']}</a>";
                $content .= "<h3 id='$id'>{$res['display_name']}</h3>";
                $triggers = get_triggers_for_text($constKey);
                if($triggers)
                    $content .= "<div>$triggers</div>";

                foreach($constRow['content'] as $r){
                    $content .= "<i><h5>{$r['title']}</h5></i>";
                    if($r['description'])
                        $content .= "<p>{$r['description']}</p>";


                    foreach($r['messages'] as $m){


                        $content .= "<div class='rectang mt-1 mx-3 text-start'> {$m['body']} </div>";


                        if($m['inlineKeyboard']){
                            $content .= "<div class='ms-3'>inline клавиатура</div>";
                            $kb='<div class="container" style="max-width:600px;display: inline-block;">';

                            foreach($m['inlineKeyboard'] as $kbRow){
                                $kb .= '<div class="row">';
                                foreach($kbRow as $kbCol)
                                    if($kbCol['status'])
                                    {

                                        $kb .= '<div class="col p-1">';
                                        $kb .= "<button type='button' $href class='btn btn-outline-secondary' style='width: 100%;background-color:#98a2aa;color:white;'>{$kbCol['text']}</button>";
                                        $kb .= '</div>';
                                    }
                                $kb .= '</div>';
                            }
                            $kb .= '</div>';
                            $content .= $kb;
                        }


                        if($m['keyboard']){
                            $content .= "<div class='ms-3'>обычная клавиатура</div>";
                            $kb='<div class="container" style="max-width:600px;display: inline-block;">';

                            foreach($m['keyboard'] as $kbRow){
                                $kb .= '<div class="row">';

                                foreach($kbRow as $kbCol){
                                    if(!is_array($kbCol)) $kbCol = ['text'=> $kbCol, 'visible'=> 1];
                                    if($kbCol['visible']){
                                        $href='';
                                        $bid = get_block_from_trigger($kbCol['text']);
                                        if($bid)
                                            $href = "href='#blckChn-$bid'";
                                        $kb .= '<div class="col p-1">';
                                        $kb .= "<a type='button ' $href class='btn btn-outline-secondary' style='width: 100%;background-color:#f1f1f1;color:#222222;'><b>{$kbCol['text']}</b></a>";
                                        //$kb .= "<button type='button' $href class='btn btn-outline-secondary' style='width: 100%;background-color:#f1f1f1;color:#222222;'><b>$kbCol</b></button>";
                                        $kb .= '</div>';


                                    }
                                }

                                $kb .= '</div>';
                            }
                            $kb .= '</div>';
                            $content .= $kb;
                        }



                    }


                }

                $content .= '<hr>';
            }
            
            ?>
            <div class="bd-example" >
                <div class="row">
                    <div class="col">
                        <div data-spy="scroll"  data-bs-target="#navbar-example" data-offset="0"  id='content-blockchains' style="max-width:600px;margin-left: auto;margin-right: auto;" >
                            <? echo $content; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div style="position: sticky;top: 20px;">
                            <nav id="navbar-example" class="navbar navbar-light bg-light">
                                <nav class="nav nav-pills flex-column" id="nav-menu-blockchains">
                                    <? echo $navMenu; ?>
                                </nav>
                            </nav>
                            <hr>
                            <div class="form-floating">
                                <textarea class="form-control" id="notes" style="height: 160px" onblur="notes()"></textarea>
                                <label for="notes">Заметки</label>
                            </div>

                            <span>Документы</span>
                            <div class="specifications">


                            </div>
                            <div id="loadbtn"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


<script>
    function init(){
        qw.post("p.php?q=docs", {},(res)=>{
            qw.qs("#loadbtn").innerHTML=appUpload.form({
                id:1
                ,group:res.id||false
                ,classes:'btn btn-outline-secondary btn-sm  m-0 mt-2 w-100'//''
                ,uploadFunc:'uploadFile'
                ,deleteFunc:'uploadFile'
            })
            qw.qs(".specifications").innerHTML=appUpload.container({
                id:1
                ,files:res.files||[]
            })
            qw.qs("#notes").value=res.notes||"";
        },"json","load file");

    }

    function notes(){
        qw.post("p.php?q=notes", {t:qw.qs("#notes").value},()=>{},"json","save notes");
    }

    function uploadFile(f,id){
        qw.post("p.php?q=load", {id:id},()=>{},"json","upload file");
    }
</script>












