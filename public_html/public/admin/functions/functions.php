<?php

//ini_set('display_errors', true);
//ini_set('error_reporting', -1);
error_reporting(E_ERROR);
include_once($_SERVER['DOCUMENT_ROOT'] . '/../_data.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/admin/functions/db_connect.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/../src/telegram/methods/methods.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/../src/project/modules/autoload.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/../src/systems/autoload.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/../src/systems/curl/curl.php');

function db(){ return new systems\classes\db\db; }
function curl(){ return new systems\curl\curl; }
function firstUserInfo(){ return new systems\classes\firstUserInfo\firstUserInfo; }
function language(){ return new systems\classes\language\language; }
function text(){ return new systems\classes\text\text; }
function lists(){ return new systems\classes\lists\lists; }
function loadFiles(){ return new systems\classes\loadFiles\loadFiles; }
function methods(){ return new telegram\methods\methods; }
function keyboards(){ return new systems\classes\keyboards\keyboards; }
function categories(){ return new systems\classes\categories\categories; }

function BOT_ID(){ return (int) BOT_ID ?? 0; }
function CHAT_ID(){ return (int) $GLOBALS['chat_id'] ?? 0; }

$Telegram_botkey = setting('bot_key');
$tgHost = 'https://api.telegram.org/bot' . $Telegram_botkey;
$curl = curl_init();
$start_time = microtime(true);
$st_time = microtime(true);
$stmt=false; // $stmt->errorInfo();

function post($url, $par = [])
{
    return curl()->post($url, $par);
}

function curlSend($url, $par)
{
    global $curl;
    curl_setopt_array($curl, array(CURLOPT_URL => $url, CURLOPT_POST => TRUE, CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_POSTFIELDS => $par,
        // CURLOPT_WRITEFUNCTION => 'progress_function'
    ));
    //curl_setopt($curl, CURLOPT_INTERFACE, '62.217.176.144');
    $res = curl_exec($curl);
    curl_reset($curl);

    $json = json_decode($res, true);
    if ($json['error_code'] == 429 && isset($json['parameters']['retry_after'])) {
        sleep((int)$json['parameters']['retry_after']);
        return curlSend($url, $par);
    }
    //{"ok":false,"error_code":429,"description":"Too Many Requests: retry after 18","parameters":{"retry_after":18}}


    return $res;
}

function curlGet($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    //curl_setopt($ch, CURLOPT_INTERFACE, '62.217.176.144');
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

function res($link)
{
    include_once($_SERVER['DOCUMENT_ROOT'] . "/admin/resources/$link");
}

function settime($point)
{
    // global $start_time, $st_time;
    //  $diff = microtime(true)-$st_time;
    //  if($diff<0.0001)
    //    $diff = 0;
    //  file_put_contents($_SERVER['DOCUMENT_ROOT']. '/log.html',  "--------$point---------"."\ntime: ".(microtime(true)-$start_time)."\ndiff: $diff". PHP_EOL, FILE_APPEND);
    //
    //  $st_time = microtime(true);

}


function converterImage($par)
{
    /*
        dirFrom
        dirTo
        format
    */

    $im = new Imagick($par['dirFrom']);
    $im->setImageBackgroundColor('white');

    $im->flattenImages(); // This does not do anything.
    $im = $im->flattenImages(); // Use this instead.

    $im->setImageFormat($par['format']);

    $im->writeImage($par['dirTo']);


}


# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function resizeImage($img, $max = 500)
{
    if (!extension_loaded('Imagick')) return false;
    $im = new Imagick($img);

    $width = $im->getImageWidth();
    $height = $im->getImageHeight();

    $st = $width > $height ? $width : $height;

    $div = 1;
    if ($st > $max) {
        $div = (int)($st / $max);
    }

    $newWidth = (int)($width / $div);
    $newHeight = (int)($height / $div);

    $im->adaptiveResizeImage($newWidth, $newHeight);
    file_put_contents($img, $im);
}

function user_log($id_user, $id_action, $id_source, $comment)
{
    query("INSERT INTO user_logs (id_user, id_action, id_source, comment) VALUES ($id_user, $id_action, $id_source, :comment);", [':comment' => $comment]);
}



function arrayQuery($query, array $par = [], $html = false)
{
    global $pdo, $stmt;
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute(parameter_if_false($par));
    } catch (PDOException $e) {
        return [];
    }

    $arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($html) foreach ($row as & $tr) $tr = e($tr);
        array_push($arr, $row);
    }
    return $arr;
}

function singleQuery($query, array $par = [], $html = false)
{
    global $pdo, $stmt;

    $stmt = $pdo->prepare($query);
    try {
        $stmt->execute(parameter_if_false($par));
    } catch (PDOException $e) {
        return false;
    }
    if ($stmt->rowCount()) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($html) foreach ($row as & $tr) $tr = e($tr);
        return $row;
    } else return false;
}

function insertQuery($query, array $par = [],$errst=false)
{
    global $pdo, $stmt;
    $stmt = $pdo->prepare($query);
    try {

        $stmt->execute(parameter_if_false($par));

        if(!$errst&&$err=$stmt->errorInfo()[2]){
            if(db()->err_correction([
                'err'=> $err,
                'query'=> $query,
                'par'=> $par
            ])) return;
        }

    } catch (PDOException $e) {
        if(!$errst)
            db()->err_correction([
                'err'=> $e->getMessage(),
                'query'=> $query,
                'par'=> $par
            ]);
        return false;
    }
    if ($id = $pdo->lastInsertId()) return $id;
    else return false;
}



