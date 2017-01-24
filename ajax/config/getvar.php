<?php

if(!defined("INCMS")) die();

count($_REQUEST);
count($_POST);
count($_GET);
count($_COOKIE);

class GetVarClass{
	private $source;
	private $wrapper;

	function __construct($source="_POST", $wrapper="db_escape"){
		$this->set_source($source);
		$this->set_wrapper($wrapper);
		}

	private function set_source($source){
		switch($source){
			case "_POST":
			case "_GET":
			case "_COOKIE":
			case "_REQUEST":
			case "_SERVER":
			case "_FILES":
			case "_SESSION":
			case "_ENV":
				$this->source = $source;break;
			default: die("GetVar cannot use $source as a source!");
			}
		}

	private function set_wrapper($wrapper){
		if(!is_callable($wrapper))
			die("GetVar cannot use $wrapper as a wrapper!");
		$this->wrapper = $wrapper;
		}

	static function filter($var, $regexp="", $default=FALSE){
		if($regexp && !mb_ereg("^($regexp)$",$var))
			$var= $default;
		return $var;
		}

	function getvar($key, $regexp="", $default=FALSE){
		global ${$this->source};

		if(isset(${$this->source}[$key])){
			$var = ${$this->source}[$key];
			if(is_scalar($var)){
				$var = self::filter($var, $regexp, $default);
				if($var!==$default){
					$wrapper = $this->wrapper;
					$var= $wrapper($var);
					}
				}
			else {
				$var=$default;
				}
			}
		else{
			$var=$default;
			}
		return $var;
		}

	function getvars_cb($key_regexp,$func,$param1=FALSE,$param2=FALSE){
		global ${$this->source};

		$ret=[];
		foreach(${$this->source} as $key=>$value){
			if(mb_ereg("^($key_regexp)$",$key)){
				$ret[$key]=$this->$func($key,$param1,$param2);
				}
			}
		return $ret;
		}

	function getvars($key_regexp,$regexp="",$default=FALSE){
		return $this->getvars_cb($key_regexp,"getvar",$regexp,$default);
		}

	function getvars_fixed_cb($key_fixed,  $key_regexp, $func, $param1=FALSE, $param2=FALSE){
		$temp = $this->getvars_cb($key_fixed . $key_regexp, $func, $param1, $param2);
		$ret = [];
		foreach($temp as $key=>$value){
			$key = mb_substr($key, mb_strlen($key_fixed));
			$ret[$key] = $value;
			}
		return $ret;
		}

	function getvars_fixed($key_fixed, $key_regexp, $regexp="", $default=FALSE){
		return $this->getvars_fixed_cb($key_fixed, $key_regexp, "getvar", $regexp, $default);
		}

	function getvars_int_cb($key_fixed, $func, $param1=FALSE, $param2=FALSE){
		$temp = $this->getvars_cb($key_fixed . "\d+", $func, $param1, $param2);
		$ret = [];
		foreach($temp as $key=>$value){
			$key = mb_substr($key, mb_strlen($key_fixed));
			$ret[$key] = $value;
			}
		return $ret;
		}

	function getvars_int($key_fixed, $regexp="", $default=FALSE){
		return $this->getvars_int_cb($key_fixed, "getvar", $regexp, $regexp, $default);
		}

	private function getarray_cb_helper($array, $func, $param1="", $param2=""){
		global ${$this->source};

		foreach($array as $key=>$value){
			if(is_scalar($value)){
				${$this->source}["temp_getarray_callback"] = $value;
				$temp = $this->$func("temp_getarray_callback", $param1,$param2);
				if($temp===FALSE)
					unset($array[$key]);
				else
					$array[$key] = $temp;
				}
			else {
				$array[$key] = $this->getarray_cb_helper($value, $func, $param1, $param2);
				}
			}
		return $array;
		}

	function getarray_cb($array_key,$func,$param1="",$param2=""){
		global ${$this->source};

		$array = isset(${$this->source}[$array_key]) ? ${$this->source}[$array_key] : [];
		if(!is_array($array))
			$array=[];

		$array = $this->getarray_cb_helper($array, $func, $param1, $param2);

		return $array;
		}

	function getarray($array_key,$regexp=""){
		return $this->getarray_cb($array_key, "getvar", $regexp);
		}

