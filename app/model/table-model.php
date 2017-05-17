<?php
namespace App\Model;
use FoxORM\Entity\TableWrapperSQL;
use FoxORM\DataSource;
use FoxORM\DataTable;
use App\Route\User;
use RedCat\Strategy\Di;

class TableModel extends TableWrapperSQL{
	protected $user;
	protected $_di;
	protected $scopeInstance;
	function __construct($type, DataSource $db=null, DataTable $table=null,User $user, Di $di){
		parent::__construct($type, $db, $table);
		$this->user = PHP_SAPI!='cli'?$user:false;
		$this->_di = $di;
	}
	function scopeInstance(){
		$this->scopeInstance = true;
		$o = $this->dataTable->getClone();
		if($this->user&&$this->columnExists('instance_id')){
			$o = $o->where('`'.$this->type.'`.`instance_id` = ?',[$this->getInstance()]);
		}
		return $o;
	}
	
	function getScope(){
		return $this->columnExists('instance_id')?['instance_id'=>$this->getInstance()]:null;
	}
	function getInstance(){
		return $this->user?$this->user->getInstance():false;
	}
	
	function readRow($id,array $scope=null){
		if($this->scopeInstance){
			$scope = $this->getScope();
		}
		return $this->_readRow($id,$scope);
	}
	function putRow($obj,$id=null,array $scope=null){
		if($this->scopeInstance){
			if(!$id){
				$obj->_one_instance = ['_type'=>'user','_modified'=>false,'id'=>$this->getInstance()];
			}
			else{
				$scope = $this->getScope();
			}
		}
		return $this->dataTable->_putRow($obj,$id,$scope);
	}
	function deleteRow($id,array $scope=null){
		if($this->scopeInstance){
			$scope = $this->getScope();
		}
		return $this->dataTable->_deleteRow($id,$scope);
	}
}
