<?php
/* 
 * diese PHP 7-Funktionen werden verwendet und müssen ggf. eingebunden werden:
 
if (phpversion() < '7') {
    require_once 'php/php7.php';
}
  
 */

function intdiv($dividend, $divisor) {
    return ($dividend - $dividend % $divisor) / $divisor;
}