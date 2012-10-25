<?php
if (!defined('wsdplugin_WSD_PLUGIN_SESSION')) exit;


// Include CSS
wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');
wp_enqueue_style('wsdplugin_css_status',    wsdplugin_Utils::cssUrl('status.css'),  array(), '1.0');
wp_enqueue_style('wsdplugin_css_alerts',    wsdplugin_Utils::cssUrl('alerts.css'),  array(), '1.0');

// Include jQuery UI
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
wp_enqueue_script('jquery-ui-mouse');

// Include Custom JavaScript
wp_enqueue_script('wsdplugin_js_hashchange', wsdplugin_Utils::jsUrl('jquery.hashchange.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_uh', wsdplugin_Utils::jsUrl('uh.js'), array('jquery', 'jquery-ui-widget'), '1.0');
wp_enqueue_script('wsdplugin_js_logger', wsdplugin_Utils::jsUrl('logger.js'), array('jquery-ui-widget'), '1.0');
wp_enqueue_script('wsdplugin_js_request', wsdplugin_Utils::jsUrl('request.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_common', wsdplugin_Utils::jsUrl('common.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_ui_main', wsdplugin_Utils::jsUrl('jquery.ui.main.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_ui_status', wsdplugin_Utils::jsUrl('jquery.ui.status.js'), array('jquery-ui-widget'), '1.0');
wp_enqueue_script('wsdplugin_js_ui_website_detail', wsdplugin_Utils::jsUrl('jquery.ui.website-detail.js'), array(), '1.0');
wp_enqueue_script('wsdplugin_js_ui_alert_list', wsdplugin_Utils::jsUrl('jquery.ui-alerts-list.js'), array(), '1.0');
wp_enqueue_script('wsdplugin_js_ui_types', wsdplugin_Utils::jsUrl('jquery.ui.alert-types-list.js'), array(), '1.0');
wp_enqueue_script('wsdplugin_js_ui_alerts', wsdplugin_Utils::jsUrl('jquery.ui.alerts.js'), array(), '1.0');


$targetId   = get_option('WSD-ID');
$user       = get_option('WSD-USER');
$hash       = get_option('WSD-HASH');

?>

<div class="wrap wsdplugin_content">

<!-- Overlay shown whenever a request is in progress -->
<div class="wsdplugin_request_overlay" style="display: none; position: absolute; width: 100%; height: 100%; z-index: 10000">

    <h3 style="display: block; float: right; margin-top: 30px; margin-right: 20px; color: #000; opacity: 1; z-index: 200000;">Loading content...</h3>
    <div style="background-color: #000; opacity: .0; width: 100%; height: 100%;"></div>

</div>


<div class="wsdplugin_website_detail">

<div class="wsdplugin_page_title">
    <div class="icon32 wsdplugin_status_page_ico"><br></div>
    <h2 style="padding-right: 0;">Welcome to your WebsiteDefender Alert Center <a class="add-new-h2" href="#refresh">Refresh</a>
	    <span style="display:block; float:right; font-weight: normal; color: #CC0000; font-size: .5em;">
		    <span id="wsdplugin-current-user" style="text-decoration: none; color: #CC0000; cursor: default; float: right;overflow: hidden; padding-left: 5px; padding-right: 5px; border-radius: 3px;;">
	            <span id="wsdplugin-current-user-name" style=" text-align: right; display: block; clear: both;"><?php echo htmlentities(get_option('WSD-USER')); ?></span>
			    <div id="wsdplugin-current-user-menu">
	                <ul>
	                    <li><a href="admin.php?page=wsdplugin_dashboard&reset">Reset plugin settings</a></li>
	                </ul>
	            </div>
            </span>

	    </span>
    </h2>
</div>
<style type="text/css">
    #wsdplugin-current-user:hover {background-color: #F9F9F9;box-shadow: 1px 1px 4px #888888;}
    #wsdplugin-current-user { overflow: hidden;}
    #wsdplugin-current-user-menu { margin-top: 10px; display: none; float: left; clear: both; }
    #wsdplugin-current-user:hover #wsdplugin-current-user-name { border-bottom: solid 1px #dadada; }
    #wsdplugin-current-user:hover #wsdplugin-current-user-menu {display: block;z-index: 800000;padding: 0 10px 10px 0px !important;margin-right: 7px !important;}
    #wsdplugin-current-user-menu ul {margin: 0 0 !important; padding: 0 0 !important; display: block;}
</style>

<?php if (get_option('WSD-SCANTYPE') == 'WSDFREE') {?>

<div style="margin-bottom: 20px; overflow: hidden">

	<div style="float: left;margin-top: 4px">
		<span class="wsdplugin_warning_trial_expired"></span>
	</div>
    <div style="float: left; display: block; margin-left: 10px">
        Your trial has expired and your website is no longer being scanned daily for malware and security issues <span class="wsdplugin_sad_face">&nbsp;</span>
		<br>
		Continue securing your WordPress for only $99.95 a year. Log into your WebsiteDefender dashboard and click on the Buy Now link.
    </div>
</div>

	<?php }?>

<div class="error wsdplugin_error_box" style="display: none">
    <p>
        <strong>Sample error message.</strong>
        <a class="dismiss" href="#">Dismiss</a>
    </p>
</div>

<!-- Status Page -->
<div class="wsdplugin_page_status">

    <div class="wsdplugin_status_box wsdplugin_status_malware">
		<span>Malware:</span>
	    <span>-</span>
	</div>

	<div class="wsdplugin_status_box_spacer" style="">&nbsp;</div>

    <div class="wsdplugin_status_box wsdplugin_status_dns">
        <span>DNS:</span>
		<span class="wsdplugin_status_bad">-</span>
	</div>

    <div class="wsdplugin_status_box_spacer" style="">&nbsp;</div>

    <div class="wsdplugin_status_box wsdplugin_status_scan">
        <span>Last Scan:</span>
        <span>-</span>
    </div>

    <div class="wsdplugin_status_box_spacer" style="">&nbsp;</div>

    <div class="wsdplugin_status_box wsdplugin_status_time">
        <span>Average Response Time:</span>
        <span>-</span>
    </div>

    <div class="wsdplugin_status_box_spacer" style="">&nbsp;</div>

    <div class="wsdplugin_status_box wsdplugin_status_target">
        <span>Target ID:</span>
        <span>-</span>
    </div>


</div>
<!-- Status Page -->


<!-- Alerts Page -->
<div class="wsdplugin_page_alerts" style="display: none">


<div class="alignright actions wsdplugin_alerts_show_view" style="float: right; margin-top: 9px;">
	<span>View</span>
    <select>
        <option value="0" selected="selected">Current</option>
        <option value="1">Resolved</option>
        <option value="2">Ignored</option>
    </select>
    <span>alerts</span>
</div>



<!-- Current Alerts -->
<div class="wsdplugin_page_alert_types_current" style="display: none">

    <!-- Action Bar -->
    <div class="wsdplugin_page_alerts_action_bar" style="float: left;">
        <div class="tablenav">
            <div class="alignleft actions wsdplugin_alerts_select_actions">
                <select>
                    <option selected="selected" value="-1">Select Alerts</option>
                    <option value="none">None</option>
                    <option value="all">All</option>
                    <option value="new">New</option>
                    <option value="viewed">Viewed</option>
                </select>
                <input type="button" value="Apply" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_bulk_actions">
                <select>
                    <option selected="selected" value="-1">Bulk Actions</option>
                    <option value="resolve">Resolve</option>
                    <option value="unread">Mark Unread</option>
                    <option value="read">Mark Read</option>
                    <option value="ignore">Ignore Category</option>
                </select>
                <input type="button" value="Apply" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_filter_severity">
                <select>
                    <option selected="selected" value="-1">All Severity Levels</option>
                    <option value="critical">Critical</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                    <option value="info">Informational</option>
                </select>
                <input type="button" value="Filter" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_sort">
                <select class="wsdplugin_alerts_sort_field">
                    <option value="-1">Default Sorting</option>
                    <option value="severity">Severity</option>
                    <option value="time">Time</option>
                </select>
                <select class="wsdplugin_alerts_sort_dir" style="display: none">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </select>
                <input type="button" value="Sort" class="button-secondary action">
            </div>
        </div>
    </div>

    <!-- Title -->
    <div class="wsdplugin_alert_section_title wsdplugin_alert_section_title_category">Current Alerts</div>

    <!-- Body -->
    <div class="wsdplugin_alert_section_body">
        <table class="widefat" cellspacing="0" cellpadding="0">
            <tbody></tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="tablenav">
        <div class="tablenav-pages">
            <span class="displaying-num"></span>
			<span class="pagination-links">
				<a href="#first" title="Go to the first page" class="first-page">«</a>
				<a href="#back" title="Go to the previous page" class="prev-page">‹</a>
				<span class="paging-input"></span><span class="total-pages"></span>
				<a href="#next" title="Go to the next page" class="next-page">›</a>
				<a href="#last" title="Go to the last page" class="last-page">»</a>
			</span>
        </div>
    </div>

</div>
<!-- Current Alerts -->

<!-- Ignored Alerts -->
<div class="wsdplugin_page_alert_types_ignored" style="display: none">

    <!-- Action Bar -->
    <div class="wsdplugin_page_alerts_action_bar" style="float: left;">
        <div class="tablenav">
            <div class="alignleft actions wsdplugin_alerts_select_actions">
                <select>
                    <option selected="selected" value="-1">Select Alerts</option>
                    <option value="none">None</option>
                    <option value="all">All</option>
                </select>
                <input type="button" value="Apply" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_bulk_actions">
                <select>
                    <option selected="selected" value="-1">Bulk Actions</option>
                    <option value="unignore">Unignore</option>
                </select>
                <input type="button" value="Apply" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_filter_severity">
                <select>
                    <option selected="selected" value="-1">All Severity Levels</option>
                    <option value="critical">Critical</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                    <option value="info">Informational</option>
                </select>
                <input type="button" value="Filter" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_sort">
                <select class="wsdplugin_alerts_sort_field">
                    <option value="-1">Default Sorting</option>
                    <option value="severity">Severity</option>
                </select>
                <select class="wsdplugin_alerts_sort_dir" style="display: none">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </select>
                <input type="button" value="Sort" class="button-secondary action">
            </div>

        </div>
    </div>

    <!-- Title -->
    <div class="wsdplugin_alert_section_title wsdplugin_alert_section_title_category">Ignored Alerts</div>

    <!-- Body -->
    <div class="wsdplugin_alert_section_body">
        <table class="widefat" cellspacing="0" cellpadding="0">
            <tbody></tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="tablenav">
        <div class="tablenav-pages">
            <span class="displaying-num"></span>
			<span class="pagination-links">
				<a href="#first" title="Go to the first page" class="first-page">«</a>
				<a href="#back" title="Go to the previous page" class="prev-page">‹</a>
				<span class="paging-input"></span><span class="total-pages"></span>
				<a href="#next" title="Go to the next page" class="next-page">›</a>
				<a href="#last" title="Go to the last page" class="last-page">»</a>
			</span>
        </div>
    </div>
</div>
<!-- Ignored Alerts -->

<!-- Resolved Alerts -->
<div class="wsdplugin_page_alert_types_resolved" style="display: none">

    <!-- Action Bar -->
    <div class="wsdplugin_page_alerts_action_bar" style="float: left;">
        <div class="tablenav">
            <div class="alignleft actions wsdplugin_alerts_select_actions">
                <select>
                    <option selected="selected" value="-1">Select Alerts</option>
                    <option value="none">None</option>
                    <option value="all">All</option>
                </select>
                <input type="button" value="Apply" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_bulk_actions">
                <select>
                    <option selected="selected" value="-1">Bulk Actions</option>
                    <option value="unresolve">Unresolve</option>
                    <option value="ignore">Ignore</option>
                </select>
                <input type="button" value="Apply" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_filter_severity">
                <select>
                    <option selected="selected" value="-1">All Severity Levels</option>
                    <option value="critical">Critical</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                    <option value="info">Informational</option>
                </select>
                <input type="button" value="Filter" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_sort">
                <select class="wsdplugin_alerts_sort_field">
                    <option value="-1">Default Sorting</option>
                    <option value="severity">Severity</option>
                    <option value="time">Time</option>
                </select>
                <select class="wsdplugin_alerts_sort_dir" style="display: none">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </select>
                <input type="button" value="Sort" class="button-secondary action">
            </div>
        </div>
    </div>

    <!-- Title -->
    <div class="wsdplugin_alert_section_title wsdplugin_alert_section_title_category">Resolved Alerts</div>

    <!-- Body -->
    <div class="wsdplugin_alert_section_body">
        <table class="widefat" cellspacing="0" cellpadding="0">
            <tbody></tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="tablenav">
        <div class="tablenav-pages">
            <span class="displaying-num"></span>
			<span class="pagination-links">
				<a href="#first" title="Go to the first page" class="first-page">«</a>
				<a href="#back" title="Go to the previous page" class="prev-page">‹</a>
				<span class="paging-input">1 of </span><span class="total-pages">4</span>
				<a href="#next" title="Go to the next page" class="next-page">›</a>
				<a href="#last" title="Go to the last page" class="last-page">»</a>
			</span>
        </div>
    </div>
</div>
<!-- Resolved Alerts -->

<!-- Details for alerts -->
<div class="wsdplugin_page_alert_list clear" style="display: none">

    <!-- Action Bar -->
    <div class="wsdplugin_page_alerts_action_bar">
        <div class="tablenav">
            <div class="alignleft actions wsdplugin_alerts_select_actions">
                <select>
                    <option selected="selected" value="-1">Select Alerts</option>
                    <option value="none">None</option>
                    <option value="all">All</option>
                    <option value="stared">Stared</option>
                    <option value="unstared">Unstared</option>
                </select>
                <input type="button" value="Apply" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_bulk_actions">
                <select>
                    <option selected="selected" value="-1">Bulk Actions</option>
                    <option value="resolve">Resolve</option>
                    <option value="unresolve">Unresolve</option>
                    <option value="star">Star</option>
                    <option value="unstar">Unstar</option>
                    <option value="ignore">Ignore</option>
                    <option value="unignore">Unignore</option>
                </select>
                <input type="button" value="Apply" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_sort">
                <select class="wsdplugin_alerts_sort_field">
                    <option value="-1">Default Sorting</option>
                    <option value="time">Time</option>
                </select>
                <select class="wsdplugin_alerts_sort_dir">
                    <option value="asc">Ascending</option>
                    <option value="desc" selected="selected">Descending</option>
                </select>
                <input type="button" value="Sort" class="button-secondary action">
            </div>

            <div class="alignleft actions wsdplugin_alerts_back clear" style="margin-top: 10px; margin-bottom: 10px">
                <input type="button" class="button-primary action" value="Back"/>
            </div>
        </div>
    </div>

    <!-- Title -->
    <div class="wsdplugin_alert_section_title"></div>

    <!-- Description -->
    <div class="wsdplugin_alert_section_description">
        <h3>Description</h3>
        <p></p>
    </div>


    <div class="wsdplugin_alerts_exapand_collapse_actions" style="margin-top: -30px; margin-bottom: 7px; overflow: hidden;">
        <div class="tablenav alignright">
            <ul class="subsubsub">
                <li><a href="#expand">Expand All</a></li>
                <li><a href="#collapse">Collapse All</a></li>
            </ul>
        </div>
    </div>


    <!-- Body -->
    <div class="wsdplugin_alert_section_body">
        <table class="widefat" cellspacing="0" cellpadding="0">
            <tbody></tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="tablenav">
        <div class="tablenav-pages">
            <span class="displaying-num"></span>
			<span class="pagination-links">
				<a href="#first" title="Go to the first page" class="first-page">«</a>
				<a href="#back" title="Go to the previous page" class="prev-page">‹</a>
				<span class="paging-input"></span><span class="total-pages"></span>
				<a href="#next" title="Go to the next page" class="next-page">›</a>
				<a href="#last" title="Go to the last page" class="last-page">»</a>
			</span>
        </div>
    </div>

    <!-- Solution -->
    <div class="wsdplugin_alert_section_solution">
        <h3>Solution</h3>
        <p></p>
    </div>


    <!-- Reference -->
    <div class="wsdplugin_alert_section_reference">
        <h3>References</h3>
        <ul></ul>
    </div>

</div>
<!-- Details for alerts -->

</div>
<!-- wsdplugin_page_alerts -->


</div>
<!-- wsdplugin_website_detail -->


<div style="margin-top: 40px;">
	<p>
		<a target="_blank" href="https://dashboard.websitedefender.com/">Want to see your full dashboard? You can login to it here.</a>
	</p>
</div>

</div>
<!-- wsdplugin_content -->

<script type="text/javascript">

    jQuery(function($)
    {
		<?php echo 'WSDPLUGIN_JSRPC_URL = "', wsdplugin_JSRPC_URL, '";'; ?>
        $(window).hashchange(function(){ $(".wsdplugin_content").wsdplugin_main("reload"); });
        $('.wsdplugin_content')
                .wsdplugin_main({
                    targetId: <?php echo "'{$targetId}'";?>,
                    email: <?php echo "'", str_replace('"', '\"', $user), "'";?>,
                    hash: <?php echo "'", $hash, "'";?>
                });
        $('.wsdplugin_content .wsdplugin_website_detail').wsdplugin_websiteDetail();
        $('.wsdplugin_page_alerts').wsdplugin_alerts();
        $('.wsdplugin_content .wsdplugin_page_alerts .wsdplugin_page_alert_list')
                .wsdplugin_alertsList({
                    itemsPerPage: 18
                });
        $('.wsdplugin_content .wsdplugin_page_alerts .wsdplugin_page_alert_types_current')
                .wsdplugin_alertTypesList({
                    uniqueId: 'wsdplugin_uid_alert_types_current',
                    filter: { status: [0, 1] },
                    sort: [['severity', 'DESC'], ['entrytime', 'DESC']],
                    displayAlertCount: false,
                    itemsPerPage: 22
                });
        $('.wsdplugin_content .wsdplugin_page_alerts .wsdplugin_page_alert_types_resolved')
                .wsdplugin_alertTypesList({
                    uniqueId: 'wsdplugin_uid_alert_types_resolved',
                    filter: { status:[2, 3] },
                    displayAlertCount: false,
                    itemsPerPage: 22
                });
        $('.wsdplugin_content .wsdplugin_page_alerts .wsdplugin_page_alert_types_ignored')
                .wsdplugin_alertTypesList({
                    uniqueId: 'wsdplugin_uid_alert_types_ignored',
                    filter: { status: [4] },
                    itemsPerPage: 22,
                    displayAlertCount: false,
                    enableDrillDown: false
                });

	    $('.wsdplugin_content .wsdplugin_page_status').wsdplugin_status({targetId: <?php echo "'{$targetId}'";?>});
    });

</script>