<?php
require 'vendor/autoload.php';

use PHPUnit\Event\Emitter;
use PHPUnit\Event\DispatchingEmitter;

$interface = new ReflectionClass(Emitter::class);
$class = new ReflectionClass(DispatchingEmitter::class);

$interfaceMethods = $interface->getMethods();
$missing = [];

foreach ($interfaceMethods as $im) {
    if (!$class->hasMethod($im->getName())) {
        $missing[] = $im->getName() . " (Missing)";
        continue;
    }
    
    $cm = $class->getMethod($im->getName());
    
    // Check parameters
    $ips = $im->getParameters();
    $cps = $cm->getParameters();
    
    if (count($ips) !== count($cps)) {
        $missing[] = $im->getName() . " (Parameter count mismatch)";
        continue;
    }
    
    for ($i = 0; $i < count($ips); $i++) {
        $ip = $ips[$i];
        $cp = $cps[$i];
        
        $it = $ip->getType();
        $ct = $cp->getType();
        
        if ((string)$it !== (string)$ct) {
            $missing[] = $im->getName() . " (Parameter $i type mismatch: '$it' vs '$ct')";
        }
        
        if ($ip->isVariadic() !== $cp->isVariadic()) {
            $missing[] = $im->getName() . " (Parameter $i variadic mismatch)";
        }
    }
}

if (empty($missing)) {
    echo "No mismatches found via Reflection!\n";
} else {
    echo "Mismatches found:\n";
    foreach ($missing as $m) {
        echo "- $m\n";
    }
}
