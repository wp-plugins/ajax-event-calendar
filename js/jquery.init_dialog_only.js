/**
 * Handle: init_dialog_only
 * Version: 0.9.8
 * Deps: $jq
 * Enqueue: true
 */

$jq = jQuery.noConflict();
$jq().ready(function(){
	// public method for sidebar widget access
	$jq.eventDialog = function(e) {
	
		// check for modal html structure, if not present add it to the DOM
		if ($jq('aec-modal').length == 0) {
			var modal = '<div id="aec-modal"><div class="aec-title"></div><div class="aec-content"></div></div>';
			$jq('body').prepend(modal);
		}	
		eventDialog(e);
	}
	
	function eventDialog(e){
		$jq('#aec-modal').modal({
			overlayId: 'aec-modal-overlay',
			containerId: 'aec-modal-container',
			closeHTML: '<div class="close"><a href="#" class="simplemodal-close" title="' + custom.close_event_form + '">x</a></div>',
			minHeight: 35,
			opacity: 65,
			position: ['0',],
			overlayClose: true,
			onOpen: function (d) {
				var modal = this;
				modal.container = d.container[0];
				d.overlay.fadeIn(150, function () {
					$jq('#aec-modal', modal.container).show();
					var title = $jq('div.aec-title', modal.container),
						content = $jq('div.aec-content', modal.container),
						closebtn = $jq('div.close', modal.container);
					title.html(custom.loading_event_form).show();
					d.container.slideDown(150, function () {
						$jq.post(custom.ajaxurl, { action:'get_event', 'id': e.id }, function(data) {
							title.html(data.title);
							content.html(data.content);
							var h = content.height() + title.height() + 20;
							d.container.animate({ height: h }, 150, function () {
								closebtn.show();
								content.show();
							});
						}, 'json');
					});
				});
			},
			onClose: function (d){
				var modal = this;
				d.container.animate({ top:'-' + (d.container.height() + 20) }, 250, function (){
					modal.close();
				});
			}
		});
	}
});