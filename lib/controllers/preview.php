<?php
RequestMapper::registerRequest('#^/preview#', '/preview', 'PreviewController');


class PreviewController extends BaseController{

    public function render($url){
        list($null, $name, $file) = explode('/', $url, 3);
        if ($file){
            echo $this->getPreview($file);
        }else{
            throw new BadRequest("Syntax: /preview</path/to/file>");
        }
    }

    private function getPreview($file){
        $root = c()->getValue('report_format', 'app_root');
        if (!$root){
            throw new Forbidden(array("reason" => "app_root not configured on server"));
        }
        $fp = realpath($root.DIRECTORY_SEPARATOR.$file);

        // sanity checking. I'm quite sure I missed nothing :-)
        if (
            !$fp ||                           // is valid path?
            (false === strstr($fp, $root)) || // is below specified root?
            !is_file($fp) ||                  // is a file?
            !is_readable($fp)                 // is readable?
        ){
            throw new NotFound();
        }
        $line = isset($_GET['line']) ? intval($_GET['line']) : 1;
        $context = isset($_GET['context']) ? min(20, intval($_GET['context'])) : 5;
        $h = fopen($fp, 'r');
        $l = 1;
        $preview = "";
        while(!feof($h)){
            $src = fgets($h);
            if ($l >= $line)
                $preview .= $src;
            $l++;
            if ($l >= $line+$context)
                break;
        }
        fclose($h);
        header('Content-Type: text/plain');
        header('Content-Length: '.strlen($preview));
        echo $preview;
    }

    // won't be called
    public function handle($url){}
}
