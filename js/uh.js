var wsdplugin_uh =
{
	getUrl: function()
	{
		var url = window.location.toString();
		for(var i=0; i<url.length; i++)
		{
			if(url.substring(i, i+1)=='#') return url.substring(0, i);
		}
		return url;
	},
	data:[],
	get:function(name, reload)
	{
		if(reload === true) this.load();
		if(name in this.data) return this.data[name];
		return undefined;
	},
	load:function()
	{
		this.data = [];
		var s = location.hash.substring(1);
		if(typeof(s)!='string') return;
		var r = s.split("/");
		for(var i=0; i<r.length; i++)
		{
			if(r[i].length < 2)continue;
			this.data[r[i].substring(0,1)]=r[i].substring(1);
		}
	},
	anchor:function(data)
	{
		if(data == undefined) data = this.data;
		var r = '#';
		for(var i in data) r = r + i + data[i] + '/';
		return r;
	},
	combine:function(data, reload)
	{
		if(reload === true) this.load();
		var newData = [];
		for(var i in data)
		{
			if(data[i] === true)
			{
				if(i in this.data)
					newData[i] = this.data[i];
				continue;
			}
			newData[i]=data[i];
		}
		return newData;
	},
	getNewAnchor:function(data, reload)
	{
		return this.anchor(this.combine(data, reload));
	},
	newLocation:function(data, reload)
	{
		var r = this.anchor(this.combine(data, reload));
		if(document.location.hash == r) return false;
		document.location = this.getUrl() + r;
		return true;
	}
};
