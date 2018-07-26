<?php
/**
 * @author Abhimanyu Sharma <abhimanyusharma003@gmail.com>
 * @copyright Abhimanyu Sharma [abhimanyusharma003]
 * @licence http://codecanyon.net/licenses/regular [ for single application ]
 * @version 2.0
 */
$setting = array(
    'compress' => TRUE,
    'cache-folder' => 'AUTO'
);



//----------------------------------------------------------------------------------------------
//                                DO NOT MODIFY ANY THING BELOW THIS
//----------------------------------------------------------------------------------------------

if ($setting['cache-folder'] == 'AUTO') {
    $cacheDir = dirname(__FILE__) . '/dwccache';
} else {
    $cacheDir = $setting['cache-folder'];
}

if (!empty($_GET['dwc-debug']) && isset($_GET['dwc-debug'])) {
    if ($_GET['dwc-debug'] == TRUE) {
        $setting['compress'] = FALSE;
    }
}

$GLOBALS['compressor']['clear'] = FALSE;
if (!empty($_GET['dwc-clear-cache']) && isset($_GET['dwc-clear-cache'])) {
    if ($_GET['dwc-clear-cache'] == TRUE) {
        $setting['compress'] = FALSE;
        $GLOBALS['compressor']['clear'] = TRUE;
    }
}

class CompJS {
    const LF = 10;
    const SP = 32;
    const KPA = 1;
    const DEA = 2;
    const DEAB = 3;

    protected $a = '';
    protected $b = '';
    protected $inp = '';
    protected $inpI = 0;
    protected $inpL = 0;
    protected $la = null;
    protected $op = '';

    function compressor_function($js)
    {
        $jsmin = new CompJS($js);
        return $jsmin->min();
    }


    public function __construct($input)
    {
        $this->inp = str_replace("\r\n", "\n", $input);
        $this->inpL = strlen($this->inp);
    }


    protected function act($command)
    {
        switch ($command) {
            case self::KPA:
                $this->op .= $this->a;

            case self::DEA:
                $this->a = $this->b;

                if ($this->a === "'" || $this->a === '"') {
                    for (; ;) {
                        $this->op .= $this->a;
                        $this->a = $this->put();

                        if ($this->a === $this->b) {
                            break;
                        }

                        if (ord($this->a) <= self::LF) {
                            throw new CompJSException('Unterminated string literal.');
                        }

                        if ($this->a === '\\') {
                            $this->op .= $this->a;
                            $this->a = $this->put();
                        }
                    }
                }

            case self::DEAB:
                $this->b = $this->nt();

                if ($this->b === '/' && (
                        $this->a === '(' || $this->a === ',' || $this->a === '=' ||
                        $this->a === ':' || $this->a === '[' || $this->a === '!' ||
                        $this->a === '&' || $this->a === '|' || $this->a === '?' ||
                        $this->a === '{' || $this->a === '}' || $this->a === ';' ||
                        $this->a === "\n")
                ) {

                    $this->op .= $this->a . $this->b;

                    for (; ;) {
                        $this->a = $this->put();

                        if ($this->a === '[') {
                            for (; ;) {
                                $this->op .= $this->a;
                                $this->a = $this->put();

                                if ($this->a === ']') {
                                    break;
                                } elseif ($this->a === '\\') {
                                    $this->op .= $this->a;
                                    $this->a = $this->put();
                                } elseif (ord($this->a) <= self::LF) {
                                    throw new CompJSException('Regex literal.');
                                }
                            }
                        } elseif ($this->a === '/') {
                            break;
                        } elseif ($this->a === '\\') {
                            $this->op .= $this->a;
                            $this->a = $this->put();
                        } elseif (ord($this->a) <= self::LF) {
                            throw new CompJSException('Regex literal.');
                        }

                        $this->op .= $this->a;
                    }

                    $this->b = $this->nt();
                }
        }
    }

    protected function put()
    {
        $c = $this->la;
        $this->la = null;

        if ($c === null) {
            if ($this->inpI < $this->inpL) {
                $c = substr($this->inp, $this->inpI, 1);
                $this->inpI += 1;
            } else {
                $c = null;
            }
        }

        if ($c === "\r") {
            return "\n";
        }

        if ($c === null || $c === "\n" || ord($c) >= self::SP) {
            return $c;
        }

        return ' ';
    }

