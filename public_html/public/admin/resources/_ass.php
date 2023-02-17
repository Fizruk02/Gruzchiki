<!--<link href="//fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">-->
<!--<link rel="stylesheet" href="//stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">-->
<!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">-->
<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">-->
<!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">-->
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>-->
<!--<script src="/admin/js/jquery.min.js"></script>-->
<!--<meta charset="utf-8">-->
<!--<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">-->
<!--<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />-->



<link href="//fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">
<script src="/admin/js/jquery.min.js"></script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


<?php


$srcs=[
    'stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
    'cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css',
    'cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css',

    'cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css',
    'cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/fonts/bootstrap-icons.woff2?524846017b983fc8ded9325d94ed40f3',
    'cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/fonts/bootstrap-icons.woff?524846017b983fc8ded9325d94ed40f3',

    'cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',
    'cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',

];


foreach($srcs as $src) downloadTheLibrary($src);

function downloadTheLibrary($src){
    $d=$_SERVER['DOCUMENT_ROOT'].'/admin/vendor/';
    if(!is_dir($d)) mkdir($d, 0777);
    
    $pathinfo = pathinfo($src);
    $dirname=$d.$pathinfo['dirname'];
    $basename=$pathinfo['basename'];
    if(strpos($basename,'?')) $basename=explode('?', $basename)[0];
    if(!is_file($dirname.'/'.$basename)){
        mkdir($dirname, 0755, 1);
        copy('https://'.$src, $dirname.'/'.$basename);
    }

    switch($pathinfo['extension']){
        case 'css':
        case 'scss':
        case 'woff2':
        echo '<link href="/admin/vendor/'.$pathinfo['dirname'].'/'.$basename.'" rel="stylesheet">'.PHP_EOL;
            break;
        case 'js':
            echo '<script src="/admin/vendor/'.$pathinfo['dirname'].'/'.$basename.'"></script>'.PHP_EOL;
            break;
    }
}

?>
