/**
 * This file is part of the ListControl
 *
 * Copyright (c) 2016 Tomáš Karlík (http://tomaskarlik.cz)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

/**
 * Client-side script for ListControl
 */
$(function () {

    if (typeof Nette === 'undefined') { //check load netteForms
	console.error('list-control.js: netteForms.js is missing, load it please!');
    }

    $('.list-control').on('change', 'form select', function (event) { //change filter option
	$(this).closest("form").submit();
	event.preventDefault();
    });

    $('.list-control').on('submit', 'form', function (event) { //submit filter form	
	$.fn.listControlLink(this, event);
    });

    $('.list-control').on('click', '.pages a, .per-page a, th a', function (event) { //sort or paginate
	$.fn.listControlLink(this, event);
    });

    $.fn.listControlLink = function (target, event) { //ajax links + add params to URL
	$.nette.ajax({
	    success: function (payload) {
		if ((typeof payload.listControlState !== 'undefined') && payload.listControlState) {
		    window.location = payload.listControlState.replace('?', '#');
		}
	    }
	}, target, event);
	event.preventDefault();
    };

    $.fn.listControlInit = function () {
	var url = location.toString();
	var component = $('.list-control form').data('control');
	if ((url.indexOf('#') >= 0) && component) {
	    url = url.indexOf('?') >= 0 ? url.replace('#', '&') : url.replace('#', '?');
	    $.nette.ajax(url + '&do=' + component + '-reload');
	}
    };

    $.fn.listControlInit();
});