    protected function isAN($c)
    {
        return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
    }

    protected function min()
    {
        if (0 == strncmp($this->pk(), "\xef", 1)) {
            $this->put();
            $this->put();
            $this->put();
        }

        $this->a = "\n";
        $this->act(self::DEAB);

        while ($this->a !== null) {
            switch ($this->a) {
                case ' ':
                    if ($this->isAN($this->b)) {
                        $this->act(self::KPA);
                    } else {
                        $this->act(self::DEA);
                    }
                    break;

                case "\n":
                    switch ($this->b) {
                        case '{':
                        case '[':
                        case '(':
                        case '+':
                        case '-':
                        case '!':
                        case '~':
                            $this->act(self::KPA);
                            break;

                        case ' ':
                            $this->act(self::DEAB);
                            break;

                        default:
                            if ($this->isAN($this->b)) {
                                $this->act(self::KPA);
                            } else {
                                $this->act(self::DEA);
                            }
                    }
                    break;

                default:
                    switch ($this->b) {
                        case ' ':
                            if ($this->isAN($this->a)) {
                                $this->act(self::KPA);
                                break;
                            }

                            $this->act(self::DEAB);
                            break;

                        case "\n":
                            switch ($this->a) {
                                case '}':
                                case ']':
                                case ')':
                                case '+':
                                case '-':
                                case '"':
                                case "'":
                                    $this->act(self::KPA);
                                    break;

                                default:
                                    if ($this->isAN($this->a)) {
                                        $this->act(self::KPA);
                                    } else {
                                        $this->act(self::DEAB);
                                    }
                            }
                            break;

                        default:
                            $this->act(self::KPA);
                            break;
                    }
            }
        }

        return $this->op;
    }


    protected function nt()
    {
        $c = $this->put();

        if ($c === '/') {
            switch ($this->pk()) {
                case '/':
                    for (; ;) {
                        $c = $this->put();

                        if (ord($c) <= self::LF) {
                            return $c;
                        }
                    }

                case '*':
                    $this->put();

                    for (; ;) {
                        switch ($this->put()) {
                            case '*':
                                if ($this->pk() === '/') {
                                    $this->put();
                                    return ' ';
                                }
                                break;

                            case null:
                                throw new CompJSException('Unterminated comment');
                        }
                    }

                default:
                    return $c;
            }
        }

        return $c;
    }


    protected function pk()
    {
        $this->la = $this->put();
        return $this->la;
    }
}

class DwcCache {

    function __construct($dir)
    {
        $this->dir = $dir;
    }

    private function _name($key)
    {
        return sprintf("%s/%s", $this->dir, sha1($key));
    }

