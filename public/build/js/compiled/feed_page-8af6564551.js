/* ===================================================
* bootstrap-suggest.js
* http://github.com/lodev09/bootstrap-suggest
* ===================================================
* Copyright 2017 Jovanni Lo @lodev09
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
* ========================================================== */

(function ($) {

	"use strict"; // jshint ;_;

	var Suggest = function(el, key, options) {
		var that = this;

		this.$element = $(el);
		this.$items = undefined;
		this.options = $.extend(true, {}, $.fn.suggest.defaults, options, this.$element.data(), this.$element.data('options'));
		this.key = key;
		this.isShown = false;
		this.query = '';
		this._queryPos = [];
		this._keyPos = -1;

		this.$dropdown = $('<div />', {
			'class': 'dropdown suggest ' + this.options.dropdownClass,
			'html': $('<ul />', {'class': 'dropdown-menu', role: 'menu'}),
			'data-key': this.key
		});

		this.load();

	};

	Suggest.prototype = {
		__setListener: function() {
			this.$element
			.on('suggest.show', $.proxy(this.options.onshow, this))
			.on('suggest.select', $.proxy(this.options.onselect, this))
			.on('suggest.lookup', $.proxy(this.options.onlookup, this))
			.on('keyup', $.proxy(this.__keyup, this));

			return this;
		},

		__getCaretPos: function(posStart) {
			// https://github.com/component/textarea-caret-position/blob/master/index.js

			// The properties that we copy into a mirrored div.
			// Note that some browsers, such as Firefox,
			// do not concatenate properties, i.e. padding-top, bottom etc. -> padding,
			// so we have to do every single property specifically.
			var properties = [
				'direction',  // RTL support
				'boxSizing',
				'width',  // on Chrome and IE, exclude the scrollbar, so the mirror div wraps exactly as the textarea does
				'height',
				'overflowX',
				'overflowY',  // copy the scrollbar for IE

				'borderTopWidth',
				'borderRightWidth',
				'borderBottomWidth',
				'borderLeftWidth',

				'paddingTop',
				'paddingRight',
				'paddingBottom',
				'paddingLeft',

				// https://developer.mozilla.org/en-US/docs/Web/CSS/font
				'fontStyle',
				'fontVariant',
				'fontWeight',
				'fontStretch',
				'fontSize',
				'fontSizeAdjust',
				'lineHeight',
				'fontFamily',

				'textAlign',
				'textTransform',
				'textIndent',
				'textDecoration',  // might not make a difference, but better be safe

				'letterSpacing',
				'wordSpacing'
			];

			var isFirefox = !(window.mozInnerScreenX == null);

			var getCaretCoordinatesFn = function (element, position, recalculate) {
				// mirrored div
				var div = document.createElement('div');
				div.id = 'input-textarea-caret-position-mirror-div';
				document.body.appendChild(div);

				var style = div.style;
				var computed = window.getComputedStyle? getComputedStyle(element) : element.currentStyle;  // currentStyle for IE < 9

				// default textarea styles
				style.whiteSpace = 'pre-wrap';
				if (element.nodeName !== 'INPUT')
				style.wordWrap = 'break-word';  // only for textarea-s

				// position off-screen
				style.position = 'absolute';  // required to return coordinates properly
				style.visibility = 'hidden';  // not 'display: none' because we want rendering

				// transfer the element's properties to the div
				$.each(properties, function (index, value)
				{
					style[value] = computed[value];
				});

				if (isFirefox) {
					style.width = parseInt(computed.width) - 2 + 'px';  // Firefox adds 2 pixels to the padding - https://bugzilla.mozilla.org/show_bug.cgi?id=753662
					// Firefox lies about the overflow property for textareas: https://bugzilla.mozilla.org/show_bug.cgi?id=984275
					if (element.scrollHeight > parseInt(computed.height))
					style.overflowY = 'scroll';
				} else {
					style.overflow = 'hidden';  // for Chrome to not render a scrollbar; IE keeps overflowY = 'scroll'
				}

				div.textContent = element.value.substring(0, position);
				// the second special handling for input type="text" vs textarea: spaces need to be replaced with non-breaking spaces - http://stackoverflow.com/a/13402035/1269037
				if (element.nodeName === 'INPUT')
				div.textContent = div.textContent.replace(/\s/g, "\u00a0");

				var span = document.createElement('span');
				// Wrapping must be replicated *exactly*, including when a long word gets
				// onto the next line, with whitespace at the end of the line before (#7).
				// The  *only* reliable way to do that is to copy the *entire* rest of the
				// textarea's content into the <span> created at the caret position.
				// for inputs, just '.' would be enough, but why bother?
				span.textContent = element.value.substring(position) || '.';  // || because a completely empty faux span doesn't render at all
				div.appendChild(span);

				var coordinates = {
					top: span.offsetTop + parseInt(computed['borderTopWidth']),
					left: span.offsetLeft + parseInt(computed['borderLeftWidth'])
				};

				document.body.removeChild(div);

				return coordinates;
			}

			return getCaretCoordinatesFn(this.$element.get(0), posStart);
		},

		__keyup: function(e) {
			// don't query special characters
			// http://mikemurko.com/general/jquery-keycode-cheatsheet/
			var specialChars = [38, 40, 37, 39, 17, 18, 9, 16, 20, 91, 93, 36, 35, 45, 33, 34, 144, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 145, 19],
			$resultItems;

			switch (e.keyCode) {
				case 27:
					this.hide();
					return;
				case 13:
					return true;
			}

			if ($.inArray(e.keyCode, specialChars) !== -1) return true;

			var $el = this.$element,
			val = $el.val(),
			currentPos = this.__getSelection($el.get(0)).start;
			for (var i = currentPos; i >= 0; i--) {
				var subChar = $.trim(val.substring(i-1, i));
				if (!subChar) {
					this.hide();
					break;
				}
				if (subChar === this.key && $.trim(val.substring(i-2, i-1)) == '') {
					this.query = val.substring(i, currentPos);
					this._queryPos = [i, currentPos];
					this._keyPos = i;
					this.lookup(this.query);
					break;
				}
			}
		},

		__getVisibleItems: function() {
			return this.$items ? this.$items.not('.hidden') : $();
		},

		__build: function() {
			var elems = [], $item,
			$dropdown = this.$dropdown,
			that = this;

			var blur = function(e) {
				that.hide();
			}

			$dropdown
			.on('click', 'li:has(a)', function(e) {
				e.preventDefault();
				that.__select($(this).index());
				that.$element.focus();
			})
			.on('mouseover', 'li:has(a)', function(e) {
				that.$element.off('blur', blur);
			})
			.on('mouseout', 'li:has(a)', function(e) {
				that.$element.on('blur', blur);
			});

			this.$element.before($dropdown)
			.on('blur', blur)
			.on('keydown', function(e) {
				var $visibleItems;
				if (that.isShown) {
					switch (e.keyCode) {
						case 13: // enter key
							$visibleItems = that.__getVisibleItems();
							$visibleItems.each(function(index) {
								if ($(this).is('.active'))
								that.__select($(this).index());
							});

							return false;
							break;
						case 40: // arrow down
							$visibleItems = that.__getVisibleItems();
							if ($visibleItems.last().is('.active')) return false;
							$visibleItems.each(function(index) {
								var $this = $(this),
								$next = $visibleItems.eq(index + 1);

								//if (!$next.length) return false;

								if ($this.is('.active')) {
									if (!$next.is('.hidden')) {
										$this.removeClass('active');
										$next.addClass('active');
									}
									return false;
								}
							});
							return false;
						case 38: // arrow up
							$visibleItems = that.__getVisibleItems();
							if ($visibleItems.first().is('.active')) return false;
							$visibleItems.each(function(index) {
								var $this = $(this),
								$prev = $visibleItems.eq(index - 1);

								//if (!$prev.length) return false;

								if ($this.is('.active')) {
									if (!$prev.is('.hidden')) {
										$this.removeClass('active');
										$prev.addClass('active');
									}
									return false;
								}
							})
							return false;
					}
				}
			});

		},

		__mapItem: function(dataItem) {
			var itemHtml, that = this,
			_item = {
				text: '',
				value: ''
			};

			if (this.options.map) {
				dataItem = this.options.map(dataItem);
				if (!dataItem) return false;
			}

			if (dataItem instanceof Object) {
				_item.text = dataItem.text || '';
				_item.value = dataItem.value || '';
			} else {
				_item.text = dataItem;
				_item.value = dataItem;
			}

			return $('<li />', {'data-value': _item.value}).html($('<a />', {
				href: '#',
				html: _item.text
			}));
		},

		__select: function(index) {
			var $el = this.$element,
			el = $el.get(0),
			val = $el.val(),
			item = this.get(index),
			setCaretPos = this._keyPos + item.value.length + 1;

			$el.val(val.slice(0, this._keyPos) + item.value + ' ' + val.slice(this.__getSelection(el).start));

			if (el.setSelectionRange) {
				el.setSelectionRange(setCaretPos, setCaretPos);
			} else if (el.createTextRange) {
				var range = el.createTextRange();
				range.collapse(true);
				range.moveEnd('character', setCaretPos);
				range.moveStart('character', setCaretPos);
				range.select();
			}

			$el.trigger($.extend({type: 'suggest.select'}, this), item);

			this.hide();
		},

		__getSelection: function (el) {
			var start = 0,
			end = 0,
			rawValue,
			normalizedValue,
			range,
			textInputRange,
			len,
			endRange;
			el.focus();//in IE9 selectionStart will always be 9 if not focused(when selecting using the mouse)
			if (typeof el.selectionStart == "number" && typeof el.selectionEnd == "number") {
				start = el.selectionStart;
				end = el.selectionEnd;
			} else {
				range = document.selection.createRange();

				if (range && range.parentElement() === el) {
					rawValue = el.value;
					len = rawValue.length;
					normalizedValue = rawValue.replace(/\r\n/g, "\n");

					// Create a working TextRange that lives only in the input
					textInputRange = el.createTextRange();
					textInputRange.moveToBookmark(range.getBookmark());

					// Check if the start and end of the selection are at the very end
					// of the input, since moveStart/moveEnd doesn't return what we want
					// in those cases
					endRange = el.createTextRange();
					endRange.collapse(false);

					if (textInputRange.compareEndPoints("StartToEnd", endRange) > -1) {
						start = end = len;
					} else {
						start = -textInputRange.moveStart("character", -len);
						start += normalizedValue.slice(0, start).split("\n").length - 1;

						if (textInputRange.compareEndPoints("EndToEnd", endRange) > -1) {
							end = len;
						} else {
							end = -textInputRange.moveEnd("character", -len);
							end += normalizedValue.slice(0, end).split("\n").length - 1;
						}
					}

					/// normalize newlines
					start -= (rawValue.substring(0, start).split('\r\n').length - 1);
					end -= (rawValue.substring(0, end).split('\r\n').length - 1);
					/// normalize newlines
				}
			}
			return {
				start: start,
				end: end
			};
		},

		__buildItems: function(data) {
			var $dropdownMenu = this.$dropdown.find('.dropdown-menu');
			$dropdownMenu.empty();
			if (data && data instanceof Array) {
				for (var i in data) {
					var $item = this.__mapItem(data[i]);
					if ($item) {
						$dropdownMenu.append($item);
					}
				}
			}
			return $dropdownMenu.find('li:has(a)');
		},

		__lookup: function(q, $resultItems) {
			this.$element.trigger($.extend({type: 'suggest.lookup'}, this), [q, $resultItems]);
			var active = $resultItems.eq(0).addClass('active');
			if ($resultItems && $resultItems.length) {
				this.show();
			} else {
				this.hide();
			}
		},

		__filterData: function(q, data) {
			var options = this.options;
			this.$items.addClass('hidden');
			this.$items.filter(function (index) {

				// return the limit if q is empty
				if (q === '') return index < options.filter.limit;

				var $this = $(this),
				value = $this.find('a:first').text();

				if (!options.filter.casesensitive) {
					value = value.toLowerCase();
					q = q.toLowerCase();
				}
				return value.indexOf(q) != -1;
			}).slice(0, options.filter.limit).removeClass('hidden active');
			return this.__getVisibleItems();
		},

		get: function(index) {
			if (!this.$items) return;

			var $item = this.$items.eq(index);
			return {
				text: $item.children('a:first').text(),
				value: $item.attr('data-value'),
				index: index,
				$element: $item
			};
		},

		lookup: function(q) {
			var options = this.options,
				that = this,
				data;

			var provide = function(data) {
				// verify that we're still "typing" the query (no space)
				if (that._keyPos !== -1) {
					if (!that.$items) {
						that.$items = that.__buildItems(data);
					}

					that.__lookup(q, that.__filterData(q, data));
				}
			};

			if (typeof this.options.data === 'function') {
				this.$items = undefined;
				data = this.options.data(q, provide);
			} else {
				data = this.options.data;
			}

			if (data && typeof data.promise === 'function') {
				data.done(provide);
			} else if (data) {
				provide.call(this, data);
			}
		},

		load: function() {
			this.__setListener();
			this.__build();
		},

		hide: function() {
			this.$dropdown.removeClass('open');
			this.isShown = false;
			if(this.$items) {
				this.$items.removeClass('active');
			}
			this._keyPos = -1;
		},

		show: function() {
			var $el = this.$element,
			$dropdownMenu = this.$dropdown.find('.dropdown-menu'),
			el = $el.get(0),
			options = this.options,
			caretPos,
			position = {
				top: 'auto',
				bottom: 'auto',
				left: 'auto',
				right: 'auto'
			};

			if (!this.isShown) {

				this.$dropdown.addClass('open');
				if (options.position !== false) {

					caretPos = this.__getCaretPos(this._keyPos);

					if (typeof options.position == 'string') {
						switch (options.position) {
							case 'bottom':
								position.top = $el.outerHeight() - parseFloat($dropdownMenu.css('margin-top'));
								position.left = 0;
								position.right = 0;
								break;
							case 'top':
								position.top = -($dropdownMenu.outerHeight(true) + parseFloat($dropdownMenu.css('margin-top')));
								position.left = 0;
								position.right = 0;
								break;
							case 'caret':
								position.top = caretPos.top - el.scrollTop;
								position.left = caretPos.left - el.scrollLeft;
								break;
						}

					} else {
						position = $.extend(position, typeof options.position === 'function' ? options.position(el, caretPos) : options.position);
					}

					$dropdownMenu.css(position);
				}

				this.isShown = true;
				$el.trigger($.extend({type: 'suggest.show'}, this));
			}
		}
	};

	var old = $.fn.suggest;

	// .suggest( key [, options] )
	// .suggest( method [, options] )
	// .suggest( suggestions )
	$.fn.suggest = function(arg1) {
		var arg2 = arguments[1];
		var arg3 = arguments[2];

		var createSuggestions = function(el, suggestions) {
			var newData = {};
			$.each(suggestions, function(keyChar, options) {
				var key =  keyChar.toString().charAt(0);

				// remove existing suggest
				// $('.suggest.dropdown[data-key="'+key+'"]').remove();
				newData[key] = new Suggest(el, key, typeof options === 'object' && options);
			});

			return newData;
		};

		return this.each(function() {
			var that = this,
			$this = $(this),
			data = $this.data('suggest'),
			suggestions = {};

			if (typeof arg1 === 'string') {
				if (arg1.length == 1) {
					// arg1 as key
					if (arg2) {
						// arg2 is a function name
						if (typeof arg2 === 'string') {
							if (arg1 in data && typeof data[arg1][arg2] !== 'undefined') {
								return data[arg1][arg2].call(data[arg1], arg3);
							} else {
								console.error(arg1 + ' is not a suggest');
							}
						} else {
							// inline data determined if it's an array
							suggestions[arg1] = $.isArray(arg2) || typeof arg2 === 'function' ? {data: arg2} : arg2;

							// if key is existing, update options
							if (data && arg1 in data) {
								data[arg1].options = $.extend({}, data[arg1].options, suggestions[arg1]);
							} else {
								data = $.extend(data, createSuggestions(this, suggestions));
							}

							$this.data('suggest', data);
						}
					}
				} else {
					console.error('you\'re not initializing suggest properly. arg1 should have length == 1');
				}
			} else {
				// arg1 contains set of suggestions
				if (!data) $this.data('suggest', createSuggestions(this, arg1));
				else if (data) {
					// create/update suggestions
					$.each(arg1, function(key, value) {
						if (key in data === false) {
							suggestions[key] = value;
						} else {
							// extend (update) options
							data[key].options = $.extend({}, data[key].options, value);
						}
					});

					$this.data('suggest', $.extend(data, createSuggestions(that, suggestions)))
				}
			}
		});
	};

	$.fn.suggest.defaults = {
		data: [],
		map: undefined,
		filter: {
			casesensitive: false,
			limit: 5
		},
		dropdownClass: '',
		position: 'caret',
		// events hook
		onshow: function(e) {},
		onselect: function(e, item) {},
		onlookup: function(e, item) {}

	}

	$.fn.suggest.Constructor = Suggest;

	$.fn.suggest.noConflict = function () {
		$.fn.suggest = old;
		return this;
	}

}( jQuery ));

//! moment.js
//! version : 2.18.1
//! authors : Tim Wood, Iskren Chernev, Moment.js contributors
//! license : MIT
//! momentjs.com
!function(a,b){"object"==typeof exports&&"undefined"!=typeof module?module.exports=b():"function"==typeof define&&define.amd?define(b):a.moment=b()}(this,function(){"use strict";function a(){return sd.apply(null,arguments)}function b(a){sd=a}function c(a){return a instanceof Array||"[object Array]"===Object.prototype.toString.call(a)}function d(a){return null!=a&&"[object Object]"===Object.prototype.toString.call(a)}function e(a){var b;for(b in a)return!1;return!0}function f(a){return void 0===a}function g(a){return"number"==typeof a||"[object Number]"===Object.prototype.toString.call(a)}function h(a){return a instanceof Date||"[object Date]"===Object.prototype.toString.call(a)}function i(a,b){var c,d=[];for(c=0;c<a.length;++c)d.push(b(a[c],c));return d}function j(a,b){return Object.prototype.hasOwnProperty.call(a,b)}function k(a,b){for(var c in b)j(b,c)&&(a[c]=b[c]);return j(b,"toString")&&(a.toString=b.toString),j(b,"valueOf")&&(a.valueOf=b.valueOf),a}function l(a,b,c,d){return sb(a,b,c,d,!0).utc()}function m(){return{empty:!1,unusedTokens:[],unusedInput:[],overflow:-2,charsLeftOver:0,nullInput:!1,invalidMonth:null,invalidFormat:!1,userInvalidated:!1,iso:!1,parsedDateParts:[],meridiem:null,rfc2822:!1,weekdayMismatch:!1}}function n(a){return null==a._pf&&(a._pf=m()),a._pf}function o(a){if(null==a._isValid){var b=n(a),c=ud.call(b.parsedDateParts,function(a){return null!=a}),d=!isNaN(a._d.getTime())&&b.overflow<0&&!b.empty&&!b.invalidMonth&&!b.invalidWeekday&&!b.nullInput&&!b.invalidFormat&&!b.userInvalidated&&(!b.meridiem||b.meridiem&&c);if(a._strict&&(d=d&&0===b.charsLeftOver&&0===b.unusedTokens.length&&void 0===b.bigHour),null!=Object.isFrozen&&Object.isFrozen(a))return d;a._isValid=d}return a._isValid}function p(a){var b=l(NaN);return null!=a?k(n(b),a):n(b).userInvalidated=!0,b}function q(a,b){var c,d,e;if(f(b._isAMomentObject)||(a._isAMomentObject=b._isAMomentObject),f(b._i)||(a._i=b._i),f(b._f)||(a._f=b._f),f(b._l)||(a._l=b._l),f(b._strict)||(a._strict=b._strict),f(b._tzm)||(a._tzm=b._tzm),f(b._isUTC)||(a._isUTC=b._isUTC),f(b._offset)||(a._offset=b._offset),f(b._pf)||(a._pf=n(b)),f(b._locale)||(a._locale=b._locale),vd.length>0)for(c=0;c<vd.length;c++)d=vd[c],e=b[d],f(e)||(a[d]=e);return a}function r(b){q(this,b),this._d=new Date(null!=b._d?b._d.getTime():NaN),this.isValid()||(this._d=new Date(NaN)),wd===!1&&(wd=!0,a.updateOffset(this),wd=!1)}function s(a){return a instanceof r||null!=a&&null!=a._isAMomentObject}function t(a){return a<0?Math.ceil(a)||0:Math.floor(a)}function u(a){var b=+a,c=0;return 0!==b&&isFinite(b)&&(c=t(b)),c}function v(a,b,c){var d,e=Math.min(a.length,b.length),f=Math.abs(a.length-b.length),g=0;for(d=0;d<e;d++)(c&&a[d]!==b[d]||!c&&u(a[d])!==u(b[d]))&&g++;return g+f}function w(b){a.suppressDeprecationWarnings===!1&&"undefined"!=typeof console&&console.warn&&console.warn("Deprecation warning: "+b)}function x(b,c){var d=!0;return k(function(){if(null!=a.deprecationHandler&&a.deprecationHandler(null,b),d){for(var e,f=[],g=0;g<arguments.length;g++){if(e="","object"==typeof arguments[g]){e+="\n["+g+"] ";for(var h in arguments[0])e+=h+": "+arguments[0][h]+", ";e=e.slice(0,-2)}else e=arguments[g];f.push(e)}w(b+"\nArguments: "+Array.prototype.slice.call(f).join("")+"\n"+(new Error).stack),d=!1}return c.apply(this,arguments)},c)}function y(b,c){null!=a.deprecationHandler&&a.deprecationHandler(b,c),xd[b]||(w(c),xd[b]=!0)}function z(a){return a instanceof Function||"[object Function]"===Object.prototype.toString.call(a)}function A(a){var b,c;for(c in a)b=a[c],z(b)?this[c]=b:this["_"+c]=b;this._config=a,this._dayOfMonthOrdinalParseLenient=new RegExp((this._dayOfMonthOrdinalParse.source||this._ordinalParse.source)+"|"+/\d{1,2}/.source)}function B(a,b){var c,e=k({},a);for(c in b)j(b,c)&&(d(a[c])&&d(b[c])?(e[c]={},k(e[c],a[c]),k(e[c],b[c])):null!=b[c]?e[c]=b[c]:delete e[c]);for(c in a)j(a,c)&&!j(b,c)&&d(a[c])&&(e[c]=k({},e[c]));return e}function C(a){null!=a&&this.set(a)}function D(a,b,c){var d=this._calendar[a]||this._calendar.sameElse;return z(d)?d.call(b,c):d}function E(a){var b=this._longDateFormat[a],c=this._longDateFormat[a.toUpperCase()];return b||!c?b:(this._longDateFormat[a]=c.replace(/MMMM|MM|DD|dddd/g,function(a){return a.slice(1)}),this._longDateFormat[a])}function F(){return this._invalidDate}function G(a){return this._ordinal.replace("%d",a)}function H(a,b,c,d){var e=this._relativeTime[c];return z(e)?e(a,b,c,d):e.replace(/%d/i,a)}function I(a,b){var c=this._relativeTime[a>0?"future":"past"];return z(c)?c(b):c.replace(/%s/i,b)}function J(a,b){var c=a.toLowerCase();Hd[c]=Hd[c+"s"]=Hd[b]=a}function K(a){return"string"==typeof a?Hd[a]||Hd[a.toLowerCase()]:void 0}function L(a){var b,c,d={};for(c in a)j(a,c)&&(b=K(c),b&&(d[b]=a[c]));return d}function M(a,b){Id[a]=b}function N(a){var b=[];for(var c in a)b.push({unit:c,priority:Id[c]});return b.sort(function(a,b){return a.priority-b.priority}),b}function O(b,c){return function(d){return null!=d?(Q(this,b,d),a.updateOffset(this,c),this):P(this,b)}}function P(a,b){return a.isValid()?a._d["get"+(a._isUTC?"UTC":"")+b]():NaN}function Q(a,b,c){a.isValid()&&a._d["set"+(a._isUTC?"UTC":"")+b](c)}function R(a){return a=K(a),z(this[a])?this[a]():this}function S(a,b){if("object"==typeof a){a=L(a);for(var c=N(a),d=0;d<c.length;d++)this[c[d].unit](a[c[d].unit])}else if(a=K(a),z(this[a]))return this[a](b);return this}function T(a,b,c){var d=""+Math.abs(a),e=b-d.length,f=a>=0;return(f?c?"+":"":"-")+Math.pow(10,Math.max(0,e)).toString().substr(1)+d}function U(a,b,c,d){var e=d;"string"==typeof d&&(e=function(){return this[d]()}),a&&(Md[a]=e),b&&(Md[b[0]]=function(){return T(e.apply(this,arguments),b[1],b[2])}),c&&(Md[c]=function(){return this.localeData().ordinal(e.apply(this,arguments),a)})}function V(a){return a.match(/\[[\s\S]/)?a.replace(/^\[|\]$/g,""):a.replace(/\\/g,"")}function W(a){var b,c,d=a.match(Jd);for(b=0,c=d.length;b<c;b++)Md[d[b]]?d[b]=Md[d[b]]:d[b]=V(d[b]);return function(b){var e,f="";for(e=0;e<c;e++)f+=z(d[e])?d[e].call(b,a):d[e];return f}}function X(a,b){return a.isValid()?(b=Y(b,a.localeData()),Ld[b]=Ld[b]||W(b),Ld[b](a)):a.localeData().invalidDate()}function Y(a,b){function c(a){return b.longDateFormat(a)||a}var d=5;for(Kd.lastIndex=0;d>=0&&Kd.test(a);)a=a.replace(Kd,c),Kd.lastIndex=0,d-=1;return a}function Z(a,b,c){ce[a]=z(b)?b:function(a,d){return a&&c?c:b}}function $(a,b){return j(ce,a)?ce[a](b._strict,b._locale):new RegExp(_(a))}function _(a){return aa(a.replace("\\","").replace(/\\(\[)|\\(\])|\[([^\]\[]*)\]|\\(.)/g,function(a,b,c,d,e){return b||c||d||e}))}function aa(a){return a.replace(/[-\/\\^$*+?.()|[\]{}]/g,"\\$&")}function ba(a,b){var c,d=b;for("string"==typeof a&&(a=[a]),g(b)&&(d=function(a,c){c[b]=u(a)}),c=0;c<a.length;c++)de[a[c]]=d}function ca(a,b){ba(a,function(a,c,d,e){d._w=d._w||{},b(a,d._w,d,e)})}function da(a,b,c){null!=b&&j(de,a)&&de[a](b,c._a,c,a)}function ea(a,b){return new Date(Date.UTC(a,b+1,0)).getUTCDate()}function fa(a,b){return a?c(this._months)?this._months[a.month()]:this._months[(this._months.isFormat||oe).test(b)?"format":"standalone"][a.month()]:c(this._months)?this._months:this._months.standalone}function ga(a,b){return a?c(this._monthsShort)?this._monthsShort[a.month()]:this._monthsShort[oe.test(b)?"format":"standalone"][a.month()]:c(this._monthsShort)?this._monthsShort:this._monthsShort.standalone}function ha(a,b,c){var d,e,f,g=a.toLocaleLowerCase();if(!this._monthsParse)for(this._monthsParse=[],this._longMonthsParse=[],this._shortMonthsParse=[],d=0;d<12;++d)f=l([2e3,d]),this._shortMonthsParse[d]=this.monthsShort(f,"").toLocaleLowerCase(),this._longMonthsParse[d]=this.months(f,"").toLocaleLowerCase();return c?"MMM"===b?(e=ne.call(this._shortMonthsParse,g),e!==-1?e:null):(e=ne.call(this._longMonthsParse,g),e!==-1?e:null):"MMM"===b?(e=ne.call(this._shortMonthsParse,g),e!==-1?e:(e=ne.call(this._longMonthsParse,g),e!==-1?e:null)):(e=ne.call(this._longMonthsParse,g),e!==-1?e:(e=ne.call(this._shortMonthsParse,g),e!==-1?e:null))}function ia(a,b,c){var d,e,f;if(this._monthsParseExact)return ha.call(this,a,b,c);for(this._monthsParse||(this._monthsParse=[],this._longMonthsParse=[],this._shortMonthsParse=[]),d=0;d<12;d++){if(e=l([2e3,d]),c&&!this._longMonthsParse[d]&&(this._longMonthsParse[d]=new RegExp("^"+this.months(e,"").replace(".","")+"$","i"),this._shortMonthsParse[d]=new RegExp("^"+this.monthsShort(e,"").replace(".","")+"$","i")),c||this._monthsParse[d]||(f="^"+this.months(e,"")+"|^"+this.monthsShort(e,""),this._monthsParse[d]=new RegExp(f.replace(".",""),"i")),c&&"MMMM"===b&&this._longMonthsParse[d].test(a))return d;if(c&&"MMM"===b&&this._shortMonthsParse[d].test(a))return d;if(!c&&this._monthsParse[d].test(a))return d}}function ja(a,b){var c;if(!a.isValid())return a;if("string"==typeof b)if(/^\d+$/.test(b))b=u(b);else if(b=a.localeData().monthsParse(b),!g(b))return a;return c=Math.min(a.date(),ea(a.year(),b)),a._d["set"+(a._isUTC?"UTC":"")+"Month"](b,c),a}function ka(b){return null!=b?(ja(this,b),a.updateOffset(this,!0),this):P(this,"Month")}function la(){return ea(this.year(),this.month())}function ma(a){return this._monthsParseExact?(j(this,"_monthsRegex")||oa.call(this),a?this._monthsShortStrictRegex:this._monthsShortRegex):(j(this,"_monthsShortRegex")||(this._monthsShortRegex=re),this._monthsShortStrictRegex&&a?this._monthsShortStrictRegex:this._monthsShortRegex)}function na(a){return this._monthsParseExact?(j(this,"_monthsRegex")||oa.call(this),a?this._monthsStrictRegex:this._monthsRegex):(j(this,"_monthsRegex")||(this._monthsRegex=se),this._monthsStrictRegex&&a?this._monthsStrictRegex:this._monthsRegex)}function oa(){function a(a,b){return b.length-a.length}var b,c,d=[],e=[],f=[];for(b=0;b<12;b++)c=l([2e3,b]),d.push(this.monthsShort(c,"")),e.push(this.months(c,"")),f.push(this.months(c,"")),f.push(this.monthsShort(c,""));for(d.sort(a),e.sort(a),f.sort(a),b=0;b<12;b++)d[b]=aa(d[b]),e[b]=aa(e[b]);for(b=0;b<24;b++)f[b]=aa(f[b]);this._monthsRegex=new RegExp("^("+f.join("|")+")","i"),this._monthsShortRegex=this._monthsRegex,this._monthsStrictRegex=new RegExp("^("+e.join("|")+")","i"),this._monthsShortStrictRegex=new RegExp("^("+d.join("|")+")","i")}function pa(a){return qa(a)?366:365}function qa(a){return a%4===0&&a%100!==0||a%400===0}function ra(){return qa(this.year())}function sa(a,b,c,d,e,f,g){var h=new Date(a,b,c,d,e,f,g);return a<100&&a>=0&&isFinite(h.getFullYear())&&h.setFullYear(a),h}function ta(a){var b=new Date(Date.UTC.apply(null,arguments));return a<100&&a>=0&&isFinite(b.getUTCFullYear())&&b.setUTCFullYear(a),b}function ua(a,b,c){var d=7+b-c,e=(7+ta(a,0,d).getUTCDay()-b)%7;return-e+d-1}function va(a,b,c,d,e){var f,g,h=(7+c-d)%7,i=ua(a,d,e),j=1+7*(b-1)+h+i;return j<=0?(f=a-1,g=pa(f)+j):j>pa(a)?(f=a+1,g=j-pa(a)):(f=a,g=j),{year:f,dayOfYear:g}}function wa(a,b,c){var d,e,f=ua(a.year(),b,c),g=Math.floor((a.dayOfYear()-f-1)/7)+1;return g<1?(e=a.year()-1,d=g+xa(e,b,c)):g>xa(a.year(),b,c)?(d=g-xa(a.year(),b,c),e=a.year()+1):(e=a.year(),d=g),{week:d,year:e}}function xa(a,b,c){var d=ua(a,b,c),e=ua(a+1,b,c);return(pa(a)-d+e)/7}function ya(a){return wa(a,this._week.dow,this._week.doy).week}function za(){return this._week.dow}function Aa(){return this._week.doy}function Ba(a){var b=this.localeData().week(this);return null==a?b:this.add(7*(a-b),"d")}function Ca(a){var b=wa(this,1,4).week;return null==a?b:this.add(7*(a-b),"d")}function Da(a,b){return"string"!=typeof a?a:isNaN(a)?(a=b.weekdaysParse(a),"number"==typeof a?a:null):parseInt(a,10)}function Ea(a,b){return"string"==typeof a?b.weekdaysParse(a)%7||7:isNaN(a)?null:a}function Fa(a,b){return a?c(this._weekdays)?this._weekdays[a.day()]:this._weekdays[this._weekdays.isFormat.test(b)?"format":"standalone"][a.day()]:c(this._weekdays)?this._weekdays:this._weekdays.standalone}function Ga(a){return a?this._weekdaysShort[a.day()]:this._weekdaysShort}function Ha(a){return a?this._weekdaysMin[a.day()]:this._weekdaysMin}function Ia(a,b,c){var d,e,f,g=a.toLocaleLowerCase();if(!this._weekdaysParse)for(this._weekdaysParse=[],this._shortWeekdaysParse=[],this._minWeekdaysParse=[],d=0;d<7;++d)f=l([2e3,1]).day(d),this._minWeekdaysParse[d]=this.weekdaysMin(f,"").toLocaleLowerCase(),this._shortWeekdaysParse[d]=this.weekdaysShort(f,"").toLocaleLowerCase(),this._weekdaysParse[d]=this.weekdays(f,"").toLocaleLowerCase();return c?"dddd"===b?(e=ne.call(this._weekdaysParse,g),e!==-1?e:null):"ddd"===b?(e=ne.call(this._shortWeekdaysParse,g),e!==-1?e:null):(e=ne.call(this._minWeekdaysParse,g),e!==-1?e:null):"dddd"===b?(e=ne.call(this._weekdaysParse,g),e!==-1?e:(e=ne.call(this._shortWeekdaysParse,g),e!==-1?e:(e=ne.call(this._minWeekdaysParse,g),e!==-1?e:null))):"ddd"===b?(e=ne.call(this._shortWeekdaysParse,g),e!==-1?e:(e=ne.call(this._weekdaysParse,g),e!==-1?e:(e=ne.call(this._minWeekdaysParse,g),e!==-1?e:null))):(e=ne.call(this._minWeekdaysParse,g),e!==-1?e:(e=ne.call(this._weekdaysParse,g),e!==-1?e:(e=ne.call(this._shortWeekdaysParse,g),e!==-1?e:null)))}function Ja(a,b,c){var d,e,f;if(this._weekdaysParseExact)return Ia.call(this,a,b,c);for(this._weekdaysParse||(this._weekdaysParse=[],this._minWeekdaysParse=[],this._shortWeekdaysParse=[],this._fullWeekdaysParse=[]),d=0;d<7;d++){if(e=l([2e3,1]).day(d),c&&!this._fullWeekdaysParse[d]&&(this._fullWeekdaysParse[d]=new RegExp("^"+this.weekdays(e,"").replace(".",".?")+"$","i"),this._shortWeekdaysParse[d]=new RegExp("^"+this.weekdaysShort(e,"").replace(".",".?")+"$","i"),this._minWeekdaysParse[d]=new RegExp("^"+this.weekdaysMin(e,"").replace(".",".?")+"$","i")),this._weekdaysParse[d]||(f="^"+this.weekdays(e,"")+"|^"+this.weekdaysShort(e,"")+"|^"+this.weekdaysMin(e,""),this._weekdaysParse[d]=new RegExp(f.replace(".",""),"i")),c&&"dddd"===b&&this._fullWeekdaysParse[d].test(a))return d;if(c&&"ddd"===b&&this._shortWeekdaysParse[d].test(a))return d;if(c&&"dd"===b&&this._minWeekdaysParse[d].test(a))return d;if(!c&&this._weekdaysParse[d].test(a))return d}}function Ka(a){if(!this.isValid())return null!=a?this:NaN;var b=this._isUTC?this._d.getUTCDay():this._d.getDay();return null!=a?(a=Da(a,this.localeData()),this.add(a-b,"d")):b}function La(a){if(!this.isValid())return null!=a?this:NaN;var b=(this.day()+7-this.localeData()._week.dow)%7;return null==a?b:this.add(a-b,"d")}function Ma(a){if(!this.isValid())return null!=a?this:NaN;if(null!=a){var b=Ea(a,this.localeData());return this.day(this.day()%7?b:b-7)}return this.day()||7}function Na(a){return this._weekdaysParseExact?(j(this,"_weekdaysRegex")||Qa.call(this),a?this._weekdaysStrictRegex:this._weekdaysRegex):(j(this,"_weekdaysRegex")||(this._weekdaysRegex=ye),this._weekdaysStrictRegex&&a?this._weekdaysStrictRegex:this._weekdaysRegex)}function Oa(a){return this._weekdaysParseExact?(j(this,"_weekdaysRegex")||Qa.call(this),a?this._weekdaysShortStrictRegex:this._weekdaysShortRegex):(j(this,"_weekdaysShortRegex")||(this._weekdaysShortRegex=ze),this._weekdaysShortStrictRegex&&a?this._weekdaysShortStrictRegex:this._weekdaysShortRegex)}function Pa(a){return this._weekdaysParseExact?(j(this,"_weekdaysRegex")||Qa.call(this),a?this._weekdaysMinStrictRegex:this._weekdaysMinRegex):(j(this,"_weekdaysMinRegex")||(this._weekdaysMinRegex=Ae),this._weekdaysMinStrictRegex&&a?this._weekdaysMinStrictRegex:this._weekdaysMinRegex)}function Qa(){function a(a,b){return b.length-a.length}var b,c,d,e,f,g=[],h=[],i=[],j=[];for(b=0;b<7;b++)c=l([2e3,1]).day(b),d=this.weekdaysMin(c,""),e=this.weekdaysShort(c,""),f=this.weekdays(c,""),g.push(d),h.push(e),i.push(f),j.push(d),j.push(e),j.push(f);for(g.sort(a),h.sort(a),i.sort(a),j.sort(a),b=0;b<7;b++)h[b]=aa(h[b]),i[b]=aa(i[b]),j[b]=aa(j[b]);this._weekdaysRegex=new RegExp("^("+j.join("|")+")","i"),this._weekdaysShortRegex=this._weekdaysRegex,this._weekdaysMinRegex=this._weekdaysRegex,this._weekdaysStrictRegex=new RegExp("^("+i.join("|")+")","i"),this._weekdaysShortStrictRegex=new RegExp("^("+h.join("|")+")","i"),this._weekdaysMinStrictRegex=new RegExp("^("+g.join("|")+")","i")}function Ra(){return this.hours()%12||12}function Sa(){return this.hours()||24}function Ta(a,b){U(a,0,0,function(){return this.localeData().meridiem(this.hours(),this.minutes(),b)})}function Ua(a,b){return b._meridiemParse}function Va(a){return"p"===(a+"").toLowerCase().charAt(0)}function Wa(a,b,c){return a>11?c?"pm":"PM":c?"am":"AM"}function Xa(a){return a?a.toLowerCase().replace("_","-"):a}function Ya(a){for(var b,c,d,e,f=0;f<a.length;){for(e=Xa(a[f]).split("-"),b=e.length,c=Xa(a[f+1]),c=c?c.split("-"):null;b>0;){if(d=Za(e.slice(0,b).join("-")))return d;if(c&&c.length>=b&&v(e,c,!0)>=b-1)break;b--}f++}return null}function Za(a){var b=null;if(!Fe[a]&&"undefined"!=typeof module&&module&&module.exports)try{b=Be._abbr,require("./locale/"+a),$a(b)}catch(a){}return Fe[a]}function $a(a,b){var c;return a&&(c=f(b)?bb(a):_a(a,b),c&&(Be=c)),Be._abbr}function _a(a,b){if(null!==b){var c=Ee;if(b.abbr=a,null!=Fe[a])y("defineLocaleOverride","use moment.updateLocale(localeName, config) to change an existing locale. moment.defineLocale(localeName, config) should only be used for creating a new locale See http://momentjs.com/guides/#/warnings/define-locale/ for more info."),c=Fe[a]._config;else if(null!=b.parentLocale){if(null==Fe[b.parentLocale])return Ge[b.parentLocale]||(Ge[b.parentLocale]=[]),Ge[b.parentLocale].push({name:a,config:b}),null;c=Fe[b.parentLocale]._config}return Fe[a]=new C(B(c,b)),Ge[a]&&Ge[a].forEach(function(a){_a(a.name,a.config)}),$a(a),Fe[a]}return delete Fe[a],null}function ab(a,b){if(null!=b){var c,d=Ee;null!=Fe[a]&&(d=Fe[a]._config),b=B(d,b),c=new C(b),c.parentLocale=Fe[a],Fe[a]=c,$a(a)}else null!=Fe[a]&&(null!=Fe[a].parentLocale?Fe[a]=Fe[a].parentLocale:null!=Fe[a]&&delete Fe[a]);return Fe[a]}function bb(a){var b;if(a&&a._locale&&a._locale._abbr&&(a=a._locale._abbr),!a)return Be;if(!c(a)){if(b=Za(a))return b;a=[a]}return Ya(a)}function cb(){return Ad(Fe)}function db(a){var b,c=a._a;return c&&n(a).overflow===-2&&(b=c[fe]<0||c[fe]>11?fe:c[ge]<1||c[ge]>ea(c[ee],c[fe])?ge:c[he]<0||c[he]>24||24===c[he]&&(0!==c[ie]||0!==c[je]||0!==c[ke])?he:c[ie]<0||c[ie]>59?ie:c[je]<0||c[je]>59?je:c[ke]<0||c[ke]>999?ke:-1,n(a)._overflowDayOfYear&&(b<ee||b>ge)&&(b=ge),n(a)._overflowWeeks&&b===-1&&(b=le),n(a)._overflowWeekday&&b===-1&&(b=me),n(a).overflow=b),a}function eb(a){var b,c,d,e,f,g,h=a._i,i=He.exec(h)||Ie.exec(h);if(i){for(n(a).iso=!0,b=0,c=Ke.length;b<c;b++)if(Ke[b][1].exec(i[1])){e=Ke[b][0],d=Ke[b][2]!==!1;break}if(null==e)return void(a._isValid=!1);if(i[3]){for(b=0,c=Le.length;b<c;b++)if(Le[b][1].exec(i[3])){f=(i[2]||" ")+Le[b][0];break}if(null==f)return void(a._isValid=!1)}if(!d&&null!=f)return void(a._isValid=!1);if(i[4]){if(!Je.exec(i[4]))return void(a._isValid=!1);g="Z"}a._f=e+(f||"")+(g||""),lb(a)}else a._isValid=!1}function fb(a){var b,c,d,e,f,g,h,i,j={" GMT":" +0000"," EDT":" -0400"," EST":" -0500"," CDT":" -0500"," CST":" -0600"," MDT":" -0600"," MST":" -0700"," PDT":" -0700"," PST":" -0800"},k="YXWVUTSRQPONZABCDEFGHIKLM";if(b=a._i.replace(/\([^\)]*\)|[\n\t]/g," ").replace(/(\s\s+)/g," ").replace(/^\s|\s$/g,""),c=Ne.exec(b)){if(d=c[1]?"ddd"+(5===c[1].length?", ":" "):"",e="D MMM "+(c[2].length>10?"YYYY ":"YY "),f="HH:mm"+(c[4]?":ss":""),c[1]){var l=new Date(c[2]),m=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"][l.getDay()];if(c[1].substr(0,3)!==m)return n(a).weekdayMismatch=!0,void(a._isValid=!1)}switch(c[5].length){case 2:0===i?h=" +0000":(i=k.indexOf(c[5][1].toUpperCase())-12,h=(i<0?" -":" +")+(""+i).replace(/^-?/,"0").match(/..$/)[0]+"00");break;case 4:h=j[c[5]];break;default:h=j[" GMT"]}c[5]=h,a._i=c.splice(1).join(""),g=" ZZ",a._f=d+e+f+g,lb(a),n(a).rfc2822=!0}else a._isValid=!1}function gb(b){var c=Me.exec(b._i);return null!==c?void(b._d=new Date(+c[1])):(eb(b),void(b._isValid===!1&&(delete b._isValid,fb(b),b._isValid===!1&&(delete b._isValid,a.createFromInputFallback(b)))))}function hb(a,b,c){return null!=a?a:null!=b?b:c}function ib(b){var c=new Date(a.now());return b._useUTC?[c.getUTCFullYear(),c.getUTCMonth(),c.getUTCDate()]:[c.getFullYear(),c.getMonth(),c.getDate()]}function jb(a){var b,c,d,e,f=[];if(!a._d){for(d=ib(a),a._w&&null==a._a[ge]&&null==a._a[fe]&&kb(a),null!=a._dayOfYear&&(e=hb(a._a[ee],d[ee]),(a._dayOfYear>pa(e)||0===a._dayOfYear)&&(n(a)._overflowDayOfYear=!0),c=ta(e,0,a._dayOfYear),a._a[fe]=c.getUTCMonth(),a._a[ge]=c.getUTCDate()),b=0;b<3&&null==a._a[b];++b)a._a[b]=f[b]=d[b];for(;b<7;b++)a._a[b]=f[b]=null==a._a[b]?2===b?1:0:a._a[b];24===a._a[he]&&0===a._a[ie]&&0===a._a[je]&&0===a._a[ke]&&(a._nextDay=!0,a._a[he]=0),a._d=(a._useUTC?ta:sa).apply(null,f),null!=a._tzm&&a._d.setUTCMinutes(a._d.getUTCMinutes()-a._tzm),a._nextDay&&(a._a[he]=24)}}function kb(a){var b,c,d,e,f,g,h,i;if(b=a._w,null!=b.GG||null!=b.W||null!=b.E)f=1,g=4,c=hb(b.GG,a._a[ee],wa(tb(),1,4).year),d=hb(b.W,1),e=hb(b.E,1),(e<1||e>7)&&(i=!0);else{f=a._locale._week.dow,g=a._locale._week.doy;var j=wa(tb(),f,g);c=hb(b.gg,a._a[ee],j.year),d=hb(b.w,j.week),null!=b.d?(e=b.d,(e<0||e>6)&&(i=!0)):null!=b.e?(e=b.e+f,(b.e<0||b.e>6)&&(i=!0)):e=f}d<1||d>xa(c,f,g)?n(a)._overflowWeeks=!0:null!=i?n(a)._overflowWeekday=!0:(h=va(c,d,e,f,g),a._a[ee]=h.year,a._dayOfYear=h.dayOfYear)}function lb(b){if(b._f===a.ISO_8601)return void eb(b);if(b._f===a.RFC_2822)return void fb(b);b._a=[],n(b).empty=!0;var c,d,e,f,g,h=""+b._i,i=h.length,j=0;for(e=Y(b._f,b._locale).match(Jd)||[],c=0;c<e.length;c++)f=e[c],d=(h.match($(f,b))||[])[0],d&&(g=h.substr(0,h.indexOf(d)),g.length>0&&n(b).unusedInput.push(g),h=h.slice(h.indexOf(d)+d.length),j+=d.length),Md[f]?(d?n(b).empty=!1:n(b).unusedTokens.push(f),da(f,d,b)):b._strict&&!d&&n(b).unusedTokens.push(f);n(b).charsLeftOver=i-j,h.length>0&&n(b).unusedInput.push(h),b._a[he]<=12&&n(b).bigHour===!0&&b._a[he]>0&&(n(b).bigHour=void 0),n(b).parsedDateParts=b._a.slice(0),n(b).meridiem=b._meridiem,b._a[he]=mb(b._locale,b._a[he],b._meridiem),jb(b),db(b)}function mb(a,b,c){var d;return null==c?b:null!=a.meridiemHour?a.meridiemHour(b,c):null!=a.isPM?(d=a.isPM(c),d&&b<12&&(b+=12),d||12!==b||(b=0),b):b}function nb(a){var b,c,d,e,f;if(0===a._f.length)return n(a).invalidFormat=!0,void(a._d=new Date(NaN));for(e=0;e<a._f.length;e++)f=0,b=q({},a),null!=a._useUTC&&(b._useUTC=a._useUTC),b._f=a._f[e],lb(b),o(b)&&(f+=n(b).charsLeftOver,f+=10*n(b).unusedTokens.length,n(b).score=f,(null==d||f<d)&&(d=f,c=b));k(a,c||b)}function ob(a){if(!a._d){var b=L(a._i);a._a=i([b.year,b.month,b.day||b.date,b.hour,b.minute,b.second,b.millisecond],function(a){return a&&parseInt(a,10)}),jb(a)}}function pb(a){var b=new r(db(qb(a)));return b._nextDay&&(b.add(1,"d"),b._nextDay=void 0),b}function qb(a){var b=a._i,d=a._f;return a._locale=a._locale||bb(a._l),null===b||void 0===d&&""===b?p({nullInput:!0}):("string"==typeof b&&(a._i=b=a._locale.preparse(b)),s(b)?new r(db(b)):(h(b)?a._d=b:c(d)?nb(a):d?lb(a):rb(a),o(a)||(a._d=null),a))}function rb(b){var e=b._i;f(e)?b._d=new Date(a.now()):h(e)?b._d=new Date(e.valueOf()):"string"==typeof e?gb(b):c(e)?(b._a=i(e.slice(0),function(a){return parseInt(a,10)}),jb(b)):d(e)?ob(b):g(e)?b._d=new Date(e):a.createFromInputFallback(b)}function sb(a,b,f,g,h){var i={};return f!==!0&&f!==!1||(g=f,f=void 0),(d(a)&&e(a)||c(a)&&0===a.length)&&(a=void 0),i._isAMomentObject=!0,i._useUTC=i._isUTC=h,i._l=f,i._i=a,i._f=b,i._strict=g,pb(i)}function tb(a,b,c,d){return sb(a,b,c,d,!1)}function ub(a,b){var d,e;if(1===b.length&&c(b[0])&&(b=b[0]),!b.length)return tb();for(d=b[0],e=1;e<b.length;++e)b[e].isValid()&&!b[e][a](d)||(d=b[e]);return d}function vb(){var a=[].slice.call(arguments,0);return ub("isBefore",a)}function wb(){var a=[].slice.call(arguments,0);return ub("isAfter",a)}function xb(a){for(var b in a)if(Re.indexOf(b)===-1||null!=a[b]&&isNaN(a[b]))return!1;for(var c=!1,d=0;d<Re.length;++d)if(a[Re[d]]){if(c)return!1;parseFloat(a[Re[d]])!==u(a[Re[d]])&&(c=!0)}return!0}function yb(){return this._isValid}function zb(){return Sb(NaN)}function Ab(a){var b=L(a),c=b.year||0,d=b.quarter||0,e=b.month||0,f=b.week||0,g=b.day||0,h=b.hour||0,i=b.minute||0,j=b.second||0,k=b.millisecond||0;this._isValid=xb(b),this._milliseconds=+k+1e3*j+6e4*i+1e3*h*60*60,this._days=+g+7*f,this._months=+e+3*d+12*c,this._data={},this._locale=bb(),this._bubble()}function Bb(a){return a instanceof Ab}function Cb(a){return a<0?Math.round(-1*a)*-1:Math.round(a)}function Db(a,b){U(a,0,0,function(){var a=this.utcOffset(),c="+";return a<0&&(a=-a,c="-"),c+T(~~(a/60),2)+b+T(~~a%60,2)})}function Eb(a,b){var c=(b||"").match(a);if(null===c)return null;var d=c[c.length-1]||[],e=(d+"").match(Se)||["-",0,0],f=+(60*e[1])+u(e[2]);return 0===f?0:"+"===e[0]?f:-f}function Fb(b,c){var d,e;return c._isUTC?(d=c.clone(),e=(s(b)||h(b)?b.valueOf():tb(b).valueOf())-d.valueOf(),d._d.setTime(d._d.valueOf()+e),a.updateOffset(d,!1),d):tb(b).local()}function Gb(a){return 15*-Math.round(a._d.getTimezoneOffset()/15)}function Hb(b,c,d){var e,f=this._offset||0;if(!this.isValid())return null!=b?this:NaN;if(null!=b){if("string"==typeof b){if(b=Eb(_d,b),null===b)return this}else Math.abs(b)<16&&!d&&(b=60*b);return!this._isUTC&&c&&(e=Gb(this)),this._offset=b,this._isUTC=!0,null!=e&&this.add(e,"m"),f!==b&&(!c||this._changeInProgress?Xb(this,Sb(b-f,"m"),1,!1):this._changeInProgress||(this._changeInProgress=!0,a.updateOffset(this,!0),this._changeInProgress=null)),this}return this._isUTC?f:Gb(this)}function Ib(a,b){return null!=a?("string"!=typeof a&&(a=-a),this.utcOffset(a,b),this):-this.utcOffset()}function Jb(a){return this.utcOffset(0,a)}function Kb(a){return this._isUTC&&(this.utcOffset(0,a),this._isUTC=!1,a&&this.subtract(Gb(this),"m")),this}function Lb(){if(null!=this._tzm)this.utcOffset(this._tzm,!1,!0);else if("string"==typeof this._i){var a=Eb($d,this._i);null!=a?this.utcOffset(a):this.utcOffset(0,!0)}return this}function Mb(a){return!!this.isValid()&&(a=a?tb(a).utcOffset():0,(this.utcOffset()-a)%60===0)}function Nb(){return this.utcOffset()>this.clone().month(0).utcOffset()||this.utcOffset()>this.clone().month(5).utcOffset()}function Ob(){if(!f(this._isDSTShifted))return this._isDSTShifted;var a={};if(q(a,this),a=qb(a),a._a){var b=a._isUTC?l(a._a):tb(a._a);this._isDSTShifted=this.isValid()&&v(a._a,b.toArray())>0}else this._isDSTShifted=!1;return this._isDSTShifted}function Pb(){return!!this.isValid()&&!this._isUTC}function Qb(){return!!this.isValid()&&this._isUTC}function Rb(){return!!this.isValid()&&(this._isUTC&&0===this._offset)}function Sb(a,b){var c,d,e,f=a,h=null;return Bb(a)?f={ms:a._milliseconds,d:a._days,M:a._months}:g(a)?(f={},b?f[b]=a:f.milliseconds=a):(h=Te.exec(a))?(c="-"===h[1]?-1:1,f={y:0,d:u(h[ge])*c,h:u(h[he])*c,m:u(h[ie])*c,s:u(h[je])*c,ms:u(Cb(1e3*h[ke]))*c}):(h=Ue.exec(a))?(c="-"===h[1]?-1:1,f={y:Tb(h[2],c),M:Tb(h[3],c),w:Tb(h[4],c),d:Tb(h[5],c),h:Tb(h[6],c),m:Tb(h[7],c),s:Tb(h[8],c)}):null==f?f={}:"object"==typeof f&&("from"in f||"to"in f)&&(e=Vb(tb(f.from),tb(f.to)),f={},f.ms=e.milliseconds,f.M=e.months),d=new Ab(f),Bb(a)&&j(a,"_locale")&&(d._locale=a._locale),d}function Tb(a,b){var c=a&&parseFloat(a.replace(",","."));return(isNaN(c)?0:c)*b}function Ub(a,b){var c={milliseconds:0,months:0};return c.months=b.month()-a.month()+12*(b.year()-a.year()),a.clone().add(c.months,"M").isAfter(b)&&--c.months,c.milliseconds=+b-+a.clone().add(c.months,"M"),c}function Vb(a,b){var c;return a.isValid()&&b.isValid()?(b=Fb(b,a),a.isBefore(b)?c=Ub(a,b):(c=Ub(b,a),c.milliseconds=-c.milliseconds,c.months=-c.months),c):{milliseconds:0,months:0}}function Wb(a,b){return function(c,d){var e,f;return null===d||isNaN(+d)||(y(b,"moment()."+b+"(period, number) is deprecated. Please use moment()."+b+"(number, period). See http://momentjs.com/guides/#/warnings/add-inverted-param/ for more info."),f=c,c=d,d=f),c="string"==typeof c?+c:c,e=Sb(c,d),Xb(this,e,a),this}}function Xb(b,c,d,e){var f=c._milliseconds,g=Cb(c._days),h=Cb(c._months);b.isValid()&&(e=null==e||e,f&&b._d.setTime(b._d.valueOf()+f*d),g&&Q(b,"Date",P(b,"Date")+g*d),h&&ja(b,P(b,"Month")+h*d),e&&a.updateOffset(b,g||h))}function Yb(a,b){var c=a.diff(b,"days",!0);return c<-6?"sameElse":c<-1?"lastWeek":c<0?"lastDay":c<1?"sameDay":c<2?"nextDay":c<7?"nextWeek":"sameElse"}function Zb(b,c){var d=b||tb(),e=Fb(d,this).startOf("day"),f=a.calendarFormat(this,e)||"sameElse",g=c&&(z(c[f])?c[f].call(this,d):c[f]);return this.format(g||this.localeData().calendar(f,this,tb(d)))}function $b(){return new r(this)}function _b(a,b){var c=s(a)?a:tb(a);return!(!this.isValid()||!c.isValid())&&(b=K(f(b)?"millisecond":b),"millisecond"===b?this.valueOf()>c.valueOf():c.valueOf()<this.clone().startOf(b).valueOf())}function ac(a,b){var c=s(a)?a:tb(a);return!(!this.isValid()||!c.isValid())&&(b=K(f(b)?"millisecond":b),"millisecond"===b?this.valueOf()<c.valueOf():this.clone().endOf(b).valueOf()<c.valueOf())}function bc(a,b,c,d){return d=d||"()",("("===d[0]?this.isAfter(a,c):!this.isBefore(a,c))&&(")"===d[1]?this.isBefore(b,c):!this.isAfter(b,c))}function cc(a,b){var c,d=s(a)?a:tb(a);return!(!this.isValid()||!d.isValid())&&(b=K(b||"millisecond"),"millisecond"===b?this.valueOf()===d.valueOf():(c=d.valueOf(),this.clone().startOf(b).valueOf()<=c&&c<=this.clone().endOf(b).valueOf()))}function dc(a,b){return this.isSame(a,b)||this.isAfter(a,b)}function ec(a,b){return this.isSame(a,b)||this.isBefore(a,b)}function fc(a,b,c){var d,e,f,g;return this.isValid()?(d=Fb(a,this),d.isValid()?(e=6e4*(d.utcOffset()-this.utcOffset()),b=K(b),"year"===b||"month"===b||"quarter"===b?(g=gc(this,d),"quarter"===b?g/=3:"year"===b&&(g/=12)):(f=this-d,g="second"===b?f/1e3:"minute"===b?f/6e4:"hour"===b?f/36e5:"day"===b?(f-e)/864e5:"week"===b?(f-e)/6048e5:f),c?g:t(g)):NaN):NaN}function gc(a,b){var c,d,e=12*(b.year()-a.year())+(b.month()-a.month()),f=a.clone().add(e,"months");return b-f<0?(c=a.clone().add(e-1,"months"),d=(b-f)/(f-c)):(c=a.clone().add(e+1,"months"),d=(b-f)/(c-f)),-(e+d)||0}function hc(){return this.clone().locale("en").format("ddd MMM DD YYYY HH:mm:ss [GMT]ZZ")}function ic(){if(!this.isValid())return null;var a=this.clone().utc();return a.year()<0||a.year()>9999?X(a,"YYYYYY-MM-DD[T]HH:mm:ss.SSS[Z]"):z(Date.prototype.toISOString)?this.toDate().toISOString():X(a,"YYYY-MM-DD[T]HH:mm:ss.SSS[Z]")}function jc(){if(!this.isValid())return"moment.invalid(/* "+this._i+" */)";var a="moment",b="";this.isLocal()||(a=0===this.utcOffset()?"moment.utc":"moment.parseZone",b="Z");var c="["+a+'("]',d=0<=this.year()&&this.year()<=9999?"YYYY":"YYYYYY",e="-MM-DD[T]HH:mm:ss.SSS",f=b+'[")]';return this.format(c+d+e+f)}function kc(b){b||(b=this.isUtc()?a.defaultFormatUtc:a.defaultFormat);var c=X(this,b);return this.localeData().postformat(c)}function lc(a,b){return this.isValid()&&(s(a)&&a.isValid()||tb(a).isValid())?Sb({to:this,from:a}).locale(this.locale()).humanize(!b):this.localeData().invalidDate()}function mc(a){return this.from(tb(),a)}function nc(a,b){return this.isValid()&&(s(a)&&a.isValid()||tb(a).isValid())?Sb({from:this,to:a}).locale(this.locale()).humanize(!b):this.localeData().invalidDate()}function oc(a){return this.to(tb(),a)}function pc(a){var b;return void 0===a?this._locale._abbr:(b=bb(a),null!=b&&(this._locale=b),this)}function qc(){return this._locale}function rc(a){switch(a=K(a)){case"year":this.month(0);case"quarter":case"month":this.date(1);case"week":case"isoWeek":case"day":case"date":this.hours(0);case"hour":this.minutes(0);case"minute":this.seconds(0);case"second":this.milliseconds(0)}return"week"===a&&this.weekday(0),"isoWeek"===a&&this.isoWeekday(1),"quarter"===a&&this.month(3*Math.floor(this.month()/3)),this}function sc(a){return a=K(a),void 0===a||"millisecond"===a?this:("date"===a&&(a="day"),this.startOf(a).add(1,"isoWeek"===a?"week":a).subtract(1,"ms"))}function tc(){return this._d.valueOf()-6e4*(this._offset||0)}function uc(){return Math.floor(this.valueOf()/1e3)}function vc(){return new Date(this.valueOf())}function wc(){var a=this;return[a.year(),a.month(),a.date(),a.hour(),a.minute(),a.second(),a.millisecond()]}function xc(){var a=this;return{years:a.year(),months:a.month(),date:a.date(),hours:a.hours(),minutes:a.minutes(),seconds:a.seconds(),milliseconds:a.milliseconds()}}function yc(){return this.isValid()?this.toISOString():null}function zc(){return o(this)}function Ac(){
return k({},n(this))}function Bc(){return n(this).overflow}function Cc(){return{input:this._i,format:this._f,locale:this._locale,isUTC:this._isUTC,strict:this._strict}}function Dc(a,b){U(0,[a,a.length],0,b)}function Ec(a){return Ic.call(this,a,this.week(),this.weekday(),this.localeData()._week.dow,this.localeData()._week.doy)}function Fc(a){return Ic.call(this,a,this.isoWeek(),this.isoWeekday(),1,4)}function Gc(){return xa(this.year(),1,4)}function Hc(){var a=this.localeData()._week;return xa(this.year(),a.dow,a.doy)}function Ic(a,b,c,d,e){var f;return null==a?wa(this,d,e).year:(f=xa(a,d,e),b>f&&(b=f),Jc.call(this,a,b,c,d,e))}function Jc(a,b,c,d,e){var f=va(a,b,c,d,e),g=ta(f.year,0,f.dayOfYear);return this.year(g.getUTCFullYear()),this.month(g.getUTCMonth()),this.date(g.getUTCDate()),this}function Kc(a){return null==a?Math.ceil((this.month()+1)/3):this.month(3*(a-1)+this.month()%3)}function Lc(a){var b=Math.round((this.clone().startOf("day")-this.clone().startOf("year"))/864e5)+1;return null==a?b:this.add(a-b,"d")}function Mc(a,b){b[ke]=u(1e3*("0."+a))}function Nc(){return this._isUTC?"UTC":""}function Oc(){return this._isUTC?"Coordinated Universal Time":""}function Pc(a){return tb(1e3*a)}function Qc(){return tb.apply(null,arguments).parseZone()}function Rc(a){return a}function Sc(a,b,c,d){var e=bb(),f=l().set(d,b);return e[c](f,a)}function Tc(a,b,c){if(g(a)&&(b=a,a=void 0),a=a||"",null!=b)return Sc(a,b,c,"month");var d,e=[];for(d=0;d<12;d++)e[d]=Sc(a,d,c,"month");return e}function Uc(a,b,c,d){"boolean"==typeof a?(g(b)&&(c=b,b=void 0),b=b||""):(b=a,c=b,a=!1,g(b)&&(c=b,b=void 0),b=b||"");var e=bb(),f=a?e._week.dow:0;if(null!=c)return Sc(b,(c+f)%7,d,"day");var h,i=[];for(h=0;h<7;h++)i[h]=Sc(b,(h+f)%7,d,"day");return i}function Vc(a,b){return Tc(a,b,"months")}function Wc(a,b){return Tc(a,b,"monthsShort")}function Xc(a,b,c){return Uc(a,b,c,"weekdays")}function Yc(a,b,c){return Uc(a,b,c,"weekdaysShort")}function Zc(a,b,c){return Uc(a,b,c,"weekdaysMin")}function $c(){var a=this._data;return this._milliseconds=df(this._milliseconds),this._days=df(this._days),this._months=df(this._months),a.milliseconds=df(a.milliseconds),a.seconds=df(a.seconds),a.minutes=df(a.minutes),a.hours=df(a.hours),a.months=df(a.months),a.years=df(a.years),this}function _c(a,b,c,d){var e=Sb(b,c);return a._milliseconds+=d*e._milliseconds,a._days+=d*e._days,a._months+=d*e._months,a._bubble()}function ad(a,b){return _c(this,a,b,1)}function bd(a,b){return _c(this,a,b,-1)}function cd(a){return a<0?Math.floor(a):Math.ceil(a)}function dd(){var a,b,c,d,e,f=this._milliseconds,g=this._days,h=this._months,i=this._data;return f>=0&&g>=0&&h>=0||f<=0&&g<=0&&h<=0||(f+=864e5*cd(fd(h)+g),g=0,h=0),i.milliseconds=f%1e3,a=t(f/1e3),i.seconds=a%60,b=t(a/60),i.minutes=b%60,c=t(b/60),i.hours=c%24,g+=t(c/24),e=t(ed(g)),h+=e,g-=cd(fd(e)),d=t(h/12),h%=12,i.days=g,i.months=h,i.years=d,this}function ed(a){return 4800*a/146097}function fd(a){return 146097*a/4800}function gd(a){if(!this.isValid())return NaN;var b,c,d=this._milliseconds;if(a=K(a),"month"===a||"year"===a)return b=this._days+d/864e5,c=this._months+ed(b),"month"===a?c:c/12;switch(b=this._days+Math.round(fd(this._months)),a){case"week":return b/7+d/6048e5;case"day":return b+d/864e5;case"hour":return 24*b+d/36e5;case"minute":return 1440*b+d/6e4;case"second":return 86400*b+d/1e3;case"millisecond":return Math.floor(864e5*b)+d;default:throw new Error("Unknown unit "+a)}}function hd(){return this.isValid()?this._milliseconds+864e5*this._days+this._months%12*2592e6+31536e6*u(this._months/12):NaN}function id(a){return function(){return this.as(a)}}function jd(a){return a=K(a),this.isValid()?this[a+"s"]():NaN}function kd(a){return function(){return this.isValid()?this._data[a]:NaN}}function ld(){return t(this.days()/7)}function md(a,b,c,d,e){return e.relativeTime(b||1,!!c,a,d)}function nd(a,b,c){var d=Sb(a).abs(),e=uf(d.as("s")),f=uf(d.as("m")),g=uf(d.as("h")),h=uf(d.as("d")),i=uf(d.as("M")),j=uf(d.as("y")),k=e<=vf.ss&&["s",e]||e<vf.s&&["ss",e]||f<=1&&["m"]||f<vf.m&&["mm",f]||g<=1&&["h"]||g<vf.h&&["hh",g]||h<=1&&["d"]||h<vf.d&&["dd",h]||i<=1&&["M"]||i<vf.M&&["MM",i]||j<=1&&["y"]||["yy",j];return k[2]=b,k[3]=+a>0,k[4]=c,md.apply(null,k)}function od(a){return void 0===a?uf:"function"==typeof a&&(uf=a,!0)}function pd(a,b){return void 0!==vf[a]&&(void 0===b?vf[a]:(vf[a]=b,"s"===a&&(vf.ss=b-1),!0))}function qd(a){if(!this.isValid())return this.localeData().invalidDate();var b=this.localeData(),c=nd(this,!a,b);return a&&(c=b.pastFuture(+this,c)),b.postformat(c)}function rd(){if(!this.isValid())return this.localeData().invalidDate();var a,b,c,d=wf(this._milliseconds)/1e3,e=wf(this._days),f=wf(this._months);a=t(d/60),b=t(a/60),d%=60,a%=60,c=t(f/12),f%=12;var g=c,h=f,i=e,j=b,k=a,l=d,m=this.asSeconds();return m?(m<0?"-":"")+"P"+(g?g+"Y":"")+(h?h+"M":"")+(i?i+"D":"")+(j||k||l?"T":"")+(j?j+"H":"")+(k?k+"M":"")+(l?l+"S":""):"P0D"}var sd,td;td=Array.prototype.some?Array.prototype.some:function(a){for(var b=Object(this),c=b.length>>>0,d=0;d<c;d++)if(d in b&&a.call(this,b[d],d,b))return!0;return!1};var ud=td,vd=a.momentProperties=[],wd=!1,xd={};a.suppressDeprecationWarnings=!1,a.deprecationHandler=null;var yd;yd=Object.keys?Object.keys:function(a){var b,c=[];for(b in a)j(a,b)&&c.push(b);return c};var zd,Ad=yd,Bd={sameDay:"[Today at] LT",nextDay:"[Tomorrow at] LT",nextWeek:"dddd [at] LT",lastDay:"[Yesterday at] LT",lastWeek:"[Last] dddd [at] LT",sameElse:"L"},Cd={LTS:"h:mm:ss A",LT:"h:mm A",L:"MM/DD/YYYY",LL:"MMMM D, YYYY",LLL:"MMMM D, YYYY h:mm A",LLLL:"dddd, MMMM D, YYYY h:mm A"},Dd="Invalid date",Ed="%d",Fd=/\d{1,2}/,Gd={future:"in %s",past:"%s ago",s:"a few seconds",ss:"%d seconds",m:"a minute",mm:"%d minutes",h:"an hour",hh:"%d hours",d:"a day",dd:"%d days",M:"a month",MM:"%d months",y:"a year",yy:"%d years"},Hd={},Id={},Jd=/(\[[^\[]*\])|(\\)?([Hh]mm(ss)?|Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|Qo?|YYYYYY|YYYYY|YYYY|YY|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|kk?|mm?|ss?|S{1,9}|x|X|zz?|ZZ?|.)/g,Kd=/(\[[^\[]*\])|(\\)?(LTS|LT|LL?L?L?|l{1,4})/g,Ld={},Md={},Nd=/\d/,Od=/\d\d/,Pd=/\d{3}/,Qd=/\d{4}/,Rd=/[+-]?\d{6}/,Sd=/\d\d?/,Td=/\d\d\d\d?/,Ud=/\d\d\d\d\d\d?/,Vd=/\d{1,3}/,Wd=/\d{1,4}/,Xd=/[+-]?\d{1,6}/,Yd=/\d+/,Zd=/[+-]?\d+/,$d=/Z|[+-]\d\d:?\d\d/gi,_d=/Z|[+-]\d\d(?::?\d\d)?/gi,ae=/[+-]?\d+(\.\d{1,3})?/,be=/[0-9]*['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+|[\u0600-\u06FF\/]+(\s*?[\u0600-\u06FF]+){1,2}/i,ce={},de={},ee=0,fe=1,ge=2,he=3,ie=4,je=5,ke=6,le=7,me=8;zd=Array.prototype.indexOf?Array.prototype.indexOf:function(a){var b;for(b=0;b<this.length;++b)if(this[b]===a)return b;return-1};var ne=zd;U("M",["MM",2],"Mo",function(){return this.month()+1}),U("MMM",0,0,function(a){return this.localeData().monthsShort(this,a)}),U("MMMM",0,0,function(a){return this.localeData().months(this,a)}),J("month","M"),M("month",8),Z("M",Sd),Z("MM",Sd,Od),Z("MMM",function(a,b){return b.monthsShortRegex(a)}),Z("MMMM",function(a,b){return b.monthsRegex(a)}),ba(["M","MM"],function(a,b){b[fe]=u(a)-1}),ba(["MMM","MMMM"],function(a,b,c,d){var e=c._locale.monthsParse(a,d,c._strict);null!=e?b[fe]=e:n(c).invalidMonth=a});var oe=/D[oD]?(\[[^\[\]]*\]|\s)+MMMM?/,pe="January_February_March_April_May_June_July_August_September_October_November_December".split("_"),qe="Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec".split("_"),re=be,se=be;U("Y",0,0,function(){var a=this.year();return a<=9999?""+a:"+"+a}),U(0,["YY",2],0,function(){return this.year()%100}),U(0,["YYYY",4],0,"year"),U(0,["YYYYY",5],0,"year"),U(0,["YYYYYY",6,!0],0,"year"),J("year","y"),M("year",1),Z("Y",Zd),Z("YY",Sd,Od),Z("YYYY",Wd,Qd),Z("YYYYY",Xd,Rd),Z("YYYYYY",Xd,Rd),ba(["YYYYY","YYYYYY"],ee),ba("YYYY",function(b,c){c[ee]=2===b.length?a.parseTwoDigitYear(b):u(b)}),ba("YY",function(b,c){c[ee]=a.parseTwoDigitYear(b)}),ba("Y",function(a,b){b[ee]=parseInt(a,10)}),a.parseTwoDigitYear=function(a){return u(a)+(u(a)>68?1900:2e3)};var te=O("FullYear",!0);U("w",["ww",2],"wo","week"),U("W",["WW",2],"Wo","isoWeek"),J("week","w"),J("isoWeek","W"),M("week",5),M("isoWeek",5),Z("w",Sd),Z("ww",Sd,Od),Z("W",Sd),Z("WW",Sd,Od),ca(["w","ww","W","WW"],function(a,b,c,d){b[d.substr(0,1)]=u(a)});var ue={dow:0,doy:6};U("d",0,"do","day"),U("dd",0,0,function(a){return this.localeData().weekdaysMin(this,a)}),U("ddd",0,0,function(a){return this.localeData().weekdaysShort(this,a)}),U("dddd",0,0,function(a){return this.localeData().weekdays(this,a)}),U("e",0,0,"weekday"),U("E",0,0,"isoWeekday"),J("day","d"),J("weekday","e"),J("isoWeekday","E"),M("day",11),M("weekday",11),M("isoWeekday",11),Z("d",Sd),Z("e",Sd),Z("E",Sd),Z("dd",function(a,b){return b.weekdaysMinRegex(a)}),Z("ddd",function(a,b){return b.weekdaysShortRegex(a)}),Z("dddd",function(a,b){return b.weekdaysRegex(a)}),ca(["dd","ddd","dddd"],function(a,b,c,d){var e=c._locale.weekdaysParse(a,d,c._strict);null!=e?b.d=e:n(c).invalidWeekday=a}),ca(["d","e","E"],function(a,b,c,d){b[d]=u(a)});var ve="Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday".split("_"),we="Sun_Mon_Tue_Wed_Thu_Fri_Sat".split("_"),xe="Su_Mo_Tu_We_Th_Fr_Sa".split("_"),ye=be,ze=be,Ae=be;U("H",["HH",2],0,"hour"),U("h",["hh",2],0,Ra),U("k",["kk",2],0,Sa),U("hmm",0,0,function(){return""+Ra.apply(this)+T(this.minutes(),2)}),U("hmmss",0,0,function(){return""+Ra.apply(this)+T(this.minutes(),2)+T(this.seconds(),2)}),U("Hmm",0,0,function(){return""+this.hours()+T(this.minutes(),2)}),U("Hmmss",0,0,function(){return""+this.hours()+T(this.minutes(),2)+T(this.seconds(),2)}),Ta("a",!0),Ta("A",!1),J("hour","h"),M("hour",13),Z("a",Ua),Z("A",Ua),Z("H",Sd),Z("h",Sd),Z("k",Sd),Z("HH",Sd,Od),Z("hh",Sd,Od),Z("kk",Sd,Od),Z("hmm",Td),Z("hmmss",Ud),Z("Hmm",Td),Z("Hmmss",Ud),ba(["H","HH"],he),ba(["k","kk"],function(a,b,c){var d=u(a);b[he]=24===d?0:d}),ba(["a","A"],function(a,b,c){c._isPm=c._locale.isPM(a),c._meridiem=a}),ba(["h","hh"],function(a,b,c){b[he]=u(a),n(c).bigHour=!0}),ba("hmm",function(a,b,c){var d=a.length-2;b[he]=u(a.substr(0,d)),b[ie]=u(a.substr(d)),n(c).bigHour=!0}),ba("hmmss",function(a,b,c){var d=a.length-4,e=a.length-2;b[he]=u(a.substr(0,d)),b[ie]=u(a.substr(d,2)),b[je]=u(a.substr(e)),n(c).bigHour=!0}),ba("Hmm",function(a,b,c){var d=a.length-2;b[he]=u(a.substr(0,d)),b[ie]=u(a.substr(d))}),ba("Hmmss",function(a,b,c){var d=a.length-4,e=a.length-2;b[he]=u(a.substr(0,d)),b[ie]=u(a.substr(d,2)),b[je]=u(a.substr(e))});var Be,Ce=/[ap]\.?m?\.?/i,De=O("Hours",!0),Ee={calendar:Bd,longDateFormat:Cd,invalidDate:Dd,ordinal:Ed,dayOfMonthOrdinalParse:Fd,relativeTime:Gd,months:pe,monthsShort:qe,week:ue,weekdays:ve,weekdaysMin:xe,weekdaysShort:we,meridiemParse:Ce},Fe={},Ge={},He=/^\s*((?:[+-]\d{6}|\d{4})-(?:\d\d-\d\d|W\d\d-\d|W\d\d|\d\d\d|\d\d))(?:(T| )(\d\d(?::\d\d(?::\d\d(?:[.,]\d+)?)?)?)([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/,Ie=/^\s*((?:[+-]\d{6}|\d{4})(?:\d\d\d\d|W\d\d\d|W\d\d|\d\d\d|\d\d))(?:(T| )(\d\d(?:\d\d(?:\d\d(?:[.,]\d+)?)?)?)([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/,Je=/Z|[+-]\d\d(?::?\d\d)?/,Ke=[["YYYYYY-MM-DD",/[+-]\d{6}-\d\d-\d\d/],["YYYY-MM-DD",/\d{4}-\d\d-\d\d/],["GGGG-[W]WW-E",/\d{4}-W\d\d-\d/],["GGGG-[W]WW",/\d{4}-W\d\d/,!1],["YYYY-DDD",/\d{4}-\d{3}/],["YYYY-MM",/\d{4}-\d\d/,!1],["YYYYYYMMDD",/[+-]\d{10}/],["YYYYMMDD",/\d{8}/],["GGGG[W]WWE",/\d{4}W\d{3}/],["GGGG[W]WW",/\d{4}W\d{2}/,!1],["YYYYDDD",/\d{7}/]],Le=[["HH:mm:ss.SSSS",/\d\d:\d\d:\d\d\.\d+/],["HH:mm:ss,SSSS",/\d\d:\d\d:\d\d,\d+/],["HH:mm:ss",/\d\d:\d\d:\d\d/],["HH:mm",/\d\d:\d\d/],["HHmmss.SSSS",/\d\d\d\d\d\d\.\d+/],["HHmmss,SSSS",/\d\d\d\d\d\d,\d+/],["HHmmss",/\d\d\d\d\d\d/],["HHmm",/\d\d\d\d/],["HH",/\d\d/]],Me=/^\/?Date\((\-?\d+)/i,Ne=/^((?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),?\s)?(\d?\d\s(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s(?:\d\d)?\d\d\s)(\d\d:\d\d)(\:\d\d)?(\s(?:UT|GMT|[ECMP][SD]T|[A-IK-Za-ik-z]|[+-]\d{4}))$/;a.createFromInputFallback=x("value provided is not in a recognized RFC2822 or ISO format. moment construction falls back to js Date(), which is not reliable across all browsers and versions. Non RFC2822/ISO date formats are discouraged and will be removed in an upcoming major release. Please refer to http://momentjs.com/guides/#/warnings/js-date/ for more info.",function(a){a._d=new Date(a._i+(a._useUTC?" UTC":""))}),a.ISO_8601=function(){},a.RFC_2822=function(){};var Oe=x("moment().min is deprecated, use moment.max instead. http://momentjs.com/guides/#/warnings/min-max/",function(){var a=tb.apply(null,arguments);return this.isValid()&&a.isValid()?a<this?this:a:p()}),Pe=x("moment().max is deprecated, use moment.min instead. http://momentjs.com/guides/#/warnings/min-max/",function(){var a=tb.apply(null,arguments);return this.isValid()&&a.isValid()?a>this?this:a:p()}),Qe=function(){return Date.now?Date.now():+new Date},Re=["year","quarter","month","week","day","hour","minute","second","millisecond"];Db("Z",":"),Db("ZZ",""),Z("Z",_d),Z("ZZ",_d),ba(["Z","ZZ"],function(a,b,c){c._useUTC=!0,c._tzm=Eb(_d,a)});var Se=/([\+\-]|\d\d)/gi;a.updateOffset=function(){};var Te=/^(\-)?(?:(\d*)[. ])?(\d+)\:(\d+)(?:\:(\d+)(\.\d*)?)?$/,Ue=/^(-)?P(?:(-?[0-9,.]*)Y)?(?:(-?[0-9,.]*)M)?(?:(-?[0-9,.]*)W)?(?:(-?[0-9,.]*)D)?(?:T(?:(-?[0-9,.]*)H)?(?:(-?[0-9,.]*)M)?(?:(-?[0-9,.]*)S)?)?$/;Sb.fn=Ab.prototype,Sb.invalid=zb;var Ve=Wb(1,"add"),We=Wb(-1,"subtract");a.defaultFormat="YYYY-MM-DDTHH:mm:ssZ",a.defaultFormatUtc="YYYY-MM-DDTHH:mm:ss[Z]";var Xe=x("moment().lang() is deprecated. Instead, use moment().localeData() to get the language configuration. Use moment().locale() to change languages.",function(a){return void 0===a?this.localeData():this.locale(a)});U(0,["gg",2],0,function(){return this.weekYear()%100}),U(0,["GG",2],0,function(){return this.isoWeekYear()%100}),Dc("gggg","weekYear"),Dc("ggggg","weekYear"),Dc("GGGG","isoWeekYear"),Dc("GGGGG","isoWeekYear"),J("weekYear","gg"),J("isoWeekYear","GG"),M("weekYear",1),M("isoWeekYear",1),Z("G",Zd),Z("g",Zd),Z("GG",Sd,Od),Z("gg",Sd,Od),Z("GGGG",Wd,Qd),Z("gggg",Wd,Qd),Z("GGGGG",Xd,Rd),Z("ggggg",Xd,Rd),ca(["gggg","ggggg","GGGG","GGGGG"],function(a,b,c,d){b[d.substr(0,2)]=u(a)}),ca(["gg","GG"],function(b,c,d,e){c[e]=a.parseTwoDigitYear(b)}),U("Q",0,"Qo","quarter"),J("quarter","Q"),M("quarter",7),Z("Q",Nd),ba("Q",function(a,b){b[fe]=3*(u(a)-1)}),U("D",["DD",2],"Do","date"),J("date","D"),M("date",9),Z("D",Sd),Z("DD",Sd,Od),Z("Do",function(a,b){return a?b._dayOfMonthOrdinalParse||b._ordinalParse:b._dayOfMonthOrdinalParseLenient}),ba(["D","DD"],ge),ba("Do",function(a,b){b[ge]=u(a.match(Sd)[0],10)});var Ye=O("Date",!0);U("DDD",["DDDD",3],"DDDo","dayOfYear"),J("dayOfYear","DDD"),M("dayOfYear",4),Z("DDD",Vd),Z("DDDD",Pd),ba(["DDD","DDDD"],function(a,b,c){c._dayOfYear=u(a)}),U("m",["mm",2],0,"minute"),J("minute","m"),M("minute",14),Z("m",Sd),Z("mm",Sd,Od),ba(["m","mm"],ie);var Ze=O("Minutes",!1);U("s",["ss",2],0,"second"),J("second","s"),M("second",15),Z("s",Sd),Z("ss",Sd,Od),ba(["s","ss"],je);var $e=O("Seconds",!1);U("S",0,0,function(){return~~(this.millisecond()/100)}),U(0,["SS",2],0,function(){return~~(this.millisecond()/10)}),U(0,["SSS",3],0,"millisecond"),U(0,["SSSS",4],0,function(){return 10*this.millisecond()}),U(0,["SSSSS",5],0,function(){return 100*this.millisecond()}),U(0,["SSSSSS",6],0,function(){return 1e3*this.millisecond()}),U(0,["SSSSSSS",7],0,function(){return 1e4*this.millisecond()}),U(0,["SSSSSSSS",8],0,function(){return 1e5*this.millisecond()}),U(0,["SSSSSSSSS",9],0,function(){return 1e6*this.millisecond()}),J("millisecond","ms"),M("millisecond",16),Z("S",Vd,Nd),Z("SS",Vd,Od),Z("SSS",Vd,Pd);var _e;for(_e="SSSS";_e.length<=9;_e+="S")Z(_e,Yd);for(_e="S";_e.length<=9;_e+="S")ba(_e,Mc);var af=O("Milliseconds",!1);U("z",0,0,"zoneAbbr"),U("zz",0,0,"zoneName");var bf=r.prototype;bf.add=Ve,bf.calendar=Zb,bf.clone=$b,bf.diff=fc,bf.endOf=sc,bf.format=kc,bf.from=lc,bf.fromNow=mc,bf.to=nc,bf.toNow=oc,bf.get=R,bf.invalidAt=Bc,bf.isAfter=_b,bf.isBefore=ac,bf.isBetween=bc,bf.isSame=cc,bf.isSameOrAfter=dc,bf.isSameOrBefore=ec,bf.isValid=zc,bf.lang=Xe,bf.locale=pc,bf.localeData=qc,bf.max=Pe,bf.min=Oe,bf.parsingFlags=Ac,bf.set=S,bf.startOf=rc,bf.subtract=We,bf.toArray=wc,bf.toObject=xc,bf.toDate=vc,bf.toISOString=ic,bf.inspect=jc,bf.toJSON=yc,bf.toString=hc,bf.unix=uc,bf.valueOf=tc,bf.creationData=Cc,bf.year=te,bf.isLeapYear=ra,bf.weekYear=Ec,bf.isoWeekYear=Fc,bf.quarter=bf.quarters=Kc,bf.month=ka,bf.daysInMonth=la,bf.week=bf.weeks=Ba,bf.isoWeek=bf.isoWeeks=Ca,bf.weeksInYear=Hc,bf.isoWeeksInYear=Gc,bf.date=Ye,bf.day=bf.days=Ka,bf.weekday=La,bf.isoWeekday=Ma,bf.dayOfYear=Lc,bf.hour=bf.hours=De,bf.minute=bf.minutes=Ze,bf.second=bf.seconds=$e,bf.millisecond=bf.milliseconds=af,bf.utcOffset=Hb,bf.utc=Jb,bf.local=Kb,bf.parseZone=Lb,bf.hasAlignedHourOffset=Mb,bf.isDST=Nb,bf.isLocal=Pb,bf.isUtcOffset=Qb,bf.isUtc=Rb,bf.isUTC=Rb,bf.zoneAbbr=Nc,bf.zoneName=Oc,bf.dates=x("dates accessor is deprecated. Use date instead.",Ye),bf.months=x("months accessor is deprecated. Use month instead",ka),bf.years=x("years accessor is deprecated. Use year instead",te),bf.zone=x("moment().zone is deprecated, use moment().utcOffset instead. http://momentjs.com/guides/#/warnings/zone/",Ib),bf.isDSTShifted=x("isDSTShifted is deprecated. See http://momentjs.com/guides/#/warnings/dst-shifted/ for more information",Ob);var cf=C.prototype;cf.calendar=D,cf.longDateFormat=E,cf.invalidDate=F,cf.ordinal=G,cf.preparse=Rc,cf.postformat=Rc,cf.relativeTime=H,cf.pastFuture=I,cf.set=A,cf.months=fa,cf.monthsShort=ga,cf.monthsParse=ia,cf.monthsRegex=na,cf.monthsShortRegex=ma,cf.week=ya,cf.firstDayOfYear=Aa,cf.firstDayOfWeek=za,cf.weekdays=Fa,cf.weekdaysMin=Ha,cf.weekdaysShort=Ga,cf.weekdaysParse=Ja,cf.weekdaysRegex=Na,cf.weekdaysShortRegex=Oa,cf.weekdaysMinRegex=Pa,cf.isPM=Va,cf.meridiem=Wa,$a("en",{dayOfMonthOrdinalParse:/\d{1,2}(th|st|nd|rd)/,ordinal:function(a){var b=a%10,c=1===u(a%100/10)?"th":1===b?"st":2===b?"nd":3===b?"rd":"th";return a+c}}),a.lang=x("moment.lang is deprecated. Use moment.locale instead.",$a),a.langData=x("moment.langData is deprecated. Use moment.localeData instead.",bb);var df=Math.abs,ef=id("ms"),ff=id("s"),gf=id("m"),hf=id("h"),jf=id("d"),kf=id("w"),lf=id("M"),mf=id("y"),nf=kd("milliseconds"),of=kd("seconds"),pf=kd("minutes"),qf=kd("hours"),rf=kd("days"),sf=kd("months"),tf=kd("years"),uf=Math.round,vf={ss:44,s:45,m:45,h:22,d:26,M:11},wf=Math.abs,xf=Ab.prototype;return xf.isValid=yb,xf.abs=$c,xf.add=ad,xf.subtract=bd,xf.as=gd,xf.asMilliseconds=ef,xf.asSeconds=ff,xf.asMinutes=gf,xf.asHours=hf,xf.asDays=jf,xf.asWeeks=kf,xf.asMonths=lf,xf.asYears=mf,xf.valueOf=hd,xf._bubble=dd,xf.get=jd,xf.milliseconds=nf,xf.seconds=of,xf.minutes=pf,xf.hours=qf,xf.days=rf,xf.weeks=ld,xf.months=sf,xf.years=tf,xf.humanize=qd,xf.toISOString=rd,xf.toString=rd,xf.toJSON=rd,xf.locale=pc,xf.localeData=qc,xf.toIsoString=x("toIsoString() is deprecated. Please use toISOString() instead (notice the capitals)",rd),xf.lang=Xe,U("X",0,0,"unix"),U("x",0,0,"valueOf"),Z("x",Zd),Z("X",ae),ba("X",function(a,b,c){c._d=new Date(1e3*parseFloat(a,10))}),ba("x",function(a,b,c){c._d=new Date(u(a))}),a.version="2.18.1",b(tb),a.fn=bf,a.min=vb,a.max=wb,a.now=Qe,a.utc=l,a.unix=Pc,a.months=Vc,a.isDate=h,a.locale=$a,a.invalid=p,a.duration=Sb,a.isMoment=s,a.weekdays=Xc,a.parseZone=Qc,a.localeData=bb,a.isDuration=Bb,a.monthsShort=Wc,a.weekdaysMin=Zc,a.defineLocale=_a,a.updateLocale=ab,a.locales=cb,a.weekdaysShort=Yc,a.normalizeUnits=K,a.relativeTimeRounding=od,a.relativeTimeThreshold=pd,a.calendarFormat=Yb,a.prototype=bf,a});
/**
 * Bootstrap Multiselect (https://github.com/davidstutz/bootstrap-multiselect)
 *
 * Apache License, Version 2.0:
 * Copyright (c) 2012 - 2015 David Stutz
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a
 * copy of the License at http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * BSD 3-Clause License:
 * Copyright (c) 2012 - 2015 David Stutz
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    - Redistributions of source code must retain the above copyright notice,
 *      this list of conditions and the following disclaimer.
 *    - Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    - Neither the name of David Stutz nor the names of its contributors may be
 *      used to endorse or promote products derived from this software without
 *      specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
!function ($) {
    "use strict";// jshint ;_;

    if (typeof ko !== 'undefined' && ko.bindingHandlers && !ko.bindingHandlers.multiselect) {
        ko.bindingHandlers.multiselect = {
            after: ['options', 'value', 'selectedOptions', 'enable', 'disable'],

            init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
                var $element = $(element);
                var config = ko.toJS(valueAccessor());

                $element.multiselect(config);

                if (allBindings.has('options')) {
                    var options = allBindings.get('options');
                    if (ko.isObservable(options)) {
                        ko.computed({
                            read: function() {
                                options();
                                setTimeout(function() {
                                    var ms = $element.data('multiselect');
                                    if (ms)
                                        ms.updateOriginalOptions();//Not sure how beneficial this is.
                                    $element.multiselect('rebuild');
                                }, 1);
                            },
                            disposeWhenNodeIsRemoved: element
                        });
                    }
                }

                //value and selectedOptions are two-way, so these will be triggered even by our own actions.
                //It needs some way to tell if they are triggered because of us or because of outside change.
                //It doesn't loop but it's a waste of processing.
                if (allBindings.has('value')) {
                    var value = allBindings.get('value');
                    if (ko.isObservable(value)) {
                        ko.computed({
                            read: function() {
                                value();
                                setTimeout(function() {
                                    $element.multiselect('refresh');
                                }, 1);
                            },
                            disposeWhenNodeIsRemoved: element
                        }).extend({ rateLimit: 100, notifyWhenChangesStop: true });
                    }
                }

                //Switched from arrayChange subscription to general subscription using 'refresh'.
                //Not sure performance is any better using 'select' and 'deselect'.
                if (allBindings.has('selectedOptions')) {
                    var selectedOptions = allBindings.get('selectedOptions');
                    if (ko.isObservable(selectedOptions)) {
                        ko.computed({
                            read: function() {
                                selectedOptions();
                                setTimeout(function() {
                                    $element.multiselect('refresh');
                                }, 1);
                            },
                            disposeWhenNodeIsRemoved: element
                        }).extend({ rateLimit: 100, notifyWhenChangesStop: true });
                    }
                }

                var setEnabled = function (enable) {
                    setTimeout(function () {
                        if (enable)
                            $element.multiselect('enable');
                        else
                            $element.multiselect('disable');
                    });
                };

                if (allBindings.has('enable')) {
                    var enable = allBindings.get('enable');
                    if (ko.isObservable(enable)) {
                        ko.computed({
                            read: function () {
                                setEnabled(enable());
                            },
                            disposeWhenNodeIsRemoved: element
                        }).extend({ rateLimit: 100, notifyWhenChangesStop: true });
                    } else {
                        setEnabled(enable);
                    }
                }

                if (allBindings.has('disable')) {
                    var disable = allBindings.get('disable');
                    if (ko.isObservable(disable)) {
                        ko.computed({
                            read: function () {
                                setEnabled(!disable());
                            },
                            disposeWhenNodeIsRemoved: element
                        }).extend({ rateLimit: 100, notifyWhenChangesStop: true });
                    } else {
                        setEnabled(!disable);
                    }
                }

                ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
                    $element.multiselect('destroy');
                });
            },

            update: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
                var $element = $(element);
                var config = ko.toJS(valueAccessor());

                $element.multiselect('setOptions', config);
                $element.multiselect('rebuild');
            }
        };
    }

    function forEach(array, callback) {
        for (var index = 0; index < array.length; ++index) {
            callback(array[index], index);
        }
    }

    /**
     * Constructor to create a new multiselect using the given select.
     *
     * @param {jQuery} select
     * @param {Object} options
     * @returns {Multiselect}
     */
    function Multiselect(select, options) {

        this.$select = $(select);

        // Placeholder via data attributes
        if (this.$select.attr("data-placeholder")) {
            options.nonSelectedText = this.$select.data("placeholder");
        }

        this.options = this.mergeOptions($.extend({}, options, this.$select.data()));

        // Initialization.
        // We have to clone to create a new reference.
        this.originalOptions = this.$select.clone()[0].options;
        this.query = '';
        this.searchTimeout = null;
        this.lastToggledInput = null;

        this.options.multiple = this.$select.attr('multiple') === "multiple";
        this.options.onChange = $.proxy(this.options.onChange, this);
        this.options.onSelectAll = $.proxy(this.options.onSelectAll, this);
        this.options.onDeselectAll = $.proxy(this.options.onDeselectAll, this);
        this.options.onDropdownShow = $.proxy(this.options.onDropdownShow, this);
        this.options.onDropdownHide = $.proxy(this.options.onDropdownHide, this);
        this.options.onDropdownShown = $.proxy(this.options.onDropdownShown, this);
        this.options.onDropdownHidden = $.proxy(this.options.onDropdownHidden, this);
        this.options.onInitialized = $.proxy(this.options.onInitialized, this);
        this.options.onFiltering = $.proxy(this.options.onFiltering, this);

        // Build select all if enabled.
        this.buildContainer();
        this.buildButton();
        this.buildDropdown();
        this.buildSelectAll();
        this.buildDropdownOptions();
        this.buildFilter();

        this.updateButtonText();
        this.updateSelectAll(true);

        if (this.options.enableClickableOptGroups && this.options.multiple) {
            this.updateOptGroups();
        }

        if (this.options.disableIfEmpty && $('option', this.$select).length <= 0) {
            this.disable();
        }

        this.$select.wrap('<span class="hide-native-select" id="native_'+this.$select.attr("class")+'">').after(this.$container);
        this.options.onInitialized(this.$select, this.$container);
    }

    Multiselect.prototype = {

        defaults: {
            /**
             * Default text function will either print 'None selected' in case no
             * option is selected or a list of the selected options up to a length
             * of 3 selected options.
             *
             * @param {jQuery} options
             * @param {jQuery} select
             * @returns {String}
             */
            buttonText: function(options, select) {
                if (this.disabledText.length > 0
                        && (select.prop('disabled') || (options.length == 0 && this.disableIfEmpty)))  {

                    return this.disabledText;
                }
                else if (options.length === 0) {
                    return this.nonSelectedText;
                }
                else if (this.allSelectedText
                        && options.length === $('option', $(select)).length
                        && $('option', $(select)).length !== 1
                        && this.multiple) {

                    if (this.selectAllNumber) {
                        return this.allSelectedText;
                    }
                    else {
                        return this.allSelectedText;
                    }
                }
                else if (options.length > this.numberDisplayed) {
                    return options.length + ' ' + this.nSelectedText;
                }
                else {
                    var selected = '';
                    var delimiter = this.delimiterText;

                    options.each(function() {
                        var label = ($(this).attr('label') !== undefined) ? $(this).attr('label') : $(this).text();
                        selected += label + delimiter;
                    });

                    return selected.substr(0, selected.length - this.delimiterText.length);
                }
            },
            /**
             * Updates the title of the button similar to the buttonText function.
             *
             * @param {jQuery} options
             * @param {jQuery} select
             * @returns {@exp;selected@call;substr}
             */
            buttonTitle: function(options, select) {
                if (options.length === 0) {
                    return this.nonSelectedText;
                }
                else {
                    var selected = '';
                    var delimiter = this.delimiterText;

                    options.each(function () {
                        var label = ($(this).attr('label') !== undefined) ? $(this).attr('label') : $(this).text();
                        selected += label + delimiter;
                    });
                    return selected.substr(0, selected.length - this.delimiterText.length);
                }
            },
            checkboxName: function(option) {
                return false; // no checkbox name
            },
            /**
             * Create a label.
             *
             * @param {jQuery} element
             * @returns {String}
             */
            optionLabel: function(element){
                return $(element).attr('label') || $(element).text();
            },
            /**
             * Create a label.
             *
             * @param {jQuery} element
             * @returns {String}
             */
            injectElement: function(element) {
            },
            /**
             * Create a class.
             *
             * @param {jQuery} element
             * @returns {String}
             */
            optionClass: function(element) {
                return $(element).attr('class') || '';
            },
            /**
             * Triggered on change of the multiselect.
             *
             * Not triggered when selecting/deselecting options manually.
             *
             * @param {jQuery} option
             * @param {Boolean} checked
             */
            onChange : function(option, checked) {

            },
            /**
             * Triggered when the dropdown is shown.
             *
             * @param {jQuery} event
             */
            onDropdownShow: function(event) {

            },
            /**
             * Triggered when the dropdown is hidden.
             *
             * @param {jQuery} event
             */
            onDropdownHide: function(event) {

            },
            /**
             * Triggered after the dropdown is shown.
             *
             * @param {jQuery} event
             */
            onDropdownShown: function(event) {

            },
            /**
             * Triggered after the dropdown is hidden.
             *
             * @param {jQuery} event
             */
            onDropdownHidden: function(event) {

            },
            /**
             * Triggered on select all.
             */
            onSelectAll: function() {

            },
            /**
             * Triggered on deselect all.
             */
            onDeselectAll: function() {

            },
            /**
             * Triggered after initializing.
             *
             * @param {jQuery} $select
             * @param {jQuery} $container
             */
            onInitialized: function($select, $container) {

            },
            /**
             * Triggered on filtering.
             *
             * @param {jQuery} $filter
             */
            onFiltering: function($filter) {

            },
            enableHTML: false,
            buttonClass: 'btn btn-default',
            inheritClass: false,
            buttonWidth: 'auto',
            buttonContainer: '<div class="btn-group" />',
            dropRight: false,
            dropUp: false,
            selectedClass: 'active',
            // Maximum height of the dropdown menu.
            // If maximum height is exceeded a scrollbar will be displayed.
            maxHeight: false,
            includeSelectAllOption: false,
            includeSelectAllIfMoreThan: 0,
            selectAllText: 'Everyone',
            selectAllValue: 'multiselect-all',
            selectAllName: false,
            selectAllNumber: true,
            selectAllJustVisible: true,
            enableFiltering: false,
            enableCaseInsensitiveFiltering: false,
            enableFullValueFiltering: false,
            enableClickableOptGroups: false,
            enableCollapsibleOptGroups: false,
            filterPlaceholder: 'Search',
            // possible options: 'text', 'value', 'both'
            filterBehavior: 'text',
            includeFilterClearBtn: true,
            preventInputChangeEvent: false,
            nonSelectedText: 'None selected',
            nSelectedText: 'Person',
            allSelectedText: 'Everyone',
            numberDisplayed: -1,
            disableIfEmpty: false,
            disabledText: '',
            delimiterText: ', ',
            templates: {
                button: '<button type="button" class="multiselect dropdown-toggle" data-toggle="dropdown"><span class="multiselect-selected-text"></span> <b class="caret"></b></button>',
                ul: '<ul class="multiselect-container dropdown-menu"></ul>',
                /*filter: '<li class="multiselect-item multiselect-filter"><div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span><input class="form-control multiselect-search" type="text"></div></li>',
               */ filterClearBtn: '<span class="input-group-btn"><button class="btn btn-default multiselect-clear-filter" type="button"><i class="glyphicon glyphicon-remove-circle"></i></button></span>',
                li: '<li><a tabindex="0"><label></label></a></li>',
                divider: '<li class="multiselect-item divider"></li>',
                liGroup: '<li class="multiselect-item multiselect-group"><label></label></li>'
            }
        },

        constructor: Multiselect,

        /**
         * Builds the container of the multiselect.
         */
        buildContainer: function() {
            this.$container = $(this.options.buttonContainer);
            this.$container.on('show.bs.dropdown', this.options.onDropdownShow);
            this.$container.on('hide.bs.dropdown', this.options.onDropdownHide);
            this.$container.on('shown.bs.dropdown', this.options.onDropdownShown);
            this.$container.on('hidden.bs.dropdown', this.options.onDropdownHidden);
        },

        /**
         * Builds the button of the multiselect.
         */
        buildButton: function() {
            this.$button = $(this.options.templates.button).addClass(this.options.buttonClass);
            if (this.$select.attr('class') && this.options.inheritClass) {
                this.$button.addClass(this.$select.attr('class'));
            }
            // Adopt active state.
            if (this.$select.prop('disabled')) {
                this.disable();
            }
            else {
                this.enable();
            }

            // Manually add button width if set.
            if (this.options.buttonWidth && this.options.buttonWidth !== 'auto') {
                this.$button.css({
                    'width' : '100%', //this.options.buttonWidth,
                    'overflow' : 'hidden',
                    'text-overflow' : 'ellipsis'
                });
                this.$container.css({
                    'width': this.options.buttonWidth
                });
            }

            // Keep the tab index from the select.
            var tabindex = this.$select.attr('tabindex');
            if (tabindex) {
                this.$button.attr('tabindex', tabindex);
            }

            this.$container.prepend(this.$button);
        },

        /**
         * Builds the ul representing the dropdown menu.
         */
        buildDropdown: function() {

            // Build ul.
            this.$ul = $(this.options.templates.ul);

            if (this.options.dropRight) {
                this.$ul.addClass('pull-right');
            }

            // Set max height of dropdown menu to activate auto scrollbar.
            if (this.options.maxHeight) {
                // TODO: Add a class for this option to move the css declarations.
                this.$ul.css({
                    'max-height': this.options.maxHeight + 'px',
                    'overflow-y': 'auto',
                    'overflow-x': 'hidden'
                });
            }

            if (this.options.dropUp) {

                var height = Math.min(this.options.maxHeight, $('option[data-role!="divider"]', this.$select).length*26 + $('option[data-role="divider"]', this.$select).length*19 + (this.options.includeSelectAllOption ? 26 : 0) + (this.options.enableFiltering || this.options.enableCaseInsensitiveFiltering ? 44 : 0));
                var moveCalc = height + 34;

                this.$ul.css({
                    'max-height': height + 'px',
                    'overflow-y': 'auto',
                    'overflow-x': 'hidden',
                    'margin-top': "-" + moveCalc + 'px'
                });
            }

            this.$container.append(this.$ul);
        },

        /**
         * Build the dropdown options and binds all necessary events.
         *
         * Uses createDivider and createOptionValue to create the necessary options.
         */
        buildDropdownOptions: function() {

            this.$select.children().each($.proxy(function(index, element) {

                var $element = $(element);
                // Support optgroups and options without a group simultaneously.
                var tag = $element.prop('tagName')
                    .toLowerCase();

                if ($element.prop('value') === this.options.selectAllValue) {
                    return;
                }

                if (tag === 'optgroup') {
                    this.createOptgroup(element);
                }
                else if (tag === 'option') {

                    if ($element.data('role') === 'divider') {
                        this.createDivider();
                    }
                    else {
                        this.createOptionValue(element);
                    }

                }

                // Other illegal tags will be ignored.
            }, this));

            // Bind the change event on the dropdown elements.
            $('li:not(.multiselect-group) input', this.$ul).on('change', $.proxy(function(event) {
                var $target = $(event.target);

                var checked = $target.prop('checked') || false;
                       if(checked)
               {
                   
                   var all_vals_of_two = [];
                    $( ".visibility_drop li a label input:checked" ).each(function( index ) 
                    {
                        all_vals_of_two.push($( this ).val());                    
                    });
                    
                     var all_string = all_vals_of_two.toString();
                    // console.log(all_string.indexOf('multiselect-all'));
                    if(all_string.indexOf('multiselect-all')>-1)
                          {
                                  all_vals_of_two =[];
                                  $( ".visibility_drop li a label input" ).each(function( index ) 
                                {
                                    all_vals_of_two.push($( this ).val());                    
                                });
                          }
                    
                    $('#alert_drop').val(all_vals_of_two);
                    $('#alert_drop').multiselect('refresh');
                }
                else
                {
                    var all_vals_of_two_2= [];
                    $( ".visibility_drop li a label input:checked" ).each(function( index ) 
                    {
                        all_vals_of_two_2.push($( this ).val());                    
                    });    
                    console.log(all_vals_of_two_2+"visibility_drop");
                    $('#alert_drop').val(all_vals_of_two_2);
                    $('#alert_drop').multiselect('refresh');            
                }
                var isSelectAllOption = $target.val() === this.options.selectAllValue;

                // Apply or unapply the configured selected class.
                if (this.options.selectedClass) {
                    if (checked) {
                        $target.closest('li')
                            .addClass(this.options.selectedClass);
                    }
                    else {
                        $target.closest('li')
                            .removeClass(this.options.selectedClass);
                    }
                }

                // Get the corresponding option.
                var value = $target.val();
                var $option = this.getOptionByValue(value);

                var $optionsNotThis = $('option', this.$select).not($option);
                var $checkboxesNotThis = $('input', this.$container).not($target);

                if (isSelectAllOption) {

                    if (checked) {
                        this.selectAll(this.options.selectAllJustVisible);
                    }
                    else {
                        this.deselectAll(this.options.selectAllJustVisible);
                    }
                }
                else {
                    if (checked) {
                        $option.prop('selected', true);

                        if (this.options.multiple) {
                            // Simply select additional option.
                            $option.prop('selected', true);
                        }
                        else {
                            // Unselect all other options and corresponding checkboxes.
                            if (this.options.selectedClass) {
                                $($checkboxesNotThis).closest('li').removeClass(this.options.selectedClass);
                            }

                            $($checkboxesNotThis).prop('checked', false);
                            $optionsNotThis.prop('selected', false);

                            // It's a single selection, so close.
                            this.$button.click();
                        }

                        if (this.options.selectedClass === "active") {
                            $optionsNotThis.closest("a").css("outline", "");
                        }
                    }
                    else {
                        // Unselect option.
                        $option.prop('selected', false);
                    }

                    // To prevent select all from firing onChange: #575
                    this.options.onChange($option, checked);

                    // Do not update select all or optgroups on select all change!
                    this.updateSelectAll();

                    if (this.options.enableClickableOptGroups && this.options.multiple) {
                        this.updateOptGroups();
                    }
                }

                this.$select.change();
                this.updateButtonText();

                if(this.options.preventInputChangeEvent) {
                    return false;
                }
            }, this));

            $('li a', this.$ul).on('mousedown', function(e) {
                if (e.shiftKey) {
                    // Prevent selecting text by Shift+click
                    return false;
                }
            });

            $('li a', this.$ul).on('touchstart click', $.proxy(function(event) {
                event.stopPropagation();

                var $target = $(event.target);

                if (event.shiftKey && this.options.multiple) {
                    if($target.is("label")){ // Handles checkbox selection manually (see https://github.com/davidstutz/bootstrap-multiselect/issues/431)
                        event.preventDefault();
                        $target = $target.find("input");
                        $target.prop("checked", !$target.prop("checked"));
                    }
                    var checked = $target.prop('checked') || false;

                    if (this.lastToggledInput !== null && this.lastToggledInput !== $target) { // Make sure we actually have a range
                        var from = $target.closest("li").index();
                        var to = this.lastToggledInput.closest("li").index();

                        if (from > to) { // Swap the indices
                            var tmp = to;
                            to = from;
                            from = tmp;
                        }

                        // Make sure we grab all elements since slice excludes the last index
                        ++to;

                        // Change the checkboxes and underlying options
                        var range = this.$ul.find("li").slice(from, to).find("input");

                        range.prop('checked', checked);

                        if (this.options.selectedClass) {
                            range.closest('li')
                                .toggleClass(this.options.selectedClass, checked);
                        }

                        for (var i = 0, j = range.length; i < j; i++) {
                            var $checkbox = $(range[i]);

                            var $option = this.getOptionByValue($checkbox.val());

                            $option.prop('selected', checked);
                        }
                    }

                    // Trigger the select "change" event
                    $target.trigger("change");
                }

                // Remembers last clicked option
                if($target.is("input") && !$target.closest("li").is(".multiselect-item")){
                    this.lastToggledInput = $target;
                }

                $target.blur();
            }, this));

            // Keyboard support.
            this.$container.off('keydown.multiselect').on('keydown.multiselect', $.proxy(function(event) {
                if ($('input[type="text"]', this.$container).is(':focus')) {
                    return;
                }

                if (event.keyCode === 9 && this.$container.hasClass('open')) {
                    this.$button.click();
                }
                else {
                    var $items = $(this.$container).find("li:not(.divider):not(.disabled) a").filter(":visible");

                    if (!$items.length) {
                        return;
                    }

                    var index = $items.index($items.filter(':focus'));

                    // Navigation up.
                    if (event.keyCode === 38 && index > 0) {
                        index--;
                    }
                    // Navigate down.
                    else if (event.keyCode === 40 && index < $items.length - 1) {
                        index++;
                    }
                    else if (!~index) {
                        index = 0;
                    }

                    var $current = $items.eq(index);
                    $current.focus();

                    if (event.keyCode === 32 || event.keyCode === 13) {
                        var $checkbox = $current.find('input');

                        $checkbox.prop("checked", !$checkbox.prop("checked"));
                        $checkbox.change();
                    }

                    event.stopPropagation();
                    event.preventDefault();
                }
            }, this));

            if (this.options.enableClickableOptGroups && this.options.multiple) {
                $("li.multiselect-group input", this.$ul).on("change", $.proxy(function(event) {
                    event.stopPropagation();

                    var $target = $(event.target);
                    var checked = $target.prop('checked') || false;

                    var $li = $(event.target).closest('li');
                    var $group = $li.nextUntil("li.multiselect-group")
                        .not('.multiselect-filter-hidden')
                        .not('.disabled');

                    var $inputs = $group.find("input");

                    var values = [];
                    var $options = [];

                    $.each($inputs, $.proxy(function(index, input) {
                        var value = $(input).val();
                        var $option = this.getOptionByValue(value);

                        if (checked) {
                            $(input).prop('checked', true);
                            $(input).closest('li')
                                .addClass(this.options.selectedClass);

                            $option.prop('selected', true);
                        }
                        else {
                            $(input).prop('checked', false);
                            $(input).closest('li')
                                .removeClass(this.options.selectedClass);

                            $option.prop('selected', false);
                        }

                        $options.push(this.getOptionByValue(value));
                    }, this))

                    // Cannot use select or deselect here because it would call updateOptGroups again.

                    this.options.onChange($options, checked);

                    this.updateButtonText();
                    this.updateSelectAll();
                }, this));
            }

            if (this.options.enableCollapsibleOptGroups && this.options.multiple) {
                $("li.multiselect-group .caret-container", this.$ul).on("click", $.proxy(function(event) {
                    var $li = $(event.target).closest('li');
                    var $inputs = $li.nextUntil("li.multiselect-group")
                            .not('.multiselect-filter-hidden');

                    var visible = true;
                    $inputs.each(function() {
                        visible = visible && $(this).is(':visible');
                    });

                    if (visible) {
                        $inputs.hide()
                            .addClass('multiselect-collapsible-hidden');
                    }
                    else {
                        $inputs.show()
                            .removeClass('multiselect-collapsible-hidden');
                    }
                }, this));

                $("li.multiselect-all", this.$ul).css('background', '#f3f3f3').css('border-bottom', '1px solid #eaeaea');
                $("li.multiselect-all > a > label.checkbox", this.$ul).css('padding', '3px 20px 3px 35px');
                $("li.multiselect-group > a > input", this.$ul).css('margin', '4px 0px 5px -20px');
            }
        },

        /**
         * Create an option using the given select option.
         *
         * @param {jQuery} element
         */
        createOptionValue: function(element) {
            var $element = $(element);
            if ($element.is(':selected')) {
                $element.prop('selected', true);
            }

            // Support the label attribute on options.
            var label = this.options.optionLabel(element);
            var subElement = this.options.injectElement(element);
            var classes = this.options.optionClass(element);
            var value = $element.val();
            var inputType = this.options.multiple ? "checkbox" : "radio";

            var $li = $(this.options.templates.li);
            var $label = $('label', $li);
            $label.addClass(inputType);
            $li.addClass(classes);

            if (this.options.enableHTML) {
                $label.html(" " + label);
            }
            else {
                $label.text(" " + label);
            }

            var $checkbox = $('<input/>').attr('type', inputType);

            var name = this.options.checkboxName($element);
            if (name) {
                $checkbox.attr('name', name);
            }

            $label.prepend($checkbox);
            $label.append(subElement);

            var selected = $element.prop('selected') || false;
            $checkbox.val(value);

            if (value === this.options.selectAllValue) {
                $li.addClass("multiselect-item multiselect-all");
                $checkbox.parent().parent()
                    .addClass('multiselect-all');
            }

            $label.attr('title', $element.attr('title'));

            this.$ul.append($li);

            if ($element.is(':disabled')) {
                $checkbox.attr('disabled', 'disabled')
                    .prop('disabled', true)
                    .closest('a')
                    .attr("tabindex", "-1")
                    .closest('li')
                    .addClass('disabled');
            }

            $checkbox.prop('checked', selected);

            if (selected && this.options.selectedClass) {
                $checkbox.closest('li')
                    .addClass(this.options.selectedClass);
            }
        },

        /**
         * Creates a divider using the given select option.
         *
         * @param {jQuery} element
         */
        createDivider: function(element) {
            var $divider = $(this.options.templates.divider);
            this.$ul.append($divider);
        },

        /**
         * Creates an optgroup.
         *
         * @param {jQuery} group
         */
        createOptgroup: function(group) {
            var label = $(group).attr("label");
            var value = $(group).attr("value");
            var $li = $('<li class="multiselect-item multiselect-group"><a href="javascript:void(0);"><label><b></b></label></a></li>');

            var classes = this.options.optionClass(group);
            $li.addClass(classes);

            if (this.options.enableHTML) {
                $('label b', $li).html(" " + label);
            }
            else {
                $('label b', $li).text(" " + label);
            }

            if (this.options.enableCollapsibleOptGroups && this.options.multiple) {
                $('a', $li).append('<span class="caret-container"><b class="caret"></b></span>');
            }

            if (this.options.enableClickableOptGroups && this.options.multiple) {
                $('a label', $li).prepend('<input type="checkbox" value="' + value + '"/>');
            }

            if ($(group).is(':disabled')) {
                $li.addClass('disabled');
            }

            this.$ul.append($li);

            $("option", group).each($.proxy(function($, group) {
                this.createOptionValue(group);
            }, this))
        },

        /**
         * Build the select all.
         *
         * Checks if a select all has already been created.
         */
        buildSelectAll: function() {
            if (typeof this.options.selectAllValue === 'number') {
                this.options.selectAllValue = this.options.selectAllValue.toString();
            }

            var alreadyHasSelectAll = this.hasSelectAll();

            if (!alreadyHasSelectAll && this.options.includeSelectAllOption && this.options.multiple
                    && $('option', this.$select).length > this.options.includeSelectAllIfMoreThan) {

                // Check whether to add a divider after the select all.
                if (this.options.includeSelectAllDivider) {
                    this.$ul.prepend($(this.options.templates.divider));
                }

                var $li = $(this.options.templates.li);
                $('label', $li).addClass("checkbox");

                if (this.options.enableHTML) {
                    $('label', $li).html(" " + this.options.selectAllText);
                }
                else {
                    $('label', $li).text(" " + this.options.selectAllText);
                }

                if (this.options.selectAllName) {
                    $('label', $li).prepend('<input type="checkbox" name="' + this.options.selectAllName + '" />');
                }
                else {
                    $('label', $li).prepend('<input type="checkbox" />');
                }

                var $checkbox = $('input', $li);
                $checkbox.val(this.options.selectAllValue);

                $li.addClass("multiselect-item multiselect-all");
                $checkbox.parent().parent()
                    .addClass('multiselect-all');

                this.$ul.prepend($li);

                $checkbox.prop('checked', false);
            }
        },

        /**
         * Builds the filter.
         */
        buildFilter: function() {

            // Build filter if filtering OR case insensitive filtering is enabled and the number of options exceeds (or equals) enableFilterLength.
            if (this.options.enableFiltering || this.options.enableCaseInsensitiveFiltering) {
                var enableFilterLength = Math.max(this.options.enableFiltering, this.options.enableCaseInsensitiveFiltering);

                if (this.$select.find('option').length >= enableFilterLength) {

                    this.$filter = $(this.options.templates.filter);
                    $('input', this.$filter).attr('placeholder', this.options.filterPlaceholder);

                    // Adds optional filter clear button
                    if(this.options.includeFilterClearBtn) {
                        var clearBtn = $(this.options.templates.filterClearBtn);
                        clearBtn.on('click', $.proxy(function(event){
                            clearTimeout(this.searchTimeout);

                            this.$filter.find('.multiselect-search').val('');
                            $('li', this.$ul).show().removeClass('multiselect-filter-hidden');

                            this.updateSelectAll();

                            if (this.options.enableClickableOptGroups && this.options.multiple) {
                                this.updateOptGroups();
                            }

                        }, this));
                        this.$filter.find('.input-group').append(clearBtn);
                    }

                    this.$ul.prepend(this.$filter);

                    this.$filter.val(this.query).on('click', function(event) {
                        event.stopPropagation();
                    }).on('input keydown', $.proxy(function(event) {
                        // Cancel enter key default behaviour
                        if (event.which === 13) {
                          event.preventDefault();
                      }

                        // This is useful to catch "keydown" events after the browser has updated the control.
                        clearTimeout(this.searchTimeout);

                        this.searchTimeout = this.asyncFunction($.proxy(function() {

                            if (this.query !== event.target.value) {
                                this.query = event.target.value;

                                var currentGroup, currentGroupVisible;
                                $.each($('li', this.$ul), $.proxy(function(index, element) {
                                    var value = $('input', element).length > 0 ? $('input', element).val() : "";
                                    var text = $('label', element).text();

                                    var filterCandidate = '';
                                    if ((this.options.filterBehavior === 'text')) {
                                        filterCandidate = text;
                                    }
                                    else if ((this.options.filterBehavior === 'value')) {
                                        filterCandidate = value;
                                    }
                                    else if (this.options.filterBehavior === 'both') {
                                        filterCandidate = text + '\n' + value;
                                    }

                                    if (value !== this.options.selectAllValue && text) {

                                        // By default lets assume that element is not
                                        // interesting for this search.
                                        var showElement = false;

                                        if (this.options.enableCaseInsensitiveFiltering) {
                                            filterCandidate = filterCandidate.toLowerCase();
                                            this.query = this.query.toLowerCase();
                                        }

                                        if (this.options.enableFullValueFiltering && this.options.filterBehavior !== 'both') {
                                            var valueToMatch = filterCandidate.trim().substring(0, this.query.length);
                                            if (this.query.indexOf(valueToMatch) > -1) {
                                                showElement = true;
                                            }
                                        }
                                        else if (filterCandidate.indexOf(this.query) > -1) {
                                            showElement = true;
                                        }

                                        // Toggle current element (group or group item) according to showElement boolean.
                                        $(element).toggle(showElement)
                                            .toggleClass('multiselect-filter-hidden', !showElement);

                                        // Differentiate groups and group items.
                                        if ($(element).hasClass('multiselect-group')) {
                                            // Remember group status.
                                            currentGroup = element;
                                            currentGroupVisible = showElement;
                                        }
                                        else {
                                            // Show group name when at least one of its items is visible.
                                            if (showElement) {
                                                $(currentGroup).show()
                                                    .removeClass('multiselect-filter-hidden');
                                            }

                                            // Show all group items when group name satisfies filter.
                                            if (!showElement && currentGroupVisible) {
                                                $(element).show()
                                                    .removeClass('multiselect-filter-hidden');
                                            }
                                        }
                                    }
                                }, this));
                            }

                            this.updateSelectAll();

                            if (this.options.enableClickableOptGroups && this.options.multiple) {
                                this.updateOptGroups();
                            }

                            this.options.onFiltering(event.target);

                        }, this), 300, this);
                    }, this));
                }
            }
        },

        /**
         * Unbinds the whole plugin.
         */
        destroy: function() {
            this.$container.remove();
            this.$select.show();
            this.$select.data('multiselect', null);
        },

        /**
         * Refreshs the multiselect based on the selected options of the select.
         */
        refresh: function () {
            var inputs = $.map($('li input', this.$ul), $);

            $('option', this.$select).each($.proxy(function (index, element) {
                var $elem = $(element);
                var value = $elem.val();
                var $input;
                for (var i = inputs.length; 0 < i--; /**/) {
                    if (value !== ($input = inputs[i]).val())
                        continue; // wrong li

                    if ($elem.is(':selected')) {
                        $input.prop('checked', true);

                        if (this.options.selectedClass) {
                            $input.closest('li')
                                .addClass(this.options.selectedClass);
                        }
                    }
                    else {
                        $input.prop('checked', false);

                        if (this.options.selectedClass) {
                            $input.closest('li')
                                .removeClass(this.options.selectedClass);
                        }
                    }

                    if ($elem.is(":disabled")) {
                        $input.attr('disabled', 'disabled')
                            .prop('disabled', true)
                            .closest('li')
                            .addClass('disabled');
                    }
                    else {
                        $input.prop('disabled', false)
                            .closest('li')
                            .removeClass('disabled');
                    }
                    break; // assumes unique values
                }
            }, this));

            this.updateButtonText();
            this.updateSelectAll();

            if (this.options.enableClickableOptGroups && this.options.multiple) {
                this.updateOptGroups();
            }
        },

        /**
         * Select all options of the given values.
         *
         * If triggerOnChange is set to true, the on change event is triggered if
         * and only if one value is passed.
         *
         * @param {Array} selectValues
         * @param {Boolean} triggerOnChange
         */
        select: function(selectValues, triggerOnChange) {
            if(!$.isArray(selectValues)) {
                selectValues = [selectValues];
            }

            for (var i = 0; i < selectValues.length; i++) {
                var value = selectValues[i];

                if (value === null || value === undefined) {
                    continue;
                }

                var $option = this.getOptionByValue(value);
                var $checkbox = this.getInputByValue(value);

                if($option === undefined || $checkbox === undefined) {
                    continue;
                }

                if (!this.options.multiple) {
                    this.deselectAll(false);
                }

                if (this.options.selectedClass) {
                    $checkbox.closest('li')
                        .addClass(this.options.selectedClass);
                }

                $checkbox.prop('checked', true);
                $option.prop('selected', true);

                if (triggerOnChange) {
                    this.options.onChange($option, true);
                }
            }

            this.updateButtonText();
            this.updateSelectAll();

            if (this.options.enableClickableOptGroups && this.options.multiple) {
                this.updateOptGroups();
            }
        },

        /**
         * Clears all selected items.
         */
        clearSelection: function () {
            this.deselectAll(false);
            this.updateButtonText();
            this.updateSelectAll();

            if (this.options.enableClickableOptGroups && this.options.multiple) {
                this.updateOptGroups();
            }
        },

        /**
         * Deselects all options of the given values.
         *
         * If triggerOnChange is set to true, the on change event is triggered, if
         * and only if one value is passed.
         *
         * @param {Array} deselectValues
         * @param {Boolean} triggerOnChange
         */
        deselect: function(deselectValues, triggerOnChange) {
            if(!$.isArray(deselectValues)) {
                deselectValues = [deselectValues];
            }

            for (var i = 0; i < deselectValues.length; i++) {
                var value = deselectValues[i];

                if (value === null || value === undefined) {
                    continue;
                }

                var $option = this.getOptionByValue(value);
                var $checkbox = this.getInputByValue(value);

                if($option === undefined || $checkbox === undefined) {
                    continue;
                }

                if (this.options.selectedClass) {
                    $checkbox.closest('li')
                        .removeClass(this.options.selectedClass);
                }

                $checkbox.prop('checked', false);
                $option.prop('selected', false);

                if (triggerOnChange) {
                    this.options.onChange($option, false);
                }
            }

            this.updateButtonText();
            this.updateSelectAll();

            if (this.options.enableClickableOptGroups && this.options.multiple) {
                this.updateOptGroups();
            }
        },

        /**
         * Selects all enabled & visible options.
         *
         * If justVisible is true or not specified, only visible options are selected.
         *
         * @param {Boolean} justVisible
         * @param {Boolean} triggerOnSelectAll
         */
        selectAll: function (justVisible, triggerOnSelectAll) {

            var justVisible = typeof justVisible === 'undefined' ? true : justVisible;
            var allLis = $("li:not(.divider):not(.disabled):not(.multiselect-group)", this.$ul);
            var visibleLis = $("li:not(.divider):not(.disabled):not(.multiselect-group):not(.multiselect-filter-hidden):not(.multiselect-collapisble-hidden)", this.$ul).filter(':visible');

            if(justVisible) {
                $('input:enabled' , visibleLis).prop('checked', true);
                visibleLis.addClass(this.options.selectedClass);

                $('input:enabled' , visibleLis).each($.proxy(function(index, element) {
                    var value = $(element).val();
                    var option = this.getOptionByValue(value);
                    $(option).prop('selected', true);
                }, this));
            }
            else {
                $('input:enabled' , allLis).prop('checked', true);
                allLis.addClass(this.options.selectedClass);

                $('input:enabled' , allLis).each($.proxy(function(index, element) {
                    var value = $(element).val();
                    var option = this.getOptionByValue(value);
                    $(option).prop('selected', true);
                }, this));
            }

            $('li input[value="' + this.options.selectAllValue + '"]').prop('checked', true);

            if (this.options.enableClickableOptGroups && this.options.multiple) {
                this.updateOptGroups();
            }

            if (triggerOnSelectAll) {
                this.options.onSelectAll();
            }
        },

        /**
         * Deselects all options.
         *
         * If justVisible is true or not specified, only visible options are deselected.
         *
         * @param {Boolean} justVisible
         */
        deselectAll: function (justVisible, triggerOnDeselectAll) {

            var justVisible = typeof justVisible === 'undefined' ? true : justVisible;
            var allLis = $("li:not(.divider):not(.disabled):not(.multiselect-group)", this.$ul);
            var visibleLis = $("li:not(.divider):not(.disabled):not(.multiselect-group):not(.multiselect-filter-hidden):not(.multiselect-collapisble-hidden)", this.$ul).filter(':visible');

            if(justVisible) {
                $('input[type="checkbox"]:enabled' , visibleLis).prop('checked', false);
                visibleLis.removeClass(this.options.selectedClass);

                $('input[type="checkbox"]:enabled' , visibleLis).each($.proxy(function(index, element) {
                    var value = $(element).val();
                    var option = this.getOptionByValue(value);
                    $(option).prop('selected', false);
                }, this));
            }
            else {
                $('input[type="checkbox"]:enabled' , allLis).prop('checked', false);
                allLis.removeClass(this.options.selectedClass);

                $('input[type="checkbox"]:enabled' , allLis).each($.proxy(function(index, element) {
                    var value = $(element).val();
                    var option = this.getOptionByValue(value);
                    $(option).prop('selected', false);
                }, this));
            }

            $('li input[value="' + this.options.selectAllValue + '"]').prop('checked', false);

            if (this.options.enableClickableOptGroups && this.options.multiple) {
                this.updateOptGroups();
            }

            if (triggerOnDeselectAll) {
                this.options.onDeselectAll();
            }
        },

        /**
         * Rebuild the plugin.
         *
         * Rebuilds the dropdown, the filter and the select all option.
         */
        rebuild: function() {
            this.$ul.html('');

            // Important to distinguish between radios and checkboxes.
            this.options.multiple = this.$select.attr('multiple') === "multiple";

            this.buildSelectAll();
            this.buildDropdownOptions();
            this.buildFilter();

            this.updateButtonText();
            this.updateSelectAll(true);

            if (this.options.enableClickableOptGroups && this.options.multiple) {
                this.updateOptGroups();
            }

            if (this.options.disableIfEmpty && $('option', this.$select).length <= 0) {
                this.disable();
            }
            else {
                this.enable();
            }

            if (this.options.dropRight) {
                this.$ul.addClass('pull-right');
            }
        },

        /**
         * The provided data will be used to build the dropdown.
         */
        dataprovider: function(dataprovider) {

            var groupCounter = 0;
            var $select = this.$select.empty();

            $.each(dataprovider, function (index, option) {
                var $tag;

                if ($.isArray(option.children)) { // create optiongroup tag
                    groupCounter++;

                    $tag = $('<optgroup/>').attr({
                        label: option.label || 'Group ' + groupCounter,
                        disabled: !!option.disabled
                    });

                    forEach(option.children, function(subOption) { // add children option tags
                        var attributes = {
                            value: subOption.value,
                            label: subOption.label || subOption.value,
                            title: subOption.title,
                            selected: !!subOption.selected,
                            disabled: !!subOption.disabled
                        };

                        //Loop through attributes object and add key-value for each attribute
                       for (var key in subOption.attributes) {
                            attributes['data-' + key] = subOption.attributes[key];
                       }
                         //Append original attributes + new data attributes to option
                        $tag.append($('<option/>').attr(attributes));
                    });
                }
                else {

                    var attributes = {
                        'value': option.value,
                        'label': option.label || option.value,
                        'title': option.title,
                        'class': option.class,
                        'selected': !!option.selected,
                        'disabled': !!option.disabled
                    };
                    //Loop through attributes object and add key-value for each attribute
                    for (var key in option.attributes) {
                      attributes['data-' + key] = option.attributes[key];
                    }
                    //Append original attributes + new data attributes to option
                    $tag = $('<option/>').attr(attributes);

                    $tag.text(option.label || option.value);
                }

                $select.append($tag);
            });

            this.rebuild();
        },

        /**
         * Enable the multiselect.
         */
        enable: function() {
            this.$select.prop('disabled', false);
            this.$button.prop('disabled', false)
                .removeClass('disabled');
        },

        /**
         * Disable the multiselect.
         */
        disable: function() {
            this.$select.prop('disabled', true);
            this.$button.prop('disabled', true)
                .addClass('disabled');
        },

        /**
         * Set the options.
         *
         * @param {Array} options
         */
        setOptions: function(options) {
            this.options = this.mergeOptions(options);
        },

        /**
         * Merges the given options with the default options.
         *
         * @param {Array} options
         * @returns {Array}
         */
        mergeOptions: function(options) {
            return $.extend(true, {}, this.defaults, this.options, options);
        },

        /**
         * Checks whether a select all checkbox is present.
         *
         * @returns {Boolean}
         */
        hasSelectAll: function() {
            return $('li.multiselect-all', this.$ul).length > 0;
        },

        /**
         * Update opt groups.
         */
        updateOptGroups: function() {
            var $groups = $('li.multiselect-group', this.$ul)

            $groups.each(function() {
                var $options = $(this).nextUntil('li.multiselect-group')
                    .not('.multiselect-filter-hidden')
                    .not('.disabled');

                var checked = true;
                $options.each(function() {
                    var $input = $('input', this);

                    if (!$input.prop('checked')) {
                        checked = false;
                    }
                });

                $('input', this).prop('checked', checked);
            });
        },

        /**
         * Updates the select all checkbox based on the currently displayed and selected checkboxes.
         */
        updateSelectAll: function(notTriggerOnSelectAll) {
            if (this.hasSelectAll()) {
                var allBoxes = $("li:not(.multiselect-item):not(.multiselect-filter-hidden):not(.multiselect-group):not(.disabled) input:enabled", this.$ul);
                var allBoxesLength = allBoxes.length;
                var checkedBoxesLength = allBoxes.filter(":checked").length;
                var selectAllLi  = $("li.multiselect-all", this.$ul);
                var selectAllInput = selectAllLi.find("input");

                if (checkedBoxesLength > 0 && checkedBoxesLength === allBoxesLength) {
                    selectAllInput.prop("checked", true);
                    selectAllLi.addClass(this.options.selectedClass);
                    this.options.onSelectAll();
                }
                else {
                    selectAllInput.prop("checked", false);
                    selectAllLi.removeClass(this.options.selectedClass);
                    if (checkedBoxesLength === 0) {
                        if (!notTriggerOnSelectAll) {
                            this.options.onDeselectAll();
                        }
                    }
                }
            }
        },

        /**
         * Update the button text and its title based on the currently selected options.
         */
        updateButtonText: function() {
            var options = this.getSelected();

            // First update the displayed button text.
            if (this.options.enableHTML) {
                $('.multiselect .multiselect-selected-text', this.$container).html(this.options.buttonText(options, this.$select));
            }
            else {
                $('.multiselect .multiselect-selected-text', this.$container).text(this.options.buttonText(options, this.$select));
            }

            // Now update the title attribute of the button.
            $('.multiselect', this.$container).attr('title', this.options.buttonTitle(options, this.$select));
        },

        /**
         * Get all selected options.
         *
         * @returns {jQUery}
         */
        getSelected: function() {
            return $('option', this.$select).filter(":selected");
        },

        /**
         * Gets a select option by its value.
         *
         * @param {String} value
         * @returns {jQuery}
         */
        getOptionByValue: function (value) {

            var options = $('option', this.$select);
            var valueToCompare = value.toString();

            for (var i = 0; i < options.length; i = i + 1) {
                var option = options[i];
                if (option.value === valueToCompare) {
                    return $(option);
                }
            }
        },

        /**
         * Get the input (radio/checkbox) by its value.
         *
         * @param {String} value
         * @returns {jQuery}
         */
        getInputByValue: function (value) {

            var checkboxes = $('li input', this.$ul);
            var valueToCompare = value.toString();

            for (var i = 0; i < checkboxes.length; i = i + 1) {
                var checkbox = checkboxes[i];
                if (checkbox.value === valueToCompare) {
                    return $(checkbox);
                }
            }
        },

        /**
         * Used for knockout integration.
         */
        updateOriginalOptions: function() {
            this.originalOptions = this.$select.clone()[0].options;
        },

        asyncFunction: function(callback, timeout, self) {
            var args = Array.prototype.slice.call(arguments, 3);
            return setTimeout(function() {
                callback.apply(self || window, args);
            }, timeout);
        },

        setAllSelectedText: function(allSelectedText) {
            this.options.allSelectedText = allSelectedText;
            this.updateButtonText();
        }
    };

    $.fn.multiselect = function(option, parameter, extraOptions) {
        return this.each(function() {
            var data = $(this).data('multiselect');
            var options = typeof option === 'object' && option;

            // Initialize the multiselect.
            if (!data) {
                data = new Multiselect(this, options);
                $(this).data('multiselect', data);
            }

            // Call multiselect method.
            if (typeof option === 'string') {
                data[option](parameter, extraOptions);

                if (option === 'destroy') {
                    $(this).data('multiselect', false);
                }
            }
        });
    };

    $.fn.multiselect.Constructor = Multiselect;

    $(function() {
        $("select[data-role=multiselect]").multiselect();
    });

}(window.jQuery);

/* ========================================================================
 * bootstrap-tour - v0.10.3
 * http://bootstraptour.com
 * ========================================================================
 * Copyright 2012-2015 Ulrich Sossou
 *
 * ========================================================================
 * Licensed under the MIT License (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://opensource.org/licenses/MIT
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================================
 */

!function(t,e){return"function"==typeof define&&define.amd?define(["jquery"],function(o){return t.Tour=e(o)}):"object"==typeof exports?module.exports=e(require("jQuery")):t.Tour=e(t.jQuery)}(window,function(t){var e,o;return o=window.document,e=function(){function e(e){var o;try{o=window.localStorage}catch(n){o=!1}this._options=t.extend({name:"tour",steps:[],container:"body",autoscroll:!0,keyboard:!0,storage:o,debug:!1,backdrop:!1,backdropContainer:"body",backdropPadding:0,redirect:!0,orphan:!1,duration:!1,delay:!1,basePath:"",template:'<div class="popover" role="tooltip"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev">&laquo; Prev</button> <button class="btn btn-sm btn-default" data-role="next">Next &raquo;</button> <button class="btn btn-sm btn-default" data-role="pause-resume" data-pause-text="Pause" data-resume-text="Resume">Pause</button> </div> <button class="btn btn-sm btn-default" data-role="end">End tour</button> </div> </div>',afterSetState:function(){},afterGetState:function(){},afterRemoveState:function(){},onStart:function(){},onEnd:function(){},onShow:function(){},onShown:function(){},onHide:function(){},onHidden:function(){},onNext:function(){},onPrev:function(){},onPause:function(){},onResume:function(){},onRedirectError:function(){}},e),this._force=!1,this._inited=!1,this._current=null,this.backdrop={overlay:null,$element:null,$background:null,backgroundShown:!1,overlayElementShown:!1}}return e.prototype.addSteps=function(t){var e,o,n;for(o=0,n=t.length;n>o;o++)e=t[o],this.addStep(e);return this},e.prototype.addStep=function(t){return this._options.steps.push(t),this},e.prototype.getStep=function(e){return null!=this._options.steps[e]?t.extend({id:"step-"+e,path:"",host:"",placement:"right",title:"",content:"<p></p>",next:e===this._options.steps.length-1?-1:e+1,prev:e-1,animation:!0,container:this._options.container,autoscroll:this._options.autoscroll,backdrop:this._options.backdrop,backdropContainer:this._options.backdropContainer,backdropPadding:this._options.backdropPadding,redirect:this._options.redirect,reflexElement:this._options.steps[e].element,backdropElement:this._options.steps[e].element,orphan:this._options.orphan,duration:this._options.duration,delay:this._options.delay,template:this._options.template,onShow:this._options.onShow,onShown:this._options.onShown,onHide:this._options.onHide,onHidden:this._options.onHidden,onNext:this._options.onNext,onPrev:this._options.onPrev,onPause:this._options.onPause,onResume:this._options.onResume,onRedirectError:this._options.onRedirectError},this._options.steps[e]):void 0},e.prototype.init=function(t){return this._force=t,this.ended()?(this._debug("Tour ended, init prevented."),this):(this.setCurrentStep(),this._initMouseNavigation(),this._initKeyboardNavigation(),this._onResize(function(t){return function(){return t.showStep(t._current)}}(this)),null!==this._current&&this.showStep(this._current),this._inited=!0,this)},e.prototype.start=function(t){var e;return null==t&&(t=!1),this._inited||this.init(t),null===this._current&&(e=this._makePromise(null!=this._options.onStart?this._options.onStart(this):void 0),this._callOnPromiseDone(e,this.showStep,0)),this},e.prototype.next=function(){var t;return t=this.hideStep(this._current,this._current+1),this._callOnPromiseDone(t,this._showNextStep)},e.prototype.prev=function(){var t;return t=this.hideStep(this._current,this._current-1),this._callOnPromiseDone(t,this._showPrevStep)},e.prototype.goTo=function(t){var e;return e=this.hideStep(this._current,t),this._callOnPromiseDone(e,this.showStep,t)},e.prototype.end=function(){var e,n;return e=function(e){return function(){return t(o).off("click.tour-"+e._options.name),t(o).off("keyup.tour-"+e._options.name),t(window).off("resize.tour-"+e._options.name),e._setState("end","yes"),e._inited=!1,e._force=!1,e._clearTimer(),null!=e._options.onEnd?e._options.onEnd(e):void 0}}(this),n=this.hideStep(this._current),this._callOnPromiseDone(n,e)},e.prototype.ended=function(){return!this._force&&!!this._getState("end")},e.prototype.restart=function(){return this._removeState("current_step"),this._removeState("end"),this._removeState("redirect_to"),this.start()},e.prototype.pause=function(){var t;return t=this.getStep(this._current),t&&t.duration?(this._paused=!0,this._duration-=(new Date).getTime()-this._start,window.clearTimeout(this._timer),this._debug("Paused/Stopped step "+(this._current+1)+" timer ("+this._duration+" remaining)."),null!=t.onPause?t.onPause(this,this._duration):void 0):this},e.prototype.resume=function(){var t;return t=this.getStep(this._current),t&&t.duration?(this._paused=!1,this._start=(new Date).getTime(),this._duration=this._duration||t.duration,this._timer=window.setTimeout(function(t){return function(){return t._isLast()?t.next():t.end()}}(this),this._duration),this._debug("Started step "+(this._current+1)+" timer with duration "+this._duration),null!=t.onResume&&this._duration!==t.duration?t.onResume(this,this._duration):void 0):this},e.prototype.hideStep=function(e,o){var n,r,i,s;return(s=this.getStep(e))?(this._clearTimer(),i=this._makePromise(null!=s.onHide?s.onHide(this,e):void 0),r=function(n){return function(){var r,i;return r=t(s.element),r.data("bs.popover")||r.data("popover")||(r=t("body")),r.popover("destroy").removeClass("tour-"+n._options.name+"-element tour-"+n._options.name+"-"+e+"-element").removeData("bs.popover").focus(),s.reflex&&t(s.reflexElement).removeClass("tour-step-element-reflex").off(""+n._reflexEvent(s.reflex)+".tour-"+n._options.name),s.backdrop&&(i=null!=o&&n.getStep(o),i&&i.backdrop&&i.backdropElement===s.backdropElement||n._hideBackdrop()),null!=s.onHidden?s.onHidden(n):void 0}}(this),n=s.delay.hide||s.delay,"[object Number]"==={}.toString.call(n)&&n>0?(this._debug("Wait "+n+" milliseconds to hide the step "+(this._current+1)),window.setTimeout(function(t){return function(){return t._callOnPromiseDone(i,r)}}(this),n)):this._callOnPromiseDone(i,r),i):void 0},e.prototype.showStep=function(t){var e,n,r,i,s,a;return this.ended()?(this._debug("Tour ended, showStep prevented."),this):(a=this.getStep(t),a&&(s=t<this._current,n=this._makePromise(null!=a.onShow?a.onShow(this,t):void 0),this.setCurrentStep(t),e=function(){switch({}.toString.call(a.path)){case"[object Function]":return a.path();case"[object String]":return this._options.basePath+a.path;default:return a.path}}.call(this),!a.redirect||!this._isRedirect(a.host,e,o.location)||(this._redirect(a,t,e),this._isJustPathHashDifferent(a.host,e,o.location)))?(i=function(e){return function(){var o;if(e._isOrphan(a)){if(a.orphan===!1)return e._debug("Skip the orphan step "+(e._current+1)+".\nOrphan option is false and the element does not exist or is hidden."),s?e._showPrevStep():e._showNextStep(),void 0;e._debug("Show the orphan step "+(e._current+1)+". Orphans option is true.")}return a.backdrop&&e._showBackdrop(a),o=function(){return e.getCurrentStep()!==t||e.ended()?void 0:(null!=a.element&&a.backdrop&&e._showOverlayElement(a,!0),e._showPopover(a,t),null!=a.onShown&&a.onShown(e),e._debug("Step "+(e._current+1)+" of "+e._options.steps.length))},a.autoscroll?e._scrollIntoView(a,o):o(),a.duration?e.resume():void 0}}(this),r=a.delay.show||a.delay,"[object Number]"==={}.toString.call(r)&&r>0?(this._debug("Wait "+r+" milliseconds to show the step "+(this._current+1)),window.setTimeout(function(t){return function(){return t._callOnPromiseDone(n,i)}}(this),r)):this._callOnPromiseDone(n,i),n):void 0)},e.prototype.getCurrentStep=function(){return this._current},e.prototype.setCurrentStep=function(t){return null!=t?(this._current=t,this._setState("current_step",t)):(this._current=this._getState("current_step"),this._current=null===this._current?null:parseInt(this._current,10)),this},e.prototype.redraw=function(){return this._showOverlayElement(this.getStep(this.getCurrentStep()).element,!0)},e.prototype._setState=function(t,e){var o,n;if(this._options.storage){n=""+this._options.name+"_"+t;try{this._options.storage.setItem(n,e)}catch(r){o=r,o.code===DOMException.QUOTA_EXCEEDED_ERR&&this._debug("LocalStorage quota exceeded. State storage failed.")}return this._options.afterSetState(n,e)}return null==this._state&&(this._state={}),this._state[t]=e},e.prototype._removeState=function(t){var e;return this._options.storage?(e=""+this._options.name+"_"+t,this._options.storage.removeItem(e),this._options.afterRemoveState(e)):null!=this._state?delete this._state[t]:void 0},e.prototype._getState=function(t){var e,o;return this._options.storage?(e=""+this._options.name+"_"+t,o=this._options.storage.getItem(e)):null!=this._state&&(o=this._state[t]),(void 0===o||"null"===o)&&(o=null),this._options.afterGetState(t,o),o},e.prototype._showNextStep=function(){var t,e,o;return o=this.getStep(this._current),e=function(t){return function(){return t.showStep(o.next)}}(this),t=this._makePromise(null!=o.onNext?o.onNext(this):void 0),this._callOnPromiseDone(t,e)},e.prototype._showPrevStep=function(){var t,e,o;return o=this.getStep(this._current),e=function(t){return function(){return t.showStep(o.prev)}}(this),t=this._makePromise(null!=o.onPrev?o.onPrev(this):void 0),this._callOnPromiseDone(t,e)},e.prototype._debug=function(t){return this._options.debug?window.console.log("Bootstrap Tour '"+this._options.name+"' | "+t):void 0},e.prototype._isRedirect=function(t,e,o){var n;return null!=t&&""!==t&&("[object RegExp]"==={}.toString.call(t)&&!t.test(o.origin)||"[object String]"==={}.toString.call(t)&&this._isHostDifferent(t,o))?!0:(n=[o.pathname,o.search,o.hash].join(""),null!=e&&""!==e&&("[object RegExp]"==={}.toString.call(e)&&!e.test(n)||"[object String]"==={}.toString.call(e)&&this._isPathDifferent(e,n)))},e.prototype._isHostDifferent=function(t,e){switch({}.toString.call(t)){case"[object RegExp]":return!t.test(e.origin);case"[object String]":return this._getProtocol(t)!==this._getProtocol(e.href)||this._getHost(t)!==this._getHost(e.href);default:return!0}},e.prototype._isPathDifferent=function(t,e){return this._getPath(t)!==this._getPath(e)||!this._equal(this._getQuery(t),this._getQuery(e))||!this._equal(this._getHash(t),this._getHash(e))},e.prototype._isJustPathHashDifferent=function(t,e,o){var n;return null!=t&&""!==t&&this._isHostDifferent(t,o)?!1:(n=[o.pathname,o.search,o.hash].join(""),"[object String]"==={}.toString.call(e)?this._getPath(e)===this._getPath(n)&&this._equal(this._getQuery(e),this._getQuery(n))&&!this._equal(this._getHash(e),this._getHash(n)):!1)},e.prototype._redirect=function(e,n,r){var i;return t.isFunction(e.redirect)?e.redirect.call(this,r):(i="[object String]"==={}.toString.call(e.host)?""+e.host+r:r,this._debug("Redirect to "+i),this._getState("redirect_to")!==""+n?(this._setState("redirect_to",""+n),o.location.href=i):(this._debug("Error redirection loop to "+r),this._removeState("redirect_to"),null!=e.onRedirectError?e.onRedirectError(this):void 0))},e.prototype._isOrphan=function(e){return null==e.element||!t(e.element).length||t(e.element).is(":hidden")&&"http://www.w3.org/2000/svg"!==t(e.element)[0].namespaceURI},e.prototype._isLast=function(){return this._current<this._options.steps.length-1},e.prototype._showPopover=function(e,o){var n,r,i,s,a;return t(".tour-"+this._options.name).remove(),s=t.extend({},this._options),i=this._isOrphan(e),e.template=this._template(e,o),i&&(e.element="body",e.placement="top"),n=t(e.element),n.addClass("tour-"+this._options.name+"-element tour-"+this._options.name+"-"+o+"-element"),e.options&&t.extend(s,e.options),e.reflex&&!i&&t(e.reflexElement).addClass("tour-step-element-reflex").off(""+this._reflexEvent(e.reflex)+".tour-"+this._options.name).on(""+this._reflexEvent(e.reflex)+".tour-"+this._options.name,function(t){return function(){return t._isLast()?t.next():t.end()}}(this)),a=e.smartPlacement===!0&&-1===e.placement.search(/auto/i),n.popover({placement:a?"auto "+e.placement:e.placement,trigger:"manual",title:e.title,content:e.content,html:!0,animation:e.animation,container:e.container,template:e.template,selector:e.element}).popover("show"),r=n.data("bs.popover")?n.data("bs.popover").tip():n.data("popover").tip(),r.attr("id",e.id),this._focus(r,n,e.next<0),this._reposition(r,e),i?this._center(r):void 0},e.prototype._template=function(e,o){var n,r,i,s,a,u;return u=e.template,this._isOrphan(e)&&"[object Boolean]"!=={}.toString.call(e.orphan)&&(u=e.orphan),a=t.isFunction(u)?t(u(o,e)):t(u),n=a.find(".popover-navigation"),i=n.find('[data-role="prev"]'),r=n.find('[data-role="next"]'),s=n.find('[data-role="pause-resume"]'),this._isOrphan(e)&&a.addClass("orphan"),a.addClass("tour-"+this._options.name+" tour-"+this._options.name+"-"+o),e.reflex&&a.addClass("tour-"+this._options.name+"-reflex"),e.prev<0&&i.addClass("disabled").prop("disabled",!0).prop("tabindex",-1),e.next<0&&r.addClass("disabled").prop("disabled",!0).prop("tabindex",-1),e.duration||s.remove(),a.clone().wrap("<div>").parent().html()},e.prototype._reflexEvent=function(t){return"[object Boolean]"==={}.toString.call(t)?"click":t},e.prototype._focus=function(t,e,o){var n,r;return r=o?"end":"next",n=t.find("[data-role='"+r+"']"),e.on("shown.bs.popover",function(){return n.focus()})},e.prototype._reposition=function(e,n){var r,i,s,a,u,p,h;if(a=e[0].offsetWidth,i=e[0].offsetHeight,h=e.offset(),u=h.left,p=h.top,r=t(o).outerHeight()-h.top-e.outerHeight(),0>r&&(h.top=h.top+r),s=t("html").outerWidth()-h.left-e.outerWidth(),0>s&&(h.left=h.left+s),h.top<0&&(h.top=0),h.left<0&&(h.left=0),e.offset(h),"bottom"===n.placement||"top"===n.placement){if(u!==h.left)return this._replaceArrow(e,2*(h.left-u),a,"left")}else if(p!==h.top)return this._replaceArrow(e,2*(h.top-p),i,"top")},e.prototype._center=function(e){return e.css("top",t(window).outerHeight()/2-e.outerHeight()/2)},e.prototype._replaceArrow=function(t,e,o,n){return t.find(".arrow").css(n,e?50*(1-e/o)+"%":"")},e.prototype._scrollIntoView=function(e,o){var n,r,i,s,a,u,p;if(n=t(e.element),!n.length)return o();switch(r=t(window),a=n.offset().top,s=n.outerHeight(),p=r.height(),u=0,e.placement){case"top":u=Math.max(0,a-p/2);break;case"left":case"right":u=Math.max(0,a+s/2-p/2);break;case"bottom":u=Math.max(0,a+s-p/2)}return this._debug("Scroll into view. ScrollTop: "+u+". Element offset: "+a+". Window height: "+p+"."),i=0,t("body, html").stop(!0,!0).animate({scrollTop:Math.ceil(u)},function(t){return function(){return 2===++i?(o(),t._debug("Scroll into view.\nAnimation end element offset: "+n.offset().top+".\nWindow height: "+r.height()+".")):void 0}}(this))},e.prototype._onResize=function(e,o){return t(window).on("resize.tour-"+this._options.name,function(){return clearTimeout(o),o=setTimeout(e,100)})},e.prototype._initMouseNavigation=function(){var e;return e=this,t(o).off("click.tour-"+this._options.name,".popover.tour-"+this._options.name+" *[data-role='prev']").off("click.tour-"+this._options.name,".popover.tour-"+this._options.name+" *[data-role='next']").off("click.tour-"+this._options.name,".popover.tour-"+this._options.name+" *[data-role='end']").off("click.tour-"+this._options.name,".popover.tour-"+this._options.name+" *[data-role='pause-resume']").on("click.tour-"+this._options.name,".popover.tour-"+this._options.name+" *[data-role='next']",function(t){return function(e){return e.preventDefault(),t.next()}}(this)).on("click.tour-"+this._options.name,".popover.tour-"+this._options.name+" *[data-role='prev']",function(t){return function(e){return e.preventDefault(),t._current>0?t.prev():void 0}}(this)).on("click.tour-"+this._options.name,".popover.tour-"+this._options.name+" *[data-role='end']",function(t){return function(e){return e.preventDefault(),t.end()}}(this)).on("click.tour-"+this._options.name,".popover.tour-"+this._options.name+" *[data-role='pause-resume']",function(o){var n;return o.preventDefault(),n=t(this),n.text(e._paused?n.data("pause-text"):n.data("resume-text")),e._paused?e.resume():e.pause()})},e.prototype._initKeyboardNavigation=function(){return this._options.keyboard?t(o).on("keyup.tour-"+this._options.name,function(t){return function(e){if(e.which)switch(e.which){case 39:return e.preventDefault(),t._isLast()?t.next():t.end();case 37:if(e.preventDefault(),t._current>0)return t.prev()}}}(this)):void 0},e.prototype._makePromise=function(e){return e&&t.isFunction(e.then)?e:null},e.prototype._callOnPromiseDone=function(t,e,o){return t?t.then(function(t){return function(){return e.call(t,o)}}(this)):e.call(this,o)},e.prototype._showBackdrop=function(e){return this.backdrop.backgroundShown?void 0:(this.backdrop=t("<div>",{"class":"tour-backdrop"}),this.backdrop.backgroundShown=!0,t(e.backdropContainer).append(this.backdrop))},e.prototype._hideBackdrop=function(){return this._hideOverlayElement(),this._hideBackground()},e.prototype._hideBackground=function(){return this.backdrop&&this.backdrop.remove?(this.backdrop.remove(),this.backdrop.overlay=null,this.backdrop.backgroundShown=!1):void 0},e.prototype._showOverlayElement=function(e,o){var n,r,i;return r=t(e.element),n=t(e.backdropElement),!r||0===r.length||this.backdrop.overlayElementShown&&!o?void 0:(this.backdrop.overlayElementShown||(this.backdrop.$element=n.addClass("tour-step-backdrop"),this.backdrop.$background=t("<div>",{"class":"tour-step-background"}),this.backdrop.$background.appendTo(e.backdropContainer),this.backdrop.overlayElementShown=!0),i={width:n.innerWidth()-20,height:n.innerHeight(),offset:n.offset()},e.backdropPadding&&(i=this._applyBackdropPadding(e.backdropPadding,i)),this.backdrop.$background.width(i.width).height(i.height).offset(i.offset))},e.prototype._hideOverlayElement=function(){return this.backdrop.overlayElementShown?(this.backdrop.$element.removeClass("tour-step-backdrop"),this.backdrop.$background.remove(),this.backdrop.$element=null,this.backdrop.$background=null,this.backdrop.overlayElementShown=!1):void 0},e.prototype._applyBackdropPadding=function(t,e){return"object"==typeof t?(null==t.top&&(t.top=0),null==t.right&&(t.right=0),null==t.bottom&&(t.bottom=0),null==t.left&&(t.left=0),e.offset.top=e.offset.top-t.top,e.offset.left=e.offset.left-t.left,e.width=e.width+t.left+t.right,e.height=e.height+t.top+t.bottom):(e.offset.top=e.offset.top-t,e.offset.left=e.offset.left-t,e.width=e.width+2*t,e.height=e.height+2*t),e},e.prototype._clearTimer=function(){return window.clearTimeout(this._timer),this._timer=null,this._duration=null},e.prototype._getProtocol=function(t){return t=t.split("://"),t.length>1?t[0]:"http"},e.prototype._getHost=function(t){return t=t.split("//"),t=t.length>1?t[1]:t[0],t.split("/")[0]},e.prototype._getPath=function(t){return t.replace(/\/?$/,"").split("?")[0].split("#")[0]},e.prototype._getQuery=function(t){return this._getParams(t,"?")},e.prototype._getHash=function(t){return this._getParams(t,"#")},e.prototype._getParams=function(t,e){var o,n,r,i,s;if(n=t.split(e),1===n.length)return{};for(n=n[1].split("&"),r={},i=0,s=n.length;s>i;i++)o=n[i],o=o.split("="),r[o[0]]=o[1]||"";return r},e.prototype._equal=function(t,e){var o,n,r,i,s,a;if("[object Object]"==={}.toString.call(t)&&"[object Object]"==={}.toString.call(e)){if(n=Object.keys(t),r=Object.keys(e),n.length!==r.length)return!1;for(o in t)if(i=t[o],!this._equal(e[o],i))return!1;return!0}if("[object Array]"==={}.toString.call(t)&&"[object Array]"==={}.toString.call(e)){if(t.length!==e.length)return!1;for(o=s=0,a=t.length;a>s;o=++s)if(i=t[o],!this._equal(i,e[o]))return!1;return!0}return t===e},e}()});
// Generated by CoffeeScript 1.12.5
(function() {
  var $, MentionsBase, MentionsContenteditable, MentionsInput, Selection, entityMap, escapeHtml, escapeRegExp, namespace,
    bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    extend = function(child, parent) { for (var key in parent) { if (hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    hasProp = {}.hasOwnProperty,
    slice = [].slice;

  namespace = "mentionsInput";

  if (typeof module === "object" && typeof module.exports === "object") {
    $ = require("jquery");
    require("jquery-ui/ui/widgets/autocomplete");
  } else {
    $ = window.jQuery;
  }

  Selection = {
    get: function(input) {
      return {
        start: input[0].selectionStart,
        end: input[0].selectionEnd
      };
    },
    set: function(input, start, end) {
      if (end == null) {
        end = start;
      }
      if (input[0].selectionStart) {
        input[0].selectStart = start;
        return input[0].selectionEnd = end;
      }
    }
  };

  entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    "\"": "&quot;",
    "'": "&#39;",
    "/": "&#x2F;"
  };

  escapeHtml = function(text) {
    return text.replace(/[&<>"'\/]/g, function(s) {
      return entityMap[s];
    });
  };

  escapeRegExp = function(str) {
    var specials;
    specials = /[.*+?|()\[\]{}\\$^]/g;
    return str.replace(specials, "\\$&");
  };

  $.widget("ui.areacomplete", $.ui.autocomplete, {
    options: $.extend({}, $.ui.autocomplete.prototype.options, {
      matcher: "(\\b[^,]*)",
      suffix: ''
    }),
    _create: function() {
      this.overriden = {
        select: this.options.select,
        focus: this.options.focus
      };
      this.options.select = $.proxy(this.selectCallback, this);
      this.options.focus = $.proxy(this.focusCallback, this);
      $.ui.autocomplete.prototype._create.call(this);
      return this.matcher = new RegExp(this.options.matcher + '$');
    },
    selectCallback: function(event, ui) {
      var after, before, newval, value;
      value = this._value();
      before = value.substring(0, this.start);
      after = value.substring(this.end);
      newval = ui.item.value + this.options.suffix;
      value = before + newval + after;
      this._value(value);
      Selection.set(this.element, before.length + newval.length);
      if (this.overriden.select) {
        ui.item.pos = this.start;
        this.overriden.select(event, ui);
      }
      return false;
    },
    focusCallback: function() {
      if (this.overriden.focus) {
        return this.overriden.focus(event, ui);
      }
      return false;
    },
    search: function(value, event) {
      var match, pos;
      if (!value) {
        value = this._value();
        pos = Selection.get(this.element).start;
        value = value.substring(0, pos);
        match = this.matcher.exec(value);
        if (!match) {
          return '';
        }
        this.start = match.index;
        this.end = match.index + match[0].length;
        this.searchTerm = match[1];
      }
      return $.ui.autocomplete.prototype.search.call(this, this.searchTerm, event);
    },
    _renderItem: function(ul, item) {
      var anchor, li, regexp, value;
      li = $('<li>');
      anchor = $('<a>').appendTo(li);
      if (item.image) {
        anchor.append("<img src=\"" + item.image + "\" />");
      }
      regexp = new RegExp("(" + escapeRegExp(this.searchTerm) + ")", "gi");
      display_name = item.display_name.replace(regexp, "<strong>$&</strong>");
      company_name = item.company_name;
      tag_all_user = item.user_id == '00000000-0000-0000-0000-000000000000' ? '<div class="tag-comment-all"></div>':'';
      anchor.append(tag_all_user+display_name+'<span>'+item.user_status+'</span>'+'<p>'+company_name+'</p>');
      ul.addClass('user-tagging-ul');
      return li.appendTo(ul);
    }
  });

  $.widget("ui.editablecomplete", $.ui.areacomplete, {
    options: $.extend({}, $.ui.areacomplete.prototype.options, {
      showAtCaret: false
    }),
    selectCallback: function(event, ui) {
      var mention, pos;
      pos = {
        start: this.start,
        end: this.end
      };
      if (this.overriden.select) {
        ui.item.pos = pos;
        if (this.overriden.select(event, ui) === false) {
          return false;
        }
      }
      mention = document.createTextNode(ui.item.value);
      insertMention(mention, pos, this.options.suffix);
      this.element.change();
      return false;
    },
    search: function(value, event) {

      var match, node, pos, sel;
      if (!value) {
        sel = window.getSelection();
        node = sel.focusNode;
        value = node.textContent;
        pos = sel.focusOffset;
        value = value.substring(0, pos);
        match = this.matcher.exec(value);
        search_text_length = node.textContent.length-1;
        if(node.textContent == '@' || (node.textContent[search_text_length] == '@' && node.textContent[search_text_length-1] == ' ') || $('div[contenteditable=true]:focus').text().trim() == '@'){
          match = this.matcher.exec(value+' ');
        }
        if (!match || (this.start>0 && !(value.indexOf(' @')>=0)) ) {
          return '';
        }
        this.start = match.index;
        this.end = match.index + match[0].length;
        this._setDropdownPosition(node);
        this.searchTerm = match[1];
      }
      return $.ui.autocomplete.prototype.search.call(this, this.searchTerm, event);
    },
    _setDropdownPosition: function(node) {
      var boundary, posX, posY, rect;
      if (this.options.showAtCaret) {
        boundary = document.createRange();
        boundary.setStart(node, this.start);
        boundary.collapse(true);
        rect = boundary.getClientRects()[0];
        posX = rect.left + (window.scrollX || window.pageXOffset);
        posY = rect.top + rect.height + (window.scrollY || window.pageYOffset);
        this.options.position.of = document;
        return this.options.position.at = "left+" + posX + " top+" + posY;
      }
    }
  });

  MentionsBase = (function() {
    MentionsBase.prototype.marker = '\u200B';

    function MentionsBase(input1, options) {
      this.input = input1;
      this.options = $.extend({}, this.settings, options);
      if (!this.options.source) {
        this.options.source = this.input.data('source') || [];
      }
    }

    MentionsBase.prototype._getMatcher = function() {
      var allowedChars;
      allowedChars = '[^' + this.options.trigger + ']';
      return '[' + this.options.trigger + '](' + allowedChars + '{0,20})';
    };

    MentionsBase.prototype._markupMention = function(mention) {
      return "@[" + mention.name + "](" + mention.uid + ")";
    };

    return MentionsBase;

  })();

  MentionsInput = (function(superClass) {
    var mimicProperties;

    extend(MentionsInput, superClass);

    mimicProperties = ['backgroundColor', 'marginTop', 'marginBottom', 'marginLeft', 'marginRight', 'paddingTop', 'paddingBottom', 'paddingLeft', 'paddingRight', 'borderTopWidth', 'borderLeftWidth', 'borderBottomWidth', 'borderRightWidth', 'fontSize', 'fontStyle', 'fontFamily', 'fontWeight', 'lineHeight', 'height', 'boxSizing'];

    function MentionsInput(input1, options) {
      var container;
      this.input = input1;
      this._updateHScroll = bind(this._updateHScroll, this);
      this._updateVScroll = bind(this._updateVScroll, this);
      this._updateValue = bind(this._updateValue, this);
      this._onSelect = bind(this._onSelect, this);
      this._addMention = bind(this._addMention, this);
      this._updateMentions = bind(this._updateMentions, this);
      this._update = bind(this._update, this);
      this.settings = {
        trigger: '@',
        widget: 'areacomplete',
        suffix: '',
        autocomplete: {
          autoFocus: true,
          delay: 0
        }
      };
      MentionsInput.__super__.constructor.call(this, this.input, options);
      this.mentions = [];
      this.input.addClass('input');
      container = $('<div>', {
        'class': 'mentions-input'
      });
      container.css('display', this.input.css('display'));
      this.container = this.input.wrapAll(container).parent();
      this.hidden = this._createHidden();
      this.highlighter = this._createHighlighter();
      this.highlighterContent = $('div', this.highlighter);
      this.input.focus((function(_this) {
        return function() {
          return _this.highlighter.addClass('focus');
        };
      })(this)).blur((function(_this) {
        return function() {
          return _this.highlighter.removeClass('focus');
        };
      })(this));
      options = $.extend({
        matcher: this._getMatcher(),
        select: this._onSelect,
        suffix: this.options.suffix,
        source: this.options.source,
        appendTo: this.input.parent()
      }, this.options.autocomplete);
      this.autocomplete = this.input[this.options.widget](options);
      this._setValue(this.input.val());
      this._initEvents();
    }

    MentionsInput.prototype._initEvents = function() {
      var tagName;
      this.input.on("input." + namespace + " change." + namespace, this._update);
      tagName = this.input.prop("tagName");
      if (tagName === "INPUT") {
        this.input.on("focus." + namespace, (function(_this) {
          return function() {
            return _this.interval = setInterval(_this._updateHScroll, 10);
          };
        })(this));
        return this.input.on("blur." + namespace, (function(_this) {
          return function() {
            setTimeout(_this._updateHScroll, 10);
            return clearInterval(_this.interval);
          };
        })(this));
      } else if (tagName === "TEXTAREA") {
        this.input.on("scroll." + namespace, ((function(_this) {
          return function() {
            return setTimeout(_this._updateVScroll, 10);
          };
        })(this)));
        return this.input.on("resize." + namespace, ((function(_this) {
          return function() {
            return setTimeout(_this._updateVScroll, 10);
          };
        })(this)));
      }
    };

    MentionsInput.prototype._setValue = function(value) {
      var match, mentionRE, offset;
      offset = 0;
      mentionRE = /@\[([^\]]+)\]\(([^ \)]+)\)/g;
      this.value = value.replace(mentionRE, '$1');
      this.input.val(this.value);
      match = mentionRE.exec(value);
      while (match) {
        this._addMention({
          name: match[1],
          uid: match[2],
          pos: match.index - offset
        });
        offset += match[2].length + 5;
        match = mentionRE.exec(value);
      }
      return this._updateValue();
    };

    MentionsInput.prototype._createHidden = function() {
      var hidden;
      hidden = $('<input>', {
        type: 'hidden',
        name: this.input.attr('name')
      });
      $.each(this.input.data(), function(name, value) {
        return hidden.attr("data-" + name.replace(/([a-zA-Z])(?=[A-Z])/g, '$1-').toLowerCase(), JSON.stringify(value));
      });
      this.input.removeData();
      hidden.appendTo(this.container);
      this.input.removeAttr('name');
      return hidden;
    };

    MentionsInput.prototype._createHighlighter = function() {
      var content, highlighter, j, len, property;
      highlighter = $('<div>', {
        'class': 'highlighter'
      });
      if (this.input.prop("tagName") === "INPUT") {
        highlighter.css('whiteSpace', 'pre');
      } else {
        highlighter.css('whiteSpace', 'pre-wrap');
        highlighter.css('wordWrap', 'break-word');
      }
      content = $('<div>', {
        'class': 'highlighter-content'
      });
      highlighter.append(content).prependTo(this.container);
      for (j = 0, len = mimicProperties.length; j < len; j++) {
        property = mimicProperties[j];
        highlighter.css(property, this.input.css(property));
      }
      this.input.css('backgroundColor', 'transparent');
      return highlighter;
    };

    MentionsInput.prototype._update = function() {
      this._updateMentions();
      return this._updateValue();
    };

    MentionsInput.prototype._updateMentions = function() {
      var change, cursor, diff, i, j, k, len, mention, piece, ref, update_pos, value;
      value = this.input.val();
      diff = diffChars(this.value, value);
      update_pos = (function(_this) {
        return function(cursor, delta) {
          var j, len, mention, ref, results;
          ref = _this.mentions;
          results = [];
          for (j = 0, len = ref.length; j < len; j++) {
            mention = ref[j];
            if (mention.pos >= cursor) {
              results.push(mention.pos += delta);
            } else {
              results.push(void 0);
            }
          }
          return results;
        };
      })(this);
      cursor = 0;
      for (j = 0, len = diff.length; j < len; j++) {
        change = diff[j];
        if (change.added) {
          update_pos(cursor, change.count);
        } else if (change.removed) {
          update_pos(cursor, -change.count);
        }
        if (!change.removed) {
          cursor += change.count;
        }
      }
      ref = this.mentions.slice(0);
      for (i = k = ref.length - 1; k >= 0; i = k += -1) {
        mention = ref[i];
        piece = value.substring(mention.pos, mention.pos + mention.name.length);
        if (mention.name !== piece) {
          this.mentions.splice(i, 1);
        }
      }
      return this.value = value;
    };

    MentionsInput.prototype._addMention = function(mention) {
      this.mentions.push(mention);
      return this.mentions.sort(function(a, b) {
        return a.pos - b.pos;
      });
    };

    MentionsInput.prototype._onSelect = function(event, ui) {
      this._updateMentions();
      this._addMention({
        name: ui.item.value,
        pos: ui.item.pos,
        uid: ui.item.uid
      });
      return this._updateValue();
    };

    MentionsInput.prototype._updateValue = function() {
      var cursor, hdContent, hlContent, j, len, mention, piece, ref, value;
      value = this.input.val();
      hlContent = [];
      hdContent = [];
      cursor = 0;
      ref = this.mentions;
      for (j = 0, len = ref.length; j < len; j++) {
        mention = ref[j];
        piece = value.substring(cursor, mention.pos);
        hlContent.push(escapeHtml(piece));
        hdContent.push(piece);
        hlContent.push("<strong>" + mention.name + "</strong>");
        hdContent.push(this._markupMention(mention));
        cursor = mention.pos + mention.name.length;
      }
      piece = value.substring(cursor);
      this.highlighterContent.html(hlContent.join('') + escapeHtml(piece));
      return this.hidden.val(hdContent.join('') + piece);
    };

    MentionsInput.prototype._updateVScroll = function() {
      var scrollTop;
      scrollTop = this.input.scrollTop();
      this.highlighterContent.css({
        top: "-" + scrollTop + "px"
      });
      return this.highlighter.height(this.input.height());
    };

    MentionsInput.prototype._updateHScroll = function() {
      var scrollLeft;
      scrollLeft = this.input.scrollLeft();
      return this.highlighterContent.css({
        left: "-" + scrollLeft + "px"
      });
    };

    MentionsInput.prototype._replaceWithSpaces = function(value, what) {
      return value.replace(what, Array(what.length).join(' '));
    };

    MentionsInput.prototype._cutChar = function(value, index) {
      return value.substring(0, index) + value.substring(index + 1);
    };

    MentionsInput.prototype.setValue = function() {
      var j, len, piece, pieces, value;
      pieces = 1 <= arguments.length ? slice.call(arguments, 0) : [];
      value = '';
      for (j = 0, len = pieces.length; j < len; j++) {
        piece = pieces[j];
        if (typeof piece === 'string') {
          value += piece;
        } else {
          value += this._markupMention(piece);
        }
      }
      return this._setValue(value);
    };

    MentionsInput.prototype.getValue = function() {
      return this.hidden.val();
    };

    MentionsInput.prototype.getRawValue = function() {
      return this.input.val().replace(this.marker, '');
    };

    MentionsInput.prototype.getMentions = function() {
      return this.mentions;
    };

    MentionsInput.prototype.clear = function() {
      this.input.val('');
      return this._update();
    };

    MentionsInput.prototype.destroy = function() {
      this.input.areacomplete("destroy");
      this.input.off("." + namespace).attr('name', this.hidden.attr('name'));
      return this.container.replaceWith(this.input);
    };

    return MentionsInput;

  })(MentionsBase);

  MentionsContenteditable = (function(superClass) {
    var insertMention, mentionTpl;

    extend(MentionsContenteditable, superClass);

    MentionsContenteditable.prototype.selector = '[data-mention]';

    function MentionsContenteditable(input1, options) {
      this.input = input1;
      this._onSelect = bind(this._onSelect, this);
      this._addMention = bind(this._addMention, this);
      this.settings = {
        trigger: '@',
        widget: 'editablecomplete',
        autocomplete: {
          autoFocus: true,
          delay: 0
        }
      };
      MentionsContenteditable.__super__.constructor.call(this, this.input, options);
      options = $.extend({
        matcher: this._getMatcher(),
        suffix: this.marker,
        select: this._onSelect,
        source: this.options.source,
        showAtCaret: this.options.showAtCaret
      }, this.options.autocomplete);
      this.autocomplete = this.input[this.options.widget](options);
      this._setValue(this.input.html());
      this._initEvents();
    }

    mentionTpl = function(mention) {
      return '<a style="text-decoration:none; color:#0D47A1" href="#!" onclick="liked_info(this);" data-id='+mention.user_id+'><span class="user_tag_link">'+mention.value+'</span></a>';
    };

    insertMention = function(mention, pos, suffix) {
      var node, range, selection;
      selection = window.getSelection();
      node = selection.focusNode;
      range = selection.getRangeAt(0);
      range.setStart(node, pos.start);
      var node_difference = pos.end-node.length;
      if(node_difference == 1) {
          range.setEnd(node, node.length==pos.end?pos.end:pos.end-1);  
      } else {
          range.setEnd(node, node.length==pos.end?pos.end:pos.end);
      }
      
      range.deleteContents();
      range.insertNode(mention);
      if (suffix) {
        suffix = document.createTextNode(suffix);
        $(suffix).insertAfter(mention);
        range.setStartAfter(suffix);
      } else {
        range.setStartAfter(mention);
      }
      range.collapse(true);
      selection.removeAllRanges();
      selection.addRange(range);
      return mention;
    };

    MentionsContenteditable.prototype._initEvents = function() {
      return this.input.find(this.selector).each((function(_this) {
        return function(i, el) {
          return _this._watch(el);
        };
      })(this));
    };

    MentionsContenteditable.prototype._setValue = function(value) {
      var mentionRE;
      mentionRE = /@\[([^\]]+)\]\(([^ \)]+)\)/g;
      value = value.replace(mentionRE, (function(_this) {
        return function(match, value, uid) {
          return mentionTpl({
            value: value,
            uid: uid
          }) + _this.marker;
        };
      })(this));
      return this.input.html(value);
    };

    MentionsContenteditable.prototype._addMention = function(data) {
      var mention, mentionNode;
      mentionNode = $(mentionTpl(data))[0];
      mention = insertMention(mentionNode, data.pos, this.marker);
      return this._watch(mention);
    };

    MentionsContenteditable.prototype._onSelect = function(event, ui) {
      this._addMention(ui.item);
      this.input.trigger("change." + namespace);
      return false;
    };

    MentionsContenteditable.prototype._watch = function(mention) {
      return mention.addEventListener('DOMCharacterDataModified', function(e) {
        var offset, range, sel, text;
        if (e.newValue !== e.prevValue) {
          return $(mention).remove();
        }
      });
    };

    MentionsContenteditable.prototype.update = function() {
      this._initValue();
      this._initEvents();
      return this.input.focus();
    };

    MentionsContenteditable.prototype.editReady = function(element) {
      var node = this;
      $(element).find('a').each(function(){
        node._watch(this);
      });
    };

    MentionsContenteditable.prototype.setValue = function() {
      var j, len, piece, pieces, value;
      pieces = 1 <= arguments.length ? slice.call(arguments, 0) : [];
      value = '';
      for (j = 0, len = pieces.length; j < len; j++) {
        piece = pieces[j];
        if (typeof piece === 'string') {
          value += piece;
        } else {
          value += this._markupMention(piece);
        }
      }
      this._setValue(value);
      this._initEvents();
      return this.input.focus();
    };

    MentionsContenteditable.prototype.getValue = function() {
      var markupMention, value;
      value = this.input.clone();
      markupMention = this._markupMention;
      $(this.selector, value).replaceWith(function() {
        var name, uid;
        uid = $(this).data('mention');
        name = $(this).text();
        return markupMention({
          name: name,
          uid: uid
        });
      });
      return value.html().replace(this.marker, '');
    };

    MentionsContenteditable.prototype.getMentions = function() {
      var mentions;
      mentions = [];
      $(this.selector, this.input).each(function() {
        return mentions.push({
          uid: $(this).data('mention'),
          name: $(this).text()
        });
      });
      return mentions;
    };

    MentionsContenteditable.prototype.clear = function() {
      return this.input.html('');
    };

    MentionsContenteditable.prototype.destroy = function() {
      this.input.editablecomplete("destroy");
      this.input.off("." + namespace);
      return this.input.html(this.getValue());
    };

    return MentionsContenteditable;

  })(MentionsBase);

  
/*
    Copyright (c) 2009-2011, Kevin Decker <kpdecker@gmail.com>
*/
function diffChars(oldString, newString) {
  // Handle the identity case (this is due to unrolling editLength == 0
  if (newString === oldString) {
    return [{ value: newString }];
  }
  if (!newString) {
    return [{ value: oldString, removed: true }];
  }
  if (!oldString) {
    return [{ value: newString, added: true }];
  }

  var newLen = newString.length, oldLen = oldString.length;
  var maxEditLength = newLen + oldLen;
  var bestPath = [{ newPos: -1, components: [] }];

  // Seed editLength = 0, i.e. the content starts with the same values
  var oldPos = extractCommon(bestPath[0], newString, oldString, 0);
  if (bestPath[0].newPos+1 >= newLen && oldPos+1 >= oldLen) {
    // Identity per the equality and tokenizer
    return [{value: newString}];
  }

  // Main worker method. checks all permutations of a given edit length for acceptance.
  function execEditLength() {
    for (var diagonalPath = -1*editLength; diagonalPath <= editLength; diagonalPath+=2) {
      var basePath;
      var addPath = bestPath[diagonalPath-1],
          removePath = bestPath[diagonalPath+1];
      oldPos = (removePath ? removePath.newPos : 0) - diagonalPath;
      if (addPath) {
        // No one else is going to attempt to use this value, clear it
        bestPath[diagonalPath-1] = undefined;
      }

      var canAdd = addPath && addPath.newPos+1 < newLen;
      var canRemove = removePath && 0 <= oldPos && oldPos < oldLen;
      if (!canAdd && !canRemove) {
        // If this path is a terminal then prune
        bestPath[diagonalPath] = undefined;
        continue;
      }

      // Select the diagonal that we want to branch from. We select the prior
      // path whose position in the new string is the farthest from the origin
      // and does not pass the bounds of the diff graph
      if (!canAdd || (canRemove && addPath.newPos < removePath.newPos)) {
        basePath = clonePath(removePath);
        pushComponent(basePath.components, undefined, true);
      } else {
        basePath = addPath;   // No need to clone, we've pulled it from the list
        basePath.newPos++;
        pushComponent(basePath.components, true, undefined);
      }

      var oldPos = extractCommon(basePath, newString, oldString, diagonalPath);

      // If we have hit the end of both strings, then we are done
      if (basePath.newPos+1 >= newLen && oldPos+1 >= oldLen) {
        return buildValues(basePath.components, newString, oldString);
      } else {
        // Otherwise track this path as a potential candidate and continue.
        bestPath[diagonalPath] = basePath;
      }
    }

    editLength++;
  }

  // Performs the length of edit iteration. Is a bit fugly as this has to support the
  // sync and async mode which is never fun. Loops over execEditLength until a value
  // is produced.
  var editLength = 1;
  while(editLength <= maxEditLength) {
    var ret = execEditLength();
    if (ret) {
      return ret;
    }
  }
}

function buildValues(components, newString, oldString) {
    var componentPos = 0,
        componentLen = components.length,
        newPos = 0,
        oldPos = 0;

    for (; componentPos < componentLen; componentPos++) {
      var component = components[componentPos];
      if (!component.removed) {
        component.value = newString.slice(newPos, newPos + component.count);
        newPos += component.count;

        // Common case
        if (!component.added) {
          oldPos += component.count;
        }
      } else {
        component.value = oldString.slice(oldPos, oldPos + component.count);
        oldPos += component.count;
      }
    }

    return components;
  }

function pushComponent(components, added, removed) {
  var last = components[components.length-1];
  if (last && last.added === added && last.removed === removed) {
    // We need to clone here as the component clone operation is just
    // as shallow array clone
    components[components.length-1] = {count: last.count + 1, added: added, removed: removed };
  } else {
    components.push({count: 1, added: added, removed: removed });
  }
}

function extractCommon(basePath, newString, oldString, diagonalPath) {
  var newLen = newString.length,
      oldLen = oldString.length,
      newPos = basePath.newPos,
      oldPos = newPos - diagonalPath,

      commonCount = 0;
  while (newPos+1 < newLen && oldPos+1 < oldLen && newString[newPos+1] == oldString[oldPos+1]) {
    newPos++;
    oldPos++;
    commonCount++;
  }

  if (commonCount) {
    basePath.components.push({count: commonCount});
  }

  basePath.newPos = newPos;
  return oldPos;
}

function clonePath(path) {
    return { newPos: path.newPos, components: path.components.slice(0) };
};

  $.fn[namespace] = function() {
    var args, options, returnValue;
    options = arguments[0], args = 2 <= arguments.length ? slice.call(arguments, 1) : [];
    returnValue = this;
    this.each(function() {
      var instance, ref;
      if (typeof options === 'string' && options.charAt(0) !== '_') {
        instance = $(this).data('mentionsInput');
        if (options in instance) {
          return returnValue = instance[options].apply(instance, args);
        }
      } else {
        if ((ref = this.tagName) === 'INPUT' || ref === 'TEXTAREA') {
          return $(this).data('mentionsInput', new MentionsInput($(this), options));
        } else if (this.contentEditable === "true") {
          return $(this).data('mentionsInput', new MentionsContenteditable($(this), options));
        }
      }
    });
    return returnValue;
  };

}).call(this);
function viewPostAttachment(url, url_type, file_name, post_id, file_size, file_object) {
    updateFileViews(post_id);
    viewAttachment(url, url_type, file_name, file_size, file_object);
}

function viewAttachment(url, url_type, file_name, file_size, file_object) {
    if((file_size/1024)/1024 > 10 && url_type != 'video/mp4') {
        downloadFile(url, url_type, file_name);
    } else {    	
        viewFile(url, url_type, file_name, file_size, file_object);
    }
}

function checkDevice(){
	if(isAppleDevice()){
		swal({
			title: 'Unable to download',
	        text: 'This file type is not supported by your device',
	        confirmButtonText: 'close',
	        customClass: 'simple-alert'
		});
		e.preventDefault();
	}
}

function downloadFile(url, url_type, file_name){
	checkDevice();
	signed_url = signedUrl(url, false, file_name);
	if(signed_url.cloud === ''){
       alert('This file type is being converted and may take a few seconds to appear.');
       return false;
	}

	window.location = signed_url.cloud;
}

function updateFileViews(post_id){
	$.ajax({
	    type: 'GET',
	    url: baseurl+'/view_file?post_id='+post_id,
	});
}
	
function viewFile(url, url_type, file_name, file_size, file_object) {
    if (url_type === 'url') {
        var win = window.open(url, '_blank');
        win.focus();
        return false;
    }
    signed_url = signedUrl(url, false, file_name);
    if (signed_url.cloud === '') {
        alert('This file type is being converted and may take a few seconds to appear.');
        return false;
    }
    viewer_class = getViewerClass(signed_url, url_type, file_size);
    signed_url.file_info_cloud = documentViewer(signed_url);
    openFile(viewer_class, signed_url, file_name, file_object);
}

function documentViewer(file_info){
    var file_info_cloud = file_info.file_url;
    var file_ext = file_info.file_ext.toLowerCase();
	if(['pptx', 'ppt', 'ppsx', 'pps', 'potx', 'ppsm', 'docx', 'dotx'].indexOf(file_ext)>=0) file_info_cloud = 'https://view.officeapps.live.com/op/view.aspx?src='+encodeURIComponent(file_info.cloud);
	else if(['pdf'].indexOf(file_ext)>=0) file_info_cloud = baseurl+"/pdf_viewer/web/viewer.html?file="+file_info.document_viewer;
	return file_info_cloud;
}

function getViewerClass(file_info, url_type, file_size) {
	image = ['png','gif','jpg','jpeg'];
	video = ['mp4'];
	pdf_docs = ['pdf'];
	ppt_word_docs = ['ppt','pptx', 'ppsx', 'pps', 'potx', 'ppsm', 'docx', 'dotx'];
	excel_docs = ['xlsx', 'xlsb', 'xls', 'xlsm'];
	viewer = false;
        var file_ext = file_info.file_ext.toLowerCase();

	if(image.indexOf(file_ext)>=0) viewer = 'image-viewer';
	else if(video.indexOf(file_ext)>=0 || url_type === 'video/mp4') viewer = 'video-viewer';
	else if( 
                (ppt_word_docs.indexOf(file_ext) >=0 && ((file_size/1024)/1024 < 10) )
                || (pdf_docs.indexOf(file_ext)>=0 && ((file_size/1024)/1024 < 8) )
                ) {
            viewer = 'docs-viewer';
        } else {
		checkDevice();
		viewer = null;
		window.location = file_info.cloud;
	}
	return viewer;
}

function openFile(viewer_class, signed_url, file_name, file_object){
	if(isAppleDevice()) $('a.file-download').remove();

    if(file_object){
        $('.'+viewer_class).find('.file-download').attr('media-id', file_object.id);
    }

	$('h4.modal-title').html(decodeURIComponent(file_name));
	$('.'+viewer_class).find('.file-download').attr('href', signed_url.cloud);
	$('.'+viewer_class).find('.modal-loader').show();
	$('.'+viewer_class).modal('show');
	$('.'+viewer_class).find('.file-source').attr('src', signed_url.file_info_cloud);
	if($('.'+viewer_class).find('video').length){
		$('.'+viewer_class).find('video').load();
		$('.'+viewer_class).find('video')[0].play();
		$('.'+viewer_class).find('.modal-loader').hide();
	}

}

$(document).on("click", ".full_screen_toggle", function () {
	$(this).closest('.modal-dialog').toggleClass('full-width-doc');
	$(this).find('i').toggleClass('fa-expand fa-compress');
});

var comment_post_id;

function pullPostId(post_id){
	post_id = $('div.comment-area:not(.edit-comment-area)').attr('id');
	return post_id.replace('comment_input_area', '');
}

function mentionsComment(element, post_id){
	$(element).mentionsInput({
        source:baseurl+'/mention_user?post_id='+post_id,
        showAtCaret: true
    });
	$(element).mentionsInput('editReady', element);
}

$(document).on('click', 'div.comment-area:not(.edit-comment-area)', function(){
    if(comment_post_id == $(this).attr('data-postid')) return;
    comment_post_id = $(this).attr('data-postid');
	mentionsComment($(this), comment_post_id);
});
/**!

 @license
 handlebars v4.0.11

Copyright (C) 2011-2017 by Yehuda Katz

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["Handlebars"] = factory();
	else
		root["Handlebars"] = factory();
})(this, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};

/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {

/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;

/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};

/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);

/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;

/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}


/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;

/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;

/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";

/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;

	var _handlebarsRuntime = __webpack_require__(2);

	var _handlebarsRuntime2 = _interopRequireDefault(_handlebarsRuntime);

	// Compiler imports

	var _handlebarsCompilerAst = __webpack_require__(35);

	var _handlebarsCompilerAst2 = _interopRequireDefault(_handlebarsCompilerAst);

	var _handlebarsCompilerBase = __webpack_require__(36);

	var _handlebarsCompilerCompiler = __webpack_require__(41);

	var _handlebarsCompilerJavascriptCompiler = __webpack_require__(42);

	var _handlebarsCompilerJavascriptCompiler2 = _interopRequireDefault(_handlebarsCompilerJavascriptCompiler);

	var _handlebarsCompilerVisitor = __webpack_require__(39);

	var _handlebarsCompilerVisitor2 = _interopRequireDefault(_handlebarsCompilerVisitor);

	var _handlebarsNoConflict = __webpack_require__(34);

	var _handlebarsNoConflict2 = _interopRequireDefault(_handlebarsNoConflict);

	var _create = _handlebarsRuntime2['default'].create;
	function create() {
	  var hb = _create();

	  hb.compile = function (input, options) {
	    return _handlebarsCompilerCompiler.compile(input, options, hb);
	  };
	  hb.precompile = function (input, options) {
	    return _handlebarsCompilerCompiler.precompile(input, options, hb);
	  };

	  hb.AST = _handlebarsCompilerAst2['default'];
	  hb.Compiler = _handlebarsCompilerCompiler.Compiler;
	  hb.JavaScriptCompiler = _handlebarsCompilerJavascriptCompiler2['default'];
	  hb.Parser = _handlebarsCompilerBase.parser;
	  hb.parse = _handlebarsCompilerBase.parse;

	  return hb;
	}

	var inst = create();
	inst.create = create;

	_handlebarsNoConflict2['default'](inst);

	inst.Visitor = _handlebarsCompilerVisitor2['default'];

	inst['default'] = inst;

	exports['default'] = inst;
	module.exports = exports['default'];

/***/ }),
/* 1 */
/***/ (function(module, exports) {

	"use strict";

	exports["default"] = function (obj) {
	  return obj && obj.__esModule ? obj : {
	    "default": obj
	  };
	};

	exports.__esModule = true;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireWildcard = __webpack_require__(3)['default'];

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;

	var _handlebarsBase = __webpack_require__(4);

	var base = _interopRequireWildcard(_handlebarsBase);

	// Each of these augment the Handlebars object. No need to setup here.
	// (This is done to easily share code between commonjs and browse envs)

	var _handlebarsSafeString = __webpack_require__(21);

	var _handlebarsSafeString2 = _interopRequireDefault(_handlebarsSafeString);

	var _handlebarsException = __webpack_require__(6);

	var _handlebarsException2 = _interopRequireDefault(_handlebarsException);

	var _handlebarsUtils = __webpack_require__(5);

	var Utils = _interopRequireWildcard(_handlebarsUtils);

	var _handlebarsRuntime = __webpack_require__(22);

	var runtime = _interopRequireWildcard(_handlebarsRuntime);

	var _handlebarsNoConflict = __webpack_require__(34);

	var _handlebarsNoConflict2 = _interopRequireDefault(_handlebarsNoConflict);

	// For compatibility and usage outside of module systems, make the Handlebars object a namespace
	function create() {
	  var hb = new base.HandlebarsEnvironment();

	  Utils.extend(hb, base);
	  hb.SafeString = _handlebarsSafeString2['default'];
	  hb.Exception = _handlebarsException2['default'];
	  hb.Utils = Utils;
	  hb.escapeExpression = Utils.escapeExpression;

	  hb.VM = runtime;
	  hb.template = function (spec) {
	    return runtime.template(spec, hb);
	  };

	  return hb;
	}

	var inst = create();
	inst.create = create;

	_handlebarsNoConflict2['default'](inst);

	inst['default'] = inst;

	exports['default'] = inst;
	module.exports = exports['default'];

/***/ }),
/* 3 */
/***/ (function(module, exports) {

	"use strict";

	exports["default"] = function (obj) {
	  if (obj && obj.__esModule) {
	    return obj;
	  } else {
	    var newObj = {};

	    if (obj != null) {
	      for (var key in obj) {
	        if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key];
	      }
	    }

	    newObj["default"] = obj;
	    return newObj;
	  }
	};

	exports.__esModule = true;

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;
	exports.HandlebarsEnvironment = HandlebarsEnvironment;

	var _utils = __webpack_require__(5);

	var _exception = __webpack_require__(6);

	var _exception2 = _interopRequireDefault(_exception);

	var _helpers = __webpack_require__(10);

	var _decorators = __webpack_require__(18);

	var _logger = __webpack_require__(20);

	var _logger2 = _interopRequireDefault(_logger);

	var VERSION = '4.0.11';
	exports.VERSION = VERSION;
	var COMPILER_REVISION = 7;

	exports.COMPILER_REVISION = COMPILER_REVISION;
	var REVISION_CHANGES = {
	  1: '<= 1.0.rc.2', // 1.0.rc.2 is actually rev2 but doesn't report it
	  2: '== 1.0.0-rc.3',
	  3: '== 1.0.0-rc.4',
	  4: '== 1.x.x',
	  5: '== 2.0.0-alpha.x',
	  6: '>= 2.0.0-beta.1',
	  7: '>= 4.0.0'
	};

	exports.REVISION_CHANGES = REVISION_CHANGES;
	var objectType = '[object Object]';

	function HandlebarsEnvironment(helpers, partials, decorators) {
	  this.helpers = helpers || {};
	  this.partials = partials || {};
	  this.decorators = decorators || {};

	  _helpers.registerDefaultHelpers(this);
	  _decorators.registerDefaultDecorators(this);
	}

	HandlebarsEnvironment.prototype = {
	  constructor: HandlebarsEnvironment,

	  logger: _logger2['default'],
	  log: _logger2['default'].log,

	  registerHelper: function registerHelper(name, fn) {
	    if (_utils.toString.call(name) === objectType) {
	      if (fn) {
	        throw new _exception2['default']('Arg not supported with multiple helpers');
	      }
	      _utils.extend(this.helpers, name);
	    } else {
	      this.helpers[name] = fn;
	    }
	  },
	  unregisterHelper: function unregisterHelper(name) {
	    delete this.helpers[name];
	  },

	  registerPartial: function registerPartial(name, partial) {
	    if (_utils.toString.call(name) === objectType) {
	      _utils.extend(this.partials, name);
	    } else {
	      if (typeof partial === 'undefined') {
	        throw new _exception2['default']('Attempting to register a partial called "' + name + '" as undefined');
	      }
	      this.partials[name] = partial;
	    }
	  },
	  unregisterPartial: function unregisterPartial(name) {
	    delete this.partials[name];
	  },

	  registerDecorator: function registerDecorator(name, fn) {
	    if (_utils.toString.call(name) === objectType) {
	      if (fn) {
	        throw new _exception2['default']('Arg not supported with multiple decorators');
	      }
	      _utils.extend(this.decorators, name);
	    } else {
	      this.decorators[name] = fn;
	    }
	  },
	  unregisterDecorator: function unregisterDecorator(name) {
	    delete this.decorators[name];
	  }
	};

	var log = _logger2['default'].log;

	exports.log = log;
	exports.createFrame = _utils.createFrame;
	exports.logger = _logger2['default'];

/***/ }),
/* 5 */
/***/ (function(module, exports) {

	'use strict';

	exports.__esModule = true;
	exports.extend = extend;
	exports.indexOf = indexOf;
	exports.escapeExpression = escapeExpression;
	exports.isEmpty = isEmpty;
	exports.createFrame = createFrame;
	exports.blockParams = blockParams;
	exports.appendContextPath = appendContextPath;
	var escape = {
	  '&': '&amp;',
	  '<': '&lt;',
	  '>': '&gt;',
	  '"': '&quot;',
	  "'": '&#x27;',
	  '`': '&#x60;',
	  '=': '&#x3D;'
	};

	var badChars = /[&<>"'`=]/g,
	    possible = /[&<>"'`=]/;

	function escapeChar(chr) {
	  return escape[chr];
	}

	function extend(obj /* , ...source */) {
	  for (var i = 1; i < arguments.length; i++) {
	    for (var key in arguments[i]) {
	      if (Object.prototype.hasOwnProperty.call(arguments[i], key)) {
	        obj[key] = arguments[i][key];
	      }
	    }
	  }

	  return obj;
	}

	var toString = Object.prototype.toString;

	exports.toString = toString;
	// Sourced from lodash
	// https://github.com/bestiejs/lodash/blob/master/LICENSE.txt
	/* eslint-disable func-style */
	var isFunction = function isFunction(value) {
	  return typeof value === 'function';
	};
	// fallback for older versions of Chrome and Safari
	/* istanbul ignore next */
	if (isFunction(/x/)) {
	  exports.isFunction = isFunction = function (value) {
	    return typeof value === 'function' && toString.call(value) === '[object Function]';
	  };
	}
	exports.isFunction = isFunction;

	/* eslint-enable func-style */

	/* istanbul ignore next */
	var isArray = Array.isArray || function (value) {
	  return value && typeof value === 'object' ? toString.call(value) === '[object Array]' : false;
	};

	exports.isArray = isArray;
	// Older IE versions do not directly support indexOf so we must implement our own, sadly.

	function indexOf(array, value) {
	  for (var i = 0, len = array.length; i < len; i++) {
	    if (array[i] === value) {
	      return i;
	    }
	  }
	  return -1;
	}

	function escapeExpression(string) {
	  if (typeof string !== 'string') {
	    // don't escape SafeStrings, since they're already safe
	    if (string && string.toHTML) {
	      return string.toHTML();
	    } else if (string == null) {
	      return '';
	    } else if (!string) {
	      return string + '';
	    }

	    // Force a string conversion as this will be done by the append regardless and
	    // the regex test will do this transparently behind the scenes, causing issues if
	    // an object's to string has escaped characters in it.
	    string = '' + string;
	  }

	  if (!possible.test(string)) {
	    return string;
	  }
	  return string.replace(badChars, escapeChar);
	}

	function isEmpty(value) {
	  if (!value && value !== 0) {
	    return true;
	  } else if (isArray(value) && value.length === 0) {
	    return true;
	  } else {
	    return false;
	  }
	}

	function createFrame(object) {
	  var frame = extend({}, object);
	  frame._parent = object;
	  return frame;
	}

	function blockParams(params, ids) {
	  params.path = ids;
	  return params;
	}

	function appendContextPath(contextPath, id) {
	  return (contextPath ? contextPath + '.' : '') + id;
	}

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _Object$defineProperty = __webpack_require__(7)['default'];

	exports.__esModule = true;

	var errorProps = ['description', 'fileName', 'lineNumber', 'message', 'name', 'number', 'stack'];

	function Exception(message, node) {
	  var loc = node && node.loc,
	      line = undefined,
	      column = undefined;
	  if (loc) {
	    line = loc.start.line;
	    column = loc.start.column;

	    message += ' - ' + line + ':' + column;
	  }

	  var tmp = Error.prototype.constructor.call(this, message);

	  // Unfortunately errors are not enumerable in Chrome (at least), so `for prop in tmp` doesn't work.
	  for (var idx = 0; idx < errorProps.length; idx++) {
	    this[errorProps[idx]] = tmp[errorProps[idx]];
	  }

	  /* istanbul ignore else */
	  if (Error.captureStackTrace) {
	    Error.captureStackTrace(this, Exception);
	  }

	  try {
	    if (loc) {
	      this.lineNumber = line;

	      // Work around issue under safari where we can't directly set the column value
	      /* istanbul ignore next */
	      if (_Object$defineProperty) {
	        Object.defineProperty(this, 'column', {
	          value: column,
	          enumerable: true
	        });
	      } else {
	        this.column = column;
	      }
	    }
	  } catch (nop) {
	    /* Ignore if the browser is very particular */
	  }
	}

	Exception.prototype = new Error();

	exports['default'] = Exception;
	module.exports = exports['default'];

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

	module.exports = { "default": __webpack_require__(8), __esModule: true };

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

	var $ = __webpack_require__(9);
	module.exports = function defineProperty(it, key, desc){
	  return $.setDesc(it, key, desc);
	};

/***/ }),
/* 9 */
/***/ (function(module, exports) {

	var $Object = Object;
	module.exports = {
	  create:     $Object.create,
	  getProto:   $Object.getPrototypeOf,
	  isEnum:     {}.propertyIsEnumerable,
	  getDesc:    $Object.getOwnPropertyDescriptor,
	  setDesc:    $Object.defineProperty,
	  setDescs:   $Object.defineProperties,
	  getKeys:    $Object.keys,
	  getNames:   $Object.getOwnPropertyNames,
	  getSymbols: $Object.getOwnPropertySymbols,
	  each:       [].forEach
	};

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;
	exports.registerDefaultHelpers = registerDefaultHelpers;

	var _helpersBlockHelperMissing = __webpack_require__(11);

	var _helpersBlockHelperMissing2 = _interopRequireDefault(_helpersBlockHelperMissing);

	var _helpersEach = __webpack_require__(12);

	var _helpersEach2 = _interopRequireDefault(_helpersEach);

	var _helpersHelperMissing = __webpack_require__(13);

	var _helpersHelperMissing2 = _interopRequireDefault(_helpersHelperMissing);

	var _helpersIf = __webpack_require__(14);

	var _helpersIf2 = _interopRequireDefault(_helpersIf);

	var _helpersLog = __webpack_require__(15);

	var _helpersLog2 = _interopRequireDefault(_helpersLog);

	var _helpersLookup = __webpack_require__(16);

	var _helpersLookup2 = _interopRequireDefault(_helpersLookup);

	var _helpersWith = __webpack_require__(17);

	var _helpersWith2 = _interopRequireDefault(_helpersWith);

	function registerDefaultHelpers(instance) {
	  _helpersBlockHelperMissing2['default'](instance);
	  _helpersEach2['default'](instance);
	  _helpersHelperMissing2['default'](instance);
	  _helpersIf2['default'](instance);
	  _helpersLog2['default'](instance);
	  _helpersLookup2['default'](instance);
	  _helpersWith2['default'](instance);
	}

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	exports.__esModule = true;

	var _utils = __webpack_require__(5);

	exports['default'] = function (instance) {
	  instance.registerHelper('blockHelperMissing', function (context, options) {
	    var inverse = options.inverse,
	        fn = options.fn;

	    if (context === true) {
	      return fn(this);
	    } else if (context === false || context == null) {
	      return inverse(this);
	    } else if (_utils.isArray(context)) {
	      if (context.length > 0) {
	        if (options.ids) {
	          options.ids = [options.name];
	        }

	        return instance.helpers.each(context, options);
	      } else {
	        return inverse(this);
	      }
	    } else {
	      if (options.data && options.ids) {
	        var data = _utils.createFrame(options.data);
	        data.contextPath = _utils.appendContextPath(options.data.contextPath, options.name);
	        options = { data: data };
	      }

	      return fn(context, options);
	    }
	  });
	};

	module.exports = exports['default'];

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;

	var _utils = __webpack_require__(5);

	var _exception = __webpack_require__(6);

	var _exception2 = _interopRequireDefault(_exception);

	exports['default'] = function (instance) {
	  instance.registerHelper('each', function (context, options) {
	    if (!options) {
	      throw new _exception2['default']('Must pass iterator to #each');
	    }

	    var fn = options.fn,
	        inverse = options.inverse,
	        i = 0,
	        ret = '',
	        data = undefined,
	        contextPath = undefined;

	    if (options.data && options.ids) {
	      contextPath = _utils.appendContextPath(options.data.contextPath, options.ids[0]) + '.';
	    }

	    if (_utils.isFunction(context)) {
	      context = context.call(this);
	    }

	    if (options.data) {
	      data = _utils.createFrame(options.data);
	    }

	    function execIteration(field, index, last) {
	      if (data) {
	        data.key = field;
	        data.index = index;
	        data.first = index === 0;
	        data.last = !!last;

	        if (contextPath) {
	          data.contextPath = contextPath + field;
	        }
	      }

	      ret = ret + fn(context[field], {
	        data: data,
	        blockParams: _utils.blockParams([context[field], field], [contextPath + field, null])
	      });
	    }

	    if (context && typeof context === 'object') {
	      if (_utils.isArray(context)) {
	        for (var j = context.length; i < j; i++) {
	          if (i in context) {
	            execIteration(i, i, i === context.length - 1);
	          }
	        }
	      } else {
	        var priorKey = undefined;

	        for (var key in context) {
	          if (context.hasOwnProperty(key)) {
	            // We're running the iterations one step out of sync so we can detect
	            // the last iteration without have to scan the object twice and create
	            // an itermediate keys array.
	            if (priorKey !== undefined) {
	              execIteration(priorKey, i - 1);
	            }
	            priorKey = key;
	            i++;
	          }
	        }
	        if (priorKey !== undefined) {
	          execIteration(priorKey, i - 1, true);
	        }
	      }
	    }

	    if (i === 0) {
	      ret = inverse(this);
	    }

	    return ret;
	  });
	};

	module.exports = exports['default'];

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;

	var _exception = __webpack_require__(6);

	var _exception2 = _interopRequireDefault(_exception);

	exports['default'] = function (instance) {
	  instance.registerHelper('helperMissing', function () /* [args, ]options */{
	    if (arguments.length === 1) {
	      // A missing field in a {{foo}} construct.
	      return undefined;
	    } else {
	      // Someone is actually trying to call something, blow up.
	      throw new _exception2['default']('Missing helper: "' + arguments[arguments.length - 1].name + '"');
	    }
	  });
	};

	module.exports = exports['default'];

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	exports.__esModule = true;

	var _utils = __webpack_require__(5);

	exports['default'] = function (instance) {
	  instance.registerHelper('if', function (conditional, options) {
	    if (_utils.isFunction(conditional)) {
	      conditional = conditional.call(this);
	    }

	    // Default behavior is to render the positive path if the value is truthy and not empty.
	    // The `includeZero` option may be set to treat the condtional as purely not empty based on the
	    // behavior of isEmpty. Effectively this determines if 0 is handled by the positive path or negative.
	    if (!options.hash.includeZero && !conditional || _utils.isEmpty(conditional)) {
	      return options.inverse(this);
	    } else {
	      return options.fn(this);
	    }
	  });

	  instance.registerHelper('unless', function (conditional, options) {
	    return instance.helpers['if'].call(this, conditional, { fn: options.inverse, inverse: options.fn, hash: options.hash });
	  });
	};

	module.exports = exports['default'];

/***/ }),
/* 15 */
/***/ (function(module, exports) {

	'use strict';

	exports.__esModule = true;

	exports['default'] = function (instance) {
	  instance.registerHelper('log', function () /* message, options */{
	    var args = [undefined],
	        options = arguments[arguments.length - 1];
	    for (var i = 0; i < arguments.length - 1; i++) {
	      args.push(arguments[i]);
	    }

	    var level = 1;
	    if (options.hash.level != null) {
	      level = options.hash.level;
	    } else if (options.data && options.data.level != null) {
	      level = options.data.level;
	    }
	    args[0] = level;

	    instance.log.apply(instance, args);
	  });
	};

	module.exports = exports['default'];

/***/ }),
/* 16 */
/***/ (function(module, exports) {

	'use strict';

	exports.__esModule = true;

	exports['default'] = function (instance) {
	  instance.registerHelper('lookup', function (obj, field) {
	    return obj && obj[field];
	  });
	};

	module.exports = exports['default'];

/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	exports.__esModule = true;

	var _utils = __webpack_require__(5);

	exports['default'] = function (instance) {
	  instance.registerHelper('with', function (context, options) {
	    if (_utils.isFunction(context)) {
	      context = context.call(this);
	    }

	    var fn = options.fn;

	    if (!_utils.isEmpty(context)) {
	      var data = options.data;
	      if (options.data && options.ids) {
	        data = _utils.createFrame(options.data);
	        data.contextPath = _utils.appendContextPath(options.data.contextPath, options.ids[0]);
	      }

	      return fn(context, {
	        data: data,
	        blockParams: _utils.blockParams([context], [data && data.contextPath])
	      });
	    } else {
	      return options.inverse(this);
	    }
	  });
	};

	module.exports = exports['default'];

/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;
	exports.registerDefaultDecorators = registerDefaultDecorators;

	var _decoratorsInline = __webpack_require__(19);

	var _decoratorsInline2 = _interopRequireDefault(_decoratorsInline);

	function registerDefaultDecorators(instance) {
	  _decoratorsInline2['default'](instance);
	}

/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	exports.__esModule = true;

	var _utils = __webpack_require__(5);

	exports['default'] = function (instance) {
	  instance.registerDecorator('inline', function (fn, props, container, options) {
	    var ret = fn;
	    if (!props.partials) {
	      props.partials = {};
	      ret = function (context, options) {
	        // Create a new partials stack frame prior to exec.
	        var original = container.partials;
	        container.partials = _utils.extend({}, original, props.partials);
	        var ret = fn(context, options);
	        container.partials = original;
	        return ret;
	      };
	    }

	    props.partials[options.args[0]] = options.fn;

	    return ret;
	  });
	};

	module.exports = exports['default'];

/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	exports.__esModule = true;

	var _utils = __webpack_require__(5);

	var logger = {
	  methodMap: ['debug', 'info', 'warn', 'error'],
	  level: 'info',

	  // Maps a given level value to the `methodMap` indexes above.
	  lookupLevel: function lookupLevel(level) {
	    if (typeof level === 'string') {
	      var levelMap = _utils.indexOf(logger.methodMap, level.toLowerCase());
	      if (levelMap >= 0) {
	        level = levelMap;
	      } else {
	        level = parseInt(level, 10);
	      }
	    }

	    return level;
	  },

	  // Can be overridden in the host environment
	  log: function log(level) {
	    level = logger.lookupLevel(level);

	    if (typeof console !== 'undefined' && logger.lookupLevel(logger.level) <= level) {
	      var method = logger.methodMap[level];
	      if (!console[method]) {
	        // eslint-disable-line no-console
	        method = 'log';
	      }

	      for (var _len = arguments.length, message = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	        message[_key - 1] = arguments[_key];
	      }

	      console[method].apply(console, message); // eslint-disable-line no-console
	    }
	  }
	};

	exports['default'] = logger;
	module.exports = exports['default'];

/***/ }),
/* 21 */
/***/ (function(module, exports) {

	// Build out our basic SafeString type
	'use strict';

	exports.__esModule = true;
	function SafeString(string) {
	  this.string = string;
	}

	SafeString.prototype.toString = SafeString.prototype.toHTML = function () {
	  return '' + this.string;
	};

	exports['default'] = SafeString;
	module.exports = exports['default'];

/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _Object$seal = __webpack_require__(23)['default'];

	var _interopRequireWildcard = __webpack_require__(3)['default'];

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;
	exports.checkRevision = checkRevision;
	exports.template = template;
	exports.wrapProgram = wrapProgram;
	exports.resolvePartial = resolvePartial;
	exports.invokePartial = invokePartial;
	exports.noop = noop;

	var _utils = __webpack_require__(5);

	var Utils = _interopRequireWildcard(_utils);

	var _exception = __webpack_require__(6);

	var _exception2 = _interopRequireDefault(_exception);

	var _base = __webpack_require__(4);

	function checkRevision(compilerInfo) {
	  var compilerRevision = compilerInfo && compilerInfo[0] || 1,
	      currentRevision = _base.COMPILER_REVISION;

	  if (compilerRevision !== currentRevision) {
	    if (compilerRevision < currentRevision) {
	      var runtimeVersions = _base.REVISION_CHANGES[currentRevision],
	          compilerVersions = _base.REVISION_CHANGES[compilerRevision];
	      throw new _exception2['default']('Template was precompiled with an older version of Handlebars than the current runtime. ' + 'Please update your precompiler to a newer version (' + runtimeVersions + ') or downgrade your runtime to an older version (' + compilerVersions + ').');
	    } else {
	      // Use the embedded version info since the runtime doesn't know about this revision yet
	      throw new _exception2['default']('Template was precompiled with a newer version of Handlebars than the current runtime. ' + 'Please update your runtime to a newer version (' + compilerInfo[1] + ').');
	    }
	  }
	}

	function template(templateSpec, env) {
	  /* istanbul ignore next */
	  if (!env) {
	    throw new _exception2['default']('No environment passed to template');
	  }
	  if (!templateSpec || !templateSpec.main) {
	    throw new _exception2['default']('Unknown template object: ' + typeof templateSpec);
	  }

	  templateSpec.main.decorator = templateSpec.main_d;

	  // Note: Using env.VM references rather than local var references throughout this section to allow
	  // for external users to override these as psuedo-supported APIs.
	  env.VM.checkRevision(templateSpec.compiler);

	  function invokePartialWrapper(partial, context, options) {
	    if (options.hash) {
	      context = Utils.extend({}, context, options.hash);
	      if (options.ids) {
	        options.ids[0] = true;
	      }
	    }

	    partial = env.VM.resolvePartial.call(this, partial, context, options);
	    var result = env.VM.invokePartial.call(this, partial, context, options);

	    if (result == null && env.compile) {
	      options.partials[options.name] = env.compile(partial, templateSpec.compilerOptions, env);
	      result = options.partials[options.name](context, options);
	    }
	    if (result != null) {
	      if (options.indent) {
	        var lines = result.split('\n');
	        for (var i = 0, l = lines.length; i < l; i++) {
	          if (!lines[i] && i + 1 === l) {
	            break;
	          }

	          lines[i] = options.indent + lines[i];
	        }
	        result = lines.join('\n');
	      }
	      return result;
	    } else {
	      throw new _exception2['default']('The partial ' + options.name + ' could not be compiled when running in runtime-only mode');
	    }
	  }

	  // Just add water
	  var container = {
	    strict: function strict(obj, name) {
	      if (!(name in obj)) {
	        throw new _exception2['default']('"' + name + '" not defined in ' + obj);
	      }
	      return obj[name];
	    },
	    lookup: function lookup(depths, name) {
	      var len = depths.length;
	      for (var i = 0; i < len; i++) {
	        if (depths[i] && depths[i][name] != null) {
	          return depths[i][name];
	        }
	      }
	    },
	    lambda: function lambda(current, context) {
	      return typeof current === 'function' ? current.call(context) : current;
	    },

	    escapeExpression: Utils.escapeExpression,
	    invokePartial: invokePartialWrapper,

	    fn: function fn(i) {
	      var ret = templateSpec[i];
	      ret.decorator = templateSpec[i + '_d'];
	      return ret;
	    },

	    programs: [],
	    program: function program(i, data, declaredBlockParams, blockParams, depths) {
	      var programWrapper = this.programs[i],
	          fn = this.fn(i);
	      if (data || depths || blockParams || declaredBlockParams) {
	        programWrapper = wrapProgram(this, i, fn, data, declaredBlockParams, blockParams, depths);
	      } else if (!programWrapper) {
	        programWrapper = this.programs[i] = wrapProgram(this, i, fn);
	      }
	      return programWrapper;
	    },

	    data: function data(value, depth) {
	      while (value && depth--) {
	        value = value._parent;
	      }
	      return value;
	    },
	    merge: function merge(param, common) {
	      var obj = param || common;

	      if (param && common && param !== common) {
	        obj = Utils.extend({}, common, param);
	      }

	      return obj;
	    },
	    // An empty object to use as replacement for null-contexts
	    nullContext: _Object$seal({}),

	    noop: env.VM.noop,
	    compilerInfo: templateSpec.compiler
	  };

	  function ret(context) {
	    var options = arguments.length <= 1 || arguments[1] === undefined ? {} : arguments[1];

	    var data = options.data;

	    ret._setup(options);
	    if (!options.partial && templateSpec.useData) {
	      data = initData(context, data);
	    }
	    var depths = undefined,
	        blockParams = templateSpec.useBlockParams ? [] : undefined;
	    if (templateSpec.useDepths) {
	      if (options.depths) {
	        depths = context != options.depths[0] ? [context].concat(options.depths) : options.depths;
	      } else {
	        depths = [context];
	      }
	    }

	    function main(context /*, options*/) {
	      return '' + templateSpec.main(container, context, container.helpers, container.partials, data, blockParams, depths);
	    }
	    main = executeDecorators(templateSpec.main, main, container, options.depths || [], data, blockParams);
	    return main(context, options);
	  }
	  ret.isTop = true;

	  ret._setup = function (options) {
	    if (!options.partial) {
	      container.helpers = container.merge(options.helpers, env.helpers);

	      if (templateSpec.usePartial) {
	        container.partials = container.merge(options.partials, env.partials);
	      }
	      if (templateSpec.usePartial || templateSpec.useDecorators) {
	        container.decorators = container.merge(options.decorators, env.decorators);
	      }
	    } else {
	      container.helpers = options.helpers;
	      container.partials = options.partials;
	      container.decorators = options.decorators;
	    }
	  };

	  ret._child = function (i, data, blockParams, depths) {
	    if (templateSpec.useBlockParams && !blockParams) {
	      throw new _exception2['default']('must pass block params');
	    }
	    if (templateSpec.useDepths && !depths) {
	      throw new _exception2['default']('must pass parent depths');
	    }

	    return wrapProgram(container, i, templateSpec[i], data, 0, blockParams, depths);
	  };
	  return ret;
	}

	function wrapProgram(container, i, fn, data, declaredBlockParams, blockParams, depths) {
	  function prog(context) {
	    var options = arguments.length <= 1 || arguments[1] === undefined ? {} : arguments[1];

	    var currentDepths = depths;
	    if (depths && context != depths[0] && !(context === container.nullContext && depths[0] === null)) {
	      currentDepths = [context].concat(depths);
	    }

	    return fn(container, context, container.helpers, container.partials, options.data || data, blockParams && [options.blockParams].concat(blockParams), currentDepths);
	  }

	  prog = executeDecorators(fn, prog, container, depths, data, blockParams);

	  prog.program = i;
	  prog.depth = depths ? depths.length : 0;
	  prog.blockParams = declaredBlockParams || 0;
	  return prog;
	}

	function resolvePartial(partial, context, options) {
	  if (!partial) {
	    if (options.name === '@partial-block') {
	      partial = options.data['partial-block'];
	    } else {
	      partial = options.partials[options.name];
	    }
	  } else if (!partial.call && !options.name) {
	    // This is a dynamic partial that returned a string
	    options.name = partial;
	    partial = options.partials[partial];
	  }
	  return partial;
	}

	function invokePartial(partial, context, options) {
	  // Use the current closure context to save the partial-block if this partial
	  var currentPartialBlock = options.data && options.data['partial-block'];
	  options.partial = true;
	  if (options.ids) {
	    options.data.contextPath = options.ids[0] || options.data.contextPath;
	  }

	  var partialBlock = undefined;
	  if (options.fn && options.fn !== noop) {
	    (function () {
	      options.data = _base.createFrame(options.data);
	      // Wrapper function to get access to currentPartialBlock from the closure
	      var fn = options.fn;
	      partialBlock = options.data['partial-block'] = function partialBlockWrapper(context) {
	        var options = arguments.length <= 1 || arguments[1] === undefined ? {} : arguments[1];

	        // Restore the partial-block from the closure for the execution of the block
	        // i.e. the part inside the block of the partial call.
	        options.data = _base.createFrame(options.data);
	        options.data['partial-block'] = currentPartialBlock;
	        return fn(context, options);
	      };
	      if (fn.partials) {
	        options.partials = Utils.extend({}, options.partials, fn.partials);
	      }
	    })();
	  }

	  if (partial === undefined && partialBlock) {
	    partial = partialBlock;
	  }

	  if (partial === undefined) {
	    throw new _exception2['default']('The partial ' + options.name + ' could not be found');
	  } else if (partial instanceof Function) {
	    return partial(context, options);
	  }
	}

	function noop() {
	  return '';
	}

	function initData(context, data) {
	  if (!data || !('root' in data)) {
	    data = data ? _base.createFrame(data) : {};
	    data.root = context;
	  }
	  return data;
	}

	function executeDecorators(fn, prog, container, depths, data, blockParams) {
	  if (fn.decorator) {
	    var props = {};
	    prog = fn.decorator(prog, props, container, depths && depths[0], data, blockParams, depths);
	    Utils.extend(prog, props);
	  }
	  return prog;
	}

/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

	module.exports = { "default": __webpack_require__(24), __esModule: true };

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

	__webpack_require__(25);
	module.exports = __webpack_require__(30).Object.seal;

/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

	// 19.1.2.17 Object.seal(O)
	var isObject = __webpack_require__(26);

	__webpack_require__(27)('seal', function($seal){
	  return function seal(it){
	    return $seal && isObject(it) ? $seal(it) : it;
	  };
	});

/***/ }),
/* 26 */
/***/ (function(module, exports) {

	module.exports = function(it){
	  return typeof it === 'object' ? it !== null : typeof it === 'function';
	};

/***/ }),
/* 27 */
/***/ (function(module, exports, __webpack_require__) {

	// most Object methods by ES6 should accept primitives
	var $export = __webpack_require__(28)
	  , core    = __webpack_require__(30)
	  , fails   = __webpack_require__(33);
	module.exports = function(KEY, exec){
	  var fn  = (core.Object || {})[KEY] || Object[KEY]
	    , exp = {};
	  exp[KEY] = exec(fn);
	  $export($export.S + $export.F * fails(function(){ fn(1); }), 'Object', exp);
	};

/***/ }),
/* 28 */
/***/ (function(module, exports, __webpack_require__) {

	var global    = __webpack_require__(29)
	  , core      = __webpack_require__(30)
	  , ctx       = __webpack_require__(31)
	  , PROTOTYPE = 'prototype';

	var $export = function(type, name, source){
	  var IS_FORCED = type & $export.F
	    , IS_GLOBAL = type & $export.G
	    , IS_STATIC = type & $export.S
	    , IS_PROTO  = type & $export.P
	    , IS_BIND   = type & $export.B
	    , IS_WRAP   = type & $export.W
	    , exports   = IS_GLOBAL ? core : core[name] || (core[name] = {})
	    , target    = IS_GLOBAL ? global : IS_STATIC ? global[name] : (global[name] || {})[PROTOTYPE]
	    , key, own, out;
	  if(IS_GLOBAL)source = name;
	  for(key in source){
	    // contains in native
	    own = !IS_FORCED && target && key in target;
	    if(own && key in exports)continue;
	    // export native or passed
	    out = own ? target[key] : source[key];
	    // prevent global pollution for namespaces
	    exports[key] = IS_GLOBAL && typeof target[key] != 'function' ? source[key]
	    // bind timers to global for call from export context
	    : IS_BIND && own ? ctx(out, global)
	    // wrap global constructors for prevent change them in library
	    : IS_WRAP && target[key] == out ? (function(C){
	      var F = function(param){
	        return this instanceof C ? new C(param) : C(param);
	      };
	      F[PROTOTYPE] = C[PROTOTYPE];
	      return F;
	    // make static versions for prototype methods
	    })(out) : IS_PROTO && typeof out == 'function' ? ctx(Function.call, out) : out;
	    if(IS_PROTO)(exports[PROTOTYPE] || (exports[PROTOTYPE] = {}))[key] = out;
	  }
	};
	// type bitmap
	$export.F = 1;  // forced
	$export.G = 2;  // global
	$export.S = 4;  // static
	$export.P = 8;  // proto
	$export.B = 16; // bind
	$export.W = 32; // wrap
	module.exports = $export;

/***/ }),
/* 29 */
/***/ (function(module, exports) {

	// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
	var global = module.exports = typeof window != 'undefined' && window.Math == Math
	  ? window : typeof self != 'undefined' && self.Math == Math ? self : Function('return this')();
	if(typeof __g == 'number')__g = global; // eslint-disable-line no-undef

/***/ }),
/* 30 */
/***/ (function(module, exports) {

	var core = module.exports = {version: '1.2.6'};
	if(typeof __e == 'number')__e = core; // eslint-disable-line no-undef

/***/ }),
/* 31 */
/***/ (function(module, exports, __webpack_require__) {

	// optional / simple context binding
	var aFunction = __webpack_require__(32);
	module.exports = function(fn, that, length){
	  aFunction(fn);
	  if(that === undefined)return fn;
	  switch(length){
	    case 1: return function(a){
	      return fn.call(that, a);
	    };
	    case 2: return function(a, b){
	      return fn.call(that, a, b);
	    };
	    case 3: return function(a, b, c){
	      return fn.call(that, a, b, c);
	    };
	  }
	  return function(/* ...args */){
	    return fn.apply(that, arguments);
	  };
	};

/***/ }),
/* 32 */
/***/ (function(module, exports) {

	module.exports = function(it){
	  if(typeof it != 'function')throw TypeError(it + ' is not a function!');
	  return it;
	};

/***/ }),
/* 33 */
/***/ (function(module, exports) {

	module.exports = function(exec){
	  try {
	    return !!exec();
	  } catch(e){
	    return true;
	  }
	};

/***/ }),
/* 34 */
/***/ (function(module, exports) {

	/* WEBPACK VAR INJECTION */(function(global) {/* global window */
	'use strict';

	exports.__esModule = true;

	exports['default'] = function (Handlebars) {
	  /* istanbul ignore next */
	  var root = typeof global !== 'undefined' ? global : window,
	      $Handlebars = root.Handlebars;
	  /* istanbul ignore next */
	  Handlebars.noConflict = function () {
	    if (root.Handlebars === Handlebars) {
	      root.Handlebars = $Handlebars;
	    }
	    return Handlebars;
	  };
	};

	module.exports = exports['default'];
	/* WEBPACK VAR INJECTION */}.call(exports, (function() { return this; }())))

/***/ }),
/* 35 */
/***/ (function(module, exports) {

	'use strict';

	exports.__esModule = true;
	var AST = {
	  // Public API used to evaluate derived attributes regarding AST nodes
	  helpers: {
	    // a mustache is definitely a helper if:
	    // * it is an eligible helper, and
	    // * it has at least one parameter or hash segment
	    helperExpression: function helperExpression(node) {
	      return node.type === 'SubExpression' || (node.type === 'MustacheStatement' || node.type === 'BlockStatement') && !!(node.params && node.params.length || node.hash);
	    },

	    scopedId: function scopedId(path) {
	      return (/^\.|this\b/.test(path.original)
	      );
	    },

	    // an ID is simple if it only has one part, and that part is not
	    // `..` or `this`.
	    simpleId: function simpleId(path) {
	      return path.parts.length === 1 && !AST.helpers.scopedId(path) && !path.depth;
	    }
	  }
	};

	// Must be exported as an object rather than the root of the module as the jison lexer
	// must modify the object to operate properly.
	exports['default'] = AST;
	module.exports = exports['default'];

/***/ }),
/* 36 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	var _interopRequireWildcard = __webpack_require__(3)['default'];

	exports.__esModule = true;
	exports.parse = parse;

	var _parser = __webpack_require__(37);

	var _parser2 = _interopRequireDefault(_parser);

	var _whitespaceControl = __webpack_require__(38);

	var _whitespaceControl2 = _interopRequireDefault(_whitespaceControl);

	var _helpers = __webpack_require__(40);

	var Helpers = _interopRequireWildcard(_helpers);

	var _utils = __webpack_require__(5);

	exports.parser = _parser2['default'];

	var yy = {};
	_utils.extend(yy, Helpers);

	function parse(input, options) {
	  // Just return if an already-compiled AST was passed in.
	  if (input.type === 'Program') {
	    return input;
	  }

	  _parser2['default'].yy = yy;

	  // Altering the shared object here, but this is ok as parser is a sync operation
	  yy.locInfo = function (locInfo) {
	    return new yy.SourceLocation(options && options.srcName, locInfo);
	  };

	  var strip = new _whitespaceControl2['default'](options);
	  return strip.accept(_parser2['default'].parse(input));
	}

/***/ }),
/* 37 */
/***/ (function(module, exports) {

	// File ignored in coverage tests via setting in .istanbul.yml
	/* Jison generated parser */
	"use strict";

	exports.__esModule = true;
	var handlebars = (function () {
	    var parser = { trace: function trace() {},
	        yy: {},
	        symbols_: { "error": 2, "root": 3, "program": 4, "EOF": 5, "program_repetition0": 6, "statement": 7, "mustache": 8, "block": 9, "rawBlock": 10, "partial": 11, "partialBlock": 12, "content": 13, "COMMENT": 14, "CONTENT": 15, "openRawBlock": 16, "rawBlock_repetition_plus0": 17, "END_RAW_BLOCK": 18, "OPEN_RAW_BLOCK": 19, "helperName": 20, "openRawBlock_repetition0": 21, "openRawBlock_option0": 22, "CLOSE_RAW_BLOCK": 23, "openBlock": 24, "block_option0": 25, "closeBlock": 26, "openInverse": 27, "block_option1": 28, "OPEN_BLOCK": 29, "openBlock_repetition0": 30, "openBlock_option0": 31, "openBlock_option1": 32, "CLOSE": 33, "OPEN_INVERSE": 34, "openInverse_repetition0": 35, "openInverse_option0": 36, "openInverse_option1": 37, "openInverseChain": 38, "OPEN_INVERSE_CHAIN": 39, "openInverseChain_repetition0": 40, "openInverseChain_option0": 41, "openInverseChain_option1": 42, "inverseAndProgram": 43, "INVERSE": 44, "inverseChain": 45, "inverseChain_option0": 46, "OPEN_ENDBLOCK": 47, "OPEN": 48, "mustache_repetition0": 49, "mustache_option0": 50, "OPEN_UNESCAPED": 51, "mustache_repetition1": 52, "mustache_option1": 53, "CLOSE_UNESCAPED": 54, "OPEN_PARTIAL": 55, "partialName": 56, "partial_repetition0": 57, "partial_option0": 58, "openPartialBlock": 59, "OPEN_PARTIAL_BLOCK": 60, "openPartialBlock_repetition0": 61, "openPartialBlock_option0": 62, "param": 63, "sexpr": 64, "OPEN_SEXPR": 65, "sexpr_repetition0": 66, "sexpr_option0": 67, "CLOSE_SEXPR": 68, "hash": 69, "hash_repetition_plus0": 70, "hashSegment": 71, "ID": 72, "EQUALS": 73, "blockParams": 74, "OPEN_BLOCK_PARAMS": 75, "blockParams_repetition_plus0": 76, "CLOSE_BLOCK_PARAMS": 77, "path": 78, "dataName": 79, "STRING": 80, "NUMBER": 81, "BOOLEAN": 82, "UNDEFINED": 83, "NULL": 84, "DATA": 85, "pathSegments": 86, "SEP": 87, "$accept": 0, "$end": 1 },
	        terminals_: { 2: "error", 5: "EOF", 14: "COMMENT", 15: "CONTENT", 18: "END_RAW_BLOCK", 19: "OPEN_RAW_BLOCK", 23: "CLOSE_RAW_BLOCK", 29: "OPEN_BLOCK", 33: "CLOSE", 34: "OPEN_INVERSE", 39: "OPEN_INVERSE_CHAIN", 44: "INVERSE", 47: "OPEN_ENDBLOCK", 48: "OPEN", 51: "OPEN_UNESCAPED", 54: "CLOSE_UNESCAPED", 55: "OPEN_PARTIAL", 60: "OPEN_PARTIAL_BLOCK", 65: "OPEN_SEXPR", 68: "CLOSE_SEXPR", 72: "ID", 73: "EQUALS", 75: "OPEN_BLOCK_PARAMS", 77: "CLOSE_BLOCK_PARAMS", 80: "STRING", 81: "NUMBER", 82: "BOOLEAN", 83: "UNDEFINED", 84: "NULL", 85: "DATA", 87: "SEP" },
	        productions_: [0, [3, 2], [4, 1], [7, 1], [7, 1], [7, 1], [7, 1], [7, 1], [7, 1], [7, 1], [13, 1], [10, 3], [16, 5], [9, 4], [9, 4], [24, 6], [27, 6], [38, 6], [43, 2], [45, 3], [45, 1], [26, 3], [8, 5], [8, 5], [11, 5], [12, 3], [59, 5], [63, 1], [63, 1], [64, 5], [69, 1], [71, 3], [74, 3], [20, 1], [20, 1], [20, 1], [20, 1], [20, 1], [20, 1], [20, 1], [56, 1], [56, 1], [79, 2], [78, 1], [86, 3], [86, 1], [6, 0], [6, 2], [17, 1], [17, 2], [21, 0], [21, 2], [22, 0], [22, 1], [25, 0], [25, 1], [28, 0], [28, 1], [30, 0], [30, 2], [31, 0], [31, 1], [32, 0], [32, 1], [35, 0], [35, 2], [36, 0], [36, 1], [37, 0], [37, 1], [40, 0], [40, 2], [41, 0], [41, 1], [42, 0], [42, 1], [46, 0], [46, 1], [49, 0], [49, 2], [50, 0], [50, 1], [52, 0], [52, 2], [53, 0], [53, 1], [57, 0], [57, 2], [58, 0], [58, 1], [61, 0], [61, 2], [62, 0], [62, 1], [66, 0], [66, 2], [67, 0], [67, 1], [70, 1], [70, 2], [76, 1], [76, 2]],
	        performAction: function anonymous(yytext, yyleng, yylineno, yy, yystate, $$, _$
	        /**/) {

	            var $0 = $$.length - 1;
	            switch (yystate) {
	                case 1:
	                    return $$[$0 - 1];
	                    break;
	                case 2:
	                    this.$ = yy.prepareProgram($$[$0]);
	                    break;
	                case 3:
	                    this.$ = $$[$0];
	                    break;
	                case 4:
	                    this.$ = $$[$0];
	                    break;
	                case 5:
	                    this.$ = $$[$0];
	                    break;
	                case 6:
	                    this.$ = $$[$0];
	                    break;
	                case 7:
	                    this.$ = $$[$0];
	                    break;
	                case 8:
	                    this.$ = $$[$0];
	                    break;
	                case 9:
	                    this.$ = {
	                        type: 'CommentStatement',
	                        value: yy.stripComment($$[$0]),
	                        strip: yy.stripFlags($$[$0], $$[$0]),
	                        loc: yy.locInfo(this._$)
	                    };

	                    break;
	                case 10:
	                    this.$ = {
	                        type: 'ContentStatement',
	                        original: $$[$0],
	                        value: $$[$0],
	                        loc: yy.locInfo(this._$)
	                    };

	                    break;
	                case 11:
	                    this.$ = yy.prepareRawBlock($$[$0 - 2], $$[$0 - 1], $$[$0], this._$);
	                    break;
	                case 12:
	                    this.$ = { path: $$[$0 - 3], params: $$[$0 - 2], hash: $$[$0 - 1] };
	                    break;
	                case 13:
	                    this.$ = yy.prepareBlock($$[$0 - 3], $$[$0 - 2], $$[$0 - 1], $$[$0], false, this._$);
	                    break;
	                case 14:
	                    this.$ = yy.prepareBlock($$[$0 - 3], $$[$0 - 2], $$[$0 - 1], $$[$0], true, this._$);
	                    break;
	                case 15:
	                    this.$ = { open: $$[$0 - 5], path: $$[$0 - 4], params: $$[$0 - 3], hash: $$[$0 - 2], blockParams: $$[$0 - 1], strip: yy.stripFlags($$[$0 - 5], $$[$0]) };
	                    break;
	                case 16:
	                    this.$ = { path: $$[$0 - 4], params: $$[$0 - 3], hash: $$[$0 - 2], blockParams: $$[$0 - 1], strip: yy.stripFlags($$[$0 - 5], $$[$0]) };
	                    break;
	                case 17:
	                    this.$ = { path: $$[$0 - 4], params: $$[$0 - 3], hash: $$[$0 - 2], blockParams: $$[$0 - 1], strip: yy.stripFlags($$[$0 - 5], $$[$0]) };
	                    break;
	                case 18:
	                    this.$ = { strip: yy.stripFlags($$[$0 - 1], $$[$0 - 1]), program: $$[$0] };
	                    break;
	                case 19:
	                    var inverse = yy.prepareBlock($$[$0 - 2], $$[$0 - 1], $$[$0], $$[$0], false, this._$),
	                        program = yy.prepareProgram([inverse], $$[$0 - 1].loc);
	                    program.chained = true;

	                    this.$ = { strip: $$[$0 - 2].strip, program: program, chain: true };

	                    break;
	                case 20:
	                    this.$ = $$[$0];
	                    break;
	                case 21:
	                    this.$ = { path: $$[$0 - 1], strip: yy.stripFlags($$[$0 - 2], $$[$0]) };
	                    break;
	                case 22:
	                    this.$ = yy.prepareMustache($$[$0 - 3], $$[$0 - 2], $$[$0 - 1], $$[$0 - 4], yy.stripFlags($$[$0 - 4], $$[$0]), this._$);
	                    break;
	                case 23:
	                    this.$ = yy.prepareMustache($$[$0 - 3], $$[$0 - 2], $$[$0 - 1], $$[$0 - 4], yy.stripFlags($$[$0 - 4], $$[$0]), this._$);
	                    break;
	                case 24:
	                    this.$ = {
	                        type: 'PartialStatement',
	                        name: $$[$0 - 3],
	                        params: $$[$0 - 2],
	                        hash: $$[$0 - 1],
	                        indent: '',
	                        strip: yy.stripFlags($$[$0 - 4], $$[$0]),
	                        loc: yy.locInfo(this._$)
	                    };

	                    break;
	                case 25:
	                    this.$ = yy.preparePartialBlock($$[$0 - 2], $$[$0 - 1], $$[$0], this._$);
	                    break;
	                case 26:
	                    this.$ = { path: $$[$0 - 3], params: $$[$0 - 2], hash: $$[$0 - 1], strip: yy.stripFlags($$[$0 - 4], $$[$0]) };
	                    break;
	                case 27:
	                    this.$ = $$[$0];
	                    break;
	                case 28:
	                    this.$ = $$[$0];
	                    break;
	                case 29:
	                    this.$ = {
	                        type: 'SubExpression',
	                        path: $$[$0 - 3],
	                        params: $$[$0 - 2],
	                        hash: $$[$0 - 1],
	                        loc: yy.locInfo(this._$)
	                    };

	                    break;
	                case 30:
	                    this.$ = { type: 'Hash', pairs: $$[$0], loc: yy.locInfo(this._$) };
	                    break;
	                case 31:
	                    this.$ = { type: 'HashPair', key: yy.id($$[$0 - 2]), value: $$[$0], loc: yy.locInfo(this._$) };
	                    break;
	                case 32:
	                    this.$ = yy.id($$[$0 - 1]);
	                    break;
	                case 33:
	                    this.$ = $$[$0];
	                    break;
	                case 34:
	                    this.$ = $$[$0];
	                    break;
	                case 35:
	                    this.$ = { type: 'StringLiteral', value: $$[$0], original: $$[$0], loc: yy.locInfo(this._$) };
	                    break;
	                case 36:
	                    this.$ = { type: 'NumberLiteral', value: Number($$[$0]), original: Number($$[$0]), loc: yy.locInfo(this._$) };
	                    break;
	                case 37:
	                    this.$ = { type: 'BooleanLiteral', value: $$[$0] === 'true', original: $$[$0] === 'true', loc: yy.locInfo(this._$) };
	                    break;
	                case 38:
	                    this.$ = { type: 'UndefinedLiteral', original: undefined, value: undefined, loc: yy.locInfo(this._$) };
	                    break;
	                case 39:
	                    this.$ = { type: 'NullLiteral', original: null, value: null, loc: yy.locInfo(this._$) };
	                    break;
	                case 40:
	                    this.$ = $$[$0];
	                    break;
	                case 41:
	                    this.$ = $$[$0];
	                    break;
	                case 42:
	                    this.$ = yy.preparePath(true, $$[$0], this._$);
	                    break;
	                case 43:
	                    this.$ = yy.preparePath(false, $$[$0], this._$);
	                    break;
	                case 44:
	                    $$[$0 - 2].push({ part: yy.id($$[$0]), original: $$[$0], separator: $$[$0 - 1] });this.$ = $$[$0 - 2];
	                    break;
	                case 45:
	                    this.$ = [{ part: yy.id($$[$0]), original: $$[$0] }];
	                    break;
	                case 46:
	                    this.$ = [];
	                    break;
	                case 47:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 48:
	                    this.$ = [$$[$0]];
	                    break;
	                case 49:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 50:
	                    this.$ = [];
	                    break;
	                case 51:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 58:
	                    this.$ = [];
	                    break;
	                case 59:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 64:
	                    this.$ = [];
	                    break;
	                case 65:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 70:
	                    this.$ = [];
	                    break;
	                case 71:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 78:
	                    this.$ = [];
	                    break;
	                case 79:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 82:
	                    this.$ = [];
	                    break;
	                case 83:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 86:
	                    this.$ = [];
	                    break;
	                case 87:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 90:
	                    this.$ = [];
	                    break;
	                case 91:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 94:
	                    this.$ = [];
	                    break;
	                case 95:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 98:
	                    this.$ = [$$[$0]];
	                    break;
	                case 99:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	                case 100:
	                    this.$ = [$$[$0]];
	                    break;
	                case 101:
	                    $$[$0 - 1].push($$[$0]);
	                    break;
	            }
	        },
	        table: [{ 3: 1, 4: 2, 5: [2, 46], 6: 3, 14: [2, 46], 15: [2, 46], 19: [2, 46], 29: [2, 46], 34: [2, 46], 48: [2, 46], 51: [2, 46], 55: [2, 46], 60: [2, 46] }, { 1: [3] }, { 5: [1, 4] }, { 5: [2, 2], 7: 5, 8: 6, 9: 7, 10: 8, 11: 9, 12: 10, 13: 11, 14: [1, 12], 15: [1, 20], 16: 17, 19: [1, 23], 24: 15, 27: 16, 29: [1, 21], 34: [1, 22], 39: [2, 2], 44: [2, 2], 47: [2, 2], 48: [1, 13], 51: [1, 14], 55: [1, 18], 59: 19, 60: [1, 24] }, { 1: [2, 1] }, { 5: [2, 47], 14: [2, 47], 15: [2, 47], 19: [2, 47], 29: [2, 47], 34: [2, 47], 39: [2, 47], 44: [2, 47], 47: [2, 47], 48: [2, 47], 51: [2, 47], 55: [2, 47], 60: [2, 47] }, { 5: [2, 3], 14: [2, 3], 15: [2, 3], 19: [2, 3], 29: [2, 3], 34: [2, 3], 39: [2, 3], 44: [2, 3], 47: [2, 3], 48: [2, 3], 51: [2, 3], 55: [2, 3], 60: [2, 3] }, { 5: [2, 4], 14: [2, 4], 15: [2, 4], 19: [2, 4], 29: [2, 4], 34: [2, 4], 39: [2, 4], 44: [2, 4], 47: [2, 4], 48: [2, 4], 51: [2, 4], 55: [2, 4], 60: [2, 4] }, { 5: [2, 5], 14: [2, 5], 15: [2, 5], 19: [2, 5], 29: [2, 5], 34: [2, 5], 39: [2, 5], 44: [2, 5], 47: [2, 5], 48: [2, 5], 51: [2, 5], 55: [2, 5], 60: [2, 5] }, { 5: [2, 6], 14: [2, 6], 15: [2, 6], 19: [2, 6], 29: [2, 6], 34: [2, 6], 39: [2, 6], 44: [2, 6], 47: [2, 6], 48: [2, 6], 51: [2, 6], 55: [2, 6], 60: [2, 6] }, { 5: [2, 7], 14: [2, 7], 15: [2, 7], 19: [2, 7], 29: [2, 7], 34: [2, 7], 39: [2, 7], 44: [2, 7], 47: [2, 7], 48: [2, 7], 51: [2, 7], 55: [2, 7], 60: [2, 7] }, { 5: [2, 8], 14: [2, 8], 15: [2, 8], 19: [2, 8], 29: [2, 8], 34: [2, 8], 39: [2, 8], 44: [2, 8], 47: [2, 8], 48: [2, 8], 51: [2, 8], 55: [2, 8], 60: [2, 8] }, { 5: [2, 9], 14: [2, 9], 15: [2, 9], 19: [2, 9], 29: [2, 9], 34: [2, 9], 39: [2, 9], 44: [2, 9], 47: [2, 9], 48: [2, 9], 51: [2, 9], 55: [2, 9], 60: [2, 9] }, { 20: 25, 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 20: 36, 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 4: 37, 6: 3, 14: [2, 46], 15: [2, 46], 19: [2, 46], 29: [2, 46], 34: [2, 46], 39: [2, 46], 44: [2, 46], 47: [2, 46], 48: [2, 46], 51: [2, 46], 55: [2, 46], 60: [2, 46] }, { 4: 38, 6: 3, 14: [2, 46], 15: [2, 46], 19: [2, 46], 29: [2, 46], 34: [2, 46], 44: [2, 46], 47: [2, 46], 48: [2, 46], 51: [2, 46], 55: [2, 46], 60: [2, 46] }, { 13: 40, 15: [1, 20], 17: 39 }, { 20: 42, 56: 41, 64: 43, 65: [1, 44], 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 4: 45, 6: 3, 14: [2, 46], 15: [2, 46], 19: [2, 46], 29: [2, 46], 34: [2, 46], 47: [2, 46], 48: [2, 46], 51: [2, 46], 55: [2, 46], 60: [2, 46] }, { 5: [2, 10], 14: [2, 10], 15: [2, 10], 18: [2, 10], 19: [2, 10], 29: [2, 10], 34: [2, 10], 39: [2, 10], 44: [2, 10], 47: [2, 10], 48: [2, 10], 51: [2, 10], 55: [2, 10], 60: [2, 10] }, { 20: 46, 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 20: 47, 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 20: 48, 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 20: 42, 56: 49, 64: 43, 65: [1, 44], 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 33: [2, 78], 49: 50, 65: [2, 78], 72: [2, 78], 80: [2, 78], 81: [2, 78], 82: [2, 78], 83: [2, 78], 84: [2, 78], 85: [2, 78] }, { 23: [2, 33], 33: [2, 33], 54: [2, 33], 65: [2, 33], 68: [2, 33], 72: [2, 33], 75: [2, 33], 80: [2, 33], 81: [2, 33], 82: [2, 33], 83: [2, 33], 84: [2, 33], 85: [2, 33] }, { 23: [2, 34], 33: [2, 34], 54: [2, 34], 65: [2, 34], 68: [2, 34], 72: [2, 34], 75: [2, 34], 80: [2, 34], 81: [2, 34], 82: [2, 34], 83: [2, 34], 84: [2, 34], 85: [2, 34] }, { 23: [2, 35], 33: [2, 35], 54: [2, 35], 65: [2, 35], 68: [2, 35], 72: [2, 35], 75: [2, 35], 80: [2, 35], 81: [2, 35], 82: [2, 35], 83: [2, 35], 84: [2, 35], 85: [2, 35] }, { 23: [2, 36], 33: [2, 36], 54: [2, 36], 65: [2, 36], 68: [2, 36], 72: [2, 36], 75: [2, 36], 80: [2, 36], 81: [2, 36], 82: [2, 36], 83: [2, 36], 84: [2, 36], 85: [2, 36] }, { 23: [2, 37], 33: [2, 37], 54: [2, 37], 65: [2, 37], 68: [2, 37], 72: [2, 37], 75: [2, 37], 80: [2, 37], 81: [2, 37], 82: [2, 37], 83: [2, 37], 84: [2, 37], 85: [2, 37] }, { 23: [2, 38], 33: [2, 38], 54: [2, 38], 65: [2, 38], 68: [2, 38], 72: [2, 38], 75: [2, 38], 80: [2, 38], 81: [2, 38], 82: [2, 38], 83: [2, 38], 84: [2, 38], 85: [2, 38] }, { 23: [2, 39], 33: [2, 39], 54: [2, 39], 65: [2, 39], 68: [2, 39], 72: [2, 39], 75: [2, 39], 80: [2, 39], 81: [2, 39], 82: [2, 39], 83: [2, 39], 84: [2, 39], 85: [2, 39] }, { 23: [2, 43], 33: [2, 43], 54: [2, 43], 65: [2, 43], 68: [2, 43], 72: [2, 43], 75: [2, 43], 80: [2, 43], 81: [2, 43], 82: [2, 43], 83: [2, 43], 84: [2, 43], 85: [2, 43], 87: [1, 51] }, { 72: [1, 35], 86: 52 }, { 23: [2, 45], 33: [2, 45], 54: [2, 45], 65: [2, 45], 68: [2, 45], 72: [2, 45], 75: [2, 45], 80: [2, 45], 81: [2, 45], 82: [2, 45], 83: [2, 45], 84: [2, 45], 85: [2, 45], 87: [2, 45] }, { 52: 53, 54: [2, 82], 65: [2, 82], 72: [2, 82], 80: [2, 82], 81: [2, 82], 82: [2, 82], 83: [2, 82], 84: [2, 82], 85: [2, 82] }, { 25: 54, 38: 56, 39: [1, 58], 43: 57, 44: [1, 59], 45: 55, 47: [2, 54] }, { 28: 60, 43: 61, 44: [1, 59], 47: [2, 56] }, { 13: 63, 15: [1, 20], 18: [1, 62] }, { 15: [2, 48], 18: [2, 48] }, { 33: [2, 86], 57: 64, 65: [2, 86], 72: [2, 86], 80: [2, 86], 81: [2, 86], 82: [2, 86], 83: [2, 86], 84: [2, 86], 85: [2, 86] }, { 33: [2, 40], 65: [2, 40], 72: [2, 40], 80: [2, 40], 81: [2, 40], 82: [2, 40], 83: [2, 40], 84: [2, 40], 85: [2, 40] }, { 33: [2, 41], 65: [2, 41], 72: [2, 41], 80: [2, 41], 81: [2, 41], 82: [2, 41], 83: [2, 41], 84: [2, 41], 85: [2, 41] }, { 20: 65, 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 26: 66, 47: [1, 67] }, { 30: 68, 33: [2, 58], 65: [2, 58], 72: [2, 58], 75: [2, 58], 80: [2, 58], 81: [2, 58], 82: [2, 58], 83: [2, 58], 84: [2, 58], 85: [2, 58] }, { 33: [2, 64], 35: 69, 65: [2, 64], 72: [2, 64], 75: [2, 64], 80: [2, 64], 81: [2, 64], 82: [2, 64], 83: [2, 64], 84: [2, 64], 85: [2, 64] }, { 21: 70, 23: [2, 50], 65: [2, 50], 72: [2, 50], 80: [2, 50], 81: [2, 50], 82: [2, 50], 83: [2, 50], 84: [2, 50], 85: [2, 50] }, { 33: [2, 90], 61: 71, 65: [2, 90], 72: [2, 90], 80: [2, 90], 81: [2, 90], 82: [2, 90], 83: [2, 90], 84: [2, 90], 85: [2, 90] }, { 20: 75, 33: [2, 80], 50: 72, 63: 73, 64: 76, 65: [1, 44], 69: 74, 70: 77, 71: 78, 72: [1, 79], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 72: [1, 80] }, { 23: [2, 42], 33: [2, 42], 54: [2, 42], 65: [2, 42], 68: [2, 42], 72: [2, 42], 75: [2, 42], 80: [2, 42], 81: [2, 42], 82: [2, 42], 83: [2, 42], 84: [2, 42], 85: [2, 42], 87: [1, 51] }, { 20: 75, 53: 81, 54: [2, 84], 63: 82, 64: 76, 65: [1, 44], 69: 83, 70: 77, 71: 78, 72: [1, 79], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 26: 84, 47: [1, 67] }, { 47: [2, 55] }, { 4: 85, 6: 3, 14: [2, 46], 15: [2, 46], 19: [2, 46], 29: [2, 46], 34: [2, 46], 39: [2, 46], 44: [2, 46], 47: [2, 46], 48: [2, 46], 51: [2, 46], 55: [2, 46], 60: [2, 46] }, { 47: [2, 20] }, { 20: 86, 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 4: 87, 6: 3, 14: [2, 46], 15: [2, 46], 19: [2, 46], 29: [2, 46], 34: [2, 46], 47: [2, 46], 48: [2, 46], 51: [2, 46], 55: [2, 46], 60: [2, 46] }, { 26: 88, 47: [1, 67] }, { 47: [2, 57] }, { 5: [2, 11], 14: [2, 11], 15: [2, 11], 19: [2, 11], 29: [2, 11], 34: [2, 11], 39: [2, 11], 44: [2, 11], 47: [2, 11], 48: [2, 11], 51: [2, 11], 55: [2, 11], 60: [2, 11] }, { 15: [2, 49], 18: [2, 49] }, { 20: 75, 33: [2, 88], 58: 89, 63: 90, 64: 76, 65: [1, 44], 69: 91, 70: 77, 71: 78, 72: [1, 79], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 65: [2, 94], 66: 92, 68: [2, 94], 72: [2, 94], 80: [2, 94], 81: [2, 94], 82: [2, 94], 83: [2, 94], 84: [2, 94], 85: [2, 94] }, { 5: [2, 25], 14: [2, 25], 15: [2, 25], 19: [2, 25], 29: [2, 25], 34: [2, 25], 39: [2, 25], 44: [2, 25], 47: [2, 25], 48: [2, 25], 51: [2, 25], 55: [2, 25], 60: [2, 25] }, { 20: 93, 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 20: 75, 31: 94, 33: [2, 60], 63: 95, 64: 76, 65: [1, 44], 69: 96, 70: 77, 71: 78, 72: [1, 79], 75: [2, 60], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 20: 75, 33: [2, 66], 36: 97, 63: 98, 64: 76, 65: [1, 44], 69: 99, 70: 77, 71: 78, 72: [1, 79], 75: [2, 66], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 20: 75, 22: 100, 23: [2, 52], 63: 101, 64: 76, 65: [1, 44], 69: 102, 70: 77, 71: 78, 72: [1, 79], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 20: 75, 33: [2, 92], 62: 103, 63: 104, 64: 76, 65: [1, 44], 69: 105, 70: 77, 71: 78, 72: [1, 79], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 33: [1, 106] }, { 33: [2, 79], 65: [2, 79], 72: [2, 79], 80: [2, 79], 81: [2, 79], 82: [2, 79], 83: [2, 79], 84: [2, 79], 85: [2, 79] }, { 33: [2, 81] }, { 23: [2, 27], 33: [2, 27], 54: [2, 27], 65: [2, 27], 68: [2, 27], 72: [2, 27], 75: [2, 27], 80: [2, 27], 81: [2, 27], 82: [2, 27], 83: [2, 27], 84: [2, 27], 85: [2, 27] }, { 23: [2, 28], 33: [2, 28], 54: [2, 28], 65: [2, 28], 68: [2, 28], 72: [2, 28], 75: [2, 28], 80: [2, 28], 81: [2, 28], 82: [2, 28], 83: [2, 28], 84: [2, 28], 85: [2, 28] }, { 23: [2, 30], 33: [2, 30], 54: [2, 30], 68: [2, 30], 71: 107, 72: [1, 108], 75: [2, 30] }, { 23: [2, 98], 33: [2, 98], 54: [2, 98], 68: [2, 98], 72: [2, 98], 75: [2, 98] }, { 23: [2, 45], 33: [2, 45], 54: [2, 45], 65: [2, 45], 68: [2, 45], 72: [2, 45], 73: [1, 109], 75: [2, 45], 80: [2, 45], 81: [2, 45], 82: [2, 45], 83: [2, 45], 84: [2, 45], 85: [2, 45], 87: [2, 45] }, { 23: [2, 44], 33: [2, 44], 54: [2, 44], 65: [2, 44], 68: [2, 44], 72: [2, 44], 75: [2, 44], 80: [2, 44], 81: [2, 44], 82: [2, 44], 83: [2, 44], 84: [2, 44], 85: [2, 44], 87: [2, 44] }, { 54: [1, 110] }, { 54: [2, 83], 65: [2, 83], 72: [2, 83], 80: [2, 83], 81: [2, 83], 82: [2, 83], 83: [2, 83], 84: [2, 83], 85: [2, 83] }, { 54: [2, 85] }, { 5: [2, 13], 14: [2, 13], 15: [2, 13], 19: [2, 13], 29: [2, 13], 34: [2, 13], 39: [2, 13], 44: [2, 13], 47: [2, 13], 48: [2, 13], 51: [2, 13], 55: [2, 13], 60: [2, 13] }, { 38: 56, 39: [1, 58], 43: 57, 44: [1, 59], 45: 112, 46: 111, 47: [2, 76] }, { 33: [2, 70], 40: 113, 65: [2, 70], 72: [2, 70], 75: [2, 70], 80: [2, 70], 81: [2, 70], 82: [2, 70], 83: [2, 70], 84: [2, 70], 85: [2, 70] }, { 47: [2, 18] }, { 5: [2, 14], 14: [2, 14], 15: [2, 14], 19: [2, 14], 29: [2, 14], 34: [2, 14], 39: [2, 14], 44: [2, 14], 47: [2, 14], 48: [2, 14], 51: [2, 14], 55: [2, 14], 60: [2, 14] }, { 33: [1, 114] }, { 33: [2, 87], 65: [2, 87], 72: [2, 87], 80: [2, 87], 81: [2, 87], 82: [2, 87], 83: [2, 87], 84: [2, 87], 85: [2, 87] }, { 33: [2, 89] }, { 20: 75, 63: 116, 64: 76, 65: [1, 44], 67: 115, 68: [2, 96], 69: 117, 70: 77, 71: 78, 72: [1, 79], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 33: [1, 118] }, { 32: 119, 33: [2, 62], 74: 120, 75: [1, 121] }, { 33: [2, 59], 65: [2, 59], 72: [2, 59], 75: [2, 59], 80: [2, 59], 81: [2, 59], 82: [2, 59], 83: [2, 59], 84: [2, 59], 85: [2, 59] }, { 33: [2, 61], 75: [2, 61] }, { 33: [2, 68], 37: 122, 74: 123, 75: [1, 121] }, { 33: [2, 65], 65: [2, 65], 72: [2, 65], 75: [2, 65], 80: [2, 65], 81: [2, 65], 82: [2, 65], 83: [2, 65], 84: [2, 65], 85: [2, 65] }, { 33: [2, 67], 75: [2, 67] }, { 23: [1, 124] }, { 23: [2, 51], 65: [2, 51], 72: [2, 51], 80: [2, 51], 81: [2, 51], 82: [2, 51], 83: [2, 51], 84: [2, 51], 85: [2, 51] }, { 23: [2, 53] }, { 33: [1, 125] }, { 33: [2, 91], 65: [2, 91], 72: [2, 91], 80: [2, 91], 81: [2, 91], 82: [2, 91], 83: [2, 91], 84: [2, 91], 85: [2, 91] }, { 33: [2, 93] }, { 5: [2, 22], 14: [2, 22], 15: [2, 22], 19: [2, 22], 29: [2, 22], 34: [2, 22], 39: [2, 22], 44: [2, 22], 47: [2, 22], 48: [2, 22], 51: [2, 22], 55: [2, 22], 60: [2, 22] }, { 23: [2, 99], 33: [2, 99], 54: [2, 99], 68: [2, 99], 72: [2, 99], 75: [2, 99] }, { 73: [1, 109] }, { 20: 75, 63: 126, 64: 76, 65: [1, 44], 72: [1, 35], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 5: [2, 23], 14: [2, 23], 15: [2, 23], 19: [2, 23], 29: [2, 23], 34: [2, 23], 39: [2, 23], 44: [2, 23], 47: [2, 23], 48: [2, 23], 51: [2, 23], 55: [2, 23], 60: [2, 23] }, { 47: [2, 19] }, { 47: [2, 77] }, { 20: 75, 33: [2, 72], 41: 127, 63: 128, 64: 76, 65: [1, 44], 69: 129, 70: 77, 71: 78, 72: [1, 79], 75: [2, 72], 78: 26, 79: 27, 80: [1, 28], 81: [1, 29], 82: [1, 30], 83: [1, 31], 84: [1, 32], 85: [1, 34], 86: 33 }, { 5: [2, 24], 14: [2, 24], 15: [2, 24], 19: [2, 24], 29: [2, 24], 34: [2, 24], 39: [2, 24], 44: [2, 24], 47: [2, 24], 48: [2, 24], 51: [2, 24], 55: [2, 24], 60: [2, 24] }, { 68: [1, 130] }, { 65: [2, 95], 68: [2, 95], 72: [2, 95], 80: [2, 95], 81: [2, 95], 82: [2, 95], 83: [2, 95], 84: [2, 95], 85: [2, 95] }, { 68: [2, 97] }, { 5: [2, 21], 14: [2, 21], 15: [2, 21], 19: [2, 21], 29: [2, 21], 34: [2, 21], 39: [2, 21], 44: [2, 21], 47: [2, 21], 48: [2, 21], 51: [2, 21], 55: [2, 21], 60: [2, 21] }, { 33: [1, 131] }, { 33: [2, 63] }, { 72: [1, 133], 76: 132 }, { 33: [1, 134] }, { 33: [2, 69] }, { 15: [2, 12] }, { 14: [2, 26], 15: [2, 26], 19: [2, 26], 29: [2, 26], 34: [2, 26], 47: [2, 26], 48: [2, 26], 51: [2, 26], 55: [2, 26], 60: [2, 26] }, { 23: [2, 31], 33: [2, 31], 54: [2, 31], 68: [2, 31], 72: [2, 31], 75: [2, 31] }, { 33: [2, 74], 42: 135, 74: 136, 75: [1, 121] }, { 33: [2, 71], 65: [2, 71], 72: [2, 71], 75: [2, 71], 80: [2, 71], 81: [2, 71], 82: [2, 71], 83: [2, 71], 84: [2, 71], 85: [2, 71] }, { 33: [2, 73], 75: [2, 73] }, { 23: [2, 29], 33: [2, 29], 54: [2, 29], 65: [2, 29], 68: [2, 29], 72: [2, 29], 75: [2, 29], 80: [2, 29], 81: [2, 29], 82: [2, 29], 83: [2, 29], 84: [2, 29], 85: [2, 29] }, { 14: [2, 15], 15: [2, 15], 19: [2, 15], 29: [2, 15], 34: [2, 15], 39: [2, 15], 44: [2, 15], 47: [2, 15], 48: [2, 15], 51: [2, 15], 55: [2, 15], 60: [2, 15] }, { 72: [1, 138], 77: [1, 137] }, { 72: [2, 100], 77: [2, 100] }, { 14: [2, 16], 15: [2, 16], 19: [2, 16], 29: [2, 16], 34: [2, 16], 44: [2, 16], 47: [2, 16], 48: [2, 16], 51: [2, 16], 55: [2, 16], 60: [2, 16] }, { 33: [1, 139] }, { 33: [2, 75] }, { 33: [2, 32] }, { 72: [2, 101], 77: [2, 101] }, { 14: [2, 17], 15: [2, 17], 19: [2, 17], 29: [2, 17], 34: [2, 17], 39: [2, 17], 44: [2, 17], 47: [2, 17], 48: [2, 17], 51: [2, 17], 55: [2, 17], 60: [2, 17] }],
	        defaultActions: { 4: [2, 1], 55: [2, 55], 57: [2, 20], 61: [2, 57], 74: [2, 81], 83: [2, 85], 87: [2, 18], 91: [2, 89], 102: [2, 53], 105: [2, 93], 111: [2, 19], 112: [2, 77], 117: [2, 97], 120: [2, 63], 123: [2, 69], 124: [2, 12], 136: [2, 75], 137: [2, 32] },
	        parseError: function parseError(str, hash) {
	            throw new Error(str);
	        },
	        parse: function parse(input) {
	            var self = this,
	                stack = [0],
	                vstack = [null],
	                lstack = [],
	                table = this.table,
	                yytext = "",
	                yylineno = 0,
	                yyleng = 0,
	                recovering = 0,
	                TERROR = 2,
	                EOF = 1;
	            this.lexer.setInput(input);
	            this.lexer.yy = this.yy;
	            this.yy.lexer = this.lexer;
	            this.yy.parser = this;
	            if (typeof this.lexer.yylloc == "undefined") this.lexer.yylloc = {};
	            var yyloc = this.lexer.yylloc;
	            lstack.push(yyloc);
	            var ranges = this.lexer.options && this.lexer.options.ranges;
	            if (typeof this.yy.parseError === "function") this.parseError = this.yy.parseError;
	            function popStack(n) {
	                stack.length = stack.length - 2 * n;
	                vstack.length = vstack.length - n;
	                lstack.length = lstack.length - n;
	            }
	            function lex() {
	                var token;
	                token = self.lexer.lex() || 1;
	                if (typeof token !== "number") {
	                    token = self.symbols_[token] || token;
	                }
	                return token;
	            }
	            var symbol,
	                preErrorSymbol,
	                state,
	                action,
	                a,
	                r,
	                yyval = {},
	                p,
	                len,
	                newState,
	                expected;
	            while (true) {
	                state = stack[stack.length - 1];
	                if (this.defaultActions[state]) {
	                    action = this.defaultActions[state];
	                } else {
	                    if (symbol === null || typeof symbol == "undefined") {
	                        symbol = lex();
	                    }
	                    action = table[state] && table[state][symbol];
	                }
	                if (typeof action === "undefined" || !action.length || !action[0]) {
	                    var errStr = "";
	                    if (!recovering) {
	                        expected = [];
	                        for (p in table[state]) if (this.terminals_[p] && p > 2) {
	                            expected.push("'" + this.terminals_[p] + "'");
	                        }
	                        if (this.lexer.showPosition) {
	                            errStr = "Parse error on line " + (yylineno + 1) + ":\n" + this.lexer.showPosition() + "\nExpecting " + expected.join(", ") + ", got '" + (this.terminals_[symbol] || symbol) + "'";
	                        } else {
	                            errStr = "Parse error on line " + (yylineno + 1) + ": Unexpected " + (symbol == 1 ? "end of input" : "'" + (this.terminals_[symbol] || symbol) + "'");
	                        }
	                        this.parseError(errStr, { text: this.lexer.match, token: this.terminals_[symbol] || symbol, line: this.lexer.yylineno, loc: yyloc, expected: expected });
	                    }
	                }
	                if (action[0] instanceof Array && action.length > 1) {
	                    throw new Error("Parse Error: multiple actions possible at state: " + state + ", token: " + symbol);
	                }
	                switch (action[0]) {
	                    case 1:
	                        stack.push(symbol);
	                        vstack.push(this.lexer.yytext);
	                        lstack.push(this.lexer.yylloc);
	                        stack.push(action[1]);
	                        symbol = null;
	                        if (!preErrorSymbol) {
	                            yyleng = this.lexer.yyleng;
	                            yytext = this.lexer.yytext;
	                            yylineno = this.lexer.yylineno;
	                            yyloc = this.lexer.yylloc;
	                            if (recovering > 0) recovering--;
	                        } else {
	                            symbol = preErrorSymbol;
	                            preErrorSymbol = null;
	                        }
	                        break;
	                    case 2:
	                        len = this.productions_[action[1]][1];
	                        yyval.$ = vstack[vstack.length - len];
	                        yyval._$ = { first_line: lstack[lstack.length - (len || 1)].first_line, last_line: lstack[lstack.length - 1].last_line, first_column: lstack[lstack.length - (len || 1)].first_column, last_column: lstack[lstack.length - 1].last_column };
	                        if (ranges) {
	                            yyval._$.range = [lstack[lstack.length - (len || 1)].range[0], lstack[lstack.length - 1].range[1]];
	                        }
	                        r = this.performAction.call(yyval, yytext, yyleng, yylineno, this.yy, action[1], vstack, lstack);
	                        if (typeof r !== "undefined") {
	                            return r;
	                        }
	                        if (len) {
	                            stack = stack.slice(0, -1 * len * 2);
	                            vstack = vstack.slice(0, -1 * len);
	                            lstack = lstack.slice(0, -1 * len);
	                        }
	                        stack.push(this.productions_[action[1]][0]);
	                        vstack.push(yyval.$);
	                        lstack.push(yyval._$);
	                        newState = table[stack[stack.length - 2]][stack[stack.length - 1]];
	                        stack.push(newState);
	                        break;
	                    case 3:
	                        return true;
	                }
	            }
	            return true;
	        }
	    };
	    /* Jison generated lexer */
	    var lexer = (function () {
	        var lexer = { EOF: 1,
	            parseError: function parseError(str, hash) {
	                if (this.yy.parser) {
	                    this.yy.parser.parseError(str, hash);
	                } else {
	                    throw new Error(str);
	                }
	            },
	            setInput: function setInput(input) {
	                this._input = input;
	                this._more = this._less = this.done = false;
	                this.yylineno = this.yyleng = 0;
	                this.yytext = this.matched = this.match = '';
	                this.conditionStack = ['INITIAL'];
	                this.yylloc = { first_line: 1, first_column: 0, last_line: 1, last_column: 0 };
	                if (this.options.ranges) this.yylloc.range = [0, 0];
	                this.offset = 0;
	                return this;
	            },
	            input: function input() {
	                var ch = this._input[0];
	                this.yytext += ch;
	                this.yyleng++;
	                this.offset++;
	                this.match += ch;
	                this.matched += ch;
	                var lines = ch.match(/(?:\r\n?|\n).*/g);
	                if (lines) {
	                    this.yylineno++;
	                    this.yylloc.last_line++;
	                } else {
	                    this.yylloc.last_column++;
	                }
	                if (this.options.ranges) this.yylloc.range[1]++;

	                this._input = this._input.slice(1);
	                return ch;
	            },
	            unput: function unput(ch) {
	                var len = ch.length;
	                var lines = ch.split(/(?:\r\n?|\n)/g);

	                this._input = ch + this._input;
	                this.yytext = this.yytext.substr(0, this.yytext.length - len - 1);
	                //this.yyleng -= len;
	                this.offset -= len;
	                var oldLines = this.match.split(/(?:\r\n?|\n)/g);
	                this.match = this.match.substr(0, this.match.length - 1);
	                this.matched = this.matched.substr(0, this.matched.length - 1);

	                if (lines.length - 1) this.yylineno -= lines.length - 1;
	                var r = this.yylloc.range;

	                this.yylloc = { first_line: this.yylloc.first_line,
	                    last_line: this.yylineno + 1,
	                    first_column: this.yylloc.first_column,
	                    last_column: lines ? (lines.length === oldLines.length ? this.yylloc.first_column : 0) + oldLines[oldLines.length - lines.length].length - lines[0].length : this.yylloc.first_column - len
	                };

	                if (this.options.ranges) {
	                    this.yylloc.range = [r[0], r[0] + this.yyleng - len];
	                }
	                return this;
	            },
	            more: function more() {
	                this._more = true;
	                return this;
	            },
	            less: function less(n) {
	                this.unput(this.match.slice(n));
	            },
	            pastInput: function pastInput() {
	                var past = this.matched.substr(0, this.matched.length - this.match.length);
	                return (past.length > 20 ? '...' : '') + past.substr(-20).replace(/\n/g, "");
	            },
	            upcomingInput: function upcomingInput() {
	                var next = this.match;
	                if (next.length < 20) {
	                    next += this._input.substr(0, 20 - next.length);
	                }
	                return (next.substr(0, 20) + (next.length > 20 ? '...' : '')).replace(/\n/g, "");
	            },
	            showPosition: function showPosition() {
	                var pre = this.pastInput();
	                var c = new Array(pre.length + 1).join("-");
	                return pre + this.upcomingInput() + "\n" + c + "^";
	            },
	            next: function next() {
	                if (this.done) {
	                    return this.EOF;
	                }
	                if (!this._input) this.done = true;

	                var token, match, tempMatch, index, col, lines;
	                if (!this._more) {
	                    this.yytext = '';
	                    this.match = '';
	                }
	                var rules = this._currentRules();
	                for (var i = 0; i < rules.length; i++) {
	                    tempMatch = this._input.match(this.rules[rules[i]]);
	                    if (tempMatch && (!match || tempMatch[0].length > match[0].length)) {
	                        match = tempMatch;
	                        index = i;
	                        if (!this.options.flex) break;
	                    }
	                }
	                if (match) {
	                    lines = match[0].match(/(?:\r\n?|\n).*/g);
	                    if (lines) this.yylineno += lines.length;
	                    this.yylloc = { first_line: this.yylloc.last_line,
	                        last_line: this.yylineno + 1,
	                        first_column: this.yylloc.last_column,
	                        last_column: lines ? lines[lines.length - 1].length - lines[lines.length - 1].match(/\r?\n?/)[0].length : this.yylloc.last_column + match[0].length };
	                    this.yytext += match[0];
	                    this.match += match[0];
	                    this.matches = match;
	                    this.yyleng = this.yytext.length;
	                    if (this.options.ranges) {
	                        this.yylloc.range = [this.offset, this.offset += this.yyleng];
	                    }
	                    this._more = false;
	                    this._input = this._input.slice(match[0].length);
	                    this.matched += match[0];
	                    token = this.performAction.call(this, this.yy, this, rules[index], this.conditionStack[this.conditionStack.length - 1]);
	                    if (this.done && this._input) this.done = false;
	                    if (token) return token;else return;
	                }
	                if (this._input === "") {
	                    return this.EOF;
	                } else {
	                    return this.parseError('Lexical error on line ' + (this.yylineno + 1) + '. Unrecognized text.\n' + this.showPosition(), { text: "", token: null, line: this.yylineno });
	                }
	            },
	            lex: function lex() {
	                var r = this.next();
	                if (typeof r !== 'undefined') {
	                    return r;
	                } else {
	                    return this.lex();
	                }
	            },
	            begin: function begin(condition) {
	                this.conditionStack.push(condition);
	            },
	            popState: function popState() {
	                return this.conditionStack.pop();
	            },
	            _currentRules: function _currentRules() {
	                return this.conditions[this.conditionStack[this.conditionStack.length - 1]].rules;
	            },
	            topState: function topState() {
	                return this.conditionStack[this.conditionStack.length - 2];
	            },
	            pushState: function begin(condition) {
	                this.begin(condition);
	            } };
	        lexer.options = {};
	        lexer.performAction = function anonymous(yy, yy_, $avoiding_name_collisions, YY_START
	        /**/) {

	            function strip(start, end) {
	                return yy_.yytext = yy_.yytext.substr(start, yy_.yyleng - end);
	            }

	            var YYSTATE = YY_START;
	            switch ($avoiding_name_collisions) {
	                case 0:
	                    if (yy_.yytext.slice(-2) === "\\\\") {
	                        strip(0, 1);
	                        this.begin("mu");
	                    } else if (yy_.yytext.slice(-1) === "\\") {
	                        strip(0, 1);
	                        this.begin("emu");
	                    } else {
	                        this.begin("mu");
	                    }
	                    if (yy_.yytext) return 15;

	                    break;
	                case 1:
	                    return 15;
	                    break;
	                case 2:
	                    this.popState();
	                    return 15;

	                    break;
	                case 3:
	                    this.begin('raw');return 15;
	                    break;
	                case 4:
	                    this.popState();
	                    // Should be using `this.topState()` below, but it currently
	                    // returns the second top instead of the first top. Opened an
	                    // issue about it at https://github.com/zaach/jison/issues/291
	                    if (this.conditionStack[this.conditionStack.length - 1] === 'raw') {
	                        return 15;
	                    } else {
	                        yy_.yytext = yy_.yytext.substr(5, yy_.yyleng - 9);
	                        return 'END_RAW_BLOCK';
	                    }

	                    break;
	                case 5:
	                    return 15;
	                    break;
	                case 6:
	                    this.popState();
	                    return 14;

	                    break;
	                case 7:
	                    return 65;
	                    break;
	                case 8:
	                    return 68;
	                    break;
	                case 9:
	                    return 19;
	                    break;
	                case 10:
	                    this.popState();
	                    this.begin('raw');
	                    return 23;

	                    break;
	                case 11:
	                    return 55;
	                    break;
	                case 12:
	                    return 60;
	                    break;
	                case 13:
	                    return 29;
	                    break;
	                case 14:
	                    return 47;
	                    break;
	                case 15:
	                    this.popState();return 44;
	                    break;
	                case 16:
	                    this.popState();return 44;
	                    break;
	                case 17:
	                    return 34;
	                    break;
	                case 18:
	                    return 39;
	                    break;
	                case 19:
	                    return 51;
	                    break;
	                case 20:
	                    return 48;
	                    break;
	                case 21:
	                    this.unput(yy_.yytext);
	                    this.popState();
	                    this.begin('com');

	                    break;
	                case 22:
	                    this.popState();
	                    return 14;

	                    break;
	                case 23:
	                    return 48;
	                    break;
	                case 24:
	                    return 73;
	                    break;
	                case 25:
	                    return 72;
	                    break;
	                case 26:
	                    return 72;
	                    break;
	                case 27:
	                    return 87;
	                    break;
	                case 28:
	                    // ignore whitespace
	                    break;
	                case 29:
	                    this.popState();return 54;
	                    break;
	                case 30:
	                    this.popState();return 33;
	                    break;
	                case 31:
	                    yy_.yytext = strip(1, 2).replace(/\\"/g, '"');return 80;
	                    break;
	                case 32:
	                    yy_.yytext = strip(1, 2).replace(/\\'/g, "'");return 80;
	                    break;
	                case 33:
	                    return 85;
	                    break;
	                case 34:
	                    return 82;
	                    break;
	                case 35:
	                    return 82;
	                    break;
	                case 36:
	                    return 83;
	                    break;
	                case 37:
	                    return 84;
	                    break;
	                case 38:
	                    return 81;
	                    break;
	                case 39:
	                    return 75;
	                    break;
	                case 40:
	                    return 77;
	                    break;
	                case 41:
	                    return 72;
	                    break;
	                case 42:
	                    yy_.yytext = yy_.yytext.replace(/\\([\\\]])/g, '$1');return 72;
	                    break;
	                case 43:
	                    return 'INVALID';
	                    break;
	                case 44:
	                    return 5;
	                    break;
	            }
	        };
	        lexer.rules = [/^(?:[^\x00]*?(?=(\{\{)))/, /^(?:[^\x00]+)/, /^(?:[^\x00]{2,}?(?=(\{\{|\\\{\{|\\\\\{\{|$)))/, /^(?:\{\{\{\{(?=[^\/]))/, /^(?:\{\{\{\{\/[^\s!"#%-,\.\/;->@\[-\^`\{-~]+(?=[=}\s\/.])\}\}\}\})/, /^(?:[^\x00]*?(?=(\{\{\{\{)))/, /^(?:[\s\S]*?--(~)?\}\})/, /^(?:\()/, /^(?:\))/, /^(?:\{\{\{\{)/, /^(?:\}\}\}\})/, /^(?:\{\{(~)?>)/, /^(?:\{\{(~)?#>)/, /^(?:\{\{(~)?#\*?)/, /^(?:\{\{(~)?\/)/, /^(?:\{\{(~)?\^\s*(~)?\}\})/, /^(?:\{\{(~)?\s*else\s*(~)?\}\})/, /^(?:\{\{(~)?\^)/, /^(?:\{\{(~)?\s*else\b)/, /^(?:\{\{(~)?\{)/, /^(?:\{\{(~)?&)/, /^(?:\{\{(~)?!--)/, /^(?:\{\{(~)?![\s\S]*?\}\})/, /^(?:\{\{(~)?\*?)/, /^(?:=)/, /^(?:\.\.)/, /^(?:\.(?=([=~}\s\/.)|])))/, /^(?:[\/.])/, /^(?:\s+)/, /^(?:\}(~)?\}\})/, /^(?:(~)?\}\})/, /^(?:"(\\["]|[^"])*")/, /^(?:'(\\[']|[^'])*')/, /^(?:@)/, /^(?:true(?=([~}\s)])))/, /^(?:false(?=([~}\s)])))/, /^(?:undefined(?=([~}\s)])))/, /^(?:null(?=([~}\s)])))/, /^(?:-?[0-9]+(?:\.[0-9]+)?(?=([~}\s)])))/, /^(?:as\s+\|)/, /^(?:\|)/, /^(?:([^\s!"#%-,\.\/;->@\[-\^`\{-~]+(?=([=~}\s\/.)|]))))/, /^(?:\[(\\\]|[^\]])*\])/, /^(?:.)/, /^(?:$)/];
	        lexer.conditions = { "mu": { "rules": [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44], "inclusive": false }, "emu": { "rules": [2], "inclusive": false }, "com": { "rules": [6], "inclusive": false }, "raw": { "rules": [3, 4, 5], "inclusive": false }, "INITIAL": { "rules": [0, 1, 44], "inclusive": true } };
	        return lexer;
	    })();
	    parser.lexer = lexer;
	    function Parser() {
	        this.yy = {};
	    }Parser.prototype = parser;parser.Parser = Parser;
	    return new Parser();
	})();exports["default"] = handlebars;
	module.exports = exports["default"];

/***/ }),
/* 38 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;

	var _visitor = __webpack_require__(39);

	var _visitor2 = _interopRequireDefault(_visitor);

	function WhitespaceControl() {
	  var options = arguments.length <= 0 || arguments[0] === undefined ? {} : arguments[0];

	  this.options = options;
	}
	WhitespaceControl.prototype = new _visitor2['default']();

	WhitespaceControl.prototype.Program = function (program) {
	  var doStandalone = !this.options.ignoreStandalone;

	  var isRoot = !this.isRootSeen;
	  this.isRootSeen = true;

	  var body = program.body;
	  for (var i = 0, l = body.length; i < l; i++) {
	    var current = body[i],
	        strip = this.accept(current);

	    if (!strip) {
	      continue;
	    }

	    var _isPrevWhitespace = isPrevWhitespace(body, i, isRoot),
	        _isNextWhitespace = isNextWhitespace(body, i, isRoot),
	        openStandalone = strip.openStandalone && _isPrevWhitespace,
	        closeStandalone = strip.closeStandalone && _isNextWhitespace,
	        inlineStandalone = strip.inlineStandalone && _isPrevWhitespace && _isNextWhitespace;

	    if (strip.close) {
	      omitRight(body, i, true);
	    }
	    if (strip.open) {
	      omitLeft(body, i, true);
	    }

	    if (doStandalone && inlineStandalone) {
	      omitRight(body, i);

	      if (omitLeft(body, i)) {
	        // If we are on a standalone node, save the indent info for partials
	        if (current.type === 'PartialStatement') {
	          // Pull out the whitespace from the final line
	          current.indent = /([ \t]+$)/.exec(body[i - 1].original)[1];
	        }
	      }
	    }
	    if (doStandalone && openStandalone) {
	      omitRight((current.program || current.inverse).body);

	      // Strip out the previous content node if it's whitespace only
	      omitLeft(body, i);
	    }
	    if (doStandalone && closeStandalone) {
	      // Always strip the next node
	      omitRight(body, i);

	      omitLeft((current.inverse || current.program).body);
	    }
	  }

	  return program;
	};

	WhitespaceControl.prototype.BlockStatement = WhitespaceControl.prototype.DecoratorBlock = WhitespaceControl.prototype.PartialBlockStatement = function (block) {
	  this.accept(block.program);
	  this.accept(block.inverse);

	  // Find the inverse program that is involed with whitespace stripping.
	  var program = block.program || block.inverse,
	      inverse = block.program && block.inverse,
	      firstInverse = inverse,
	      lastInverse = inverse;

	  if (inverse && inverse.chained) {
	    firstInverse = inverse.body[0].program;

	    // Walk the inverse chain to find the last inverse that is actually in the chain.
	    while (lastInverse.chained) {
	      lastInverse = lastInverse.body[lastInverse.body.length - 1].program;
	    }
	  }

	  var strip = {
	    open: block.openStrip.open,
	    close: block.closeStrip.close,

	    // Determine the standalone candiacy. Basically flag our content as being possibly standalone
	    // so our parent can determine if we actually are standalone
	    openStandalone: isNextWhitespace(program.body),
	    closeStandalone: isPrevWhitespace((firstInverse || program).body)
	  };

	  if (block.openStrip.close) {
	    omitRight(program.body, null, true);
	  }

	  if (inverse) {
	    var inverseStrip = block.inverseStrip;

	    if (inverseStrip.open) {
	      omitLeft(program.body, null, true);
	    }

	    if (inverseStrip.close) {
	      omitRight(firstInverse.body, null, true);
	    }
	    if (block.closeStrip.open) {
	      omitLeft(lastInverse.body, null, true);
	    }

	    // Find standalone else statments
	    if (!this.options.ignoreStandalone && isPrevWhitespace(program.body) && isNextWhitespace(firstInverse.body)) {
	      omitLeft(program.body);
	      omitRight(firstInverse.body);
	    }
	  } else if (block.closeStrip.open) {
	    omitLeft(program.body, null, true);
	  }

	  return strip;
	};

	WhitespaceControl.prototype.Decorator = WhitespaceControl.prototype.MustacheStatement = function (mustache) {
	  return mustache.strip;
	};

	WhitespaceControl.prototype.PartialStatement = WhitespaceControl.prototype.CommentStatement = function (node) {
	  /* istanbul ignore next */
	  var strip = node.strip || {};
	  return {
	    inlineStandalone: true,
	    open: strip.open,
	    close: strip.close
	  };
	};

	function isPrevWhitespace(body, i, isRoot) {
	  if (i === undefined) {
	    i = body.length;
	  }

	  // Nodes that end with newlines are considered whitespace (but are special
	  // cased for strip operations)
	  var prev = body[i - 1],
	      sibling = body[i - 2];
	  if (!prev) {
	    return isRoot;
	  }

	  if (prev.type === 'ContentStatement') {
	    return (sibling || !isRoot ? /\r?\n\s*?$/ : /(^|\r?\n)\s*?$/).test(prev.original);
	  }
	}
	function isNextWhitespace(body, i, isRoot) {
	  if (i === undefined) {
	    i = -1;
	  }

	  var next = body[i + 1],
	      sibling = body[i + 2];
	  if (!next) {
	    return isRoot;
	  }

	  if (next.type === 'ContentStatement') {
	    return (sibling || !isRoot ? /^\s*?\r?\n/ : /^\s*?(\r?\n|$)/).test(next.original);
	  }
	}

	// Marks the node to the right of the position as omitted.
	// I.e. {{foo}}' ' will mark the ' ' node as omitted.
	//
	// If i is undefined, then the first child will be marked as such.
	//
	// If mulitple is truthy then all whitespace will be stripped out until non-whitespace
	// content is met.
	function omitRight(body, i, multiple) {
	  var current = body[i == null ? 0 : i + 1];
	  if (!current || current.type !== 'ContentStatement' || !multiple && current.rightStripped) {
	    return;
	  }

	  var original = current.value;
	  current.value = current.value.replace(multiple ? /^\s+/ : /^[ \t]*\r?\n?/, '');
	  current.rightStripped = current.value !== original;
	}

	// Marks the node to the left of the position as omitted.
	// I.e. ' '{{foo}} will mark the ' ' node as omitted.
	//
	// If i is undefined then the last child will be marked as such.
	//
	// If mulitple is truthy then all whitespace will be stripped out until non-whitespace
	// content is met.
	function omitLeft(body, i, multiple) {
	  var current = body[i == null ? body.length - 1 : i - 1];
	  if (!current || current.type !== 'ContentStatement' || !multiple && current.leftStripped) {
	    return;
	  }

	  // We omit the last node if it's whitespace only and not preceeded by a non-content node.
	  var original = current.value;
	  current.value = current.value.replace(multiple ? /\s+$/ : /[ \t]+$/, '');
	  current.leftStripped = current.value !== original;
	  return current.leftStripped;
	}

	exports['default'] = WhitespaceControl;
	module.exports = exports['default'];

/***/ }),
/* 39 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;

	var _exception = __webpack_require__(6);

	var _exception2 = _interopRequireDefault(_exception);

	function Visitor() {
	  this.parents = [];
	}

	Visitor.prototype = {
	  constructor: Visitor,
	  mutating: false,

	  // Visits a given value. If mutating, will replace the value if necessary.
	  acceptKey: function acceptKey(node, name) {
	    var value = this.accept(node[name]);
	    if (this.mutating) {
	      // Hacky sanity check: This may have a few false positives for type for the helper
	      // methods but will generally do the right thing without a lot of overhead.
	      if (value && !Visitor.prototype[value.type]) {
	        throw new _exception2['default']('Unexpected node type "' + value.type + '" found when accepting ' + name + ' on ' + node.type);
	      }
	      node[name] = value;
	    }
	  },

	  // Performs an accept operation with added sanity check to ensure
	  // required keys are not removed.
	  acceptRequired: function acceptRequired(node, name) {
	    this.acceptKey(node, name);

	    if (!node[name]) {
	      throw new _exception2['default'](node.type + ' requires ' + name);
	    }
	  },

	  // Traverses a given array. If mutating, empty respnses will be removed
	  // for child elements.
	  acceptArray: function acceptArray(array) {
	    for (var i = 0, l = array.length; i < l; i++) {
	      this.acceptKey(array, i);

	      if (!array[i]) {
	        array.splice(i, 1);
	        i--;
	        l--;
	      }
	    }
	  },

	  accept: function accept(object) {
	    if (!object) {
	      return;
	    }

	    /* istanbul ignore next: Sanity code */
	    if (!this[object.type]) {
	      throw new _exception2['default']('Unknown type: ' + object.type, object);
	    }

	    if (this.current) {
	      this.parents.unshift(this.current);
	    }
	    this.current = object;

	    var ret = this[object.type](object);

	    this.current = this.parents.shift();

	    if (!this.mutating || ret) {
	      return ret;
	    } else if (ret !== false) {
	      return object;
	    }
	  },

	  Program: function Program(program) {
	    this.acceptArray(program.body);
	  },

	  MustacheStatement: visitSubExpression,
	  Decorator: visitSubExpression,

	  BlockStatement: visitBlock,
	  DecoratorBlock: visitBlock,

	  PartialStatement: visitPartial,
	  PartialBlockStatement: function PartialBlockStatement(partial) {
	    visitPartial.call(this, partial);

	    this.acceptKey(partial, 'program');
	  },

	  ContentStatement: function ContentStatement() /* content */{},
	  CommentStatement: function CommentStatement() /* comment */{},

	  SubExpression: visitSubExpression,

	  PathExpression: function PathExpression() /* path */{},

	  StringLiteral: function StringLiteral() /* string */{},
	  NumberLiteral: function NumberLiteral() /* number */{},
	  BooleanLiteral: function BooleanLiteral() /* bool */{},
	  UndefinedLiteral: function UndefinedLiteral() /* literal */{},
	  NullLiteral: function NullLiteral() /* literal */{},

	  Hash: function Hash(hash) {
	    this.acceptArray(hash.pairs);
	  },
	  HashPair: function HashPair(pair) {
	    this.acceptRequired(pair, 'value');
	  }
	};

	function visitSubExpression(mustache) {
	  this.acceptRequired(mustache, 'path');
	  this.acceptArray(mustache.params);
	  this.acceptKey(mustache, 'hash');
	}
	function visitBlock(block) {
	  visitSubExpression.call(this, block);

	  this.acceptKey(block, 'program');
	  this.acceptKey(block, 'inverse');
	}
	function visitPartial(partial) {
	  this.acceptRequired(partial, 'name');
	  this.acceptArray(partial.params);
	  this.acceptKey(partial, 'hash');
	}

	exports['default'] = Visitor;
	module.exports = exports['default'];

/***/ }),
/* 40 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;
	exports.SourceLocation = SourceLocation;
	exports.id = id;
	exports.stripFlags = stripFlags;
	exports.stripComment = stripComment;
	exports.preparePath = preparePath;
	exports.prepareMustache = prepareMustache;
	exports.prepareRawBlock = prepareRawBlock;
	exports.prepareBlock = prepareBlock;
	exports.prepareProgram = prepareProgram;
	exports.preparePartialBlock = preparePartialBlock;

	var _exception = __webpack_require__(6);

	var _exception2 = _interopRequireDefault(_exception);

	function validateClose(open, close) {
	  close = close.path ? close.path.original : close;

	  if (open.path.original !== close) {
	    var errorNode = { loc: open.path.loc };

	    throw new _exception2['default'](open.path.original + " doesn't match " + close, errorNode);
	  }
	}

	function SourceLocation(source, locInfo) {
	  this.source = source;
	  this.start = {
	    line: locInfo.first_line,
	    column: locInfo.first_column
	  };
	  this.end = {
	    line: locInfo.last_line,
	    column: locInfo.last_column
	  };
	}

	function id(token) {
	  if (/^\[.*\]$/.test(token)) {
	    return token.substr(1, token.length - 2);
	  } else {
	    return token;
	  }
	}

	function stripFlags(open, close) {
	  return {
	    open: open.charAt(2) === '~',
	    close: close.charAt(close.length - 3) === '~'
	  };
	}

	function stripComment(comment) {
	  return comment.replace(/^\{\{~?\!-?-?/, '').replace(/-?-?~?\}\}$/, '');
	}

	function preparePath(data, parts, loc) {
	  loc = this.locInfo(loc);

	  var original = data ? '@' : '',
	      dig = [],
	      depth = 0,
	      depthString = '';

	  for (var i = 0, l = parts.length; i < l; i++) {
	    var part = parts[i].part,

	    // If we have [] syntax then we do not treat path references as operators,
	    // i.e. foo.[this] resolves to approximately context.foo['this']
	    isLiteral = parts[i].original !== part;
	    original += (parts[i].separator || '') + part;

	    if (!isLiteral && (part === '..' || part === '.' || part === 'this')) {
	      if (dig.length > 0) {
	        throw new _exception2['default']('Invalid path: ' + original, { loc: loc });
	      } else if (part === '..') {
	        depth++;
	        depthString += '../';
	      }
	    } else {
	      dig.push(part);
	    }
	  }

	  return {
	    type: 'PathExpression',
	    data: data,
	    depth: depth,
	    parts: dig,
	    original: original,
	    loc: loc
	  };
	}

	function prepareMustache(path, params, hash, open, strip, locInfo) {
	  // Must use charAt to support IE pre-10
	  var escapeFlag = open.charAt(3) || open.charAt(2),
	      escaped = escapeFlag !== '{' && escapeFlag !== '&';

	  var decorator = /\*/.test(open);
	  return {
	    type: decorator ? 'Decorator' : 'MustacheStatement',
	    path: path,
	    params: params,
	    hash: hash,
	    escaped: escaped,
	    strip: strip,
	    loc: this.locInfo(locInfo)
	  };
	}

	function prepareRawBlock(openRawBlock, contents, close, locInfo) {
	  validateClose(openRawBlock, close);

	  locInfo = this.locInfo(locInfo);
	  var program = {
	    type: 'Program',
	    body: contents,
	    strip: {},
	    loc: locInfo
	  };

	  return {
	    type: 'BlockStatement',
	    path: openRawBlock.path,
	    params: openRawBlock.params,
	    hash: openRawBlock.hash,
	    program: program,
	    openStrip: {},
	    inverseStrip: {},
	    closeStrip: {},
	    loc: locInfo
	  };
	}

	function prepareBlock(openBlock, program, inverseAndProgram, close, inverted, locInfo) {
	  if (close && close.path) {
	    validateClose(openBlock, close);
	  }

	  var decorator = /\*/.test(openBlock.open);

	  program.blockParams = openBlock.blockParams;

	  var inverse = undefined,
	      inverseStrip = undefined;

	  if (inverseAndProgram) {
	    if (decorator) {
	      throw new _exception2['default']('Unexpected inverse block on decorator', inverseAndProgram);
	    }

	    if (inverseAndProgram.chain) {
	      inverseAndProgram.program.body[0].closeStrip = close.strip;
	    }

	    inverseStrip = inverseAndProgram.strip;
	    inverse = inverseAndProgram.program;
	  }

	  if (inverted) {
	    inverted = inverse;
	    inverse = program;
	    program = inverted;
	  }

	  return {
	    type: decorator ? 'DecoratorBlock' : 'BlockStatement',
	    path: openBlock.path,
	    params: openBlock.params,
	    hash: openBlock.hash,
	    program: program,
	    inverse: inverse,
	    openStrip: openBlock.strip,
	    inverseStrip: inverseStrip,
	    closeStrip: close && close.strip,
	    loc: this.locInfo(locInfo)
	  };
	}

	function prepareProgram(statements, loc) {
	  if (!loc && statements.length) {
	    var firstLoc = statements[0].loc,
	        lastLoc = statements[statements.length - 1].loc;

	    /* istanbul ignore else */
	    if (firstLoc && lastLoc) {
	      loc = {
	        source: firstLoc.source,
	        start: {
	          line: firstLoc.start.line,
	          column: firstLoc.start.column
	        },
	        end: {
	          line: lastLoc.end.line,
	          column: lastLoc.end.column
	        }
	      };
	    }
	  }

	  return {
	    type: 'Program',
	    body: statements,
	    strip: {},
	    loc: loc
	  };
	}

	function preparePartialBlock(open, program, close, locInfo) {
	  validateClose(open, close);

	  return {
	    type: 'PartialBlockStatement',
	    name: open.path,
	    params: open.params,
	    hash: open.hash,
	    program: program,
	    openStrip: open.strip,
	    closeStrip: close && close.strip,
	    loc: this.locInfo(locInfo)
	  };
	}

/***/ }),
/* 41 */
/***/ (function(module, exports, __webpack_require__) {

	/* eslint-disable new-cap */

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;
	exports.Compiler = Compiler;
	exports.precompile = precompile;
	exports.compile = compile;

	var _exception = __webpack_require__(6);

	var _exception2 = _interopRequireDefault(_exception);

	var _utils = __webpack_require__(5);

	var _ast = __webpack_require__(35);

	var _ast2 = _interopRequireDefault(_ast);

	var slice = [].slice;

	function Compiler() {}

	// the foundHelper register will disambiguate helper lookup from finding a
	// function in a context. This is necessary for mustache compatibility, which
	// requires that context functions in blocks are evaluated by blockHelperMissing,
	// and then proceed as if the resulting value was provided to blockHelperMissing.

	Compiler.prototype = {
	  compiler: Compiler,

	  equals: function equals(other) {
	    var len = this.opcodes.length;
	    if (other.opcodes.length !== len) {
	      return false;
	    }

	    for (var i = 0; i < len; i++) {
	      var opcode = this.opcodes[i],
	          otherOpcode = other.opcodes[i];
	      if (opcode.opcode !== otherOpcode.opcode || !argEquals(opcode.args, otherOpcode.args)) {
	        return false;
	      }
	    }

	    // We know that length is the same between the two arrays because they are directly tied
	    // to the opcode behavior above.
	    len = this.children.length;
	    for (var i = 0; i < len; i++) {
	      if (!this.children[i].equals(other.children[i])) {
	        return false;
	      }
	    }

	    return true;
	  },

	  guid: 0,

	  compile: function compile(program, options) {
	    this.sourceNode = [];
	    this.opcodes = [];
	    this.children = [];
	    this.options = options;
	    this.stringParams = options.stringParams;
	    this.trackIds = options.trackIds;

	    options.blockParams = options.blockParams || [];

	    // These changes will propagate to the other compiler components
	    var knownHelpers = options.knownHelpers;
	    options.knownHelpers = {
	      'helperMissing': true,
	      'blockHelperMissing': true,
	      'each': true,
	      'if': true,
	      'unless': true,
	      'with': true,
	      'log': true,
	      'lookup': true
	    };
	    if (knownHelpers) {
	      for (var _name in knownHelpers) {
	        /* istanbul ignore else */
	        if (_name in knownHelpers) {
	          this.options.knownHelpers[_name] = knownHelpers[_name];
	        }
	      }
	    }

	    return this.accept(program);
	  },

	  compileProgram: function compileProgram(program) {
	    var childCompiler = new this.compiler(),
	        // eslint-disable-line new-cap
	    result = childCompiler.compile(program, this.options),
	        guid = this.guid++;

	    this.usePartial = this.usePartial || result.usePartial;

	    this.children[guid] = result;
	    this.useDepths = this.useDepths || result.useDepths;

	    return guid;
	  },

	  accept: function accept(node) {
	    /* istanbul ignore next: Sanity code */
	    if (!this[node.type]) {
	      throw new _exception2['default']('Unknown type: ' + node.type, node);
	    }

	    this.sourceNode.unshift(node);
	    var ret = this[node.type](node);
	    this.sourceNode.shift();
	    return ret;
	  },

	  Program: function Program(program) {
	    this.options.blockParams.unshift(program.blockParams);

	    var body = program.body,
	        bodyLength = body.length;
	    for (var i = 0; i < bodyLength; i++) {
	      this.accept(body[i]);
	    }

	    this.options.blockParams.shift();

	    this.isSimple = bodyLength === 1;
	    this.blockParams = program.blockParams ? program.blockParams.length : 0;

	    return this;
	  },

	  BlockStatement: function BlockStatement(block) {
	    transformLiteralToPath(block);

	    var program = block.program,
	        inverse = block.inverse;

	    program = program && this.compileProgram(program);
	    inverse = inverse && this.compileProgram(inverse);

	    var type = this.classifySexpr(block);

	    if (type === 'helper') {
	      this.helperSexpr(block, program, inverse);
	    } else if (type === 'simple') {
	      this.simpleSexpr(block);

	      // now that the simple mustache is resolved, we need to
	      // evaluate it by executing `blockHelperMissing`
	      this.opcode('pushProgram', program);
	      this.opcode('pushProgram', inverse);
	      this.opcode('emptyHash');
	      this.opcode('blockValue', block.path.original);
	    } else {
	      this.ambiguousSexpr(block, program, inverse);

	      // now that the simple mustache is resolved, we need to
	      // evaluate it by executing `blockHelperMissing`
	      this.opcode('pushProgram', program);
	      this.opcode('pushProgram', inverse);
	      this.opcode('emptyHash');
	      this.opcode('ambiguousBlockValue');
	    }

	    this.opcode('append');
	  },

	  DecoratorBlock: function DecoratorBlock(decorator) {
	    var program = decorator.program && this.compileProgram(decorator.program);
	    var params = this.setupFullMustacheParams(decorator, program, undefined),
	        path = decorator.path;

	    this.useDecorators = true;
	    this.opcode('registerDecorator', params.length, path.original);
	  },

	  PartialStatement: function PartialStatement(partial) {
	    this.usePartial = true;

	    var program = partial.program;
	    if (program) {
	      program = this.compileProgram(partial.program);
	    }

	    var params = partial.params;
	    if (params.length > 1) {
	      throw new _exception2['default']('Unsupported number of partial arguments: ' + params.length, partial);
	    } else if (!params.length) {
	      if (this.options.explicitPartialContext) {
	        this.opcode('pushLiteral', 'undefined');
	      } else {
	        params.push({ type: 'PathExpression', parts: [], depth: 0 });
	      }
	    }

	    var partialName = partial.name.original,
	        isDynamic = partial.name.type === 'SubExpression';
	    if (isDynamic) {
	      this.accept(partial.name);
	    }

	    this.setupFullMustacheParams(partial, program, undefined, true);

	    var indent = partial.indent || '';
	    if (this.options.preventIndent && indent) {
	      this.opcode('appendContent', indent);
	      indent = '';
	    }

	    this.opcode('invokePartial', isDynamic, partialName, indent);
	    this.opcode('append');
	  },
	  PartialBlockStatement: function PartialBlockStatement(partialBlock) {
	    this.PartialStatement(partialBlock);
	  },

	  MustacheStatement: function MustacheStatement(mustache) {
	    this.SubExpression(mustache);

	    if (mustache.escaped && !this.options.noEscape) {
	      this.opcode('appendEscaped');
	    } else {
	      this.opcode('append');
	    }
	  },
	  Decorator: function Decorator(decorator) {
	    this.DecoratorBlock(decorator);
	  },

	  ContentStatement: function ContentStatement(content) {
	    if (content.value) {
	      this.opcode('appendContent', content.value);
	    }
	  },

	  CommentStatement: function CommentStatement() {},

	  SubExpression: function SubExpression(sexpr) {
	    transformLiteralToPath(sexpr);
	    var type = this.classifySexpr(sexpr);

	    if (type === 'simple') {
	      this.simpleSexpr(sexpr);
	    } else if (type === 'helper') {
	      this.helperSexpr(sexpr);
	    } else {
	      this.ambiguousSexpr(sexpr);
	    }
	  },
	  ambiguousSexpr: function ambiguousSexpr(sexpr, program, inverse) {
	    var path = sexpr.path,
	        name = path.parts[0],
	        isBlock = program != null || inverse != null;

	    this.opcode('getContext', path.depth);

	    this.opcode('pushProgram', program);
	    this.opcode('pushProgram', inverse);

	    path.strict = true;
	    this.accept(path);

	    this.opcode('invokeAmbiguous', name, isBlock);
	  },

	  simpleSexpr: function simpleSexpr(sexpr) {
	    var path = sexpr.path;
	    path.strict = true;
	    this.accept(path);
	    this.opcode('resolvePossibleLambda');
	  },

	  helperSexpr: function helperSexpr(sexpr, program, inverse) {
	    var params = this.setupFullMustacheParams(sexpr, program, inverse),
	        path = sexpr.path,
	        name = path.parts[0];

	    if (this.options.knownHelpers[name]) {
	      this.opcode('invokeKnownHelper', params.length, name);
	    } else if (this.options.knownHelpersOnly) {
	      throw new _exception2['default']('You specified knownHelpersOnly, but used the unknown helper ' + name, sexpr);
	    } else {
	      path.strict = true;
	      path.falsy = true;

	      this.accept(path);
	      this.opcode('invokeHelper', params.length, path.original, _ast2['default'].helpers.simpleId(path));
	    }
	  },

	  PathExpression: function PathExpression(path) {
	    this.addDepth(path.depth);
	    this.opcode('getContext', path.depth);

	    var name = path.parts[0],
	        scoped = _ast2['default'].helpers.scopedId(path),
	        blockParamId = !path.depth && !scoped && this.blockParamIndex(name);

	    if (blockParamId) {
	      this.opcode('lookupBlockParam', blockParamId, path.parts);
	    } else if (!name) {
	      // Context reference, i.e. `{{foo .}}` or `{{foo ..}}`
	      this.opcode('pushContext');
	    } else if (path.data) {
	      this.options.data = true;
	      this.opcode('lookupData', path.depth, path.parts, path.strict);
	    } else {
	      this.opcode('lookupOnContext', path.parts, path.falsy, path.strict, scoped);
	    }
	  },

	  StringLiteral: function StringLiteral(string) {
	    this.opcode('pushString', string.value);
	  },

	  NumberLiteral: function NumberLiteral(number) {
	    this.opcode('pushLiteral', number.value);
	  },

	  BooleanLiteral: function BooleanLiteral(bool) {
	    this.opcode('pushLiteral', bool.value);
	  },

	  UndefinedLiteral: function UndefinedLiteral() {
	    this.opcode('pushLiteral', 'undefined');
	  },

	  NullLiteral: function NullLiteral() {
	    this.opcode('pushLiteral', 'null');
	  },

	  Hash: function Hash(hash) {
	    var pairs = hash.pairs,
	        i = 0,
	        l = pairs.length;

	    this.opcode('pushHash');

	    for (; i < l; i++) {
	      this.pushParam(pairs[i].value);
	    }
	    while (i--) {
	      this.opcode('assignToHash', pairs[i].key);
	    }
	    this.opcode('popHash');
	  },

	  // HELPERS
	  opcode: function opcode(name) {
	    this.opcodes.push({ opcode: name, args: slice.call(arguments, 1), loc: this.sourceNode[0].loc });
	  },

	  addDepth: function addDepth(depth) {
	    if (!depth) {
	      return;
	    }

	    this.useDepths = true;
	  },

	  classifySexpr: function classifySexpr(sexpr) {
	    var isSimple = _ast2['default'].helpers.simpleId(sexpr.path);

	    var isBlockParam = isSimple && !!this.blockParamIndex(sexpr.path.parts[0]);

	    // a mustache is an eligible helper if:
	    // * its id is simple (a single part, not `this` or `..`)
	    var isHelper = !isBlockParam && _ast2['default'].helpers.helperExpression(sexpr);

	    // if a mustache is an eligible helper but not a definite
	    // helper, it is ambiguous, and will be resolved in a later
	    // pass or at runtime.
	    var isEligible = !isBlockParam && (isHelper || isSimple);

	    // if ambiguous, we can possibly resolve the ambiguity now
	    // An eligible helper is one that does not have a complex path, i.e. `this.foo`, `../foo` etc.
	    if (isEligible && !isHelper) {
	      var _name2 = sexpr.path.parts[0],
	          options = this.options;

	      if (options.knownHelpers[_name2]) {
	        isHelper = true;
	      } else if (options.knownHelpersOnly) {
	        isEligible = false;
	      }
	    }

	    if (isHelper) {
	      return 'helper';
	    } else if (isEligible) {
	      return 'ambiguous';
	    } else {
	      return 'simple';
	    }
	  },

	  pushParams: function pushParams(params) {
	    for (var i = 0, l = params.length; i < l; i++) {
	      this.pushParam(params[i]);
	    }
	  },

	  pushParam: function pushParam(val) {
	    var value = val.value != null ? val.value : val.original || '';

	    if (this.stringParams) {
	      if (value.replace) {
	        value = value.replace(/^(\.?\.\/)*/g, '').replace(/\//g, '.');
	      }

	      if (val.depth) {
	        this.addDepth(val.depth);
	      }
	      this.opcode('getContext', val.depth || 0);
	      this.opcode('pushStringParam', value, val.type);

	      if (val.type === 'SubExpression') {
	        // SubExpressions get evaluated and passed in
	        // in string params mode.
	        this.accept(val);
	      }
	    } else {
	      if (this.trackIds) {
	        var blockParamIndex = undefined;
	        if (val.parts && !_ast2['default'].helpers.scopedId(val) && !val.depth) {
	          blockParamIndex = this.blockParamIndex(val.parts[0]);
	        }
	        if (blockParamIndex) {
	          var blockParamChild = val.parts.slice(1).join('.');
	          this.opcode('pushId', 'BlockParam', blockParamIndex, blockParamChild);
	        } else {
	          value = val.original || value;
	          if (value.replace) {
	            value = value.replace(/^this(?:\.|$)/, '').replace(/^\.\//, '').replace(/^\.$/, '');
	          }

	          this.opcode('pushId', val.type, value);
	        }
	      }
	      this.accept(val);
	    }
	  },

	  setupFullMustacheParams: function setupFullMustacheParams(sexpr, program, inverse, omitEmpty) {
	    var params = sexpr.params;
	    this.pushParams(params);

	    this.opcode('pushProgram', program);
	    this.opcode('pushProgram', inverse);

	    if (sexpr.hash) {
	      this.accept(sexpr.hash);
	    } else {
	      this.opcode('emptyHash', omitEmpty);
	    }

	    return params;
	  },

	  blockParamIndex: function blockParamIndex(name) {
	    for (var depth = 0, len = this.options.blockParams.length; depth < len; depth++) {
	      var blockParams = this.options.blockParams[depth],
	          param = blockParams && _utils.indexOf(blockParams, name);
	      if (blockParams && param >= 0) {
	        return [depth, param];
	      }
	    }
	  }
	};

	function precompile(input, options, env) {
	  if (input == null || typeof input !== 'string' && input.type !== 'Program') {
	    throw new _exception2['default']('You must pass a string or Handlebars AST to Handlebars.precompile. You passed ' + input);
	  }

	  options = options || {};
	  if (!('data' in options)) {
	    options.data = true;
	  }
	  if (options.compat) {
	    options.useDepths = true;
	  }

	  var ast = env.parse(input, options),
	      environment = new env.Compiler().compile(ast, options);
	  return new env.JavaScriptCompiler().compile(environment, options);
	}

	function compile(input, options, env) {
	  if (options === undefined) options = {};

	  if (input == null || typeof input !== 'string' && input.type !== 'Program') {
	    throw new _exception2['default']('You must pass a string or Handlebars AST to Handlebars.compile. You passed ' + input);
	  }

	  options = _utils.extend({}, options);
	  if (!('data' in options)) {
	    options.data = true;
	  }
	  if (options.compat) {
	    options.useDepths = true;
	  }

	  var compiled = undefined;

	  function compileInput() {
	    var ast = env.parse(input, options),
	        environment = new env.Compiler().compile(ast, options),
	        templateSpec = new env.JavaScriptCompiler().compile(environment, options, undefined, true);
	    return env.template(templateSpec);
	  }

	  // Template is only compiled on first use and cached after that point.
	  function ret(context, execOptions) {
	    if (!compiled) {
	      compiled = compileInput();
	    }
	    return compiled.call(this, context, execOptions);
	  }
	  ret._setup = function (setupOptions) {
	    if (!compiled) {
	      compiled = compileInput();
	    }
	    return compiled._setup(setupOptions);
	  };
	  ret._child = function (i, data, blockParams, depths) {
	    if (!compiled) {
	      compiled = compileInput();
	    }
	    return compiled._child(i, data, blockParams, depths);
	  };
	  return ret;
	}

	function argEquals(a, b) {
	  if (a === b) {
	    return true;
	  }

	  if (_utils.isArray(a) && _utils.isArray(b) && a.length === b.length) {
	    for (var i = 0; i < a.length; i++) {
	      if (!argEquals(a[i], b[i])) {
	        return false;
	      }
	    }
	    return true;
	  }
	}

	function transformLiteralToPath(sexpr) {
	  if (!sexpr.path.parts) {
	    var literal = sexpr.path;
	    // Casting to string here to make false and 0 literal values play nicely with the rest
	    // of the system.
	    sexpr.path = {
	      type: 'PathExpression',
	      data: false,
	      depth: 0,
	      parts: [literal.original + ''],
	      original: literal.original + '',
	      loc: literal.loc
	    };
	  }
	}

/***/ }),
/* 42 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';

	var _interopRequireDefault = __webpack_require__(1)['default'];

	exports.__esModule = true;

	var _base = __webpack_require__(4);

	var _exception = __webpack_require__(6);

	var _exception2 = _interopRequireDefault(_exception);

	var _utils = __webpack_require__(5);

	var _codeGen = __webpack_require__(43);

	var _codeGen2 = _interopRequireDefault(_codeGen);

	function Literal(value) {
	  this.value = value;
	}

	function JavaScriptCompiler() {}

	JavaScriptCompiler.prototype = {
	  // PUBLIC API: You can override these methods in a subclass to provide
	  // alternative compiled forms for name lookup and buffering semantics
	  nameLookup: function nameLookup(parent, name /* , type*/) {
	    if (JavaScriptCompiler.isValidJavaScriptVariableName(name)) {
	      return [parent, '.', name];
	    } else {
	      return [parent, '[', JSON.stringify(name), ']'];
	    }
	  },
	  depthedLookup: function depthedLookup(name) {
	    return [this.aliasable('container.lookup'), '(depths, "', name, '")'];
	  },

	  compilerInfo: function compilerInfo() {
	    var revision = _base.COMPILER_REVISION,
	        versions = _base.REVISION_CHANGES[revision];
	    return [revision, versions];
	  },

	  appendToBuffer: function appendToBuffer(source, location, explicit) {
	    // Force a source as this simplifies the merge logic.
	    if (!_utils.isArray(source)) {
	      source = [source];
	    }
	    source = this.source.wrap(source, location);

	    if (this.environment.isSimple) {
	      return ['return ', source, ';'];
	    } else if (explicit) {
	      // This is a case where the buffer operation occurs as a child of another
	      // construct, generally braces. We have to explicitly output these buffer
	      // operations to ensure that the emitted code goes in the correct location.
	      return ['buffer += ', source, ';'];
	    } else {
	      source.appendToBuffer = true;
	      return source;
	    }
	  },

	  initializeBuffer: function initializeBuffer() {
	    return this.quotedString('');
	  },
	  // END PUBLIC API

	  compile: function compile(environment, options, context, asObject) {
	    this.environment = environment;
	    this.options = options;
	    this.stringParams = this.options.stringParams;
	    this.trackIds = this.options.trackIds;
	    this.precompile = !asObject;

	    this.name = this.environment.name;
	    this.isChild = !!context;
	    this.context = context || {
	      decorators: [],
	      programs: [],
	      environments: []
	    };

	    this.preamble();

	    this.stackSlot = 0;
	    this.stackVars = [];
	    this.aliases = {};
	    this.registers = { list: [] };
	    this.hashes = [];
	    this.compileStack = [];
	    this.inlineStack = [];
	    this.blockParams = [];

	    this.compileChildren(environment, options);

	    this.useDepths = this.useDepths || environment.useDepths || environment.useDecorators || this.options.compat;
	    this.useBlockParams = this.useBlockParams || environment.useBlockParams;

	    var opcodes = environment.opcodes,
	        opcode = undefined,
	        firstLoc = undefined,
	        i = undefined,
	        l = undefined;

	    for (i = 0, l = opcodes.length; i < l; i++) {
	      opcode = opcodes[i];

	      this.source.currentLocation = opcode.loc;
	      firstLoc = firstLoc || opcode.loc;
	      this[opcode.opcode].apply(this, opcode.args);
	    }

	    // Flush any trailing content that might be pending.
	    this.source.currentLocation = firstLoc;
	    this.pushSource('');

	    /* istanbul ignore next */
	    if (this.stackSlot || this.inlineStack.length || this.compileStack.length) {
	      throw new _exception2['default']('Compile completed with content left on stack');
	    }

	    if (!this.decorators.isEmpty()) {
	      this.useDecorators = true;

	      this.decorators.prepend('var decorators = container.decorators;\n');
	      this.decorators.push('return fn;');

	      if (asObject) {
	        this.decorators = Function.apply(this, ['fn', 'props', 'container', 'depth0', 'data', 'blockParams', 'depths', this.decorators.merge()]);
	      } else {
	        this.decorators.prepend('function(fn, props, container, depth0, data, blockParams, depths) {\n');
	        this.decorators.push('}\n');
	        this.decorators = this.decorators.merge();
	      }
	    } else {
	      this.decorators = undefined;
	    }

	    var fn = this.createFunctionContext(asObject);
	    if (!this.isChild) {
	      var ret = {
	        compiler: this.compilerInfo(),
	        main: fn
	      };

	      if (this.decorators) {
	        ret.main_d = this.decorators; // eslint-disable-line camelcase
	        ret.useDecorators = true;
	      }

	      var _context = this.context;
	      var programs = _context.programs;
	      var decorators = _context.decorators;

	      for (i = 0, l = programs.length; i < l; i++) {
	        if (programs[i]) {
	          ret[i] = programs[i];
	          if (decorators[i]) {
	            ret[i + '_d'] = decorators[i];
	            ret.useDecorators = true;
	          }
	        }
	      }

	      if (this.environment.usePartial) {
	        ret.usePartial = true;
	      }
	      if (this.options.data) {
	        ret.useData = true;
	      }
	      if (this.useDepths) {
	        ret.useDepths = true;
	      }
	      if (this.useBlockParams) {
	        ret.useBlockParams = true;
	      }
	      if (this.options.compat) {
	        ret.compat = true;
	      }

	      if (!asObject) {
	        ret.compiler = JSON.stringify(ret.compiler);

	        this.source.currentLocation = { start: { line: 1, column: 0 } };
	        ret = this.objectLiteral(ret);

	        if (options.srcName) {
	          ret = ret.toStringWithSourceMap({ file: options.destName });
	          ret.map = ret.map && ret.map.toString();
	        } else {
	          ret = ret.toString();
	        }
	      } else {
	        ret.compilerOptions = this.options;
	      }

	      return ret;
	    } else {
	      return fn;
	    }
	  },

	  preamble: function preamble() {
	    // track the last context pushed into place to allow skipping the
	    // getContext opcode when it would be a noop
	    this.lastContext = 0;
	    this.source = new _codeGen2['default'](this.options.srcName);
	    this.decorators = new _codeGen2['default'](this.options.srcName);
	  },

	  createFunctionContext: function createFunctionContext(asObject) {
	    var varDeclarations = '';

	    var locals = this.stackVars.concat(this.registers.list);
	    if (locals.length > 0) {
	      varDeclarations += ', ' + locals.join(', ');
	    }

	    // Generate minimizer alias mappings
	    //
	    // When using true SourceNodes, this will update all references to the given alias
	    // as the source nodes are reused in situ. For the non-source node compilation mode,
	    // aliases will not be used, but this case is already being run on the client and
	    // we aren't concern about minimizing the template size.
	    var aliasCount = 0;
	    for (var alias in this.aliases) {
	      // eslint-disable-line guard-for-in
	      var node = this.aliases[alias];

	      if (this.aliases.hasOwnProperty(alias) && node.children && node.referenceCount > 1) {
	        varDeclarations += ', alias' + ++aliasCount + '=' + alias;
	        node.children[0] = 'alias' + aliasCount;
	      }
	    }

	    var params = ['container', 'depth0', 'helpers', 'partials', 'data'];

	    if (this.useBlockParams || this.useDepths) {
	      params.push('blockParams');
	    }
	    if (this.useDepths) {
	      params.push('depths');
	    }

	    // Perform a second pass over the output to merge content when possible
	    var source = this.mergeSource(varDeclarations);

	    if (asObject) {
	      params.push(source);

	      return Function.apply(this, params);
	    } else {
	      return this.source.wrap(['function(', params.join(','), ') {\n  ', source, '}']);
	    }
	  },
	  mergeSource: function mergeSource(varDeclarations) {
	    var isSimple = this.environment.isSimple,
	        appendOnly = !this.forceBuffer,
	        appendFirst = undefined,
	        sourceSeen = undefined,
	        bufferStart = undefined,
	        bufferEnd = undefined;
	    this.source.each(function (line) {
	      if (line.appendToBuffer) {
	        if (bufferStart) {
	          line.prepend('  + ');
	        } else {
	          bufferStart = line;
	        }
	        bufferEnd = line;
	      } else {
	        if (bufferStart) {
	          if (!sourceSeen) {
	            appendFirst = true;
	          } else {
	            bufferStart.prepend('buffer += ');
	          }
	          bufferEnd.add(';');
	          bufferStart = bufferEnd = undefined;
	        }

	        sourceSeen = true;
	        if (!isSimple) {
	          appendOnly = false;
	        }
	      }
	    });

	    if (appendOnly) {
	      if (bufferStart) {
	        bufferStart.prepend('return ');
	        bufferEnd.add(';');
	      } else if (!sourceSeen) {
	        this.source.push('return "";');
	      }
	    } else {
	      varDeclarations += ', buffer = ' + (appendFirst ? '' : this.initializeBuffer());

	      if (bufferStart) {
	        bufferStart.prepend('return buffer + ');
	        bufferEnd.add(';');
	      } else {
	        this.source.push('return buffer;');
	      }
	    }

	    if (varDeclarations) {
	      this.source.prepend('var ' + varDeclarations.substring(2) + (appendFirst ? '' : ';\n'));
	    }

	    return this.source.merge();
	  },

	  // [blockValue]
	  //
	  // On stack, before: hash, inverse, program, value
	  // On stack, after: return value of blockHelperMissing
	  //
	  // The purpose of this opcode is to take a block of the form
	  // `{{#this.foo}}...{{/this.foo}}`, resolve the value of `foo`, and
	  // replace it on the stack with the result of properly
	  // invoking blockHelperMissing.
	  blockValue: function blockValue(name) {
	    var blockHelperMissing = this.aliasable('helpers.blockHelperMissing'),
	        params = [this.contextName(0)];
	    this.setupHelperArgs(name, 0, params);

	    var blockName = this.popStack();
	    params.splice(1, 0, blockName);

	    this.push(this.source.functionCall(blockHelperMissing, 'call', params));
	  },

	  // [ambiguousBlockValue]
	  //
	  // On stack, before: hash, inverse, program, value
	  // Compiler value, before: lastHelper=value of last found helper, if any
	  // On stack, after, if no lastHelper: same as [blockValue]
	  // On stack, after, if lastHelper: value
	  ambiguousBlockValue: function ambiguousBlockValue() {
	    // We're being a bit cheeky and reusing the options value from the prior exec
	    var blockHelperMissing = this.aliasable('helpers.blockHelperMissing'),
	        params = [this.contextName(0)];
	    this.setupHelperArgs('', 0, params, true);

	    this.flushInline();

	    var current = this.topStack();
	    params.splice(1, 0, current);

	    this.pushSource(['if (!', this.lastHelper, ') { ', current, ' = ', this.source.functionCall(blockHelperMissing, 'call', params), '}']);
	  },

	  // [appendContent]
	  //
	  // On stack, before: ...
	  // On stack, after: ...
	  //
	  // Appends the string value of `content` to the current buffer
	  appendContent: function appendContent(content) {
	    if (this.pendingContent) {
	      content = this.pendingContent + content;
	    } else {
	      this.pendingLocation = this.source.currentLocation;
	    }

	    this.pendingContent = content;
	  },

	  // [append]
	  //
	  // On stack, before: value, ...
	  // On stack, after: ...
	  //
	  // Coerces `value` to a String and appends it to the current buffer.
	  //
	  // If `value` is truthy, or 0, it is coerced into a string and appended
	  // Otherwise, the empty string is appended
	  append: function append() {
	    if (this.isInline()) {
	      this.replaceStack(function (current) {
	        return [' != null ? ', current, ' : ""'];
	      });

	      this.pushSource(this.appendToBuffer(this.popStack()));
	    } else {
	      var local = this.popStack();
	      this.pushSource(['if (', local, ' != null) { ', this.appendToBuffer(local, undefined, true), ' }']);
	      if (this.environment.isSimple) {
	        this.pushSource(['else { ', this.appendToBuffer("''", undefined, true), ' }']);
	      }
	    }
	  },

	  // [appendEscaped]
	  //
	  // On stack, before: value, ...
	  // On stack, after: ...
	  //
	  // Escape `value` and append it to the buffer
	  appendEscaped: function appendEscaped() {
	    this.pushSource(this.appendToBuffer([this.aliasable('container.escapeExpression'), '(', this.popStack(), ')']));
	  },

	  // [getContext]
	  //
	  // On stack, before: ...
	  // On stack, after: ...
	  // Compiler value, after: lastContext=depth
	  //
	  // Set the value of the `lastContext` compiler value to the depth
	  getContext: function getContext(depth) {
	    this.lastContext = depth;
	  },

	  // [pushContext]
	  //
	  // On stack, before: ...
	  // On stack, after: currentContext, ...
	  //
	  // Pushes the value of the current context onto the stack.
	  pushContext: function pushContext() {
	    this.pushStackLiteral(this.contextName(this.lastContext));
	  },

	  // [lookupOnContext]
	  //
	  // On stack, before: ...
	  // On stack, after: currentContext[name], ...
	  //
	  // Looks up the value of `name` on the current context and pushes
	  // it onto the stack.
	  lookupOnContext: function lookupOnContext(parts, falsy, strict, scoped) {
	    var i = 0;

	    if (!scoped && this.options.compat && !this.lastContext) {
	      // The depthed query is expected to handle the undefined logic for the root level that
	      // is implemented below, so we evaluate that directly in compat mode
	      this.push(this.depthedLookup(parts[i++]));
	    } else {
	      this.pushContext();
	    }

	    this.resolvePath('context', parts, i, falsy, strict);
	  },

	  // [lookupBlockParam]
	  //
	  // On stack, before: ...
	  // On stack, after: blockParam[name], ...
	  //
	  // Looks up the value of `parts` on the given block param and pushes
	  // it onto the stack.
	  lookupBlockParam: function lookupBlockParam(blockParamId, parts) {
	    this.useBlockParams = true;

	    this.push(['blockParams[', blockParamId[0], '][', blockParamId[1], ']']);
	    this.resolvePath('context', parts, 1);
	  },

	  // [lookupData]
	  //
	  // On stack, before: ...
	  // On stack, after: data, ...
	  //
	  // Push the data lookup operator
	  lookupData: function lookupData(depth, parts, strict) {
	    if (!depth) {
	      this.pushStackLiteral('data');
	    } else {
	      this.pushStackLiteral('container.data(data, ' + depth + ')');
	    }

	    this.resolvePath('data', parts, 0, true, strict);
	  },

	  resolvePath: function resolvePath(type, parts, i, falsy, strict) {
	    // istanbul ignore next

	    var _this = this;

	    if (this.options.strict || this.options.assumeObjects) {
	      this.push(strictLookup(this.options.strict && strict, this, parts, type));
	      return;
	    }

	    var len = parts.length;
	    for (; i < len; i++) {
	      /* eslint-disable no-loop-func */
	      this.replaceStack(function (current) {
	        var lookup = _this.nameLookup(current, parts[i], type);
	        // We want to ensure that zero and false are handled properly if the context (falsy flag)
	        // needs to have the special handling for these values.
	        if (!falsy) {
	          return [' != null ? ', lookup, ' : ', current];
	        } else {
	          // Otherwise we can use generic falsy handling
	          return [' && ', lookup];
	        }
	      });
	      /* eslint-enable no-loop-func */
	    }
	  },

	  // [resolvePossibleLambda]
	  //
	  // On stack, before: value, ...
	  // On stack, after: resolved value, ...
	  //
	  // If the `value` is a lambda, replace it on the stack by
	  // the return value of the lambda
	  resolvePossibleLambda: function resolvePossibleLambda() {
	    this.push([this.aliasable('container.lambda'), '(', this.popStack(), ', ', this.contextName(0), ')']);
	  },

	  // [pushStringParam]
	  //
	  // On stack, before: ...
	  // On stack, after: string, currentContext, ...
	  //
	  // This opcode is designed for use in string mode, which
	  // provides the string value of a parameter along with its
	  // depth rather than resolving it immediately.
	  pushStringParam: function pushStringParam(string, type) {
	    this.pushContext();
	    this.pushString(type);

	    // If it's a subexpression, the string result
	    // will be pushed after this opcode.
	    if (type !== 'SubExpression') {
	      if (typeof string === 'string') {
	        this.pushString(string);
	      } else {
	        this.pushStackLiteral(string);
	      }
	    }
	  },

	  emptyHash: function emptyHash(omitEmpty) {
	    if (this.trackIds) {
	      this.push('{}'); // hashIds
	    }
	    if (this.stringParams) {
	      this.push('{}'); // hashContexts
	      this.push('{}'); // hashTypes
	    }
	    this.pushStackLiteral(omitEmpty ? 'undefined' : '{}');
	  },
	  pushHash: function pushHash() {
	    if (this.hash) {
	      this.hashes.push(this.hash);
	    }
	    this.hash = { values: [], types: [], contexts: [], ids: [] };
	  },
	  popHash: function popHash() {
	    var hash = this.hash;
	    this.hash = this.hashes.pop();

	    if (this.trackIds) {
	      this.push(this.objectLiteral(hash.ids));
	    }
	    if (this.stringParams) {
	      this.push(this.objectLiteral(hash.contexts));
	      this.push(this.objectLiteral(hash.types));
	    }

	    this.push(this.objectLiteral(hash.values));
	  },

	  // [pushString]
	  //
	  // On stack, before: ...
	  // On stack, after: quotedString(string), ...
	  //
	  // Push a quoted version of `string` onto the stack
	  pushString: function pushString(string) {
	    this.pushStackLiteral(this.quotedString(string));
	  },

	  // [pushLiteral]
	  //
	  // On stack, before: ...
	  // On stack, after: value, ...
	  //
	  // Pushes a value onto the stack. This operation prevents
	  // the compiler from creating a temporary variable to hold
	  // it.
	  pushLiteral: function pushLiteral(value) {
	    this.pushStackLiteral(value);
	  },

	  // [pushProgram]
	  //
	  // On stack, before: ...
	  // On stack, after: program(guid), ...
	  //
	  // Push a program expression onto the stack. This takes
	  // a compile-time guid and converts it into a runtime-accessible
	  // expression.
	  pushProgram: function pushProgram(guid) {
	    if (guid != null) {
	      this.pushStackLiteral(this.programExpression(guid));
	    } else {
	      this.pushStackLiteral(null);
	    }
	  },

	  // [registerDecorator]
	  //
	  // On stack, before: hash, program, params..., ...
	  // On stack, after: ...
	  //
	  // Pops off the decorator's parameters, invokes the decorator,
	  // and inserts the decorator into the decorators list.
	  registerDecorator: function registerDecorator(paramSize, name) {
	    var foundDecorator = this.nameLookup('decorators', name, 'decorator'),
	        options = this.setupHelperArgs(name, paramSize);

	    this.decorators.push(['fn = ', this.decorators.functionCall(foundDecorator, '', ['fn', 'props', 'container', options]), ' || fn;']);
	  },

	  // [invokeHelper]
	  //
	  // On stack, before: hash, inverse, program, params..., ...
	  // On stack, after: result of helper invocation
	  //
	  // Pops off the helper's parameters, invokes the helper,
	  // and pushes the helper's return value onto the stack.
	  //
	  // If the helper is not found, `helperMissing` is called.
	  invokeHelper: function invokeHelper(paramSize, name, isSimple) {
	    var nonHelper = this.popStack(),
	        helper = this.setupHelper(paramSize, name),
	        simple = isSimple ? [helper.name, ' || '] : '';

	    var lookup = ['('].concat(simple, nonHelper);
	    if (!this.options.strict) {
	      lookup.push(' || ', this.aliasable('helpers.helperMissing'));
	    }
	    lookup.push(')');

	    this.push(this.source.functionCall(lookup, 'call', helper.callParams));
	  },

	  // [invokeKnownHelper]
	  //
	  // On stack, before: hash, inverse, program, params..., ...
	  // On stack, after: result of helper invocation
	  //
	  // This operation is used when the helper is known to exist,
	  // so a `helperMissing` fallback is not required.
	  invokeKnownHelper: function invokeKnownHelper(paramSize, name) {
	    var helper = this.setupHelper(paramSize, name);
	    this.push(this.source.functionCall(helper.name, 'call', helper.callParams));
	  },

	  // [invokeAmbiguous]
	  //
	  // On stack, before: hash, inverse, program, params..., ...
	  // On stack, after: result of disambiguation
	  //
	  // This operation is used when an expression like `{{foo}}`
	  // is provided, but we don't know at compile-time whether it
	  // is a helper or a path.
	  //
	  // This operation emits more code than the other options,
	  // and can be avoided by passing the `knownHelpers` and
	  // `knownHelpersOnly` flags at compile-time.
	  invokeAmbiguous: function invokeAmbiguous(name, helperCall) {
	    this.useRegister('helper');

	    var nonHelper = this.popStack();

	    this.emptyHash();
	    var helper = this.setupHelper(0, name, helperCall);

	    var helperName = this.lastHelper = this.nameLookup('helpers', name, 'helper');

	    var lookup = ['(', '(helper = ', helperName, ' || ', nonHelper, ')'];
	    if (!this.options.strict) {
	      lookup[0] = '(helper = ';
	      lookup.push(' != null ? helper : ', this.aliasable('helpers.helperMissing'));
	    }

	    this.push(['(', lookup, helper.paramsInit ? ['),(', helper.paramsInit] : [], '),', '(typeof helper === ', this.aliasable('"function"'), ' ? ', this.source.functionCall('helper', 'call', helper.callParams), ' : helper))']);
	  },

	  // [invokePartial]
	  //
	  // On stack, before: context, ...
	  // On stack after: result of partial invocation
	  //
	  // This operation pops off a context, invokes a partial with that context,
	  // and pushes the result of the invocation back.
	  invokePartial: function invokePartial(isDynamic, name, indent) {
	    var params = [],
	        options = this.setupParams(name, 1, params);

	    if (isDynamic) {
	      name = this.popStack();
	      delete options.name;
	    }

	    if (indent) {
	      options.indent = JSON.stringify(indent);
	    }
	    options.helpers = 'helpers';
	    options.partials = 'partials';
	    options.decorators = 'container.decorators';

	    if (!isDynamic) {
	      params.unshift(this.nameLookup('partials', name, 'partial'));
	    } else {
	      params.unshift(name);
	    }

	    if (this.options.compat) {
	      options.depths = 'depths';
	    }
	    options = this.objectLiteral(options);
	    params.push(options);

	    this.push(this.source.functionCall('container.invokePartial', '', params));
	  },

	  // [assignToHash]
	  //
	  // On stack, before: value, ..., hash, ...
	  // On stack, after: ..., hash, ...
	  //
	  // Pops a value off the stack and assigns it to the current hash
	  assignToHash: function assignToHash(key) {
	    var value = this.popStack(),
	        context = undefined,
	        type = undefined,
	        id = undefined;

	    if (this.trackIds) {
	      id = this.popStack();
	    }
	    if (this.stringParams) {
	      type = this.popStack();
	      context = this.popStack();
	    }

	    var hash = this.hash;
	    if (context) {
	      hash.contexts[key] = context;
	    }
	    if (type) {
	      hash.types[key] = type;
	    }
	    if (id) {
	      hash.ids[key] = id;
	    }
	    hash.values[key] = value;
	  },

	  pushId: function pushId(type, name, child) {
	    if (type === 'BlockParam') {
	      this.pushStackLiteral('blockParams[' + name[0] + '].path[' + name[1] + ']' + (child ? ' + ' + JSON.stringify('.' + child) : ''));
	    } else if (type === 'PathExpression') {
	      this.pushString(name);
	    } else if (type === 'SubExpression') {
	      this.pushStackLiteral('true');
	    } else {
	      this.pushStackLiteral('null');
	    }
	  },

	  // HELPERS

	  compiler: JavaScriptCompiler,

	  compileChildren: function compileChildren(environment, options) {
	    var children = environment.children,
	        child = undefined,
	        compiler = undefined;

	    for (var i = 0, l = children.length; i < l; i++) {
	      child = children[i];
	      compiler = new this.compiler(); // eslint-disable-line new-cap

	      var existing = this.matchExistingProgram(child);

	      if (existing == null) {
	        this.context.programs.push(''); // Placeholder to prevent name conflicts for nested children
	        var index = this.context.programs.length;
	        child.index = index;
	        child.name = 'program' + index;
	        this.context.programs[index] = compiler.compile(child, options, this.context, !this.precompile);
	        this.context.decorators[index] = compiler.decorators;
	        this.context.environments[index] = child;

	        this.useDepths = this.useDepths || compiler.useDepths;
	        this.useBlockParams = this.useBlockParams || compiler.useBlockParams;
	        child.useDepths = this.useDepths;
	        child.useBlockParams = this.useBlockParams;
	      } else {
	        child.index = existing.index;
	        child.name = 'program' + existing.index;

	        this.useDepths = this.useDepths || existing.useDepths;
	        this.useBlockParams = this.useBlockParams || existing.useBlockParams;
	      }
	    }
	  },
	  matchExistingProgram: function matchExistingProgram(child) {
	    for (var i = 0, len = this.context.environments.length; i < len; i++) {
	      var environment = this.context.environments[i];
	      if (environment && environment.equals(child)) {
	        return environment;
	      }
	    }
	  },

	  programExpression: function programExpression(guid) {
	    var child = this.environment.children[guid],
	        programParams = [child.index, 'data', child.blockParams];

	    if (this.useBlockParams || this.useDepths) {
	      programParams.push('blockParams');
	    }
	    if (this.useDepths) {
	      programParams.push('depths');
	    }

	    return 'container.program(' + programParams.join(', ') + ')';
	  },

	  useRegister: function useRegister(name) {
	    if (!this.registers[name]) {
	      this.registers[name] = true;
	      this.registers.list.push(name);
	    }
	  },

	  push: function push(expr) {
	    if (!(expr instanceof Literal)) {
	      expr = this.source.wrap(expr);
	    }

	    this.inlineStack.push(expr);
	    return expr;
	  },

	  pushStackLiteral: function pushStackLiteral(item) {
	    this.push(new Literal(item));
	  },

	  pushSource: function pushSource(source) {
	    if (this.pendingContent) {
	      this.source.push(this.appendToBuffer(this.source.quotedString(this.pendingContent), this.pendingLocation));
	      this.pendingContent = undefined;
	    }

	    if (source) {
	      this.source.push(source);
	    }
	  },

	  replaceStack: function replaceStack(callback) {
	    var prefix = ['('],
	        stack = undefined,
	        createdStack = undefined,
	        usedLiteral = undefined;

	    /* istanbul ignore next */
	    if (!this.isInline()) {
	      throw new _exception2['default']('replaceStack on non-inline');
	    }

	    // We want to merge the inline statement into the replacement statement via ','
	    var top = this.popStack(true);

	    if (top instanceof Literal) {
	      // Literals do not need to be inlined
	      stack = [top.value];
	      prefix = ['(', stack];
	      usedLiteral = true;
	    } else {
	      // Get or create the current stack name for use by the inline
	      createdStack = true;
	      var _name = this.incrStack();

	      prefix = ['((', this.push(_name), ' = ', top, ')'];
	      stack = this.topStack();
	    }

	    var item = callback.call(this, stack);

	    if (!usedLiteral) {
	      this.popStack();
	    }
	    if (createdStack) {
	      this.stackSlot--;
	    }
	    this.push(prefix.concat(item, ')'));
	  },

	  incrStack: function incrStack() {
	    this.stackSlot++;
	    if (this.stackSlot > this.stackVars.length) {
	      this.stackVars.push('stack' + this.stackSlot);
	    }
	    return this.topStackName();
	  },
	  topStackName: function topStackName() {
	    return 'stack' + this.stackSlot;
	  },
	  flushInline: function flushInline() {
	    var inlineStack = this.inlineStack;
	    this.inlineStack = [];
	    for (var i = 0, len = inlineStack.length; i < len; i++) {
	      var entry = inlineStack[i];
	      /* istanbul ignore if */
	      if (entry instanceof Literal) {
	        this.compileStack.push(entry);
	      } else {
	        var stack = this.incrStack();
	        this.pushSource([stack, ' = ', entry, ';']);
	        this.compileStack.push(stack);
	      }
	    }
	  },
	  isInline: function isInline() {
	    return this.inlineStack.length;
	  },

	  popStack: function popStack(wrapped) {
	    var inline = this.isInline(),
	        item = (inline ? this.inlineStack : this.compileStack).pop();

	    if (!wrapped && item instanceof Literal) {
	      return item.value;
	    } else {
	      if (!inline) {
	        /* istanbul ignore next */
	        if (!this.stackSlot) {
	          throw new _exception2['default']('Invalid stack pop');
	        }
	        this.stackSlot--;
	      }
	      return item;
	    }
	  },

	  topStack: function topStack() {
	    var stack = this.isInline() ? this.inlineStack : this.compileStack,
	        item = stack[stack.length - 1];

	    /* istanbul ignore if */
	    if (item instanceof Literal) {
	      return item.value;
	    } else {
	      return item;
	    }
	  },

	  contextName: function contextName(context) {
	    if (this.useDepths && context) {
	      return 'depths[' + context + ']';
	    } else {
	      return 'depth' + context;
	    }
	  },

	  quotedString: function quotedString(str) {
	    return this.source.quotedString(str);
	  },

	  objectLiteral: function objectLiteral(obj) {
	    return this.source.objectLiteral(obj);
	  },

	  aliasable: function aliasable(name) {
	    var ret = this.aliases[name];
	    if (ret) {
	      ret.referenceCount++;
	      return ret;
	    }

	    ret = this.aliases[name] = this.source.wrap(name);
	    ret.aliasable = true;
	    ret.referenceCount = 1;

	    return ret;
	  },

	  setupHelper: function setupHelper(paramSize, name, blockHelper) {
	    var params = [],
	        paramsInit = this.setupHelperArgs(name, paramSize, params, blockHelper);
	    var foundHelper = this.nameLookup('helpers', name, 'helper'),
	        callContext = this.aliasable(this.contextName(0) + ' != null ? ' + this.contextName(0) + ' : (container.nullContext || {})');

	    return {
	      params: params,
	      paramsInit: paramsInit,
	      name: foundHelper,
	      callParams: [callContext].concat(params)
	    };
	  },

	  setupParams: function setupParams(helper, paramSize, params) {
	    var options = {},
	        contexts = [],
	        types = [],
	        ids = [],
	        objectArgs = !params,
	        param = undefined;

	    if (objectArgs) {
	      params = [];
	    }

	    options.name = this.quotedString(helper);
	    options.hash = this.popStack();

	    if (this.trackIds) {
	      options.hashIds = this.popStack();
	    }
	    if (this.stringParams) {
	      options.hashTypes = this.popStack();
	      options.hashContexts = this.popStack();
	    }

	    var inverse = this.popStack(),
	        program = this.popStack();

	    // Avoid setting fn and inverse if neither are set. This allows
	    // helpers to do a check for `if (options.fn)`
	    if (program || inverse) {
	      options.fn = program || 'container.noop';
	      options.inverse = inverse || 'container.noop';
	    }

	    // The parameters go on to the stack in order (making sure that they are evaluated in order)
	    // so we need to pop them off the stack in reverse order
	    var i = paramSize;
	    while (i--) {
	      param = this.popStack();
	      params[i] = param;

	      if (this.trackIds) {
	        ids[i] = this.popStack();
	      }
	      if (this.stringParams) {
	        types[i] = this.popStack();
	        contexts[i] = this.popStack();
	      }
	    }

	    if (objectArgs) {
	      options.args = this.source.generateArray(params);
	    }

	    if (this.trackIds) {
	      options.ids = this.source.generateArray(ids);
	    }
	    if (this.stringParams) {
	      options.types = this.source.generateArray(types);
	      options.contexts = this.source.generateArray(contexts);
	    }

	    if (this.options.data) {
	      options.data = 'data';
	    }
	    if (this.useBlockParams) {
	      options.blockParams = 'blockParams';
	    }
	    return options;
	  },

	  setupHelperArgs: function setupHelperArgs(helper, paramSize, params, useRegister) {
	    var options = this.setupParams(helper, paramSize, params);
	    options = this.objectLiteral(options);
	    if (useRegister) {
	      this.useRegister('options');
	      params.push('options');
	      return ['options=', options];
	    } else if (params) {
	      params.push(options);
	      return '';
	    } else {
	      return options;
	    }
	  }
	};

	(function () {
	  var reservedWords = ('break else new var' + ' case finally return void' + ' catch for switch while' + ' continue function this with' + ' default if throw' + ' delete in try' + ' do instanceof typeof' + ' abstract enum int short' + ' boolean export interface static' + ' byte extends long super' + ' char final native synchronized' + ' class float package throws' + ' const goto private transient' + ' debugger implements protected volatile' + ' double import public let yield await' + ' null true false').split(' ');

	  var compilerWords = JavaScriptCompiler.RESERVED_WORDS = {};

	  for (var i = 0, l = reservedWords.length; i < l; i++) {
	    compilerWords[reservedWords[i]] = true;
	  }
	})();

	JavaScriptCompiler.isValidJavaScriptVariableName = function (name) {
	  return !JavaScriptCompiler.RESERVED_WORDS[name] && /^[a-zA-Z_$][0-9a-zA-Z_$]*$/.test(name);
	};

	function strictLookup(requireTerminal, compiler, parts, type) {
	  var stack = compiler.popStack(),
	      i = 0,
	      len = parts.length;
	  if (requireTerminal) {
	    len--;
	  }

	  for (; i < len; i++) {
	    stack = compiler.nameLookup(stack, parts[i], type);
	  }

	  if (requireTerminal) {
	    return [compiler.aliasable('container.strict'), '(', stack, ', ', compiler.quotedString(parts[i]), ')'];
	  } else {
	    return stack;
	  }
	}

	exports['default'] = JavaScriptCompiler;
	module.exports = exports['default'];

/***/ }),
/* 43 */
/***/ (function(module, exports, __webpack_require__) {

	/* global define */
	'use strict';

	exports.__esModule = true;

	var _utils = __webpack_require__(5);

	var SourceNode = undefined;

	try {
	  /* istanbul ignore next */
	  if (false) {
	    // We don't support this in AMD environments. For these environments, we asusme that
	    // they are running on the browser and thus have no need for the source-map library.
	    var SourceMap = require('source-map');
	    SourceNode = SourceMap.SourceNode;
	  }
	} catch (err) {}
	/* NOP */

	/* istanbul ignore if: tested but not covered in istanbul due to dist build  */
	if (!SourceNode) {
	  SourceNode = function (line, column, srcFile, chunks) {
	    this.src = '';
	    if (chunks) {
	      this.add(chunks);
	    }
	  };
	  /* istanbul ignore next */
	  SourceNode.prototype = {
	    add: function add(chunks) {
	      if (_utils.isArray(chunks)) {
	        chunks = chunks.join('');
	      }
	      this.src += chunks;
	    },
	    prepend: function prepend(chunks) {
	      if (_utils.isArray(chunks)) {
	        chunks = chunks.join('');
	      }
	      this.src = chunks + this.src;
	    },
	    toStringWithSourceMap: function toStringWithSourceMap() {
	      return { code: this.toString() };
	    },
	    toString: function toString() {
	      return this.src;
	    }
	  };
	}

	function castChunk(chunk, codeGen, loc) {
	  if (_utils.isArray(chunk)) {
	    var ret = [];

	    for (var i = 0, len = chunk.length; i < len; i++) {
	      ret.push(codeGen.wrap(chunk[i], loc));
	    }
	    return ret;
	  } else if (typeof chunk === 'boolean' || typeof chunk === 'number') {
	    // Handle primitives that the SourceNode will throw up on
	    return chunk + '';
	  }
	  return chunk;
	}

	function CodeGen(srcFile) {
	  this.srcFile = srcFile;
	  this.source = [];
	}

	CodeGen.prototype = {
	  isEmpty: function isEmpty() {
	    return !this.source.length;
	  },
	  prepend: function prepend(source, loc) {
	    this.source.unshift(this.wrap(source, loc));
	  },
	  push: function push(source, loc) {
	    this.source.push(this.wrap(source, loc));
	  },

	  merge: function merge() {
	    var source = this.empty();
	    this.each(function (line) {
	      source.add(['  ', line, '\n']);
	    });
	    return source;
	  },

	  each: function each(iter) {
	    for (var i = 0, len = this.source.length; i < len; i++) {
	      iter(this.source[i]);
	    }
	  },

	  empty: function empty() {
	    var loc = this.currentLocation || { start: {} };
	    return new SourceNode(loc.start.line, loc.start.column, this.srcFile);
	  },
	  wrap: function wrap(chunk) {
	    var loc = arguments.length <= 1 || arguments[1] === undefined ? this.currentLocation || { start: {} } : arguments[1];

	    if (chunk instanceof SourceNode) {
	      return chunk;
	    }

	    chunk = castChunk(chunk, this, loc);

	    return new SourceNode(loc.start.line, loc.start.column, this.srcFile, chunk);
	  },

	  functionCall: function functionCall(fn, type, params) {
	    params = this.generateList(params);
	    return this.wrap([fn, type ? '.' + type + '(' : '(', params, ')']);
	  },

	  quotedString: function quotedString(str) {
	    return '"' + (str + '').replace(/\\/g, '\\\\').replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r').replace(/\u2028/g, '\\u2028') // Per Ecma-262 7.3 + 7.8.4
	    .replace(/\u2029/g, '\\u2029') + '"';
	  },

	  objectLiteral: function objectLiteral(obj) {
	    var pairs = [];

	    for (var key in obj) {
	      if (obj.hasOwnProperty(key)) {
	        var value = castChunk(obj[key], this);
	        if (value !== 'undefined') {
	          pairs.push([this.quotedString(key), ':', value]);
	        }
	      }
	    }

	    var ret = this.generateList(pairs);
	    ret.prepend('{');
	    ret.add('}');
	    return ret;
	  },

	  generateList: function generateList(entries) {
	    var ret = this.empty();

	    for (var i = 0, len = entries.length; i < len; i++) {
	      if (i) {
	        ret.add(',');
	      }

	      ret.add(castChunk(entries[i], this));
	    }

	    return ret;
	  },

	  generateArray: function generateArray(entries) {
	    var ret = this.generateList(entries);
	    ret.prepend('[');
	    ret.add(']');

	    return ret;
	  }
	};

	exports['default'] = CodeGen;
	module.exports = exports['default'];

/***/ })
/******/ ])
});
;
Handlebars.registerHelper('extention', function(url) {
	url = url.fn(this).split('.');
	return url.pop();
});

Handlebars.registerHelper('extention_icon', function(data) {
	return extension_wise_img(data.fn(this));
});

Handlebars.registerHelper('feedSignedUrl', function(url, element, post_id) {
	return feedSignedUrl(url, element, post_id);
});

Handlebars.registerHelper('endorse', function(endorse_by_me, endorse_by_others, options) {
  if(endorse_by_me.length && !endorse_by_others.length) {
    return 'You';
  }else if(endorse_by_me.length && endorse_by_others.length) {
    if(endorse_by_others.length > 1)
      return 'You, '+endorseUserWrap(endorse_by_others[0]['user'])+' and '+ endorseUsersList(endorse_by_others.slice(1));
    else 
      return 'You & '+endorseUserWrap(endorse_by_others[0]['user']);
  } else if(!endorse_by_me.length && endorse_by_others.length){
    switch(endorse_by_others.length){
      case 1:{
        return endorseUserWrap(endorse_by_others[0]['user']);
      }case 2:{
        return endorseUserWrap(endorse_by_others[0]['user'])+' & '+endorseUserWrap(endorse_by_others[1]['user']);
      }case 3:{
        return endorseUserWrap(endorse_by_others[0]['user'])+', '+endorseUserWrap(endorse_by_others[1]['user'])+' & '+endorseUserWrap(endorse_by_others[2]['user']);
      } default: {
        return endorseUserWrap(endorse_by_others[0]['user'])+', '+endorseUserWrap(endorse_by_others[1]['user'])+' & '+ endorseUsersList(endorse_by_others.slice(2));
      }
    }
  }
});

Handlebars.registerHelper("counter", function (index){
  return index + 1;
});

Handlebars.registerHelper('compareValue', function(string, options) {
    if(!string){
      return options.inverse(this);
    }
    var found = string.match("youtube.com");
    if(found){
      return options.fn(this);
    }
    return options.inverse(this);
}) 

Handlebars.registerHelper('checkPostVisibility', function(string, options) {
    var array = string.split(',');
    var found = array.indexOf("All");
    if(found != -1){
      return options.fn(this);
    }
    return options.inverse(this);
}) 

Handlebars.registerHelper('addLink', function(string,limit, options) {
   var description = linkify(string);
   if(limit){
     var text = htmlSubstring(description, limit);
     return text.replace(/(<br( \/)?>\s*)*$/,'');
   }
   return description;
}) 

Handlebars.registerHelper('dateFormat', function(date_string, format, options) {
  return stringDateFormat(date_string, format);
})

Handlebars.registerHelper('postDescriptionTextCheck', function(string, limit, options) {
  if(string.length>limit){
    return options.fn(this);
  }
  return options.inverse(this);
})

Handlebars.registerHelper('limitText', function(string, limit, options) {
  return limitText(string, limit);
})

Handlebars.registerHelper('isSingleImage', function(images, options) {
  if(images.length > 1) return false;
  return 'single-image';
})

Handlebars.registerHelper('indexInfo', function(object, index, options) {
  return object[index];
})

Handlebars.registerHelper('compare', function(first_value, second_value, options) {
  if(first_value === second_value) {
    return options.fn(this);
  }
  return options.inverse(this);
})

Handlebars.registerHelper('count', function(first_value, second_value, options) {
  if(first_value > second_value) {
    return options.fn(this);
  }
  return options.inverse(this);
})

Handlebars.registerHelper('ifCond', function (first_value, operator, second_value, options) {
    switch (operator) {
        case '==':
            return (first_value == second_value) ? options.fn(this) : options.inverse(this);
        case '===':
            return (first_value === second_value) ? options.fn(this) : options.inverse(this);
        case '!=':
            return (first_value != second_value) ? options.fn(this) : options.inverse(this);
        case '!==':
            return (first_value !== second_value) ? options.fn(this) : options.inverse(this);
        case '<':
            return (first_value < second_value) ? options.fn(this) : options.inverse(this);
        case '<=':
            return (first_value <= second_value) ? options.fn(this) : options.inverse(this);
        case '>':
            return (first_value > second_value) ? options.fn(this) : options.inverse(this);
        case '>=':
            return (first_value >= second_value) ? options.fn(this) : options.inverse(this);
        case '&&':
            return (first_value && second_value) ? options.fn(this) : options.inverse(this);
        case '||':
            return (first_value || second_value) ? options.fn(this) : options.inverse(this);
        default:
            return options.inverse(this);
    }
});

Handlebars.registerHelper('getPostUsers', function(string) {
  if(space_users.length == 0){
    return 'Restricted';
  }
  var users_array = string.split(',');
  return getUsersListHtml(users_array);
});

Handlebars.registerHelper('commentLimit', function(loop_index, comment_length, limit, options) {
  if(loop_index < ((comment_length+1)-limit)) {
    return options.fn(this);
  }
  return options.inverse(this);
});

Handlebars.registerHelper('math', function (first_value, operator, second_value, options) {

  switch (operator) {
    case '-':
      return first_value - second_value;
    case '+':
      return first_value + second_value;
    case '/':
      return (first_value / second_value).toFixed(2);
    default:
      return options.inverse(this);
  }
});

Handlebars.registerHelper('minus',function(number, decreased_by, context){
  return number-decreased_by;
});

Handlebars.registerHelper('removePreviewedDocument',function(documents, file, context){
  $(documents).each(function(index){
    if(file.length && this.id == file[0]['id']) {
      documents.splice(index, 1);
      return true;
    }
  });
  this['documents'] = documents;
});

Handlebars.registerHelper('escape', function(variable) {
  return variable.replace(/(['"])/g, '\\$1');
});

Handlebars.registerHelper('toJson', function(variable) {
  return JSON.stringify(variable);
});

Handlebars.registerHelper('calculateRAGColor', function(date_string) {
  var now = moment(new Date());
  var end = moment(date_string);
  if(!end.isValid() || !date_string){
    var days = 0;
  } else {
    var duration = moment.duration(now.diff(end));
    var days = duration.asDays();
  }

  if(days >0 && days <=7 ) return 'green';
  else if(days >7 && days <=14) return 'yellow';
  else return 'red';
});

Handlebars.registerHelper('checkUserIsPostOwnerOrAdmin', function(is_admin, logged_in_user, post_owner, options) {
  if(is_admin || (logged_in_user == post_owner) ){
    return options.fn(this);
  }
  return options.inverse(this);
});

Handlebars.registerHelper('userStatusByInvitationCode',function(invitation_code){
    var status = 'Pending';
    if(invitation_code > 0) status = 'Active';
    else if(invitation_code < 0) status = 'Cancelled';

    return status;
});

Handlebars.registerHelper('isProfileImageExist', function (profile_image){
  if(profile_image){
    return profile_image;
  }
  return baseurl+'/images/dummy-avatar-img.svg';
});

Handlebars.registerHelper('isMobileDevice', function(options) {
  if(isMobileDevice()){
    return options.fn(this);
  }
  return options.inverse(this);
});
var space_category;

$(document).ready(function(){
  getFeed();
  setTimeout(function() {
      var unpin_post = $('.unpin_post').length;
      if(unpin_post == 4){
        $('.pin_post').remove();
      }
  }, 5000);
  $(document).on('click','.edit-post-dropdown .delete_post',function(){
    var post_id = $(this).attr('post_id');
    $('.delete_posted').attr('href',baseurl+'/delete_post/'+post_id); 
  });
});
path_name = 'clientshare';
var temp_key = '';
var feed_xhr = null;
var content_found = sticky_sidebar_stop = true;
var post_category;
var single_post_data;
var post_null = false;
var post_feed_data = {
  'is_scroll': 1,
  'offset':0,
  'space_id':$('.space_id_hidden').val()
};
var counter = 0;
  
$(window).bind('scroll', function() {
  post_feed_data['is_scroll'] = 1;
  if($(window).scrollTop() >= $('#load_more_post').offset().top + $('#load_more_post').outerHeight() - window.innerHeight - 10 && content_found === true && !feed_xhr){
    getFeed();
  }
});

function feedbackPopup(){
  rating = getQueryVariable('feedback_rating');
  if(!rating) return;
  $.ajax({
    type: 'POST',
    dataType: 'html',
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    data: {'rating':rating},
    url: baseurl+'/feedback_popup/'+session_space_id,
    success: function(responce){
      $('#feedback-popup').remove();
      $('.post-modal').append(responce);
      $('#feedback-popup').find('input:radio[value='+rating+']').trigger('click');
      $('#feedback-popup').modal('show');
    }
  });
}

$('.executive_show_less,.executive_show_more a').hide();
$(document).ready(function(){
  var check_feedback_status = $('.check_feedback_on_off_status').val();
  if(check_feedback_status == 1)
      $('.give-feedback-buyer').trigger('click');  

  if(single_post_id)
    $('.single-post-popup .post-feed-section > iframe').remove();

  if(feedback_flag)
    $('#feedback-popup').modal('show');

  feedbackPopup();
  
  $(document).on('click','span.more-attachments', function(){
    $(this).closest('.post-block').find('.findmedia.hiddenable').toggleClass('hidden');
    $(this).closest('.post-block').find('.more-attachments').toggleClass('hidden');
  });

  $('[data-toggle="popover"]').popover();
  $(document).on('click','.dropdown-toggle',function(){
    $('.popover').css('display', 'none', 'important');
  });

   $(document).on('mouseover','.dropdown-toggle',function(){
       if($(".visible-dropdown-view").hasClass('open')){
          $('.popover').css('display', 'none', 'important');
       }
   });

   $(document).on('mouseover','.visible-setting',function(){
       if($(".visible-dropdown-view").hasClass('open')){
          $('.popover').css('display', 'none', 'important');
       }
   });

  $(document).on('click','.filter_post_category',function(){
    post_null = false;
    post_feed_data['is_scroll'] = 0;
    post_feed_data['offset'] = 0;
    post_feed_data['category'] = $(this).attr('key');
      if(temp_key != $(this).attr('key')){
        $('.filter-select').removeClass('category-selected filter-select').addClass('disable');
      }
      if ( $( this ).hasClass( "category-selected" ) ) {
        $(this).removeClass('category-selected').addClass('disable');
      post_feed_data['category'] = '';
        temp_key = '';
      }else{
        $(this).removeClass('disable').addClass('category-selected filter-select');
        temp_key = $(this).attr('key');
      }
    sticky_sidebar_stop = true;
    refreshFeed();
  });

  $(document).on('click','.show_extra_content',function(){
      var full_description = $(this).closest('.post-description').find('.full_description');
      full_description.removeClass('hidden').show();
      $(this).closest('.post-description').find('.trim_description').addClass('hidden');
  });

  $(document).on('click','.show_less_content',function(){
    $(this).closest('.post-description').find('.trim_description').removeClass('hidden').show();
    $(this).closest('.post-description').find('.full_description').addClass('hidden');
  });

  $(document).on('paste', '.comment-add-section div[contenteditable="true"]', function(event){
      pasteAsPlainText(this, event);   
  });

});

function endorseUserWrap(user){
  return '<a style="text-decoration:none; color:#0D47A1" href="#!" onclick="liked_info(this);" data-id="'+user['id']+'">'+user['fullname']+'</a>';
}

function endorseUsersList(users){
    var user_list =  '';
    $.each(users, function(user_index, user){
      user_list += user['user']['fullname'] + '<br>';
      if(user_index >= 5){
        if(parseInt(user_index) === 5){
            user_list += 'and '+ (users.length - 5) +' others'; 
          }
        return false;
       }
    });
  return '<a href="#!" class="endorse-user" data-toggle="modal" data-target="#endoresedpopup"><span class="visible_tooltip endorse-user'+ users[0]['post_id'] +' " data-trigger="hover" type="button" data-toggle="popover" data-placement="top" data-html="true" title="" data-content="'+ user_list + '" data-original-title=""><span id="example_popover" class="other-endorse endorsed_popup" endors-poup-post="'+ users[0]['post_id'] +'" space-id="'+current_space_id+'">' + (users.length) + ' others</span></span></a>';
}

function getFeed(){
  category = $('.categories-wrap').find('.category-selected').attr('key');
  category = category?category:'';
  offset = $('.post-block').length;
  if(!post_null) {
  feed_xhr = $.ajax({
    type: 'GET',
    dataType: 'JSON',
    url: baseurl+'/posts?space_id='+session_space_id+'&offset='+offset+'&category='+category,
    beforeSend: function(){
      $('.show_puff').show();
    },
    success: function(response){
      var parser = new DOMParser;
      var source = $("#post_template").html();
      var template = Handlebars.compile(source);
      var html=''; 
      $.each(response.posts, function(index, post){
        post_category = response.space_category;
        post['space_category'] = post_category;
        var post_subject = parser.parseFromString(
                  '<!doctype html><body>' + post.post_subject,
                  'text/html');
        post.post_subject = post_subject.body.textContent;
        post['logged_user'] = response.user;
        post['baseurl'] = baseurl;
        post['is_logged_in_user_admin'] = is_logged_in_user_admin;
        post['feature_restriction'] = response.feature_restriction;
        html += template(post);
      });
      $('#load_more_post').before(html);
      $('#load_more_post').show();
      $('[data-toggle="popover"]').popover();
      $(".show_extra_content").parent().attr("href","javascript:void();");
      onYouTubeIframeAPIReady();
      videoPlayLog();
      if(response.posts.length == 0) {
          post_null = true;
      }
    },complete:function(){
      $('.show_puff').hide();
      $('.lazy-loading.post').hide();
      feed_xhr = null;
      sticky_sidebar_stop = false;
      if($(window).width() > 767 && $('.feed-col-right .post-wrap').length > 3) {
          $('#left-content, .right-content').theiaStickySidebar({
              additionalMarginTop: 68
          });
      }
      hidePinPostOption();
    }
  });
  }
}

function getPost(post_id, filters){
  $.ajax({
    type: 'GET',
    dataType: 'JSON',
    data:filters,
    url: baseurl+'/post/'+post_id+'?space_id='+session_space_id,
    success: function(response) {
      getpostContent(post_id,response);
      videoPlayLog();
    },complete:function(){
      feed_xhr = null;
      hidePinPostOption();
    }
  });
}

function hidePinPostOption() {
  var unpin_post = $('.unpin_post').length;
  if(unpin_post == 4) {
    $('.pin_post').remove();
  }
}

function getpostContent(post_id, data){
  var source = $("#post_template").html();
  var template = Handlebars.compile(source);
  var html='';
  $.each(data.posts, function(index, post){
    post['space_category'] = post_category;
    post['logged_user'] = data.user;
    post['baseurl'] = baseurl;
    post['is_logged_in_user_admin'] = is_logged_in_user_admin;
    post['feature_restriction'] = data.feature_restriction;
    html += template(post);
  });
  $('.post-block.'+post_id).replaceWith(html);
  $('[data-toggle="popover"]').popover();
}

function getEndorseContent(post_id, data){ 
      var source = $("#post_template").html();
      var source = $(source).find('.like-detail-section').html();
      var template = Handlebars.compile(source);
      var html=''; 
      $.each(data.posts, function(index, post){ 
        post['baseurl'] = baseurl;
        html += template(post);
      });
      $('.post-block.'+post_id+' .like-detail-section').html(html);
      $('[data-toggle="popover"]').popover();
}

function refreshPost(post_id, filters){
  return getPost(post_id, filters);
}

function refreshFeed() {
  $('.post-block').remove();
  $('.lazy-loading.post').show();
  getFeed();
}

function getExtention(url){
  ext = url.split('.');
  return ext.pop();
}

function feedRequest(){
  category = post_feed_data['category']?'&tokencategory='+post_feed_data['category']:'';
  if (feed_xhr) {
    if (category) {
      feed_xhr.abort();
      feed_xhr = null;
    } else {
        return;
    }
  }
  if(post_feed_data['is_scroll'] === 0) {
    $('.feed-col-right .post-wrap').remove();
  }
  feed_xhr = $.ajax({
    type: "GET",
    dataType: "html",
    url: baseurl+'/get_ajax_posts/'+post_feed_data['space_id']+'?limit='+post_feed_data['offset']+category,
    beforeSend:function(){
      $('.show_puff').show();
    },
    success: function(response) { 
      $(window).scrollTop($(window).scrollTop()-2);
      if(response === ""){
        $('#load_more_post').hide();
        $('.show_puff').hide();
        content_found = false;
        $('#load_more_post').before('<div class="post-wrap"><div class="post"><div class="no-result-div"><div class="no-result-col"><i class="fa fa-search" aria-hidden="true"></i><p>No result found.</p></div></div></div></div>');
      }else{
        content_found = true;
        $('#load_more_post').show();
      }
      
      $('#load_more_post').before(response);
      $('.load_ajax_new_posts').val(0);
      $('.post_show_hidden').val(3);
      post_feed_data['offset'] += 3;

      video_player_log_bind();
      onYouTubeIframeAPIReady();
      postsLoadedSuccessfully();
      $('[data-toggle="popover"]').popover();
    },
    error: function(xhr, status, error) {
      $('.load_ajax_new_posts').val(0);
      $('.show_puff').hide();
    },
    complete:function(){
      $('.show_puff').hide();
      $('.lazy-loading.post').hide();
      feed_xhr = null;
      hidePinPostOption();
    }
  });
}


function getTwitterFeeds() {
  $.ajax({
    type: 'get',
    url: baseurl +"/get_twitter_feeds?space_id="+current_space_id,
    success: function (response) {
      if(response)
        $('.twitter-feed-section-dashboard').html(response);

      return false;
    },
    error:function(xhr, status, error) {
      logErrorOnPage(xhr, status, error, 'getTwitterFeeds');
    }
  });
}

function getTopPost(){
    var date = new Date();
    $.ajax({
      type: "GET",
      url: baseurl+'/gettopthreepost?month='+ (date.getMonth()+1) +'&year='+ date.getFullYear()+'&company=&space_id='+current_space_id,
      success: function( response ) {
        $('.top-post-ajax-div').html(response);
        $('.top-post-front, .lazy-loading.top-post').toggleClass('hidden');
      }
    });
}
function changeUrl(page, url) {
    if (typeof (history.pushState) != "undefined") {
      var obj = {Page: page, Url: url};
      single_post_view = null;  
      history.pushState(obj, obj.Page, obj.Url);
    } else {
      console.log("This browser does not support HTML5.");
    }
}

window.addEventListener('popstate', function() {
  if(single_post_id && (single_post_id !== '0')) {
    getSinglePost(single_post_id);
  }
});

function expandPost(postid,single) {
    var post_selector = $('#post_' + postid+' .post-feed-section').closest('.post-wrap');
    post_selector.removeClass('minimize');
    if(single == 'single'){
       $('#post_' + postid).removeClass('minimize');
    }
    post_selector.find('.m-collapse').addClass('minimize-post');
    post_selector.find('.m-collapse').removeClass('m-collapse');
    if(single === undefined){ 
      post_selector.find('.post-description .full_description').hide();
      post_selector.find('.post-description .trim_description').show();
      post_selector.find('.post-description .trim_description').removeClass('hidden');
    }
    post_selector.find('.expand_view_content').show();
    post_selector.find('.minimize-post').html('<span class="dropdown-post-icon"><img src="'+baseurl+'/images/ic_unfold_less.svg"></span>' + 'Minimise post');
}

function getSinglePost(post_id) {
  $.ajax({
    type: "GET",
    url: baseurl+'/post/'+ post_id + '?space_id='+session_space_id,
    success: function( response ) {
      single_post_data = response;
      var source = $("#post_template").html();
      var template = Handlebars.compile(source);
      var html='';
      var post_id = 0;
      $.each(response.posts, function(index, post){
        post_id = post.id;
        post_category = response.space_category;
        post['space_category'] = post_category;
        post['logged_user'] = response.user;
        post['baseurl'] = baseurl;
        post['is_logged_in_user_admin'] = is_logged_in_user_admin;
        post['single_post_view'] = single_post_view;
        post['feature_restriction'] = response.feature_restriction;
        html += template(post);
      });
      $('.single_post_content').html(html);
      $('#single_post_modal').modal();
      $('.modal-backdrop').eq(0).addClass('second-overlay');
      $('.single-post-popup .post-feed-section > iframe').remove();
      var collapsed_view = $('.single_post_content .minimize-collapse,.m-collapse');
      if(collapsed_view.length > 0) {
        expandPost(post_id,'single');
      }
      videoPlayLog();
    }
  });
}

$(document).on('hidden.bs.modal', '#single_post_modal', function () { 
    changeUrl('Client Share', baseurl+'/clientshare/'+current_space_id);
    single_post_id = null;
});

$(document).on('hidden.bs.modal', '.modal', function () {
    if(single_post_id != 0 && single_post_id){
       $('body').addClass('modal-open');
    }
});


/* Initial scripts */
getTopPost();

$(document).on('click', 'img.endorse', function () {
  feedEndorse($(this).attr('id'), 1);
});

$(document).on('click', 'img.dendorse', function () {
  feedEndorse($(this).attr('id'), 0);
});

function feedEndorse(post_id, endorse){
  $.ajax({
    type: 'POST',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {post_id:post_id, endorse:endorse},
    url: baseurl+'/endorsePost',
    success: function (response) {
      getEndorseContent(post_id, response);
    }
  });
}

if(!window.location.pathname.includes(path_name))
  window.history.pushState(null, null, '/clientshare/'+current_space_id);

if(single_post_view) {
  $(document).on('shown.bs.modal', '#single_post_modal', function () {
    $(document).find('body').addClass('single-post-body')
  });

  $(document).on('hidden.bs.modal', '#single_post_modal',  function () {
    $(document).find('body').removeClass('single-post-body')
  });

  getSinglePost(single_post_id);

  customLogger({
      'space_id':session_space_id,
      'action': 'View single post',
      'content_type': 'AppPostMedia',
      'content_id': single_post_id,
      'metadata': {'post_id':single_post_id}
    }, true);
}

function linkify(input_text) {
    var replaced_text, replace_pattern_one, replace_pattern_two, replace_pattern_three;
    var link_css_class = 'linkify-anchor';

    replace_pattern_one = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    replaced_text = input_text.replace(replace_pattern_one, '<a href="$1" class='+link_css_class+' target="_blank">$1</a>');

    replace_pattern_two = /(^|[^\/])(www\.[\S]+(\b|$))/ig;
    replaced_text = replaced_text.replace(replace_pattern_two, '$1<a class='+link_css_class+' href="http://$2" target="_blank">$2</a>');

    replace_pattern_three = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/ig;
    replaced_text = replaced_text.replace(replace_pattern_three, '<a class='+link_css_class+' href="mailto:$1">$1</a>');

    return replaced_text;
}

function htmlSubstring(string, limit) {
    var expression, validate = /<([^>\s]*)[^>]*>/g,
        stack = [],
        last = 0,
        result = '';

    while ((expression = validate.exec(string)) && limit) {
        var temp = string.substring(last, expression.index).substr(0, limit);
        result += temp;
        limit -= temp.length;
        last = validate.lastIndex;

        if (limit) {
            result += expression[0];
            if (expression[1].indexOf('/') === 0) {
                stack.pop();
            } else if (expression[1].lastIndexOf('/') !== expression[1].length - 1) {
                stack.push(expression[1]);
            }
        }
    }
    
    result += string.substr(last, limit);
    return result.trim();
}

function copyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.style.position = 'fixed';
    textArea.style.top = 0;
    textArea.style.left = 0;
    textArea.style.width = '2em';
    textArea.style.height = '2em';
    textArea.style.padding = 0;
    textArea.style.border = 'none';
    textArea.style.outline = 'none';
    textArea.style.boxShadow = 'none';
    textArea.style.background = 'transparent';
    textArea.value = text;
    
    if(single_post_view)
      $('#single_post_modal').append(textArea);
    else
      document.body.appendChild(textArea);

    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
    } catch (err) {
        alert('Could not copy the URL - please update your browser to the latest version.');
    }

    if(single_post_view)
      $('#single_post_modal').find(textArea).remove();
    else
      document.body.removeChild(textArea);
}

function iosCopyToClipboard(val) {
    el = document.getElementById('copy_post_link_ios');
    el.innerHTML = val;
    var range = document.createRange();
    range.selectNodeContents(el);
    var s = window.getSelection();
    s.removeAllRanges();
    s.addRange(range);
    el.setSelectionRange(0, 999999); // A big number, to cover anything that could be inside the element.
    document.execCommand('copy');
}

$(document).on('click', '.copy-post-link', function (e) {
    e.preventDefault();
    if (navigator.userAgent.match(/ipad|ipod|iphone/i)) {
        iosCopyToClipboard($(this).attr('data-href'));
    } else {
    copyTextToClipboard($(this).attr('data-href'));
    }
    $(this).closest('.dropdown-toggle').dropdown('toggle');
    return;
});

$('.modal').on('shown.bs.modal', function () {
  $(window).trigger('resize');
});

$(document).on('click', '.endorse-user [data-toggle="popover"]', function(){
  $(this).popover('hide');
});

$(document).on('click', '.post-header-col [data-toggle="popover"]', function(){
  $(this).popover('hide');
});

function pasteAsPlainText(elem, e) {
  e.preventDefault();
  var text = '';
  if (e.clipboardData || e.originalEvent.clipboardData) {
    text = (e.originalEvent || e).clipboardData.getData('text/plain');
  } else if (window.clipboardData) {
    text = window.clipboardData.getData('Text');
  }

  if (document.queryCommandSupported('insertText')) {
    document.execCommand('insertText', false, text);
  } else {
    document.execCommand('paste', false, text);
  }
}


function checkBlockWords(subject, body, CSRF_TOKEN){
  var any_error=false;
  $.ajax({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      type: 'POST',
      url: baseurl + '/matchwordsubject',
      data: {
          subject: subject,
          body: body,
          _token: CSRF_TOKEN
      },
      dataType: 'json',
      async: false,
      success: function (response) {
          if (response != '') {
              if (response.subject) {
                  var block_word_subject = response.subject.toString().replace(/\,/g, '", "');
                  if (block_word_subject != '') {
                    $('.post_subject:visible').addClass('word-block-error');
                    $('.post_subject:visible').parent().find('.error-msg').remove();

                    var message_html = '<span class="error-msg error-body text-left" style="text-align: left;">';
                    message_html += 'This post contains the following blocked word(s): "' + block_word_subject + '"</br>';
                    message_html += 'Please remove any blocked words before adding your post</span>';

                    $('.post_subject:visible').after(message_html);
                    any_error = 1;
                  }
              }
              if (response.body1) {
                  var block_word_body = response.body1.toString().replace(/\,/g, '", "');
                  if (block_word_body != '') {
                      $('.main_post_ta:visible').addClass('word-block-error');
                      $('.main_post_ta:visible').parent().find('.error-msg').remove();

                      var message_html = '<span class="error-msg error-body text-left" style="text-align: left;">';
                      message_html += 'This post contains the following blocked word(s): "' + block_word_body + '"</br>';
                      message_html += 'Please remove any blocked words before adding your post</span>';
                      
                      $('.main_post_ta:visible').after(message_html);
                      any_error = 1;
                  }
              }
          } else {
              if (!subject || !subject.length > 0) {
                  $('.post_subject:visible').parent().find('.error-msg').remove();
                  $('.post_subject:visible').after('<span class="error-msg error-body text-left" style="text-align: left;">Subject is mandatory</span>');
                  any_error = 1;
              }
              if (!body || !body.length > 0) {
                  $('.post-description-textarea:visible').parent().find('.error-msg').remove();
                  $('.post-description-textarea:visible').after('<span class="error-msg error-body text-left" style="text-align: left;">Body is mandatory</span>');
                  any_error = 1;
              }
          }
      },
      error: function (error) {
          ev.preventDefault();
      }
    });

  return any_error;
}

function addPost(form_data){
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    type:'POST',
    url:baseurl+'/addpost',
    data:form_data,
    dataType:'JSON',
    success: function(response){
      if(typeof response.message != 'undefined' && response.message == 'user_deleted') {
           window.location.href = baseurl+'/logout';  
      }
      post_null = false;
      refreshFeed();
      resetAddPostFormElements();
      addProgressBarsection('add_post');
    },
    error: function(xhr, status, error) {
      errorOnPage(xhr, status, error);
    }
  });
}

$(document).on('click', '#save_post_btn_new', function (ev) {
  ev.preventDefault();
  $('.add_post_form').find('.form-submit-loader').show();
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  var subject = $('.post_subject:visible').val();
  var body = $('.main_post_ta:visible').val();
  blocked_word = checkBlockWords(subject, body, CSRF_TOKEN); 
  if(blocked_word) {
    $('.add_post_form').find('.form-submit-loader').hide();
  }
  if(!blocked_word) {
    $('.add_post_form').find('.form-submit-loader').show();
    setTimeout(triggerAddPost, 500);
  }
});

function triggerAddPost(){
  addPost($('#save_post_btn_new').closest('form').serialize());
}


function resetAddPostFormElements(){
  $('#discard').trigger('click');
  $('.add_post_form').find('.form-submit-loader').hide();

  $('.alert-checkbox').parent().addClass('active').removeClass('disable_check');
  $('.select_all_alert').parent().addClass('active').removeClass('disable_check');
  $('.selection_alert').text('Everyone');

  $('.visiblity-checkbox').parent().addClass('active');
  $('.select_all_visibility').parent().addClass('active');
  $('.selection_visibility').text('Everyone');
  $('form.add_post_form textarea').height(29);
  loadEditPostTemplate();
}


function singlePostEditTemplate() {
  $.ajax({
    type: 'GET',
    dataType: 'html',
    url: baseurl+'/single_post_edit_template/'+session_space_id,
    success: function(response) {
      singlePostEditTemplateInit(response);      
    }
  });
}

function singlePostEditTemplateInit(response) {
  $('.container-prime').html(response);
}

function resetEditedSinglePost() {
  $('.single-post-edit-view').remove();
  $('.single_post_content').show();
  $('.edit_post_aws_files_data').val('');
  $('.edit_media_div').html();
}

function submitEditedPost(form) {
  post_data = form.serializeArray();
  post_id = form.find('[name="post[id]"]').val();

  $.ajax({
    type: 'POST',
    dataType: 'json',
    data: post_data,
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    url: baseurl+'/updatepost/',
    success: function(response) {
      getSinglePost(post_id);
      resetEditedSinglePost();
    }
  });
}

var edit_post_files = new Array();

function uploadEditPostFileCompleted(file_data) {
  
  html = '<div class="upload-content post_categories edit_media_div_container" id="'+file_data.uid+'">';
  html+= ' <span class="close">';
  html+= ' <img src="'+baseurl+'/images/ic_deleteBlue.svg" id="" class="edit_file_del" fileid="'+file_data.originalName+'" onclick="removeUploadedFile(this, \'edit_post_files\', \''+uid+'\')"></span>';
  html+= ' <a class="findmedia attach-link full-width" href="javascript:void(0)" onclick=""><img class="" src="'+baseurl+'/images/ic_IMAGE.svg" media-id="'+file_data.uid+'">';
  html+= ' <span class="attachment-text">'+file_data.originalName+'</span>';
  html+= ' </a></div>';

  $('.upload-preview-wrap').append(html);
  $('.' + file_data.uid).remove();
  edit_post_files = new Array();
}

function uploadEditPostFileError(file_data) {}

function removeUploadedFile(element, storage, uid) {
  if ($(element).attr('id') == "")
      $(element).attr('id', 0);
  $('#'+uid+'.edit_media_div_container').remove();

  file_object = new Array();
  $.each(window[storage], function(index, file) {
    if(file.uid != uid) {
      file_object.push(file);
    }
  });
  window[storage] = file_object;
}

function uploadEditPostFile() {
  direct_upload_s3_data.push({
    'storage': 'edit_post_files',
    'progress_element_class': 's3_progress',
    'form_field_class': 'edit_post_aws_files_data',
    'done_callback': 'uploadEditPostFileCompleted',
    'error_callback': 'uploadEditPostFileError',
    'allowed_extension': ['pdf', 'docx', 'ppt', 'pptx', 'mp4', 'doc', 'xls', 'xlsx', 'csv' , 'mov', 'MOV', 'png', 'jpeg', 'jpg'],
    'progress_bar_ele': '.upload-preview-wrap'
  });

  $('#upload_s3_file').trigger('click');
}

$(document).on('click', '.edit-single-post-data', function() {
  single_post_clone = $('.container-prime').clone();
  single_post_clone.find('.upload-preview-wrap').html('');

  var multiselect_attributes = {
    numberDisplayed: 1,
    includeSelectAllOption: true,
    enableCaseInsensitiveFiltering: true,
    buttonWidth: '100%',
    nonSelectedText: 'NOTHING SELECTED',
    disabledText: 'Disabled'
  };
  

  single_post_clone.find('select')
    .not('.single-post-edit-visiblity-select')
    .multiselect(multiselect_attributes);

  single_post_clone.show();
  single_post_clone.addClass('single-post-edit-view');
  $('.single_post_content').hide();
  var regex = /<br\s*[\/]?>/gi;
  single_post_clone.find('textarea.post-subject-textarea').val(single_post_data.posts[0].post_subject.replace(regex, "\n"));
  single_post_clone.find('textarea.post-description-textarea').val(single_post_data.posts[0].post_description.replace(regex, "\n"));
  
  single_post_clone.find('.single-post-edit-category-select').multiselect('select', [single_post_data.posts[0].meta_array.category]);
  
  single_post_clone.find('.single-post-edit-visiblity-select').multiselect({
    injectElement: function(element) {
      return '<span class="community-member-company">'+$(element).attr('data-company')+'</span>';
    },
    numberDisplayed: 1,
    includeSelectAllOption: true,
    enableCaseInsensitiveFiltering: true,
    buttonWidth: '100%',
    nonSelectedText: 'NOTHING SELECTED',
    disabledText: 'Disabled'
  });
  
  single_post_clone.find('.single-post-edit-visiblity-select').multiselect('select', single_post_data.posts[0].visibility.split(','));

  single_post_clone.find('.single-post-edit-alert-select').multiselect('selectAll', false);
  single_post_clone.find('.single-post-edit-alert-select').multiselect('disable');
  single_post_clone.find('.single-post-edit-alert-select').multiselect('updateButtonText');

  single_post_clone.find('input[name="post[id]"]').val(single_post_data.posts[0].id);
  single_post_clone.find('input[name="space[id]"]').val(single_post_data.posts[0].space_id);

  single_post_clone.find('meta[name="csrf-token"]').attr('content', $('meta[name="csrf-token"]').attr('content'));
  
  html='';
  $.each(single_post_data.posts[0].post_media, function(index, file) {
    html+= '<div class="upload-content post_categories edit_media_div_container" style="">';
    html+= '<span class="close"><img src="'+baseurl+'/images/ic_deleteBlue.svg" id="" class="edit_file_del" fileid="'+file.id+'" onclick="close_preview_edit(this)"></span>';
    html+= '<a class="findmedia attach-link full-width" href="javascript:void(0)" onclick="">';
    html+= '<img class="" src="'+baseurl+'/images/ic_IMAGE.svg" media-id="'+file.id+'" viewfile="">';
    html+= '<span class="attachment-text">'+file.metadata.originalName+'</span>';
    html+= '</a></div>';
  });


  single_post_clone.find('.edit_media_div').html(html);
  
  $('.single_post_content').after(single_post_clone);
  $('#single_post_modal .single-post-repost').attr('id','single-post-repost-feed');
  $('#single_post_modal .single-edit-post-checkbox').attr('for','single-post-repost-feed')
  autosize(document.querySelectorAll('textarea.t1-resize'));
  autosize(document.querySelectorAll('textarea.t2-resize'));
});

$(document).on('click', '.single-post-repost', function() {
  if($(this).prop('checked')) {
    $(this).closest('.single-post-edit-view').find('.single-post-edit-alert-select').multiselect('enable');
  } else {
    $(this).closest('.single-post-edit-view').find('.single-post-edit-alert-select').multiselect('disable');    
  }
  $(this).closest('.single-post-edit-view').find('.single-post-edit-alert-select').multiselect('selectAll', false);
  $(this).closest('.single-post-edit-view').find('.single-post-edit-alert-select').multiselect('updateButtonText');
});

$(document).on('click', '.submit-edited-post', function() {
  form = $(this).closest('form');
  form.find('.form-submit-loader').show();
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
  var subject = form.find('textarea.post-subject-textarea').val().trim();
  var body = form.find('textarea.post-description-textarea').val().trim();
  blocked_word = checkBlockWords(subject, body, CSRF_TOKEN);
  
  if(!blocked_word) {
    $(this).closest('form').submit();
  } else {
    form.find('.form-submit-loader').hide(); 
  }
});

$(document).ready(function(){
  singlePostEditTemplate();
});
var edit_comment_triggered = false;

function update_comment(element, comment_id) {
    var edit_comment_element = $(element).closest('.comment-edit-section').find('.edit-comment-area');
    var new_value = edit_comment_element.html();
    var uploading_file = $(element).closest('.post-block').find('.s3_running_process').length;
    if(!(edit_comment_element.text().trim().length) || !placeholderCheck(edit_comment_element) || uploading_file){
        edit_comment_element.focus();
        return false;
    }
    $.ajax({
        type: 'POST',
        async:false,
        dataType: 'JSON',
        url: baseurl + '/comments/'+comment_id,
        data: {
            _method: 'PATCH',
            _token: $('meta[name="csrf-token"]').attr('content'),
            'comment': {'comment_text':new_value, 'comment_attachment':window['attachment_'+comment_id]}
        },
        success: function (response) {
            var post_id = $(element).closest('.post-block').find('input[name="post_id"]').attr('id');
            refreshPost(post_id, {'comment_limit':0});  
            edit_comment_triggered = false;
        },
        error: function (xhr, status, error) {
            errorOnPage(xhr, status, error);    
        },
        complete: function(){
            window['attachment_'+comment_id] = new Array();
        }
    });
}

function getComment(comment_id){
    var comment = 'Please wait..';
    $.ajax({
        type: 'GET',
        dataType: 'json',
        async : false,
        url: baseurl + '/comments/'+comment_id,
        success: function (response) {
            comment = response;
        },
        error: function (xhr, status, error) {
            errorOnPage(xhr, status, error);
        }
    });
    return comment;
}

function delete_comment_confirm(element) {
    var postid = $(element).attr('id');
    var commentid = $(element).attr('commentid');
    var userid = '';
    var spaceid = $(element).attr('spaceid');
    var commentlimit = '';
    var comment = '';
    var morecheck = $('.checkclickedviewmore' + postid).val();
    var view_more = 'false';
    $.ajax({
        type: "GET",
        url: baseurl + '/add_comments?postid=' + postid + '&userid=' + userid + '&comment=' + comment + '&commentlimit=' + commentlimit + '&morecheck=' + morecheck + '&view_more=' + view_more + '&spaceid=' + spaceid + '&commentid=' + commentid + '&action=delete',
        success: function (response) {
            $('.comments' + postid).html(response);
            $('#comment_input_area' + postid).val('');
            $("#delete_modal_comment").modal('hide');
            refreshPost(postid, {'comment_limit':0});
        },
        error: function (xhr, status, error) {
            logErrorOnPage(xhr, status, error, 'delete_comment_confirm');
        }
    });
}

function delete_comment(post_id, comment_id, space_id) {
    $('.del_comment').attr('id', post_id);
    $('.del_comment').attr('commentid', comment_id);
    $('.del_comment').attr('spaceid', space_id);
    $("#delete_modal_comment").modal('show');
}

function commentPreview(file_data, element){
    if(!file_data.attachments.length) $('.feed-post-attachment-box.'+file_data.id).parent().hide();
    var source = $("#comment_attachment_preview").html();
    var template = Handlebars.compile(source);
    window['attachment_'+file_data.id] = new Array();
    $.each(file_data.attachments, function(key){
        window['attachment_'+file_data.id].push(this.metadata);
    });

    var html = template({'comment_files':window['attachment_'+file_data.id], 'uid': file_data.id});
    $('.feed-post-attachment-box.'+file_data.id).html(html);
}

function edit_comment(comment_id, element) {
    var post_id = $(element).closest('.post-block').find('input[name="post_id"]').attr('id');
    comment_data = getComment(comment_id);
    comment = comment_data.comment;

    div = '<div class="comment-edit-section"><div contenteditable="true" class="form-control no-border edit-comment-area comment-area" id="'+comment_id+'" data-placeholder="Write a comment..." areaid="'+comment_id+'" style="border: 2px solid red; min-height:30px;width:200px">'+comment+'</div><div class="comment-attach-col"><input type="submit" value="File Attachment" class="comment_attachment comment_edit_attachment_trigger" data-commentid="'+comment_id+'" data-postid="'+post_id+'" style="float:right;"></div><button type="button" class="invite-btn right save_comment" onclick="return update_comment(this,\'' + comment_id + '\')">Save</button><div class="attachment-box-row full-width"><div class="feed-post-attachment-box '+comment_id+'"></div></div><div class="comment_edit_attachment_progress full-width '+comment_id+'"></div></div>';

    comment_wrap = $(element).closest('.user-comment-post');
    comment_wrap.find('.user-comment-detail').hide();
    comment_wrap.append(div);

    if($(element).attr('data-comment-restriction'))
        $('.comment_attachment').remove();

    mentionsComment(comment_wrap.find('.edit-comment-area'), post_id);
    commentPreview(comment_data, element);
    comment_wrap.find('.edit-comment-area').focusEnd();
    edit_comment_triggered = true;
}

function addComment(comment_data, element){
    $.ajax({
        type: 'POST',
        dataType: 'html',
        data: {
            'post_id': comment_data['postid'] ,
            'user_id': comment_data['userid'] ,
            'space_id': comment_data['spaceid'],
            'comment': comment_data['comment'],
            'commentlimit': comment_data['commentlimit'] ,
            'morecheck': comment_data['morecheck'] ,
            'view_more': 0,
            'attachments': comment_data['attachments'],
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            $(element).after('<i class="fa fa-circle-o-notch fa-spin"></i>');
            $(element).attr('disabled', true);
            $(element).val('');
        },
        url: baseurl + '/comments',
        success: function (response) {
            comment_post_id = null;
        },
        error: function (xhr, status, error) {
            logErrorOnPage(xhr, status, error, 'addComment');
        },
        complete: function(){
            $(element).parent().find('.fa-circle-o-notch').remove();
            $(element).attr('disabled', false);
            $(element).val('SEND');
        }
    });
}

function showComments(comment_data, comments){
    $('#post_'+comment_data['post_id']).find('.comment-wrap').html(comments)
    $('#comment_input_area' + comment_data['post_id']).val('');
    $("#delete_modal_comment").modal('hide');
    comment_post_id = null;
}


$(document).on("mouseup", function(e){
    var container = $('.comment-edit-section');
    var modal_container = $('.swal2-modal');
    if(container.has(e.target).length === 0 && edit_comment_triggered){
        if(modal_container.has(e.target).length) return true;
        swal({
            html: $('#discardModalcomment .modal-content').html(),
            customClass: 'simple-alert discard-comment',
            showConfirmButton: false,
            animation: false
        });
        $('.discard_comment').attr('id', $('div.edit-comment-area').attr('id'));
        return false;
    }
});

$(document).on('click', '.comment-show-less', function(){
    if($(this).hasClass('show-more')){
        $(this).parent().find('.post-comment').css('max-height', '100%');
        $(this).html('Show Less');
        $(this).toggleClass('show-more', 'show-less');
    } else {
        $(this).parent().find('.post-comment').css('max-height', '50px');
        $(this).html('Show More');
        $(this).toggleClass('show-more', 'show-less');
    }
});

function placeholderCheck(element){
    if(navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/trident/i) ) {
        var placeholder_content = 'Add a comment or tag someone using @...';
        return placeholder_content == $(element).text() ? false : true;
    }
    return true;
}

$(document).on('click', '.send_comment', function (e) {
    var tag = $(this);
    var post_id = $(this).attr('datapostid');
    var user_id = $(this).attr('datauserid');
    var comment_limit = $(this).attr('commentlimit');
    var space_id = $(this).attr('spaceid');
    var comment = $(this).parent().find('.comment-area').html();
    var more_check = 'false';
    var view_more = 'false';
    var comment_element = $(this).parent().find('.comment-area');
    var uploading_file = $(this).closest('.post-block').find('.s3_running_process').length;

    if(!(comment_element.text().trim().length) || !(placeholderCheck(comment_element)) || uploading_file){
        comment_element.focus();
        return false;
    }

    addComment({
        'postid' : post_id,
        'userid' : user_id,
        'comment' : comment,
        'commentlimit' : comment_limit,
        'morecheck' : more_check,
        'view_more' : view_more,
        'spaceid' : space_id,
        'attachments': window['attachment_'+post_id]

    }, this);
    getPost(post_id,{'comment_limit':2}, 'comment');
    window['attachment_'+post_id] = new Array();
});

$(document).on('click','a.view-more-comments', function(){
    $(this).hide();
    post_id = $(this).closest('.post-block').find('input[name="post_id"]').attr('id');
    $('.'+post_id+'.post-block').find('.user-comment-post.hidden').removeClass('hidden').addClass('user-comment-post-show');
    $('.'+post_id+'.post-block').find('.view-less-comments.hidden').removeClass('hidden');
});

$(document).on('click','a.view-less-comments', function(){
    $(this).addClass('hidden');
    post_id = $(this).closest('.post-block').find('input[name="post_id"]').attr('id');
    $('.'+post_id+'.post-block').find('.user-comment-post-show').addClass('hidden').removeClass('user-comment-post-show');
    $('.'+post_id+'.post-block').find('.view-more-comments').show();
});

function discardComment(){
    $('.user-comment-detail').show();
    $('.comment-edit-section').hide();
    $('#discardModalcomment').modal('hide');
    swal.close();
    edit_comment_triggered = false;
    $('.edit-comment-area').trigger('mouseenter').focusEnd();
}

$(document).on('click', '.discard-comment button', function(){
    swal.close();
    $('.edit-comment-area').trigger('mouseenter').focusEnd();
});

if(navigator.userAgent.match(/msie/i) || navigator.userAgent.match(/trident/i) ) {
    var placeholder_content = 'Add a comment or tag someone using @...';
    $(document).on('focus', '.comment-area', function(){
        if(placeholder_content == $(this).text()) $(this).html('').css('color', 'black');
        $(this).attr('data-placeholder', '');
    });
    $(document).on('blur', '.comment-area', function(){
        if(!$(this).text().trim().length) $(this).html(placeholder_content).css('color', '#b6b6b6');
    });
}

function commentEditAttachment(element){
    direct_upload_s3_data = new Array();
    direct_upload_s3_data.push({
        'storage': 'attachment_'+$(element).attr("data-commentid"),
        'progress_element_class': 's3_progress',
        'form_field_class': 'executive_aws_files_data',
        'done_callback': 'commentEditAttachmentUploaded',
        'error_callback': 'upload_executive_file_error',
        'allowed_extension': constants['POST_EXTENSION'],
        'progress_bar_ele': $(element).closest('.post-block').find('.comment_edit_attachment_progress.'+$(element).attr('data-commentid')),
        'element': element
    });
    $('#upload_s3_file').trigger('click');
}

function commentAttachment(element){
    direct_upload_s3_data = new Array();
    if(!window['attachment_'+$(element).attr("data-postid")]) 
            window['attachment_'+$(element).attr("data-postid")] = new Array();
    direct_upload_s3_data.push({
        'storage': 'attachment_'+$(element).attr("data-postid"),
        'progress_element_class': 's3_progress',
        'form_field_class': 'executive_aws_files_data',
        'done_callback': 'commentAttachmentUploaded',
        'error_callback': 'upload_executive_file_error',
        'allowed_extension': constants['POST_EXTENSION'],
        'progress_bar_ele': $(element).closest('.post-block').find('.comment_attachment_progress.'+$(element).attr('data-postid')),
        'element': element
    });
    $('#upload_s3_file').trigger('click');
}

function commentEditAttachmentUploaded(file_data){
    var send_trigger = direct_upload_s3_data[0]['element'];
    post_id = $(send_trigger).attr('data-postid');
    comment_id = $(send_trigger).attr('data-commentid');
    var source = $("#comment_attachment_preview").html();
    var template = Handlebars.compile(source);
    var html = template({'comment_files':window['attachment_'+comment_id], 'uid': comment_id});

    $(send_trigger).closest('.post-block').find('.comment-edit-section .feed-post-attachment-box.'+comment_id).html(html).parent().show();
    $(send_trigger).closest('.post-block').find('.s3_running_process.'+file_data['uid']).remove();
    $(send_trigger).focus();
}

function commentAttachmentUploaded(file_data){
    var send_trigger = direct_upload_s3_data[0]['element'];
    var post_id = $(send_trigger).attr('data-postid');
    var source = $("#comment_attachment_preview").html();
    var template = Handlebars.compile(source);
    var html = template({'comment_files':window['attachment_'+post_id], 'uid': post_id});

    $(send_trigger).closest('.post-block').find('.feed-post-attachment-box').html(html).parent().show();
    $(send_trigger).closest('.post-block').find('.s3_running_process.'+file_data['uid']).remove();
}

function removeCommentAttachment(element){    
    var uid = $(element).attr('data-uid');
    
    var temp_arr = new Array();
    var attachment = $(element).closest('.comment-attachment');
    var attachment_container = attachment.closest('.attachment-box-row');
    var attachment_id = attachment.attr('id');

    $.each(window['attachment_'+uid], function (key) {
        if (this.uid != attachment_id)
            temp_arr[key] = window['attachment_'+uid][key];
    });
    delete window['attachment_'+uid];
    window['attachment_'+uid] = temp_arr;
    attachment.remove();
    if(!attachment_container.find('.comment-attachment').length){
        window['attachment_'+uid] = new Array();
        attachment_container.hide();
    } 
    return false;
}

$(document).ready(function(){

    $(document).on('click', '.comment-attach-delete', function(){
        removeCommentAttachment(this);
    });

    $(document).on('click', '.comment_attachment_trigger', function(){
        commentAttachment(this);
    });

    $(document).on('click', '.comment_edit_attachment_trigger', function(){
        commentEditAttachment(this);
    });

    $(document).on('keyup', '.comment-area', function(){
        if(!$(this).text().length) {
            $(this).html('');
            $(this).mentionsInput('destroy');
            mentionsComment(this, $(this).attr('data-postId'));
        }
    });
});
var get_url_xhr = null;
var url_validate_var;
var edit_post_category_previous_state = "";
var uploaded_file_aws = new Array();
var filesUploaded = [];
var cached_url = '';
var s3_executive = new Array();
var is_mouse_inside_tour = true;
var executive_summary_max_length = 300;
var show_feedback = false;
var space_users = new Array();


/* initials scripts */
if($('.edit-post-dropdown').find('li').length <= 0) $('.edit-post-dropdown').parent().hide();

function getSpaceUsers(){
    $.ajax({
        type: 'GET',
        dataType: 'JSON',
        url: baseurl+'/get_space_users/'+session_space_id,
        success: function( response ) {
            if(response.result){
              space_users = response.space_users;
              setPostUsers();
            }
        }
    });
}

getSpaceUsers();

function setPostUsers(){
    var post_header = $('.post-block .post-feed-section .post-header-col');
    $(post_header).each(function(){
        var tooltip = $(this).find('.visible_tooltip');
        var tooltip_content = $(tooltip).attr('data-content').trim();
        var post_users = $(this).siblings('input.post_visible_user').val();
        if( tooltip_content == 'Restricted' || $(tooltip).find('span.earth').hasClass('lock')){
            var post_users_array = post_users.split(',');
            $(tooltip).attr('data-content', getUsersListHtml(post_users_array));
            if(post_users.indexOf("All") == -1)
            $(tooltip).find('a.s-everyone').attr('visibletousers', post_users + ',All');
        }
        else{
            $(tooltip).find('a.s-everyone').attr('visibletousers', post_users);
        }
    });
}

function getUsersListHtml(users_array){
    $html = '';
    var user_count = users_array.length;
    $.each(users_array, function(user_index, user_id){
        if(user_index < 5){
            $html += space_users[user_id] + '<br>';
        }
        else
        {
            if(parseInt(user_index) === 5 && parseInt(user_count) > 5){
              $html += 'and '+ (user_count - 5) +' others'; 
            }
            return false;
        }
    });
    return $html;
}

function postFormListReset(visibility_list, alert_list ){
    $(visibility_list).find('input:checkbox').prop('checked', false);
    $(visibility_list).find('input:checkbox').parent().removeClass('active');

    $(alert_list).find('input:checkbox').prop('checked', false);
    $(alert_list).find('input:checkbox').parent().removeClass('active');
    $(alert_list).find('input:checkbox').parent().addClass('disable_check');
}

function postVisiblityUser(element, selector) {
    $(element).closest('.dropdown-wrap').addClass('open');
    $(element).parent().toggleClass('active', $(element).prop('checked'));

    if(!$(element).prop('checked') && $('input:checkbox:checked.visibility_group').length ) getGroupByUser($(element).val());

    label = postDropDownLabel(selector.find('input:checkbox.visiblity-checkbox' ).length, selector.find('input:checkbox:checked.visiblity-checkbox').length);
    selector.find('.post-visiblity-label').text(label.text);
    selector.find('.post-visiblity-checkbox').prop('checked', label.master_box_check).parent().toggleClass('active', label.master_box_check);
    post_alert_selector = selector.find('.alert-drop').find('input:checkbox[value="'+$(element).val()+'"]');
    post_alert_selector.parent().toggleClass('disable_check',!$(element).prop('checked'));

    selector.find('.post-alert-checkbox').parent()
        .toggleClass('disable_check', !selector.find('input:checkbox:checked.visiblity-checkbox').length);

    postAlertUser(post_alert_selector, $(element).prop('checked'), selector);
}

function postAlertUser(element, is_selected, selector) {
    element.prop('checked', is_selected);
    $(element).parent().toggleClass('active', is_selected);

    label = postDropDownLabel(selector.find('.alert-drop').find('input:checkbox.alert-checkbox').length, selector.find('.alert-drop').find('input:checkbox:checked.alert-checkbox').length);
    selector.find('.post-alert-label').text(label.text);

    label = postDropDownLabel(selector.find('.alert-drop').find('input:checkbox.alert-checkbox').parent().not('.disable_check').length, selector.find('.alert-drop').find('input:checkbox:checked.alert-checkbox').length);
    selector.find('.post-alert-checkbox').prop('checked', label.master_box_check).parent().toggleClass('active', label.master_box_check);
}

/* Video player click bink for log */
function video_player_log_bind() {
    $('video').bind('play', function (e) {
        parent_div = $(this).parent().closest('.post-wrap');
        post_id = parent_div.attr('id');
        if(!post_id) return;
        post_id = post_id.replace('post_', '');
        media_src = parent_div.find('input[name="url_src"]').val();
        content_id = parent_div.find('.for_download').attr('href').split('/').pop();
        data = {
            'description': 'View Attachment',
            'action': 'view',
            'content_id': content_id,
            'content_type': 'App\PostMedia',
            'metadata': {'media_src': media_src, 'post_id': post_id}
        };
        custom_logger(data);

        $.ajax({
            async: false,
            type: "GET",
            url: baseurl + '/view_file?post_id=' + post_id,
        });
    });
}

function get_preview_url() {
    autosize(document.querySelectorAll('textarea.t2-resize'));
    var thumbcheck = $('#thumbcheck').val();

    if ($(this).val()) {

        data = $(this).val().replace(/\n/g, " ");
        data = data.replace('#', " ");
        url = filter_url(data);
        data = encodeURIComponent(data);

        if (url === false) {
            console.log('url false');
            return true;
        }
        if (cached_url != '' && cached_url == url) {
            console.log('cached url');
            return true;
        }

        cached_url = url;
        get_url_xhr = $.ajax({
            type: "GET",
            url: baseurl + '/get_url_data?q=' + data,
            processData: false,
            contentType: false,
            beforeSend: function () {
                if (get_url_xhr != null) {
                    get_url_xhr.abort();
                    get_url_xhr = null;
                }
            },
            success: function (data) {

                if (data != 0) {

                    if ($('#ytd_iframe').length && typeof (data.metatags) != 'undefined' && ($('#ytd_iframe').attr('src') == data.metatags["twitter:player"])) {
                        return 0;
                    }

                    html = '<img class="url_embed_trigger" src="'+baseurl+'/images/ic_highlight_removegray.svg"/>';
                    html = html + '<div class="outer-block">';
                    html = html + '<div class="inner-block">';

                    if (data.metatags["twitter:player"]) {
                        html = html + '<div class="thumbnail-block iframe-content ">';
                        html = html + '<iframe id="ytd_iframe" allowfullscreen="allowfullscreen" width="420" height="345" src=' + data.metatags["twitter:player"] + '></iframe>';
                    } else {
                        html = html + '<div class="thumbnail-block">';
                        img_class = data.thumbnail_img ? "thumbnail-img" : "";
                        html = html + '<img src="'+baseurl+'/file_loading?url=' + data.favicon + '" class="url-favicon ' + img_class + '" onerror="this.src=\'http://' + data.domain + '/favicon.ico\';">';
                    }

                    html = html + '</div>';

                    html = html + '<div class="description-block"><div>';
                    //html = html+'<h5><img src="'+data.favicon+'"/>'+data.domain+'</h5>';
                    var title = data.title ? data.title : data.title[0];
                    if (data.full_url.search('http') < 0) {
                        html = html + '<a target="_blank" href=http://' + data.full_url + ' title=' + title + '> <img src="https://www.google.com/s2/favicons?domain=' + data.url + '"/>' + data.title + '</a>';
                    } else {
                        html = html + '<a target="_blank" href=' + data.full_url + ' title=' + data.title + '> <img src="https://www.google.com/s2/favicons?domain=' + data.url + '"/>' + data.title + '</a>';
                    }
                    if (data.description) {
                        html = html + '<p>' + data.description + '</p>';
                    }
                    /* Descroption block close*/
                    html = html + '</div></div>';

                    /* Outer - Innner block close */
                    html = html + '</div></div>';

                    $('.url_embed_div').empty();
                    $('.url_embed_div').append(html);
                    $('input[name=url_embed_toggle]').val(1);
                    $('input[name=url_preview_data_json]').val(JSON.stringify(data));
                    $('#thumbcheck').val(1);
                } else {
                    $('.url_embed_div').empty();
                }
            },
            error: function (xhr, status, error) {

            }
        });
    } else {
        $('#thumbcheck').val(0);
    }
}


function filter_url(data) {
    regex = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/igm;
    response = data.match(regex);
    if (response && response.length > 0) {
        return response[0];
    }
    return false;
}

function url_embed_enable(ele) {
    if ($(ele).hasClass('fa-toggle-on')) {
        $(ele).removeClass('fa-toggle-on');
        $(ele).addClass('fa-toggle-off');
        $('input[name=url_embed_toggle]').val(0);
        $('.url_embed_div').find('.outer-block').hide();
    } else {
        $(ele).addClass('fa-toggle-on');
        $(ele).removeClass('fa-toggle-off');
        $('input[name=url_embed_toggle]').val(1);
        $('.url_embed_div').find('.outer-block').show();

    }
}

function onYouTubeIframeAPIReady() {
    try {
        $('iframe.youtube_iframe').each(function () {
            video_id = extractUrlParam($(this).attr('data-video-src'), 'v');
            new YT.Player($(this).attr('id'), {
                videoId: video_id,
                events: {
                    'onStateChange': onPlayerStateChange
                }
            });
        });
    } catch(error) {
        logErrorOnPage('', '500', error, 'On YouTube Iframe API Ready');
    }

}

function onPlayerStateChange(event) {

    if(event.data == 1) {
        media_src = event.target.a.src;
        content_id = event.target.a.id;
        
        customLogger({
            'space_id':session_space_id,
            'description': 'viewed youtube video ' + media_src,
            'action': 'view embedded url',
            'content_id': content_id,
            'content_type': 'App\\Post',
            'metadata': {'media_src': media_src, 'post_id': content_id}
        }, true);

        mixpanelLogger({
            'space_id': session_space_id,
            'event_tag':'View embedded video'
        }, true);
    }
}

function autoCapitalize(element){
    var start = element.selectionStart, end = element.selectionEnd;
    element.value = sentenceCase(element.value);
    element.setSelectionRange(start, end);
}

function sentenceCase(strval) {
    var re = /(^|[.!?]\s+)([a-z])/g;
    var fstring = strval.replace(re, function (m, $1, $2) {
        return $1 + $2.toUpperCase()
    });
    return fstring;
}

function readURL(input, file_ext, id) {
    $('.upload_file_name').html(file_ext);
    var extension = file_ext.substr((file_ext.lastIndexOf('.') + 1));
    extension = extension.toLowerCase();

    if (extension == 'png' || extension == 'gif' || extension == 'jpg' || extension == 'jpeg') {

        if (input.files && input.files[0]) {
            var reader = new FileReader();
            $('.post_categories_maincontent' + id).show();
            $('.post_categories_file' + id).show();
            $('.post_categories' + id).show();
            $('#post_button' + id).show();

            reader.onload = function (e) {
                $('#blah' + id).show();
                $('#blah' + id).attr('src', e.target.result)
                    .height('auto');
            }

            reader.readAsDataURL(input.files[0]);
            $('.upload_file_name').parent().find('img').attr('src', '../images/ic_IMAGE.svg');
            $('.upload_file_name').html(file_ext);
        }
    }
    else if (extension == 'pdf') {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('.upload_file_name').parent().find('img').attr('src', '../images/ic_PDF.svg');
        $('.upload_file_name').html(file_ext);
    }
    else if (extension == 'mp4' || extension.toLowerCase() == 'mov' || extension == 'mp3' || extension == 'avi' || extension == 'mkv') {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('.upload_file_name').parent().find('img').attr('src', '../images/ic_VIDEO.svg');
        $('.upload_file_name').html(file_ext);
    }
    else if (extension == 'doc' || extension == 'docs' || extension == 'docx') {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('.upload_file_name').parent().find('img').attr('src', '../images/ic_WORD.svg');
        $('.upload_file_name').html(file_ext);
    }
    else if (extension == "xlsx") {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('.upload_file_name').parent().find('img').attr('src', '../images/ic_EXCEL.svg');
        $('.upload_file_name').html(file_ext);
    }
    else if (extension == "xls") {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('.upload_file_name').parent().find('img').attr('src', '../images/ic_EXCEL.svg');
        $('.upload_file_name').html(file_ext);
    }
    else if (extension == "csv") {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('.upload_file_name').parent().find('img').attr('src', '../images/ic_EXCEL.svg');
        $('.upload_file_name').html(file_ext);
    }
    else if (extension == 'ppt' || extension == 'pptx') {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('.upload_file_name').parent().find('img').attr('src', '../images/ic_PWERPOINT.svg');
        $('.upload_file_name').html(file_ext);
    }
}

function titleCase(str) {
    var splitStr = str.toLowerCase().split(' ');
    for (var i = 0; i < splitStr.length; i++) {
        splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
    }
    // Directly return the joined string
    return splitStr.join(' ');
}

/*--------- preview_before_upload_aws start ---------- */
function preview_before_upload_aws() {
    if(!$('input[name="uploaded_file_aws"]').val())
        return;
    data = JSON.parse($('input[name="uploaded_file_aws"]').val());
    if ($('.upload-content.post_attachment').length == 1 && data.length == 1 && $('.edit_media_div #view_file').length == 0) {
        if (data[0].mimeType.indexOf('image') > -1) {
            $.ajax({
                type: "GET",
                async: false,
                url: baseurl + '/url_validate?q=' + data[0].url,
                success: function (response) {
                    $('.upload-content.post_attachment').find('#blah').attr('src', response.cloud);
                    $('.upload-content.post_attachment').find('#blah').show();
                }
            });
        }
    } else {
        /* remove single image in post preview if there is more than 1 file */
        $('img#blah').hide();
        $('.edit_media_div .img-responsive').hide();
    }
}
/*---------- preview_before_upload_aws end --------------- */

/* */
function preview_before_upload(ele) {
    $('.post_categories_file_temp').remove();
    $('.post_file').each(function (index) {
        var file_type = "";

        var video_ext = ['mp4', 'mov', 'MOV'];
        var image_ext = ['jpeg', 'png', 'jpg'];
    });
}

function close_preview_general_s3(ele) {
    s3_upload_xhr[$(ele).attr('id')].abort();
    $('.save_executive_btn').prop('disabled', false);
    $('.onboarding_save_executive_btn').prop('disabled', false);
    $(ele).remove();
}

function close_preview(ele) {
    
    if($('input[name="uploaded_file_aws"]').val()){
        data = JSON.parse($('input[name="uploaded_file_aws"]').val());
        for (i = 0; i < data.length; i++) {
            filename = data[i].s3_name.split('/');
            if (filename[1].split('.')[0] + "_uid" == $(ele).attr('id')) {
                data.splice(i, 1);
            }
        }
        $('input[name="uploaded_file_aws"]').val(JSON.stringify(data, null, 2));
        filesUploaded=data;
    }
    
    if(s3_upload_xhr[$(ele).attr('id')]){
        s3_upload_xhr[$(ele).attr('id')].abort();
    }

    if ($(ele).attr('id') == "")
        $(ele).attr('id', 0);
    $('#post_file_' + $(ele).attr('id')).remove();
    if ($(ele).parent().parent().hasClass('post_categories_file'))
        $(ele).parent().parent().remove();
    else
        $(ele).parent().parent().remove();
    if ($('.post_file').length == 1) {
        $('.remove-all').hide();
    }
    preview_before_upload_aws();
    if ($('.direct-upload').fileupload('progress').loaded == $('.direct-upload').fileupload('progress').total) {
        $('.post_btn').attr('disabled', false);
    }
    $('.popover').remove();
}

function close_preview_edit(ele) {
    if ($(ele).attr('id') == "")
        $(ele).attr('id', 0);
    $('#post_file_d_' + $(ele).attr('id')).remove();
    if ($(ele).parent().parent().hasClass('post_categories_file_edt'))
        $(ele).parent().parent().hide();
    else
        $(ele).parent().parent().remove();
    if ($(".edit_remove_all img:visible").length == 1) {
        $('.remove-all').hide();
    }
    var edit_deleted_files = $('.edit_deleted_files').val();
    var del_files_id = $(ele).attr('fileid');

    if (jQuery.type(del_files_id) === 'undefined') {
    } else {
        if (del_files_id != 0) {
            if (edit_deleted_files == '') {
                $('.edit_deleted_files').val(del_files_id);
            } else {
                $('.edit_deleted_files').val(edit_deleted_files + ',' + del_files_id);
            }
        }
    }
}

/*----------- Add input box------------ */

function readFileName(input, file_ext) {
    var extension1 = file_ext.substr((file_ext.lastIndexOf('.') + 1));
    if (extension1 == 'pdf') {
        $('#upload_video_name').html(file_ext);
    } else {
        $('#upload_video_name').html(file_ext);
    }
}


function readFileName2(input, file_ext) {
    var extension1 = file_ext.substr((file_ext.lastIndexOf('.') + 1));
    if (extension1 == 'pdf') {
        $('#upload_pdf_name').html(file_ext);
    } else {
        $('#upload_pdf_name').html(file_ext);
    }
}

function iframe_load(iframe, url) {
    $(iframe).on('load', function () {
        $('.modal-loader').hide();
    });

    if (url) {
        url_validate_var = setTimeout(function () {
            $.ajax({
                type: "GET",
                url: baseurl + '/url_validate?viewer_st=false&q=' + url,
            });
        }, 40000);
    }
}

function validate(file) {
    var ext = file.split(".");
    ext = ext[ext.length - 1].toLowerCase();
    var arrayExtensions = ["pdf", "mp4", "ppt", "pptx", "docx", "doc", "xls", "xlsx", "csv", "flv", "3gp", "mkv"];
    if (arrayExtensions.lastIndexOf(ext) == -1) {
        alert("Wrong extension type. Please upload pdf, docx, ppt, pptx, mp4, mov, MOV, doc, xls, xlsx, csv Files");

        var check = $("#upload_pdf_file").val();
        var ext1 = check.split(".");
        ext1 = ext1[ext1.length - 1].toLowerCase();
        if (arrayExtensions.lastIndexOf(ext1) == -1) {
            $("#upload_pdf_file").val("");
        }
        var check1 = $("#upload_video_file").val();
        var ext2 = check1.split(".");
        ext2 = ext2[ext2.length - 1].toLowerCase();
        if (arrayExtensions.lastIndexOf(ext2) == -1) {
            $("#upload_video_file").val("");
        }
    }
}

function dimlight() {
    if ($('#tour2').hasClass('highlight')) {
        var postvalue = $('.main_post_ta').val();
        var postsubject = $('.post_subject').val();
        var filevalue = $('.post_file').length;

        if (postvalue != '' || postsubject != '' || filevalue > 1) {
            $('#discardModal').modal('show');
        } else {
            resetVisibiltyAlers($('.post_subject'));
            $('#discard').trigger('click');
            $(".dp-input").find('.error-msg').remove();
            $(".post_subject").attr("placeholder", "Click here to add text, files, links etc.");
        }
    }
}


function edit_category(event, ele) {
    var spaceid = $(ele).attr('spaceid');

    $.ajax({
        type: "GET",
        async: false,
        url: baseurl + '/editcategory_ajax?spaceid=' + spaceid,
        success: function (response) {
            edit_post_category_previous_state = $(".categories").html();
            $(".modal_category .categories").html(response);
        }, error: function (message) {
            console.log(message);
        }
    });
}

function save_edit_cat(event, ele) {
    var action = $(ele).attr('action');
    var spaceid = $(ele).attr('spaceid');
    if (action != 'cancel') {
        var flag = true;
        if ($('.cat_2').val() != '') {
            var cat_2 = $('.cat_2').val();
        } else {
            flag = false;
        }

        if ($('.cat_3').val() != '') {
            var cat_3 = $('.cat_3').val();
        } else {
            flag = false;
        }
        if ($('.cat_4').val() != '') {
            var cat_4 = $('.cat_4').val();
        } else {
            flag = false;
        }
        if ($('.cat_5').val() != '') {
            var cat_5 = $('.cat_5').val();
        } else {
            flag = false;
        }
        if ($('.cat_6').val() != '') {
            var cat_6 = $('.cat_6').val();
        } else {
            flag = false;
        }
        if (flag == true) {
            document.forms["edit_post_form"].submit();
        } else {
            $('.cat_error').show();
            $('.cat_error').html('Category should not be blank.');
        }
    } else {
        $.ajax({
            type: "GET",
            url: baseurl + '/cancel_editcategory_ajax?spaceid=' + spaceid + '&cat_2=' + cat_2 + '&cat_3=' + cat_3 + '&cat_4=' + cat_4 + '&cat_5=' + cat_5 + '&cat_6=' + cat_6 + '&action=' + action,
            success: function (response) {
                //location.reload();
                $(".categories").html('');
                $(".categories").html(response);
                $('.edit-cat').show();
            }, error: function (message) { }
        });
    }
}

function countCharEditCat(val, cls) {
    $('.letter-count').hide();
    $('.' + cls).show();
    var len = val.value.length;
    if (len <= 25) {
        $('.' + cls).text(len + '/25');
    }
}

function deleteCategory(){
    $('.btn-quick-links').attr('disabled',true);
    var deleted_category_id = $('input[name="delete_category"]').val();
    var more_category_count = $('.more-categories a span').text()-parseInt(1);
    var category_form = $('#edit_post_category_form');
    var form_data = new FormData(category_form[0]);
    $(category_form).find('input[name="delete_category"]').val('');

    $.ajax({
              type: 'POST',
              url: baseurl+'/delete_category',
              data: form_data,
              async: true,
              success: function (response) {
                $('.btn-quick-links').attr('disabled',false);
                 if(response.result){ 
                    $('a[key="'+deleted_category_id+'"]').parent().remove();
                    $('.more-categories a span').text(more_category_count);
                    refreshFeed();
                    addProgressBarsection();
                 }else{
                    $('.category_error').text('Error! can\'t delete category, please try again.');
                 }
              },
              error: function(xhr, status, error) {
                $('.btn-quick-links').attr('disabled',false);
                logErrorOnPage(xhr, status, error, 'deleteCategory');
              },
              cache: false,
              contentType: false,
              processData: false
            });
    return false;
}

function cancel_edit_post() {
    var postid = $('.editing_post_id').val();
    $('#post_edit_' + postid).html('');
    $('#post_' + postid).show();
    $(".black-overlay").css('display', 'none');

    return false;
}

function get_edited_post_data(postid) {
    var parser = new DOMParser;
    var profile_image = $('#post_' + postid).find('span').eq(0).attr('style');
    var usrename = $('#post_' + postid).find('a').eq(0).html();
    var category = $('#post_' + postid).find('.category-chip-wrap input').eq(0).val();
    var post_subject = $('#post_' + postid).find('h4').eq(0).html();
    var subject = parser.parseFromString(
                  '<!doctype html><body>' + post_subject,
                  'text/html');
    subject = subject.body.textContent;
    var body = $('#post_' + postid).find('p').eq(2).text().trim();
    var count = 1;
    $('#post_' + postid).find('.findmedia').each(function (index) {
        var m_id = $(this).find('img').attr('media-id');
        m_id = m_id ? m_id : $(this).find('video').attr('media-id');
        if (m_id != '') {
            var clone_data = $(this).clone();
            var clone = clone_data;
            file_orig_name = "";
            if ($('#post_' + postid).find('input[name=file_name]').length) {
                file_orig_name = $('#post_' + postid).find('input[name=file_name]').val();
                $('#post_edit_' + postid).find('.edit_media_div').append("<div class='upload-content post_categories edit_media_div_container' style=''><span class='close'><img src=" + baseurl + "/images/ic_deleteBlue.svg id='' class='edit_file_del' fileid='" + m_id + "' onclick='close_preview_edit(this)'></span><div class='upload-text upload-attachment-box'><h3> <img src=" + baseurl + "/images/ic_IMAGE.svg alt=''><span class='upload_file_name'>" + file_orig_name + " </span></h3><p></p></div></div>");
            } else {
                $('#post_edit_' + postid).find('.edit_media_div').append("<div class='upload-content post_categories edit_media_div_container' style=''><span class='close'><img src=" + baseurl + "/images/ic_deleteBlue.svg id='' class='edit_file_del' fileid='" + m_id + "' onclick='close_preview_edit(this)'></span></div>");
            }

            $(clone).find('video').replaceWith('<img style="margin-right: 20px;" src="' + baseurl + '/images/ic_VIDEO.svg" viewfile="' + m_id + '" id="view_file" media-id="2"><span style="display: inline-block; overflow-wrap: break-word; width: 86%;">' + $('#post_' + postid).find('input[name=file_orignal_name]').val() + '</span>');
            $('#post_edit_' + postid).find('.edit_media_div').find('.edit_media_div_container').eq(index).append(clone);
            $('#post_edit_' + postid).find('.edit_media_div').find('.edit_media_div_container').find('.findmedia').removeClass('hidden');            
            $('.remove_all_trigger').show();
        }
        count++;
        $('#post_edit_' + postid).find('.upload-attachment-box:not(:first)').remove();
    });

    $('.edit_post_form').find('.editing_post_id').val(postid);
    $('.edit_post_form').find('span').eq(0).attr({"style": profile_image});
    $('.edit_post_form').find('textarea.post_subject').val(subject);
    $('.edit_post_form').find('textarea.post-description-textarea').val(body);

    autosize(document.querySelectorAll('textarea.t1-resize'));
    autosize(document.querySelectorAll('textarea.t2-resize'));

    /*22-02-2017*/
    var visibility = $('#post_' + postid).find('.post_visible_user').val();
    var value = visibility.replace(" ", "");
    var visible_user_count = value.split(",").length - 1;
    $('.visibility_alert_count').val(visible_user_count);

    $('#visibilty_count').val(visible_user_count);

    var visibility_all_count = 0;
    $('#post_edit_' + postid).find('.edit-post-checkbox').each(function (index) {
        var visible_all = $(this).val();

        if (visibility.indexOf(visible_all) > -1 || visibility.toLowerCase().indexOf("all") > -1) {
            $(this).parent('label').addClass('active');
            $(this).prop('checked', 'checked');
            visibility_all_count = visibility_all_count;
        } else {
            $(this).parent('label').removeClass('active');
            $(this).prop('checked', '');
        }

    });

    /*check if all exiist then add active to everyone*/
    if (visibility.indexOf("All") >= 0 || $('#post_edit_' + postid).find('.edit-post-checkbox').length == visibility.split(',').length-1) {
        $('#post_edit_' + postid).find(".edit_selection_visibility").text("Everyone");
        $('#post_edit_' + postid).find('.hidden_edit_everyone_box').val('true');
        $('#post_edit_' + postid).find('.select_all_visibility_edt').attr('checked', 'checked');
        $('#post_edit_' + postid).find('.select_all_visibility_edt').parent().addClass('active');
    } else {
        $('#post_edit_' + postid).find('.hidden_edit_everyone_box').val('false');
        $('#post_edit_' + postid).find('.select_all_visibility_edt').checked = false;
        $('#post_edit_' + postid).find('.select_all_visibility_edt').parent().removeClass('active');
        $('#post_edit_' + postid).find(".edit_selection_visibility").text(visible_user_count + " Member(s)");
    }
    if (visible_user_count == visibility_all_count) {
        $('.select_all_visibility_edit').prop('checked', 'checked');
        $('.select_all_visibility_edit').parent().addClass('active');
        $('.select_all_visibility_edit').parent().text('Everyone');
        $('.hidden_edit_everyone_box').val('true');


    }
    /*category selected */
    $('#post_edit_' + postid).find('.cat_id_edtt').each(function (index) {
        var cate_all = $(this).val();
        if (category == cate_all) {
            $(this).parent().parent().parent().addClass('active');
            $('.category_heading').text($(this).attr('catgoryname'));
            $('.editcategory').val(category);
        }
    });

    $('.main_post_ta2').trigger('paste');
    $('#post_edit_' + postid).find('.url_embed_trigger').css('display', 'none');
    return {profile_image: profile_image, usrename: usrename, category: category, subject: subject, body: body, visibility: visibility};
}

function d_edig() {
    $("#upload").trigger('click');
}

function readURL_edtt(input, file_ext, id) {
    $('.upload_file_name_edtt').html(file_ext);
    var extension = file_ext.substr((file_ext.lastIndexOf('.') + 1));

    if (extension == 'png' || extension == 'gif' || extension == 'jpg' || extension == 'jpeg') {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            $('.post_categories_file_edt' + id).show();
            $('.post_categories_edit' + id).show();
            $('#post_button' + id).show();

            reader.onload = function (e) {
                $('#blah_edit' + id).show();
                $('#blah_edit' + id).attr('src', e.target.result)
                    .height('auto');
            }

            reader.readAsDataURL(input.files[0]);
            $('.upload_file_name_edtt').parent().find('img').attr('src', '../images/ic_IMAGE.svg');
            $('.upload_file_name_edtt').html(file_ext);
        }
    }
}

function preview_before_upload_edita(ele, thisid) {
    $('#' + thisid + ' .post_categories_file_temp_edit').remove();
    $('#' + thisid + ' .post_file_edtt').each(function (index) {
        var file_type = "image";
        if ($(this)[0].files[0].type.indexOf('image') >= 0)
            file_type = 'image';
        if ($(this)[0].files[0].type.indexOf('video') >= 0)
            file_type = 'video';

        var clone = $('#' + thisid + ' .post_categories_file_edt').eq(0).clone();
        file_ext = $(this)[0].files[0].name.split('.');
        var extension = file_ext.pop();
        file_ext = file_ext.join('.');
        clone.find('.upload_file_name_edtt').html(file_ext.toString());

        if (file_type == 'video')
            clone.find('img').eq(1).attr('src', '../images/ic_VIDEO.svg');
        if (file_type == 'image')
            clone.find('img').eq(1).attr('src', '../images/ic_IMAGE.svg');
        if (extension == 'ppt' || extension == 'pptx')
            clone.find('img').eq(1).attr('src', '../images/ic_PWERPOINT.svg');
        if (extension == 'csv' || extension == 'xls' || extension == 'xlsx')
            clone.find('img').eq(1).attr('src', '../images/ic_EXCEL.svg');
        if (extension == 'pdf')
            clone.find('img').eq(1).attr('src', '../images/ic_PDF.svg');
        if (extension == 'doc' || extension == 'docx')
            clone.find('img').eq(1).attr('src', '../images/ic_WORD.svg');

        clone.find('img').eq(2).hide();
        clone.find('img').eq(0).attr('id', index);
        clone.removeClass('post_categories_file_edt');
        clone.addClass('post_categories_file_temp_edit');
        $('#' + thisid + ' .post_categories_file_edt').after(clone);
        $('#' + thisid + ' .post_categories_file_edt').hide();
        clone.show();
    });
}

function readURL_edit(input, file_ext, id, thisid) {
    $('#' + thisid + ' .upload_file_name_edtt').html(file_ext);
    var extension = file_ext.substr((file_ext.lastIndexOf('.') + 1));
    if (extension == 'png' || extension == 'gif' || extension == 'jpg' || extension == 'jpeg') {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            $('.post_categories_maincontent' + id).show();
            $('.post_categories_file' + id).show();
            $('.post_categories' + id).show();
            $('#post_button' + id).show();
            reader.onload = function (e) {
                $('#blah' + id).show();
                $('#blah' + id).attr('src', e.target.result)
                    .height('auto');
            }

            reader.readAsDataURL(input.files[0]);
            $('#' + thisid + ' .upload_file_name_edtt').parent().find('img').attr('src', '../images/ic_IMAGE.svg');
            $('#' + thisid + ' .upload_file_name_edtt').html(file_ext);
        }
    } else if (extension == 'pdf') {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('#' + thisid + ' .upload_file_name_edtt').parent().find('img').attr('src', '../images/ic_PDF.svg');
        $('#' + thisid + ' .upload_file_name_edtt').html(file_ext);

    } else if (extension == 'mp4' || extension.toLowerCase() == 'mov' || extension == 'mp3' || extension == 'avi' || extension == 'mkv') {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('#' + thisid + ' .upload_file_name_edtt').parent().find('img').attr('src', '../images/ic_VIDEO.svg');
        $('#' + thisid + ' .upload_file_name_edtt').html(file_ext);
    } else if (extension == 'doc' || extension == 'docs' || extension == 'docx') {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('#' + thisid + ' .upload_file_name_edtt').parent().find('img').attr('src', '../images/ic_WORD.svg');
        $('#' + thisid + ' .upload_file_name_edtt').html(file_ext);
    } else if (extension == "xlsx") {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('#' + thisid + ' .upload_file_name_edtt').parent().find('img').attr('src', '../images/ic_EXCEL.svg');
        $('#' + thisid + ' .upload_file_name_edtt').html(file_ext);
    } else if (extension == "xls") {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('#' + thisid + ' .upload_file_name_edtt').parent().find('img').attr('src', '../images/ic_EXCEL.svg');
        $('#' + thisid + ' .upload_file_name_edtt').html(file_ext);
    } else if (extension == "csv") {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('#' + thisid + ' .upload_file_name_edtt').parent().find('img').attr('src', '../images/ic_EXCEL.svg');
        $('#' + thisid + ' .upload_file_name_edtt').html(file_ext);
    } else if (extension == 'ppt' || extension == 'pptx') {
        $('.post_categories_maincontent' + id).show();
        $('.post_categories_file' + id).show();
        $('.post_categories' + id).show();
        $('#post_button' + id).show();
        $('#' + thisid + ' .upload_file_name_edtt').parent().find('img').attr('src', '../images/ic_PWERPOINT.svg');
        $('#' + thisid + ' .upload_file_name_edtt').html(file_ext);
    }
    return false;
}

function textAreaAdjust(o) {
    o.style.height = "1px";
    o.style.height = (25 + o.scrollHeight) + "px";
}

function selectComp(val) {
    $(".sub_comp_input").val(val);
    $("#suggesstion-box").hide();
}

function upload_feed_file_error() {
    $('button.post_btn').prop('disabled', false);
}

function upload_executive_file_error() {
    $('.save_executive_btn').prop('disabled', false);
    $('.onboarding_save_executive_btn').prop('disabled', false);
}

function upload_executive_file() {
    if (($('.upload-preview-wrap .s3_running_process:visible').length + $('.upload-preview-wrap .executive_file_s3:visible').length + $('.upload-preview-wrap .remove_executive_file:visible').length) >= 2) {
       $('.file_upload_error').text('You can upload a maximum of 2 files to the executive summary. Please remove an existing file.');
       return 0;
    }
    direct_upload_s3_data.push({
        'storage': 's3_executive',
        'progress_element_class': 's3_progress',
        'form_field_class': 'executive_aws_files_data',
        'done_callback': 'executive_file_preview',
        'error_callback': 'upload_executive_file_error',
        'allowed_extension': ['pdf', 'docx', 'ppt', 'pptx', 'mp4', 'doc', 'xls', 'xlsx', 'csv' , 'mov', 'MOV'],
        'progress_bar_ele': '.upload-preview-wrap'
    });
    $('#upload_s3_file').trigger('click');

}

function executive_file_preview(file_data) {
    html = '<div class="executive_file_s3 pdf_list_file"><span class="link-input-icon"><img src="'+baseurl+'/images/ic_link.svg"></span><span>' + file_data.originalName + '</span><a id="' + file_data.uid + '" class="remove_executive_s3_file" href="#!"><img src="'+baseurl+'/images/ic_highlight_remove.svg" alt="" id="" class=""></a></div>';
    $('.upload-preview-wrap').append(html);
    $('.' + file_data.uid).remove();

    if( !$('.s3_running_process').length ){
        $('.save_executive_btn').prop('disabled', false);
        $('.onboarding_save_executive_btn').prop('disabled', false);
    }

    if (($('.upload-preview-wrap').find('.remove_executive_file').length + $('.upload-preview-wrap').find('.remove_executive_s3_file').length) >= 2) {
        $('span.fileupload-new').show();
        $('i.fa-upload').show();
    }

    if($('.upload-preview-wrap').find('.remove_executive_file:visible').length >= 2 || $('.upload-preview-wrap').find('.executive_file_s3:visible').length >=2){
       $('.upload_doc_col').hide();
    }
    if($('.upload-preview-wrap').find('.remove_executive_file:visible').length == 1 && $('.upload-preview-wrap').find('.executive_file_s3:visible').length ==1){
       $('.upload_doc_col').hide();
    }
}

function make_form_post_ready(ele) {
    $('.body').trigger('click');
    $(ele).parent().parent().removeClass('post-sbj');
    $(".post_categories_maincontent").show();
    $('#save_post_btn_new').show();
    $(".post_categories").show();
    $(".black-overlay").show();
    $("#tour2").addClass("highlight");
    $(".subject_class").show();
    $(".subject_text").show();
    $(".post_subject").css('padding-top', '5px');
    $(".post_subject").attr("placeholder", "Add a subject");
    return;
}

function reset_addpost_upload_section() {
    $('.post_categories_file').hide();
    $('.post_categories_file_temp').remove();
    $('.post_categories_file_edt').hide();
    $('.post_categories_file_temp_edit').remove();
    $('.post_file').each(function () {
        if ($(this).attr('id') == 'upload')
            $(this).hide();
        else
            $(this).remove();
    });
    $('.post_file_edtt').each(function () {
        if ($(this).attr('id') == 'upload_edtt')
            $(this).hide();
        else
            $(this).remove();
    });

    del_old_files = "";
    $('.edit_file_del').each(function (index) {
        $(this).trigger('click');

    });
    $('.remove-all').hide();
    return true;
}

function reset_form_post_ready(ele) {
    $('.post_show_hidden').val(0);
    $('.post-wrap').remove();
    $('.body').trigger('click');
    $(ele).parent().parent().addClass('post-sbj');
    $(".post_categories_maincontent").hide();
    $('#save_post_btn_new').hide();
    $(".post_categories").hide();
    $(".black-overlay").hide();
    $("#tour2").removeClass("highlight");
    $(".subject_class").hide();
    $(".subject_text").hide();
    $(".post_subject").css('padding-top', '5px');
    $(".post_subject").attr("placeholder", "");
    $('.add_post_form').find('.form-submit-loader').hide();
    $('.url_embed_trigger').trigger('click');
    $('.remove_all_trigger').trigger('click');
    $('input[name="uploaded_file_aws"]').val('');
    uploaded_file_aws = new Array();
    filesUploaded = [];
    $(document).trigger('scroll');

    /* sec1 */
    $('.category_drop_share').hide();
    $('.category_drop').show();

    $('.visibilty-drop-wrap-share').hide();
    $('.visibilty-drop-wrap').show();

    $('.alert-drop-wrap-share').hide();
    $('.alert-drop-wrap').show();
    $('.share-drop-wrap').hide();
    $('.post_categories_maincontent').removeClass('share-box');
    $('.category-drop').multiselect('rebuild');
    /* reset Visibility drop down start */
    $('ul.visibilty-drop').find('input[type="checkbox"]').each(function () {
        $(this).attr('checked', false);
        $(this).parent().removeClass('active');
    });
    $('ul.visibilty-drop').find('input[name="visibility[]"]').each(function () {
        $(this).attr('checked', 'checked');
        $(this).parent().addClass('active');
    });
    $('.select_all_visibility').attr('checked', 'checked');
    $('.select_all_visibility').parent().addClass('active');
    $('span.selection_visibility').html('Everyone');
    $('ul.alert-drop').find('input[name="alert[]"]').each(function () {
        $(this).attr('checked', 'checked');
        $(this).parent().addClass('active');
    });
    $('.select_all_alert').attr('checked', 'checked');
    $('.select_all_alert').parent().addClass('active');
    $('span.selection_alert').html('Everyone');
    /* reset Alert drop down end */

    /* reset "choose share" drop down start */
    $('ul.multishare').find('input[name="share[]"]').each(function () {
        $(this).attr('checked', false);
        $(this).parent().removeClass('active');
    });
    $('.multiselect-all').attr('checked', false);
    $('.select_all_alert').parent().removeClass('active');
    $('.choose_share').html('Choose');

    $('.post_subject').attr('placeholder', 'Click here to add text, files, links etc.');
    return;
}

function add_attachment_post(ele) {
    $("#upload").trigger('click');
    return;
}

function prepareViewerURL(signed_url){
    if(['pdf'].indexOf(signed_url.file_ext) > -1){
        url = baseurl+'/pdf_viewer/web/viewer.html?file='+signed_url.file_url;
    } else if((['ppt', 'pptx', 'doc', 'docx'].indexOf(signed_url.file_ext)) > -1){
        url = 'https://view.officeapps.live.com/op/embed.aspx?src='+signed_url.file_url+'&wdAr=1.3333333333333333';
    } else {
        url = signed_url.file_url;
    }
    return url;
}

function postsLoadedSuccessfully(){
    // this section run after posts load
    return;
}
/*-------------End jQuery Functions----------------*/

window.reset = function (e) {
    e.wrap('<form>').closest('form').get(0).reset();
    document.getElementById("upload_video_name").innerHTML = "";
    e.unwrap();
}
    /*FEEDBACK FORM VALIDATION START*/
$(document).on('submit', '.feedback_form',function(){

    if(!$('.rating').is(':checked')) {
       $('.rating-wrap').parent().find('.error-msg').remove();
       $('.rating-wrap').after('<span class="error-msg error-body text-left rating-error" style="text-align: left;">Rating is mandatory</span>');
       error = 1;
    }else{
       error = 0;
    }
    if( error ){
       return false;
    } else {
       return true;
    }
});

/*FEEDBACK FORM VALIDATION END*/
function getSuggeCount(texVal){
   var texlen = texVal.length;
   $('.subButton').attr("disabled", false);
   $('.subButton').removeClass('disabled');
   $(".suggCount").text(texlen+'/500');

}
function getCommCount(texVal){
   var texlen = texVal.length;
   $('.subButton').attr("disabled", false);
   $('.subButton').removeClass('disabled');
   $(".commCount").text(texlen+'/500');

}

function loadEditPostTemplate(){
    $.ajax({
        type: "GET",
        url: baseurl + '/get_edit_post_template/' +session_space_id,
        success: function( response ) {
            $('.edit_popup_skull').html(response);
        }
    });
}

$(".suggesResize, .genCommResize, .commentResize").on('click change, focus', function() {
         autosize(document.querySelectorAll('textarea.suggesResize'));
         autosize(document.querySelectorAll('textarea.genCommResize'));
         autosize(document.querySelectorAll('textarea.commentResize'));
});

$('#feedback-popup').on('shown.bs.modal', function () {
});

var d = new Date();

var month_names_array = [ "months","January", "February", "March", "April", "May", "June",
"July", "August", "September", "October", "November", "December" ];
var n = month_names_array[d.getMonth()];

$(document).on("click", ".top_post_add_link", function() {
    $('.add_post_form .post_subject').trigger('click');
});

$(document).ready(function () {
    loadEditPostTemplate();
    $.ajax({
        type: "GET",
        async: false,
        url: baseurl + '/get_add_post_template/' +session_space_id,
        success: function( response ) {
            $('.add_post_form_ajax').html(response);
            eval($('.add_post_form_ajax').html);
        }
    });

    $('.subButton').addClass('disabled');
    $('.subButton').attr("disabled", true);
     $(document).on("click", ".rating", function() {
         var r = $(this).val();
           if(parseInt(r) >= 0 ){
               $('.subButton').removeClass('disabled');
               $('.subButton').attr("disabled", false);
                 if($('.rating-error').length > 0) {
                     $('.rating-error').hide();
                 }
           }
     });
    /* Show addpost from on anywhere click at post section*/
    $('.add_post_form').on('click', function(){
        make_form_post_ready($('.post_subject'));
    });

    $('.post_subject').on('focus', function(e){
        make_form_post_ready($('.post_subject'));
        e.preventDefault();
    });

    $('.post-button').on('click',function(){
        $('.post_subject').focus();
    });

    $(document).on("click", ".last-month", function() {
        var cur_month = $("#curnt-month").attr("monnum");
        var cur_year = $("#curnt-month").attr("yearnum");

        if(cur_month=="1") {
          var prev_month = 12;
          var prev_year  = parseInt(cur_year)-1;
        } else {
          var prev_month = parseInt(cur_month)-1;
          var prev_year  = cur_year;
        }

        var company = '';
        if($(".top_post_buyer").hasClass('active')) {
          var company = $(".top_post_buyer").text();
        }
        if($(".top_post_seller").hasClass('active')) {
          var company = $(".top_post_seller").text();
        }

        $("#curnt-month").attr("monnum",prev_month);
        $("#curnt-month").attr("yearnum",prev_year);
        var n = month_names_array[prev_month];
        $("#curnt-month").text(n);

        var url = window.location.href;
        var id = url.substring(url.lastIndexOf('/') + 1);

        $.ajax({
              type: "GET",
              async: false,
              url: baseurl+'/gettopthreepost?month='+ $("#curnt-month").attr("monnum") +'&year='+ $("#curnt-month").attr("yearnum")+'&company='+company+'&space_id='+id,
              beforeSend:function() {
              },
              success: function( response ) {
                 $('.top-post-ajax-div').html(response);
              }
          });
    });

    $(document).on("click", ".next-month", function() {
        var cur_month = $("#curnt-month").attr("monnum");
        var cur_year = $("#curnt-month").attr("yearnum");
        if(cur_month=="12")
        {
          var prev_month = 1;
          var prev_year  = parseInt(cur_year)+1;
        } else
        {
          var prev_month = parseInt(cur_month)+1;
          var prev_year  = cur_year;
        }

        var company = '';
        if($(".top_post_buyer").hasClass('active')) {
            var company = $(".top_post_buyer").text();
        }
        if($(".top_post_seller").hasClass('active')) {
            var company = $(".top_post_seller").text();
        }
        if($(this).attr('curr_month') >= prev_month  && $(this).attr('curr_year') >= prev_year ){
            $("#curnt-month").attr("monnum",prev_month);
            $("#curnt-month").attr("yearnum",prev_year);
            var n = month_names_array[prev_month];
            $("#curnt-month").text(n);
        } else {
            if($(this).attr('curr_month') <= prev_month  && $(this).attr('curr_year') != prev_year ){
                $("#curnt-month").attr("monnum",prev_month);
                $("#curnt-month").attr("yearnum",prev_year);
                var n = month_names_array[prev_month];
                $("#curnt-month").text(n);
            }
        }

        var url = window.location.href;
        var id = url.substring(url.lastIndexOf('/') + 1);
        $.ajax({
                type: "GET",
                async: false,
                url: baseurl+'/gettopthreepost?month='+ $("#curnt-month").attr("monnum") +'&year='+ $("#curnt-month").attr("yearnum")+'&company='+company+'&space_id='+id,
                beforeSend:function() {
                },
                success: function( response ) {
                 $('.top-post-ajax-div').html(response);
                }
            });

    });

    $.validator.addMethod("defaultInvalid", function(value, element) {
        return !(element.value == element.defaultValue);
    });

    $(document).on('click','.save_executive_btn,.onboarding_save_executive_btn',function(){
        var id = this.id;
        var reference = $('#'+id).closest('.executive_summary_save');
        reference.find('.executive-textarea-error').remove();
        if(id != 'onboarding_save_executive_btn'){
            if(reference.find('.summary_box').val().trim() == ''){
                reference.find('.summary_box').addClass('has-error');
                reference.find('.executive-textarea-col').after('<span class="error-msg executive-textarea-error text-left">Executive Summary cannot be empty</span>');
                return false;
            }
            if(reference.find('.summary_box').val().trim().lenght > executive_summary_max_length){
                reference.find('.summary_box').addClass('has-error');
                reference.find('.executive-textarea-col').after('<span class="error-msg executive-textarea-error text-left">Executive Summary cannot be greater than '+executive_summary_max_length+' characters</span>');
                return false;
            }
        }
        reference.submit();
        return false;
    });

    $(document).on('submit','#executive_summary_save,#welcome_executive_summary_save',function(e){
       e.preventDefault();
       var reference = $('.save_executive_btn');
       reference.attr('disabled',true);
       var form =$(this);
       var form_data = new FormData(form[0]);
       $.ajax({
              type: 'post',
              url: baseurl+'/executive_summary_save',
              data:  form_data,
              async: true,
              beforeSend: function()
              { 
                 $('#welcome_tour .form-submit-loader').removeClass('hidden');
              },
              success: function (response) 
              {
                 $('#welcome_tour .form-submit-loader').addClass('hidden')
                 if(response.result)
                 { 
                    var summary = response.executive_summary;
                    $('.executive_show_less').show();
                    $('.executive_show_more').hide();
                    if(summary.length <= 180)
                    {
                        $('.executive_show_less').html(summary);
                        $('.executive_show_more').html(summary);
                    }
                    else
                    {
                        $('.executive_show_more').html(summary+' <a href="javascript:void();" style="display: inline;">Show less</a>'); 
                        var short_summary = summary.substring(0, 180);
                        $('.executive_show_less').html(short_summary+' <a href="javascript:void();">Show more</a>');
                    }
                    if(summary != '') {
                        $('.executive-center-insider').css('min-height','100px');
                    }
                    $('.summary-links .executive-link-col, .nothing-in-executive').hide();
                    renderExecutiveAttachments();
                    current_step = form.closest('.welcome-cs-popup');
                    updateTourStep(current_step.attr('data-step'));
                    current_step.addClass('hidden');
                    current_step.next($('.welcome-cs-popup')).removeClass('hidden');
                    $('.add_executive_button').hide();
                    $('#executive_modal').modal('hide');
                    addProgressBarsection();
                 }
                 else
                 {
                    $('.executive-textarea-col').after('<span class="error-msg executive-textarea-error text-left">'+response.error+'</span>');
                 }
                 reference.attr('disabled',false);
              },
              error: function(response){
                console.log('Something went wrong.');
              },
              cache: false,
              contentType: false,
              processData: false
            });
        return false;
    });

    $(document).on('hidden.bs.modal', '#executive_modal', function () {
        location.reload();
    });

    $(".main_post_ta").on('change, paste input', get_preview_url);

    $(document).on('change,  paste input', '.main_post_ta2', function () {
        var thumbcheck = $('#thumbcheck').val();
        if ($(this).val()) {
            data = $(this).val().replace(/\n/g, " ");
            data = data.replace('#', " ");
            data = encodeURIComponent(data);
             $(this).next("span").remove();
            get_url_xhr = $.ajax({
                type: "GET",
                url: baseurl + '/get_url_data?q=' + data,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    if (get_url_xhr != null) {
                        get_url_xhr.abort();
                        get_url_xhr = null;
                    }
                },
                success: function (data) {
                    if (data != 0) {
                        if ($('#ytd_iframe').length && typeof (data.metatags) != 'undefined' && ($('#ytd_iframe').attr('src') == data.metatags["twitter:player"])) {
                            return 0;
                        }

                        html = '<img class="url_embed_trigger" src="'+baseurl+'/images/ic_highlight_removegray.svg"/>';
                        html = html + '<div class="outer-block">';
                        html = html + '<div class="inner-block">';

                        if (data.metatags["twitter:player"]) {
                            html = html + '<div class="thumbnail-block test iframe-content ">';
                            html = html + '<iframe id="ytd_iframe" allowfullscreen="allowfullscreen" width="420" height="345" src=' + data.metatags["twitter:player"] + '></iframe>';
                        } else {
                            html = html + '<div class="thumbnail-block">';
                            img_class = data.thumbnail_img ? "thumbnail-img" : "";
                            html = html + '<img src="'+baseurl+'/file_loading?url=' + data.favicon + '" class="url-favicon ' + img_class + '" onerror="this.src=\'http://' + data.domain + '/favicon.ico\'; this.removeClass"thumbnail-img);">';
                        }

                        html = html + '</div>';
                        html = html + '<div class="description-block "><div>';
                        var title = data.title ? data.title : data.title[0];
                        if (data.full_url.search('http') < 0) {
                            html = html + '<a target="_blank" href=http://' + data.full_url + ' title=' + title + '> <img src="https://www.google.com/s2/favicons?domain=' + data.url + '"/>' + data.title + '</a>';
                        } else {
                            html = html + '<a target="_blank" href=' + data.full_url + ' title=' + data.title + '> <img src="https://www.google.com/s2/favicons?domain=' + data.url + '"/>' + data.title + '</a>';
                        }
                        if (data.description) {
                            html = html + '<p>' + data.description + '</p>';
                        }

                        /* Description block close*/
                        html = html + '</div></div>';

                        /* Outer - Innner block close */
                        html = html + '</div></div>';

                        $('.url_embed_div_edit').empty();
                        $('.url_embed_div_edit').append(html);
                        $('input[name=url_embed_toggle]').eq(1).val(1);
                        $('#thumbcheck').val(1);

                    } else {
                        $('.url_embed_div_edit').empty();
                    }
                }
            });
        } else {
            $('#thumbcheck').val(0);
        }
    });

    $(document).on('click', '.url_embed_trigger', function () {
        $('input[name=url_embed_toggle]').val(0);
        $(this).parent().empty();
    });


    $(document).on('click', '.remove', function () {
        var hideval = $(this).find('input').val();
        var index = 0;
        for (i = 1; 1 < postdata.length; i++) {
            if (postdata[i].id == hideval) {
                index = i;
                break;
            }
        }
        postdata.splice(index, 1);
        $('#lbljson').val(JSON.stringify(postdata));

        $(".second_form").prop('disabled', true);
        var numItems = $('.imgpanel').length;
        if (numItems == 1) {
            $(".second_form").prop('disabled', true);
        }
        $(this).parent().parent().parent(".imgpanel").remove();
        var arrayval = "";
        $('.mainimage').each(function () {
            var numItems = $('.imgpanel').length;
            if (numItems == 1) {
                var getval = $(this).attr('value');
                arrayval = getval;
            } else if (numItems > 1) {
                var getval = $(this).attr('value');
                arrayval += getval + ',';
            }
        });
        var newdata = arrayval.slice(0, -1);
        $('#remainingimages').val(newdata);
    });

    $(function () {
        $(".exe_summry[maxlength]").bind('input propertychange', function () {
            var maxLength = $(this).attr('maxlength');
            if ($(this).val().length > maxLength) {
                $(this).val($(this).val().substring(0, maxLength));
            }
        })

        $(".exe_summry").keyup(function (e) {
            while ($(this).outerHeight() < this.scrollHeight + parseFloat($(this).css("borderTopWidth")) + parseFloat($(this).css("borderBottomWidth"))) {
                $(this).height($(this).height() + 1);
            }
            ;
        });
    });

    /******************If buyer login**********************/
    check_sub_comp_status_buyer = $('.buyer_info_hidden').attr('sub-comp-active');
    if (check_sub_comp_status_buyer == 1) {
        $('.sub_comp_div').css("display", "block");
        $('.sub_comp_input').attr('name', 'sub_comp');
        $('.sub_comp_input').addClass('c_side_validation');
    }
    /******************If buyer login**********************/
    $('.executive-summary-preview .findmedia').on('click', function () {
        custom_logger({
            'description': 'view executive file',
            'action': 'view executive file'
        });
    });

    $(document).on('click', '.read-more-trigger', function () {
        if ($("#" + $(this).attr('for')).prop("checked") == false) {
            $(this).find('span').hide();
        } else {
            $(this).find('span').show();
        }
    });

    video_player_log_bind();
    onYouTubeIframeAPIReady();


    /*********************Multiselect Toggle Start*************************/

    $(".add_post_form .post-visiblity-checkbox").change(function () {
        var this_checked = this.checked;
        $(this).prop('checked', this_checked).parent().toggleClass('active', this_checked);
        label = postDropDownLabel(1,this_checked?1:0);
        $('.selection_visibility').text(label.text);

        $('.add_post_form .visibilty-drop .visiblity-checkbox')
            .prop('checked', this_checked)
            .parent().toggleClass('active', this.checked);

        $('.add_post_form .alert-drop .alert-checkbox')
            .prop('checked', this_checked)
            .parent().toggleClass('active', this.checked)
            .toggleClass('disable_check', !this.checked);

        $('.add_post_form').find('.post-alert-checkbox').parent()
            .toggleClass('active', this.checked)
            .toggleClass('disable_check', !this.checked);

        $(".select_all_alert").trigger('change');
    });


    $('.visibilty-drop .visiblity-checkbox').change(function () {
        postVisiblityUser(this, $('.add_post_form') );
    });

    //////////////////for child checkboxes handling/////////////////////////////////////
    $(document).on('click', '.checkbox', function () {
        $(this).parent().parent().parent().addClass('open');
    });

    $(document).on('click', '.alert-drop .disable_check', function () {
        return false;
    });

    $('.alert-drop .checkbox2').change(function () {  //any checkbox in child changes
        $(this).closest('.dropdown-wrap').addClass('open');
        postAlertUser($(this), this.checked, $('.add_post_form'));
    });

    // add post start
    $(".select_all_alert").change(function () {
        $('.add_post_form .alert-drop').find('input:checkbox.checkbox2').parent().not('.disable_check').toggleClass('active', this.checked).find('input:checkbox').prop('checked', this.checked);// toggle all cb

        label = postDropDownLabel($('.add_post_form .alert-drop').find('input:checkbox.checkbox2').length, $('.add_post_form .alert-drop').find('input:checkbox:checked.checkbox2').length);
        $('.selection_alert').text(label.text);

        label = postDropDownLabel($('.add_post_form .alert-drop').find('input:checkbox.checkbox2').parent().not('.disable_check').length, $('.add_post_form .alert-drop').find('input:checkbox:checked.checkbox2').length);
        $('.select_all_alert').parent().toggleClass('active', label.master_box_check);
    });
    // add post start end


    /* Edit post start */
    $(document).on('change', ".edit_post_form .post-visiblity-checkbox", function () {
        var postid = $('.editing_post_id').val();
        var this_checked = this.checked;
        $(this).prop('checked', this_checked).parent().toggleClass('active', this_checked);
        label = postDropDownLabel(1,this_checked?1:0);
        $('.edit_selection_visibility').text(label.text);

        $('#post_edit_' + postid+' .visibilty-drop .visiblity-checkbox')
            .prop('checked', this_checked)
            .parent().toggleClass('active', this.checked);

        $('#post_edit_' + postid+' .alert-drop .alert-checkbox')
            .prop('checked', this_checked)
            .parent().toggleClass('active', this.checked)
            .toggleClass('disable_check', !this.checked);

        $('#post_edit_' + postid).find('.post-alert-checkbox')
            .prop('checked', this_checked)
            .parent().toggleClass('active', this.checked)
            .toggleClass('disable_check', !this.checked);

        $(".edit_post_form .post-alert-checkbox").trigger('change');
    });

    $(document).on('click', '.repost', function () {
        var postid = $('.editing_post_id').val();
        $('#post_edit_' + postid).find('.submit_edit_post').html('Post');
        $(this).parent().parent().find('.edit-alert-bx-disable').toggle(!this.checked);
        $(this).parent().parent().find('.edit-alert-bx').toggle(this.checked);

        $('#post_edit_' + postid).find('.alert-drop .alert-checkbox').attr('checked', false)
            .parent().addClass('disable_check').removeClass('active');

        var visiblity_list = $('#post_edit_' + postid).find('.active .visiblity-checkbox')
            .map(function() {
                return this.value;
            }).get();

        visiblity_list = "input:checkbox[value='" + visiblity_list.join("'],input:checkbox[value='") + "']";
        $('#post_edit_' + postid).find('.alert-drop').find(visiblity_list).attr('checked', true)
            .parent().addClass('active').removeClass('disable_check');
        $(".edit_post_form .post-alert-checkbox").trigger('change');
    });

    $(document).on('change', '.edit-post-alert-checkbox', function () {
        $(this).closest('.dropdown-wrap').addClass('open');
        postAlertUser( $(this), this.checked, $('#post_edit_'+$('.editing_post_id').val()));
    });

    $(document).on('change', '.edit-post-checkbox', function () {
        postVisiblityUser(this, $('#post_edit_'+$('.editing_post_id').val()));
    });

    $(document).on('click','.visibility_group',function(e) {
        var checked = this.checked;
        if( $('.add_post_form').find('input:checkbox:checked.visibility_group').length == 1){
            postFormListReset($('.add_post_form').find('.visibilty-drop'), $('.add_post_form').find('.alert-drop'));
            $(this).prop('checked', true);
        }

        $(this).closest('.visibilty-drop-wrap').addClass('open');
        $(this).parent().toggleClass('active', checked);
        $.ajax({
            type: "GET",
            dataType:"json",
            url: baseurl+'/get_group_members?gid='+$(this).attr('id'),
            success: function(response) {
               $(response).each(function(){
                  user_checkbox = $('.add_post_form').find('.visibilty-drop').find('input:checkbox[value="'+this.space_user.user_id+'"]');
                  user_checkbox.prop('checked', checked);
                  postVisiblityUser(user_checkbox, $('.add_post_form') );
               });
            }
        });
    });

    $(document).on('change', '.edit_post_form .post-alert-checkbox', function () {
        var postid = $('.editing_post_id').val();
        $('#post_edit_' + postid).find('.alert-drop').find('input:checkbox.edit-post-alert-checkbox').parent().not('.disable_check').toggleClass('active', this.checked).find('input:checkbox').prop('checked', this.checked);// toggle all cb

        label = postDropDownLabel($('#post_edit_' + postid).find('.alert-drop').find('input:checkbox.edit-post-alert-checkbox').length, $('#post_edit_' + postid).find('.alert-drop').find('input:checkbox:checked.edit-post-alert-checkbox').length);
        $('#post_edit_' + postid).find('.post-alert-label').text(label.text);

        label = postDropDownLabel($('#post_edit_' + postid).find('.alert-drop').find('input:checkbox.edit-post-alert-checkbox').parent().not('.disable_check').length, $('#post_edit_' + postid).find('.alert-drop').find('input:checkbox:checked.edit-post-alert-checkbox').length);
        $(this).parent().toggleClass('active', this.checked);
    });

    $('.category-drop').multiselect({
        numberDisplayed: 1,
        includeSelectAllOption: true,
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        nonSelectedText: 'NOTHING SELECTED'
    });

    $('.categories-edit-drop').multiselect({
        numberDisplayed: 1,
        includeSelectAllOption: true,
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        nonSelectedText: 'NOTHING SELECTED'
    });

    $('.alert-edit-drop').multiselect({
        numberDisplayed: 1,
        includeSelectAllOption: true,
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        nonSelectedText: 'NOTHING SELECTED'
    });

    /*********************End Multiselect Toggle End*************************/


    $(".exe_sum_video").on('click', function (e) {
        if($('.modal-dialog').hasClass('full-width-doc')) {
              $('.modal-dialog').removeClass('full-width-doc');
        }
    });


    $('.ph_number').bind('keyup paste', function () {
      position = this.selectionStart;
      this.value = this.value.replace(/[^ 0-9+(),-.]/g, '');
      this.selectionEnd = position;
    });


    $("#upload_link").on('click', function (e) {
        add_attachment_post(this);
    });

    $(".camera-icon").on('click', function (e) {
        $("#img_show").trigger('click');
    });

    $("#myModalInvite").on('hide.bs.modal', function () {
        location.reload();
    });

    $('body').on('hidden.bs.modal', '.modal', function () {
        $('video').trigger('pause');
    });

    /*-------- Get iframe src attribute value i.e. YouTube video url
     and store it in a variable----------*/
    var url = $("#cartoonVideo").attr('src');
    /*-------- Assign empty url value to the iframe src attribute when
     modal hide, which stop the video playing ------------*/
    $("#myModal").on('hide.bs.modal', function () {
        $("#cartoonVideo").attr('src', '');
    });
    /*--------- Assign the initially stored url back to the iframe src
     attribute when modal is displayed again -------*/
    $("#myModal").on('show.bs.modal', function () {
        $("#cartoonVideo").attr('src', url);
    });

    $('.white_box_info').on('click', function () {
        $(this).fadeOut('fast');
    });

    /*-----------Remove error-msg from input when value change------------*/
    $("textarea, input").on('change, keyup paste input', function () {
        $(this).removeClass('has-error');
        $(this).parent().removeClass('has-error');
        $(this).parent().find('.error-msg').remove();
    });

    $("input[name=first_name]").on('keyup', function () {
        $(".mailbody").find('span').eq(0).show();
        $(".mailbody").find('span').eq(1).html(' ' + $(this).val() + ',');

    });

    $(".mailbody").on('focus', function () {
        $('.mail_body').get(0).selectionStart = $('.mail_body').html().length;
        $('.mail_body').get(0).selectionEnd = $('.mail_body').html().length;
        $('.mail_body').focus();
    });


    $(document).on('click', '.addendorse', function () {
        var endorse_id = $(this).attr('add-endorse-id');
        var user_id = $(this).attr('add-endorse-userid');
        var post_honor = $(this).attr('data-honor');
        var space_id = $(this).attr('space-id');
        var liked_by_email = $(this).attr('get-liked-by-email');

        var file_name_index = $(this).find("img").attr('src').lastIndexOf("/") + 1;
        var file_name = $(this).find("img").attr('src').substr(file_name_index);
        if (file_name == 'ic_thumb_up.svg') {
            $(this).find("img").attr('src', baseurl + "/images/ic_thumb_up_grey.svg");
            var like_status = 1; //liked
            var liked_from_email = 0;
        }
        if (file_name == 'ic_thumb_up_grey.svg') {
            $(this).find("img").attr('src', baseurl + "/images/ic_thumb_up.svg");
            var like_status = 0; //disliked
            var liked_from_email = 0;
        }
        if(liked_by_email == 1){
            $(this).find("img").attr('src', baseurl + "/images/ic_thumb_up.svg");
            var like_status = 0; //liked
            var liked_from_email = 1;
        }

        $.ajax({
            type: "GET",
            url: baseurl + '/endorse?endorseid=' + endorse_id + '&userid=' + user_id + '&spaceid=' + space_id + '&posthonor=' + post_honor + '&like_status=' + like_status+'&liked_from_email='+liked_from_email,
            success: function (response) {
                $('.endorsediv_' + endorse_id).html(response);
            },
            error: function (error) {
                custom_logger({
                    'action': 'endorse post',
                    'description': error
                });
            }
        });
    });

    $(document).on('click', '.endorsed_popup', function () {
        var postid = $(this).attr('endors-poup-post');
        var spaceid = $(this).attr('space-id');
        $.ajax({
            type: "GET",
            url: baseurl + '/endorsepopup_ajax?endorseid=' + postid + '&spaceid=' + spaceid,
            success: function (response) {

                $('.endorse_popup_modal').html(response);
            },
            error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'endorsed_popup');
            }
        });
    });

    $(document).on('click', '.save_edit_visiblity', function () {
        var postid = $(this).attr('ediitvisible');
        var data1 = $(".visiblity_update_" + postid).serialize().replace(/&checkbox%5B%5D=/g, ',');

        var newString = data1.replace(/checkbox%5B%5D=/g, '');
        var str1 = $(this).attr('allvisibleuser');
        var str2 = 'All';

        var visible_to = "";

        if (data1.indexOf("checkboxall=1") > -1) {
            newString = 'All,' + newString;
            newString = newString.replace(/checkboxall=1,/g, '');
            visible_to = 'All';
        }
        //user can not  remove himself and postuser
        var post_logedinuser = $(this).attr('logedin-and-postuser');
        if (newString != '') {
            newString = post_logedinuser + ',' + newString;
        } else {
            newString = post_logedinuser;
        }

        $.ajax({
            type: "GET",
            dataType: "json",
            url: baseurl + '/edit_visibillitypopup_ajax?postid=' + postid + '&visibleuser=' + newString + '&visible_to=' + visible_to,
            success: function (response) {
                mixpanelLogger({
                    'space_id': session_space_id,
                    'event_tag':'Change post visibility'
                }, true)
                if (response.user == 'all') {
                    if ($('.now_active_private' + postid).hasClass('active')) {
                        $('.now_active_private' + postid).removeClass('active');
                        $('.now_active_public' + postid).addClass('active');
                        $('.v_image' + postid).removeClass('lock');
                        $('.rest-memb' + postid).css("display", "none");

                    }
                    $('.show-user' + postid).attr('data-content', 'Everyone');
                    $('#post_' + postid).find('.post_visible_user').val(response.user_id);
                    /*22-02-2017*/
                    $('#post_' + postid).find('.post_visible_user_edit').val(response.user_id);
                    /*22-02-2017*/
                }
                else {
                    if ($('.now_active_public' + postid).hasClass('active')) {
                        $('.now_active_public' + postid).removeClass('active');
                        $('.now_active_private' + postid).addClass('active');
                        $('.v_image' + postid).addClass('lock');
                        $('.rest-memb' + postid).css("display", "block");
                    }
                    $('.show-user' + postid).attr('data-content', response.names);
                    $('#post_' + postid).find('.post_visible_user').val(response.user_id);
                    /*22-02-2017*/
                    $('#post_' + postid).find('.post_visible_user_edit').val(response.user_id);
                    /*22-02-2017*/
                }
            },
            error: function (message) {
            }
        });
    });

    $(document).on('click', '.cancel_visibility', function () {
        $(this).closest('form')[0].reset();
    });

    $(document).on('click', '.s-everyone', function () {
        var postid = $(this).attr('postid');
        var spaceid = $(this).attr('space-id');
        var visible_users = $(this).attr('visibletousers');
        $.ajax({
           type: "GET",
           url: baseurl + '/edit_visibility?postid=' + postid + '&visibleusers=' + visible_users + '&spaceid=' + spaceid,
           success: function (response) {
                if ($('.now_active_private' + postid).hasClass('active')) {
                    $('.now_active_private' + postid).removeClass('active');
                    $('.now_active_public' + postid).addClass('active');
                    $('.v_image' + postid).removeClass('lock');
                    $('.rest-memb' + postid).css("display", "none");
                }

                $('.show-user' + postid).attr('data-content', 'Everyone');
                $('#post_' + postid).find('.post_visible_user').val(response.user_id);
                /*22-02-2017*/
                $('#post_' + postid).find('.post_visible_user_edit').val(response.user_id);
                /*22-02-2017*/
           },
           error: function (message) {
           }
        });
    });

    /*--------REMOVE ADDED FILE TO POST----------*/
    $(document).on('click', '#close_post_file', function () {
        $('.post_categories_file').hide();
        var control = $("#upload");
        control.replaceWith(control = control.clone(true));
    });

    $(document).on('change', '#upload', function () {
        var clone = $(this).clone();
        clone.removeAttr('id');
        clone.addClass('post_attachment_clone');
        $(this).attr('id', 'post_file_' + ($('.post_file').length - 1));
        $('.direct-upload').after(clone);
        $('.post_attachment:visible').length ? $('.remove_all_trigger').show() : $('.remove_all_trigger').hide();
        
    });

    $(document).on('change', '#upload_edita', function () {
        var allow_extention = constants['POST_EXTENSION'];
        var file_ext = document.getElementById("upload_edita").files[0].name;
        file_ext = file_ext.split('.');
        ext = file_ext.pop();
        file_ext = file_ext.join('.');
        if (allow_extention.indexOf(ext) < 0) {
            alert("Only " + allow_extention.toString() + " extensions are allowed");
            return false;
        }

        if ($('.post_file_edita').length == 1) { // preview file if single
            file_ext = document.getElementById("upload_edita").files[0].name;
            var id = '';
            $("#blah").hide();
            readURL(this, file_ext, id);
            file_ext = file_ext.split('.');
            ext = file_ext.pop();
            file_ext = file_ext.join('.');
            $('.upload_file_name').html(file_ext.toString());
        }
        else { // list preview of files before upload
            preview_before_upload_edit(this);
        }

        var clone = $(this).clone();
        $(this).attr('id', 'post_file_' + ($('.post_file_edita').length - 1));
        $(this).after(clone);
        $('.remove-all').show();
    });

    $('textarea.comment-area').on('click', function () {
        autosize(document.querySelectorAll('textarea.comment-area'));
        $(".send_comment").attr('disabled', false);
    });



    /* On Edit Post*/
    $(document).on('click', '#edit_post', function (ev) {

        postid = $(this).attr('editpost');
        var post_by = $(this).attr('postby');
        var active_user = $(this).attr('activeuser');

        if (post_by != active_user) {
            alert('You are not Autorize for this.');
            return false;
        } else {
            $("#post_" + postid).css("display", "none");
            $(".black-overlay").css("display", "block")
            $("#editpost_" + postid).addClass("highlight").css("margin-top", "9px").show();
            $('select[name=selValue]').val(1);
            ev.preventDefault();
            return false;
        }
    });

    /*Edit Share Name*/
    $.fn.focusEnd = function () {
        $(this).focus();
        var tmp = $('<span />').appendTo($(this)),
            node = tmp.get(0),
            range = null,
            sel = null;
        if (document.selection) {
            range = document.body.createTextRange();
            range.moveToElementText(node);
            range.select();
        } else if (window.getSelection) {
            range = document.createRange();
            range.selectNode(node);
            sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
        tmp.remove();
        return this;
    }

    $(".edit_share_name").on('click', function (e) {
        $(".share_name").hide();
        $(".edit_share").show();
        $(".updated_share").focusEnd();
    });

    $(".cancel_edit_share").on('click', function (e) {
        $(".share_name").show();
        $(".edit_share").hide();
    });

   /* Update Post */
    $(document).on('click', '#edit_post_button', function () {
        var postid = $(this).attr('editpostform');
        $("#editpost_" + postid).addClass("test").css("margin-top", "9px").hide();
        $(".black-overlay").css("display", "none");
        $("#post_" + postid).show();
        var form_data = new FormData($(this).closest('form')[0]);

        $.ajax({
            type: "POST",
            url: baseurl + '/updatepost',

            data: form_data,
            processData: false,
            contentType: false,
            success: function (data) {
                if(typeof data.message != 'undefined' && data.message == 'user_deleted') {
                    window.location.href = baseurl+'/logout';  
                }
                $('.change_post_' + postid).html(data);
            },
            error: function (error) {
               console.log(error);
            }
        });
    });

    /* Remove Added File from Edit Post*/
    $(".edit_preview_file").on('click', function (e) {
        var postid = $(this).attr('closeid');
        $('.edit_preview_main' + postid).hide();
        $('#edit_preview_main' + postid).hide();
        $('input.edit_file_url').val('');
        var input = $("#edit_upload_" + postid);
        input = input.val('').clone(true);
    });

    /* Add Attachments to Edit*/
    $(".edit_upload_link").on('click', function (e) {
        e.preventDefault();
        var postid = $(this).attr('editupload');
        $("#edit_upload_" + postid + ":hidden").trigger('click');
        $("#edit_upload_" + postid).change(function () {
            var file_ext = $(this).val();
            readURL(this, file_ext, postid);
            $('.upload_file_name').html(file_ext);
        });
    });

    $('.updated_share').keypress(function (e) {
        if (e.which == 13) {
            return false;
        }
    });

    $(document).on('keypress, keyup', '.domain_name_inp', function (e) {
        var block_key = [64, 32, 44, 59];
        $(this).val($(this).val().toLowerCase());
        if (block_key.indexOf(e.which) > -1) {
            return false;
        }
    });

    $(document).on('keypress', '.invite_email_inp, input[name=email]', function (e) {
        var block_key = [32, 44, 59];
        if (block_key.indexOf(e.which) > -1) {
            return false;
        }
    });

    /* Cancel Button on Edit Post*/
    $(document).on('click', '#edit_cacel_btn', function () {
        var postid = $(this).attr('cancelid');
        var fileurl = $("#file_url_" + postid).attr('filepath');
        var editid = $("#edit_post_id_" + postid).attr('olddata');
        $("#editpost_" + postid).hide();
        $(".black-overlay").css("display", "none");
        $("#post_" + postid).show();
        $('.edit_preview_main' + postid).show();
        $('input.edit_file_url').val(fileurl); // Replacing Old filepath in upload value

        document.getElementById("edit_post_form_" + postid).reset();
        $('textarea.edit_post_area' + postid).text(editid); // Replacing new post value in edit post
        $(".post_categories_file" + postid).css("display", "none");
    });

    $(document).on('click', '#see_community', function () {
        var userid = $(this).attr('userid');
        var space_id = $(this).attr('spaceid');
        $.ajax({
            type: "GET",
            url: baseurl + '/view_community_profile?user_id=' + userid + '&space_id=' + space_id,
            success: function (response) {
                $('.community-mem-detail').modal('show').html(response);
            }
        });
    })

    $("#upload_pdf_file").change(function () {
        var urls = $(this).attr('url');

        if ($('.post-media-data').val() >= 2) {
            $("#upload_pdf_file").val('');
            alert('You can upload a maximum of 2 files to the executive summary. Please remove an existing file.');
            return false;
        }
        if ($(".upload_pdf_hidden").val() != '') {
            $("#upload_pdf_file").val('');
            alert('You can upload a maximum of 2 files to the executive summary. Please remove an existing file');
            return false;
        }
        var fileSize = document.getElementById("upload_pdf_file").files[0];
        var sizeInMb = fileSize.size / 1024;
        var sizeLimit = 1024 * 100;
        var file_ext = document.getElementById("upload_pdf_file").files[0].name;

        if (sizeInMb > sizeLimit) {
            alert("Please upload file of less than 100 mb size");
            $("#upload_pdf_file").val("");
        }
        var file_ext = document.getElementById("upload_pdf_file").files[0].name;
        if (($(".already_uploaded_file").val() == file_ext) || ($(".already_uploaded_video_file").val() == file_ext) || ($(".already_uploaded_pdf_file").val() == file_ext)) {
            $("#upload_pdf_file").val('');
            alert('You have already uploaded same file. Please choose another');
            return false;
        } else {
            $(".already_uploaded_file").val(file_ext);
        }

        readFileName(this, file_ext);
        $('#upload_video_name').html('<span>' + file_ext + '</span>').append("<a><div id='cross'><img src='" + urls + "/images/ic_highlight_remove.svg' alt=''></div></a>");
        $(this).hide();
        $('#upload_video_file').show();
        $(".upload_pdf_hidden").val('true');
        var inc_n = parseInt($('.post-media-data').val()) + parseInt(1);
        $('.post-media-data').val(inc_n);
    });

    $("#upload_video_file").change(function () {
        var urls = $(this).attr('url');
        if ($(".upload_video_hidden").val() != '') {
            $("#upload_video_file").val('');
            alert('You can upload a maximum of 2 files to the executive summary. Please remove an existing file');
            return false;
        }

        if ($('.post-media-data').val() >= 2) {
            $("#upload_video_file").val('');
            alert('You can upload a maximum of 2 files to the executive summary. Please remove an existing file.');
            return false;
        }
        var fileSize = document.getElementById("upload_video_file").files[0];
        var sizeInMb = fileSize.size / 1024;
        var sizeLimit = 1024 * 100;
        var file_ext = document.getElementById("upload_video_file").files[0].name;

        if (sizeInMb > sizeLimit) {
            alert("Please upload file of less than 100 mb size");
            $("#upload_video_file").val("");
        }
        var file_extension = document.getElementById("upload_video_file").files[0].name;
        if (($(".already_uploaded_file").val() == file_extension) || ($(".already_uploaded_video_file").val() == file_extension) || ($(".already_uploaded_pdf_file").val() == file_extension)) {
            $("#upload_video_file").val('');
            alert('You have already uploaded same file. Please choose another');
            return false;
        } else {
            $(".already_uploaded_file").val(file_extension);
        }
        readFileName2(this, file_extension);
        $('#upload_pdf_name').html('<span>' + file_extension + '</span>').append("<a><div id='cross2' style='display: inline-block;margin-left: 10px;'><img src='" + urls + "/images/ic_highlight_remove.svg' alt=''></div></a>");
        $(this).hide();
        $(".upload_video_hidden").val('true');
        var post_media_data = parseInt($('.post-media-data').val()) + 1;
        $('.post-media-data').val(post_media_data);
    });

    /********************Save who view file*************************/
    $(document).on("click", ".findmedia", function (e) {
        return; // Following code is not being used at the moment(AFAIK) and needs to be reviewed (and probably removed)
        var space_id = $('.view_file_spaceid').val();
        var user_id = "";
        var post_id = $(this).find('#view_file,#view_file1').attr('viewfile');
        $.ajax({
            async: false,
            type: "GET",
            url: baseurl + '/view_file?space_id=' + space_id + '&user_id=' + user_id + '&post_id=' + post_id,
            success: function (response) {
                view_cout = $('.viewpostsuser' + post_id).children().attr('view-count');
                total_count = parseInt(view_cout) + 1;
                if (total_count > 1) {
                    var view_text = 'views';
                } else {
                    var view_text = 'view';
                }
                $('.viewpostsuser' + post_id).find('.view_eye_content').html(total_count + ' ' + view_text);
                $('.viewpostsuser' + post_id).children().attr('view-count', total_count);
            }
        });

        ele = $(this);
        viewer_modal_id = $(this).attr('data-target');

        if (typeof (viewer_modal_id) == 'undefined') {
            return;
        }

        if ($(viewer_modal_id).find('iframe').length) { //if file is in iframe/preveiw mode
            viewer_modal_id = $(this).attr('data-target');

            clearTimeout(url_validate_var);
            $.ajax({
                type: "GET",
                async: false,
                url: baseurl + '/url_validate?doc_viewer=true&q=' + $(ele).find('input[name=url_src]').val(),
                beforeSend: function () {
                    $(viewer_modal_id).find('iframe').attr('src', '');
                    $(viewer_modal_id).find('.modal-loader').show();
                },
                success: function (response) {

                    $(viewer_modal_id).find('.modal-loader').show();
                    var url_temp = '';
                    if (response.file_ext.indexOf('pdf') >= 0 || response.file_ext.indexOf('PDF') >= 0) {
                        url_temp = baseurl + "/pdf_viewer/web/viewer.html?file=" + response.file_url;
                    } else {
                        url_temp = 'https://view.officeapps.live.com/op/embed.aspx?src=' + response.file_url + '&wdAr=1.3333333333333333';
                    }
                    $(viewer_modal_id).find('iframe').attr('src', url_temp);
                    iframe_load($(viewer_modal_id).find('iframe'), $(ele).find('input[name=url_src]').val());
                }
            });
        } else {
            $.ajax({
                type: "GET",
                async: false,
                url: baseurl + '/url_validate?q=' + $(ele).find('input[name=url_src]').val(),
                beforeSend: function () {
                    $(viewer_modal_id).find('.modal-loader').show();
                    $(ele).attr('href', '');
                },
                success: function (response) {
                    response = validateSignedUrl(response);
                    var file_url = response.cloud;
                    $(viewer_modal_id).find('.modal-loader').show();
                    $(ele).attr('href', file_url);
                    $(viewer_modal_id).find('.modal-body').find('img').attr('src', file_url);
                    $(viewer_modal_id).find('.modal-body').find('img').attr('src', file_url);
                    if ($(viewer_modal_id).find('.modal-body').find('video').length) {
                        // $(viewer_modal_id).find('.modal-body').find('video').attr('poster', file_url);
                        $(viewer_modal_id).find('.modal-body').find('source').attr('src', file_url);
                        $(viewer_modal_id).find('.modal-body').find('video').load();
                        $(viewer_modal_id).find('.modal-loader').hide();
                    }

                }
            });
        }
        return true;
    });

    /*Add Css if Ios Device*/
    if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
        $('.bkg').attr('poster', baseurl + '/images/video-poster.jpg');
    }

    $('video').on('loadstart', function (event) {
        $(this).addClass('bkg');
    });

    $('video').on('canplay', function (event) {
        if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
            $('.bkg').attr('poster', baseurl + '/images/video-poster.jpg');
        } else {
            $(this).removeClass('bkg');
            $(this).removeAttr("poster");
        }
    });

    $('video').onloadstart = function () {
        alert("Starting to load video");
    };

    $('.modal').on('hidden.bs.modal', function () {
        $('.modal-loader').show();
    });

    /* Mouseover on view/eye icon on embeded url post */
    $(document).on("mouseover", ".get_view_user_embeded", function () {
        var post_id = $(this).find('img').attr('getViewId');
        if(!!('ontouchstart' in window)){
         $('.viewpostsuser' + post_id).popover('hide');
          }
        $.ajax({
            type: "GET",
            async: false,
            url: baseurl + '/view_url_embeded/' + post_id,

            beforeSend: function () {
                $('.viewpostsuser' + post_id).attr("data-content", '<img src="' + baseurl + '/images/loading_bar1.gif">');
                $('.viewpostsuser' + post_id).popover('show');
            },
            success: function (response) {
                if (response == '') {
                    $('.viewpostsuser' + post_id).attr("data-content", '&nbsp;');
                } else {
                    $('.viewpostsuser' + post_id).attr("data-content", response);
                }
                $('.viewpostsuser' + post_id).popover('hide');

            },
            error: function (response) {
                $('.viewpostsuser' + post_id).attr("data-content", '');
                $('.viewpostsuser' + post_id).popover('show');
            },

        });
        return false;
    });

    $(document).on("mouseover", ".get_view_user", function () {
        var view_post_button = $(this);
        var post_id = $(this).find('img').attr('getViewId');
        $.ajax({
            type: "GET",
            async: false,
            url: baseurl + '/view_users_post?post_id=' + post_id,

            beforeSend: function () {
                view_post_button.attr("data-content", '<img src="' + baseurl + '/images/loading_bar1.gif">');
                view_post_button.popover('show');
            },
            success: function (response) {
                if (response == '') {
                    view_post_button.attr("data-content", '&nbsp;');
                } else {
                    view_post_button.attr("data-content", response);
                }
                view_post_button.popover('show');
            },
            error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'view user');
            }

        });
        return false;
    });

    $(document).on("click", ".get_view_user_embeded", function () {
        var post_id = $(this).find('img').attr('getViewId');
        var space_id = $(this).find('img').attr('space-id');
        if(!!('ontouchstart' in window)){
         $('.viewpostsuser' + post_id).popover('hide');
          }
        $.ajax({
            type: "GET",
            async: false,
            url: baseurl + '/view_url_embeded_users?post_id=' + post_id,

            beforeSend: function () {

            },
            success: function (response) {
                if (response == '') {

                } else {
                    $('.eye_users_popup').modal('show');
                    $('.eye_users_popup').html(response);
                    $('.viewpostsuser' + post_id).popover('hide');
                }

            },
            error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'get_view_user_embeded');
            },

        });
        return false;

    });

    $(document).on("click", ".get_view_user", function () {
        var post_id = $(this).find('img').attr('getViewId');
        var space_id = $(this).find('img').attr('space-id');
        $.ajax({
            type: "GET",
            async: false,
            url: baseurl + '/view_eye_users_pop?post_id=' + post_id + '&space_id=' + space_id,

            success: function (response) {
                if (response == '') {

                } else {
                    $('.eye_users_popup').modal('show');
                    $('.eye_users_popup').html(response);
                    $('.viewpostsuser' + post_id).popover('hide');
                }

            },
            error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'get_view_user click');
            }

        });
        return false;
    });

    $(document).on("click", "#cross", function () {
        $(".upload_pdf_hidden").val('');
        $('#already_uploaded_file').val('');
        $('#upload_pdf_file').show();
        $('#upload_video_file').hide();
        $("#upload_video_name").empty();
        $(this).hide();
        var control = $("#upload_pdf_file");
        control.replaceWith(control = control.clone(true)); //remove file from input
    });

    $(document).on("click", "#cross2", function () {
        $(".upload_video_hidden").val('');
        $('#already_uploaded_file').val('');
        $('#upload_pdf_file').hide();
        $('#upload_video_file').show();
        $("#upload_pdf_name").empty();
        $(this).hide();
        var control = $("#upload_video_file");
        control.replaceWith(control = control.clone(true)); //remove file from input
    });

    /*Delete saved summary files*/
    $(document).on("click", ".delete_summary_files", function () {
        var delete_file = $(this).attr('id');
        data = $('.delete_summary_files_inp').val();
        if (data.length)
            data = data + "," + delete_file;
        else
            data = delete_file;

        $('.delete_summary_files_inp').val(data);
        $(this).closest('.remove_executive_file').remove();
        $('.upload_doc_col').show();
        $('span.fileupload-new').show();
        $('i.fa-upload').show();
        return false;
    });

    $('#content').on('change keyup keydown paste cut', 'textarea', function () {
        $(this).height(10).height(this.scrollHeight);
    }).find('#exec_textarea').change();

    $("#comment_input_area").on('click', function () {
        make_form_post_ready(this);
        return false;
    });

    $(".remove_border").on('keyup', function () {
        $('.remove_border').removeClass('word-block-error');
    });

    $(".remove_border1").on('keyup', function () {
        $('.remove_border1').removeClass('word-block-error');
    });

    $(".post-wrap").on('touchstart', function (e) {
        var test = e.originalEvent.targetTouches[0].target.className;
        if ($('.r1').find('.dropdown-wrap').hasClass('open')) {
            $('.r1').find('.dropdown-wrap').removeClass('open');
        }

        if (test == 'checkbox blue_check_bx active') {
            if (!$('.r1').find('.dropdown-wrap').hasClass('open')) {
                $('.r1').find('.dropdown-wrap').addClass('open');
            }
        }
        if (test == 'checkbox blue_check_bx') {//|| test!='checkbox blue_check_bx_active' || test!='checkbox blue_check_bx'
            if (!$('.r1').find('.dropdown-wrap').hasClass('open')) {
                $('.r1').find('.dropdown-wrap').addClass('open');
            }
        }

        var touch_control = e.originalEvent.targetTouches[0].target.className;
        if ($('.r2').find('.dropdown-wrap').hasClass('open')) {
            $('.r2').find('.dropdown-wrap').removeClass('open');
        }

        if (touch_control == 'checkbox active') {
            if (!$('.r2').find('.dropdown-wrap').hasClass('open')) {
                $('.r2').find('.dropdown-wrap').addClass('open');
            }
        }
        if (touch_control == 'checkbox') {//|| test!='checkbox blue_check_bx_active' || test!='checkbox blue_check_bx'
            if (!$('.r2').find('.dropdown-wrap').hasClass('open')) {
                $('.r2').find('.dropdown-wrap').addClass('open');
            }

        }
    });

    /* For Ipad Add Background Dim Light*/
    $("#comment_input_area").on('touchstart', function () {
        $(".post_categories_maincontent").show();
        $('html, body').animate({
            scrollTop: $(".add_post_form .shareupdate-wrap").offset().top
        }, 2000);
        $('#comment_input_area').focus();
        $(".post_categories").show();
        $(".black-overlay").show();
        $("#tour2").addClass("highlight");
        $(".subject_class").show();
        $(".subject_text").show();
        $(".post_subject").css('padding-top', '5px');
        $(".post_subject").attr("placeholder", "Add a subject");
        var char = $('.main_post_ta').val().length;
        return false;
    });

    $(".post_subject").on('click change, focus', function () {
        autosize(document.querySelectorAll('textarea.post_subject'));
    });

    var mouse_is_inside = false;

    $('.shareupdate-wrap').hover(function () {
        mouse_is_inside = true;
    }, function () {
            mouse_is_inside = false;
    });

    $('#group_modal').hover(function () {
        mouse_is_inside = true;
    }, function () {
        mouse_is_inside = false;
    });

    var mouse_is_outside_navi = false;
    $('#a_nav').hover(function () {
        mouse_is_outside_navi = true;
    }, function () {
        mouse_is_outside_navi = false;
    });

    $('#tour2').on('mouseenter', function (argument) {
        is_mouse_inside_tour = true;
    });
    $('#tour2').on('mouseleave', function (argument) {
        is_mouse_inside_tour = false;
    });

    $("body").mouseup(function () {
        if (!mouse_is_inside && $('.shareupdate-wrap').length > 1) {
            dimlight();
            if ((!mouse_is_outside_navi) && $('#bs-example-navbar-collapse-2').hasClass('in')) {
                $("#bs-example-navbar-collapse-2").removeClass("in");
            }
        }
    });

    var mouse_is_outside = false;
    $('#user_profile').hover(function () {
        mouse_is_outside = true;
    }, function () {
        mouse_is_outside = false;
    });

    $("body").mouseup(function () {
        if (!mouse_is_outside) {
            $('.profile_update_form').trigger("reset");
        }
    });

    $('.profile_popup').on('click', function () {
        $leng = $('.jobtitle_admin').val().length;
        if ($leng > 0){
            $('.jobtitletxt').html("");
        }
    });

    $(document).on("click", "#discard", function () {
        $("#tour2").removeClass("highlight");
        $(".add_post_form .share-inner-wrap").addClass("post-sbj");
        $('#save_post_btn_new').hide();
        //hide multishar post
        $('.category_drop_share').hide();
        $('.category_drop').show();

        $('.visibilty-drop-wrap-share').hide();
        $('.visibilty-drop-wrap').show();

        $('.alert-drop-wrap-share').hide();
        $('.alert-drop-wrap').show();
        $('.share-drop-wrap').hide();
        $('.post_share').parent().removeClass('share-box');
        //hide multishar post
        $(".black-overlay").hide();
        $(".add_post_form")[0].reset();
        $('#catg_id').multiselect('refresh');
        $('#visibility_drop').multiselect('refresh');
        $('#alert_drop').multiselect('refresh');
        $(".post_categories_maincontent").hide();
        $(".post_categories").hide();
        $(".subject_class").hide();
        $(".subject_text").hide();
        $(".error-msg").hide();

        if (get_url_xhr != null) {
            get_url_xhr.abort();
            get_url_xhr = null;
        }
        $('.url_embed_div').empty();
        $('input[name="uploaded_file_aws"]').val('');
        uploaded_file_aws = new Array();
        filesUploaded = [];
        reset_addpost_upload_section();
    });

    $('.black-overlay').on('touchstart', function () {
        dimlight();
    });

    /*EDIT VISIBILTY POPU START*/
    $(document).on("click", ".checkbox1", function () {
        var toggle_all_id = $(this).attr('visibiliity-toogleall-edit-id');
        $('.visibility_checkbox_popup_' + toggle_all_id).not(this).prop('checked', this.checked);
        $(".save_edit_visiblity").removeClass("disabled-grey");
        $(".save_edit_visiblity").attr('disabled', false);
        $(".cancel_visibility").removeClass("disabled-grey");
        $(".cancel_visibility").attr('disabled', false);
    });

    $(document).on("click", ".visbility_check", function () {
        var id = $(this).attr('postid');
        var numberChecked = $('input.visibility_checkbox_popup_' + id + ':checked').length
        var total_count = $('.hidden_count_visibility_' + id).val();
        if (numberChecked == total_count) {
            $('.chkb1_' + id).not(this).prop('checked', this.checked);
        } else {
            $('.chkb1_' + id).not(this).prop('checked', false);
        }
        $(".save_edit_visiblity").removeClass("disabled-grey");
        $(".save_edit_visiblity").attr('disabled', false);
        $(".cancel_visibility").removeClass("disabled-grey");
        $(".cancel_visibility").attr('disabled', false);
    });

    /*----------------EDIT VISIBILITY POPUP END-----------------*/
    $(document).on('click', '.visibility_setting', function () {
        if ($('.modal-sm').hasClass('active_pop')) {
            $('.active_pop').html('');
        }
        var postid = $(this).attr('setting-id');
        $('#visibility_setting_modal').html('');
        var spaceid = $(this).attr('space-id');
        $('#visibility_setting_modal').removeClass("add_scroll");
        $.ajax({
            type: "GET",
            url: baseurl + '/endorse_setting_popup_ajax?endorseid=' + postid + '&spaceid=' + spaceid,
            success: function (response) {

                $('#visibility_setting_modal').html(response);
                $('#visibility_setting_modal').addClass("add_scroll");
                $(".save_edit_visiblity").addClass("disabled-grey");
                $(".save_edit_visiblity").attr('disabled', true);
                $(".cancel_visibility").addClass("disabled-grey");
                $(".cancel_visibility").attr('disabled', true);
            },
            error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'visibility_setting - click');
            }
        });

    });

    $('body').on('touchstart', function (e) {
        //did not click a popover toggle or popover
        if ($(e.target).data('toggle') !== 'popover'
            && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });

    $(document).on('click', '.exe_pencil', function () {
        autosize(document.querySelectorAll('textarea.summary_box'));
        $('[data-toggle="popover"]').popover();
        var upload_preview_wrap = $('.upload-preview-wrap');
        var upload_preview_wrap_length = upload_preview_wrap.find('.remove_executive_file').length;
        var upload_preview_s3_wrap_length = upload_preview_wrap.find('.remove_executive_s3_file').length;
        if ((upload_preview_wrap_length + upload_preview_s3_wrap_length) >= 2) 
            $('span.fileupload-new,i.fa-upload').show();
    });



    /*------------EDIT VISITLITY UPDATE FUNCTION END------------------*/

   $(document).on('click', '.post_emb_link', function () {
        parent_div = $(this).parent().closest('.post-wrap');
        post_id = parent_div.attr('id');
        post_id = post_id.replace('post_', '');
        p_id = $(this).parent().parent().attr('post-id');
        $.ajax({
            type: "GET",
            url: baseurl + '/log_post_file_evnt?content_id=' + post_id + '&url=' + $(this).attr('href'),
            success: function (response) {

                view_cout = $('.viewpostsuser' + p_id).children().attr('view-count');
                total_count = parseInt(view_cout) + 1;
                if (total_count > 1) {
                    var view_text = 'views';
                } else {
                    var view_text = 'view';
                }
                $('.viewpostsuser' + p_id).find('.view_eye_content').html(total_count + ' ' + view_text);
                $('.viewpostsuser' + p_id).children().attr('view-count', total_count);
            }
        });
    });


    $(document).on('click', '#temp_id_trigger', function () {
        var char = $('#biotextarea').val().length;
        if (char <= 121) {
            $('#biotextarea').attr('rows', '2');
        } else if (char > 121 && char <= 240) {
            $('#biotextarea').attr('rows', '4');
        } else {
            $('#biotextarea').attr('rows', '5');
        }

        var job_title = $('#jobtitletxt').val().length;

        if (job_title <= 121) {
            $('#jobtitletxt').attr('rows', '2');
        } else if (job_title > 121 && job_title <= 240) {
            $('#jobtitletxt').attr('rows', '4');
        } else {
            $('#jobtitletxt').attr('rows', '5');
        }
    });

    $(document).on('click', '.visibility_setting_more', function () {
        var postid = $(this).attr('setting-id');
        $('#visiblepopup' + postid).html('');
        var spaceid = $(this).attr('space-id');
        $.ajax({
            type: "GET",
            url: baseurl + '/visibility_popupmore_ajax?endorseid=' + postid + '&spaceid=' + spaceid,
            success: function (response) {

                $('#visiblepopup' + postid).html(response);
            },
            error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'visibility_setting_more - click');
            }
        });
    });

    $(document).on('click', '.add_category', function (ev) {
        var d = new Date();
        $(".categories .category_edit_list").append('<li class=""><input name="category_' + d.getTime() + '" class="form-control box category_value" type="text" maxlength="25" placeholder="Start typing..." value=""><span class="cat-del-icon" onclick="$(this).parent().remove();" aria-hidden="true"></span><span class="letter-count count_cat"></span></li>');
    });

    $(document).on('focus', '.category_value', function () {
        $(this).siblings('.count_cat').removeClass('hidden');
    });

    $(document).on('blur', '.category_value', function () {
        $(this).siblings('.count_cat').addClass('hidden');
    });

    $(document).on('keyup paste', '.category_value', function () {
        $(this).removeClass('error, has-error');
        $(this).siblings('.category_error').html('');
        $(this).parent().find('p').remove();
        $(this).parent().find('span.cat-del-icon').hide();
        $(this).parent().find('span.count_cat').html('<span>'+$(this).val().length + '</span>/25');

        if ($(this).val().length == 0) {
            $(this).parent().find('span.count_cat').hide();
            $(this).parent().find('span.cat-del-icon').show();
        } else {
            $(this).parent().find('span.count_cat').show();
            $(this).parent().find('span.cat-del-icon').hide();
        }
    });

    $(document).on('click', '#edit_post_btn_new', function (ev) {
        ev.preventDefault();
        var error = '';
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var subject = $('.subject_validate:visible').val();
        var body = $('.main_post_ta2:visible').val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: baseurl + '/matchwordsubject',
            data: {
                subject: subject,
                body: body,
                _token: CSRF_TOKEN
            },
            dataType: 'json',
            async: false,
            success: function (response) {
                if (response != '') {
                    if (response.subject) {
                        var block_word_subject = response.subject.toString().replace(/\,/g, '", "');
                        if (block_word_subject != '') {
                            $('.subject_validate:visible').addClass('word-block-error');
                            $('.subject_validate:visible').parent().find('.error-msg').remove();
                            $('.subject_validate:visible').after('<span class="error-msg error-body text-left" style="text-align: left;">This post contains the following blocked word(s): "' + block_word_subject + '"</br>Please remove any blocked words before adding your post</span>');
                            error = 1;
                        }
                    }
                    if (response.body1) {
                        var block_word_body = response.body1.toString().replace(/\,/g, '", "');
                        if (block_word_body != '') {
                            $('.main_post_ta2:visible').addClass('word-block-error');
                            $('.main_post_ta2:visible').parent().find('.error-msg').remove();
                            $('.main_post_ta2:visible').after('<span class="error-msg error-body text-left" style="text-align: left;">This post contains the following blocked word(s): "' + block_word_body + '"</br>Please remove any blocked words before adding your post</span>');
                            error = 1;
                        }
                    }
                } else {
                    if (!$('.subject_validate:visible').val() || !$.trim($('.subject_validate:visible').val()).length > 0) {
                        $('.subject_validate:visible').parent().find('.error-msg').remove();
                        $('.subject_validate:visible').after('<span class="error-msg error-body text-left" style="text-align: left;">Subject is mandatory</span>');
                        error = 1;
                    }
                    if (!$('.main_post_ta2:visible').val() || !$.trim($('.main_post_ta2:visible').val()).length > 0) {
                        $('.main_post_ta2:visible').parent().find('.error-msg').remove();
                        $('.main_post_ta2:visible').after('<span class="error-msg error-body text-left" style="text-align: left;">Body is mandatory</span>');
                        error = 1;
                    }
                }
            },
            error: function (error) {
                ev.preventDefault();
            }
        });
        if (error == '') {
            $(this).closest('form').submit();
        } else {
            ev.preventDefault();
        }
        if (error) {
            return false;
        } else {
            $('input[id=upload]').remove();
            $('.edit_post_form').find('.form-submit-loader').show();
            return true;
        }
    });

    $(document).on('click', '.remove_all_trigger', function (e) {
        $('.post_categories_file').hide();
        $('.post_categories_file_temp').remove();
        $('.post_categories_file_edt').hide();
        $('.post_categories_file_temp_edit').remove();
        $('.post_file').each(function () {
            if ($(this).attr('id') == 'upload')
                $(this).hide();
            else
                $(this).remove();
        });
        $('.post_file_edtt').each(function () {
            if ($(this).attr('id') == 'upload_edtt')
                $(this).hide();
            else
                $(this).remove();
        });

        del_old_files = "";
        $('.edit_file_del').each(function (index) {
            $(this).trigger('click');

        });

        $('.remove-all').hide();
    });

    $(document).on('click', '.editpost_data', function (e) {
        edit_popup_skull_clone = $('.edit_popup_skull').find('form').clone();
        edit_popup_skull_clone.addClass('edit_popup_skull_clone');

        var postid = $(this).attr('editpost');
        $('#post_edit_' + postid).html(edit_popup_skull_clone);
        get_edited_post_data(postid);
        $('#post_' + postid).hide();
        $(".black-overlay").show();
        $('.edit_post_form').find('.subject_text').show();
        $('.edit_post_form').find('.subject_class').show();
        $('.edit_post_form').find('.post_categories_maincontent').show();
        e.preventDefault();
    });

    $(document).on('click', '.cat_id_edtt', function () {
        $('.category_heading').text($(this).attr('catgoryname'));
        $('.editcategory').val($(this).val());
    });

    $(document).on('click', '#edit_post_category_form .category-field-delete .delete', function(){
        var category_input = $(this).closest('li').find('input.category_value');
        var confirm_modal_id = $(this).attr('data-target');
        $(confirm_modal_id).find('p.confirm-text').find('span:first').html($(category_input).val());
        $(this).closest('form').find('input[name="delete_category"]').val($(category_input).attr('name'));
    });

    $(document).on('click', '#category_delete_popup button.cancel', function(){
        $('#edit_post_category_form').find('input[name="delete_category"]').val('');
        $('#category_delete_popup').on('hidden.bs.modal', function (){ 
            $(document).find('body.feed').addClass('modal-open');
        });
        $('#modal_category').modal('show');
    });

    $(document).on('click', '#category_delete_popup button.delete', function(){
        $(this).closest('.category-delete-popup').modal('hide');
        deleteCategory();
    });

    $('#category_delete_popup').on('shown.bs.modal', function (){ 
        $('#modal_category').modal('hide');
        $(document).find('body.feed').addClass('modal-open');
    });

    $(document).on('change', '#upload_edtt', function () {
        var allow_extention = constants['POST_EXTENSION'];
        var file_ext = document.getElementById("upload_edtt").files[0].name;
        file_ext = file_ext.split('.');
        ext = file_ext.pop();

        ext = ext.toLowerCase();
        file_ext = file_ext.join('.');
        if (allow_extention.indexOf(ext) < 0) {
            alert("Only " + allow_extention.toString() + " extensions are allowed");
            return false;
        }
        var thisid = $(this).parent().parent().parent().parent().attr('id');
        if ($('#' + thisid + ' .post_file_edtt').length == 1) { // preview file if single
            file_ext = document.getElementById("upload_edtt").files[0].name;
            var id = '';
            $("#blah_edit").hide();
            readURL_edtt(this, file_ext, id);
            file_ext = file_ext.split('.');
            ext = file_ext.pop();
            file_ext = file_ext.join('.');
            $('.upload_file_name_edtt').html(file_ext.toString());
            $('#' + thisid + ' .post_categories_file_edt').show();
        } else { // list preview of files before upload
            preview_before_upload_edita(this, thisid);
        }

        var clone = $(this).clone();
        $(this).attr('id', 'post_file_d_' + ($('#' + thisid + ' .post_file_edtt').length - 1));
        $(this).after(clone);
        $('.remove-all').show();
    });

    $(document).on({
        mouseenter: function () {
            is_mouse_inside_tour = true;
        },
        mouseleave: function () {
            is_mouse_inside_tour = false;
        }
    }, '.single-cmt-wrap, .edit-comment, .save_cmt');

    $(document).on('click', '.editpost_data', function () {
        $('.navbar-header .dropdown-toggle,.navbar-header .nav-btn, .navbar-nav .nav-btn').css('pointer-events', 'none');
    });

    $(document).on('click', '.edit_popup_skull_clone button.post_btn', function () {
        $('.navbar-header .dropdown-toggle,.navbar-header .nav-btn, .navbar-nav .nav-btn').css('pointer-events', 'unset');
    });

    $(document).on('click', '.minimize-post', function () {
        var tag = $(this);
        var postid = $(tag).attr('attr-id');

        var userid = $(tag).attr('attr-uid');
        $.ajax({
            type: "GET",
            dataType: "html",
            url: baseurl + '/expandpost?postid=' + postid + '&userid=' + userid + '&type=true',
            success: function (response) {
                var post_selector = $('#post_' + postid+' .post-feed-section').closest('.post-wrap');
                post_selector.addClass('minimize');
                post_selector.find(tag).removeClass('minimize-post');
                post_selector.find(tag).addClass('m-collapse');
                post_selector.find('.post-description .full_description').hide();
                post_selector.find('.post-description .trim_description').hide();
                post_selector.find('.expand_view_content').hide();
                post_selector.find(tag).html('<span class="dropdown-post-icon"><img src="'+baseurl+'/images/ic_unfold.svg"></span>' + 'Expand post');
            },
            error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'minimize-post - click');
            }
        });
    });

    $(document).on('click', '.minimize-collapse,.m-collapse', function () {
        var tag = $(this);
        var postid = $(tag).attr('attr-id');
        var userid = $(tag).attr('attr-uid');
        $.ajax({
            type: "GET",
            dataType: "html",
            url: baseurl + '/expandpost?postid=' + postid + '&userid=' + userid + '&type=false',
            success: function (response) {
                expandPost(postid);
            },
            error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'post minimize - click');
            }
        });
    });

    /******* Check buyer or seller selected *********/
    $(document).on('change', '.company_admin', function () {
        var check_sub_comp_status = $('.buyer_info_hidden').attr('sub-comp-active');
        if (check_sub_comp_status == 1) {
            var buyer_company_hidden_id = $('.buyer_info_hidden').attr('buyer-id');
            var buyer_company_hidden_name = $('.buyer_info_hidden').val();
            var buyer_company_id = $(this).find(":selected").val();
            var buyer_company = $(this).find(":selected").text();
            if (buyer_company_hidden_id == buyer_company_id && buyer_company_hidden_name == buyer_company) {
                //$('.comp_lab').html('Community <span class="required-star">&nbsp; *</span>');
                $('.sub_comp_div').css("display", "block");
                $('.sub_comp_input').attr('name', 'sub_comp');
                $('.sub_comp_input').addClass('c_side_validation');

            } else {
                //$('.comp_lab').html('Company <span class="required-star">&nbsp; *</span>');
                $('.sub_comp_input').removeAttr('name');
                $('.sub_comp_div').css("display", "none");
                $('.sub_comp_input').removeClass('c_side_validation');

            }
        }
    });

    $('.company_admin').trigger("change");

    $(document).on('keyup', '.sub_comp_input', function () {
        var sub_comp = $(this).val();
        $(".sub_comp_input .client_side_validation_msg").hide();
        var space_id = $('.hidden_sp_id').val();
        $.ajax({
            type: "GET",
            dataType: "html",
            url: baseurl + '/search_sub_comp?comp=' + sub_comp.trim() + '&space_id=' + space_id,
            success: function (data) {
                $("#suggesstion-box").show();
                $("#suggesstion-box").html(data);

                $(".sub_comp_add_list").html("Add ' " + sub_comp + " '").css("color", '#0D47A1');
                $(".sub_comp_input").css("background", "#FFF");
                var sub_comp_len = $(".sub_comp_hid_input").length;
                if (sub_comp_len == '1') {
                    $(".sub_comp_add_list").hide();
                }
            }
        });
    });

    $(document).on('click', '.sub_comp_add_list', function () {
        $("#suggesstion-box").hide();
    });

    $('.visible_search_in').keyup(function () {
        var valThis = $(this).val().toLowerCase();
        $('.visibilty-drop li label').each(function () {
            var text = $(this).text().toLowerCase();
            var match = text.indexOf(valThis);
            if (match >= 0) {
                $(this).show();
            } else {
                $(this).hide();
                $(this).parent().css('border-bottom', 'none');
            }
        });
    });

    $('.visible_search_in_groups').keyup(function () {
        var valThis = $(this).val().toLowerCase();
        $('.label-wrap label').each(function () {
            var text = $(this).text().toLowerCase();
            var match = text.indexOf(valThis);
            if (match >= 0) {
                $(this).show();
                $(this).parent().css('border-bottom', '1px solid #e0e0e0 ');
            } else {
                $(this).hide();
                $(this).parent().css('border-bottom', 'none');
            }
        });
    });

    $('.visible_search_in_groups_edit').keyup(function () {
        var valThis = $(this).val().toLowerCase();
        $('.label-wrap label').each(function () {
            var text = $(this).text().toLowerCase();
            var match = text.indexOf(valThis);
            if (match >= 0) {
                $(this).show();
                $(this).parent().css('border-bottom', '1px solid #e0e0e0 ');
            } else {
                $(this).hide();
                $(this).parent().css('border-bottom', 'none');
            }
        });
    });

    // Assigned to variable for later use.
    var form = $('.direct-upload');
    // Place any uploads within the descending folders
    // so ['test1', 'test2'] would become /test1/test2/filename
    var folders = ['post_file'];

    form.fileupload({
        url: form.attr('action'),
        type: form.attr('method'),
        datatype: 'xml',
        add: function (event, data) {

            // Show warning message if your leaving the page during an upload.

            // Give the file which is being uploaded it's current content-type (It doesn't retain it otherwise)
            // and give it a unique name (so it won't overwrite anything already on s3).
            var file = data.files[0]; 
            if(constants['POST_EXTENSION'].indexOf(file.name.split('.').pop().toLowerCase()) < 0) {
              alert("Wrong extension type. Please upload " + constants['POST_EXTENSION'].toString() + " Files");
              upload_feed_file_error();
              return 0;
            }
            window.onbeforeunload = function () {
                return 'You have unsaved changes.';
            };
            $('.post_btn').attr('disabled', true);
            $('.close_trigger').css('pointer-events','none');
            uid = data.files[0]['uid'] = Date.now() + '_' + 'uid';
            var filename = Date.now() + '.' + file.name.split('.').pop();
            form.find('input[name="Content-Type"]').val(file.type);
            form.find('input[name="key"]').val((folders.length ? folders.join('/') + '/' : '') + filename);

            // Actually submit to form to S3.
            s3_upload_xhr[uid] = data.submit();

            // Show the progress bar
            // Uses the file size as a unique identifier
            var file_extension = file.name.split('.').pop();
            uploaded_file_aws_unik = uid;
            var clone = $('.post_attachment_skull').clone();
            clone.removeClass('post_attachment_skull');
            clone.addClass('post_attachment');
            if(file_extension.toLowerCase() == 'mov'){
                clone.addClass('post_attachment_mov');
                clone.attr('data-toggle','popover');
                clone.attr('data-trigger','hover');
                clone.attr('data-placement','bottom');
                clone.find('.close').after('<span class="alert"><i class="fa fa-question-circle"></i></span>');
                clone.attr('data-content','This file type is being converted and may take a few seconds to appear.');
            }
            clone.find('.progress-bar-striped').addClass(uploaded_file_aws_unik);
            file_name = file.name.split('.');
            file_name.pop();
            clone.find('.upload_file_name').html(file_name.join('.'));
            clone.find('.close_trigger').attr('id', uploaded_file_aws_unik);
            clone.find('img').eq(1).attr('src', extension_wise_img(file.name.split('.').pop()));

            clone.show();
            $('.upload-content').eq($('.upload-content').length - 2).after(clone);


        },
        progress: function (e, data) {
            uid = data.files[0].uid;
            var percent = Math.round((data.loaded / data.total) * 100);
            $('.' + uid).width(percent + '%');
            $('.' + uid).html(percent + '%');
            if (percent == 100)
                $('.' + uid).parent().hide();
        },
        fail: function (e, data) {
            attachment_element = $('.'+uid).parent().closest('.post_attachment');
            attachment_element.addClass('attachment-error');
            attachment_element.find('.progress').hide();
            attachment_element.find('.upload_file_name').html(attachment_element.find('.upload_file_name').html()+'<strong style="color:red"> - file upload failed</strong>');
            window.onbeforeunload = null;
            $('.progress[data-mod="' + data.files[0].size + '"] .bar').css('width', '100%').addClass('red').html('');
            if ($('.direct-upload').fileupload('progress').loaded == $('.direct-upload').fileupload('progress').total) {
                $('.post_btn').attr('disabled', false);
            }
        },
        done: function (event, data) {
            window.onbeforeunload = null;
            var original = data.files[0];
            var s3Result = xmlToJson(data.result.documentElement);
            var s3_file_name = s3Result.Key;
            var extension = s3_file_name.split(".")[1];
            var mime_type = original.type;
            var file_url = s3Result.Location;
            if(extension.toLowerCase() == 'mov'){
                convertVideo(s3_file_name);
                mime_type = 'video/mp4';
                file_url = file_url.replace(extension, "mp4");
                $('.post_attachment_mov').popover({trigger: 'hover'});
            }
            filesUploaded.push({
                "originalName": original.name,
                "s3_name": s3_file_name,
                "size": original.size,
                "url": file_url,
                'mimeType': mime_type
            });
            $('.close_trigger').css('pointer-events','auto');
            $('#uploaded').html(JSON.stringify(filesUploaded, null, 2));
            uploaded_file_aws.push(JSON.stringify(filesUploaded, null, 2));
            $('input[name="uploaded_file_aws"]').val(JSON.stringify(filesUploaded, null, 2));
            preview_before_upload_aws();
            if ($('.direct-upload').fileupload('progress').loaded == $('.direct-upload').fileupload('progress').total) {
                $('.post_btn').attr('disabled', false);
            }
        }
    });

    var is_ios = /(iPhone)/g.test( navigator.userAgent );

    if(is_ios===true){
        $(".pro_info_member textarea#jobtitletxt").addClass("job-title-text");
        $(".pro_info_member textarea#biotextarea").addClass("job-title-text");
    }

    $(document).on("click", ".top_post_seller", function () {
        var company = $(this).text();
        var url = window.location.href;
        var id = url.substring(url.lastIndexOf('/') + 1);
        $.ajax({
            type: "GET",
            async: false,
            url: baseurl + '/gettopthreepost?month=' + $("#curnt-month").attr("monnum") + '&year=' + $("#curnt-month").attr("yearnum") + '&company=' + company+'&space_id='+id,
            success: function (response) {
                $('.top-post-ajax-div').html(response);
                $('.t_post').removeClass('active');
                $('.top_post_seller').addClass('active');
            }
        });
    });

    $(document).on("click", ".top_post_all", function () {
        $('.t_post').removeClass('active');
        var url = window.location.href;
        var id = url.substring(url.lastIndexOf('/') + 1);
        $.ajax({
            type: "GET",
            async: false,
            url: baseurl + '/gettopthreepost?month=' + $("#curnt-month").attr("monnum") + '&year=' + $("#curnt-month").attr("yearnum")+'&space_id='+id,
            success: function (response) {
                $('.t_post').removeClass('active');
                $('.top_post_all').addClass('active');
                $('.top-post-ajax-div').html(response);
            }
        });
    });

    $(document).on("click", ".top_post_buyer", function () {
        var company = $(this).text();
        var url = window.location.href;
        var id = url.substring(url.lastIndexOf('/') + 1);
        $.ajax({
            type: "GET",
            async: false,
            url: baseurl + '/gettopthreepost?month=' + $("#curnt-month").attr("monnum") + '&year=' + $("#curnt-month").attr("yearnum") + '&company=' + company+'&space_id='+id,
            success: function (response) {
                $('.top-post-ajax-div').html(response);
                $('.t_post').removeClass('active');
                $('.top_post_buyer').addClass('active');
            }
        });
    });

    /* Remove upload file on s3 from executive list */
    $(document).on('click', '.remove_executive_s3_file', function () {
        var uid = $(this).attr('id');
        var temp_arr = new Array();

        $.each(s3_executive, function (key) {
            if (this.uid != uid){
                temp_arr[key] = s3_executive[key];
            }
        });
        $('input.deleted_executive_aws_files_data').val(JSON.stringify(s3_executive, null, 2));
        delete s3_executive;
        s3_executive = temp_arr;
        $('input.executive_aws_files_data').val(JSON.stringify(s3_executive, null, 2));
        $(this).closest('.executive_file_s3').remove();
        $('span.fileupload-new').show();
        $('i.fa-upload').show();
        $('.upload_doc_col').show();
    });

    $(document).on('click', '.summary_cancel', function () {
        var process = Object.keys(s3_upload_xhr)
        $.each(process, function (key) {
            s3_upload_xhr[process[key]].abort();
            $('#' + process[key]).remove();
            location.reload();
        });

    });

    /* Executive Summary iframe reload/refresh */
    $(document).on('click', '.pdf_list_file', function (event) {
        if ($($(this).find('a').attr('data-target')).find('iframe').length) {
            $($(this).find('a').attr('data-target')).find('.modal-loader').show();
            $($(this).find('a').attr('data-target')).find('iframe').attr('src', $($(this).find('a').attr('data-target')).find('iframe').attr('src'));
            iframe_load($($(this).find('a').attr('data-target')).find('iframe'), '');
        } else {
            $('.modal-loader').hide();
        }
    });

    $(document).on('click', 'i.fa-expand, i.fa-compress', function (event) {
        if ($(this).parent().closest('.modal').find('iframe').length) {
            $($(this).parent().closest('.modal')).find('.modal-loader').show();
            $(this).parent().closest('.modal').find('iframe').attr('src', $(this).parent().closest('.modal').find('iframe').attr('src'));
            iframe_load($(this).parent().closest('.modal').find('iframe'), '');
        } else {
            $('.modal-loader').hide();
        }
    });

    $(".edit_share_name").on('click', function(e) {
        $(".share_name").hide();
        $(".edit_share").show();
        $(".updated_share").focusEnd();
    });
    $(".edit_user_name").on('click', function(e) {
        $(this).hide();
        $(".user_first_last_name").hide();
        $(".edit_user").show();
    });
    $(".cancel_edit_share").on('click', function(e) {
        $(".share_name").show();
        $(".edit_share").hide();
    });
    $(".cancel_user_name").on('click', function(e) {
        $(".user_first_last_name").show();
        $(".edit_user").hide();
        var first_prev_name = $("#first_prev_name").val();
        var last_prev_name = $("#last_prev_name").val();
        $("#first_name").val(first_prev_name);
        $("#last_name").val(last_prev_name);
        $(".first_name_text_error").css("display", "none");
        $(".last_name_text_error").css("display", "none");
        $(".edit_user_name").show();
    });

    $(document).on('keyup', '#first_name', function() {
        var char = $('#first_name').val().length;
        if( char > 0){
            $(".first_name_text_error").css("display", "none");
        }
    });
    $(document).on('keyup', '#last_name', function() {
        var char = $('#last_name').val().length;
        if( char > 0){
            $(".last_name_text_error").css("display", "none");
        }
    });
   $(document).on('change', '#landing_company', function() {
                if($(this).val().length){
                    $(".company_admin .client_side_validation_msg").hide();
                }
   });
   $(document).on('keyup', '.subject_validate', function() {
        var char = $('.subject_validate').val().length;
        if( char > 0){
           $(this).next("span").remove();
        }
    });

    if(extractUrlParam(window.location.href, 'action') == 'reply'){
        $('.comment-area').focus().trigger('click');
    }

});

function convertVideo(name){
     $.ajax({
        type: 'GET',
        dataType: 'JSON',
        url: baseurl+'/convert_mov_video?image_name='+name,
        success: function( response ) {
            if(response.result){

            }
        }
    });
}
$('.community-unlocked-column, .community-locked-column').addClass('hidden');
var category_flag = false;
var tour_guide = {
    'executive': {
            element: ".executive_col_tile",
            title: "Add an Executive Summary to the Client Share.",
            content: "Use this to share key information about your relationship. Consider uploading a welcome video of an account summary document to add detail to the Executive Summary.",
            backdrop: true,
            backdropPadding: 0,
            placement: "bottom",
            template: "<div class='popover tour'><div class='arrow'></div> <h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='tourlink' data-role='next'>GOT IT</button></div></div>"
        },
    'category': {
            element: "#tour4",
            title: "Categories",
            content: "All posts that are added to the Client Share are given a category to help organise them. Members of the Client Share can choose to view posts according to their category.",
            backdrop: true,
            backdropPadding: 0,
            placement: "bottom",
            template: "<div class='popover tour'><div class='arrow'></div> <h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='tourlink' data-role='next'>GOT IT</button></div></div>"
        },
    'post': {
            element: "#tour2",
            content: "Post something to the Client Share so members can see your content when they join. You can add text, links, documents or media. When members join the Client Share, anybody can restrict who can see their posts and set an alert to tell other members that content has been added.",
            backdrop: true,
            backdropPadding: 0,
            placement: "bottom",
            template: "<div class='popover tour'><div class='arrow'></div> <h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='tourlink' data-role='next'>GOT IT</button></div></div>"
        }, 
    'invite': {
            element: "#tour3",
            title: "Invite Members",
            content: "When you are ready, you can invite members to join you on the Client Share.",
            backdrop: true,
            backdropPadding: 0,
            placement: "bottom",
            template: "<div class='popover tour'><div class='arrow'></div> <h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='tourlink endtour' data-role='end'>FINISH TOUR</button></div></div>"
        }
};

var user_wise_tour = {
    'user': [
        'category',
        'post',
        'invite'
    ],
    'admin': [
        'executive',
        'category',
        'post',
        'invite',
    ]
};

function runTour(){
    var tour_steps = Array();
    
    $.each(user_wise_tour[logged_in_user_role], function(){
        tour_steps.push(tour_guide[this]);
    });

    $(function () {
        var wid = $(window).width();
        var tour = new Tour({
            storage: false,
            onShown: function (tour) {
                $('.categories-wrap').attr('style', 'background: #fff none repeat scroll 0 0;border-radius: 6px;padding: 20px;');
                $('.tour-backdrop').after('<div class="sss tour-backdrop" style="background: transparent none repeat scroll 0% 0%; z-index: 1102;"></div>');                
            },
            onHidden: function (tour) {
                $('.categories-wrap').attr('style', '');
                $("div.sss").remove();
            }
        });

        tour.addSteps(tour_steps);

        if (!(navigator.userAgent.toLowerCase().indexOf('mobile') >= 0)) {
            tour.init(); // Initialize the tour        
            tour.start(); // Start the tour
        }
    });
}

var triggerPendingOnboardingSteps = once(function() {
    if($('.wc-step-uncomplete').length){
        $('.wc-step-uncomplete').eq(0).parent().find('p').trigger('click');

    }
});

function validateCategoryData(current_step) {
    $(current_step).find('input.category_value').each(function(){
        if(!$(this).val().trim().length){
            $(this).addClass('has-error');
            $(this).siblings('.category_error').html('This field is required');
        } else {
            $(this).removeClass('has-error');
            $(this).siblings('.category_error').html('');
        }
    });
}

function saveCategoryRequest(categories) {
    var data;
    var category_list = [];
    $('.edit-categories .tour-category-list').each(function(){
        var category_value = $(this).find('.category_value').val();
        category_list.push(category_value);
    });
    var category_list = category_list.sort(); 

    var category_list_duplicate = [];
    for (var i = 0; i < category_list.length - 1; i++) {
        if (category_list[i + 1] == category_list[i]) {
            category_list_duplicate.push(category_list[i]);
        }
    }
    if(category_list_duplicate.length > 0)
    {
        $('.category_error_duplicacy').fadeIn( 400 ).html('Duplicate category exist.').delay(3000).fadeOut( 400 );
        return false;
    }

    data = {
        'space_id': session_space_id,
        'categories': categories
    };

    $.ajax({
        type: 'POST',
        data: data,
        url: baseurl+'/save_categories',
        beforeSend: function(){ $('#welcome_tour .form-submit-loader').removeClass('hidden')},
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        error: function(xhr, status, error) { errorOnPage(xhr, status, error); },
        complete: function(){ 
            addProgressBarsection();
            $('#welcome_tour .form-submit-loader').addClass('hidden');
            $('.category_step').delay(10000).removeClass('wc-step-uncomplete');
            category_flag = true;
        }
    });
    
    return true;
}

function saveCategory(current_step) {
    if(!current_step.find('.edit-categories').length)
        return true;

    validateCategoryData(current_step);
    if($(current_step).find('.has-error').length)
        return false;

    var categories = $('form.category_form').serialize();
    return saveCategoryRequest(categories);
}

function checkCategory(current_step) {
    if(!current_step.find('.edit-categories').length)
        return true;

    $(current_step).find('input.category_value').each(function(){
        if(!$(this).val().trim().length){
            $(this).addClass('has-error');
            $(this).siblings('.category_error').html('This field is required');
        } else {
            $(this).removeClass('has-error');
            $(this).siblings('.category_error').html('');
        }
    });
    return $(current_step).find('.has-error').length?false:true;
}

function addTwitterFeedInput(){
    var add_feed_btn = $('#onboarding_twitter_handles .add-handles');
    var input_count = add_feed_btn.closest('div.twitter-handle-wrap').find('div.twitter-handle').length;
    if(input_count >=3){
        $(add_feed_btn).hide(); 
        return false;
    }    
    var cloned_div = add_feed_btn.closest('.twitter-handle-wrap').find('.twitter-handle:last').clone();
    var twitter_handle_input = $(cloned_div).find('input[name="twitter_handles[]"]');
    $(twitter_handle_input).val('');
    $(cloned_div).find('p.error').remove();
    $(twitter_handle_input).attr('id', 'twitter_handle_' + (input_count));
    $(cloned_div).find('span.link-input-icon p').html((input_count + 1));
    $('#onboarding_twitter_handles .twitter-handle-wrap-column').append(cloned_div);
    var new_input_count = add_feed_btn.closest('div.twitter-handle-wrap').find('div.twitter-handle').length;
    if(new_input_count >=3){
       add_feed_btn.hide(); 
    }
}

function toggleAddTwitterFeedButton(){
    var add_feed_btn = $('a.add-twitter-feed.add-handles');
    var twitter_handle_input = $('.welcome_twitter_feed_modal .modal-body .twitter-handle-wrap').find('input.twitter-feed-input');
    if(twitter_handle_input.length >= 3)
        $(add_feed_btn).hide();
    else
        $(add_feed_btn).show();
}

function updateTourStep(step){
    if(!step)
        return false;

    $.ajax({
        type: 'GET',
        url: baseurl+'/update_tour_step/'+session_space_id+'/'+(parseInt(step)+1),
        error: function(xhr, status, error) { errorOnPage(xhr, status, error); }
    });
}

function runOnBoardingTour(force_trigger) {
    force_trigger = force_trigger ? force_trigger : false;

    if(share_setup_steps <= 10 || force_trigger)
        $('.tour-trigger').trigger('click');
    else if(isMobileDevice() && share_setup_steps <= 10)
        runTour();
}

function saveDomainRequest(form_class, current_step) {
    var form_to_submit = $("."+form_class);
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    var settings = {
        "crossDomain": true,
        "url": baseurl+"/clientshare/"+session_space_id,
        "method": "put",
        "headers": {
            "cache-control": "no-cache",
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        "data": {
            "metadata": {
                "rule": $(form_to_submit).serializeArray()
            },
            "domain_restriction": true,
            "onboarding_domain_flag" : true,
            "_method": "put",
        }
    };

    $('#welcome_tour .form-submit-loader').removeClass('hidden');
    $.ajax(settings).done(function(response) {
        $.each(response.message, function(index, value) {
            index = index.split(".");
            input = $(form_to_submit).find('input[name="rule[]"]').eq(index[0]);
            input.parent().find('.error-msg').remove();
            input.after('<span class="error-msg text-left">' + value + '</span>');
            input.addClass("has-error");
        });
        if(response.code != 401)        
            updateNextStep(current_step);
            addProgressBarsection();
        
        $('#welcome_tour .form-submit-loader').addClass('hidden');
    });
}

function retrictDomin(current_step, check){
    $.ajax({
        type: 'POST',
        data: {
            'data':{
                'domain_restriction': check
            },
            'space_id': session_space_id
        },
        url: baseurl+'/restict_domain',
        beforeSend: function(){ $('#welcome_tour .form-submit-loader').removeClass('hidden')},
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) { 
            updateNextStep(current_step);
            addProgressBarsection();
        },
        error: function(xhr, status, error) { errorOnPage(xhr, status, error); },
        complete: function(){ $('#welcome_tour .form-submit-loader').addClass('hidden')}
    });
}

function saveDomain(current_step){
    if(!current_step.find('form.save-domain').length)
        return true;
    if(!$('.restrict-domain-check').prop("checked"))
        retrictDomin(current_step, false);
    else
        saveDomainRequest('save-domain', current_step);
}

function updateNextStep(current_step) {
    current_step.addClass('hidden');
    current_step.next($('.welcome-cs-popup')).removeClass('hidden');
    $('.banner-image-error').text('');
    $('#banner-image, #edit-banner-image').val('');
    updateTourStep(current_step.attr('data-step'));
}

function addCategory(element){
    category_layout = $(element).closest('.welcome-tour-categorie-col').find('.tour-category-list');
    category_layout_clone = category_layout.eq(2).clone();
    category_layout_clone.find('input').val('');
    category_layout_clone.find('input').attr('name', 'category_'+random());
    category_layout_clone.find('.category-count').html(0);
    category_layout_clone.addClass('tour-category-list-new-add');
    category_layout_clone.append('<span class="wc-categories-delete"><img src="'+baseurl+'/images/ic_deleteBlue.svg"></span>');
    $('.tour-category-list').eq(category_layout.length-1).after(category_layout_clone);
}

function displayPanel() {
    pending_steps = $('.wc-step-done.wc-step-uncomplete');
    
    if(!pending_steps.length) 
        return false;
    
    $('.finish-setup-alert').find('span').html(pending_steps.length);
    $('.finish-setup-alert').parent().show();
    triggerPendingOnboardingSteps();
}

function addProgressBarsection(refrence){
    if(!refrence) {
        refrence = 'default';
    }
    $.ajax({
        type: 'GET',
        url: baseurl+'/get_share_profile_status?space_id='+session_space_id,
        success: function (response) {
            if(response.data.space_admin) {
                $('.btn-invite-back').remove();
            }
            if(response.data.progress == parseInt(100) || response.data.space_users) {
                $('#save_post_btn_new').text('Post');
                $('.community-locked-column').remove();
                $('.community-unlocked-column').removeClass('hidden');
                $('.user-profile-status-col, .finish-setup-alert').remove();
                return false;
            }
            if(response.result){
                var parser = new DOMParser;
                var source = $("#progress-bar-section").html();
                var template = Handlebars.compile(source);
                response.baseurl = baseurl;
                if((response.data.category && response.data.category_flag) || category_flag || share_setup_steps >= 5) {
                    response.data.category = true;
                } else {
                    response.data.category = false;
                }
                
                var html = template(response);
                if($(window).width() >= 767){ 
                    $('.user-profile-status-show').html(html);    
                } else {
                    $('.user-profile-status-show-mobile').html(html); 
                }

                if(response.data.space_admin_data.length > 0) {
                    $('.admin-invite-result-box').removeClass('hidden');
                    appendSpaceUserInInviteAdminScreen(response.data.space_admin_data);
                }
                $('.community-locked-column').removeClass('hidden');
                $('.community-unlocked-column').addClass('hidden');
                $(".cdev").circlos();
                if(refrence == 'add_post' && response.data.posts_count < 5) {
                    $('.add_post_trigger').trigger('click');
                }
                if(response.data.posts_count == 4) {
                   $('#save_post_btn_new').text('Finish');
                }
                if(response.data.posts_count == 5) {
                   $('#save_post_btn_new').text('Post');
                }
                displayPanel();
            }
        },
        error: function(xhr, status, error) { errorOnPage(xhr, status, error); }
    });
}

function showOnboardingPopup(step){
    $('div.welcome-cs-popup').addClass('hidden');
    if(step<10) {
        $('div.welcome-cs-popup[data-step="'+step+'"]').removeClass('hidden');
        runOnBoardingTour(true);
    } else {
        $('.add_post_trigger').trigger('click');
    }
}

function sendOnboardingQuickLinks(btn){ 
     var link_count = 0;
     var reference = $(btn).closest('.quick_links_form');
     var pattern = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
     var quick_links_error = reference.find('.quick_links_error');
     quick_links_error.text('');
     var hyperlink = reference.find('.hyperlink[name="hyperlink[]"]');
     var link_name = reference.find('.link_name[name="link_name[]"]');
     for (i=0; i<hyperlink.length; i++){
            if(hyperlink[i].value.trim() != '' && link_name[i].value.trim() != ''){
                   link_count++;
            }
            if(hyperlink[i].value.trim() != '' && link_name[i].value.trim() == ''){
                   quick_links_error.text('Please enter link name.');
                   return false;
            }
            if(hyperlink[i].value.trim() == '' && link_name[i].value.trim() != ''){
                   quick_links_error.text('Please add hyperlink.');
                   return false;
            }
            if(hyperlink[i].value.trim() != '' && !pattern.test(hyperlink[i].value.trim())){
                   quick_links_error.text('Please add correct hyperlink.');
                   return false;
            }
     }
     //if(link_count >= 2){ 
        var twitter_handle_last = $('#twitter_handle_2').val();
        if((typeof twitter_handle_last != 'undefined' && twitter_handle_last.length < 2) || twitter_handle_last == '')
          $('#twitter_handle_2').closest('.twitter-input-col').find('.remove-handle').trigger('click');

        reference.find('.btn-quick-links-button').attr('disabled',true);
        reference.submit(); 
     /*}else{
        quick_links_error.text('You need to add a minimum of 2 quick links to your client share.');
        return false;
     }*/
}

function validateAdminInviteEmail(ele) {
    $('.admin-invite-error').remove();
    $('.admin-invite-box .twitter-feed-input').removeClass('has-error');
    var filter = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    var email_flag = validate_flag = false;
    $('.admin-invite-box .twitter-input-col input').each(function(index, value) {
        var email = $(this).val();
        if(email.trim()) {
            email_flag = true;
        }
        if (email.trim() != '' && !filter.test(email.trim())) {
            $(this).addClass('has-error');
            $(this).parent().append('<span class="admin-invite-error">Please enter a correct email format.</span>');
            validate_flag = true;
            setTimeout(function(){
              $('.edit-admin-invite').find('.admin-invite-error').remove();
              $('.admin-invite-box .twitter-feed-input').removeClass('has-error');
            }, 4000);
        }
    });
    if(!email_flag) {
        if ($('.admin-invite-result-box').html().trim() != "") {
            current_step = $(ele).closest('.welcome-cs-popup');
            updateNextStep(current_step);
            return false;
        }
        $('.edit-admin-invite').append('<span class="admin-invite-error">Please enter at least one email address.</span>');
        setTimeout(function(){
              $('.edit-admin-invite').find('.admin-invite-error').remove();
            }, 3000);
        return false;
    }
    if(validate_flag) {
        return false;
    }
    return true;
}

function appendSpaceUserInInviteAdminScreen(admin_data) {
    var html = '';
    $.each(admin_data, function(i, item) {
        html += '<div class="onboarding-invite-status">';
        html += '<p>'+admin_data[i].user.email+'</p>';
        if(admin_data[i].metadata.invitation_code == 0) {
            html += '<p class="invite-result">Pending</p></div>'; 
        }
         if(admin_data[i].metadata.invitation_code == 1 
            && typeof admin_data[i].metadata.user_profile != 'undefined'
            && admin_data[i].metadata.user_profil != '') {
             html += '<p class="invite-result">Accepted</p></div>'; 
        }
    })
    $('.admin-invite-result-box').html(html);
    $('.admin-invite-box .twitter-input-col').not(':first').remove();
}

$(document).ready(function () {
    addProgressBarsection();
    toggleAddTwitterFeedButton();

    $('.category-count').each(function(){
        val = $(this).closest('.tour-category-list').find('.category_value').val();
        $(this).html(val.length);
    });
});

$(document).on('click', '.finish-setup-alert a', function(){
    pending_steps = $('.wc-step-done.wc-step-uncomplete');
    step = pending_steps.eq(0).attr('data-step');
    showOnboardingPopup(step);
});

$(document).on('click', '.admin-progress-icon a', function(){
    $('div.welcome-cs-popup').addClass('hidden');
    step = $(this).attr('data-step');
    if(typeof step == 'undefined' || !step) {
        return false;
    }
    showOnboardingPopup(step);
});

$(document).on('click', '.wc-step-col p', function(){
    $('div.welcome-cs-popup').addClass('hidden');
    step = $(this).parent().find('.wc-step-done').attr('data-step');
    if(typeof step == 'undefined' || !step) {
        return false;
    }
    showOnboardingPopup(step);
});

$(document).on('click', '.endtour', function () {
    if (show_feedback) $('#feedback-popup').modal('show');
    $.ajax({
        type: "GET",
        url: baseurl + '/update_showtour',
        success: function (response) {},
        error: function (xhr, status, error) {}
    });
});

$(document).on('click', '.add-admin-invite-link', function(event){ 
    var html = '<div class="twitter-input-col"><input class="form-control twitter-feed-input" name="admin_invite[]" placeholder="Email address" type="text"></div>';
    $('.admin-invite-box').append(html);
});

$(document).on('click', '.btn-invite-handle', function(event){
    current_step = $(this).closest('.welcome-cs-popup');
    var validate_data = validateAdminInviteEmail(this);
    if(!validate_data) {
        return false;
    }
    email_array = [];
    var duplicate_email_flag = true;
    $('.admin-invite-box .twitter-input-col input').each(function(index, value) {
        var email = $(this).val();
        if($.inArray(email, email_array) !== -1) {
            $(this).addClass('has-error');
            $(this).parent().append('<span class="admin-invite-error">Email already mentioned in input.</span>');
            setTimeout(function(){
                $('.edit-admin-invite').find('.admin-invite-error').remove();
                $('.admin-invite-box .twitter-feed-input').removeClass('has-error');
            }, 4000);
            duplicate_email_flag = false;
        }
        if(email.trim() != '') { 
            email_array[index] = email;
        }
    });
    if(!duplicate_email_flag) {
        return false;
    }
    var mail = [];
    mail[0] = $('.admin_invite_body').val();
    var subject = $('.admin_invite_subject').val();
    var settings = {
        "async": true,
        "crossDomain": true,
        "url": baseurl+"/invite_admin_user",
        "method": "post",
        "headers": {
            "cache-control": "no-cache",
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        "data": {
            "share_id": session_space_id,
            "admin_invite": email_array,
            "user": {
                 "first_name": '',
                 "last_name": '',
                 "user_type_id": 2,
                 'onboarding' : true,
                 "subject": subject
            },
        "mail": {
             "body": mail
            }
        }
    }
    $('#welcome_tour .form-submit-loader').removeClass('hidden')
    $.ajax(settings).done(function(response) {
        $('#welcome_tour .form-submit-loader').addClass('hidden');
        if(typeof response.code != 'undefined' && response.code == 400) {
            $(".admin-invite-box div:nth-child("+response.key+")").find('input').addClass('has-error');
            $(".admin-invite-box div:nth-child("+response.key+")").append('<span class="admin-invite-error">'+response.message+'</span>');
            setTimeout(function(){
                $('.edit-admin-invite').find('.admin-invite-error').remove();
                $(".admin-invite-box div:nth-child("+response.key+")").find('input').removeClass('has-error');
            }, 5000);
        } else if(response.code == 200) {
            if(response.space_admin.length > 0) {
                $('.admin-invite-result-box').removeClass('hidden');
                appendSpaceUserInInviteAdminScreen(response.space_admin);
            }
            $('.admin-invite-box .twitter-feed-input').val('');
            updateNextStep(current_step);
        }
    });
});

$(document).on('click', '.tour-next-step', function(event){
    current_step = $(this).closest('.welcome-cs-popup');
    
    if(!saveCategory(current_step, event))
        return false;

    if(!saveDomain(current_step, event))
        return false;

    updateNextStep(current_step);
});

$(document).on('click', '.tour-previous-step', function(){
    $('.tour-domain-list span.error-msg').remove();
    $('.tour-domain-list input').removeClass('has-error');
    current_step = $(this).closest('.welcome-cs-popup');
    current_step.addClass('hidden');
    current_step.prev($('.welcome-cs-popup')).removeClass('hidden');
});

$(document).on('click', '.add-domain', function(){
    domain_layout = $(this).closest('.welcome-tour-domain-col').find('.tour-domain-list');
    domain_layout_clone = domain_layout.eq(0).clone();
    domain_layout_clone.find('input').val('');
    domain_layout_clone.find('input').removeClass('has-error');
    domain_layout_clone.find('input').removeAttr('readonly');
    domain_layout_clone.find('input').attr('name', 'rule[]');
    domain_layout_clone.append('<span class="wc-domain-delete"><img src="'+baseurl+'/images/ic_deleteBlue.svg"></span>');
    domain_layout_clone.find('.link-input-icon p').html(domain_layout.length+1);
    domain_layout_clone.find('.error-msg').remove();
    $('.tour-domain-list').eq(domain_layout.length-1).after(domain_layout_clone);
});

$(document).on('click', '.add-tour-category', function(){
    addCategory(this);

    if($('.tour-category-list').length%2 != 0)
        addCategory(this);
});

$(document).on('click', '.wc-categories-delete', function(){
    $(this).closest('.tour-category-list').remove();
});

$(document).on('click', '.wc-domain-delete', function(){
    $(this).closest('.tour-domain-list').remove();
    $('.tour-domain-list').each(function(index){
        $(this).find('.link-input-icon p').html(index+1)
    });
});

$(document).on('click', '.add_post_trigger', function(){
    current_step = $(this).closest('.welcome-cs-popup');
    updateTourStep(current_step.attr('data-step'));
    current_step.addClass('hidden');
    current_step.prev($('.welcome-cs-popup')).removeClass('hidden');
    $('#welcome_tour').modal('hide');
    $('.add_post_form .post_subject').trigger('click');

});

$(document).on('click', '#onboarding_twitter_handles .add-handles', function(ele){
    addTwitterFeedInput(ele);
});

$(document).on('click', '.btn-twitter-handle', function () {
    $('.tour-domain-list span.error-msg').remove();
    $('.tour-domain-list input').removeClass('has-error');
    $('.tour-twitter-list').each(function(index){
        $(this).find('.twitter-input-col input').attr('id','twitter_handle_'+index);
    });
    $('#onboarding_twitter_handles').submit();
});

$(document).on('click', 'form#update_welcome_share_logo .onboarding_company_logo', function(){
    $('.twitter-input-col input').removeClass('has-error');
    $('.twitter-error').remove();
    var seller_twitter_name = $('.seller_twitter_name').val().trim();
    var buyer_twitter_name = $('.buyer_twitter_name').val().trim();
    if(seller_twitter_name)
        $('#onboarding_twitter_handles #twitter_handle_0').val(seller_twitter_name);
    if(!seller_twitter_name && buyer_twitter_name)
        $('#onboarding_twitter_handles #twitter_handle_0').val(buyer_twitter_name);
      
});

$(document).on('click', 'form#update_welcome_share_banner .onboarding_company_logo', function(){
    var seller_twitter_name = $('.seller_twitter_name').val().trim();
    var buyer_twitter_name = $('.buyer_twitter_name').val().trim();
    var twitter_handle_last = $('#twitter_handle_2').val();
    if(seller_twitter_name && buyer_twitter_name)
    {
        $('#onboarding_twitter_handles .add-handles').trigger('click');
        $('#onboarding_twitter_handles #twitter_handle_1').val(buyer_twitter_name);
        if($('#twitter_handle_1').val() != '' && typeof twitter_handle_last != 'undefined' && twitter_handle_last.length < 2)
            $('#twitter_handle_2').val('');
    }
    if((typeof twitter_handle_last != 'undefined' && twitter_handle_last.length < 2) || twitter_handle_last == '')
        $('#twitter_handle_2').parent().find('.remove-handle').trigger('click');
        
});

$(document).on('submit','#onboarding_twitter_handles',function(e){
       var validate = validateInputs('welcome_twitter_feed_modal');
       if(!validate)
        return false;

       $('.welcome_twitter_feed_modal .twitter-feed-input').removeClass('has-error');
       $('.btn-twitter-handles').attr('disabled', true);
       e.preventDefault();
       var form =$(this);
       var form_data = new FormData(form[0]);
       $.ajax({
              type: 'post',
              url: baseurl+'/save_twitter_feed?space_id='+session_space_id,
              data:  form_data,
              async: true,
              success: function (response) {
                 if(response.result){ 
                      $('.btn-twitter-handles').attr('disabled', false);
                      current_step = form.closest('.welcome-cs-popup');
                      updateTourStep(current_step.attr('data-step'));
                      current_step.addClass('hidden');
                      current_step.next($('.welcome-cs-popup')).removeClass('hidden');
                      getTwitterFeeds();
                      addProgressBarsection();
                 } else {
                    $('.welcome_twitter_feed_modal #twitter_handle_'+response.key).addClass('has-error');
                    $('.welcome_twitter_feed_modal #twitter_handle_'+response.key).closest('div.twitter-handle')
                    .append($('<p class="error twitter_links_error">'+response.error+'</p>'));
                 } 
              },
              error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'onboarding_twitter_handles');
              },
              cache: false,
              contentType: false,
              processData: false
            });
        return false;
});

$(document).on('click', '.slider.round', function() {
    $('.slider-toggle-on-content').toggleClass('hidden');
    $('.slider-toggle-off-content').toggleClass('hidden');
});

//# sourceMappingURL=feed_page.js.map
