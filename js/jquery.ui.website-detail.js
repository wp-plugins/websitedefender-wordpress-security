(function($)
{
$.widget('ui.wsdplugin_websiteDetail',
{
	_$alerts: null,
	_$pageTitle: null,


	options: {

	},

	_create: function()
	{
		wsdplugin_logger.debug(this.widgetName + '._create');

		var self = this;

		this._$alerts = $('.wsdplugin_page_alerts', this.element);
		this._$pageTitle = $('.wsdplugin_page_title', this.element);

		$('a[href=#refresh]', this._$pageTitle).click(function() { self.render(wsdplugin_uh.get('p')); return false; });
	},

	_init: function()
	{
		wsdplugin_logger.debug(this.widgetName + '._init');
	},

	render: function(panel)
	{
		wsdplugin_logger.info(this.widgetName + '._render', panel);

		// Hide everything first
		this._$alerts.hide();

		switch (panel)
		{
			case 'alist':
			case 'alerts':
				this._$alerts.wsdplugin_alerts('render');
				break;
		}
		this.element.show();
	},

	reload: function()
	{
		wsdplugin_logger.info(this.widgetName + '._reload');
	},

	setTitle: function(title, iconClass)
	{
		this._$pageTitle
			.find('h2').text(title).end()
			.find('.icon32').addClass(iconClass).end()
			.show();
	}

});

})(jQuery);
