/** 
* @class EventH Event Helper������һЩEvent�����������
* @singleton
* @helper
* @namespace QW
*/
QW.EventH = function () {
	var getDoc = function (e) {
		var target = EventH.getTarget(e), doc = document;

		/*
		ie unload target is null
		*/

		if (target) {
			doc = target.ownerDocument || target.document || (target.defaultView || target.window) && target || document;
		}
		return doc;
	};

	var EventH = {

		/** 
		* ��ȡ���λ������ҳ���X����
		* @method	getPageX
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{int}		X����
		*/
		getPageX : function () {
			var e = EventH.getEvent.apply(EventH, arguments)
				, doc = getDoc(e);
			return ('pageX' in e) ? e.pageX : (e.clientX + (doc.documentElement.scrollLeft || doc.body.scrollLeft) - 2);
		}

		/** 
		* ��ȡ���λ������ҳ���Y����
		* @method	getPageY
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{int}		Y����
		*/
		, getPageY : function () {
			var e = EventH.getEvent.apply(EventH, arguments)
				, doc = getDoc(e);
			return ('pageY' in e) ? e.pageY : (e.clientY + (doc.documentElement.scrollTop || doc.body.scrollTop) - 2);
		}
		
		/** 
		* ��ȡ�����봥���¼����󶥶�X����
		* @method	getLayerX
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{int}		X����
		, getLayerX : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			return ('layerX' in e) ? e.layerX : e.offsetX;
		}
		*/
		
		
		/** 
		* ��ȡ�����봥���¼����󶥶�Y����
		* @method	getLayerY
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{int}		Y����
		, getLayerY : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			return ('layerY' in e) ? e.layerY : e.offsetY;
		}
		*/
		
		
		/** 
		* ��ȡ�����ַ���
		* @method	getDetail
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{int}		����0����,С��0����.
		*/
		, getDetail : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			return e.detail || -(e.wheelDelta || 0);
		}
		
		/** 
		* ��ȡ�����¼��İ�����Ӧ��ascii��
		* @method	getKeyCode
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{int}		����ascii
		*/
		, getKeyCode : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			return ('keyCode' in e) ? e.keyCode : (e.charCode || e.which || 0);
		}
		
		/** 
		* ��ֹ�¼�ð��
		* @method	stopPropagation
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{void}
		*/
		, stopPropagation : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			if (e.stopPropagation) e.stopPropagation();
			else e.cancelBubble = true;
		}
		
		/** 
		* ��ֹ�¼�Ĭ����Ϊ
		* @method	preventDefault
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{void}
		*/
		, preventDefault : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			if (e.preventDefault) e.preventDefault();
			else e.returnValue = false;
		}
		
		/** 
		* ��ȡ�¼�����ʱ�Ƿ������סctrl��
		* @method	getCtrlKey
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{boolean}	�жϽ��
		*/
		, getCtrlKey : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			return e.ctrlKey;
		}
		
		/** 
		* �¼�����ʱ�Ƿ������סshift��
		* @method	getShiftKey
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{boolean}	�жϽ��
		*/
		, getShiftKey : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			return e.shiftKey;
		}
		
		/** 
		* �¼�����ʱ�Ƿ������סalt��
		* @method	getAltKey
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{boolean}	�жϽ��
		*/
		, getAltKey : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			return e.altKey;
		}
		
		/** 
		* �����¼���Ԫ��
		* @method	getTarget
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{element}	node ����
		*/
		, getTarget : function () {
			var e = EventH.getEvent.apply(EventH, arguments), node = e.srcElement || e.target;

			if (!node) return null;
			if (node.nodeType == 3) node = node.parentNode;
			return node;
		}
		
		/** 
		* ��ȡԪ��
		* @method	getRelatedTarget
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{element}	mouseover/mouseout �¼�ʱ��Ч overʱΪ��ԴԪ��,outʱΪ�ƶ�����Ԫ��.
		*/
		, getRelatedTarget : function () {
			var e = EventH.getEvent.apply(EventH, arguments);
			if ('relatedTarget' in e) return e.relatedTarget;
			if (e.type == 'mouseover') return e.fromElement || null;
			if (e.type == 'mouseout') return e.toElement || null;
			return null;
		}

		/** 
		* ���event����
		* @method	target
		* @param	{event}		event	(Optional)event���� Ĭ��Ϊ����λ������������event
		* @param	{element}	element (Optional)����element���� element��������������event
		* @return	{event}		event����
		*/
		, getEvent : function (event, element) {
			if (event) {
				return event;
			} else if (element) {
				if (element.document) return element.document.parentWindow.event;
				if (element.parentWindow) return element.parentWindow.event;
			}

			if (window.event) {
				return window.event;
			} else {
				var f = arguments.callee;
				do {
					if (/Event/.test(f.arguments[0])) return f.arguments[0];
				} while (f = f.caller);
				return null;
			}
		}
	};

	return EventH;
}();