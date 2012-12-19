(function($)
{
$.widget('ui.wsdplugin_local_alerts',
{
	options: {
		alerts: []
	},

	_init: function()
	{
		// Render the alerts
		var body = $('tbody', this.element).empty();
		var anyExpander = false;
		var i;

		for (i in this.options.alerts)
		{
			if (this.options.alerts[i].detail) {
				anyExpander = true;
				break;
			}
		}

		if (this.options.alerts.length == 0)
		{
			body.append('<tr><td colspan="4">No alerts here...</td></tr>');
			return;
		}

		for (i in this.options.alerts)
		{
			var item = this.options.alerts[i],
				tr = $(document.createElement('tr')),
				slidingRow = null;


			// Expander
			if (item.detail)
			{
				slidingRow = $(document.createElement('tr'))
					.css('display', 'none')
					.append(
						$(document.createElement('td'))
							.css({'padding-top': '20px', 'padding-bottom': '20px', 'background-color': '#fff'})
							.html(item.detail)
							.attr('colspan', 4 - (anyExpander ? 0 : 1))
				);

				$(document.createElement('td')).addClass('wsdplugin_expander_open')
					.data('sliding-row', slidingRow)
					.append(
						$(document.createElement('a')).attr('href', '#').click(function()
						{
							var parentCell = $(this).closest('td');

							if (parentCell.hasClass('wsdplugin_expander_open'))
							{
								parentCell.removeClass('wsdplugin_expander_open');
								parentCell.addClass('wsdplugin_expander_close');
								parentCell.data('sliding-row').slideDown(40);
							}
							else if (parentCell.hasClass('wsdplugin_expander_close'))
							{
								parentCell.removeClass('wsdplugin_expander_close');
								parentCell.addClass('wsdplugin_expander_open');
								parentCell.data('sliding-row').slideUp(40);
							}
							return false;
						})
				)
				.appendTo(tr);
			}
			else if (anyExpander)
			{
				$(document.createElement('td')).appendTo(tr);
			}

			// Indicator
			$(document.createElement('td')).addClass('wsdplugin_alert_indicator wsdplugin_alert_indicator_' + this._getLevelName(item.severity))
				.appendTo(tr);

			// Title
			$(document.createElement('td'))
				.html(item.title)
				.attr('title', item.title)
				.addClass('wsdplugin_title')
				.appendTo(tr);

			// Entry time
			$(document.createElement('td')).css({'width': '140px', 'text-align': 'right'})
				.html(item.time)
				.appendTo(tr);

			body.append(tr);
			if (slidingRow) slidingRow.appendTo(body);
		}
	},

	_getLevelName: function(severity)
	{
		switch (severity)
		{
			case 2:
				return 'low';
			case 3:
				return 'medium';
		}
		return 'critical';
	}
});

})(jQuery);
