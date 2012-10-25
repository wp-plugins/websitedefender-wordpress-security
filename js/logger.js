wsdplugin_LOGGER_LEVEL_OFF    = 0;
wsdplugin_LOGGER_LEVEL_ERROR  = 1;
wsdplugin_LOGGER_LEVEL_WARN   = 2;
wsdplugin_LOGGER_LEVEL_INFO   = 3;
wsdplugin_LOGGER_LEVEL_DEBUG  = 4;


var wsdplugin_logger =
{
    level: wsdplugin_LOGGER_LEVEL_OFF,

    debug: function()
    {
	    if (this.level >= wsdplugin_LOGGER_LEVEL_DEBUG)
        {
            if (console && typeof(console.log) === 'function')
                console.log.apply(console, arguments);
        }
    },

    info: function()
    {
	    if (this.level >= wsdplugin_LOGGER_LEVEL_INFO)
        {
	        if (console)
	        {
	            if (typeof(console.info) === 'function')
	                console.info.apply(console, arguments);
	            else if (typeof(console.log) === 'function')
	                console.log.apply(console, ['INFO: ', arguments]);
	        }
        }
    },

    warn: function()
    {
        if (this.level >= wsdplugin_LOGGER_LEVEL_WARN)
        {
	        if (console)
	        {
		        if (typeof(console.warn) === 'function')
			        console.warn.apply(console, arguments);
		        else if (typeof(console.log) === 'function')
			        console.log.apply(console, ['WARN: ', arguments]);
	        }
        }
    },

    error: function()
    {
        if (this.level >= wsdplugin_LOGGER_LEVEL_ERROR)
        {
	        if (console)
	        {
	            if (typeof(console.error) === 'function')
	                console.error.apply(console, arguments);
	            else if (typeof(console.log) === 'function')
	                console.log.apply(console, ['ERROR: ', arguments]);
		    }
        }
    }
};
