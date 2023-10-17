/*jslint bitwise: true, browser: true, eqeqeq: true, immed: true, newcap: true, nomen: true, onevar: true, plusplus: true, regexp: true, undef: true, white: true */
/*global window */

//search popup support
var scyzorykSearch = {

	open: function (event) {
		try {
			if (this.windowSearch && this.windowSearch.close && !this.windowSearch.closed) {
				this.windowSearch.close();
				this.windowSearch = null;
			}
			this.windowSearch = window.open('search?type=' + this.getAttribute('data-search-type'),
				this.getAttribute('data-search-target'));
		} catch (ex) {
			alert('Błąd otwierania popupa.');
		}
	},

	loadResult: function (event) {
		var target;
		if (window.opener && window.opener.document) {
			target = window.opener.document.getElementById(window.name);
			if (target) {
				target.value = this.getAttribute('data-search-result');
				window.close();
			}
		}
	},

	init: function (elements) {
		var e, i, n;
		for (i = 0, n = elements.length; i < n; i += 1) {
			e = elements[i];
			if (e.hasAttribute('data-search-type') && e.hasAttribute('data-search-target')) {
				e.addEventListener('click', this.open, false);
			} else if (e.hasAttribute('data-search-result')) {
				e.addEventListener('click', this.loadResult, false);
			}
		}
	}

};

(function () {

	//kick unsupporting browsers
	if (!document.addEventListener) {
		return;
	}

	//add search buttons
	scyzorykSearch.init(document.getElementsByTagName('input'));
	scyzorykSearch.init(document.getElementsByTagName('img'));

}());
