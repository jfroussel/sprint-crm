<?php
namespace App\Model\Entity;
use App\Model\TableModel;

class Litige_Table extends TableModel{
	function getAmountSumForDebtor($id,$formatCurrency=false){
        if($this->columnExists('amount')) {
            $promesses = $this
                ->unSelect()
                ->select("SUM(amount)")
                ->where('debtor_id = ?', [$id])
                ->getCell();
            if($formatCurrency){
				$promesses = $this->formatCurrency($promesses);
			}
            return $promesses;
        }
    }
    function getAmountSum(){
		if($this->columnExists('amount')) {
			return $this->formatCurrency($this->unSelect()->select("SUM(amount)")->getCell());
		}
	}
    protected function formatCurrency($number){
        return money_format('%#1n', $number );
    }
}
