<?php
namespace App\Model;
trait PaperworkStateChangeTrait{
	function unsetRelatedPaperworksState($state){
		foreach($this->_many2many_paperwork as $paperwork){
			if($paperwork->state==$state){
				$paperwork->state = null;
				$paperwork->store();
			}
		}
	}
	function setRelatedPaperworksState($state){
		foreach($this->_many2many_paperwork as $paperwork){
			$paperwork->state = $state;
			$paperwork->store();
		}
	}
}
