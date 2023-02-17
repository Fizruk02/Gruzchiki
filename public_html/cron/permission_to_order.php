<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/functions/functions.php';
require_once __DIR__ . '/../src/project/modules/project/project.php';

use project\modules\project\project;
set_time_limit(0);

$project = new project();
$project->sendOrder();

?>