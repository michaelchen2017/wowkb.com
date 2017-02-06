<?php
include '../inc.comm.php';
// If you need to parse XLS files, include php-excel-reader
require('php-excel-reader/excel_reader2.php');

require('SpreadsheetReader.php');

$Reader = new SpreadsheetReader('wuliao.xlsx');
// debug::d($Reader);exit;
foreach ($Reader as $Row)
{
	debug::d($Row);
// 	print_r($Row);
}
echo count($Reader);
?>