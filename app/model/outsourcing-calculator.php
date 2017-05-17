<?php
namespace App\Model;
use InvalidArgumentException;
class OutsourcingCalculator{
	const DAY_PER_MONTH = 'day_per_month';
	const COST_PER_MONTH = 'cost_per_month';
	protected $prices = [
		0=>[
			self::DAY_PER_MONTH=>2,
			self::COST_PER_MONTH=>750,
		],
		50=>[
			self::DAY_PER_MONTH=>5,
			self::COST_PER_MONTH=>1500,
		],
		100=>[
			self::DAY_PER_MONTH=>5,
			self::COST_PER_MONTH=>1500,
		],
		150=>[
			self::DAY_PER_MONTH=>7,
			self::COST_PER_MONTH=>2300,
		],
		200=>[
			self::DAY_PER_MONTH=>9,
			self::COST_PER_MONTH=>2800,
		],
	];
	protected function get($amount,$key){
		$rlevel = 0;
		foreach(array_keys($this->prices) as $level){
			if($amount>$level){
				$rlevel = $level;
			}
			else{
				break;
			}
		}
		return $this->prices[$rlevel][$key];
	}
	function getDayPerMonth($amount){
		return $this->get($amount,self::DAY_PER_MONTH);
	}
	function getCostPerMonth($amount){
		return $this->get($amount,self::COST_PER_MONTH);
	}
}
