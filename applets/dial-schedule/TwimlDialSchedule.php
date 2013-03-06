<?php

class TwimlDialException extends Exception {};

class TwimlDialSchedule {
	/**
	 * Use the CodeIgniter session class to set the cookie
	 * Not using this has caused issues on some systems, but
	 * until we know that this squashes our bugs we'll leave
	 * the toggle to allow the legacy method of tracking
	 *
	 * @var bool
	 */
	private $use_ci_session = true;
	
	static $hangup_stati = array('completed', 'answered');
	static $voicemail_stati = array('no-answer', 'failed');
	
	protected $cookie_name;
		
	public $state;
	public $response;
	
	public $dial;
	
	protected $timeout = false;
	protected $voice = 'man';
	protected $language = 'en';
	
	protected $sequential = false;

	public $default_timeout = 20;
	
	public function __construct($settings = array()) {
		$this->response = new TwimlResponse;
		$this->cookie_name = 'state-'.AppletInstance::getInstanceId();
		$this->callerId = AppletInstance::getValue('callerId', null);
		if (empty($this->callerId) && !empty($_REQUEST['From'])) 
			$this->callerId = $_REQUEST['From'];
    $this->dial_whisper = AppletInstance::getValue('dial-whisper', true);
		$this->no_answer_redirect = AppletInstance::getDropZoneUrl('no-answer-redirect');
		$this->unscheduled_redirect = AppletInstance::getDropZoneUrl('unscheduled-redirect');
		if(count($settings))
			foreach($settings as $setting => $value) 
				if(isset($this->$setting))
					$this->$setting = $value;
	}

	public function getDial() {
		if(empty($this->dial)) 
			$this->dial = $this->response->dial(NULL, array(
					'action' => current_url(),
					'callerId' => $this->callerId,
					'timeout' => (!empty($this->timeout)) ? $this->timeout : $this->default_timeout,
					'sequential' => ($this->sequential ? 'true' : 'false')
				));
		return $this->dial;
	}

	public function callOpts($params) {
		$opts = array();
		if($params['whisper_to'] && $this->dial_whisper) 
			$opts['url'] = site_url('twiml/whisper?name='.urlencode($params['whisper_to']));
		return $opts;
	}
	
	public function dial($device_or_user) {
		$dialed = false;
		if($device_or_user instanceof VBX_User)
			$dialed = $this->dialUser($device_or_user);
		elseif($device_or_user instanceof VBX_Device) 
			$dialed = $this->dialDevice($device_or_user);
		else 
			$dialed = $this->dialNumber($device_or_user);
		return $dialed;
	}

	public function dialDevice($device) {
		$dialed = false;
		if($device->is_active) {
			$user = VBX_User::get($device->user_id);				
			$dial = $this->getDial();
			$call_opts = $this->callOpts(array(
				'whisper_to' => $user->first_name
			));
			if(strpos($device->value, 'client:') !== false)
				$dial->client(str_replace('client:', '', $device->value), $call_opts);
			else 
				$dial->number($device->value, $call_opts);
			$this->state = 'calling';
			$dialed = true;
		}
		return $dialed;
	}

	public function dialUser($user) {
		$dialed = 0;
		if(count($user->devices)) {
			$dial = $this->getDial();
			$call_opts = $this->callOpts(array(
				'whisper_to' => $user->first_name
			));
			foreach($user->devices as $device)
				if($device->is_active) {
					if(strpos($device->value, 'client:') !== false) 
						$dial->client(str_replace('client:', '', $device->value), $call_opts);
					else 
						$dial->number($device->value, $call_opts);
					$this->state = 'calling';
					$dialed++;
				}
		}
		return $dialed > 0;
	}

	public function dialNumber($number) {
		$dial = $this->getDial();
		$number = normalize_phone_to_E164($number);
		$dial->number($number);
		$this->state = 'calling';
		return true;
	}

	public function noanswer() {
		if(empty($this->no_answer_redirect))
		  die("empty no_answer_redirect");
			//$this->hangup();
		$this->response->redirect($this->no_answer_redirect);
	}

	public function unscheduled() {
		if(empty($this->unscheduled_redirect))
		  die("empty unscheduled_redirect");
			//$this->hangup();
		$this->response->redirect($this->unscheduled_redirect);
	}

	public function hangup() {
		$this->response->hangup();
	}

	public function respond() {
		$this->response->respond();
	}

	public function set_state() {
		$call_status = isset($_REQUEST['CallStatus']) ? $_REQUEST['CallStatus'] : null;
		$dial_call_status = isset($_REQUEST['DialCallStatus']) ? $_REQUEST['DialCallStatus'] : null;
		
		$this->state = $this->_get_state();

		if(in_array($dial_call_status, self::$hangup_stati) || in_array($call_status, self::$hangup_stati))
			$this->state = 'hangup';
		elseif(in_array($dial_call_status, self::$voicemail_stati))
			$this->state = 'voicemail';
		elseif(!$this->state) 
			$this->state = 'new';
	}

	private function _get_state() {
		$state = null;
		if($this->use_ci_session) {
			$CI =& get_instance();
			$state = $CI->session->userdata($this->cookie_name);
		}
		else 
			if(!empty($_COOKIE[$this->cookie_name])) 
				$state = $_COOKIE[$this->cookie_name];

		return $state;
	}

	public function save_state() {
		$state = $this->state;
		if($this->use_ci_session) {
			$CI =& get_instance();
			$CI->session->set_userdata($this->cookie_name, $state);
		}
		else
			set_cookie($this->cookie_name, $state, time() + (5 * 60));
	}
}