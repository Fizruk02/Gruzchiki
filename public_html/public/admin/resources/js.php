<?php

$srcs=[
    'ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/jquery-ui.min.js',
    'cdnjs.cloudflare.com/ajax/libs/exceljs/3.8.2/exceljs.js',
    'cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.71/pdfmake.min.js',
    'cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.71/vfs_fonts.js',
    'cdn.jsdelivr.net/momentjs/latest/moment.min.js',
    'cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',
    //'cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
    'snipp.ru/cdn/jQuery-Form-Plugin/dist/jquery.form.min.js',
    'b2bot.ru/js/jscolor.min.js',
    'b2bot.ru/components/table.js',
    'b2bot.ru/components/grid.js',
    'b2bot.ru/components/toast.js',
    'b2bot.ru/components/upload/js.js',
    'b2bot.ru/components/b2.js',
    'b2bot.ru/components/qw.js',
    'b2bot.ru/components/dialogboxes.js',
    'b2bot.ru/components/tgeditor/js.js',
];


foreach($srcs as $src){
    downloadTheLibrary($src);

}

?>