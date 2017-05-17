<?php
namespace App\Model;
use DateTime;
trait NormalizeTrait{
	function normalizeDateFields(){
		foreach($this as $k=>$v){
			if(substr($k,0,5)=='date_'){
				$this[$k] = $this->normalizeDate($v);
			}
		}
	}
	function normalizeDate($date){
		$date = trim($date);
		if(!$date){
			return $date;
		}
		
		try{
			if(strpos($date,'-')===4){
				$format = 'Y-m-d';
			}
			else{
				$date = str_replace(['-',' '],'/',$date);
				if(strpos($date,'/')===false){
					$format = 'Ymd';
				}
				else{
					$format = 'd/m/Y';
					$x = explode('/',$date);
					if(count($x)>3){
						array_pop($x);
						$date = implode('/',$x);
					}
				}
			}
			$date = DateTime::createFromFormat($format, $date);
			if($date){
				$date = $date->format('Y-m-d');
			}
		}
		catch(Exception $e){
			$date = null;
		}
		
		return $date;
	}
	function normalizeDecimal($number){
		$number = trim(str_replace(',','.',$number));
		if(!is_numeric($number)){
			$number = null;
		}
		return $number;
	}
}
