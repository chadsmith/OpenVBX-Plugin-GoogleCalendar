<?php
include_once('TwimlDialSchedule.php');
define('DIAL_COOKIE', 'state-'.AppletInstance::getInstanceId());

$ci =& get_instance();
$transcribe = (bool) $ci->vbx_settings->get('transcriptions', $CI->tenant->id);
$voice = $ci->vbx_settings->get('voice', $CI->tenant->id);
$language = $ci->vbx_settings->get('voice_language', $CI->tenant->id);
$timeout = $ci->vbx_settings->get('dial_timeout', $CI->tenant->id);

$dialer = new TwimlDialSchedule(array(
	'voice' => $voice,
	'language' => $language,
	'sequential' => true,
	'timeout' => $timeout
));
$dialer->set_state();

try {
	switch ($dialer->state) {
		case 'voicemail':
			$dialer->noanswer();
			break;
		case 'hangup':
			$dialer->hangup();
			break;
		default:
      $ics = AppletInstance::getValue('calendar');
      $events = preg_split('/BEGIN:VEVENT/', file_get_contents($ics), -1, PREG_SPLIT_NO_EMPTY);
      array_shift($events);
      $schedule = array();
      $dial_list = array();
      $dialed = 0;
      foreach($events as $event) {
        preg_match('/DTSTART[:;](?:VALUE=DATE:)?(\d{8}(?:T\d{6}Z)?)/', $event, $start);
        preg_match('/DTEND[:;](?:VALUE=DATE:)?(\d{8}(?:T\d{6}Z)?)/', $event, $end);
        preg_match('/LOCATION:([^\r\n]+)/', $event, $location);
        if(time() > strtotime($start[1].$start[2]) && time() < strtotime($end[1].$end[2])) {
          $schedule[] = $location[1];
      }
      if(empty($schedule))
        $dialer->unscheduled();
      else {
        $users = OpenVBX::getUsers();
        foreach($schedule as $i => $scheduled)
          if(preg_match('/^\+?[0-9\(\)\-\.\d]{10,}$/', $scheduled)) {
            $dial_list[] = $scheduled;
            unset($schedule[$i]);
          }
        foreach($users as $user)
          if(in_array($user->values['email'], $schedule))
            $dial_list[] = $user;
        foreach($dial_list as $to_dial)
          if($dialer->dial($to_dial))
            $dialed++;
        if($dialed == 0)
          $dialer->noanswer();
      }
	}
}
catch (Exception $e) {
	error_log('Dial Schedule Applet exception: '.$e->getMessage());
	$dialer->response->say("We're sorry, an error occurred while dialing. Goodbye.");
	$dialer->hangup();
}

$dialer->save_state();
$dialer->respond();