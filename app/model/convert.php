<?php
namespace App\Model;
class Convert{
	static function formatCurrency($number){
        return money_format('%#1n', $number );
    }
}
