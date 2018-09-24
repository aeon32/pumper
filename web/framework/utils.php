<?php
  /*
   * Различные вспомогательные функции и классы
   *
   */


 /* Функция возвращает случайную строку, составленную из цифр и
  * знаков английского алфавита
  */

 function getRandomString() {
	$id = 0;
	while (strlen($id) < 32)  {
		$id .= mt_rand(0, mt_getrandmax());
	}

	$id	= md5( uniqid($id, true));
	return $id;
 }


  /* Функция возвращает случайную строку, составленную из цифр и
  * знаков английского алфавита
  */

 function getShortRandomString($length) {
	$str = '';
	for ($i=0;$i<$length;$i++) {
		$m=mt_rand ( 0 ,35 );
		$m=$m<10? $m+ord('0'):$m+ord('a')-10;
		$str.=chr($m);
	}

	return $str;
 }


 /*Функция проверяет е-mail на правильность
  *
  */
 function validateEMAIL($email) {
 	if (preg_match('/[0-9a-z_\-]+@[0-9a-z_\.\-]+\.[a-z]{2,3}/i',$email)) return true; else return false;
 }


 /*
  * Функция переводит число в двоичное представление. Добавляет спереди нули.
  * Таким образом, результат всегда имеет длину 8 символов
  */
  function advDecHex($a) {
  	$res=dechex($a);
  	$len=strlen($res);
  	$zeros='';
  	if ($len<8) for($i=0;$i<8-$len;$i++) $zeros.='0';//добавляем нули
  	return $zeros.$res;
  }


  /*
   * Функция транслитерации
   */
  function translit($str) {
   $trans = array("а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e", "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i","й"=>"i","к"=>"k","л"=>"l", "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t", "у"=>"y","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch", "ш"=>"sh","щ"=>"sh","ы"=>"i","э"=>"e","ю"=>"u","я"=>"ya",
       "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D","Е"=>"E", "Ё"=>"Yo","Ж"=>"J","З"=>"Z","И"=>"I","Й"=>"I","К"=>"K", "Л"=>"L","М"=>"M","Н"=>"N","О"=>"O","П"=>"P", "Р"=>"R","С"=>"S","Т"=>"T","У"=>"Y","Ф"=>"F", "Х"=>"H","Ц"=>"C","Ч"=>"Ch","Ш"=>"Sh","Щ"=>"Sh", "Ы"=>"I","Э"=>"E","Ю"=>"U","Я"=>"Ya",
       "ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>"");
   return strtr($str, $trans);
  }

  /*
   * Жёсткая функция транслитерации (для получения url'ов страниц)
   */
  function hard_translit($str) {
   $trans = array("а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e", "ё"=>"yo","ж"=>"j","з"=>"z","и"=>"i","й"=>"i","к"=>"k","л"=>"l", "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t", "у"=>"y","ф"=>"f","х"=>"h","ц"=>"c","ч"=>"ch", "ш"=>"sh","щ"=>"sh","ы"=>"i","э"=>"e","ю"=>"u","я"=>"ya",
       "А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e", "Ё"=>"yo","Ж"=>"j","З"=>"z","И"=>"i","Й"=>"i","К"=>"k", "Л"=>"l","М"=>"m","Н"=>"n","О"=>"o","П"=>"p", "Р"=>"r","С"=>"s","Т"=>"t","У"=>"y","Ф"=>"f", "Х"=>"h","Ц"=>"c","Ч"=>"ch","Ш"=>"sh","Щ"=>"sh", "Ы"=>"i","Э"=>"e","Ю"=>"u","Я"=>"ya",
       "ь"=>"","Ь"=>"","ъ"=>"","Ъ"=>"",' '=>'_','`'=>'','~'=>'','!'=>'','@'=>'','"'=>'','#'=>'','№'=>'','$'=>'',"'"=>'','$'=>'',';'=>'','%'=>'','^'=>'',':'=>'','&'=>'','?'=>'','*'=>'','('=>'',')'=>'','-'=>'','+'=>'','|'=>'','\\'=>'','/'=>'');
   $str=strtolower(strtr($str, $trans));
   return rawurlencode($str);
  }

  /*
   *
   */
  function processMagicQuotes($str) {
  	if (get_magic_quotes_gpc())
  	 return stripcslashes($str);
  	else return $str;
  }

  /*
   * Функция для обработки галочек на странице. NamePattern-образец имени.
   * Функция возвращает массив из 2ух строк. Первая-список выделенных идентификаторов,
   * вторая-неВыделынныъ
   */

  function processCheckBoxes($namePattern) {
	 $on=array();
	 $off=array();
	 foreach($_POST as $key=>$value) {
	  	  $fl_array=array();
	  	  if (preg_match("/$namePattern([0-9]+)/", $key,$fl_array)) {
           $anket_id=$fl_array[1];
           if ($value=="on")
             $on[]=(int)($anket_id);
           else
             $off[]=(int)($anket_id);
	  	  }
	  }
	  $on[]="'-1'";
	  $off[]="'-1'";
	  return array(implode(',',$on),implode(',',$off));
  }


  /*
   * Функция для обработки галочек на странице. NamePattern-образец имени.
   * Функция возвращает массив из 2ух строк. Первая-список выделенных идентификаторов,
   * вторая-неВыделынныъ
   */

  function processCheckBoxesArray($namePattern) {
	 $res=array();
	 foreach($_POST as $key=>$value) {
	  	  $fl_array=array();
	  	  if (preg_match("/$namePattern([0-9]+)/", $key,$fl_array)) {
           $anket_id=$fl_array[1];
           if ($value=="on")
             $res[(int)$anket_id]=true;
           else
             $res[(int)($anket_id)]=false;
	  	  }
	  }
	  return $res;
  }


   /*
   * Функция для пакетного сбора значений текстбоксов на странице
   * Функция возвращает массив из 2ух строк. Первая-список выделенных идентификаторов,
   * вторая-неВыделынныъ Объяснил, блядь :-)
   */

  function processInputs($namePattern) {
	 $values=array();
     foreach($_POST as $key=>$value) {
	  	  $fl_array=array();
	  	  if (preg_match("/$namePattern([0-9]+)/", $key,$fl_array)) {
           $anket_id=$fl_array[1];
           $values[(int)$anket_id]=$value;
	      };
     }
	 return $values;
   }

   /**
    * Функция возвращает обрезает строку до количества символов, не превышающего len символов, и оканчивающуюся пробелом
    */
   function trimToSpace($text,$len) {
   	if (strlen($text)>$len) {
   		  $fl_array=array();
          preg_match("/(.*)[ ][^ ]+/", $text,$fl_array);
          return $fl_array[1];
   		} else return $text;

   }

   /**\
    * Функция для именования файлов
    */

   function id_to_fileName($id) {
   		return advDecHex(((int)$id)^0xA1ECD6F5);
   };

/**
* This function returns a regular expression pattern for commonly used expressions
* Use with / as delimiter for email mode and # for url modes
* mode can be: email|bbcode_htm|url|url_inline|www_url|www_url_inline|relative_url|relative_url_inline|ipv4|ipv6
*/
function get_preg_expression($mode)
{
	switch ($mode)
	{
		case 'email':
			return '(?:[a-z0-9\'\.\-_\+\|]++|&amp;)+@[a-z0-9\-]+\.(?:[a-z0-9\-]+\.)*[a-z]+';
		break;

		case 'bbcode_htm':
			return array(
				'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
				'#<!\-\- l \-\-><a (?:class="[\w-]+" )?href="(.*?)(?:(&amp;|\?)sid=[0-9a-f]{32})?">.*?</a><!\-\- l \-\->#',
				'#<!\-\- ([mw]) \-\-><a (?:class="[\w-]+" )?href="(.*?)">.*?</a><!\-\- \1 \-\->#',
				'#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
				'#<!\-\- .*? \-\->#s',
				'#<.*?>#s',
			);
		break;

		// Whoa these look impressive!
		// The code to generate the following two regular expressions which match valid IPv4/IPv6 addresses
		// can be found in the develop directory
		case 'ipv4':
			return '#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#';
		break;

		case 'ipv6':
			return '#^(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){5}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-
5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:))$#i';
		break;

		case 'url':
		case 'url_inline':
			$inline = ($mode == 'url') ? ')' : '';
			$scheme = ($mode == 'url') ? '[a-z\d+\-.]' : '[a-z\d+]'; // avoid automatic parsing of "word" in "last word.http://..."
			// generated with regex generation file in the develop folder
			return "[a-z]$scheme*:/{2}(?:(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'www_url':
		case 'www_url_inline':
			$inline = ($mode == 'www_url') ? ')' : '';
			return "www\.(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})+(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;

		case 'relative_url':
		case 'relative_url_inline':
			$inline = ($mode == 'relative_url') ? ')' : '';
			return "(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
		break;
	}

	return '';
}


