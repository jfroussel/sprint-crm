<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use Redis;

use Esker\ODSession_SessionService;
use Esker\ODSession_BindingResult;
use Esker\ODSession_LoginResult;
use Esker\ODSubmission_SubmissionService;
use Esker\ODSubmission_SessionHeader;
use Esker\ODSubmission_Transport;
use Esker\ODSubmission_TransportVars;
use Esker\ODSubmission_TransportAttachments;
use Esker\ODSubmission_Attachment;
use Esker\ODSubmission_WSFile;
use Esker\ODSubmission_Var;
use Esker\ODQuery_QueryService;
use Esker\ODQUery_SessionHeader;
use Esker\ODQuery_QueryHeader;
use Esker\ODQUery_QueryRequest;

class EskerMail extends ArtistPlugin{
	
	protected $description = "Send a mail on demand with esker api";
	protected $args = [
		'step_id'=>'step id'
	];
	protected $opts = [];
	protected $boolOpts = [];
	function __construct($name = null, Redis $redis, MainDb $db){
		parent::__construct($name);
		$this->redis = $redis;
		$this->db = $db;
	}
	
	protected function exec(){
		
		$id = $this->input->getArgument('step_id');
		$step = $this->db['running_scenario_step'][$id];
		$template = $step->_one_template->message;
		$debtor = $step->_one_running_scenario->_one_debtor;
		$instanceUser = $step->_one_instance;
		$varData = [
			''=>''
		];
		foreach($varData as $k=>$v){
			$template = str_replace('{{'.$k.'}}',$v,$template);
		}
		
		$m_Username			= 'mYus3r';				// Session username
		$m_Password			= 'mYpassw0rd';			// Session password
		$m_MailOnDemandAttachment1 = "data/invoice.pdf";	// Document sent - Change it by a path to a local document
		$m_PollingInterval	   = 15000;			// check fax status every 15 seconds


		//////////////////////////////////////////////////////////////////////
		// STEP #2 : Initialization + Authentication
		//////////////////////////////////////////////////////////////////////

		$this->output->writeln('Retrieving bindings');
		
		$session = new ODSession_SessionService();

		// Retrieve the bindings on the Application Server (location of the Web Services)
		$bindings = new ODSession_BindingResult;
		$bindings = $session->GetBindings($m_Username);
		if($ex = $session->soapException)  
		{
			$this->output->writeln('Call to GetBindings() failed with message: ' . $ex->Message);
			return; 
		}

		$this->output->writeln('Binding = ' . $bindings->sessionServiceLocation);

		// Now uses the returned URL with our session object, in case the Application Server redirected us.
		$session->Url = $bindings->sessionServiceLocation;

		$this->output->writeln('Authenticating session');

		// Authenticate the user on this session object to retrieve a sessionID
		$login = new ODSession_LoginResult;
		$login = $session->login($m_Username, $m_Password);

		if($ex = $session->soapException)  
		{    
			$this->output->writeln('Call to Login() failed with message: ' . $ex->Message);
			return;
		} 

		// This sessionID is an impersonation token representing the logged on user
		// You can use it with other Web Services objects, until you call Logout (which releases the
		// current sessionID and it's associated resources), or until the session times out (default is 10
		// minutes on the Application Server).
		$this->output->writeln('SessionID = ' . $login->sessionID);

		//////////////////////////////////////////////////////////////////////
		// STEP #3 : Simple Mail On Demand MOD submission
		//////////////////////////////////////////////////////////////////////

		// Creating and initializing a SubmissionService object.
		$submissionService = new ODSubmission_SubmissionService();		

		// Set the service URL with the location retrieved above with GetBindings()
		$submissionService->Url = $bindings->submissionServiceLocation;
					
		// Set the sessionID with the one retrieved above with Login()
		// Every action performed on this object will now use the authenticated context created in step 1
		$submissionService->SessionHeaderValue = new ODSubmission_SessionHeader;
		$submissionService->SessionHeaderValue->sessionID = $login->sessionID;

		$this->output->writeln('Sending Mail On Demand Request');

		// Now allocate a transport with transportName = 'MODEsker'
		$transport = new ODSubmission_Transport;
		$transport->recipientType = "";
		$transport->transportIndex = 0;
		$transport->transportName = 'MODEsker';
		
		// Specifies MailOnDemand variables (see documentation for their meanings)
		$transport->vars = new ODSubmission_TransportVars;
		$transport->vars->Var = array();
		$transport->vars->Var[0] = $this->createValue('Subject', 'Sample Mail On Demand');
		$transport->vars->Var[1] = $this->createValue('FromName', 'John DOE');
		$transport->vars->Var[2] = $this->createValue('FromCompany', 'Dummy Inc.');
		$transport->vars->Var[3] = $this->createValue('ToBlockAddress', 'ADERTY firm' .  chr(10) . 'Jaco Aderti' .  chr(10) . '17 Bella Villa Roma' .  chr(10) . '12666 Querbo' .  chr(10) . 'FRANCE');
		$transport->vars->Var[4] = $this->createValue('Color', 'Y');
		$transport->vars->Var[5] = $this->createValue('Cover', 'Y');
		$transport->vars->Var[6] = $this->createValue('BothSided', 'Y');
		$transport->vars->Var[7] = $this->createValue('MaxRetry', '3'); 

		// Specify a text attachment to append to the MailOnDemand.
		// The attachment content is inlined in the transport description
		$transport->attachments = new ODSubmission_TransportAttachments;
		$transport->attachments->Attachment = array();
		$transport->attachments->Attachment[0] = new ODSubmission_Attachment;
		$transport->attachments->Attachment[0]->sourceAttachment = $this->fileRead($m_MailOnDemandAttachment1,$submissionService);

		// Submit the complete transport description to the Application Server
		$result = $submissionService->SubmitTransport($transport);
		
		if($ex = $submissionService->soapException)
		{		
			$this->output->writeln('Call to SubmitTransport() failed with message: ' . $ex->Message);
			return;
		} 

		$this->output->writeln('Request submitted with transportID ' . $result->transportID);


		//////////////////////////////////////////////////////////////////////
		// STEP #4 : MailOnDemand tracking
		//////////////////////////////////////////////////////////////////////

		// Creating and initializing a QueryService object.
		$queryService = new ODQuery_QueryService();		

		// Set the service url with the location retrieved above with GetBindings()
		$queryService->Url = $bindings->queryServiceLocation;

		// Set the sessionID with the one retrieved above with Login()
		// Every action performed on this object will now use the authenticated context created in step 1
		$queryService->SessionHeaderValue = new ODQUery_SessionHeader;
		$queryService->SessionHeaderValue->sessionID = $login->sessionID;
		
		// Set the QueryRecipientTypeValue with a comma separated list of RecipientType
		// The following page lists the available recipient types and the corresponding transport names.
		// http://doc.esker.com/eskerondemand/cv_ly/en/webservices/index.asp?page=References/Common/RecipientTypes.html
		// Instead, the following page lists the variables common to all transports.
		// http://doc.esker.com/eskerondemand/cv_ly/en/webservices/index.asp?page=References/Fields/defaulttransportprintable.html
		$queryService->QueryHeaderValue = new ODQuery_QueryHeader;
		$queryService->QueryHeaderValue->recipientType = "MOD";

		// Build a request on the newly submitted fax transport using its unique identifier
		// We also specify the variables (attributes) we want to retrieve.
		$request = new ODQUery_QueryRequest;
		$request->nItems = 1;
		$request->attributes = 'State,ShortStatus,CompletionDateTime';
		$request->filter = '(ruidex=' . $result->transportID . ')';
				
		echo 'Checking for your MailOnDemand status...<BR>';

		$state = 0;
		$status = '';
		$date = '';

		while( true )
		{
			// Ask the Application Server
			$qresult = $queryService->QueryFirst($request);
			if($ex = $queryService->soapException)
			{		
				$this->output->writeln('Call to QueryFirst() failed with message: ' . $ex->Message);
				return;
			} 

			if( $qresult->nTransports == 1 )
			{
				// Hopefully, we found it
				// Parse the returned variables
				for($iVar=0; $iVar<$qresult->transports[0]->nVars; $iVar++)
				{
					if( strtolower( $qresult->transports[0]->vars[$iVar]->attribute ) == 'state' )
					{
						$state = $qresult->transports[0]->vars[$iVar]->simpleValue;
					}
					else if( strtolower( $qresult->transports[0]->vars[$iVar]->attribute ) == 'shortstatus' )
					{
						$status = $qresult->transports[0]->vars[$iVar]->simpleValue;
					}
					else if( strtolower( $qresult->transports[0]->vars[$iVar]->attribute ) == 'completiondatetime' )
					{
						$date = $qresult->transports[0]->vars[$iVar]->simpleValue;
					}
				}

				if( $state >= 90 )
					break;
						
				$this->output->writeln('MailOnDemand pending...');
			}
			else
			{
				$this->output->writeln('Error !! MailOnDemand not found in database');
				return;
			}

			// Wait 5 seconds, then try again...
			sleep(5);

		}

		if( $state >= 90 && $state <= 100 )
		{
			$this->output->writeln('MailOnDemand successfully sent with transportID ' . $result->transportID);
		}
		else
			$this->output->writeln('MailOnDemand failed at ' . $date . ', reason: ' . $status);		
			


		//////////////////////////////////////////////////////////////////////
		// STEP #5 : Release the session and its allocated resources
		//////////////////////////////////////////////////////////////////////

		// As soon as you call Logout(), the files allocated on the server during this session won't be available
		// anymore, so keep in mind that former urls are now useless...
		
		$this->output->writeln('Releasing session and server files');

		$session->Logout();
		if($ex = $session->soapException)
		{		
			$this->output->writeln('Call to Logout() failed with message: ' . $ex->Message);
			return;
		} 
	}
	
	
	// Method used to read data from a file and store them in a Web Service file object.
	protected function fileRead($filename,$submissionService){
		$wsFile = new ODSubmission_WSFile;
		$wsFile->mode = $submissionService->WSFILE_MODE['MODE_INLINED'];
		$wsFile->name = $this->shortFileName($filename);
		$myfile = fopen($filename,'r');
		$wsFile->content = (fread($myfile, filesize ($filename)));
		fclose($myfile);			
		return $wsFile;
	}

	// Helper method to return the last position of a search string in a source string
	protected function lastIndexOf($sourceString, $searchString) {
		$index = strpos(strrev($sourceString), strrev($searchString));
		$index = strlen($sourceString) - strlen($index) - $index;
		return $index;
	} 

	// Helper method to allocate and fill in Variable objects.
	protected function createValue($AttributeName,$AttributeValue){
		$var = new ODSubmission_Var;
		$var->attribute = $AttributeName;
		$var->simpleValue = utf8_encode($AttributeValue);
		$var->type = 'TYPE_STRING';
		return $var;
	}

	// Helper method to extract the short file name from a full file path
	protected function shortFileName($filename){
		$i = $this->lastIndexOf($filename,'/');
		if($i < 0 ) $i= $this->lastIndexOf($filename,'\\');
		if($i < 0 ) return $filename;
		return substr($filename,$i+1);
	}
}
