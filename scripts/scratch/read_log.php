<?php
$lines = file('storage/logs/laravel.log');
$last_lines = array_slice($lines, -150);
echo implode("", $last_lines);