function updateQuery($query, array $par = [])
{
    global $pdo, $stmt;
    $stmt = $pdo->prepare($query);
    try {
        $stmt->execute(parameter_if_false($par));
    } catch (PDOException $e) {
        return false;
    }
}

function query($query, array $par = [])
{

    if (strpos($query, 'INSERT INTO') !== false && strpos($query, 'EXPLAIN') === false){

        return insertQuery($query, $par);
    }


    updateQuery($query, $par);
}

function deleteQuery($query, array $par = [])
{
    global $pdo, $stmt;
    $stmt = $pdo->prepare($query);
    $stmt->execute(parameter_if_false($par));
}

function parameter_if_false($parameters)
{
    foreach ($parameters as &$parameter)
        if ($parameter === false || !isset($parameter))
            $parameter = '';
    return $parameters;
}

function e($val, $type = 'html')
{
    $type = 'html';
    if ($type === 'html') {
        // Используем ISO-8859-1 вместо UTF-8, чтобы функцию можно было применять для любых ASCII совместимых кодировок
        // без передачи аргумента с кодировкой (все спецсимволы html находятся в диапазоне ASCII, поэтому кодировки
        // ISO-8859-1 достаточно). С кодировкой по умолчанию (UTF-8) вызов htmlspecialchars('йцу') вернут пустую строку,
        // если "йцу" будет в кодировке, например, windows1251.
        return htmlspecialchars($val, ENT_QUOTES | ENT_HTML5, 'ISO-8859-1');
    } else if ($type === 'json') {
        return json_encode($val, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    } else {
        return new \Exception('В функцию ' . ($c = debug_backtrace()) [count($c) - 1]['function'] . ' передан некорректный тип (' . $type . ')');
    }
}

function err($msg = '', $log = true)
{
    global $chat_id;
    $c = debug_backtrace();
    if ($log) {
        $errNum = query('INSERT INTO `s_error` (`chat_id`, `log`) VALUES (:chat_id, :log)', [':chat_id' => $chat_id, ':log' => json_encode($c)]);
        $msg .= ' (err: ' . $errNum . ')';
    }

    tgMess($msg);
}
function bterr($text=false,$code='',$desc='',$json=false){
    $e=['err' => $text?:'error', 'err_code'=> $code, 'err_desc'=> $desc];
    return $json?json_encode($e):$e;
}
function response_if_error($e)
{
    echo bterr($e,'','',1);
}
function br($text = '')
{
    echo "$text<br>";
}

function last_step($arr = false) {
    $row = singleQuery( 'SELECT position, parameters FROM steps s WHERE id = (SELECT max(id) id FROM steps WHERE id_chat = '.CHAT_ID().')' );
    $response = [];
    if ($row) {
        $response['position'] = $row['position'];
        $response['parameters'] = $row['parameters'];
    } else $response['position'] = false;
    return $arr ? $response : $response['position'];
}

function delete_last_step($id_chat = '')
{ # удаление последней записи
    query('SET @id = (SELECT max(id) id FROM steps WHERE id_chat = '.CHAT_ID().' AND BOT_ID = '.BOT_ID().')');
    query('DELETE FROM steps WHERE id = @id');
    query('SET @id = NULL;');
}

function set_pos($step, $par = [])
{
    query('DELETE FROM `steps` WHERE id_chat = '.CHAT_ID().' AND t_date < NOW() - INTERVAL 1 DAY');
    return query('INSERT INTO steps (`id_chat`, `parameters`, `position`, `BOT_ID`) VALUES ('.CHAT_ID().', ?, ?, '.BOT_ID().');', [ json_encode($par), $step ]);
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function get_chat($parameters = [])
{
    if ($res = singleQuery('SELECT * FROM chats WHERE techname = :techname OR id_chat = :id_chat', [':id_chat' => $parameters['id_chat'], 'techname' => $parameters['techname']])) {
        $res['success'] = true;
    } else return ['success' => false];
    return $res;
}

function multyArraySearchVal($text, $array)
{
    foreach ($array as $key => $val) if (array_search($text, $val) != '') return $key;
    return -1;
}

function multyArraySearchKey($text, $array)
{
    foreach ($array as $key => $val) if (isset($val[$text])) return $key;
    return -1;
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function text_for_the_db($text)
{
    $text = str_replace("\\", "\\\\", $text);
    $text = str_replace("'", "\'", $text);
    $text = str_replace("--", "-", $text);
    return $text;
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function t_log($text, $append, $name)
{
    $file=$_SERVER['DOCUMENT_ROOT'] . '/log.html';
    if($name){
        if(!is_dir(($d=$_SERVER['DOCUMENT_ROOT'].'/files/logs'))) mkdir($d);
        $file=$d.'/'.$name.'.html';
    }
    if (!$append) file_put_contents($file, '<head><meta charset="utf-8"></head><pre>');
    if (is_array($text)) file_put_contents($file, date('Y-m-d H:i:s') . ' ' . htmlspecialchars(print_r($text, true)) . PHP_EOL, FILE_APPEND);
    else file_put_contents($file, date('Y-m-d H:i:s') . ' ' . htmlspecialchars($text) . PHP_EOL, FILE_APPEND);
}

function qwer($text,$n=false)
{
    t_log($text, true,$n);
}

function qwe($text,$n=false)
{
    t_log($text, false,$n);
}

function getStrBetween($string, $from, $to)
{
    if (!strpos(' ' . $string, $from)) return '';
    $prepared = substr($string, stripos($string, $from) + strlen($from));
    $returned = substr($prepared, 0, (stripos($prepared, $to) - strlen($prepared) + strlen($to) - strlen($to)));
    return $returned;
}

function get_category($par = [])
{
    return categories()->get_category_func($par);
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function setting($key)
{
    global $bot_id, $bot_hash, $bot_rec;
    /*TODO: Отлавливать нормальный $bot_id*/
    if ($key == 'bot_key') {
        if (isset($bot_hash) && $bot_hash) {
            $bot_rec = $row = singleQuery('SELECT id, `bot_key` FROM bot WHERE hash = :key', [':key' => $bot_hash]);
            $bot_id = $row['id'];
            define('BOT_ID', $bot_id);
            return $row['bot_key'];
        }
    }
    $row = singleQuery('SELECT value FROM settings WHERE t_key = :key', [':key' => $key]);
    return $row['value'];
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function setUserSetting($par = [])
{
    # $par: ['chat_id'=> false, 'var'=> 'var', 'val'=> '', 'unique'=> true]
    if (!$par['var'] || !isset($par['val']))
        return;
    $chat_id = $par['chat_id'] ? $par['chat_id'] : $GLOBALS['chat_id'];

    if ($par['unique'])
        query('DELETE FROM `user_settings` WHERE variable = :variable AND id_chat = :id_chat', [':variable' => $par['var'], ':id_chat' => $chat_id]);

    query('INSERT INTO `user_settings` (`variable`, `value`, `id_chat`) VALUES (:variable, :value, :id_chat)', [':variable' => $par['var'], ':value' => $par['val'], ':id_chat' => $chat_id]);

}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function gethash($key, $getarr = false)
{
    $row = singleQuery('SELECT u.* FROM users u
                        JOIN us_auth h ON h.hash = :hash AND h.us = u.id
                        WHERE u.t_login <> "" AND u.t_password <> "" AND h.status = 1', [':hash' => $key]);
    if ($getarr) {
        $res = array();
        if ($row) {
            $res['success'] = true;
            $user_status = $row['status'];
            if ($user_status != 3 && $row['dep_access']) $user_status = 4;
            $res['dep_access'] = $row['dep_access'];
            $res['user_status'] = $user_status;
            $res['id_chat'] = $row['id_chat'];
            $res['user'] = $row['name'];
            $res['uid'] = $row['id'];
            $res['image'] = $row['image'];
        } else $res['success'] = false;
    } else $res = $row ? true : false;
    return $res;
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function permission_to_use()
{
    if (isset($_COOKIE['b2h']) && trim($_COOKIE['b2h']) != '') {
        $gethash = gethash($_COOKIE["b2h"], true);
        if ($gethash['success']) {
            $gethash['access'] = true;
            return $gethash;
        }
    }
    return ['access' => false, 'mess' => ['err' => 'не верные данные авторизации, попробуйте залогиниться заново']];
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function whmess($mess, $id_chat)
{
    return post_whatsapp(setting('api_url') . 'SendMessage/' . setting('token'), ["chatId" => $id_chat, "message" => $mess]);
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function post_whatsapp($url, $arguments)
{
    $myCurl = curl_init();
    $send_headers[] = 'Content-Type:application/json';
    curl_setopt_array($myCurl, array(CURLOPT_URL => $url, CURLOPT_HTTPHEADER => $send_headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($arguments)));
    $response = curl_exec($myCurl);
    curl_close($myCurl);
    return $response;
}

function randhash($b = 20)
{
    return bin2hex(random_bytes($b));
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function formate_link($uid = '', $username = '', $first_name = '')
{
    global $chat_id;
    if (!$uid) {
        $uid = $chat_id;
        $username = $GLOBALS['username'];
        $first_name = $GLOBALS['first_name'];
    }


    return $username ? "@$username" : "<a href=\"tg://user?id=$uid\">$first_name</a>";
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function web_link($uid, $username, $first_name)
{
    return "<a href='https://tlgrm.ru/user?id=$uid'>" . ($username ? $username : $first_name) . "</a>";
}

function get_admin($single)
{
    $res = [];
    $t = get_chat(['techname' => 'admin']);
    $id_chat = $t['id_chat'];
    if ($single) return $id_chat;
    $res['success'] = true;
    $res['id_chat'] = $id_chat;
    return $res;
}

function numbers($text)
{
    return text()->numbers($text);
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function letter($text, $arr = [ /* 'numbers'=>, 'U005F'=> нижнее подчеркивание _ */])
{

    if ($arr['numbers']) $num = '0-9';
    if ($arr['U005F']) $uf = '_';
    $p = "/[^a-zA-Zа-яА-Я$num$uf ]/ui";
    $res = preg_replace($p, ' ', $text);
    # убираем двойные пробелы
    while (strpos($res, '  ') !== false) $res = str_replace('  ', ' ', $res);
    return trim($res);
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function text_to_mach($text, $numbers = true)
{
    $par = [];
    if ($numbers) $par['numbers'] = true;
    //$text = letter($text, $par);
    $text = str_replace('*', '_matchstar', $text);
    $words_arr = explode(" ", $text);
    $t = "";
    for ($i = 0; $i < count($words_arr); $i++) {
        $r = $words_arr[$i];
        if (numbers($r) != '') continue;
        $words_arr[$i] = strlen($r) > 4 ? substr($r, 0, strlen($r) - 1) : $r;
    }
    foreach ($words_arr as $r) {
        $t = $t . ' ' . $r . (numbers($r) != '' ? '' : '*');
    }
    return trim($t);
}

# viber functions
# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function text_from_special_fulltext_field($text)
{
    $text = str_replace('*', '_matchstar', $text);
    return trim($text);
}

function sendReq($data, $resource)
{
    $request_data = json_encode($data);
    $ch = curl_init("https://chatapi.viber.com/pa/$resource");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        return $err;
    } else {
        return $response;
    }
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function dial($name, $lan = 1)
{
    return language()->get_message(['name' => $name]);
}

function DIALTEXT($name, $chat_id = false)
{
    $dial = language()->get_message(['name' => $name, 'chat_id' => $chat_id]);
    return $dial['body'];
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function vbMsg($sender_id, $text, $type, $keyboard = false, $rich_media = false, $contact = false, $tracking_data = Null, $arr_asoc = Null)
{
    global $auth_token, $send_name, $send_avatar;
    $data['auth_token'] = $auth_token;
    $data['min_api_version'] = '3';
    $data['receiver'] = $sender_id;
    $data['type'] = $type;
    switch ($type) {
        case 'text':
            $data['text'] = $text;
            break;
        case 'picture':
            if ($text['text']) $data['text'] = $text['text'];
            $data['media'] = $text['media'];
            $data['thumbnail'] = $text['thumbnail'];
            break;
        case 'contact':
            $data['contact'] = $text;
            break;
    }
    $data['sender']['name'] = $send_name;
    if ($keyboard) $data['keyboard'] = $keyboard;
    if ($contact) $data['contact'] = $contact;
    if ($rich_media) $data['rich_media'] = $rich_media;
    if ($send_avatar) $data['sender']['avatar'] = $send_avatar;
    if ($tracking_data != Null) {
        $data['tracking_data'] = $tracking_data;
    }
    if ($arr_asoc != Null) {
        foreach ($arr_asoc as $key => $val) {
            $data[$key] = $val;
        }
    }
    return sendReq($data, "send_message");
}

############################# SEND MESSAGE #
function send_mess($par)
{
    global $chat_id, $Telegram_botkey;

    $filehost = _dir_;
    $res_arr = [];
    $curlPar = [];
    $host = 'https://api.telegram.org/bot' . $Telegram_botkey;
    $curlPar['chat_id'] = $par['id_chat'] ?: $chat_id;
    $rest=false;

    if (isset($par['reply_to_message_id'])) $curlPar['reply_to_message_id'] = $par['reply_to_message_id'];
    $curlPar['disable_notification']=$par['disable_notification']??false;
    $curlPar['disable_web_page_preview']=$par['disable_web_page_preview']??false;
    $kb=$par['inline_keyboard']??($par['kb']??false);


    # сначала сотавляем структуру сообщения
    $st_text = 0; # текст отсутствует
    $body = '';
    $files = [];

    if ($par['files'] && !is_numeric($par['files'])) $par['files'] = [$par['files']];

    if (isset($par['npm'])) $par['body'] = $par['npm']['body'];
    if(isset($par['body']))$par['body']=trim($par['body']);
    if (is_array($par['files'])) {

        foreach ($par['files'] as $it) {

            if (!pathinfo($it, PATHINFO_EXTENSION)) {
                $itype = exif_imagetype($it);
                switch ($itype) {
                    case 2:
                    case 3:

                        $file = 'files/loaded/' . preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '', parse_url($it)['host'] . parse_url($it)['path']) . ($itype == 2 ? '.jpg' : '.png');

                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $file)) {
                            if (!copy($it, $_SERVER['DOCUMENT_ROOT'] . '/' . $file) || !chmod($_SERVER['DOCUMENT_ROOT'] . '/' . $file, 0644)) {
                                continue 2;
                            }

                        }
                        $it = $file;
                        break;
                    default:
                        continue 2;
                }

            }

            $files[] = ['id_file' => 0, 'type_file' => 'img', 'small_size' => $it, 'medium_size' => $it, 'large_size' => $it];
        }
    } elseif ($par['files'] != '' && (int)$par['files'] > 0) {
        $files = arrayQuery("SELECT id, id_file, type_file, small_size, medium_size, large_size FROM files WHERE id_group = '{$par['files']}' LIMIT 10");
    }

    $format_text = $par['format_text'];
    if (isset($par['body']) && $par['body'] != '') {
        $curlPar['parse_mode'] = strpos($par['body'],'</')?'html': '';

        $st_text = 1;
        $rar = array_filter(explode('||', $par['body']), function($k) {
            return trim($k);
        });
        $body=$rar[array_rand($rar)];
        if ($format_text) {
            $body = str_replace('<p', "\n<p", $body);
            $body = str_replace('<br>', "\n", $body);
            $body = str_replace('&nbsp;', ' ', $body);
            $body = str_replace('<li', "\n● <li", $body);
            $links = [];
            $link = 'link';
            while ($link !== '') {
                $link = getStrBetween($body, '<a ', '>');
                if (!strpos($link, 'style')) break;
                $href = getStrBetween($link, 'href="', '"');
                $body = str_replace('<a ' . $link . '>', '[link' . count($links) . ']', $body);
                array_push($links, $href);
            }
            for ($i = 0; $i < count($links); $i++) {
                $body = str_replace("[link$i]", '<a href="' . $links[$i] . '">', $body);
            }

            $img = '';
            while ($img !== '') {
                $img = getStrBetween($body, '<img', '>');
                if ($img == '') break;
                $link = getStrBetween($img, 'src="', '"');
                if (!$link) $link = getStrBetween($img, 'src=\'', '\'');
                if ($link) {
                    array_push($files, ['id_file' => 0, 'type_file' => 'img', 'small_size' => $link, 'medium_size' => $link, 'large_size' => $link]);
                    $body = str_replace('<img' . $img . '>', ' 🏞[изображение ' . count($files) . ']', $body);
                }
            }
            $countImgArr = count($files);
            if ($countImgArr) {
                $countImgArr == 1 ? 1 : 2;
            }

            $body = strip_tags($body, '<i><a><b><u><s><code>');
        }
    }

    switch (count($files)) {
        case 0:
            $st_files = 0;
            break; # файлы отсутствуют
        case 1:
            $st_files = 1;
            break; # один файл
        default:
            $st_files = 2; # несколько файлов
    }


    $maxlen=$st_files !== 0?1000:4095;
    if (mb_strlen($body) > $maxlen){
        $len= mb_strrpos(mb_substr($body, 0,$maxlen), " ")?:$maxlen;
        $rest=['body'=> '...'.mb_substr($body, $len, 100000), 'id_chat'=> $curlPar['chat_id']];
        $rest['disable_notification']=$par['disable_notification'];
        $rest['disable_web_page_preview']=$par['disable_web_page_preview'];

        //  kb reply_markup

        $body = mb_strimwidth($body, 0, $len+3, "...");
    }
    if($rest) {
        if ($kb) $rest['kb'] = $kb;
    } else {
        if ($kb) $curlPar['reply_markup'] = json_encode($kb);
    }

    foreach ($files as $key => $file) {
        $orgfile = $file['small_size'] ?: $file['large_size'];

        if ($file['type_file'] == 'doc') {
            if (!$orgfile) continue;
            $pth=pathinfo($orgfile, PATHINFO_EXTENSION);
            if($pth==='mp3'){
                $files[$key]['type_file']='audio';
                continue;
            }

            if (in_array($pth, ['pdf', 'zip', 'gif']) || strpos($orgfile, 'http') !== false) continue;
            $turl = '/files/loaded/' . uniqid() . '.zip';
            zip($_SERVER['DOCUMENT_ROOT'] . '/' . $orgfile, $_SERVER['DOCUMENT_ROOT'] . $turl);
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $turl)) {
                $files[$key]['small_size'] = $filehost . $turl;
                $files[$key]['delete'] = $_SERVER['DOCUMENT_ROOT'] . $turl;
            }
        }

        if (pathinfo($orgfile, PATHINFO_EXTENSION) === 'gif') {
            $files[$key]['type_file'] = 'animation';
        }
    }

    $curlPar['text'] = $body;
    if ($st_text == 1 && $st_files == 0) { #если только текст
        $json = curlSend($host . '/sendMessage', $curlPar);
        $json = json_decode($json, true);
        if ($json['error_code'] && $json['description']) notification(if_error_code($json));
        else array_push($res_arr, array('message_id' => $json['result']['message_id'], 'channel_id' => $json['result']['chat']['id'], 'channel_name' => $json['result']['chat']['username'], 'type' => 1));
    }
    if ($st_files == 1) { #если только одна картинка или файл
        $file = $files[0];
        $link = $file['large_size'] ?: ($file['medium_size'] ?: $file['small_size']);
        if(in_array(strtolower(pathinfo($link, PATHINFO_EXTENSION)), ['jpeg', 'jpg']))
            $link = $file['medium_size'] ?: ($file['small_size'] ?: $file['large_size']);

        if (!strpos(' ' . $link, 'http')) $link = $filehost . "/" . $link;
        $id_file_in_telegram = $file['id_file'];
        $curlPar['caption'] = $body ?: '';
        $json = false;

        switch ($file['type_file']) {
            case 'img':
                if ($id_file_in_telegram) {
                    $curlPar['photo'] = $id_file_in_telegram;
                    $json = curlSend($host . '/sendPhoto', $curlPar);
                    $json = json_decode($json, true);
                }
                if (!$json['ok']) {
                    $curlPar['photo'] = $link;
                    $json = curlSend($host . '/sendPhoto', $curlPar);
                    $json = json_decode($json, true);
                    if ($file['id'] && ($tgfid = $json['result']['photo'][0]['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                }

                break;
            case 'doc':

                if ($id_file_in_telegram) {
                    $curlPar['document'] = $id_file_in_telegram;
                    $json = curlSend($host . '/sendDocument', $curlPar);
                    $json = json_decode($json, true);
                }
                if (!$json) {
                    $curlPar['document'] = $link;
                    $json = curlSend($host . '/sendDocument', $curlPar);
                    $json = json_decode($json, true);
                    if ($file['id'] && ($tgfid = $json['result']['document']['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                }
                break;
            case 'audio':

                if ($id_file_in_telegram) {
                    $curlPar['audio'] = $id_file_in_telegram;
                    $json = curlSend($host . '/sendAudio', $curlPar);
                    $json = json_decode($json, true);
                }
                if (!$json) {
                    $curlPar['audio'] = $link;
                    $json = curlSend($host . '/sendAudio', $curlPar);
                    $json = json_decode($json, true);
                    if ($file['id'] && ($tgfid = $json['result']['audio']['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                }
                break;
            case 'video':
                if ($id_file_in_telegram) {
                    $curlPar['video'] = $id_file_in_telegram;
                    $json = curlSend($host . '/sendVideo', $curlPar);
                    $json = json_decode($json, true);
                }
                if (!$json) {
                    $curlPar['video'] = $link;
                    $json = curlSend($host . '/sendVideo', $curlPar);
                    $json = json_decode($json, true);
                    if ($file['id'] && ($tgfid = $json['result']['video']['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                }

                break;
            case 'animation':
                if ($id_file_in_telegram) {
                    $curlPar['animation'] = $id_file_in_telegram;
                    $json = curlSend($host . '/sendAnimation', $curlPar);
                    $json = json_decode($json, true);
                }
                if (!$json) {
                    $curlPar['animation'] = $link;
                    $json = curlSend($host . '/sendAnimation', $curlPar);
                    $json = json_decode($json, true);
                    if ($file['id'] && ($tgfid = $json['result']['animation']['file_id'])) query('UPDATE `files` SET `id_file` = ? WHERE id=?', [$tgfid, $file['id']]);
                }
                break;
        }

        if ($json['error_code'] && $json['description']) notification(if_error_code($json));
        else array_push($res_arr, array('message_id' => $json['result']['message_id'], 'channel_id' => $json['result']['chat']['id'], 'channel_name' => $json['result']['chat']['username'], 'type' => 3));


    }
    if ($st_files == 2) {
        $mediaarr = [];
        foreach ($files as $file) {

            if($file['id_file']){
                $link=$file['id_file'];
            }else{
                $link = $file['large_size'] ?: ($file['medium_size'] ?: $file['small_size']);
                if(in_array(strtolower(pathinfo($link, PATHINFO_EXTENSION)), ['jpeg', 'jpg']))
                    $link = $file['medium_size'] ?: ($file['small_size'] ?: $file['large_size']);
                if (strpos($link, 'http') === false) $link = $filehost . "/" . $link;
            }


            switch ($file['type_file']) {
                case 'img':
                    array_push($mediaarr, array('type' => 'photo', 'media' => $link));
                    break;
                case 'doc':
                    array_push($mediaarr, array('type' => 'document', 'media' => $link));
                    break;
                case 'video':
                case 'animation':
                    array_push($mediaarr, array('type' => 'video', 'media' => $link));
                    break;
            }
        }

        if (!$kb) {
            $mediaarr[0]['caption'] = $curlPar['text'];
            $mediaarr[0]['parse_mode'] = $curlPar['parse_mode']??'';
        }

        $temp = $curlPar;
        $temp['media'] = json_encode($mediaarr);
        $json_media = curlSend($host . '/sendMediaGroup', $temp);
        # записываем медиа после текстового файла
        $json_media = json_decode($json_media, true);
        if ($json_media['error_code'] && $json_media['description']) notification($json_media['description']);
        foreach ($json_media['result'] as $r_media) {
            array_push($res_arr, array('message_id' => $r_media['message_id'], 'channel_id' => $r_media['chat']['id'], 'channel_name' => $r_media['chat']['username'], 'type' => 5));
            $rplMsg = $r_media['message_id'];
        }

        if ($kb) {

            $json = curlSend($host . '/sendMessage', $curlPar);
            $json = json_decode($json, true);
            array_push($res_arr, array('message_id' => $json['result']['message_id'], 'channel_id' => $json['result']['chat']['id'], 'channel_name' => $json['result']['chat']['username'], 'type' => 1));
        }


    }

    if($rest) $res_arr=array_merge($res_arr,send_mess($rest));

    foreach ($files as $file)
        if ($file['delete']) unlink($file['delete']);

    return $res_arr;
}

function ex($t='exit'){
    tgmess($t);exit;
}

function if_error_code($err)
{
    global $chat_id, $from_id, $obj, $first_name;
    $first_name = $obj['message']['from']['first_name'];
    $lan = $obj['message']['from']['language_code'];
    if ($first_name) $first_name = $first_name . ', ';

    if ($err['chat_id'] != $chat_id && $err['chat_id'] != $from_id) return $err['description'] . ' (' . text()->userLink($err['chat_id']) . ')';

    switch ($err['error_code']) {
        case 403:
            //пользователь заблокировал бота
            return $lan == 'ru' ?
                $first_name . 'я не могу тебе помочь😔' . PHP_EOL . 'ты заблокировал меня❌' :
                $first_name . 'i cant help you😔' . PHP_EOL . 'you blocked me❌';
            break;
        default:
            return $err['description'];
    }

}

function getUserProfilePhotos($chat_id)
{
    global $tgHost, $curl;
    curl_setopt_array($curl, array(CURLOPT_URL => $tgHost . '/getUserProfilePhotos', CURLOPT_POST => TRUE, CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_TIMEOUT => 10, CURLOPT_POSTFIELDS => ['user_id' => $chat_id],));
    $res = curl_exec($curl);
    return $res;
}

function getFile($file_id)
{
    global $tgHost, $curl;
    curl_setopt_array($curl, array(CURLOPT_URL => $tgHost . '/getFile', CURLOPT_POST => TRUE, CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_TIMEOUT => 10, CURLOPT_POSTFIELDS => array('file_id' => $file_id,),));
    $res = curl_exec($curl);
    return $res;
}

function setData($data)
{
    if (is_array($data)) {
        $data = json_encode($data);
        $type = 'array';
    } else $type = 'text';
    $id = query('INSERT INTO `s_other_data` (`data`, `type`) VALUES (:data, :type)', [':data' => $data, ':type' => $type]);
    return $id;
}

function getData($dataId)
{
    $res = singleQuery('SELECT data, type FROM `s_other_data` WHERE id = :id', [':id' => $dataId]);
    if ($res) {
        $data = $res['data'];
        if ($res['type'] == 'array') $data = json_decode($data, true);
    }
    return $res ? $data : false;
}

function updateData($dataId, $data)
{
    if (is_array($data)) {
        $data = json_encode($data);
        $type = 'array';
    } else $type = 'text';
    query('UPDATE `s_other_data` SET `data` = :data, `type` = :type WHERE id = :id', [':data' => $data, ':type' => $type, ':id' => $dataId]);
    return false;
}

function deleteData($dataId)
{
    query('DELETE FROM `s_other_data` WHERE id = :id', [':id' => $dataId]);
}

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function formateUserLink($uid, $username, $first_name)
{
    return $username ? "@$username" : "<a href=\"tg://user?id=$uid\">$first_name</a>";
}

function intermediate_function($par)
{
    $intermediate_functions = $par['intermediate_function'];
    foreach ($intermediate_functions as $intermediate_function) {
        $intermediate_function_name = $intermediate_function['function_name'];
        if ($intermediate_function_name) {
            $f = get_custom_function($intermediate_function_name);
            if ($f) {
                $b = $f['funcName']($par);
                if (!$b) return;
            }
        }
    }
    return true;
}

function multi_array_search($arr, $col, $search)
{
    $result = false;
    foreach ($arr as $key => $val) {
        if ($val[$col] == $search) {
            $result = $key;
            break;
        }
    }
    return $result;
}


function notification($text, $show_alert = 1)
{
    global $original;
    if (isset($original['callback_query'])) methods()->answerCallbackQuery($text, $original['callback_query']['id'], $show_alert);
    else tgMess($text);
}

function tgMess($text)
{
    global $tgHost, $chat_id;
    //if(is_array($text)) $text=print_r($text,1);
    return curl()->post($tgHost . '/sendMessage', ['text' => $text, 'chat_id' => $chat_id, 'parse_mode' => strpos($text,'</')?'html': '']);
}

function delete_steps()
{
    global $chat_id;
    query('DELETE FROM steps WHERE id_chat = :id_chat', [':id_chat' => $chat_id]);
}

############################### GET CUSTOM FUNCTION #
function get_custom_function($id)
{
    if (!$id) return false;
    $row = singleQuery('SELECT techname, dir FROM `s_functions` WHERE id = :id OR techname = :id', [':id' => $id]);
    if (!$row) return false;
    $dir = $_SERVER['DOCUMENT_ROOT'].'/SECRETFOLDER/'.$row['dir'] . $row['techname'] . ".php";
    if (file_exists($dir)) {
        include_once($dir);
        return ['funcName' => $row['techname']];
    } else return false;
}

############################### GO TO CUSTOM FUNCTION #
function go_to_custom_function($id, $par = [])
{

    $func = get_custom_function($id);
    if (!$func)
        return err('Функция не найдена!.');
    $func = $func['funcName'];
    $func($par);
}

############################### GO TO BLOCKCHAIN #
function go_to_blockchain($id, $par = [])
{
    $blockchain = singleQuery('SELECT techname FROM `constructors` WHERE id = :id OR techname = :id', [':id' => $id]);
    if (!$blockchain)
        return err('Сценарий не найден!');
    the_distribution_module($blockchain['techname'], $par);

}

############################### THE DISTRIBUTION MODULE #

function zip($source, $destination)
{
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', DIRECTORY_SEPARATOR, realpath($source));
    $source = str_replace('/', DIRECTORY_SEPARATOR, $source);

    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source),
            RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);

            if ($file == '.' || $file == '..' || empty($file) || $file == DIRECTORY_SEPARATOR) {
                continue;
            }
            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1), array('.', '..'))) {
                continue;
            }

            $file = realpath($file);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);

            if (is_dir($file) === true) {
                $d = str_replace($source . DIRECTORY_SEPARATOR, '', $file);
                if (empty($d)) {
                    continue;
                }
                $zip->addEmptyDir($d);
            } elseif (is_file($file) === true) {
                $zip->addFromString(str_replace($source . DIRECTORY_SEPARATOR, '', $file),
                    file_get_contents($file));
            } else {
                // do nothing
            }
        }
    } elseif (is_file($source) === true) {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}


function file_force_download($file)
{
    if (file_exists($file)) {
        // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
        // если этого не сделать файл будет читаться в память полностью!

        // заставляем браузер показать окно сохранения файла
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));


        // читаем файл и отправляем его пользователю
        readfile($file);
        exit;
    }
}

function removeDirectory($path)
{
    if (file_exists($path) and is_dir($path)) {
        // открываем папку
        $dir = opendir($path);
        while (false !== ($element = readdir($dir))) {
            // удаляем только содержимое папки
            if ($element != '.' and $element != '..') {
                $tmp = $path . '/' . $element;
                chmod($tmp, 0777);
                // если элемент является папкой, то
                // удаляем его используя нашу функцию removeDirectory
                if (is_dir($tmp)) {
                    removeDirectory($tmp);
                    // если элемент является файлом, то удаляем файл
                } else {
                    unlink($tmp);
                }
            }
        }
        // закрываем папку
        closedir($dir);
        // удаляем саму папку
        if (file_exists($path)) {
            rmdir($path);
        }
    }
}

function glob_tree_dirs($path, $_base_path = null)
{
    global $ignore;
    if (is_null($_base_path)) {
        $_base_path = '';
    } else {
        $_base_path .= basename($path) . '/';
    }

    $out = array();
    foreach (glob($path . '/*', GLOB_ONLYDIR) as $file) {
        if (is_dir($file) && in_array($_base_path . basename($file), $ignore) === false) {
            $out[] = $_base_path . basename($file);
            $out = array_merge($out, glob_tree_dirs($file, $_base_path));
        }
    }

    return $out;
}

############################## РАСЧЕТ ЗНАЧЕНИЯ ИЗ СТРОКИ С ФОРМУЛОЙ #
// Исключения для парсера выражений
class AriphmeticException extends Exception
{
    function __construct($msg, $code)
    {
        return parent::__construct($msg, $code);
    }

    function __toString()
    {
        return get_class($this) . '(' . $this->code . '): ' . $this->message;
    }
}

// Собственно сам вычислитель выражений
function calculate($statement)
{
    if (!is_string($statement)) {
        throw new AriphmeticException('Wrong type', 1);
    }
    $calcQueue = array();
    $operStack = array();
    $operPriority = array('(' => 0, ')' => 0, '+' => 1, '-' => 1, '*' => 2, '/' => 2,);
    $token = '';
    foreach (str_split($statement) as $char) {
        // Если цифра, то собираем из цифр число
        if ($char >= '0' && $char <= '9') {
            $token .= $char;
        } else {
            // Если число накопилось, сохраняем в очереди вычисления
            if (strlen($token)) {
                array_push($calcQueue, $token);
                $token = '';
            }
            // Если найденный символ - операция (он есть в списке приоритетов)
            if (isset($operPriority[$char])) {
                if (')' == $char) {
                    // Если символ - закрывающая скобка, переносим операции из стека в очередь вычисления пока не встретим открывающую скобку
                    while (!empty($operStack)) {
                        $oper = array_pop($operStack);
                        if ('(' == $oper) {
                            break;
                        }
                        array_push($calcQueue, $oper);
                    }
                    if ('(' != $oper) {
                        // Упс! А открывающей-то не было. Сильно ругаемся (18+)
                        throw new AriphmeticException('Unexpected ")"', 2);
                    }
                } else {
                    // Встретили операцию кроме скобки. Переносим операции с меньшим приоритетом в очередь вычисления
                    while (!empty($operStack) && '(' != $char) {
                        $oper = array_pop($operStack);
                        if ($operPriority[$char] > $operPriority[$oper]) {
                            array_push($operStack, $oper);
                            break;
                        }
                        if ('(' != $oper) {
                            array_push($calcQueue, $oper);
                        }
                    }
                    // Кладем операцию на стек операций
                    array_push($operStack, $char);
                }
            } elseif (strpos(' ', $char) !== FALSE) {
                // Игнорируем пробелы (можно добавить что еще игнорируем)

            } else {
                // Встретили что-то непонятное (мы так не договаривались). Опять ругаемся
                throw new AriphmeticException('Unexpected symbol "' . $char . '"', 3);
            }
        }
    }
    // Вроде все разобрали, но если остались циферки добавляем их в очередь вычисления
    if (strlen($token)) {
        array_push($calcQueue, $token);
        $token = '';
    }
    // ... и оставшиеся в стеке операции
    if (!empty($operStack)) {
        while ($oper = array_pop($operStack)) {
            if ('(' == $oper) {
                // ... кроме открывающих скобок. Это верный признак отсутствующей закрывающей
                throw new AriphmeticException('Unexpected "("', 4);
            }
            array_push($calcQueue, $oper);
        }
    }
    $calcStack = array();
    // Теперь вычисляем все то, что напарсили
    // Тут ошибки не ловил, но они могут быть (это домашнее задание)
    foreach ($calcQueue as $token) {
        switch ($token) {
            case '+':
                $arg2 = array_pop($calcStack);
                $arg1 = array_pop($calcStack);
                array_push($calcStack, $arg1 + $arg2);
                break;
            case '-':
                $arg2 = array_pop($calcStack);
                $arg1 = array_pop($calcStack);
                array_push($calcStack, $arg1 - $arg2);
                break;
            case '*':
                $arg2 = array_pop($calcStack);
                $arg1 = array_pop($calcStack);
                array_push($calcStack, $arg1 * $arg2);
                break;
            case '/':
                $arg2 = array_pop($calcStack);
                $arg1 = array_pop($calcStack);
                array_push($calcStack, $arg1 / $arg2);
                break;
            default:
                array_push($calcStack, $token);
        }
    }
    return array_pop($calcStack);
}
