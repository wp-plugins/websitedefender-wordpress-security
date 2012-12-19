(function($)
{
$.widget('ui.wsdplugin_status',
{
    _lastRefresh: null,

    _create: function()
    {
        wsdplugin_logger.info(this.widgetName + '._create');

        if (this.options.embed)
        {
            var self = this;

            $('.wsdplugin_page_alert_types_current,.wsdplugin_page_alert_types_ignored,.wsdplugin_page_alert_types_resolved')
                .bind('wsdplugin_alerttypeslistalertsloaded', function() {
                    self.reload();
                });

            $('.wsdplugin_page_alert_list').bind('wsdplugin_alertslistalertsloaded', function() { self.reload(); });
        }
    },

    _init: function()
    {
        wsdplugin_logger.info(this.widgetName + '._init');

        if (!this.options.embed)
        {
            if (this.options.email && this.options.hash)
            {
                var self = this;
                var callback = function() {
                    wsdplugin_doHTTPRPC('cUser.login', [self.options.email, self.options.hash], self, self.reload, self._clearView);
                };
                setTimeout(callback, 400);
            }
            else {
                this._clearView();
            }
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

        // Check last refresh
        if (this.options.embed && this._lastRefresh !== null)
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
                if (this.options.embed)
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
						$('.wsdplugin_status_malware span:nth-child(2)', this.element).addClass('wsdplugin_status_bad').text('Your website is infected with malware');
					}
					else
					{
						$('.wsdplugin_status_malware span:nth-child(2)', this.element).addClass('wsdplugin_status_ok').text('Your website is clean');
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

                    var pos = data.scan.date.indexOf(',');
                    if (pos > -1) data.scan.date = data.scan.date.substr(pos + 1);

                    data.scan.date = $.trim(data.scan.date);
				}

				$('.wsdplugin_status_scan span:nth-child(2)', this.element).text(data.scan.date);

				// Avg response time
                if (data.scan.avrg && data.scan.avrg.length > 1)
                {
                    data.scan.avrg = $.trim(data.scan.avrg.replace('sec', ''));
                    data.scan.avrg = Math.round(parseFloat(data.scan.avrg) * 1000);
                    data.scan.avrg += ' ms';
                }
				$('.wsdplugin_status_time span:nth-child(2)', this.element).text(data.scan.avrg);

				// Target ID
				$('.wsdplugin_status_target span:nth-child(2)', this.element).text(this.options.targetId);


                // Domain
				var expires = '-',
					expired = '';
				if (data.dns.exp.dateDiff) {
					if (data.dns.exp.dateDiff > 0) {
						expires = data.dns.exp.dateDiff + ' days';
					}
					else {
						expires = 'Expired!';
						expired = true;
					}
				}

				$('.wsdplugin_status_domain span:nth-child(2)', this.element).text(expires).addClass(expired ? 'wsdplugin_status_bad' : 'wsdplugin_status_ok');

                // DNS
                var dns = 'Not available yet.';
                var dnsStatusOk = true;
                if (data.dns.status == 'up') dns = 'Up and running';
                if (data.dns.status == 'down') {
                    dns = 'DNS not responding';
                    dnsStatusOk = false;
                }
                if (data.dns.status == '-') dns = '-';
                $('.wsdplugin_status_dns span:nth-child(2)', this.element).text(dns).addClass(dnsStatusOk ? 'wsdplugin_status_ok' : 'wsdplugin_status_bad');

			},
			function(error) {
				$.wsdplugin_showError(error);
			});
	},

	_clearView: function()
	{
		$('.wsdplugin_status_box span:nth-child(2)', this.element).removeClass().text('-');
	}
});

})(jQuery);
