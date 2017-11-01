/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	Download by http://www.codefans.net
	version: $version$ $release$ released
	author: wuliang@baidu.com
*/

/**
 * @class FunctionH ���Ķ���Function����չ
 * @singleton 
 * @namespace QW
 * @helper
 */
(function(){

var FunctionH = {
	/**
	 * ������װ�� methodize���Ժ�������methodize����ʹ��ĵ�һ������Ϊthis����this[attr]��
	 * @method methodize
	 * @static
	 * @param {function} funcҪ�������ĺ���
	 * @optional {string} attr ����
	 * @return {function} �ѷ������ĺ���
	 */
	methodize: function(func,attr){
		if(attr) return function(){
			var ret = func.apply(null,[this[attr]].concat([].slice.call(arguments)));
			return ret;
		};
		return function(){
			var ret = func.apply(null,[this].concat([].slice.call(arguments)));
			return ret;
		};
	},
   /** �Ժ������м�����ʹ���һ����������������
	* @method mul
	* @static
	* @param {function} func
	* @param {bite} opt ���������ȱʡ��ʾĬ�ϣ�
					1 ��ʾgetFirst��ֻ������һ��Ԫ�أ�
					2 ��ʾjoinLists�������һ�����������飬�������Ľ����ƽ������
	* @return {Object} �Ѽ����ĺ���
	*/
	mul: function(func, opt){
		
		var getFirst = opt == 1, joinLists = opt == 2;

		if(getFirst){
			return function(){
				var list = arguments[0];
				if(!(list instanceof Array)) return func.apply(this,arguments);
				if(list.length) {
					var args=[].slice.call(arguments,0);
					args[0]=list[0];
					return func.apply(this,args);
				}
			}
		}

		return function(){
			var list = arguments[0];
			if(list instanceof Array){
				var ret = [];
				var moreArgs = [].slice.call(arguments,0);
				for(var i = 0, len = list.length; i < len; i++){
					moreArgs[0]=list[i];
					var r = func.apply(this, moreArgs);
					if(joinLists) r && (ret = ret.concat(r));
					else ret.push(r); 	
				}
				return ret;
			}else{
				return func.apply(this, arguments);
			}
		}
	},
	/**
	 * ������װ�任
	 * @method rwrap
	 * @static
	 * @param {func} 
	 * @return {Function}
	 */
	rwrap: function(func,wrapper,idx){
		idx=idx|0;
		return function(){
			var ret = func.apply(this, arguments);
			if(idx>=0) ret=arguments[idx];
			return wrapper ? new wrapper(ret) : ret;
		}
	},
	/**
	 * ��
	 * @method bind
	 * @via https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Function/bind
	 * @compatibile ECMA-262, 5th (JavaScript 1.8.5)
	 * @static
	 * @param {func} Ҫ�󶨵ĺ���
	 * @obj {object} this_obj
	 * @optional [, arg1 [, arg2 [...] ] ] Ԥ��ȷ���Ĳ���
	 * @return {Function}
	 */
	bind: function(func, obj/*,[, arg1 [, arg2 [...] ] ]*/){
		var slice = [].slice,
			args = slice.call(arguments, 2),
			nop = function(){},
			bound = function(){
				return func.apply(this instanceof nop?this:(obj||{}),
								args.concat(slice.call(arguments)));
			};

		nop.prototype = func.prototype;

		bound.prototype = new nop();

		return bound;
	}
};


QW.FunctionH=FunctionH;

})();