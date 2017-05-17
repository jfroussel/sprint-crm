<?php
namespace App\Route;
use App\Route\Session;
use JsonSerializable;
class User implements JsonSerializable{
	function __construct(Session $session){
		$this->session = $session;
	}
	function getInstance(){
		return $this->__get('instance_id')?:$this->__get('id');
	}
	function getSession(){
		return $this->session;
	}
	function getEmail(){
		return $this->__get('email');
	}
	function getId(){
		return $this->__get('id');
	}

	function __get($k){
		return $this->session->$k;
	}
	function getDisplayName(){
		if($this->name)
			return $this->name;
		if($this->login)
			return $this->login;
		if($this->email)
			return $this->email;
	}
	function jsonSerialize(){
		$data = $this->session->get();
		unset($data['_FP_']);
		return $data;
	}
}
