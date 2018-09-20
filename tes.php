<?php
konsep pembenaran
$string = 'memakan saya apel';
$pattern = '/(?:memakan) (saya) (apel)/i';
$replacement = '$2 $1';
echo preg_replace($pattern, $replacement, $string);





?>
