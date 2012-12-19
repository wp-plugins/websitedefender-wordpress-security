(function($)
{
$.widget('ui.wsdplugin_alertsList',
{
	options:
	{
		uniqueId: 'wsdplugin_alerts_list',
		itemsPerPage: 5,
		filter: {status: []},
		sort: []
	},

	_targetId: null,
	_alerts: { },
	_offset: 0,
	_count: 0,
	_sort: null,
	_filter: null,
	_alertType: 0,
	_reloadNeeded: false,


	_create: function()
	{
		wsdplugin_logger.info(this.widgetName + '._create');

		var self = this;

		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_back', this.element)
			.click(function() {
				//window.history.back();
				wsdplugin_uh.newLocation({p: 'alerts', v: true});
				return false;
			});

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

		// Event handlers for expand / collapse
		$('.wsdplugin_alerts_exapand_collapse_actions a', this.element)
			.click(function()
			{
				switch ($(this).attr('href'))
				{
					case '#expand':
						$('.wsdplugin_alert_section_body table tbody tr[id]', self.element).each(function() {
							$(this).find('td.wsdplugin_expander_open a').click();
						});
						break;
					case '#collapse':
						$('.wsdplugin_alert_section_body table tbody tr[id]', self.element).each(function() {
							$(this).find('td.wsdplugin_expander_close a').click();
						});
						break;
				}
				return false;
			});
	},

	_init: function()
	{
		wsdplugin_logger.info(this.widgetName + '._init');

		this._targetId = $('.wsdplugin_content').wsdplugin_main('getTargetId');
	},

	render: function()
	{
		wsdplugin_logger.info(this.widgetName + '._render');

		this.reload();
		this.element.show();
	},

	reload: function()
	{
		wsdplugin_logger.info(this.widgetName + '._reload');

		this._count = 0;
		this._alerts = { };
		this._offset = parseInt(wsdplugin_uh.get('o') || '0');
		this._alertType = parseInt(wsdplugin_uh.get('t') || '0');
		this._sort = this._getSortingFromURL(true);

		if (this._filter == null) {
			this._filter = this.options.filter;
		}
		if (!('status' in this._filter))
			this._filter['status'] = [];

		switch (wsdplugin_uh.get('v'))
		{
			case '0':
				this._filter.status = [0,1];
				break;
			case '1':
				this._filter.status = [2,3];
				break;
			case '2':
				this._filter.status = [4];
				break;
		}

		// Reset current filters
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_select_actions select', this.element).val('-1');
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_bulk_actions select', this.element).val('-1');
		$('.wsdplugin_page_alerts_action_bar .wsdplugin_alerts_filter_severity select', this.element).val('-1');

		$('.wsdplugin_alert_section_title', this.element).hide().html('');
		$('.wsdplugin_alert_section_description', this.element).hide().find('p').html('');
		$('.wsdplugin_alert_section_solution', this.element).hide().find('p').html('');
		$('.wsdplugin_alert_section_reference', this.element).hide().find('p').html('');
		$('.wsdplugin_alert_section_body table tbody', this.element).empty();
		$('.tablenav-pages', this.element).parent().hide();
		$('.wsdplugin_alerts_exapand_collapse_actions', this.element).hide();

		wsdplugin_doHTTPRPC('cAlerts.getTypeInfo', [this._targetId, this._alertType], this,
			function(result) {
				this._onTypeInfoLoaded(result);
				this._loadAlerts();
			},
			function(error) {
				$.wsdplugin_showError(error);
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
				for (var id in this._alerts) {
					this._alerts[id].checked = false;
				}
				break;
			case 'all':
				for (var id in this._alerts) {
					this._alerts[id].checked = true;
				}
				break;
			case 'stared':
				for (var id in this._alerts) {
					this._alerts[id].checked = (this._alerts[id].star == true);
				}
				break;
			case 'unstared':
				for (var id in this._alerts) {
					this._alerts[id].checked = (this._alerts[id].star == false);
				}
				break;
		}

		// Update UI
		$('.wsdplugin_alert_section_body table tr', this.element).each(function()
		{
			var alertId = $(this).data('alert-id');

			if (typeof(alertId) !== 'undefined')
			{
				// Each tr has the alert type associated
				if (self._alerts[alertId].checked)
					$(this).find('td.wsdplugin_checkbox').addClass('wsdplugin_checkbox_active');
				else
					$(this).find('td.wsdplugin_checkbox').removeClass('wsdplugin_checkbox_active');
			}
		});
	},

	_onBulkAction: function(action)
	{
		wsdplugin_logger.info(this.widgetName + '._onBulkAction', action);

		var alertIds = [];
		var self = this;

		switch (action)
		{
			case 'resolve':
				for (var alertId in this._alerts)
				{
					var item = this._alerts[alertId];

					if (item.checked && item.status < 2)
						alertIds.push(alertId);
				}
				break;
			case 'ignore':
				for (var alertId in this._alerts)
				{
					var item = this._alerts[alertId];

					if (item.checked && item.status < 4)
						alertIds.push(alertId);
				}
				break;
			case 'unresolve':
				for (var alertId in this._alerts)
				{
					var item = this._alerts[alertId];

					if (item.checked && (item.status == 2 || item.status == 3))
						alertIds.push(alertId);
				}
				break;
			case 'unignore':
				for (var alertId in this._alerts)
				{
					var item = this._alerts[alertId];

					if (item.checked && item.status == 4)
						alertIds.push(alertId);
				}
				break;
			case 'star':
				for (var alertId in this._alerts)
				{
					var item = this._alerts[alertId];

					if (item.checked)
						alertIds.push(alertId);
				}
				break;
			case 'unstar':
				for (var alertId in this._alerts)
				{
					var item = this._alerts[alertId];

					if (item.checked)
						alertIds.push(alertId);
				}
				break;
		}

		if (alertIds.length > 0)
		{
			wsdplugin_logger.debug(this.widgetName + '._onBulkAction', action, alertIds);
			this._doAction(action, alertIds);
		}
		else if (action == 'ignore' || action == 'resolve')
		{
			alert('Select an alert to ' + action + '.');
		}
	},

	_onSort: function(field, direction)
	{
		wsdplugin_logger.debug(this.widgetName + '._onSort', field, direction);

		var orderFilter = null;
		var location = {p: 'alist', t: true, v: true};

		switch (field) {
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

		this._offset = 0;
		wsdplugin_uh.newLocation(location, true);
	},


	_doAction: function(action, alertIds)
	{
		if (alertIds.length == 0) {
			return;
		}

		switch (action)
		{
			case 'star':
				for (var i in alertIds)
				{
					$('#' + this.options.uniqueId + '_tr_' + alertIds[i] + ' td.wsdplugin_star')
						.addClass('wsdplugin_star_active');
				}
				break;

			case 'unstar':
				for (var i in alertIds)
				{
					$('#' + this.options.uniqueId + '_tr_' + alertIds[i] + ' td.wsdplugin_star.wsdplugin_star_active')
						.removeClass('wsdplugin_star_active');
				}
				break;

			case 'resolve':
			case 'ignore':
			case 'unresolve':
			case 'unignore':
				this._reloadNeeded = true;
				break;
		}
		wsdplugin_doHTTPRPC('cAlerts.actionAlerts', [this._targetId, alertIds, action], this, function() { if (this._reloadNeeded) this.reload(); });
	},

	_applyDecorations: function()
	{
		for (var alertId in this._alerts) {
			this._applyDecoration(alertId);
		}
	},
	_applyDecoration: function(alertId)
	{
		var row = $('#' + this.options.uniqueId + '_tr_' + id, $('.wsdplugin_alert_section_body table tbody'));

		// Checkbox
		if (this._alerts[alertId].checked)
			$('td.wsdplugin_checkbox', row).addClass('wsdplugin_checkbox_active');
		else
			$('td.wsdplugin_checkbox', row).removeClass('wsdplugin_checkbox_active');
		// Star
		if (this._alerts[alertId].star)
			$('td.wsdplugin_star', row).addClass('wsdplugin_star_active');
		else
			$('td.wsdplugin_star', row).removeClass('wsdplugin_star_active');
		// Read
		if (this._alerts[alertId].status == 0)
			$('td.wsdplugin_title', row).addClass('wsdplugin_strong');
		else
			$('td.wsdplugin_title', row).removeClass('wsdplugin_strong');
	},

	// Helper methods

	_renderAlerts: function(alerts)
	{
		var anyExpander = false,
			anyRepeated = false,
			slidingRow = null,
			self = this,
			tbody = $('.wsdplugin_alert_section_body table tbody', this.element).empty();

		for (var i in alerts) {
			if (alerts[i].detail)
				anyExpander = true;
			if (alerts[i].firsttime != alerts[i].entrytime)
				anyRepeated = true;
		}

		if (!anyExpander) {
			$('.wsdplugin_alerts_exapand_collapse_actions', this.element).hide();
		}
		else {
			$('.wsdplugin_alerts_exapand_collapse_actions', this.element).show();
		}

		for (var i in alerts)
		{
			var item = alerts[i];

			this._alerts[item.id] = {
				status: item.status,
				star: !!(item.starred == 't')
			};

			var tr = $(document.createElement('tr')).data('alert-id', item.id).attr('id', this.options.uniqueId + '_tr_' + item.id);


			// Expander
			if (item.detail)
			{
				slidingRow = $(document.createElement('tr'))
					.css('display', 'none')
					.append(
						$(document.createElement('td'))
							.css({'padding-top': '20px', 'padding-bottom': '20px', 'background-color': '#fff'})
							.html(item.detail)
							.attr('colspan', 6 - (anyExpander ? 0 : 1) - (anyRepeated ? 0 : 1))
					);

				// Download Link
				var $downloadLink = $('a[href="#download-restore-file"]', slidingRow);
				if ($downloadLink.length > 0) {
					$downloadLink.attr('href', 'https://dashboard.websitedefender.com/download-restore-file.php?tid='
						+ this._targetId + '&aid=' + item.id.substr(item.id.indexOf('_') + 1));
				}

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

			// Checkbox
			$(document.createElement('td')).addClass('wsdplugin_checkbox')
				.append(
					$(document.createElement('a')).attr('href', '#').click(function()
					{
						var parentCell = $(this).closest('td.wsdplugin_checkbox').removeClass('wsdplugin_checkbox_active');
						var alertId = parentCell.closest('tr').data('alert-id');

						self._alerts[alertId].checked = !self._alerts[alertId].checked;

						if (self._alerts[alertId].checked)
							parentCell.addClass('wsdplugin_checkbox_active');

						return false;
					})
			)
			.appendTo(tr);

			// Star
			$(document.createElement('td')).addClass('wsdplugin_star' + (this._alerts[item.id].star ? ' wsdplugin_star_active' : ''))
				.append(
					$(document.createElement('a')).attr('href', '#').click(function()
					{
						var parentCell = $(this).closest('td.wsdplugin_star');
						var alertId = parentCell.closest('tr').data('alert-id');
						var action = self._alerts[alertId].star ? 'unstar' : 'star';

						self._alerts[alertId].star = !self._alerts[alertId].star;

						if (self._alerts[alertId].star)
							parentCell.addClass('wsdplugin_star_active');
						else
							parentCell.removeClass('wsdplugin_star_active');

						wsdplugin_doHTTPRPC('cAlerts.actionAlerts', [self._targetId, [alertId], action], this,
							null,
							function(error) {
								$.wsdplugin_showError(error);
							});

						return false;
					})
				)
			.appendTo(tr);


			// Title
			$(document.createElement('td'))
				.html(item.title)
				.attr('title', item.title)
				.addClass('wsdplugin_title' + (item.status == 0 ? ' wsdplugin_strong' : ''))
			.appendTo(tr);


			// Entry time
			$(document.createElement('td')).css({'width': '100px', 'text-align': 'right', 'vertical-align': 'middle'}).html(item.entrytime).appendTo(tr);

			if (item.firsttime != item.entrytime)
			{
				$(document.createElement('td')).addClass('wsdplugin_alert_repeated').attr('title', 'This alert was first issued on ' + item.firsttime + '.')
					.appendTo(tr);
			}
			else if (anyRepeated)
			{
				$(document.createElement('td')).appendTo(tr);
			}

			tr.appendTo(tbody);
			if (slidingRow) slidingRow.appendTo(tbody);

			tr = slidingRow = null;
		}

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

		$('.tablenav-pages', this.element).parent().hide();

		if (currentPage > 1) {
			paginationLinks.find('a[href="#first"]').removeClass('disabled')
				.unbind('click')
				.bind('click', function() {
					wsdplugin_uh.newLocation({p: true, v: true, s: true, t: true, f: true, o: 0}, true);
					return false;
				});
			paginationHost.find('a[href="#back"]').removeClass('disabled')
				.unbind('click')
				.bind('click', function() {
					wsdplugin_uh.newLocation({p: true, v: true, s: true, t: true, f: true, o: self._offset - self.options.itemsPerPage}, true);
					return false;
				});
			showPagination = true;
		}
		if (currentPage < pageCount) {
			paginationHost.find('a[href="#next"]').removeClass('disabled')
				.unbind('click')
				.bind('click', function() {
					wsdplugin_uh.newLocation({p: true, v: true, s: true, t: true, f: true, o: self._offset + self.options.itemsPerPage}, true);
					return false;
				});
			paginationHost.find('a[href="#last"]').removeClass('disabled')
				.unbind('click')
				.bind('click', function() {
					wsdplugin_uh.newLocation({p: true, v: true, s: true, t: true, f: true, o: (pageCount - 1) * self.options.itemsPerPage}, true);
					return false;
				});
			showPagination = true;
		}
		if (showPagination)
			$('.tablenav-pages', this.element).parent().show();
	},

	_getSortingFromURL: function(updateSortSelectors)
	{
		var sortExpr = wsdplugin_uh.get('s');
		var sort = null;
		var sortInfo = ['-1', 'asc'];

		// Validate sortExpr
		switch (sortExpr) {
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

	_onTypeInfoLoaded: function(data)
	{
		// Title
		var $title = $('.wsdplugin_alert_section_title', this.element);
		$title.html(data.title).removeClass('wsdplugin_alert_section_title_level_critical wsdplugin_alert_section_title_level_medium wsdplugin_alert_section_title_level_low wsdplugin_alert_section_title_level_info');

		if (data.severity <= 25) {
			$title.addClass('wsdplugin_alert_section_title_level_info').attr('title', 'Informational Alert');
		}
		else if (data.severity <= 50) {
			$title.addClass('wsdplugin_alert_section_title_level_low').attr('title', 'Low Alert');
		}
		else if (data.severity <= 75) {
			$title.addClass('wsdplugin_alert_section_title_level_medium').attr('title', 'Medium Alert');
		}
		else {
			$title.addClass('wsdplugin_alert_section_title_level_critical').attr('title', 'Critical Alert');
		}
		$title.show();


		// Description
		$('.wsdplugin_alert_section_description', this.element).find('p').html(data.description).end().show();

		// Solution
		if(data.solution) {
			$('.wsdplugin_alert_section_solution', this.element).find('p').html(data.solution).end().show();
		}
		else {
			$('.wsdplugin_alert_section_solution', this.element).find('p').html('').end().hide();
		}

		// References
		var $links = $('.wsdplugin_alert_section_reference', this.element).find('ul').empty();

		if (data.reference.length > 0) {
			for (var i = 0, n = data.reference.length; i < n; i++) {
				$links.append(
					$('<li></li>').append($('<a target="_blank"></a>').attr('href', data.reference[i][1]).html(data.reference[i][0]))
				);
			}
			$links.end().show();
		}
	},

	_loadAlerts: function()
	{
		wsdplugin_doHTTPRPC('cAlerts.getAlertsList', [this._targetId, this._alertType, this._filter || this.options.filter, this._sort || this.options.sort, this._offset, this.options.itemsPerPage, false], this,
			function(result) {
				this._onAlertsLoaded(result);
				this._trigger('alertsloaded');
			},
		function(error) {
			$.wsdplugin_showError(error);
		});
	},

	_onAlertsLoaded: function(data)
	{
		this._count = data.count;
		this._alerts = {};

		if (this._count == 0) {
			wsdplugin_uh.newLocation({p: 'alerts', v: true}, true);
			return;
		}

		$(".wsdplugin_page_alerts_action_bar .wsdplugin_alerts_bulk_actions select", this.element).find('option[value!=-1]').hide();


		var currentView = wsdplugin_uh.get('v');


		if (currentView == '0') {
			$(".wsdplugin_page_alerts_action_bar .wsdplugin_alerts_bulk_actions select", this.element).find('option[value=ignore],option[value=resolve],option[value=star],option[value=unstar]').show();
		}
		else if (currentView == '1') {
			$(".wsdplugin_page_alerts_action_bar .wsdplugin_alerts_bulk_actions select", this.element).find('option[value=ignore],option[value=unresolve],option[value=star],option[value=unstar]').show();
		}
		else if (currentView == '2') {
			$(".wsdplugin_page_alerts_action_bar .wsdplugin_alerts_bulk_actions select", this.element).find('option[value=unignore]').show();
		}

		if (data.alerts.length == 0 && this._offset > 0)
		{
			var offset = this._offset - this.options.itemsPerPage;
			if (offset < 0 || offset > 1000) offset = 0;
			wsdplugin_uh.newLocation({p: 'alist', v: currentView,  o: offset, t: this._alertType}, true);
			return;
		}

		this._renderAlerts(data.alerts);
		this._applyPagination(data.alerts.length);
	}
});

})(jQuery);
