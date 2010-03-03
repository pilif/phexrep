#!/usr/bin/env php
<?php

$base_dir = realpath(__DIR__.'/..');

$of = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'out.phar';
$alias = basename($of);
$preconfig = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '';
if ($preconfig && !file_exists($preconfig))
    die("Invalid configuration file: $preconfig\n");

if (file_exists($of))
    unlink($of);

$arch = new Phar($of, 0, $alias);
$arch->compressFiles(Phar::GZ);
#$p->setSignatureAlgorithm (Phar::SHA1);
$files = array();

$rd = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base_dir));
foreach($rd as $file) {
    $p = substr($file, strlen($base_dir));
    if (preg_match('#\.+$#', $file->getFilename()))
        continue;
    if (preg_match('#^/(\.[a-z]|scripts|config.ini)#', $p))
        continue;
    $files[$p] = (string)$file;
}
$arch->startBuffering();
$arch->buildFromIterator(new ArrayIterator($files));
if ($preconfig){
    $arch['config.ini'] = file_get_contents($preconfig);
    $arch->setStub(
<<<ENDSTUB
<?php

function rewrite(){
    \$p = preg_replace('#^/+#', '', \$_SERVER['PATH_INFO']);
    return '/public/'.\$p;
}

Phar::interceptFileFuncs();
Phar::webPhar("$alias", "/index.html", null, array(), 'rewrite');
echo "phexrep should be run through a webbrowser\n";
exit -1;
__HALT_COMPILER();
');
ENDSTUB
);
}
$arch->stopBuffering();
$arch = null;
