<?php
	$ci =& get_instance();
	$ci->load->model('vbx_incoming_numbers');

	try {
		$numbers = $ci->vbx_incoming_numbers->get_numbers();
	}
	catch (VBX_IncomingNumberException $e) {
		log_message('Incoming numbers exception: '.$e->getMessage.' :: '.$e->getCode());
		$numbers = array();
	}

	$callerId = AppletInstance::getValue('callerId', null);
  $whisper = AppletInstance::getValue('dial-whisper', true);
?>
<div class="vbx-applet dial-schedule-applet">
  <h2>Google Calendar URL (.ics)</h2>
	<div class="vbx-full-pane">
		<fieldset class="vbx-input-container">
				<input type="text" name="calendar" class="medium" value="<?php echo AppletInstance::getValue('calendar'); ?>" />
		</fieldset>
	</div>
	<br />
	<h2>If somebody is scheduled...</h2>
	<div class="vbx-full-pane">
  	<h3>Caller ID</h3>
		<fieldset class="vbx-input-container">
			<select class="medium" name="callerId">
				<option value="">Caller's Number</option>
<?php if(count($numbers)) foreach($numbers as $number): $number->phone = normalize_phone_to_E164($number->phone); ?>
				<option value="<?php echo $number->phone; ?>"<?php echo $number->phone == $callerId ? ' selected="selected" ' : ''; ?>><?php echo $number->name; ?></option>
<?php endforeach; ?>
			</select>
		</fieldset>
    <br />
  	<h3>Whisper</h3>
  	<div class="radio-table">
  		<table>
  			<tr class="radio-table-row first <?php echo $whisper ? 'on' : 'off' ?>">
  				<td class="radio-cell">
  					<input type="radio" class='dial-whisper-radio' name="dial-whisper" value="1" <?php echo ($whisper) ? 'checked="checked"' : '' ?> />
  				</td>
  				<td class="content-cell">
  					<h4>Announce the caller</h4>
  				</td>
  			</tr>
  			<tr class="radio-table-row last <?php echo !$whisper ? 'on' : 'off' ?>">
  				<td class="radio-cell">
  					<input type="radio" class='dial-whisper-radio' name="dial-whisper" value="0" <?php echo (!$whisper) ? 'checked="checked"' : '' ?> />
  				</td>
  				<td class="content-cell">
  					<h4>Connect without announcing</h4>
  				</td>
  			</tr>
  		</table>
  	</div>
  	<br />
  	<h3>If nobody answers...</h3>
  	<?php echo AppletUI::DropZone('no-answer-redirect') ?>
  </div>
	<br />
	<h2>If nobody is scheduled...</h2>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('unscheduled-redirect') ?>
	</div>
</div>