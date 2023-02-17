<?php
//$this->addBreadcrumb($title, $_SERVER['REQUEST_URI']);

if ($_SERVER['REQUEST_URI'] != '/' && $_SERVER['REQUEST_URI'] != '/?'.$_SERVER['QUERY_STRING'])
        $this->breadcrumbs();