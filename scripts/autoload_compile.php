#!/usr/bin/env php
<?

while(count($_SERVER['argv']) > 0){
    $el = array_shift($_SERVER['argv']);
    if (substr($el, 0, 1) == '-'){
        for($i = 1; $i < strlen($el); $i++){
            $options['-'.$el[$i]] = true;
        }
        $el = '';
    }
}
$skip_dirs = array('controllers');
$libdir = __DIR__."/../lib";
$outfile = __DIR__."/../lib/_autoload.php";


if ($options['-v'])
    echo "Compiling autoload-list for $libdir to $outfile\n";

$symtable = array();
$dup = array();

$skipre = count($skip_dirs) > 0 ? sprintf("#^(%s)#", implode('|', $skip_dirs)) : '';

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($libdir), RecursiveIteratorIterator::SELF_FIRST);
foreach($it as $ent){
    if ($ent->isDir()) continue;
    $rp = substr($ent->getPath(), strlen($libdir)+1);

    // skip non-popscan or autoincluded stuff
    if ($skipre && (preg_match($skipre, $rp) || preg_match($skipre, $ent->getFilename())))
        continue;

    // skip non php files
    if (!preg_match('#\.php$#', $ent->getFilename()))
        continue;

    if (!empty($rp)) $rp = "$rp/";
    $file = $rp.$ent->getFilename();
    if ($options['-v']){
        echo "Parsing $file:\n";
    }
    $syms = extractSymbols($ent->getPathname());
    foreach($syms as $sym){
        if ($symtable[strtolower($sym)] && ($symtable[strtolower($sym)] != $file)){
            fprintf(STDERR, "Duplicate Symbol: %s, first declared in %s, now also detected in %s\n",
                            $sym, $symtable[strtolower($sym)], $file);
            $dup[] = $sym;
        }
        $symtable[strtolower($sym)] = $file;
    }
}
if (count($dup) > 0){
    echo "\nWARNING:\n";
    echo "Will not autoload the following duplicate symbols:\n";
    echo implode(', ', array_unique(array_values($dup)));
    echo "\n";
}
foreach($dup as $d){
    unset($symtable[$d]);
}

file_put_contents($outfile, '<?php $GLOBALS["__er_autoload_funcmapping"] = '.var_export($symtable, true).'; ?>');
if ($options['-v']){
    echo "Written table to $outfile\n";
}


function extractSymbols($file){
    $r = token_get_all(file_get_contents($file));
    $c = count($r);
    $res = array();
    for($i = 0; $i < $c; $i++){
        $token = $r[$i];
        if (in_array($token[0], array(T_INTERFACE, T_CLASS))){
            $t = $token[0];
            while($r[++$i][0] == T_WHITESPACE)
                ;
            if ($r[$i][0] == T_STRING){
                $res[] = $r[$i][1];
                if ($GLOBALS['options']['-v'])
                    echo "\tFound ".token_name($t).": ".$r[$i][1]."\n";
            }else{
                fwrite(STDERR, "Expected interface name, but found ".token_name($r[$i][0]));
                continue;
            }
        }
    }
    return $res;
}