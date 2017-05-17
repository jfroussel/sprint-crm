<?php

date_default_timezone_set('Europe/Paris');
setlocale(LC_MONETARY, 'fr_FR.UTF-8');

RedCat\Autoload\LowerCasePSR4::getInstance()->addNamespace('App',REDCAT_CWD.'app')->splRegister();

return [
	'$'=>[
		'default-gravatar'=>'info@jfroussel.com',
		':databaseMap'=>[
			0 => [
				'modelClassPrefix'=> ['App\Model\Entity\\'],
				'entityClassDefault'=> 'App\Model\EntityModel',
				'tableWrapperClassDefault'=> 'App\Model\TableModel',
			],
		],
		'superRoot'=>[
			'email'=>'test@test.com',
			'login'=>'demo',
			'password'=>'demo',
		],
		'mail'=>[
			'host'=>'mail.wildsurikat.com',
			'username'=>'desico@surikat.pro',
			'password'=>'JFRtnjsjade2010',
			'port'=>26,
		],
		'api'=>[
			'mailgun'=>[
				'key'=>'key-667619638d09aabc4527d03bb86235a2',
//				'domain'=>'sandboxa44020dd12f846afbe9a7813e1fc0ce0.mailgun.org',
                'domain'=>'mg.mycreance.com',

			],
			'activetrail'=>[
				'key'=>'0X5FE10F4EBD36FDC9A93A499FE24E9189E75021D4321F88AAE2883F4C86AA9492E7367A2C8C0C80B1EFD6A8DB75EFD88D',
				'url'=>'https://webapi.mymarketing.co.il/api/smscampaign/OperationalMessage',
			],
		],
        'urlBaseHref'=>'http://mycreance.com/',
	],
	'rules'=>[
		App\Api\AbstractSms::class => [
 			'construct'=>[
				'$apiKey'=>'api.activetrail.key',
				'$apiUrl'=>'api.activetrail.url',
			],
		],
        App\Api\AbstractMail::class => [
            'construct'=>[
                '$mailGunApiKey' => 'api.mailgun.key',
                '$mailGunDomain' => 'api.mailgun.domain',
            ],
        ],

		SP\Session::class => [ //Service Postal
 			'construct'=>[
				//'url_test'=>'',
				'url_production'=>__DIR__.DIRECTORY_SEPARATOR.'.service-postal'.DIRECTORY_SEPARATOR,
//                'api_login'=>'jean-francois.roussel@desico.fr',
//                'api_password'=>'123456',
				'api_login'=>'courriers@mycreance.com',
				'api_password'=>'1Forester@-1',
				//'flag_test'=>true,
			],
		],
		Redis::class => [
			'shared'=> true,
			'call' => [
				'connect'=> ['127.0.0.1'],
			]
		],
		RedCat\Identify\PHPMailer::class => [
 			'shared'=>true,
 			'construct'=>[
				
				'$host'=>'mail.host',
				'$username'=>'mail.username',
				'$password'=>'mail.password',
				'$port'=>'mail.port',
				
				'secure'=>null,
				'sendmail'=>false,
				'exceptions'=>false,
				
				'fromEmail'=>'no-reply@mycreance.com',
				'fromName'=>'MyCreance - MyCreance',
				'replyEmail'=>'no-reply@mycreance.com',
				'replyName'=>'MyCreance - MyCreance',


				'SMTPOptions'=>[
					'ssl' => [ //not secure - enable for dev
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					]
				],

			]
			
		],
		
		FoxORM\Bases::class	=> [
			'shared'=>true,
			'construct' => [
				'entityClassDefault'=>App\Model\Entity::class,
			],
		],
		App\Route\Session::class=>[
			'shared'=>true,
			'construct'=>[
				'sessionName'=>'mycreance',
			]
		],
		App\Modules\Auth\Auth::class=>[
			'construct'=>[
				'$rootEmail' => 'superRoot.email',
				'$rootLogin' => 'superRoot.login',
				'$rootPassword' => 'superRoot.password',
				'rootName'	=> 'Developer',
			],
		],
		App\Route\Route::class => [
			'shared'=>true,
			'instanceOf'=>"#router",
		],
		App\Route\Session::class=>[
			'shared'=>true,
			'instanceOf'=>App\Route\Session::class,
		],
		App\Route\User::class => [
			'shared'=>true,
			'instanceOf'=>App\Route\User::class,
		],
		App\Model\Db::class => [
			'shared'=>true,
		],
	],
];
