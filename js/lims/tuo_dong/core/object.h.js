/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	Download by http://www.codefans.net
	version: $version$ $release$ released
	author: wuliang@baidu.com
*/


/**
 * @class ObjectH ���Ķ���Object�ľ�̬��չ
 * @singleton
 * @namespace QW
 * @helper
 */

(function(){
var encode4Js=QW.StringH.encode4Js,
	getConstructorName=function(o){
		return o!=null && Object.prototype.toString.call(o).slice(8,-1);
	};
var ObjectH = {
	/** 
	* �ж�һ�������Ƿ���booleanֵ��boolean����
	* @method isBoolean
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isBoolean: function (obj){
		return getConstructorName(obj) =='Boolean';
	},
	
	/** 
	* �ж�һ�������Ƿ���numberֵ��Number����
	* @method isNumber
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isNumber: function (obj){
		return getConstructorName(obj) =='Number' && isFinite(obj) ;
	},
	
	/** 
	* �ж�һ�������Ƿ���stringֵ��String����
	* @method isString
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isString: function (obj){
		return getConstructorName(obj) =='String';
	},
	
	/** 
	* �ж�һ�������Ƿ���Date����
	* @method isDate
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isDate: function (obj){
		return getConstructorName(obj) == 'Date';
	},
	
	/** 
	* �ж�һ�������Ƿ���function����
	* @method isFunction
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isFunction: function (obj){
		return getConstructorName(obj) =='Function';
	},
	
	/** 
	* �ж�һ�������Ƿ���RegExp����
	* @method isRegExp
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isRegExp: function (obj){
		return getConstructorName(obj) =='RegExp';
	},
	/** 
	* �ж�һ�������Ƿ���Array����
	* @method isArray
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isArray: function (obj){
		return getConstructorName(obj) =='Array';
	},
	
	/** 
	* �ж�һ�������Ƿ���typeof 'object'
	* @method isObject
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isObject: function (obj){
		return obj !== null && typeof obj == 'object';
	},
	
	/** 
	* �ж�һ�������Ƿ���Array���ͣ���:��length���Բ��Ҹ���������ֵ�Ķ���
	* @method isArrayLike
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isArrayLike: function (obj){
		return !!obj && typeof obj =='object' && obj.nodeType!=1 && typeof obj.length == 'number';
	},

	/** 
	* �ж�һ��������constructor�Ƿ���Object��---ͨ���������ж�һ�������Ƿ���{}����new Object()�����Ķ���
	* @method isPlainObject
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isPlainObject: function (obj){
		return !!obj && obj.constructor === Object;
	},
	
	/** 
	* �ж�һ�������Ƿ���Wrap����
	* @method isWrap
	* @static
	* @param {any} obj Ŀ�����
	* @param {string} coreName (Optional) core����������Ĭ��Ϊ'core'
	* @returns {boolean} 
	*/
	isWrap: function (obj, coreName){
		return !!obj && !!obj[coreName||'core'];
	},

	/** 
	* �ж�һ�������Ƿ���Html��ElementԪ��
	* @method isElement
	* @static
	* @param {any} obj Ŀ�����
	* @returns {boolean} 
	*/
	isElement: function (obj){
		return !!obj && obj.nodeType == 1;
	},

	/** 
	* Ϊһ��������������
	* @method set
	* @static
	* @param {Object} obj Ŀ�����
	* @param {string} prop ������
	* @param {any} value ����ֵ
	* @returns {void} 
	*/
	set:function (obj,prop,value){
		obj[prop]=value;
	},

	/** 
	* ��ȡһ�����������ֵ:
	* @method set
	* @static
	* @param {Object} obj Ŀ�����
	* @param {string} prop ������
	* @returns {any} 
	*/
	get:function (obj,prop){
		return obj[prop];
	},

	/** 
	* Ϊһ�������������ԣ�֧���������ֵ��÷�ʽ:
		setEx(obj, prop, value)
		setEx(obj, propJson)
		setEx(obj, props, values)
		---�ر�˵��propName����ĵ㣬�ᱻ�������ԵĲ��
	* @method setEx
	* @static
	* @param {Object} obj Ŀ�����
	* @param {string|Json|Array|setter} prop �����string,��������(�������������������ַ���,��"style.display")�������function����setter�����������Json����prop/value�ԣ���������飬��prop���飬�ڶ���������Ӧ��Ҳ��value����
	* @param {any | Array} value ����ֵ
	* @returns {Object} obj 
	* @example 
		var el={style:{},firstChild:{}};
		setEx(el,"id","aaaa");
		setEx(el,{className:"cn1", 
			"style.display":"block",
			"style.width":"8px"
		});
	*/
	setEx:function (obj,prop,value){
		if(ObjectH.isArray(prop)) {
			//setEx(obj, props, values)
			for(var i=0;i<prop.length;i++){
				ObjectH.setEx(obj,prop[i],value[i]);
			}
		}
		else if(typeof prop == 'object') {
			//setEx(obj, propJson)
			for(i in prop)
				ObjectH.setEx(obj,i,prop[i]);
		}
		else if(typeof prop == 'function'){//getter
			var args=[].slice.call(arguments,1);
			args[0]=obj;
			prop.apply(null,args);
		}
		else {
			//setEx(obj, prop, value);
			var keys=(prop+"").split(".");
			i=0;
			for(var obj2=obj, len=keys.length-1;i<len;i++){
				obj2=obj2[keys[i]];
			}
			obj2[keys[i]]=value;
		}
		return obj;
	},

	/** 
	* �õ�һ�������������ԣ�֧���������ֵ��÷�ʽ:
		getEx(obj, prop) -> obj[prop]
		getEx(obj, props) -> propValues
		getEx(obj, propJson) -> propJson
	* @method getEx
	* @static
	* @param {Object} obj Ŀ�����
	* @param {string|Array|getter} prop �����string,��������(�������������������ַ���,��"style.display")�������function����getter�����������array���򵱻�ȡ�����������У�
		�����Array����props����
	* @param {boolean} nullSensitive �Ƿ���������쳣���С���������������м�Ϊ�գ��Ƿ��׳��쳣
	* @returns {any|Array} ��������ֵ
	* @example 
		getEx(obj,"style"); //����obj["style"];
		getEx(obj,"style.color"); //���� obj.style.color;
		getEx(obj,"styleee.color"); //���� undefined;
		getEx(obj,"styleee.color",true); //�׿�ָ���쳣����Ϊobj.styleee.color�����е�obj.styleeeΪ��;
		getEx(obj,["id","style.color"]); //���� [obj.id, obj.style.color];
	*/
	getEx:function (obj,prop,nullSensitive){
		if(ObjectH.isArray(prop)){	//getEx(obj, props)
			var ret=[];
			for(i =0; i<prop.length;i++){
				ret[i]=ObjectH.getEx(obj,prop[i],nullSensitive);
			}
		}
		else if(typeof prop == 'function'){	//getter
			var args=[].slice.call(arguments,1);
			args[0]=obj;
			return prop.apply(null,args);
		}
		else {	//getEx(obj, prop)
			var keys=(prop+"").split(".");
			ret=obj;
			for(i=0;i<keys.length;i++){
				if(!nullSensitive && ret==null) return undefined;
				ret=ret[keys[i]];
			}
		}
		return ret;
	},

	/** 
	* ��Դ��������Բ��뵽Ŀ�����
	* @method mix
	* @static
	* @param {Object} des Ŀ�����
	* @param {Object|Array} src Դ������������飬�����β���
	* @param {boolean} override (Optional) �Ƿ񸲸���������
	* @returns {Object} des
	*/
	mix: function(des, src, override){
		if(ObjectH.isArray(src)){
			for(var i = 0, len = src.length; i<len; i++){
				ObjectH.mix(des, src[i], override);
			}
			return des;
		}
		for(i in src){
			if(override || !(des[i]) && !(i in des) ){
				des[i] = src[i];
			}
		}
		return des;
	},

	/**
	* <p>���һ���������������</p>
	* <p><strong>������Ա�"."�ָ�����ȡ�����ε�����</strong>������:</p>
	* <p>ObjectH.dump(o, "aa"); //�õ� {"aa": o.aa}</p>
	* @method dump
	* @static
	* @param {Object} obj �������Ķ���
	* @param {Array} props ����Ҫ�����Ƶ��������Ƶ�����
	* @return {Object} ������dump�������ԵĶ��� 
	*/
	dump: function(obj, props){
		var ret = {};
		for(var i = 0, len = props.length; i < len; i++){
			if(i in props){
				var key = props[i];
				ret[key] = obj[key];
			}
		}
		return ret;
	},
	/**
	* �ڶ����е�ÿ��������������һ��������������������ֵ��Ϊ���Ե�ֵ��
	* @method map
	* @static
	* @param {Object} obj �������Ķ���
	* @param {function} fn ��������ÿ�����Ե����ӣ������ӵ���������������value-����ֵ��key-��������obj����ǰ����
	* @param {Object} thisObj (Optional)��������ʱ��this
	* @return {Object} ���ذ�������������������Լ������Ķ���
	*/
	map : function(obj, fn, thisObj){
		var ret = {};
		for(var key in obj){
			ret[key] = fn.call(thisObj, obj[key], key, obj);
		}
		return ret;
	},
	/**
	* �õ�һ�����������п��Ա�ö�ٳ������Ե��б�
	* @method keys
	* @static
	* @param {Object} obj �������Ķ���
	* @return {Array} ���ذ�������������������Ե�����
	*/
	keys : function(obj){
		var ret = [];
		for(var key in obj){
			if(obj.hasOwnProperty(key)){ 
				ret.push(key);
			}
		}
		return ret;
	},

	/**
	* ��keys/values����ķ�ʽ������Ե�һ������<br/>
	* <strong>���values�ĳ��ȴ���keys�ĳ��ȣ������Ԫ�ؽ�������</strong>
	* @method fromArray
	* @static
	* @param {Object} obj �������Ķ���
	* @param {Array} keys ���key������
	* @param {Array} values ���value������
	* @return {Object} ������������ԵĶ���
	*/
	fromArray : function(obj, keys, values){
		values = values || [];
		for(var i = 0, len = keys.length; i < len; i++){
			obj[keys[i]] = values[i];
		}
		return obj;
	},

	/**
	* �õ�һ�����������п��Ա�ö�ٳ�������ֵ���б�
	* @method values
	* @static
	* @param {Object} obj �������Ķ���
	* @return {Array} ���ذ��������������������ֵ������
	*/
	values : function(obj){
		var ret = [];
		for(var key in obj){
			if(obj.hasOwnProperty(key)){ 
				ret.push(obj[key]);
			}
		}
		return ret;
	},
	/**
	 * ��ĳ����Ϊԭ�ʹ���һ���µĶ��� ��by Ben Newman��
	 * @method create
	 * @static 
	 * @param {Object} proto ��Ϊԭ�͵Ķ���
	 * @optional {Object} props ��������
	 */
	create : function(proto, props){
		var ctor = function(ps){
			if(ps){
			  ObjectH.mix(this, ps, true);
			}
		};
		ctor.prototype = proto;
		return new ctor(props);		
	},
	/** 
	* ���л�һ������(ֻ���л�String,Number,Boolean,Date,Array,Json�������toJSON�����Ķ���,�����Ķ��󶼻ᱻ���л���null)
	* @method stringify
	* @static
	* @param {Object} obj ��Ҫ���л���Json��Array�������������
	* @returns {String} : �������л����
	* @example 
		var card={cardNo:"bbbb1234",history:[{date:"2008-09-16",count:120.0,isOut:true},1]};
		alert(stringify(card));
	*/
	stringify:function (obj){
		if(obj==null) return null;
		if(obj.toJSON) {
			obj= obj.toJSON();
		}
		var type=typeof obj;
		switch(type){
			case 'string': return '"'+encode4Js(obj)+'"';
			case 'number': 
			case 'boolean': return obj+'';
			case 'object' :
				if(obj instanceof Date)  return 'new Date(' + obj.getTime() + ')';
				if(obj instanceof Array) {
					var ar=[];
					for(var i=0;i<obj.length;i++) ar[i]=ObjectH.stringify(obj[i]);
					return '['+ar.join(',')+']';
				}
				if(ObjectH.isPlainObject(obj)){
					ar=[];
					for(i in obj){
						ar.push('"'+encode4Js(i+'')+'":'+ObjectH.stringify(obj[i]));
					}
					return '{'+ar.join(',')+'}';
				}
		}
		return null;//�޷����л��ģ�����null;
	}

};

QW.ObjectH=ObjectH;
})();