/**
 * Функция для загрузки изображения с заданными именами
 */

function uploadImage($name,$id) {
	  $res = true;
	  $photo=$_FILES[$name];
	  $res = NULL;
	  if (isset($photo) && $photo['error']==0 && $photo['size']) {
	    $filename='';
	    do {
	     $filename=getRandomString();
	    } while(file_exists(PATH_BASE.'/tmp/'.$filename));
        $filename=PATH_BASE.'/tmp/'.$filename;
	    move_uploaded_file($photo['tmp_name'],$filename);
	    $im=@imagecreatefromjpeg($filename);
	    $ext = ".jpg";
	    if (!$im) {
	    	$im=@imagecreatefrompng($filename);
	    	$ext = ".png";
	    }

	    if (!$im) {
	    	$im=@imagecreatefromgif($filename);
	    	$ext = ".gif";
	    };
	    if (!$im) return false;    //ничего не получилось :-(
        imagedestroy($im);
        @rename ("$filename", PATH_BASE.'/user_images/'.id_to_filename($id).$ext);
        @unlink($filename);
        $res ="user_images/".id_to_filename($id).$ext;
	  };
	  return $res;
};

/**
 *  Функция преобразует количество прошедших секунд в читабельный формат
**/
function getFormattedUptime($uptime) {
   $days = floor($uptime/ (24*3600*100));
   $uptime -= $days*24*3600*100;
   $hours = floor($uptime / (3600*100));
   $uptime -= $hours * 3600*100;
   $minutes = floor($uptime / (60*100));
   $uptime -= $minutes * (60*100);
   $seconds = floor($uptime/100);
   $res = '';
   if ($days) {
      $res = $res."$days д ";
   };
   
   if ($hours || ($days>0 && $hours==0)) {
      $res = $res."$hours ч ";
   };
   
   if ($minutes || ($minutes == 0 && ($days>0 || $hours>0))) {
      $res = $res."$minutes мин ";
   };   
   
   $res = $res."$seconds c ";
   return $res;
   

   
   
}


?>
