<?php
$GLOBALS['compressor']['compress'] = true;
//VERSION 1.4.1
//////////////////// DO NOT EDIT BELOW THIS ///////////////////////////////////////////////
define('LEFT', 10);
define('SPACE', 32);

class CompJS {

  var $a	= '';
  var $b	= '';
  var $i	= '';
  var $ii	= 0;
  var $il	= 0;
  var $ia	= null;
  var $out	= array();

  function compressor_function($js) {
    $jsmin	= new CompJS($js);
    return	$jsmin->javascript();
  }

  function CompJS($i) {
    $this->i	= $i;
    $this->il	= strlen($i);
  }

  function protection($d) {
    switch($d) {
      case 1:
        $this->out[] = $this->a;

      case 2:
        $this->a = $this->b;

        if ($this->a === "'" || $this->a === '"') {
          for (;;) {
            $this->out[] = $this->a;
            $this->a        = $this->get();

            if ($this->a === $this->b) {
              break;
            }

            if (ord($this->a) <= LEFT) {
              die('Unterminated string literal.');
            }

            if ($this->a === '\\') {
              $this->out[] = $this->a;
              $this->a        = $this->get();
            }
          }
        }

      case 3:
        $this->b = $this->next();

        if ($this->b === '/' && (
            $this->a === '(' || $this->a === ',' || $this->a === '=' ||
            $this->a === ':' || $this->a === '[' || $this->a === '!' ||
            $this->a === '&' || $this->a === '|' || $this->a === '?')) {

          $this->out[] = $this->a;
          $this->out[] = $this->b;

          for (;;) {
            $this->a = $this->get();

            if ($this->a === '/') {
              break;
            }
            elseif ($this->a === '\\') {
              $this->out[] = $this->a;
              $this->a        = $this->get();
            }
            elseif (ord($this->a) <= LEFT) {
              die('Error occured');
            }

            $this->out[] = $this->a;
          }

          $this->b = $this->next();
        }
    }
  }

  function get() {
    $c = $this->ia;
    $this->ia = null;

    if ($c === null) {
      if ($this->ii < $this->il) {
        $c = $this->i[$this->ii];
        $this->ii += 1;
      }
      else {
        $c = null;
      }
    }

    if ($c === "\r") {
      return "\n";
    }

    if ($c === null || $c === "\n" || ord($c) >= SPACE) {
      return $c;
    }

    return ' ';
  }

  function number($c) {
    return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
  }

  function javascript() {
    $this->a = "\n";
    $this->protection(3);

    while ($this->a !== null) {
      switch ($this->a) {
        case ' ':
          if ($this->number($this->b)) {
            $this->protection(1);
          }
          else {
            $this->protection(2);
          }
          break;

        case "\n":
          switch ($this->b) {
            case '{':
            case '[':
            case '(':
            case '+':
            case '-':
              $this->protection(1);
              break;

            case ' ':
              $this->protection(3);
              break;

            default:
              if ($this->number($this->b)) {
                $this->protection(1);
              }
              else {
                $this->protection(2);
              }
          }
          break;

        default:
          switch ($this->b) {
            case ' ':
              if ($this->number($this->a)) {
                $this->protection(1);
                break;
              }

              $this->protection(3);
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
                  $this->protection(1);
                  break;

                default:
                  if ($this->number($this->a)) {
                    $this->protection(1);
                  }
                  else {
                    $this->protection(3);
                  }
              }
              break;

            default:
              $this->protection(1);
              break;
          }
      }
    }

    return implode('', $this->out);
  }

  function next() {
    $c = $this->get();

    if ($c === '/') {
      switch($this->peek()) {
        case '/':
          for (;;) {
            $c = $this->get();

            if (ord($c) <= LEFT) {
              return $c;
            }
          }

        case '*':
          $this->get();

          for (;;) {
            switch($this->get()) {
              case '*':
                if ($this->peek() === '/') {
                  $this->get();
                  return ' ';
                }
                break;

              case null:
               die('Unterminated comment.');
            }
          }

        default:
          return $c;
      }
    }

    return $c;
  }

  function peek() {
    $this->ia = $this->get();
    return $this->ia;
  }
}


