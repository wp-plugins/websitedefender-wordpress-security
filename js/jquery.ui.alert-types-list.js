(function($)
{
$.widget('ui.wsdplugin_alertTypesList',
{
	options: {
		uniqueId: null,
		itemsPerPage: 20,
		filter: { status: [] },
		sort: [],
		enableDrillDown: true,
		displayAlertCount: true
	},

	_targetId: null,
	_sort: null,
	_filter: null,
	_offset: 0,
	_count: 0,
	_alerts: {},
	_reloadNeeded: false,


	_create: function()
	{
		wsdplugin_logger.info(this.widgetName + '._create', this.options.uniqueId);


		var self = this;

		// Event handler for select alerts
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_select_actions input.action', this.element)
			.click(function() {
				self._onSelectAlerts($(this).prev('select').val());
			});

		// Event handler for bulk actions
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_bulk_actions input.action', this.element)
			.click(function() {
				self._onBulkAction($(this).prev('select').val());
			});

		// Event handler for severity filter
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_filter_severity input.action', this.element)
			.click(function() {
				self._onFilterBySeverity($(this).prev('select').val());
			});

		// Event handler for sorting
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_sort .wsdplugin_alerts_sort_field', this.element)
			.change(function() {
				if ($(this).val() == '-1')
					$(this).next('.wsdplugin_alerts_sort_dir').hide();
				else
					$(this).next('.wsdplugin_alerts_sort_dir').show();
			});
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_sort input.action', this.element)
			.click(function() {
				self._onSort(
					$(this).parent().find('.wsdplugin_alerts_sort_field').val(),
					$(this).parent().find('.wsdplugin_alerts_sort_dir').val()
				);
			});
	},

	_init: function()
	{
		wsdplugin_logger.info(this.widgetName + '._init', this.options.uniqueId);

		this._targetId  = $('.wsdplugin_content').wsdplugin_main('getTargetId');
		this._sort      = null;
		this._filter    = null;
		this._offset    = 0;
		this._count     = 0;
		this._alerts    = {};
	},

	render: function()
	{
		wsdplugin_logger.info(this.widgetName + '._render', this.options.uniqueId);

		$('.wsdplugin_alert_section_body table tbody', this.element).empty();
		$('.tablenav-pages', this.element).hide();


		// Reset current filters
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_select_actions select', this.element).val('-1');
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_bulk_actions select', this.element).val('-1');
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_filter_severity select', this.element).val('-1');

		this.reload();
		this.element.show();
	},

	reload: function()
	{
		wsdplugin_logger.info(this.widgetName + '._reload', this.options.uniqueId);

		this._count = 0;
		this._alerts = { };
		this._offset = parseInt(wsdplugin_uh.get('o') || '0');
		this._sort = this._getSortingFromURL(true);

		var severity = this._getSeverityFromURL(true);
		if (severity) {
			this._filter = this.options.filter;
			this._filter['severity'] = severity;
		}
		else if (this._filter && 'severity' in this._filter) {
			delete this._filter['severity'];
		}

		var flag = (this._filter && this._filter.status && this._filter.status.length == 1 && this._filter.status[0] == 4) ||
			(this.options.filter.status.length == 1 && this.options.filter.status[0] == 4);

		wsdplugin_doHTTPRPC('cAlerts.getTypesList', [this._targetId, this._filter || this.options.filter, this._sort || this.options.sort, this._offset, this.options.itemsPerPage, true, flag],
			this,
			function(data) {
				this._count = data.count;

				if (data.alerts.length == 0 && this._offset > 0)
				{
					var offset = this._offset - this.options.itemsPerPage;
					if (offset < 0 || offset > 1000) offset = 0;
					wsdplugin_uh.newLocation({p: 'alerts', 'o': offset}, true);
					return;
				}
				this._renderAlerts(data.alerts);

				this._trigger('alertsloaded');
			},
			function(error) {
				wsdplugin_showError(error.message, error.code);
			});
	},


	// Event handlers
	_onSelectAlerts: function(kind)
	{
		wsdplugin_logger.info(this.widgetName + '._onSelectAlerts', kind);

		var self = this;

		// Update state
		switch (kind)
		{
			case 'none':
				for (var type in this._alerts) {
					this._alerts[type].checked = false;
				}
				break;
			case 'all':
				for (var type in this._alerts) {
					this._alerts[type].checked = true;
				}
				break;
			case 'new':
				for (var type in this._alerts) {
					this._alerts[type].checked = (this._alerts[type].status == 0);
				}
				break;
			case 'viewed':
				for (var type in this._alerts) {
					this._alerts[type].checked = (this._alerts[type].status > 0);
				}
				break;
		}

		// Update UI
		$('.wsdplugin_alert_section_body table tr', this.element).each(function()
		{
			var alertType = $(this).data('alert-type');

			if (alertType)
			{
				// Each tr has the alert type associated
				if (self._alerts[alertType].checked)
					$(this).find('td.wsdplugin_checkbox').addClass('wsdplugin_checkbox_active');
				else
					$(this).find('td.wsdplugin_checkbox').removeClass('wsdplugin_checkbox_active');
			}
		});
	},

	_onBulkAction: function(action)
	{
		wsdplugin_logger.debug(this.widgetName + '._onBulkAction', action);

		var alertTypes = [];

		// Retrieve checked alerts
		for (var type in this._alerts)
		{
			if (this._alerts[type].checked)
				alertTypes.push(type);
		}
		if (alertTypes.length > 0)
		{
			wsdplugin_logger.info(this.widgetName + '._onBulkAction', action, alertTypes);
			this._doAction(action, alertTypes);
		}
		else if (action == 'ignore' || action == 'resolve')
		{
			alert('Select an alert to ' + action + '.');
		}
	},

	_onFilterBySeverity: function(severity)
	{
		wsdplugin_logger.info(this.widgetName + '._onFilterBySeverity', severity);

		var severityFilter = null;
		var sortingFilter = wsdplugin_uh.get('s');
		var location = {p: 'alerts', v: true};

		switch (severity)
		{
			case 'critical':
				severityFilter = [3];
				break;
			case 'medium':
				severityFilter = [2];
				break;
			case 'low':
				severityFilter = [1];
				break;
			case 'info':
				severityFilter = [0];
				break;
		}

		if (severityFilter != null) {
			location['f'] = severity;
			this._filter = $.extend(true, {}, this.options.filter, {'severity': severityFilter});
		}
		else if (this._filter && 'severity' in this._filter) {
			delete this._filter['severity'];
		}

		if (sortingFilter)
			location['s'] = sortingFilter;

		this._offset = 0;
		wsdplugin_uh.newLocation(location, true);
	},

	_onSort: function(field, direction)
	{
		wsdplugin_logger.debug(this.widgetName + '._onSort', field, direction);

		var orderFilter = null;
		var severityFilter = wsdplugin_uh.get('f');
		var location = {p: 'alerts', v: true};

		switch (field)
		{
			case 'severity':
				if (direction == 'desc')
					orderFilter = [ ['severity', 'DESC'], ['entrytime', 'DESC'] ];
				else if (direction == 'asc')
					orderFilter = [ ['severity', 'ASC'], ['entrytime', 'DESC'] ];
				break;
			case 'time':
				if (direction == 'desc')
					orderFilter = [ ['entrytime', 'DESC'] ];
				else if (direction == 'asc')
					orderFilter = [ ['entrytime', 'ASC'] ];
				break;
		}

		if (orderFilter) {
			location['s'] = field + '-' + direction;
			this._sort = orderFilter;
		}
		else {
			this._sort = null;
		}

		if (severityFilter)
			location['f'] = severityFilter;

		this._offset = 0;
		wsdplugin_uh.newLocation(location, true);
	},



	// Helper methods

	_doAction: function(action, alertTypes)
	{
		if (alertTypes.length == 0) {
			return;
		}
		switch (action)
		{
			case 'read':
				for (var i in alertTypes) {
					$('#' + this.options.uniqueId + '_tr_' + alertTypes[i] + ' td.wsdplugin_title.wsdplugin_strong')
						.removeClass('wsdplugin_strong');
				}
				break;
			case 'unread':
				for (var i in alertTypes) {
					$('#' + this.options.uniqueId + '_tr_' + alertTypes[i] + ' td.wsdplugin_title')
						.addClass('wsdplugin_strong');
				}
				break;

			case 'resolve':
			case 'unresolve':
			case 'ignore':
			case 'unignore':
				if (action == 'ignore')
				{
					if (!confirm('Are you sure you want to ignore this alert category? Doing so you will no longer receive alerts of this type.'))
						return;
				}
				else if (action == 'resolve')
				{
					if (!confirm('Are you sure you want to resolve this type of alert? Doing so all applicable alerts of this type will be marked as resolved.'))
						return;
				}
				this._reloadNeeded = true;
				break;
		}
		wsdplugin_doHTTPRPC('cAlerts.actionTypes', [this._targetId, alertTypes, action], this, function() { if (this._reloadNeeded) this.reload(); });
	},



	_getLevelName: function(severity)
	{
		if (severity <= 25)
			return 'info';
		if (severity <= 50)
			return 'low';
		if (severity <= 75)
			return 'medium';
		return 'critical';
	},

	_renderAlerts: function(alerts)
	{
		var self = this,
			tbody = $('.wsdplugin_alert_section_body table tbody', this.element).empty();


		if (alerts.length == 0)
		{
			this._applyPagination(0);
			tbody.append('<tr><td colspan="4">No alerts here...</td></tr>');
			return;
		}

		for (var i in alerts)
		{
			var item = alerts[i];

			this._alerts[item.type] = {
				status: item.status,
				checked: false
			};

			var tr = $(document.createElement('tr')).attr('id', this.options.uniqueId + '_tr_' + item.type).data('alert-type', item.type);


			// Checkbox
			$(document.createElement('td')).addClass('wsdplugin_checkbox')
				.append(
					$(document.createElement('a')).attr('href', '#').click(function()
					{
						var parentCell = $(this).closest('td.wsdplugin_checkbox').removeClass('wsdplugin_checkbox_active');
						var alertType = parentCell.closest('tr').data('alert-type');

						self._alerts[alertType].checked = !self._alerts[alertType].checked;

						if (self._alerts[alertType].checked)
							parentCell.addClass('wsdplugin_checkbox_active');

						return false;
					})
				)
			.appendTo(tr);


			// Indicator
			$(document.createElement('td')).addClass('wsdplugin_alert_indicator wsdplugin_alert_indicator_' + this._getLevelName(item.severity))
				.appendTo(tr);


			// Title
			if (self.options.enableDrillDown)
			{
				$(document.createElement('td'))
					.append(
						$(document.createElement('a'))
						.attr('href', '#')
						.html(item.title + (this.options.displayAlertCount ? ' (' + item.count + ' alerts)' : ''))
						.click(function()
						{
							var parentCell = $(this).closest('td.wsdplugin_title');
							var alertType = parentCell.closest('tr').data('alert-type');

							if (self._alerts[alertType].status == 0)
							{
								self._alerts[alertType].status = 1;
								parentCell.removeClass('wsdplugin_strong');
							}
							wsdplugin_uh.newLocation({p: 'alist', t: alertType, v: wsdplugin_uh.get('v') || 0}, true);

							return false;
						})
					)
					.addClass('wsdplugin_title' + (item.status == 0 ? ' wsdplugin_strong' : ''))
				.appendTo(tr);
			}
			else
			{
				$(document.createElement('td'))
					.html(item.title + (this.options.displayAlertCount ? ' (' + item.count + ' alerts)' : ''))
					.addClass('wsdplugin_title')
				.appendTo(tr);
			}

			// Entry time
			$(document.createElement('td')).css({'width': '140px', 'max-width': '140px', 'text-align': 'right'}).html(item.time).appendTo(tr);

			tbody.append(tr);
		}

		this._applyPagination(alerts.length);
	},

	_applyPagination: function(countOfRetrievedAlerts)
	{
		var self = this;
		var paginationHost = $('.tablenav-pages', this.element);
		var paginationLinks = paginationHost.find('.pagination-links');
		var showPagination = false;

		var currentPage = 1 + Math.round(this._offset / this.options.itemsPerPage);
		var pageCount = Math.ceil(this._count / this.options.itemsPerPage);

		paginationLinks.find('a').addClass('disabled').unbind('click').click(function() { return false; });
		paginationHost.find('.displaying-num').html(Math.min(this.options.itemsPerPage, countOfRetrievedAlerts) + ' items');

		paginationLinks.find('.paging-input').text(currentPage + ' of ');
		paginationLinks.find('.total-pages').text('' + pageCount);

		if (currentPage > 1) {
			paginationLinks.find('a[href=#first]').removeClass('disabled')
				.unbind('click')
				.bind('click', function() {
					wsdplugin_uh.newLocation({p: true, v: true, s: true, f: true, o: 0}, true);
					return false;
				});
			paginationHost.find('a[href=#back]').removeClass('disabled')
				.unbind('click')
				.bind('click', function() {
					wsdplugin_uh.newLocation({p: true, v: true, s: true, f: true, o: self._offset - self.options.itemsPerPage}, true);
					return false;
				});
			showPagination = true;
		}
		if (currentPage < pageCount) {
			paginationHost.find('a[href=#next]').removeClass('disabled')
				.unbind('click')
				.bind('click', function() {
					wsdplugin_uh.newLocation({p: true, v: true, s: true, f: true, o: self._offset + self.options.itemsPerPage}, true);
					return false;
				});
			paginationHost.find('a[href=#last]').removeClass('disabled')
				.unbind('click')
				.bind('click', function() {
					wsdplugin_uh.newLocation({p: true, v: true, s: true, f: true, o: (pageCount - 1) * self.options.itemsPerPage}, true);
					return false;
				});
			showPagination = true;
		}
		if (showPagination)
			$('.tablenav-pages', this.element).show();
	},

	_getSortingFromURL: function(updateSortSelectors)
	{
		var sortExpr = wsdplugin_uh.get('s');
		var sort = null;
		var sortInfo = ['-1', 'asc'];

		// Validate sortExpr
		switch (sortExpr) {
			case 'severity-desc':
				sort = [['severity', 'DESC'], ['entrytime', 'DESC']];
				sortInfo = ['severity', 'desc'];
				break;
			case 'severity-asc':
				sort = [['severity', 'ASC'], ['entrytime', 'DESC']];
				sortInfo = ['severity', 'asc'];
				break;
			case 'time-desc':
				sort = [['entrytime', 'DESC']];
				sortInfo = ['time', 'desc'];
				break;
			case 'time-asc':
				sort = [['entrytime', 'ASC']];
				sortInfo = ['time', 'asc'];
				break;
		}
		if (updateSortSelectors) {
			var sortBySelector = $('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_sort .wsdplugin_alerts_sort_field', this.element);
			var sortDirSelector = $('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_sort .wsdplugin_alerts_sort_dir', this.element);

			sortBySelector.val(sortInfo[0]);
			sortDirSelector.val(sortInfo[1]);

			(sortInfo[0] == '-1') ? sortDirSelector.hide() : sortDirSelector.show();
		}
		return sort;
	},

	_getSeverityFromURL: function(updateFilterSelector)
	{
		var filterInfo = '-1';
		var severityCode = wsdplugin_uh.get('f');
		var severity = null;

		switch (severityCode) {
			case 'critical':
				severity = [3];
				filterInfo = 'critical';
				break;
			case 'medium':
				severity = [2];
				filterInfo = 'medium';
				break;
			case 'low':
				severity = [1];
				filterInfo = 'low';
				break;
			case 'info':
				severity = [0];
				filterInfo = 'info';
				break;
		}
		if (updateFilterSelector) {
			$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_filter_severity select').val(filterInfo);
		}
		return severity;
	}
});

})(jQuery);
