<?php
function days_in_month($month, $year) { 
 return date('t', mktime(0, 0, 0, $month+1, 0, $year)); 
}