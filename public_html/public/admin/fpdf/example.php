<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
require 'vendor/autoload.php';
define('FPDF_FONTPATH',"vendor/setasign/fpdf/font/");

$MARGIN_LR = 18;
$MARGIN_T = 9;
$PAGE_WIDTH = 210;
$PAGE_HEIGHT = 297;
$PAGE_WIDTH-$MARGIN_LR*2;
$WORK_WIDTH = $PAGE_WIDTH-$MARGIN_LR*2;
 

$pdf = new FPDF('P','mm','A4'); # $pdf = new FPDF('P', 'pt', 'Letter');


 /**
  * FONTS
  * Шрифты создавать здесь
  * http://www.fpdf.org/makefont/make.php
    //$pdf->SetDisplayMode(real,'default');
    $pdf->AddFont('Arial','','arial.php'); 
    $pdf->AddFont('ArialB','','arial_bold.php');
    $pdf->AddFont('os-Bold','','OpenSans-Bold.php');
    $pdf->AddFont('os-BoldItalic','','OpenSans-BoldItalic.php');
    $pdf->AddFont('os-ExtraBold','','OpenSans-ExtraBold.php');
    $pdf->AddFont('os-ExtraBoldItalic','','OpenSans-ExtraBoldItalic.php');
    $pdf->AddFont('os-Italic','','OpenSans-Italic.php');
    $pdf->AddFont('os-Light','','OpenSans-Light.php');
    $pdf->AddFont('os-LightItalic','','OpenSans-LightItalic.php');
    $pdf->AddFont('os-Regular','','OpenSans-Regular.php');
    $pdf->AddFont('os-Semibold','','OpenSans-Semibold.php');
    $pdf->AddFont('os-SemiboldItalic','','OpenSans-SemiboldItalic.php');
  * 
  */


$pdf->SetMargins($MARGIN_LR,$MARGIN_T,$MARGIN_LR);
$pdf->AddPage('P','A4'); 
$pdf ->SetAutoPageBreak(false);
$border = 0;



/**
 * 
    $pdf->Image('img2.png',0,110,210,0,'PNG');
    $pdf->Image('img1.jpg',149.8,2.2,42.6,0,'JPG', 'https://www.site.ru'); # с ссылкой
 * 
 */

/**
 * 
    $pdf->SetFont('os-Semibold','', 30);
    $pdf->SetTextColor(223,110,43); # $pdf->SetTextColor(0,0,0);
    $pdf->Cell(6, 16, '/ ', $border,0,'L',0);
    $pdf->Ln(1);
    $pdf->SetFont('os-Bold','', 15);

    $pdf->Ln(3);
    
    
    utf('кириллица');
    $pdf->Cell(40, 5, utf('E. office@email.com'), $border,0,'L',0, "mailto:office@email.com");
    $pdf->Cell(40, 5, utf('www.site.ru'), $border,0,'L',0, "www.site.ru");
    
    
    $t='Строка 1'.PHP_EOL.
    'Строка 2'.PHP_EOL.
    'Строка 3'.PHP_EOL.
    'Строка 4';
    $pdf->MultiCell(0, 4, utf($t), $border);
    
    
    $pdf -> SetXY(1, 1);
    $pdf -> SetX(2, 2);
 * 
 */

/**
$filename=uniqid();

if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/files/pdf')) mkdir($_SERVER['DOCUMENT_ROOT'].'/files/pdf');
$file = '/files/pdf/'.$filename.'.pdf';

$pdf->Output($_SERVER['DOCUMENT_ROOT'].$file,'F');
*/
echo json_encode([
     'success'=> 'ok'
    ,'file'=> $file
    ,'url'=> _dir_.$file
]);

function utf($t){
    return iconv( 'utf-8','windows-1251', $t);
}
