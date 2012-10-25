(function($)
{
$.widget('ui.wsdplugin_status',
{
	_lastRefresh: null,

	_create: function()
	{
		wsdplugin_logger.info(this.widgetName + '._create');

		var self = this;

		$('.wsdplugin_page_alert_types_current,.wsdplugin_page_alert_types_ignored,.wsdplugin_page_alert_types_resolved')
			.bind('wsdplugin_alerttypeslistalertsloaded', function() {
				self.reload();
			});

		$('.wsdplugin_page_alert_list').bind('wsdplugin_alertslistalertsloaded', function() { self.reload(); });
	},

	_init: function()
	{
		wsdplugin_logger.info(this.widgetName + '._init');
	},

	render: function()
	{
		wsdplugin_logger.info(this.widgetName + '._render');
		this.reload();
	},

	reload: function()
	{
		wsdplugin_logger.info(this.widgetName + '._reload');

		// Check last refresh
		if (this._lastRefresh !== null)
		{
			var now = new Date();
			var lastRefresh = new Date(this._lastRefresh.getTime());
			lastRefresh.setMinutes(lastRefresh.getMinutes() + 5);

			if (lastRefresh.getTime() > now.getTime()) {
				wsdplugin_logger.debug(this.widgetName, '.reload', ' - Skipped');
				return;
			}
		}

		this._clearView();

		wsdplugin_doHTTPRPC('cTargets.getDetail', [this.options.targetId, ['scan', 'malware', 'status', 'dns']], this,
			function(data)
			{
				this._lastRefresh = new Date();

				// Malware
				var malwareReported = [];
				var malwareStatusAvailable = false;
				var providers = ['MalwareDomainList', 'Website Defender', 'Google', 'SpamHaus', 'abuse.ch'];

				for (var i in providers)
				{
					var provider = providers[i];

					if (typeof data.malware[provider] !== 'undefined')
					{
						if (data.malware[provider] !== '-')
						{
							malwareStatusAvailable = true;

							if (data.malware[provider].toUpperCase() !== 'OK')
							{
								malwareReported.push(provider);
							}
						}
					}
				}

				if (malwareStatusAvailable)
				{
					if (malwareReported.length > 0)
					{
						var text = '';

						if (malwareReported.length > 0 && malwareReported[0] == 'MalwareDomainList')
							text = 'In malware database';

						if (malwareReported.length > 1)
						{
							if (text !== '')
								text += ', ';

							text += 'Reported by ';
							text += malwareReported.splice(1).join(', ');
						}
						$('.wsdplugin_status_malware span:nth-child(2)', this.element).addClass('wsdplugin_status_ok').text(text);
					}
					else
					{
						$('.wsdplugin_status_malware span:nth-child(2)', this.element).addClass('wsdplugin_status_ok').text('OK');
					}
				}
				else
				{
					$('.wsdplugin_status_malware span:nth-child(2)', this.element).addClass('wsdplugin_status_ok').text('-');
				}

				// Scan
				if (data.scan.date && data.scan.date.length > 0)
				{
					var pos = data.scan.date.lastIndexOf(':');
					if (pos > -1) data.scan.date = data.scan.date.substr(0, pos);
				}

				$('.wsdplugin_status_scan span:nth-child(2)', this.element).text(data.scan.date);

				// Avg response time
				$('.wsdplugin_status_time span:nth-child(2)', this.element).text(data.scan.avrg);

				// Target ID
				$('.wsdplugin_status_target span:nth-child(2)', this.element).text(this.options.targetId);

				// DNS
				var dns = 'Not available yet.';
				if (data.dns.status == 'up') dns = 'UP';
				if (data.dns.status == 'down') dns = 'DOWN';
				if (data.dns.status == '-') dns = '-';

				var expires = '-',
					expired = '';

				if (data.dns.exp.dateDiff) {
					if (data.dns.exp.dateDiff > 31) {
						expires = 'Expires in ' + data.dns.exp.dateDiff + ' days';
					}
					else if (data.dns.exp.dateDiff > 0) {
						expires = 'About to expire!';
					}
					else {
						expires = 'Expired!';
						expired = true;
					}
				}

				$('.wsdplugin_status_dns span:nth-child(2)').text(expires).addClass(expired ? 'wsdplugin_status_bad' : 'wsdplugin_status_ok');
			},
			function(error) {
				$.wsdplugin_showError(error);
			});
	},

	_clearView: function()
	{
		$('.wsdplugin_status_box span:nth-child(2)').removeClass().text('-');
	}
});

})(jQuery);
