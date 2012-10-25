WSDPLUGIN_ERROR_TOKEN       = 0x04;
WSDPLUGIN_ERROR_LOGIN       = 0x10;
WSDPLUGIN_ERROR_CLIENTSIDE  = 0x30;


wsdplugin_rpc_token = null;



wsdplugin_httpRequest = function()
{
	this._requestId = null;
	this._userData = null;
	this._callbackContext = null;
	this._successCallback = null;
	this._errorCallback = null;

	this.onError = function(error)
	{
		if (error.code == WSDPLUGIN_ERROR_LOGIN || error.code == WSDPLUGIN_ERROR_TOKEN)
		{
			jQuery.wsdplugin_showError(error.message + ' Usually this is fixed with a page refresh.', error.code);
			wsdplugin_logger.error(error.code, error.message);
			return;
		}

		var callbackArgs = [error, this._userData];

		if (typeof(this._errorCallback) === 'function')
		{
			this._errorCallback.apply(this._callbackContext || window, callbackArgs);
		}
		else if (typeof(this._errorCallback) === 'string')
		{
			jQuery(this._callbackContext || document).trigger(this._errorCallback, callbackArgs);
		}
	};

	this.onSuccess = function(result)
	{
		var callbackArgs = [result, this._userData];

		if (typeof(this._successCallback) === 'function')
		{
			this._successCallback.apply(this._callbackContext || window, callbackArgs);
		}
		else if (typeof(this._successCallback) === 'string')
		{
			jQuery(this._callbackContext || document).trigger(this._successCallback, callbackArgs);
		}
	};
}


wsdplugin_doHTTPRPC = function(method, params, context, onSuccess, onError, userData)
{
	this._refCount = 0;

	this.do = function(method, params, context, onSuccess, onError, userData)
	{
		var requestInfo = new wsdplugin_httpRequest();
		requestInfo._requestId = ('' + Math.random() * 1000).substring(0, 10);
		requestInfo._callbackContext = context;
		requestInfo._successCallback = onSuccess;
		requestInfo._errorCallback = onError;
		requestInfo._userData = userData;

		var self = this;

		var requestData = {rpc: JSON.stringify({
			'jsonrpc': '2.0',
			'id': requestInfo._requestId,
			'method': method,
			'params': params,
			'token' : wsdplugin_rpc_token})
		};

		this.addRef();

		jQuery.getJSON(WSDPLUGIN_JSRPC_URL, requestData, function(response)
		{
			self.release();

			if (response === null)
			{
				this.onError({ code: WSDPLUGIN_ERROR_CLIENTSIDE, message: 'Invalid server response.' });
				return;
			}

			if ((typeof(response) === 'object') && ('jsonrpc' in response) && ('id' in response) && (parseFloat(response.jsonrpc) == 2))
			{
				if ((response.id == requestInfo._requestId) && ('result' in response))
				{
					if ('token' in response)
						wsdplugin_rpc_token = response.token;

					try
					{
						requestInfo.onSuccess(response.result);
					}
					catch(err)
					{
						wsdplugin_logger.error('bug: ', err);
					}
					return;
				}
				if (('error' in response) && ('code' in response.error))
				{
					requestInfo.onError(response.error);
					return;
				}
			}
			requestInfo.onError({ code: WSDPLUGIN_ERROR_CLIENTSIDE, message: 'Invalid server response Json.' });
		});
	};

	this.addRef = function()
	{
		this._refCount++;

		if (this._refCount > 0)
		{
			jQuery('.wsdplugin_content .wsdplugin_request_overlay').show();
		}
	};

	this.release = function()
	{
		if (this._refCount > 0) {
			this._refCount--;
		}
		if (this._refCount == 0)
			jQuery('.wsdplugin_content .wsdplugin_request_overlay').hide();
	};

	this.do(method, params, context, onSuccess, onError, userData);
}

