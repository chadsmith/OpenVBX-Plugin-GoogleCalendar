<div class="vbx-applet dial-schedule-applet">
  <h2>Google Calendar URL (.ics)</h2>
	<div class="vbx-full-pane">
		<fieldset class="vbx-input-container">
				<input type="text" name="calendar" class="medium" value="<?php echo AppletInstance::getValue('calendar'); ?>" />
		</fieldset>
	</div>
	<br />
	<h2>If available...</h2>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('available') ?>
	</div>
	<br />
	<h2>If busy...</h2>
	<div class="vbx-full-pane">
		<?php echo AppletUI::DropZone('busy') ?>
	</div>
</div>