function compressor_ob_handler($start_buffer) {
	static $file_html = false;
	if (!$file_html) {
		if (stripos($start_buffer, '<html') !== false) {
			$file_html = true;
		} else {
			return $start_buffer;
		}
	}
	
	$start_buffer = protect_pretag($start_buffer);	
	$start_buffer = preg_replace('/<script(?!.*(src\=))[^>]*>(\s+)?<!--/','<script type="text/javascript"> ',$start_buffer);
	$start_buffer = preg_replace('/(\/\/)?-->(\s+)?<\/script>/','</script>',$start_buffer);

	// CDATA
	$start_buffer = preg_replace('/^(?:\s*\/\*\s*<!\[CDATA\[\s*\*\/|\s*\/\/\s*<!\[CDATA\[.*)/',' ',$start_buffer);
	$start_buffer = preg_replace('/(?:\/\*\s*\]\]>\s*\*\/|\/\/\s*\]\]>)\s*$/',' ',$start_buffer);
	$start_buffer = preg_replace('/\/\/]]>/','',$start_buffer);
	$start_buffer = preg_replace('/\/\*<!\[CDATA\[\*\//',' ',$start_buffer);
	$start_buffer = preg_replace('/\/\*]]>\*\//',' ',$start_buffer);
	$start_buffer = preg_replace('/\/\/]]&gt;/',' ',$start_buffer);
	//CDATA
	
	$start_buffer = preg_replace('/(<|<)!--(?!(\s+)?.(\s+)?[ifIFIfiF<!])(<)?\s*.*?\s*--((\s){0,3})?(>|>)/',' ',$start_buffer);	
	$start_buffer = preg_replace('/<script(?!.*(src\=))[^>]*>/','<script type="text/javascript"> ',$start_buffer);
	
	

	$start_buffer = preg_replace_callback('/<\s*script.*?>(.*?)<\/script>/s','protect_script',$start_buffer); 
	
	$start_buffer = preg_replace('/>[^\S]+</', '> <', $start_buffer);
	$start_buffer = preg_replace('/\n/',' ', $start_buffer); 
	$start_buffer = preg_replace('/\s{3,}/',' ', $start_buffer); 
	$start_buffer = preg_replace('/\t/',' ', $start_buffer);	


	$start_buffer = preg_replace('/1NS3R7N3WL1N3/',"\n",$start_buffer);
	
 	$start_buffer = preg_replace_callback('/<script.*?>(.*?)<\/script>/s','minifyJS',$start_buffer); 
	
	return $start_buffer;
}

function protect_pretag($str) {
	$str = " ".$str;  
	$parts = preg_split("/(< \s* pre .* \/ \s* pre \s* >)/Umsxu",$str,-1,PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $idx=>$part) {
        if ($idx % 2) {
            $parts[$idx] = preg_replace("/\n/", "1NS3R7N3WL1N3", $part);
        }
    }
    $str = implode('',$parts);
	return substr($str,1);
}

 function minifyJS($js) {
    return CompJS::compressor_function($js[0]);
  }
  
  function protect_script($str) {
	$str = " ".$str[0];  
	$parts = preg_split("/(< \s* script .* \/ \s* script \s* >)/Umsxu",$str,-1,PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $idx=>$part) {
        if ($idx % 2) {
            $parts[$idx] = preg_replace("/\n/", "1NS3R7N3WL1N3", $part);
        }
    }
    $str = implode('',$parts);
	$hi  = substr($str,1);
	return $hi;
}

if ($GLOBALS['compressor']['compress'])     ob_start('compressor_ob_handler');
//////////////////// DO NOT EDIT ABOVE THIS ///////////////////////////////////////////////
