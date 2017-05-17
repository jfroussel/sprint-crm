<?php
namespace App\Model;
use FoxORM\Bases;
class Db implements \ArrayAccess {
	protected $db;
	protected $tables = [];
	function __construct(Bases $databases){
		$this->db = $databases[0];
	}
	function __call($f,$a){
		return call_user_func_array([$this->db,$f],$a);
	}
	function offsetSet($k,$v){
		$this->db[$k] = $v;
	}
	function offsetExists($k){
		return isset($this->db[$k]);
	}
	function offsetGet($k){
		if(!isset($this->tables[$k])){
			$this->tables[$k] = $this->db[$k]->scopeInstance();
			$this->tables[$k]->isFork();
		}
		return $this->tables[$k];
	}
	function offsetUnset($k){
		unset($this->db[$k]);
	}
}