	const REGEXPINT = "[\d\s]+";
	function getint($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPINT,$default);
		return ($temp===$default) ? $temp : intval(preg_replace("/\s/","",$temp));
		}

	const REGEXPSINT = "[+\-]?[\d\s]+";
	function getsint($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPSINT,$default);
		return ($temp===$default) ? $temp : intval(preg_replace("/\s/","",$temp));
		}

	const REGEXPFLOAT = "[\d\s]*([\.,][\d\s]*)?";
	function getfloat($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPFLOAT,$default);
		return ($temp===$default) ? $temp : floatval(str_replace(",",".",preg_replace("/\s/","",$temp)));
		}

	const REGEXPSFLOAT = "\s*[+\-]?[\d\s]*([\.,][\d\s]*)?";
	function getsfloat($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPSFLOAT,$default);
		return ($temp===$default) ? $temp : floatval(str_replace(",",".",preg_replace("/\s/","",$temp)));
		}

	const REGEXPEMAIL = "\s*[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\s*";
	function getemail($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPEMAIL,$default);
		return ($temp===$default) ? $temp : trim($temp);
		}

	const REGEXPRELURL = "[\d\w\/\?%&#=_.\-]+";
	function getrelurl($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPRELURL,$default);
		return $temp;
		}

	private function valid_iso_date($date,$default){

		$date=explode("-",$date);
		if(count($date)!=3)
			return $default;
		list($year,$month,$day)=$date;

		$year=add0($year,2);
		if(strlen($year)==2)
			$year="20$year";
		$year=add0($year,4);

		$month=add0($month,2);
		$day=add0($day,2);

		return checkdate($month,$day,$year) ? "$year-$month-$day" : $default;
		}

	function getcheck($name, $default=FALSE){
		$temp = $this->getvar($name, "on", $default);
		if($temp!==$default)
			$temp = (bool)$temp;
		return $temp;
		}

	const REGEXPADATE = "\s*\d{1,4}?\s*\-\s*\d{1,2}\s*\-\s*\d{1,2}\s*";
	function getadate($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPADATE,$default);
		if($temp===$default)
			return $temp;

		$temp=preg_replace("/[^\d\-]/","",$temp);

		return self::valid_iso_date($temp,$default);
		}

	const REGEXPDOTDATE = "\s*\d{1,2}\s*\.\s*\d{1,2}\s*\.\s*\d{1,4}\s*";
	function getdotdate($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPDOTDATE,$default);
		if($temp===$default)
			return $temp;

		$temp=preg_replace("/\s/","",$temp);
		$temp=explode(".",$temp);
		if(count($temp)!=3)
			return $default;
		list($day,$month,$year)=$temp;

		return self::valid_iso_date("$year-$month-$day",$default);
		}

	const REGEXPICQ = "\s*[\d\s\-]+";
	function geticq($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPICQ,$default);
		return ($temp===$default) ? $temp : substr(chunk_split(preg_replace("/[^\d]/","",$temp),3,"-"),0,-1);
		}

	const REGEXPALPHANUM = "[\d\sa-zA-Z_\-]+";
	function getalphanum($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPALPHANUM,$default);
		return ($temp===$default) ? $temp : trim(preg_replace("/\s\s/"," ",$temp));
		}

	const REGEXPALPHANUMCYR = "[\d\sa-zA-Zа-яА-ЯЁё_\-\ ]+";
	function getalphanumcyr($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPALPHANUMCYR,$default);
		return ($temp===$default) ? $temp : trim(preg_replace("/\s\s/"," ",$temp));
		}

	const REGEXPMD5 = "\s*[\da-fA-F]{32}\s*";
	function getmd5($name,$default=FALSE){
		$temp = $this->getvar($name,self::REGEXPMD5,$default);
		return ($temp===$default) ? $temp : strtolower(preg_replace("/\s/","",$temp));
		}

	function getenum($name,$array,$default=FALSE){
		$search = ["|", "-"];
		$replace = [];
		foreach($search as $letter){
			$replace[] = "\\$letter";
			}

		$regexp = "";
		foreach($array as $alt){
			$regexp.= "|".str_replace($search, $replace, $alt);
			}

		if(!$regexp)
			return $default;
		$regexp = substr($regexp, 1);
		$temp   = $this->getvar($name, $regexp, $default);
		if($temp===$default)
			return $temp;
		return $temp;
		}

	function getenumkeys($name,$array,$default=FALSE){
		return $this->getenum($name,array_keys($array),$default);
		}

	function getkeyword($name,$default=FALSE){
		return trim(preg_replace("/\s+/u", " ", preg_replace("/[^-\wА-Яа-яЁё\d\s]+/u", " ", strip_tags($this->getvar($name,$default)))));
		}

	}

?>
