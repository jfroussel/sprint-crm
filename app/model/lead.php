<?php
namespace App\Model;
abstract class Lead{
	const LOYER_IMPAYE = 'loyer-impaye';
	const CHEQUE_IMPAYE = 'cheque-impaye';
	const CREANCE_COMMERCIALE = 'creance-commerciale';
	const REACTIVATION_CREANCE = 'reactivation-creance';
	
	const AMIABLE = 'amiable';
	const JUDICIAIRE = 'judiciaire';
	const PRE_CONTENTIEUX = 'pre-contentieux';
	const ASSIGNATION_SAISIE = 'assignation-saisie';
	const ASSIGNATION_EXPULSION = 'assignation-expulsion';
	const INJONCTION_DE_PAYER = 'injonction-de-payer';
	const ASSIGNATION_EN_REFERE = 'assignation-en-refere';
	const ASSIGNATION_AU_FOND = 'assignation-au-fond';
	
	const PLUS1AN = '+1an';
	const MOINS1AN = '-1an';
	
	const PROCEDURE = 'procedure';
	const HONORARY = 'honorary';
}
