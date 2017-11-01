/** 
* @class EventTargetH EventTarget Helper��������¼�����Ŀ���йصļ�������
* @Download by http://www.codefans.net
* @singleton
* @helper
* @namespace QW
*/
QW.EventTargetH = function () {

	var E = {};
	var g=QW.NodeH.g;


	var cache = {};
	var delegateCache = {};
	var PROPERTY_NAME = '__EventTargetH_ID';
	var index = 0;


	/** 
	* ��ȡkey
	* @method	getKey
	* @private
	* @param	{element}	element		���۲��Ŀ��
	* @param	{string}	type		(Optional)�¼�����
	* @param	{function}	handler		(Optional)�¼��������
	* @return	{string}	key
	*/
	var getKey = function (element, type, handler) {
		var result = '';

		if (!element[PROPERTY_NAME]) element[PROPERTY_NAME] = ++ index;

		result += element[PROPERTY_NAME];

		if (type) {
			result += '_' + type;

			if (handler) {
				if (!handler[PROPERTY_NAME]) handler[PROPERTY_NAME] = ++ index;
				result += '_' + handler[PROPERTY_NAME];
			}
		}

		return result;
	};

	/** 
	* ��ȡkey
	* @method	getDelegateKey
	* @private
	* @param	{element}	element		��ί�е�Ŀ��
	* @param	{string}	selector	(Optional)ί�е�Ŀ��
	* @param	{string}	type		(Optional)�¼�����
	* @param	{function}	handler		(Optional)�¼��������
	* @return	{string}	key
	*/
	var getDelegateKey = function (element, selector, type, handler) {
		var result = '';

		if (!element[PROPERTY_NAME]) element[PROPERTY_NAME] = ++ index;

		result += element[PROPERTY_NAME];

		if (selector) {
			result += '_' + selector.replace(/_/g, '\x01');

			if (type) {
				result += '_' + type;

				if (handler) {
					if (!handler[PROPERTY_NAME]) handler[PROPERTY_NAME] = ++ index;
					result += '_' + handler[PROPERTY_NAME];
				}
			}
		}

		return result;
	};

	/** 
	* ͨ��key��ȡ�¼���
	* @method	keyToName
	* @private
	* @param	{string}	key		��ֵ
	* @return	{string}	�¼�����
	*/
	var keyToName = function (key) {
		return key.split('_')[1];
	};

	/** 
	* ͨ��key��ȡ�¼���
	* @method	delegateKeyToName
	* @private
	* @param	{string}	key		��ֵ
	* @return	{string}	�¼�����
	*/
	var delegateKeyToName = function (key) {
		return key.split('_')[2];
	};

	/** 
	* ��������
	* @method	listener
	* @private
	* @param	{element}	element	����Ŀ��
	* @param	{string}	name	�¼�����
	* @param	{function}	handler	�¼��������
	* @return	{object}	ί�з���ִ�н��
	*/
	var listener = function (element, name, handler) {
		return function (e) {
			return fireHandler(element, e, handler, name);
		};
	};

	/** 
	* ��������
	* @method	delegateListener
	* @private
	* @param	{element}	element 	����Ŀ��
	* @param	{string}	selector	ѡ����
	* @param	{string}	name		�¼�����
	* @param	{function}	handler		�¼��������
	* @return	{object}	ί�з���ִ�н��
	*/
	var delegateListener = function (element, selector, name, handler) {
		return function (e) {
			var elements = [], node = e.srcElement || e.target;
			
			if (!node) return;

			if (node.nodeType == 3) node = node.parentNode;

			while (node && node != element) {
				elements.push(node);
				node = node.parentNode;
			}

			elements = QW.Selector.filter(elements, selector, element);

			for (var i = 0, l = elements.length ; i < l ; ++ i) {
				fireHandler(elements[i], e, handler, name);

				/*fix remove element[i] bubble bug*/
				if (elements[i].parentNode && elements[i].parentNode.nodeType == 11) {
					if (e.stopPropagation) {
						e.stopPropagation();
					} else {
						e.cancelBubble = true;
					}
					break;
				}
			}
		};
	};

	/**
	 * ����¼�����
	 * @method	addEventListener
	 * @param	{element}	element	����Ŀ��
	 * @param	{string}	name	�¼�����
	 * @param	{function}	handler	�¼��������
	 * @param	{bool}		capture	(Optional)�Ƿ񲶻��ie����Ч
	 * @return	{void}
	 */
	E.addEventListener = function () {
		if (document.addEventListener) {
			return function (element, name, handler, capture) {
				element.addEventListener(name, handler, capture || false);
			};
		} else if (document.attachEvent) {
			return function (element, name, handler) {
				element.attachEvent('on' + name, handler);
			};
		} else {
			return function () {};
		}
	}();

	/**
	 * �Ƴ��¼�����
	 * @method	removeEventListener
	 * @private
	 * @param	{element}	element	����Ŀ��
	 * @param	{string}	name	�¼�����
	 * @param	{function}	handler	�¼��������
	 * @param	{bool}		capture	(Optional)�Ƿ񲶻��ie����Ч
	 * @return	{void}
	 */
	E.removeEventListener = function () {
		if (document.removeEventListener) {
			return function (element, name, handler, capture) {
				element.removeEventListener(name, handler, capture || false);
			};
		} else if (document.detachEvent) {
			return function (element, name, handler) {
				element.detachEvent('on' + name, handler);
			};
		} else {
			return function () {};
		}
	}();


	/**
	 * �������¼�
	 * @method	typedef
	 * @param	{string}	name	�����������
	 * @param	{string}	newname	�¶��������
	 * @param	{function}	handler	(Optional)�¼�������� ������������������e��handler. ����eΪevent����,handlerΪʹ���߶�Ͷ��ί��.
	 * @return	{void}
	 */
	var Types = {};
	E.typedef = function (name, newname, handler) {
		Types[newname] = { name : name, handler : handler };
	};

	/** 
	* ��׼���¼�����
	* @method	getName
	* @private
	* @param	{string}	name	�¼�����
	* @return	{string}	ת������¼�����
	*/
	
	var getName = function (name) {
		return Types[name] ? Types[name].name : name;
	};

	/** 
	* �¼�ִ�����
	* @method	fireHandler
	* @private
	* @param	{element}	element		�����¼�����
	* @param	{event}		event		�¼�����
	* @param	{function}	handler		�¼�ί��
	* @param	{string}	name		����ǰ�¼�����
	* @return	{object}	�¼�ί��ִ�н��
	*/
	var fireHandler = function (element, e, handler, name) {
		if (Types[name] && Types[name].handler) {
			return E.fireHandler(element, e, function (e) { return Types[name].handler.call(this, e, handler); }, name);
		} else {
			return E.fireHandler(element, e, handler, name);
		}
	};

	/** 
	* �¼�ִ�����
	* @method	fireHandler
	* @param	{element}	element		�����¼�����
	* @param	{event}		event		�¼�����
	* @param	{function}	handler		�¼�ί��
	* @param	{string}	name		����ǰ�¼�����
	* @return	{object}	�¼�ί��ִ�н��
	*/
	E.fireHandler = function (element, e, handler, name) {
		return handler.call(element, e);
	};

	/** 
	* ��Ӷ�ָ���¼��ļ���
	* @method	on
	* @param	{element}	element	����Ŀ��
	* @param	{string}	sEvent	�¼�����
	* @param	{function}	handler	�¼��������
	* @return	{boolean}	�¼��Ƿ�����ɹ�
	*/
	E.on = function (element, sEvent, handler) {
		element = g(element);

		var name = getName(sEvent);
		
		var key = getKey(element, sEvent, handler);

		if (cache[key]) {
			return false;
		} else {
			var _listener = listener(element, sEvent, handler);

			E.addEventListener(element, name, _listener);

			cache[key] = _listener;

			return true;
		}
	};

	/** 
	* �Ƴ���ָ���¼��ļ���
	* @method	un
	* @param	{element}	element	�Ƴ�Ŀ��
	* @param	{string}	sEvent	(Optional)�¼�����
	* @param	{function}	handler	(Optional)�¼��������
	* @return	{boolean}	�¼������Ƿ��Ƴ��ɹ�
	*/
	E.un = function (element, sEvent, handler) {
		
		element = g(element);
		
		if (handler) {

			var name = getName(sEvent);

			var key = getKey(element, sEvent, handler);

			var _listener = cache[key];

			if (_listener) {
				E.removeEventListener(element, name, _listener);
				
				delete cache[key];

				return true;
			} else {
				return false;
			}
		} else {			

			var leftKey = '^' + getKey(element, sEvent, handler), i, name;
			
			for (i in cache) {
				if (new RegExp(leftKey, 'i').test(i)) {
					name = keyToName(i);
					E.removeEventListener(element, getName(name), cache[i]);
					delete cache[i];
				}
			}

			return true;
		}
	};

	/** 
	* ����¼�ί��
	* @method	delegate
	* @param	{element}	element		��ί�е�Ŀ��
	* @param	{string}	selector	ί�е�Ŀ��
	* @param	{string}	sEvent		�¼�����
	* @param	{function}	handler		�¼��������
	* @return	{boolean}	�¼������Ƿ��Ƴ��ɹ�
	*/
	E.delegate = function (element, selector, sEvent, handler) {
		element = g(element);

		var name = getName(sEvent);
		
		var key = getDelegateKey(element, selector, sEvent, handler);

		if (delegateCache[key]) {
			return false;
		} else {
			var _listener = delegateListener(element, selector, sEvent, handler);

			E.addEventListener(element, name, _listener);

			delegateCache[key] = _listener;

			return true;
		}
	};

	/** 
	* �Ƴ��¼�ί��
	* @method	undelegate
	* @param	{element}	element		��ί�е�Ŀ��
	* @param	{string}	selector	(Optional)ί�е�Ŀ��
	* @param	{string}	sEvent		(Optional)�¼�����
	* @param	{function}	handler		(Optional)�¼��������
	* @return	{boolean}	�¼������Ƿ��Ƴ��ɹ�
	*/
	E.undelegate = function (element, selector, sEvent, handler) {
		element = g(element);
		
		if (handler) {

			var name = getName(sEvent);

			var key = getDelegateKey(element, selector, sEvent, handler);

			var _listener = delegateCache[key];

			if (_listener) {
				E.removeEventListener(element, name, _listener);
				
				delete delegateCache[key];

				return true;
			} else {
				return false;
			}
		} else {			

			var leftKey = '^' + getDelegateKey(element, selector, sEvent, handler).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1'), i, name;
			
			for (i in delegateCache) {
				if (new RegExp(leftKey, 'i').test(i)) {
					name = delegateKeyToName(i);
					E.removeEventListener(element, getName(name), delegateCache[i]);
					delete delegateCache[i];
				}
			}

			return true;
		}
	};

	/** 
	* ���������ָ���¼�
	* @method	fire
	* @param	{element}	element	Ҫ�����¼��Ķ���
	* @param	{string}	sEvent	�¼�����
	* @return	{void}
	*/
	E.fire = function (element, sEvent) {
		element = g(element);
		var name = getName(sEvent);

		if (element.fireEvent) {
			element.fireEvent('on' + name);
		} else {
			var evt = null, doc = element.ownerDocument || element;
			
			if (/mouse|click/i.test(sEvent)) {
				evt = doc.createEvent('MouseEvents');
				evt.initMouseEvent(name, true, true, doc.defaultView, 1, 0, 0, 0, 0, false, false, false, false, 0, null);
			} else {
				var evt = doc.createEvent('Events');
				evt.initEvent(name, true, true, doc.defaultView);
			}
			element.dispatchEvent(evt);
		}
	};

	var extend = function (types) {
		for (var i = 0, l = types.length ; i < l ; ++ i) {
			void function (type) {
				E[type] = function (element, handler) {
					if (handler) {
						E.on(element, type, handler)
					} else {
						element[type] && element[type]() || E.fire(element,type);
					}
				};
			}(types[i]);
		}
	};

	/** 
	* �󶨶����click�¼�����ִ��click����
	* @method	click
	* @param	{element}	element	Ҫ�����¼��Ķ���
	* @param	{function}	handler	(Optional)�¼�ί��
	* @return	{void}
	*/


	/** 
	* �󶨶����submit�¼�����ִ��submit����
	* @method	submit
	* @param	{element}	element	Ҫ�����¼��Ķ���
	* @param	{function}	handler	(Optional)�¼�ί��
	* @return	{void}
	*/

	/** 
	* �󶨶����focus�¼�����ִ��focus����
	* @method	focus
	* @param	{element}	element	Ҫ�����¼��Ķ���
	* @param	{function}	handler	(Optional)�¼�ί��
	* @return	{void}
	*/

	/** 
	* �󶨶����blur�¼�����ִ��blur����
	* @method	blur
	* @param	{element}	element	Ҫ�����¼��Ķ���
	* @param	{function}	handler	(Optional)�¼�ί��
	* @return	{void}
	*/

	extend('submit,click,focus,blur'.split(','));

	E.typedef('mouseover', 'mouseenter', function (e, handler) {
		var element = this, target = e.relatedTarget || e.fromElement || null;
		if (!target || target == element || (element.contains ? element.contains(target) : !!(element.compareDocumentPosition(target) & 16))) {
			return;
		}
		handler.call(element, e);
	});

	E.typedef('mouseout', 'mouseleave', function (e, handler) {
		var element = this, target = e.relatedTarget || e.toElement || null;
		if (!target || target == element || (element.contains ? element.contains(target) : !!(element.compareDocumentPosition(target) & 16))) {
			return;
		}
		handler.call(element, e);
	});

	void function () {
		var UA = navigator.userAgent;
		
		if (/firefox/i.test(UA)) {
			E.typedef('DOMMouseScroll', 'mousewheel');
		}

	}();

	return E;

}();