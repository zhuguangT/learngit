/** 
* Dom Utils����Domģ�������
* @class DomU 
* @singleton
* @namespace QW
*/
QW.DomU = function () {
	var Selector=QW.Selector;
	var Browser = QW.Browser;
	var DomU = {

		/** 
		* ��cssselector��ȡԪ�ؼ� 
		* @method	query
		* @param {String} sSelector cssselector�ַ���
		* @param {Element} refEl (Optional) �ο�Ԫ�أ�Ĭ��Ϊdocument.documentElement
		* @return {Array}
		*/
		query: function (sSelector,refEl) {
			return Selector.query(refEl || document.documentElement,sSelector);
		},
		/** 
		* ��ȡdoc��һЩ������Ϣ 
		* �ο���YUI3.1.1
		* @refer  https://github.com/yui/yui3/blob/master/build/dom/dom.js
		* @method	getDocRect
		* @param	{object} doc (Optional) document����/Ĭ��Ϊ��ǰ������document
		* @return	{object} ����doc��scrollX,scrollY,width,height,scrollHeight,scrollWidthֵ��json
		*/
		getDocRect : function (doc) {
			doc = doc || document;

			var win = doc.defaultView || doc.parentWindow,
				mode = doc.compatMode,
				root = doc.documentElement,
				h = win.innerHeight || 0,
				w = win.innerWidth || 0,
				scrollX = win.pageXOffset || 0,
				scrollY = win.pageYOffset || 0,
				scrollW = root.scrollWidth,
				scrollH = root.scrollHeight;

			if (mode != 'CSS1Compat') { // Quirks
				root = doc.body;
				scrollW = root.scrollWidth;
				scrollH = root.scrollHeight;
			}

			if (mode && !Browser.opera) { // IE, Gecko
				w = root.clientWidth;
				h = root.clientHeight;
			}

			scrollW = Math.max(scrollW, w);
			scrollH = Math.max(scrollH, h);

			scrollX = Math.max(scrollX, doc.documentElement.scrollLeft, doc.body.scrollLeft);
			scrollY = Math.max(scrollY, doc.documentElement.scrollTop, doc.body.scrollTop);

			return {
				width : w,
				height : h,
				scrollWidth : scrollW,
				scrollHeight : scrollH,
				scrollX : scrollX,
				scrollY : scrollY
			};
		},

		/** 
		* ͨ��html�ַ�������Dom���� 
		* @method	create
		* @param	{string}	html html�ַ���
		* @param	{boolean}	rfrag (Optional) �Ƿ񷵻�documentFragment����
		* @param	{object}	doc	(Optional)	document Ĭ��Ϊ ��ǰdocument
		* @return	{element}	����html�ַ���element�����documentFragment����
		*/
		create : function () {
			var temp = document.createElement('div');

			return function (html, rfrag, doc) {
				var dtemp = doc && doc.createElement('div') || temp;
				dtemp.innerHTML = html;
				var el = dtemp.firstChild;
				
				if (!el || !rfrag) {
					return el;
				} else {
					doc = doc || document;
					var frag = doc.createDocumentFragment();
					while (el = dtemp.firstChild) frag.appendChild(el);
					return frag;
				}
			};
		}(),

		/** 
		* ��NodeCollectionתΪElementCollection
		* @method	pluckWhiteNode
		* @param	{NodeCollection|array} list Node�ļ���
		* @return	{array}						Element�ļ���
		*/
		pluckWhiteNode : function (list) {
			var result = [], i = 0, l = list.length;
			for (; i < l ; i ++)
				if (DomU.isElement(list[i])) result.push(list[i]);
			return result;
		},

		/** 
		* �ж�Nodeʵ���Ƿ�̳���Element�ӿ�
		* @method	isElement
		* @param	{object} element Node��ʵ��
		* @return	{boolean}		 �жϽ��
		*/
		isElement : function (el) {
			return !!(el && el.nodeType == 1);
		},

		/** 
		* ����Dom���ṹ��ʼ������¼�
		* @method	ready
		* @param	{function} handler �¼��������
		* @param	{object}	doc	(Optional)	document Ĭ��Ϊ ��ǰdocument
		* @return	{void}
		*/
		ready : function (handler, doc) {
			doc = doc || document;

			if (/complete/.test(doc.readyState)) {
				handler();
			} else {				
				if (doc.addEventListener) {
					if ('interactive' == doc.readyState) {
						handler();
					} else {
						doc.addEventListener("DOMContentLoaded", handler, false);
					}
				} else {
					var fireDOMReadyEvent = function () {
						fireDOMReadyEvent = new Function;
						handler();
					};
					void function () {
						try {
							doc.body.doScroll('left');
						} catch (exp) {
							return setTimeout(arguments.callee, 1);
						}
						fireDOMReadyEvent();
					}();
					doc.attachEvent('onreadystatechange', function () {
						('complete' == doc.readyState) && fireDOMReadyEvent();
					});
				}
			}
		},
	

		/** 
		* �ж�һ�������Ƿ������һ������
		* @method	rectContains
		* @param	{object} rect1	����
		* @param	{object} rect2	����
		* @return	{boolean}		�ȽϽ��
		*/
		rectContains : function (rect1, rect2) {
			return rect1.left	 <= rect2.left
				&& rect1.right   >= rect2.right
				&& rect1.top     <= rect2.top
				&& rect1.bottom  >= rect2.bottom;
		},

		/** 
		* �ж�һ�������Ƿ����һ�������н���
		* @method	rectIntersect
		* @param	{object} rect1	����
		* @param	{object} rect2	����
		* @return	{rect}			�������λ�null
		*/
		rectIntersect : function (rect1, rect2) {
			//����������
			var t = Math.max( rect1.top,	  rect2.top    )
				, r = Math.min( rect1.right,  rect2.right  )
				, b = Math.min( rect1.bottom, rect2.bottom )
				, l = Math.max( rect1.left,   rect2.left   );
			
			if (b >= t && r >= l) {
				return { top : t, right : r, bottom: b, left : l };
			} else {
				return null;
			}
		},

		/** 
		* ����һ��element
		* @method	createElement
		* @param	{string}	tagName		Ԫ������
		* @param	{json}		property	����
		* @param	{document}	doc	(Optional)		document
		* @return	{element}	������Ԫ��
		*/
		createElement : function (tagName, property, doc) {
			doc = doc || document;
			var el = doc.createElement(tagName);
			
			if (property) {
				for (var i in property) el[i] = property[i];
			}
			return el;
		}

	};
	
	return DomU;
}();