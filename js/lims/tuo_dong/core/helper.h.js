/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	version: $version$ $release$ released
	author: yingjiakuan@baidu.com
*/

/**
 * Helper������������ģ������������Helper����ģ��
 * @module core
 * @beta
 * @submodule core_HelperH
 */

/**
 * @class HelperH
 * <p>һ��Helper��ָͬʱ��������������һ������</p>
 * <ol><li>Helper��һ�������п�ö��proto���Եļ򵥶�������ζ���������for...in...ö��һ��Helper�е��������Ժͷ�����</li>
 * <li>Helper����ӵ�����Ժͷ�������Helper�Է����Ķ��������������������</li>
 * <div> 1). Helper�ķ��������Ǿ�̬���������ڲ�����ʹ��this��</div>
 * <div> 2). ͬһ��Helper�еķ����ĵ�һ��������������ͬ���ͻ���ͬ���͡�</div>
 * <li> Helper���͵����ֱ�����Helper���д��ĸH��β�� </li>
 * <li> ����ֻ�����һ����JSON��Ҳ���Ƿ�Helper��ͨ���ԡ�U����util����β�� </li>
 * <li> ����Util��HelperӦ���Ǽ̳й�ϵ������JavaScript�����ǰѼ̳й�ϵ���ˡ�</li>
 * </ol>
 * @singleton
 * @namespace QW
 * @helper
 */

(function(){

var FunctionH = QW.FunctionH,
	create = QW.ObjectH.create,
	Methodized = function(){};

var HelperH = {
	/**
	* ������Ҫ����wrap�����helper���������н����װ
	* @method rwrap
	* @static
	* @param {Helper} helper Helper����
	* @param {Class} wrapper ������ֵ���а�װʱ�İ�װ��(WrapClass)
	* @param {Object} wrapConfig ��Ҫ����Wrap����ķ���������
	* @return {Object} ������rwrap����<strong>�µ�</strong>Helper
	*/
	rwrap: function(helper, wrapper, wrapConfig){
		var ret = create(helper);
		wrapConfig = wrapConfig || {};

		for(var i in helper){
			var wrapType=wrapConfig, fn = helper[i];
			if (typeof wrapType != 'string') {
				wrapType=wrapConfig[i] || '';
			}
			if('queryer' == wrapType){ //����������ز�ѯ������Է���ֵ���а�װ
				ret[i] = FunctionH.rwrap(fn, wrapper, -1);
			}
			else if('operator' == wrapType){ //�������ֻ��ִ��һ������
				if(helper instanceof Methodized){ //�����methodized���,��thisֱ�ӷ���
					ret[i] = function(fn){
						return function(){
							fn.apply(this, arguments);
							return this;
						}
					}(fn);
				}
				else{ 
					ret[i] = FunctionH.rwrap(fn, wrapper, 0);//����Ե�һ���������а�װ�����getterϵ��
				}
			}
		}
		return ret;
	},
	/**
	* �������ã�����gsetter�·���������駲����ĳ�������������getter����setter
	* @method gsetter
	* @static
	* @param {Helper} helper Helper����
	* @param {Object} gsetterConfig ��Ҫ����Wrap����ķ���������
	* @return {Object} ������gsetter����<strong>�µ�</strong>helper
	*/
	gsetter: function(helper,gsetterConfig){
		var ret = create(helper);
		gsetterConfig=gsetterConfig||{};

		for(var i in gsetterConfig){
			if(helper instanceof Methodized){
				ret[i]=function(config){
					return function(){
						return ret[config[Math.min(arguments.length,config.length-1)]].apply(this,arguments);
					}
				}(gsetterConfig[i]);
			}else{
				ret[i]=function(config){
					return function(){
						return ret[config[Math.min(arguments.length,config.length)-1]].apply(null,arguments);
					}
				}(gsetterConfig[i]);
			}
		}
		return ret;
	},
	
	/**
	* ��helper�ķ���������mul����ʹ���ڵ�һ������Ϊarrayʱ�����Ҳ����һ������
	* @method mul
	* @static
	* @param {Helper} helper Helper����
	* @param {json|string} mulConfig ���ĳ��������mulConfig���ͺͺ������£�
			getter ��getter_first_all //ͬʱ����get--(����fist)��getAll--(����all)
			getter_first	//����get--(����first)
			getter_all	//����get--(����all)
			queryer		//����get--(����concat all���)
	* @return {Object} ������mul����<strong>�µ�</strong>Helper
	*/
	mul: function (helper, mulConfig){ 		
		var ret = create(helper);
		mulConfig =mulConfig ||{};

		for(var i in helper){
			if(typeof helper[i] == "function"){
				var mulType=mulConfig;
				if (typeof mulType != 'string') {
					mulType=mulConfig[i] || '';
				}

				if("getter" == mulType ||
				   "getter_first" == mulType || 
				   "getter_first_all" == mulType){ 
					//��������ó�gettter||getter_first||getter_first_all����ô��Ҫ�õ�һ������
					ret[i] = FunctionH.mul(helper[i], 1);
				}
				else if("getter_all" == mulType){
					ret[i] = FunctionH.mul(helper[i], 0);
				}else{
					ret[i] = FunctionH.mul(helper[i], 2); //operator��queryer�Ļ���Ҫjoin����ֵ���ѷ���ֵjoin������˵
				}
				if("getter" == mulType ||
				   "getter_first_all" == mulType){ 
					//������ó�getter||getter_first_all����ô��������һ����All��׺�ķ���
					ret[i+"All"] = FunctionH.mul(helper[i], 0);
				}
			}
		}
		return ret;
	},
	/**
	* ��helper�ķ���������methodize����ʹ��ĵ�һ������Ϊthis����this[attr]��
	* <strong>methodize������������helper�ϵķ�function���Ա�Լ��������»��߿�ͷ�ĳ�Ա��˽�г�Ա��</strong>
	* @method methodize
	* @static
	* @param {Helper} helper Helper������DateH
	* @param {optional} attr (Optional)����
	* @return {Object} ������methodize���Ķ���
	*/
	methodize: function(helper, attr){
		var ret = new Methodized(); //��Ϊ methodize ֮��gsetter��rwrap����Ϊ��һ��  
		
		for(var i in helper){
			if(typeof helper[i] == "function" && !/^_/.test(i)){
				ret[i] = FunctionH.methodize(helper[i], attr); 
			}
		}
		return ret;
	}

};

QW.HelperH = HelperH;
})();

