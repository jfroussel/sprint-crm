<?php
namespace App\Model;
use DateTime;
use Exception;
use FoxORM\Entity\RulableModel;
use App\Route\User;
use RedCat\Strategy\Di;

class EntityModel extends RulableModel {
	protected $trashDirectoryName = 'db-trash';
	protected $__dirDb = '.data';
    protected $dynamic = [];
	protected $validateRules = [
		
	];
	protected $validateFilters = [
		
	];
	protected $validateProperties = false;
	//protected $validateProperties = [];
	
	protected $validateAllowHtml = [];
	
	protected $_di;
	protected $_user;
	
	function __construct($array=[],$type=null,User $user, Di $di){
		parent::__construct($array,$type);
		
		$this->_di = $di;
		$this->_user = $user;
		
        $this->dynamic = (object)[];
		
		$this->on('beforeCreate',function(){
			if(isset($this->debtor_id)&&$this->debtor_id){
				if(!isset($this->debtor_primary)){ //keep track of denormalized debtor primary reference
					$this->debtor_primary = $this->db['debtor']
						->unSelect()->select('debtor_primary')
						->where('id = ?',[$this->debtor_id])
						->getCell();
				}
				
				//fill instance_id from debtor
				$this->instance_id = $this->db['debtor']->unSelect()->select('instance_id')->where('id = ?',[$this->debtor_id])->getCell();
				
			}
		});
		
	}
	function now(){
		$date = new DateTime();
		$date->setTimestamp(time());
		return $date->format('Y-m-d H:i:s');
	}



	protected function trashDirectory(){
		return $this->__dirDb.'/'.$this->trashDirectoryName.'/'.$this->_type.'/'.$this->id;
	}
	protected function fkToNullIfNotExists(&$trash,$type,&$keep=false){
		$fkeys = $this->db->getKeyMapForType($type);
		foreach($fkeys as $fkey){
			$from = $fkey['from'];
			if(isset($trash[$from])){
				if(!isset($this->db[$fkey['table']][$trash[$from]])){
					$keep = true;
					unset($trash[$from]);
				}
			}
		}
	}
	function trash(){
		if(!$this->id)
			return;
		$dirDb = $this->trashDirectory();
		if(!is_dir($dirDb))
			@mkdir($dirDb,0777,true);
		$time = $this->now();
		foreach($this->db as $tableName=>$table){
			$fkeys = $this->db->getKeyMapForType($tableName);
			$relatedFkeys = [];
			$dependent = false;
			foreach($fkeys as $fk){
				if($fk['table']==$this->_type){
					$relatedFkeys[] = $fk['from'];
					$dependent = $dependent||strtoupper($fk['on_delete'])=='CASCADE';
				}
			}
			if(!empty($relatedFkeys)){
				$dirDbTime = $dirDb.'/'.$time;
				if(!is_dir($dirDbTime))
					mkdir($dirDbTime);
				$fp = fopen($dirDbTime.'/'.$tableName.'.jsonl','a');
				foreach($this->db->one2many($this,$tableName) as $row){
					$properties = [];
					foreach($row as $k=>$v){
						if(substr($k,0,1)!='_'&&(!$dependent||in_array($k,$relatedFkeys)))
							$properties[$k] = $v;
					}
					fwrite($fp,json_encode($properties)."\n");
				}
				fclose($fp);
			}
		}
		$properties = [];
		foreach($this as $k=>$v){
			if(substr($k,0,1)!='_')
				$properties[$k] = $v;
		}
		$this->db['trash'][] = ['table'=>$this->_type,'pk'=>$this->id,'time'=>$time,'row'=>json_encode($properties)];
		unset($this->db[$this->_type][$this->id]);
	}
	function restore($time=null){
		if(!$this->id)
			return;
		$this->db->debug();
		$q = $this->db['trash']->select('id')->where('`table` = ? and pk = ?',[$this->_type,$this->id])->limit(1);
		if($time){
			$q->where('time = ?',[$time]);
		}
		$trashId = $q->getCell();
		if(!$trashId){
			throw new Exception('No row found in trash to restore '.$this->_type.' '.$this->id.' '.$this->time);
		}
		$trash = $this->db['trash'][$trashId];
		$trashRow = json_decode($trash->row,true);
		if(!isset($this->db[$this->_type][$trashRow['id']])){
			$trashRow['_forcePK'] = true;
		}
		
		$this->fkToNullIfNotExists($trash,$this->_type,$keep);
		
		$this->db[$this->_type][] = $trashRow;

		$dirDb = $this->trashDirectory();
		$dirDbTime = $dirDb.'/'.$trash->time;
		
		
		foreach(glob($dirDbTime.'/*.jsonl') as $rowsFile){
			$type = pathinfo($rowsFile,PATHINFO_FILENAME);
			$fp = fopen($rowsFile,'r');
			$table = $this->db[$type];
			while(false!==$line=fgets($fp)){
				$row = json_decode($line,true);
				if(!isset($this->db[$type][$row['id']])){
					$row['_forcePK'] = true;
				}
				$this->fkToNullIfNotExists($row,$type,$keep);
				$table[] = $row;
			}
			fclose($fp);
		}
		
		if(!$keep){
			unset($this->db['trash'][$trashId]);
			foreach(glob($dirDbTime.'/*.jsonl') as $rowsFile){
				unlink($rowsFile);
			}
			rmdir($dirDbTime);
		}
	}
	
	function getDynamicData(){
		$result = $this->getArray();
		if(method_exists($this,'getDynamic')){
			$result = array_merge($result,(array)$this->getDynamic());
		}
		return $result;
	}


}
