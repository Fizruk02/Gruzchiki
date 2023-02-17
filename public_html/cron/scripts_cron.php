<?
    $_SERVER['DOCUMENT_ROOT'] = __DIR__.'/../public_html';
    require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
    require $_SERVER['DOCUMENT_ROOT'].'/../src/project/modules/scripts/scripts.php';
    use project\modules\scripts\scripts as scripts;
    
    
    $arr=arrayQuery('SELECT id, id_user, go_to_script FROM `script_block_control` WHERE status = 0 AND date_go_to_script < NOW()');
    foreach($arr as $row){
        $GLOBALS['chat_id'] = $row['id_user'];
        scripts::goTo($row['go_to_script']);
        query('UPDATE `script_block_control` SET status=1 WHERE id=?',[$row['id']]);
    }