<?php
require '../vendor/autoload.php';

use Carbon\Carbon;

$dt = Carbon::now();
echo $dt->year.'年';
$dt = Carbon::now();
echo $dt->month.'月';
$dt = Carbon::now();
echo $dt->day.'日';


$dt = Carbon::now();
echo '時間：'.$dt->hour.'時';
$dt = Carbon::now();
echo $dt->minute.'分';
