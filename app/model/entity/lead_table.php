<?php
namespace App\Model\Entity;
use DateTime;
use App\Model\TableModel;

class Lead_Table extends TableModel{
	function getList(){
		$rows = [];
		foreach($this as $row){
			$a = $row->getArray();
			if(isset($a['ctime']))
				$a['ctime'] = (new DateTime($a['ctime']))->getTimestamp();
			if(isset($a['mtime']))
				$a['mtime'] = (new DateTime($a['mtime']))->getTimestamp();
			$rows[] = $a;
		}
		return $rows;
	}
}
