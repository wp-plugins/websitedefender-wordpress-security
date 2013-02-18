(function($)
{

$.widget('ui.wsdplugin_editionFeatures',
{
	_init: function()
	{
		if ( this.options.user && this.options.hash )
		{
			var self = this;
			var callback = function()
			{
				wsdplugin_doHTTPRPC('cUser.login', [self.options.user, self.options.hash], self, self.render,
					function(message, code) {
						$.wsdplugin_showError(message, code);
						self._renderStatus({u: 0})
					});
			};
			setTimeout(callback, 400);
		}
		else
		{
			this._renderStatus({u: 0});
		}
	},

	render: function()
	{
		wsdplugin_logger.info(this.widgetName + '._render');
		this.reload();
	},

	reload: function()
	{
		wsdplugin_logger.info(this.widgetName + '._reload');

		wsdplugin_doHTTPRPC('cTargets.getFeaturesStatus', [this.options.targetId], this, this._renderStatus,
			function(message, code) { $.wsdplugin_showError(message, code); this._renderStatus({u: 0}) });
	},


	_renderStatus: function(status)
	{
		var userType = status['u'];
			malwareStatus = status['lms'],
			backupStatus = status['lbs'],
			hackingCheck = status['lhdc']
			completeScan = status['lcss'],
			adminSurvScan = status['laass'],
			dns = status['dns'],
			dnsexp = status['dnsexp'];


		$('[data-jsrender-template]')
			.each( function()
			{
				$(this).html(
					$('#' + $(this).attr('data-jsrender-template')).render(status)
				);
			});
	}

});

})(jQuery);
