/*jslint bitwise: true, browser: true, eqeqeq: true, immed: true, newcap: true, nomen: true, onevar: true, plusplus: true, regexp: true, undef: true, white: true */


//common page enhancements
(function () {
	var i, n, elements;

	//kick unsupporting browsers
	if (!document.addEventListener) {
		return;
	}

	//form elements: select content on focus
	elements = document.getElementsByTagName('input');
	for (i = 0, n = elements.length; i < n; i += 1) {
		elements[i].addEventListener('focus', elements[i].select, false);
	}
	elements = document.getElementsByTagName('textarea');
	for (i = 0, n = elements.length; i < n; i += 1) {
		elements[i].addEventListener('focus', elements[i].select, false);
	}

}());
