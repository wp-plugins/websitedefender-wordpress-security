//
// This widget mediates all the possible views of alerts (current, resolved, ignored)
//
(function ($) {
	$.widget('ui.wsdplugin_alerts',
		{
			_currentView: '0',              // the current view (can be one of [ 0=c(urrent), 1=r(esolved), 2=i(gnored) ]
											// Note: when alerts list is displayed this value does not change because direct
											// navigation to this view is not possible

			// cached jQuery references
			_$currentAlerts: null,          // current alert types
			_$resolvedAlerts: null,         // resolved alert types
			_$ignoredAlerts: null,          // ignored alert types
			_$alertsList: null,             // list of individual alerts
			_$viewSelector: null,           // drop-down list with views available (current, resolved and ignored)


			_create: function()
			{
				wsdplugin_logger.info(this.widgetName + '._create');

				this._$currentAlerts    = $('.wsdplugin_page_alert_types_current', this.element);
				this._$resolvedAlerts   = $('.wsdplugin_page_alert_types_resolved', this.element);
				this._$ignoredAlerts    = $('.wsdplugin_page_alert_types_ignored', this.element);
				this._$alertsList       = $('.wsdplugin_page_alert_list', this.element);
				this._$viewSelector     = $('.wsdplugin_alerts_show_view select', this.element);

				// Event handler on view selector
				this._$viewSelector.change(function()
				{
					wsdplugin_uh.newLocation({p: true, v: $(this).val() });
					if ($.browser.mozilla) {
						//FF won't let you click again on the selector unless it loses focus
						$(this).blur();
					}
				});
			},

			render: function(skipStatusUpdate)
			{
				wsdplugin_logger.info(this.widgetName + '._render', skipStatusUpdate);

				this._$viewSelector.parent('.wsdplugin_alerts_show_view').hide();


				// retrieve current page
				var p = wsdplugin_uh.get('p');

				if (p != 'alerts' && p != 'alist') {
					wsdplugin_uh.newLocation({p: 'alerts', v: '0'});
					return;
				}

				if (p == 'alist')
				{
					var type = wsdplugin_uh.get('t');
					if (type == null) {
						wsdplugin_uh.newLocation({p: 'alerts', v: '0'});
						return;
					}

					this._currentView = null;
					this._onViewChanged();
					this.element.show();
					return;
				}


				// Select the desired view or fall back to default view if necessary - valid only if current panel is alerts
				var view = wsdplugin_uh.get('v', true) || '0';
				if ($.inArray(view, ['0', '1', '2']) === -1)
				{
					// If view is not valid then we must use 'uh' otherwise we might end up
					// with a view which does not match the current URL
					view = '0';
					wsdplugin_uh.newLocation({p: 'alerts', 'v': view});
					return;
				}

				this._$viewSelector.val(view).parent('.wsdplugin_alerts_show_view').show();
				this._currentView = view;
				this._onViewChanged();          // this should update the UI accordingly

				this.element.show();
			},

			//--------------------------------------------------------------------------------------------
			// Private Implementation Details

			_onViewChanged: function()
			{
				// Hide all views first
				this._$currentAlerts.hide();
				this._$resolvedAlerts.hide();
				this._$ignoredAlerts.hide();
				this._$alertsList.hide();

				switch (this._currentView)
				{
					// current
					case '0':
						this._currentView = '0';
						this._$currentAlerts.wsdplugin_alertTypesList('render');
						break;

					// resolved
					case '1':
						this._currentView = '1';
						this._$resolvedAlerts.wsdplugin_alertTypesList('render');
						break;

					// ignored
					case '2':
						this._currentView = '2';
						this._$ignoredAlerts.wsdplugin_alertTypesList('render');
						break;

					// individual alerts
					default:
						this._currentView = null;
						this._$alertsList.wsdplugin_alertsList('render');
						break;
				}
			}
		});

})(jQuery);

