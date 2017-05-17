<?php
namespace App\Model;
use InvalidArgumentException;
use App\Model\Lead;
class PriceCalculator{
	
	//prices[ category ][ type ]([ seniority ])[ level (>) ][ honoraryPercent | procedurePercent | procedure ]
	protected $prices = [
		Lead::CHEQUE_IMPAYE=> [
			Lead::AMIABLE=> [
				0 => [
					'honoraryPercent'       => 15,
					'procedurePercent'      => 0,
					'procedureStatic'       => false,
				],
				128 => [
					'honoraryPercent'       => 15,
					'procedurePercent'      => 0,
					'procedureStatic'       => false,
				],
				1280 => [
					'honoraryPercent'       => 15,
					'procedurePercent'      => 0,
					'procedureStatic'       => false,
				],
			],
			Lead::JUDICIAIRE=> [
				0 => [
					'honoraryPercent'       => 15,
					'procedurePercent'      => 105,
					'procedureStatic'       => false,
				],
				128 => [
					'honoraryPercent'       => 15,
					'procedurePercent'      => 25,
					'procedureStatic'       => false,
				],
				1280 => [
					'honoraryPercent'       => 15,
					'procedurePercent'      => 20,
					'procedureStatic'       => false,
				],
			],
		],
		Lead::LOYER_IMPAYE=> [
			Lead::PRE_CONTENTIEUX => [
				0 => [
					'honoraryPercent'       => 13,
					'procedurePercent'      => 81,
					'procedureStatic'       => false,
				],
				128 => [
					'honoraryPercent'       => 10,
					'procedurePercent'      => 15,
					'procedureStatic'       => false,
				],
				1280 => [
					'honoraryPercent'       => 10,
					'procedurePercent'      => 11,
					'procedureStatic'       => false,
				],
			],
			Lead::ASSIGNATION_SAISIE => [
				0 => [
					'honoraryPercent'       => 13,
					'procedurePercent'      => 95,
					'procedureStatic'       => false,
				],
				128 => [
					'honoraryPercent'       => 10,
					'procedurePercent'      => 18,
					'procedureStatic'       => false,
				],
				1280 => [
					'honoraryPercent'       => 10,
					'procedurePercent'      => 15,
					'procedureStatic'       => false,
				],
			],
			Lead::ASSIGNATION_EXPULSION => [
				0 => [
					'honoraryPercent'       => 13,
					'procedurePercent'      => 126,
					'procedureStatic'       => false,
				],
				128 => [
					'honoraryPercent'       => 10,
					'procedurePercent'      => 25,
					'procedureStatic'       => false,
				],
				1280 => [
					'honoraryPercent'       => 10,
					'procedurePercent'      => 19,
					'procedureStatic'       => false,
				],
			],
		],
		Lead::CREANCE_COMMERCIALE=>[
			Lead::AMIABLE=> [
				Lead::PLUS1AN=> [
					0=> [
						'honoraryPercent'       => 20,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					1999=> [
						'honoraryPercent'       => 17,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					14999=> [
						'honoraryPercent'       => 14,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					24999=> [
						'honoraryPercent' => 11,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
				],
				Lead::MOINS1AN => [
					0=> [
						'honoraryPercent'       => 15,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					1999=> [
						'honoraryPercent'       => 14,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					14999=> [
						'honoraryPercent'       => 10,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
				],
			],

			Lead::PRE_CONTENTIEUX=> [
				Lead::PLUS1AN=> [
					0=> [
						'honoraryPercent'       => 20,
						'procedurePercent'      => false,
						'procedureStatic'       => 210,
					],
					1999=>[
						'honoraryPercent'       => 16,
						'procedurePercent'      => false,
						'procedureStatic'       => 210,
					],
					14999=>[
						'honoraryPercent'       => 11,
						'procedurePercent'      => false,
						'procedureStatic'       => 210,
					],
				],
				Lead::MOINS1AN=> [
					0=> [
						'honoraryPercent'       => 15,
						'procedurePercent'      => false,
						'procedureStatic'       => 210,
					],
					1999=> [
						'honoraryPercent'       => 14,
						'procedurePercent'      => false,
						'procedureStatic'       => 210,
					],
					14999=> [
						'honoraryPercent'       => 10,
						'procedurePercent'      => false,
						'procedureStatic'       => 210,
					],
				],
			],


			Lead::INJONCTION_DE_PAYER=> [
				Lead::PLUS1AN=> [
					0=> [
						'honoraryPercent'       => 20,
						'procedurePercent'      => false,
						'procedureStatic'       => 350,
					],
					1999=> [
						'honoraryPercent'       => 16,
						'procedurePercent'      => false,
						'procedureStatic'       => 350,
					],
					14999=> [
						'honoraryPercent'       => 11,
						'procedurePercent'      => false,
						'procedureStatic'       => 350,
					]
				],
				Lead::MOINS1AN => [
					0=> [
						'honoraryPercent'       => 15,
						'procedurePercent'      => false,
						'procedureStatic'       => 350,
					],
					1999=> [
						'honoraryPercent'       => 14,
						'procedurePercent'      => false,
						'procedureStatic'       => 350,
					],
					14999=> [
						'honoraryPercent'       => 10,
						'procedurePercent'      => false,
						'procedureStatic'       => 350,
					],
				],
			],

			Lead::ASSIGNATION_EN_REFERE=> [
				Lead::PLUS1AN=> [
					0=> [
						'honoraryPercent'       => 20,
						'procedurePercent'      => false,
						'procedureStatic'       => 1500,
					],
					1999=> [
						'honoraryPercent'       => 16,
						'procedurePercent'      => false,
						'procedureStatic'       => 1500,
					],
					14999=> [
						'honoraryPercent'       => 11,
						'procedurePercent'      => false,
						'procedureStatic'       => 1500,
					]
				],
				Lead::MOINS1AN=> [
					0=> [
						'honoraryPercent'       => 15,
						'procedurePercent'      => false,
						'procedureStatic'       => 1500,
					],
					1999=> [
						'honoraryPercent'       => 14,
						'procedurePercent'      => false,
						'procedureStatic'       => 1500,
					],
					14999=> [
						'honoraryPercent'       => 10,
						'procedurePercent'      => false,
						'procedureStatic'       => 1500,
					],
				],
			],
		],
		Lead::REACTIVATION_CREANCE=>[
			Lead::AMIABLE=>[
				Lead::PLUS1AN=> [
					0=> [
						'honoraryPercent'       => 20,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					1999=> [
						'honoraryPercent'       => 17,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					14999=> [
						'honoraryPercent'       => 13,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					24999=> [
						'honoraryPercent'       => 11,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
				],
				Lead::MOINS1AN=> [
					0=> [
						'honoraryPercent'       => 18,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					1999=> [
						'honoraryPercent'       => 14,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
					14999=> [
						'honoraryPercent'       => 10,
						'procedurePercent'      => false,
						'procedureStatic'       => 0,
					],
				],
			],
			Lead::PRE_CONTENTIEUX=> [
				0=>[
					'honoraryPercent'       => 20,
					'procedurePercent'      => false,
					'procedureStatic'       => 210,
				],
				1999=> [
					'honoraryPercent'       => 17,
					'procedurePercent'      => false,
					'procedureStatic'       => 210,
				],
				14999=> [
					'honoraryPercent'       => 13,
					'procedurePercent'      => false,
					'procedureStatic'       => 210,
				],
				24999=> [
					'honoraryPercent'       => 11,
					'procedurePercent'      => false,
					'procedureStatic'       => 210,
				],
			],
			Lead::INJONCTION_DE_PAYER=> [
				0=>[
					'honoraryPercent'       => 20,
					'procedurePercent'      => false,
					'procedureStatic'       => 350,
				],
				1999=> [
					'honoraryPercent'       => 17,
					'procedurePercent'      => false,
					'procedureStatic'       => 350,
				],
				14999=> [
					'honoraryPercent'       => 13,
					'procedurePercent'      => false,
					'procedureStatic'       => 350,
				],
				24999=> [
					'honoraryPercent' => 11,
					'procedurePercent'      => false,
					'procedureStatic'       => 350,
				],
			],
			Lead::ASSIGNATION_AU_FOND=> [
				0=>[
					'honoraryPercent'       => 20,
					'procedurePercent'      => false,
					'procedureStatic'       => 1500,
				],
				1999=> [
					'honoraryPercent'       => 17,
					'procedurePercent'      => false,
					'procedureStatic'       => 1500,
				],
				14999=> [
					'honoraryPercent'       => 13,
					'procedurePercent'      => false,
					'procedureStatic'       => 1500,
				],
				24999=> [
					'honoraryPercent'       => 11,
					'procedurePercent'      => false,
					'procedureStatic'       => 1500,
				],
			],
		],
	];
	
	function get($amount,$key,$category,$type,$seniority=null){
		$r = &$this->prices;
		if(!isset($r[$category])){
			throw new InvalidArgumentException('Unknow category '.$category);
		}
		$r = &$r[$category];
		if(!isset($r[$type])){
			throw new InvalidArgumentException('Unknow type '.$type.' in category '.$category);
		}
		$r = &$r[$type];
		if(key($r)!=0){
			if(!isset($r[$seniority])){
				throw new InvalidArgumentException('Unknow seniority '.$seniority.' in category '.$category.' and type '.$type);
			}
			$r = &$r[$seniority];
		}
		
		$rlevel = 0;
		foreach(array_keys($r) as $level){
			if($amount>$level){
				$rlevel = $level;
			}
			else{
				break;
			}
		}
		$r = &$r[$rlevel];
		switch($key){
			case 'honorary':
				return $r['honoraryPercent']*$amount/100;
			break;
			case 'procedure':
				return $r['procedureStatic']!==false ? $r['procedureStatic'] : $r['procedurePercent']*$amount/100;
			break;
			default:
				return $r[$key];
			break;
		}
	}
	
	function getCiAmiableProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CHEQUE_IMPAYE,Lead::AMIABLE);
	}
	function getCiJudiciaireProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CHEQUE_IMPAYE,Lead::JUDICIAIRE);
	}
	function getLiPreContentieuxProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::LOYER_IMPAYE,Lead::PRE_CONTENTIEUX);
	}
	function getLiAssignationSaisieProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::LOYER_IMPAYE,Lead::ASSIGNATION_SAISIE);
	}
	function getLiAssignationExpulsionProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::LOYER_IMPAYE,Lead::ASSIGNATION_EXPULSION);
	}
	
	function getCcAmiablePlus1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::AMIABLE,Lead::PLUS1AN);
	}
	function getCcAmiableMoins1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::AMIABLE,Lead::MOINS1AN);
	}
	
	function getCcPreContentieuxPlus1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::PRE_CONTENTIEUX,Lead::PLUS1AN);
	}
	function getCcPreContentieuxMoins1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::PRE_CONTENTIEUX,Lead::MOINS1AN);
	}
	
	function getCcInjonctionDePayerPlus1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::INJONCTION_DE_PAYER,Lead::PLUS1AN);
	}
	function getCcInjonctionDePayerMoins1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::INJONCTION_DE_PAYER,Lead::MOINS1AN);
	}
	
	function getCcAssignationEnReferePlus1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::ASSIGNATION_EN_REFERE,Lead::PLUS1AN);
	}
	function getCcAssignationEnRefereMoins1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::ASSIGNATION_EN_REFERE,Lead::MOINS1AN);
	}
	
	function getRcAmiablePlus1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::REACTIVATION_CREANCE,Lead::AMIABLE,Lead::PLUS1AN);
	}
	function getRcAmiableMoins1AnProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::REACTIVATION_CREANCE,Lead::AMIABLE,Lead::MOINS1AN);
	}
	
	function getRcPreContentieuxProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::REACTIVATION_CREANCE,Lead::PRE_CONTENTIEUX);
	}
	function getRcInjonctionDePayerProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::REACTIVATION_CREANCE,Lead::INJONCTION_DE_PAYER);
	}
	function getRcAssignationAuFondProcedure($amount){
		return $this->get($amount,Lead::PROCEDURE,Lead::REACTIVATION_CREANCE,Lead::ASSIGNATION_AU_FOND);
	}
	
	function getCiAmiableHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CHEQUE_IMPAYE,Lead::AMIABLE);
	}
	function getCiJudiciaireHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CHEQUE_IMPAYE,Lead::JUDICIAIRE);
	}
	function getLiPreContentieuxHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::LOYER_IMPAYE,Lead::PRE_CONTENTIEUX);
	}
	function getLiAssignationSaisieHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::LOYER_IMPAYE,Lead::ASSIGNATION_SAISIE);
	}
	function getLiAssignationExpulsionHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::LOYER_IMPAYE,Lead::ASSIGNATION_EXPULSION);
	}
	
	function getCcAmiablePlus1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::AMIABLE,Lead::PLUS1AN);
	}
	function getCcAmiableMoins1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::AMIABLE,Lead::MOINS1AN);
	}
	
	function getCcPreContentieuxPlus1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::PRE_CONTENTIEUX,Lead::PLUS1AN);
	}
	function getCcPreContentieuxMoins1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::PRE_CONTENTIEUX,Lead::MOINS1AN);
	}
	
	function getCcInjonctionDePayerPlus1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::INJONCTION_DE_PAYER,Lead::PLUS1AN);
	}
	function getCcInjonctionDePayerMoins1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::INJONCTION_DE_PAYER,Lead::MOINS1AN);
	}
	
	function getCcAssignationEnReferePlus1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::ASSIGNATION_EN_REFERE,Lead::PLUS1AN);
	}
	function getCcAssignationEnRefereMoins1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::ASSIGNATION_EN_REFERE,Lead::MOINS1AN);
	}
	
	function getRcAmiablePlus1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::REACTIVATION_CREANCE,Lead::AMIABLE,Lead::PLUS1AN);
	}
	function getRcAmiableMoins1AnHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::REACTIVATION_CREANCE,Lead::AMIABLE,Lead::MOINS1AN);
	}
	
	function getRcPreContentieuxHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::REACTIVATION_CREANCE,Lead::PRE_CONTENTIEUX);
	}
	function getRcInjonctionDePayerHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::REACTIVATION_CREANCE,Lead::INJONCTION_DE_PAYER);
	}
	function getRcAssignationAuFondHonorary($amount){
		return $this->get($amount,Lead::HONORARY,Lead::REACTIVATION_CREANCE,Lead::ASSIGNATION_AU_FOND);
	}
	
	function getCcAmiableProcedure($amount,$seniority){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::AMIABLE,$seniority);
	}
	function getCcPreContentieuxProcedure($amount,$seniority){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::PRE_CONTENTIEUX,$seniority);
	}
	function getCcInjonctionDePayerProcedure($amount,$seniority){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::INJONCTION_DE_PAYER,$seniority);
	}
	function getCcAssignationEnRefereProcedure($amount,$seniority){
		return $this->get($amount,Lead::PROCEDURE,Lead::CREANCE_COMMERCIALE,Lead::ASSIGNATION_EN_REFERE,$seniority);
	}
	
	function getCcAmiableHonorary($amount,$seniority){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::AMIABLE,$seniority);
	}
	function getCcPreContentieuxHonorary($amount,$seniority){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::PRE_CONTENTIEUX,$seniority);
	}
	function getCcInjonctionDePayerHonorary($amount,$seniority){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::INJONCTION_DE_PAYER,$seniority);
	}
	function getCcAssignationEnRefereHonorary($amount,$seniority){
		return $this->get($amount,Lead::HONORARY,Lead::CREANCE_COMMERCIALE,Lead::ASSIGNATION_EN_REFERE,$seniority);
	}
	
	function getRcAmiableProcedure($amount,$seniority){
		return $this->get($amount,Lead::PROCEDURE,Lead::REACTIVATION_CREANCE,Lead::AMIABLE,$seniority);
	}
	function getRcAmiableHonorary($amount,$seniority){
		return $this->get($amount,Lead::HONORARY,Lead::REACTIVATION_CREANCE,Lead::AMIABLE,$seniority);
	}
}
