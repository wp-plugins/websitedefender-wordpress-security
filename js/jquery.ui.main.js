(function($)
{

/**
 * Handles navigation between different views / widgets
 */
$.widget('ui.wsdplugin_main',
{
	_baseUrl: null,
	_$websiteDetail: null,

	options: {
		targetId: null,
		email: null,
		hash: null
	},

	_create: function()
	{
		wsdplugin_logger.debug(this.widgetName + '.create');

		var url = window.location.href,
			pos = url.indexOf('#');

		this._baseUrl = (pos < 0) ? url : url.substr(0, pos);
		this._$websiteDetail = $('.wsdplugin_website_detail', this.element);
	},

	_init: function()
	{
		wsdplugin_logger.debug(this.widgetName + '.init');

		var self = this;
		var callback = function() {
			wsdplugin_doHTTPRPC('cUser.login', [self.options.email, self.options.hash], self, self.reload, $.wsdplugin_showError);
		};
		setTimeout(callback, 400);
	},

	reload: function()
	{
		this._$websiteDetail.hide();

		wsdplugin_uh.load();
		var panel = wsdplugin_uh.get('p');

		if (panel == null)
		{
			wsdplugin_uh.newLocation({p:'alerts', 'v': '0'});
			return;
		}
		this._$websiteDetail.wsdplugin_websiteDetail('render', panel);
	},

	getTargetId: function()
	{
		return this.options.targetId;
	}
});


})(jQuery);