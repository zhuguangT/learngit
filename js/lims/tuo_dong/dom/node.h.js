/** 
* @class NodeH Node Helper�����element���ݴ���͹�����չ
* @singleton
* @Download by http://www.codefans.net
* @namespace QW
*/
QW.NodeH = function () {

	var ObjectH = QW.ObjectH;
	var StringH = QW.StringH;
	var DomU = QW.DomU;
	var Browser = QW.Browser;
	var Selector = QW.Selector;

	/** 
	* ���element����
	* @method	g
	* @param	{element|string|wrap}	el	id,Elementʵ����wrap
	* @param	{object}				doc		(Optional)document Ĭ��Ϊ ��ǰdocument
	* @return	{element}				�õ��Ķ����null
	*/
	var g = function (el, doc) {
		if ('string' == typeof el) {
			if(el.indexOf('<')==0) return DomU.create(el,false,doc);
			return (doc||document).getElementById(el);
		} else {
			return (ObjectH.isWrap(el)) ? arguments.callee(el.core) : el;
		}
	};

	var regEscape = function (str) {
		return String(str).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
	};

	var getPixel = function (el, value) {
		if (/px$/.test(value) || !value) return parseInt(value, 10) || 0;
		var right = el.style.right, runtimeRight = el.runtimeStyle.right;
		var result;

		el.runtimeStyle.right = el.currentStyle.right;
		el.style.right = value;
		result = el.style.pixelRight || 0;

		el.style.right = right;
		el.runtimeStyle.right = runtimeRight;
		return result;
	};

	var NodeH = {
		
		/** 
		* ���element�����outerHTML����
		* @method	outerHTML
		* @param	{element|string|wrap}	el	id,Elementʵ����wrap
		* @param	{object}				doc		(Optional)document Ĭ��Ϊ ��ǰdocument
		* @return	{string}				outerHTML����ֵ
		*/
		outerHTML : function () {
			var temp = document.createElement('div');
			
			return function (el, doc) {
				el = g(el);
				if ('outerHTML' in el) {
					return el.outerHTML;
				} else {
					temp.innerHTML='';
					var dtemp = doc && doc.createElement('div') || temp;
					dtemp.appendChild(el.cloneNode(true));
					return dtemp.innerHTML;
				}
			};
		}(),

		/** 
		* �ж�element�Ƿ����ĳ��className
		* @method	hasClass
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				className	��ʽ��
		* @return	{void}
		*/
		hasClass : function (el, className) {
			el = g(el);
			return new RegExp('(?:^|\\s)' + regEscape(className) + '(?:\\s|$)').test(el.className);
		},

		/** 
		* ��element���className
		* @method	addClass
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				className	��ʽ��
		* @return	{void}
		*/
		addClass : function (el, className) {
			el = g(el);
			if (!NodeH.hasClass(el, className))
				el.className = el.className ? el.className + ' ' + className : className;
		},

		/** 
		* �Ƴ�elementĳ��className
		* @method	removeClass
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				className	��ʽ��
		* @return	{void}
		*/
		removeClass : function (el, className) {
			el = g(el);
			if (NodeH.hasClass(el, className))
				el.className = el.className.replace(new RegExp('(?:^|\\s)' + regEscape(className) + '(?=\\s|$)', 'ig'), '');
		},

		/** 
		* �滻element��className
		* @method	replaceClass
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				oldClassName	Ŀ����ʽ��
		* @param	{string}				newClassName	����ʽ��
		* @return	{void}
		*/
		replaceClass : function (el, oldClassName, newClassName) {
			el = g(el);
			if (NodeH.hasClass(el, oldClassName)) {
				el.className = el.className.replace(new RegExp('(^|\\s)' + regEscape(oldClassName) + '(?=\\s|$)', 'ig'), '$1' + newClassName);
			} else {
				NodeH.addClass(el, newClassName);
			}
		},

		/** 
		* element��className1��className2�л�
		* @method	toggleClass
		* @param	{element|string|wrap}	el			id,Elementʵ����wrap
		* @param	{string}				className1		��ʽ��1
		* @param	{string}				className2		(Optional)��ʽ��2
		* @return	{void}
		*/
		toggleClass : function (el, className1, className2) {
			className2 = className2 || '';
			if (NodeH.hasClass(el, className1)) {
				NodeH.replaceClass(el, className1, className2);
			} else {
				NodeH.replaceClass(el, className2, className1);
			}
		},

		/** 
		* ��ʾelement����
		* @method	show
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				value		(Optional)display��ֵ Ĭ��Ϊ��
		* @return	{void}
		*/
		show : function (el, value) {
			el = g(el);
			el.style.display = value || '';
		},

		/** 
		* ����element����
		* @method	hide
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{void}
		*/
		hide : function (el) {
			el = g(el);
			el.style.display = 'none';
		},

		/** 
		* ����/��ʾelement����
		* @method	toggle
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				value		(Optional)��ʾʱdisplay��ֵ Ĭ��Ϊ��
		* @return	{void}
		*/
		toggle : function (el, value) {
			if (NodeH.isVisible(el)) {
				NodeH.hide(el);
			} else {
				NodeH.show(el, value);
			}
		},

		/** 
		* �ж�element�����Ƿ�ɼ�
		* @method	isVisible
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{boolean}				�жϽ��
		*/
		isVisible : function (el) {
			el = g(el);
			//return this.getStyle(el, 'visibility') != 'hidden' && this.getStyle(el, 'display') != 'none';
			//return !!(el.offsetHeight || el.offestWidth);
			return !!((el.offsetHeight + el.offsetWidth) && NodeH.getStyle(el, 'display') != 'none');
		},


		/** 
		* ��ȡelement�������doc��xy����
		* �ο���YUI3.1.1
		* @refer  https://github.com/yui/yui3/blob/master/build/dom/dom.js
		* @method	getXY
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{array}					x, y
		*/
		getXY : function () {

			var calcBorders = function (node, xy) {
				var t = parseInt(NodeH.getCurrentStyle(node, 'borderTopWidth'), 10) || 0,
					l = parseInt(NodeH.getCurrentStyle(node, 'borderLeftWidth'), 10) || 0;

				if (Browser.gecko) {
					if (/^t(?:able|d|h)$/i.test(node.tagName)) {
						t = l = 0;
					}
				}
				xy[0] += l;
				xy[1] += t;
				return xy;
			};

			return document.documentElement.getBoundingClientRect ? function (node) {
				var doc = node.ownerDocument,
					docRect = DomU.getDocRect(doc),
					scrollLeft = docRect.scrollX,
					scrollTop = docRect.scrollY,
					box = node.getBoundingClientRect(),
					xy = [box.left, box.top],
					off1, off2,
					mode,
					bLeft, bTop;


				if (Browser.ie) {
					off1 = 2;
					off2 = 2;
					mode = doc.compatMode;
					bLeft = NodeH.getCurrentStyle(doc.documentElement, 'borderTopWidth');
					bTop = NodeH.getCurrentStyle(doc.documentElement, 'borderLeftWidth');
					
					if (mode == 'BackCompat') {
						if (bLeft !== 'medium') {
							off1 = parseInt(bLeft, 10);
						}
						if (bTop !== 'medium') {
							off2 = parseInt(bTop, 10);
						}
					} else if (Browser.ie6) {
						off1 = 0;
						off2 = 0;
					}
					
					xy[0] -= off1;
					xy[1] -= off2;

				}

				if (scrollTop || scrollLeft) {
					xy[0] += scrollLeft;
					xy[1] += scrollTop;
				}

				return xy;

			} : function (node, doc) {
				doc = doc || document;

				var xy = [node.offsetLeft, node.offsetTop],
					parentNode = node.parentNode,
					doc = node.ownerDocument,
					docRect = DomU.getDocRect(doc),
					bCheck = !!(Browser.gecko || parseFloat(Browser.webkit) > 519),
					scrollTop = 0,
					scrollLeft = 0;
				
				while ((parentNode = parentNode.offsetParent)) {
					xy[0] += parentNode.offsetLeft;
					xy[1] += parentNode.offsetTop;
					if (bCheck) {
						xy = calcBorders(parentNode, xy);
					}
				}

				if (NodeH.getCurrentStyle(node, 'position') != 'fixed') {
					parentNode = node;

					while ((parentNode = parentNode.parentNode)) {
						scrollTop = parentNode.scrollTop;
						scrollLeft = parentNode.scrollLeft;


						if (Browser.gecko && (NodeH.getCurrentStyle(parentNode, 'overflow') !== 'visible')) {
							xy = calcBorders(parentNode, xy);
						}
						
						if (scrollTop || scrollLeft) {
							xy[0] -= scrollLeft;
							xy[1] -= scrollTop;
						}
					}
					
				}

				xy[0] += docRect.scrollX;
				xy[1] += docRect.scrollY;

				return xy;

			};

		}(),

		/** 
		* ����element�����xy����
		* @method	setXY
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{int}					x			(Optional)x���� Ĭ�ϲ�����
		* @param	{int}					y			(Optional)y���� Ĭ�ϲ�����
		* @return	{void}
		*/
		setXY : function (el, x, y) {
			el = g(el);
			x = parseInt(x, 10);
			y = parseInt(y, 10);
			if ( !isNaN(x) ) NodeH.setStyle(el, 'left', x + 'px');
			if ( !isNaN(y) ) NodeH.setStyle(el, 'top', y + 'px');
		},

		/** 
		* ����element�����offset���
		* @method	setSize
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{int}					w			(Optional)�� Ĭ�ϲ�����
		* @param	{int}					h			(Optional)�� Ĭ�ϲ�����
		* @return	{void}
		*/
		setSize : function (el, w, h) {
			el = g(el);
			w = parseFloat (w, 10);
			h = parseFloat (h, 10);

			if (isNaN(w) && isNaN(h)) return;

			var borders = NodeH.borderWidth(el);
			var paddings = NodeH.paddingWidth(el);

			if ( !isNaN(w) ) NodeH.setStyle(el, 'width', Math.max(+w - borders[1] - borders[3] - paddings[1] - paddings[3], 0) + 'px');
			if ( !isNaN(h) ) NodeH.setStyle(el, 'height', Math.max(+h - borders[0] - borders[2] - paddings[1] - paddings[2], 0) + 'px');
		},

		/** 
		* ����element����Ŀ��
		* @method	setInnerSize
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{int}					w			(Optional)�� Ĭ�ϲ�����
		* @param	{int}					h			(Optional)�� Ĭ�ϲ�����
		* @return	{void}
		*/
		setInnerSize : function (el, w, h) {
			el = g(el);
			w = parseFloat (w, 10);
			h = parseFloat (h, 10);

			if ( !isNaN(w) ) NodeH.setStyle(el, 'width', w + 'px');
			if ( !isNaN(h) ) NodeH.setStyle(el, 'height', h + 'px');
		},

		/** 
		* ����element�����offset��ߺ�xy����
		* @method	setRect
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{int}					x			(Optional)x���� Ĭ�ϲ�����
		* @param	{int}					y			(Optional)y���� Ĭ�ϲ�����
		* @param	{int}					w			(Optional)�� Ĭ�ϲ�����
		* @param	{int}					h			(Optional)�� Ĭ�ϲ�����
		* @return	{void}
		*/
		setRect : function (el, x, y, w, h) {
			NodeH.setXY(el, x, y);
			NodeH.setSize(el, w, h);
		},

		/** 
		* ����element����Ŀ�ߺ�xy����
		* @method	setRect
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{int}					x			(Optional)x���� Ĭ�ϲ�����
		* @param	{int}					y			(Optional)y���� Ĭ�ϲ�����
		* @param	{int}					w			(Optional)�� Ĭ�ϲ�����
		* @param	{int}					h			(Optional)�� Ĭ�ϲ�����
		* @return	{void}
		*/
		setInnerRect : function (el, x, y, w, h) {
			NodeH.setXY(el, x, y);
			NodeH.setInnerSize(el, w, h);
		},

		/** 
		* ��ȡelement����Ŀ��
		* @method	getSize
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{object}				width,height
		*/
		getSize : function (el) {
			el = g(el);
			return { width : el.offsetWidth, height : el.offsetHeight };
		},

		/** 
		* ��ȡelement����Ŀ�ߺ�xy����
		* @method	setRect
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{object}				width,height,left,top,bottom,right
		*/
		getRect : function (el) {
			el = g(el);
			var p = NodeH.getXY(el);
			var x = p[0];
			var y = p[1];
			var w = el.offsetWidth; 
			var h = el.offsetHeight;
			return {
				'width'  : w,    'height' : h,
				'left'   : x,    'top'    : y,
				'bottom' : y+h,  'right'  : x+w
			};
		},

		/** 
		* ����ȡelement���󸴺��������ֵܽڵ�
		* @method	nextSibling
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				selector	(Optional)��ѡ���� Ĭ��Ϊ�ռ�������ֵܽڵ�
		* @return	{node}					�ҵ���node��null
		*/
		nextSibling : function (el, selector) {
			var fcheck = Selector.selector2Filter(selector || '');
			el = g(el);
			do {
				el = el.nextSibling;
			} while (el && !fcheck(el));
			return el;
		},

		/** 
		* ��ǰ��ȡelement���󸴺��������ֵܽڵ�
		* @method	previousSibling
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				selector	(Optional)��ѡ���� Ĭ��Ϊ�ռ�������ֵܽڵ�
		* @return	{node}					�ҵ���node��null
		*/
		previousSibling : function (el, selector) {
			var fcheck = Selector.selector2Filter(selector || '');
			el = g(el);
			do {
				el = el.previousSibling;
			} while (el && !fcheck(el)); 
			return el;
		},

		/** 
		* ���ϻ�ȡelement���󸴺��������ֵܽڵ�
		* @method	previousSibling
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				selector	(Optional)��ѡ���� Ĭ��Ϊ�ռ�������ֵܽڵ�
		* @return	{element}					�ҵ���node��null
		*/
		ancestorNode : function (el, selector) {
			var fcheck = Selector.selector2Filter(selector || '');
			el = g(el);
			do {
				el = el.parentNode;
			} while (el && !fcheck(el));
			return el;
		},

		/** 
		* ���ϻ�ȡelement���󸴺��������ֵܽڵ�
		* @method	parentNode
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				selector	(Optional)��ѡ���� Ĭ��Ϊ�ռ�������ֵܽڵ�
		* @return	{element}					�ҵ���node��null
		*/
		parentNode : function (el, selector) {
			return NodeH.ancestorNode(el, selector);
		},

		/** 
		* ��element��������ʼλ�û�ȡ���������Ľڵ�
		* @method	firstChild
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				selector	(Optional)��ѡ���� Ĭ��Ϊ�ռ�������ֵܽڵ�
		* @return	{node}					�ҵ���node��null
		*/
		firstChild : function (el, selector) {
			var fcheck = Selector.selector2Filter(selector || '');
			el = g(el).firstChild;
			while (el && !fcheck(el)) el = el.nextSibling;
			return el;
		},

		/** 
		* ��element�����ڽ���λ�û�ȡ���������Ľڵ�
		* @method	lastChild
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				selector	(Optional)��ѡ���� Ĭ��Ϊ�ռ�������ֵܽڵ�
		* @return	{node}					�ҵ���node��null
		*/
		lastChild : function (el, selector) {
			var fcheck = Selector.selector2Filter(selector || '');
			el = g(el).lastChild;
			while (el && !fcheck(el)) el = el.previousSibling;
			return el;
		},

		/** 
		* �ж�Ŀ������Ƿ���element���������ڵ�
		* @method	contains
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{element|string|wrap}	target		Element����
		* @return	{boolean}				�жϽ��
		*/
		contains : function (el, target) {
			el = g(el), target = g(target);
			return el.contains
				? el != target && el.contains(target)
				: !!(el.compareDocumentPosition(target) & 16);
		},

		/** 
		* ��element����ǰ/������ʼ���ڽ�β����html
		* @method	insertAdjacentHTML
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				sWhere		λ�����ͣ�����ֵ�У�beforebegin��afterbegin��beforeend��afterend
		* @param	{element|string|wrap}	html		�����html
		* @return	{void}
		*/
		insertAdjacentHTML : function (el, sWhere, html) {
			el = g(el);
			if (el.insertAdjacentHTML) {
				el.insertAdjacentHTML(sWhere, html);
			} else {
				var df;
				var r = el.ownerDocument.createRange();
				switch (String(sWhere).toLowerCase()) {
					case "beforebegin":
						r.setStartBefore(el);
						df = r.createContextualFragment(html);
						break;
					case "afterbegin":
						r.selectNodeContents(el);
						r.collapse(true);
						df = r.createContextualFragment(html);
						break;
					case "beforeend":
						r.selectNodeContents(el);
						r.collapse(false);
						df = r.createContextualFragment(html);
						break;
					case "afterend":
						r.setStartAfter(el);
						df = r.createContextualFragment(html);
						break;
				}
				NodeH.insertAdjacentElement(el, sWhere, df);
			}
		},

		/** 
		* ��element����ǰ/������ʼ���ڽ�β����element����
		* @method	insertAdjacentElement
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				sWhere		λ�����ͣ�����ֵ�У�beforebegin��afterbegin��beforeend��afterend
		* @param	{element|string|html|wrap}	newEl		�¶���
		* @return	{element}				newEl���¶���
		*/
		insertAdjacentElement : function (el, sWhere, newEl) {
			el = g(el), newEl = g(newEl);
			if (el.insertAdjacentElement) {
				el.insertAdjacentElement(sWhere, newEl);
			} else {
				switch (String(sWhere).toLowerCase()) {
					case "beforebegin":
						el.parentNode.insertBefore(newEl, el);
						break;
					case "afterbegin":
						el.insertBefore(newEl, el.firstChild);
						break;
					case "beforeend":
						el.appendChild(newEl);
						break;
					case "afterend":
						el.parentNode.insertBefore(newEl, el.nextSibling || null);
						break;
				}
			}
			return newEl;
		},

		/** 
		* ��element����ǰ/������ʼ���ڽ�β����element����
		* @method	insert
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				sWhere		λ�����ͣ�����ֵ�У�beforebegin��afterbegin��beforeend��afterend
		* @param	{element|string|wrap}	newEl		�¶���
		* @return	{void}	
		*/
		insert : function (el, sWhere, newEl) {
			NodeH.insertAdjacentElement(el,sWhere,newEl);
		},

		/** 
		* ��һ������嵽��һ�������ڽ���
		* @method	insertTo
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				sWhere		λ�����ͣ�����ֵ�У�beforebegin��afterbegin��beforeend��afterend
		* @param	{element|string|wrap}	refEl		λ�òο�����
		* @return	{void}				
		*/
		insertTo : function (el, sWhere, refEl) {
			NodeH.insertAdjacentElement(refEl,sWhere,el);
		},

		/** 
		* ��element������׷��element����
		* @method	appendChild
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{element|string|wrap}	newEl		�¶���
		* @return	{element}				�¶���newEl
		*/
		appendChild : function (el, newEl) {
			return g(el).appendChild(g(newEl));
		},

		/** 
		* ��element����ǰ����element����
		* @method	insertSiblingBefore
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{element|string|html|wrap}	newEl	�¶���
		* @return	{element}				�¶���newEl
		*/
		insertSiblingBefore : function (el, newEl) {
			el = g(el);
			return el.parentNode.insertBefore(g(newEl), el);
		},

		/** 
		* ��element��������element����
		* @method	insertSiblingAfter
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{element|string|wrap}	newEl	�¶���id,Elementʵ����wrap
		* @return	{element}				�¶���newEl
		*/
		insertSiblingAfter : function (el, newEl) {
			el = g(el);
			el.parentNode.insertBefore(g(newEl), el.nextSibling || null);
		},

		/** 
		* ��element�����ڲ���ĳԪ��ǰ����element����
		* @method	insertBefore
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{element|string|wrap}	newEl	�¶���id,Elementʵ����wrap
		* @param	{element|string|wrap}	refEl	λ�òο�����
		* @return	{element}				�¶���newEl
		*/
		insertBefore : function (el, newEl, refEl) {
			return g(el).insertBefore(g(newEl), refEl && g(refEl) || null);
		},

		/** 
		* ��element�����ڲ���ĳԪ�غ����element����
		* @method	insertAfter
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{element|string|wrap}	newEl	�¶���
		* @param	{element|string|wrap}	refEl	λ�òο�����
		* @return	{element}				�¶���newEl
		*/
		insertAfter : function (el, newEl, refEl) {
			return g(el).insertBefore(g(newEl), refEl && g(refEl).nextSibling || null);
		},

		/** 
		* ��һ��Ԫ���滻�Լ�
		* @method	replaceNode
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{element|string|wrap}	newEl		�½ڵ�id,Elementʵ����wrap
		* @return	{element}				���滻�ɹ����˷����ɷ��ر��滻�Ľڵ㣬���滻ʧ�ܣ��򷵻� NULL
		*/
		replaceNode : function (el, newEl) {
			el = g(el);
			return el.parentNode.replaceChild(g(newEl), el);
		},

		/** 
		* ��element���relement�滻��nelement
		* @method	replaceChild
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{element|string|wrap}	newEl	�½ڵ�id,Elementʵ����wrap
		* @param	{element|string|wrap}	childEl	���滻��id,Elementʵ����wrap��
		* @return	{element}				���滻�ɹ����˷����ɷ��ر��滻�Ľڵ㣬���滻ʧ�ܣ��򷵻� NULL
		*/
		replaceChild : function (el, newEl, childEl) {
			return g(el).replaceChild(g(newEl), g(childEl));
		},

		/** 
		* ��element�Ƴ���
		* @method	removeNode
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{element}				��ɾ���ɹ����˷����ɷ��ر�ɾ���Ľڵ㣬��ʧ�ܣ��򷵻� NULL��
		*/
		removeNode : function (el) {
			el = g(el);
			return el.parentNode.removeChild(el);
		},

		/** 
		* ��element���childEl�Ƴ���
		* @method	removeChild
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{element|string|wrap}	childEl		��Ҫ�Ƴ����Ӷ���
		* @return	{element}				��ɾ���ɹ����˷����ɷ��ر�ɾ���Ľڵ㣬��ʧ�ܣ��򷵻� NULL��
		*/
		removeChild : function (el, childEl) {
			return g(el).removeChild(g(childEl));
		},

		/** 
		* ��Ԫ�ص���ObjectH.setEx
		* @method	get
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				prop	��Ա����
		* @return	{object}				��Ա����
		* @see ObjectH.getEx
		*/
		get : function (el, prop) {
			//var args = [g(el)].concat([].slice.call(arguments, 1));
			el = g(el);
			return ObjectH.getEx.apply(null, arguments);
		},

		/** 
		* ��Ԫ�ص���ObjectH.setEx
		* @method	set
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				prop	��Ա����
		* @param	{object}				value		��Ա����/����
		* @return	{void}
		* @see ObjectH.setEx
		*/
		set : function (el, prop, value) {
			el = g(el);
			ObjectH.setEx.apply(null, arguments);
		},

		/** 
		* ��ȡelement���������
		* @method	getAttr
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				attribute	��������
		* @param	{int}					iFlags		(Optional)ieonly ��ȡ����ֵ�ķ������� ����ֵ0,1,2,4 
		* @return	{string}				����ֵ ie���п��ܲ���object
		*/
		getAttr : function (el, attribute, iFlags) {
			el = g(el);

			if ((attribute in el) && 'href' != attribute) {
				return el[attribute];
			} else {
				return el.getAttribute(attribute, iFlags || (el.nodeName == 'A' && attribute.toLowerCase() == 'href') && 2 || null);
			}
		},

		/** 
		* ����element���������
		* @method	setAttr
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				attribute	��������
		* @param	{string}				value		���Ե�ֵ
		* @param	{int}					iCaseSensitive	(Optional)
		* @return	{void}
		*/
		setAttr : function (el, attribute, value, iCaseSensitive) {
			el = g(el);

			if (attribute in el) {
				el[attribute] = value;
			} else {
				el.setAttribute(attribute, value, iCaseSensitive || null);
			}
		},

		/** 
		* ɾ��element���������
		* @method	removeAttr
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				attribute	��������
		* @param	{int}					iCaseSensitive	(Optional)
		* @return	{void}
		*/
		removeAttr : function (el, attribute, iCaseSensitive) {
			el = g(el);
			return el.removeAttribute(attribute, iCaseSensitive || 0);
		},

		/** 
		* ������������element��Ԫ����
		* @method	query
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				selector	����
		* @return	{array}					elementԪ������
		*/
		query : function (el, selector) {
			el = g(el);
			return Selector.query(el, selector || '');
		},

		/** 
		* ������������element��Ԫ��
		* @method	one
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				selector	����
		* @return	{HTMLElement}			elementԪ��
		*/
		one : function (el, selector) {
			el = g(el);
			return Selector.one(el, selector || '');
		},

		/** 
		* ����element�����а���className�ļ���
		* @method	getElementsByClass
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				className	��ʽ��
		* @return	{array}					elementԪ������
		*/
		getElementsByClass : function (el, className) {
			el = g(el);
			return Selector.query(el, '.' + className);
		},

		/** 
		* ��ȡelement��value
		* @method	getValue
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{string}				Ԫ��value
		*/
		getValue : function (el) {
			el = g(el);
			//if(el.value==el.getAttribute('data-placeholder')) return '';
			return el.value;
		},

		/** 
		* ����element��value
		* @method	setValue
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				value		����
		* @return	{void}					
		*/
		setValue : function (el, value) {
			g(el).value=value;
		},

		/** 
		* ��ȡelement��innerHTML
		* @method	getHTML
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{string}					
		*/
		getHtml : function (el) {
			el = g(el);
			return el.innerHTML;
		},

		/** 
		* ����element��innerHTML
		* @method	setHtml
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				value		����
		* @return	{void}					
		*/
		setHtml : function (el,value) {
			g(el).innerHTML=value;
		},

		/** 
		* ���form������elements����valueת������'&'���ӵļ�ֵ�ַ���
		* @method	encodeURIForm
		* @param	{element}	el			form����
		* @param	{string}	filter	(Optional)	���˺���,�ᱻѭ�����ô��ݸ�item������Ҫ�󷵻ز���ֵ�ж��Ƿ����
		* @return	{string}					��'&'���ӵļ�ֵ�ַ���
		*/
		encodeURIForm : function (el, filter) {

			el = g(el);

			filter = filter || function (el) { return false; };

			var result = []
				, els = el.elements
				, l = els.length
				, i = 0
				, push = function (name, value) {
					result.push(encodeURIComponent(name) + '=' + encodeURIComponent(value));
				};
			
			for (; i < l ; ++ i) {
				var el = els[i], name = el.name;

				if (el.disabled || !name) continue;
				
				switch (el.type) {
					case "text":
					case "hidden":
					case "password":
					case "textarea":
						if (filter(el)) break;
						push(name, el.value);
						break;
					case "radio":
					case "checkbox":
						if (filter(el)) break;
						if (el.checked) push(name, el.value);
						break;
					case "select-one":
						if (filter(el)) break;
						if (el.selectedIndex > -1) push(name, el.value);
						break;
					case "select-multiple":
						if (filter(el)) break;
						var opts = el.options;
						for (var j = 0 ; j < opts.length ; ++ j) {
							if (opts[j].selected) push(name, opts[j].value);
						}
						break;
				}
			}
			return result.join("&");
		},

		/** 
		* �ж�form�������Ƿ��иı�
		* @method	isFormChanged
		* @param	{element}	el			form����
		* @param	{string}	filter	(Optional)	���˺���,�ᱻѭ�����ô��ݸ�item������Ҫ�󷵻ز���ֵ�ж��Ƿ����
		* @return	{bool}					�Ƿ�ı�
		*/
		isFormChanged : function (el, filter) {

			el = g(el);

			filter = filter || function (el) { return false; };

			var els = el.elements, l = els.length, i = 0, j = 0, el, opts;
			
			for (; i < l ; ++ i, j = 0) {
				el = els[i];
				
				switch (el.type) {
					case "text":
					case "hidden":
					case "password":
					case "textarea":
						if (filter(el)) break;
						if (el.defaultValue != el.value) return true;
						break;
					case "radio":
					case "checkbox":
						if (filter(el)) break;
						if (el.defaultChecked != el.checked) return true;
						break;
					case "select-one":
						j = 1;
					case "select-multiple":
						if (filter(el)) break;
						opts = el.options;
						for (; j < opts.length ; ++ j) {
							if (opts[j].defaultSelected != opts[j].selected) return true;
						}
						break;
				}
			}

			return false;
		},

		/** 
		* ��¡Ԫ��
		* @method	cloneNode
		* @param	{element}	el			form����
		* @param	{bool}		bCloneChildren	(Optional) �Ƿ���ȿ�¡ Ĭ��ֵfalse
		* @return	{element}					��¡���Ԫ��
		*/
		cloneNode : function (el, bCloneChildren) {
			return g(el).cloneNode(bCloneChildren || false);
		},

		/** 
		* ���element�������ʽ
		* @method	getStyle
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				attribute	��ʽ��
		* @return	{string}				
		*/
		getStyle : function (el, attribute) {
			el = g(el);

			attribute = StringH.camelize(attribute);

			var hook = NodeH.cssHooks[attribute], result;

			if (hook) {
				result = hook.get(el);
			} else {
				result = el.style[attribute];
			}
			
			return (!result || result == 'auto') ? null : result;
		},

		/** 
		* ���element����ǰ����ʽ
		* @method	getCurrentStyle
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				attribute	��ʽ��
		* @return	{string}				
		*/
		getCurrentStyle : function (el, attribute, pseudo) {
			el = g(el);

			var displayAttribute = StringH.camelize(attribute);

			var hook = NodeH.cssHooks[displayAttribute], result;

			if (hook) {
				result = hook.get(el, true, pseudo);
			} else if (Browser.ie) {
				result = el.currentStyle[displayAttribute];
			} else {
				var style = el.ownerDocument.defaultView.getComputedStyle(el, pseudo || null);
				result = style ? style.getPropertyValue(StringH.decamelize(attribute)) : null;
			}
			
			return (!result || result == 'auto') ? null : result;
		},

		/** 
		* ����element�������ʽ
		* @method	setStyle
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @param	{string}				attribute	��ʽ��
		* @param	{string}				value		ֵ
		* @return	{void}
		*/
		setStyle : function (el, attribute, value) {
			el = g(el);

			if ('string' == typeof attribute) {
				var temp = {};
				temp[attribute] = value;
				attribute = temp;
			}

			//if (el.currentStyle && !el.currentStyle['hasLayout']) el.style.zoom = 1;
			
			for (var prop in attribute) {

				var displayProp = StringH.camelize(prop);

				var hook = NodeH.cssHooks[displayProp];

				if (hook) {
					hook.set(el, attribute[prop]);
				} else {
					el.style[displayProp] = attribute[prop];
				}
			}
		},

		/** 
		* ��ȡelement�����border���
		* @method	borderWidth
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{array}					topWidth, rightWidth, bottomWidth, leftWidth
		*/
		borderWidth : function () {
			var map =  {
				thin : 2,
				medium : 4,
				thick : 6
			};

			var getWidth = function (el, val) {
				var result = NodeH.getCurrentStyle(el, val);
				result = map[result] || parseFloat(result);
				return result || 0;
			};

			return function (el) {
				el = g(el);

				return [
					getWidth(el, 'borderTopWidth'),
					getWidth(el, 'borderRightWidth'),
					getWidth(el, 'borderBottomWidth'),
					getWidth(el, 'borderLeftWidth')
				];
			}
		}(),

		/** 
		* ��ȡelement�����padding���
		* @method	paddingWidth
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{array}					topWidth, rightWidth, bottomWidth, leftWidth
		*/
		paddingWidth : function (el) {
			el = g(el);
			return [
				getPixel(el, NodeH.getCurrentStyle(el, 'paddingTop'))
				, getPixel(el, NodeH.getCurrentStyle(el, 'paddingRight'))
				, getPixel(el, NodeH.getCurrentStyle(el, 'paddingBottom'))
				, getPixel(el, NodeH.getCurrentStyle(el, 'paddingLeft'))
			];
		},

		/** 
		* ��ȡelement�����margin���
		* @method	marginWidth
		* @param	{element|string|wrap}	el		id,Elementʵ����wrap
		* @return	{array}					topWidth, rightWidth, bottomWidth, leftWidth
		*/
		marginWidth : function (el) {
			el = g(el);
			return [
				getPixel(el, NodeH.getCurrentStyle(el, 'marginTop'))
				, getPixel(el, NodeH.getCurrentStyle(el, 'marginRight'))
				, getPixel(el, NodeH.getCurrentStyle(el, 'marginBottom'))
				, getPixel(el, NodeH.getCurrentStyle(el, 'marginLeft'))
			];
		},

		cssHooks : {
			'float' : {
				get : function (el, current, pseudo) {
					if (current) {
						var style = el.ownerDocument.defaultView.getComputedStyle(el, pseudo || null);
						return style ? style.getPropertyValue('cssFloat') : null;
					} else {
						return el.style['cssFloat'];
					}
				},
				set : function (el, value) {
					el.style['cssFloat'] = value;
				}
			}
		}

	};

	if (Browser.ie) {
		NodeH.cssHooks['float'] = {
			get : function (el, current) {
				return el[current ? 'currentStyle' : 'style'].styleFloat;
			},
			set : function (el, value) {
				el.style.styleFloat = value;
			}
		};
		
		NodeH.cssHooks.opacity = {
			get : function (el, current) {
				var match = el.currentStyle.filter.match(/alpha\(opacity=(.*)\)/);
				return match && match[1] ? parseInt(match[1], 10) / 100 : 1.0;
			},

			set : function (el, value) {
				el.style.filter = 'alpha(opacity=' + parseInt(value * 100) + ')';
			}
		};
	}

	NodeH.g = g;
	
	return NodeH;
}();