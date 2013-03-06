$(function() {
	$('.dial-schedule-applet input.dial-whisper-radio').live('click', function(event) {
		var tr = $(this).closest('tr');
		$('tr', tr.closest('table')).each(function (index, element) {
			$(element).removeClass('on').addClass('off');
		});
		tr.addClass('on').removeClass('off');
	});
});