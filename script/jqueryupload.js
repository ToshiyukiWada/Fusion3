/*
 * jQuery.upload v1.0.2
 *
 * Copyright (c) 2010 lagos
 * Dual licensed under the MIT and GPL licenses.
 *
 * http://lagoscript.org
 */
(function($) {

	var uuid = 0;

	$.fn.upload = function(url, data, callback, type) {
		var self = this, inputs, checkbox, checked,
			iframeName = 'jquery_upload' + ++uuid,
			iframe = $('<iframe name="' + iframeName + '" style="position:absolute;top:-9999px" />').appendTo('body'),
			form = '<form target="' + iframeName + '" method="post" enctype="multipart/form-data" />';

		if ($.isFunction(data)) {
			type = callback;
			callback = data;
			data = {};
		}

		checkbox = $('input:checkbox', this);
		checked = $('input:checked', this);
		form = self.wrapAll(form).parent('form').attr('action', url);

		// Make sure radios and checkboxes keep original values
		// (IE resets checkd attributes when appending)
		checkbox.removeAttr('checked');
		checked.attr('checked', true);

		inputs = createInputs(data);
		inputs = inputs ? $(inputs).appendTo(form) : null;

		form.submit(function() {
			iframe.load(function() {
				var data = handleData(this, type),
					checked = $('input:checked', self);

				form.after(self).remove();
				checkbox.removeAttr('checked');
				checked.attr('checked', true);
				if (inputs) {
					inputs.remove();
				}

				setTimeout(function() {
					iframe.remove();
					if (type === 'script') {
						$.globalEval(data);
					}
					if (callback) {
						callback.call(self, data);
					}
				}, 0);
			});
		}).submit();

		return this;
	};

	function createInputs(data) {
		return $.map(param(data), function(param) {
			return '<input type="hidden" name="' + param.name + '" value="' + param.value + '"/>';
		}).join('');
	}

	function param(data) {
		if ($.isArray(data)) {
			return data;
		}
		var params = [];

		function add(name, value) {
			params.push({name:name, value:value});
		}

		if (typeof data === 'object') {
			$.each(data, function(name) {
				if ($.isArray(this)) {
					$.each(this, function() {
						add(name, this);
					});
				} else {
					add(name, $.isFunction(this) ? this() : this);
				}
			});
		} else if (typeof data === 'string') {
			$.each(data.split('&'), function() {
				var param = $.map(this.split('='), function(v) {
					return decodeURIComponent(v.replace(/\+/g, ' '));
				});

				add(param[0], param[1]);
			});
		}

		return params;
	}

	function handleData(iframe, type) {
		var data, contents = $(iframe).contents().get(0);

		if ($.isXMLDoc(contents) || contents.XMLDocument) {
			return contents.XMLDocument || contents;
		}
		data = $(contents).find('body').html();

		switch (type) {
			case 'xml':
				data = parseXml(data);
				break;
			case 'json':
				data = window.eval('(' + data + ')');
				break;
		}
		return data;
	}

	function parseXml(text) {
		if (window.DOMParser) {
			return new DOMParser().parseFromString(text, 'application/xml');
		} else {
			var xml = new ActiveXObject('Microsoft.XMLDOM');
			
			if (xml.xml == "")
			{
				text = text.replace(/<\w+(\s+("[^"]*"|'[^']*'|[^>])+)?>|<\/\w+>/gi, '');
				text = unescapeHTML(text);
				text = replaceAll(text,"- ","-");
				text = replaceAll(text,"-<","<");
				text = replaceAll(text,"  <?xml","<?xml");
				text = replaceAll(text," <?xml","<?xml");
				text = replaceAll(text,"<?xml","<?xml");
				if (text.substr(0,1) != "<"){ text = text.substr(1); }
				xml.loadXML(text);
			}
			
			xml.async = false;
			xml.loadXML(text);
			return xml;
		}
	}
	
	function unescapeHTML(text)
	{
		var div = document.createElement('div');
		div.innerHTML = text;
		return div.childNodes[0] ? (div.childNodes.length > 1 ?
		$A(div.childNodes).inject('', function(memo, node) { return memo+node.nodeValue }) :
		div.childNodes[0].nodeValue) : '';
	}

	function replaceAll(expression, org, dest){
	    return expression.split(org).join(dest);
	}

})(jQuery);
