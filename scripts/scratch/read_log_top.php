<?php
$lines = file('storage/logs/laravel.log');
$last_lines = array_slice($lines, -250);
echo implode("", array_slice($last_lines, 0, 80));
