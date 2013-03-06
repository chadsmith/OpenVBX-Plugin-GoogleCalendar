<?php
$ics = AppletInstance::getValue('calendar');
$events = preg_split('/BEGIN:VEVENT/', file_get_contents($ics), -1, PREG_SPLIT_NO_EMPTY);
$available = true;

array_shift($events);

foreach($events as $event) {
  preg_match('/DTSTART:(\d{8}T\d{6}Z)/', $event, $start);
  preg_match('/DTEND:(\d{8}T\d{6}Z)/', $event, $end);
  preg_match('/TRANSP:(OPAQUE|TRANSPARENT)/', $event, $availability);
  if(time() > strtotime($start[1]) && time() < strtotime($end[1]) && 'OPAQUE' == $availability[1])
    $available = false;
}

$response = new TwimlResponse;
$next = AppletInstance::getDropZoneUrl($available ? 'available' : 'busy');

if(!empty($next))
  $response->redirect($next);

$response->respond();