    public function get($key, $expiration = 3600)
    {

        if (!is_dir($this->dir)) {
            return FALSE;
        }

        $cache_path = $this->_name($key);

        if (!@file_exists($cache_path)) {
            return FALSE;
        }

        if (!$fp = @fopen($cache_path, 'rb')) {
            return FALSE;
        }

        flock($fp, LOCK_SH);

        $cache = '';

        if (filesize($cache_path) > 0) {
            $cache = unserialize(fread($fp, filesize($cache_path)));
        } else {
            $cache = NULL;
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $cache;
    }

    public function set($key, $data)
    {

        if (!is_dir($this->dir) OR !is_writable($this->dir)) {
            return FALSE;
        }

        $cache_path = $this->_name($key);

        if (!$fp = fopen($cache_path, 'wb')) {
            return FALSE;
        }

        if (flock($fp, LOCK_EX)) {
            fwrite($fp, serialize($data));
            flock($fp, LOCK_UN);
        } else {
            return FALSE;
        }
        fclose($fp);
        @chmod($cache_path, 0777);
        return TRUE;
    }
}


function compressor_ob_handler($start_buffer)
{
    static $file_html = false;
    if (!$file_html) {
        if (stripos($start_buffer, '<html') !== false) {
            $file_html = true;
        } else {
            return $start_buffer;
        }
    }

    $start_buffer = protect_pretag($start_buffer);
    $start_buffer = protect_textarea($start_buffer);

    $start_buffer = preg_replace('/<script(?!.*(src\=))[^>]*>(\s+)?<!--/', '<script type="text/javascript"> ', $start_buffer);
    $start_buffer = preg_replace('/(\/\/)?-->(\s+)?<\/script>/', '</script>', $start_buffer);

    // CDATA
    $start_buffer = preg_replace('/^(?:\s*\/\*\s*<!\[CDATA\[\s*\*\/|\s*\/\/\s*<!\[CDATA\[.*)/', ' ', $start_buffer);
    $start_buffer = preg_replace('/(?:\/\*\s*\]\]>\s*\*\/|\/\/\s*\]\]>)\s*$/', ' ', $start_buffer);
    $start_buffer = preg_replace('/\/\/]](>|&gt;)/', '', $start_buffer);
    $start_buffer = preg_replace('/\/\*<!\[CDATA\[\*\//', ' ', $start_buffer);
    $start_buffer = preg_replace('/\/\*]]>\*\//', ' ', $start_buffer);
    //CDATA

    $start_buffer = preg_replace('/(<|<)!--(\s){0,1}?(?!<)(?!(\s+)?.(\s+)?(<)?[ifIFIfiF<!])(<)?\s*.*?\s*--((\s){0,3})?(>|>)/s', ' ', $start_buffer);
    $start_buffer = preg_replace('/<script(?!.*(src\=))[^>]*>/', '<script type="text/javascript"> ', $start_buffer);

    $start_buffer = preg_replace_callback('/<\s*script(?![^>]*\.js)[^>]*>(.*?)<\/script>/s', 'minifyJS', $start_buffer);

    $start_buffer = preg_replace('/>[^\S]+</', '> <', $start_buffer);
    $start_buffer = preg_replace('/\n/', ' ', $start_buffer);
    $start_buffer = preg_replace('/\s{3,}/', ' ', $start_buffer);
    $start_buffer = preg_replace('/\t/', ' ', $start_buffer);


    $start_buffer = preg_replace('/1NS3R7N3WL1N3/', "\n", $start_buffer);


    $start_buffer = preg_replace('/"src=/', '" src=', $start_buffer);
    $start_buffer = preg_replace('/\'src=/', '\' src=', $start_buffer);
    $start_buffer = preg_replace('/"type=/', '" type=', $start_buffer);
    $start_buffer = preg_replace('/\'type=/', '\' type=', $start_buffer);

    return $start_buffer;
}

function protect_textarea($str)
{
    $str = " " . $str;
    $parts = preg_split("/(< \s* textarea .* \/ \s* textarea \s* >)/Umsxu", $str, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $idx => $part) {
        if ($idx % 2) {
            $parts[$idx] = preg_replace("/\n/", "1NS3R7N3WL1N3", $part);
        }
    }
    $str = implode('', $parts);
    return substr($str, 1);
}


function protect_pretag($str)
{
    $str = " " . $str;
    $parts = preg_split("/(< \s* pre .* \/ \s* pre \s* >)/Umsxu", $str, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $idx => $part) {
        if ($idx % 2) {
            $parts[$idx] = preg_replace("/\n/", "1NS3R7N3WL1N3", $part);
        }
    }
    $str = implode('', $parts);
    return substr($str, 1);
}

function minifyJS($js)
{
    global $cacheDir;
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir);
    }
    $cache = new DwcCache($cacheDir);
    $md = md5($js[0]);
    $data = $cache->get($md);
    if ($data === FALSE) {
        $comp = new CompJS($js[0]);
        $data = $comp->compressor_function($js[0]);
        $data = protect_script($data);
        $cache->set($md, $data);
    }
    return $data;
}

function protect_script($str)
{
    $str = " " . $str;
    $parts = preg_split("/(< \s* script .* \/ \s* script \s* >)/Umsxu", $str, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $idx => $part) {
        if ($idx % 2) {
            $parts[$idx] = preg_replace("/\n/", "1NS3R7N3WL1N3", $part);
        }
    }
    $str = implode('', $parts);
    $hi = substr($str, 1);
    return $hi;
}

function deleteDir($dirPath)
{
    if (!is_dir($dirPath)) {
        return FALSE;
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::deleteDir($file);
        } else {
            unlink($file);
        }
    }
}

if ($setting['compress'] == TRUE) ob_start('compressor_ob_handler');
if ($GLOBALS['compressor']['clear'] == TRUE) deleteDir($cacheDir);
//////////////////// DO NOT EDIT ABOVE THIS ///////////////////////////////////////////////