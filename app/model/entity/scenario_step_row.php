<?php
namespace App\Model\Entity;
use App\Model\EntityModel;
class Scenario_Step_Row extends EntityModel{
    protected $validateProperties = [
        'scenario_id',
        'template_id',
        'name',
        'category',
        'type',
        'active',
        'start_day',
        'letter',
        'length',
    ];
}
