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
    if(empty(\$_SERVER['PATH_INFO'])){
        header(sprintf('Location: http://%s%s/',
            \$_SERVER['HTTP_HOST'],
            \$_SERVER["PHP_SELF"]
        ));
        exit;
    }
    \$p = preg_replace('#^/+#', '', \$_SERVER['PATH_INFO']);
    if (\$p == '') \$p = 'index.html';
    return '/public/'.\$p;
}

function authenticate(\$ac){
    if (!\$ac) return;

    \$u = \$ac['username'];
    \$p = \$ac['password'];
    if (\$_SERVER['PHP_AUTH_USER'] !== \$u  ||
        crypt(\$_SERVER['PHP_AUTH_PW'], \$p) != \$p){
        header('WWW-Authenticate: Basic realm="phexrep"');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
}

\$conf = parse_ini_file('phar://'.__FILE__.'/config.ini', true);
authenticate(\$conf['access_control']);


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
