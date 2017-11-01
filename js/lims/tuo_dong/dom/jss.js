(function () {
	var mix=QW.ObjectH.mix,
		evalExp=QW.StringH.evalExp;
/** 
* @class Jss Jss-Data���
* @singleton
* @namespace QW
*/

	var Jss={};

	mix(Jss,{
		/** 
		* @property	rules Jss�ĵ�ǰ����rule���൱��css������
		*/
		rules : {},
		/** 
		* ���jss rule
		* @method	addRule
		* @param	{string}	sSelector	selector�ַ�����Ŀǰֻ֧��#id��@name��.className��tagName
		* @param	{json}	ruleData json���󣬼�ΪarrtibuteName��ֵΪattributeValue������attributeValue�������κζ���
		* @return	{void}	
		*/
		addRule : function(sSelector, ruleData) {
			var data= Jss.rules[sSelector] || (Jss.rules[sSelector]={});
			mix(data,ruleData,true);
		},

		/** 
		* ���һϵ��jss rule
		* @method	addRules
		* @param	{json}	rules json���󣬼�Ϊselector��ֵΪruleData��Json����
		* @return	{json}	
		*/
		addRules : function(rules) {
			for(var i in rules){
				Jss.addRule(i,rules[i]);
			}
		},

		/** 
		* �Ƴ�jss rule
		* @method	removeRule
		* @param	{string}	sSelector	selector�ַ�����Ŀǰֻ֧��#id��@name��.className��tagName
		* @return	{boolean}	�Ƿ����Ƴ�����
		*/
		removeRule : function(sSelector) {
			var data = Jss.rules[sSelector];
			if(data) {
				delete Jss.rules[sSelector];
				return true;
			}
			return false;
		},
		/** 
		* ��ȡjss rule
		* @method	getRuleData
		* @param	{string}	sSelector	selector�ַ�����Ŀǰֻ֧��#id��@name��.className��tagName
		* @return	{json}	��ȡrule����������
		*/
		getRuleData : function(sSelector) {
			return Jss.rules[sSelector];
		},

		/** 
		* ����rule��ĳ����
		* @method	setRuleAttribute
		* @param	{string}	sSelector	selector�ַ�����Ŀǰֻ֧��#id��@name��.className��tagName
		* @param	{string}	arrtibuteName (Optional) attributeName
		* @param	{any}	value attributeValue
		* @return	{json}	�Ƿ񷢻��Ƴ�����
		*/
		setRuleAttribute : function(sSelector, arrtibuteName, value) {
			var data = {};
			data[arrtibuteName]=value;
			Jss.addRule(sSelector,data);
		},

		/** 
		* �Ƴ�rule��ĳ����
		* @method	removeRuleAttribute
		* @param	{string}	sSelector	selector�ַ�����Ŀǰֻ֧��#id��@name��.className��tagName
		* @param	{string}	arrtibuteName (Optional) attributeName
		* @return	{json}	�Ƿ񷢻��Ƴ�����
		*/
		removeRuleAttribute : function(sSelector, arrtibuteName) {
			var data = Jss.rules[sSelector];
			if(data && (attributeName in data)) {
				delete data[attributeName];
				return true;
			}
			return false;
		},

		/** 
		* ��selector��ȡjss ����
		* @method	getRuleAttribute
		* @param	{string}	sSelector	selector�ַ�����Ŀǰֻ֧��#id��@name��.className��tagName
		* @param	{string}	arrtibuteName	������
		* @return	{json}	��ȡrule������
		*/
		getRuleAttribute : function(sSelector,arrtibuteName) {
			var data = Jss.rules[sSelector]||{};
			return data[arrtibuteName];
		}
	});
/** 
* @class JssTargetH JssTargetH���
* @singleton
* @namespace QW
*/

/*
* ��ȡԪ�ص�inline��jssData
* @method	getOwnJssData
* @param	{element}	el	Ԫ��
* @return	{json}	��ȡ����JssData
*/
function getOwnJssData(el,needInit){
	var data=el.__jssData;
	if(!data){
		var s=el.getAttribute('data-jss');
		if(s){
			data=el.__jssData=evalExp('{'+s+'}');
		}
	}
	else if(needInit){
		data=el.__jssData={};
	}
	return data;
};

	var JssTargetH={

		/** 
		* ��ȡԪ�ص�inline��jss
		* @method	getOwnJss
		* @param	{element}	el	Ԫ��
		* @return	{any}	��ȡ����jss attribute
		*/
		getOwnJss : function(el, attributeName) {
			var data=getOwnJssData(el);
			if (data && (attributeName in data)){
				return data[attributeName];
			}
			return undefined;
		},

		/** 
		* ��ȡԪ�ص�jss���ԣ����ȶ�Ϊ��el.getAttribute('data-'+attributeName) > inlineJssAttribute > #id > @name > .className > tagName
		* @method	getJss
		* @param	{element}	el	Ԫ��
		* @return	{any}	��ȡ����jss attribute
		*/
		getJss : function(el, attributeName) {//Ϊ������ܣ������������е㳤��
			var val=el.getAttribute('data-'+attributeName);
			if(val) return val;
			var data=getOwnJssData(el);
			if (data && (attributeName in data)){
				return data[attributeName];
			}
			var getRuleData=Jss.getRuleData,
				id=el.id;
			if(id && (data=getRuleData('#'+id)) && (attributeName in data)){
				return data[attributeName];
			}
			var name=el.name;
			if(name && (data=getRuleData('@'+name)) && (attributeName in data)){
				return data[attributeName];
			}
			var className=el.className;
			if(className){
				var classNames=className.split(' ');
				for(var i=0;i<classNames.length;i++){
					if((data=getRuleData('.'+classNames[i])) && (attributeName in data)){
						return data[attributeName];
					}
				}
			}
			var tagName=el.tagName;
			if(name && (data=getRuleData(tagName)) && (attributeName in data)){
				return data[attributeName];
			}
			return undefined;	
		},
		/** 
		* ����Ԫ�ص�jss����
		* @method	setJss
		* @param	{element}	el	Ԫ��
		* @param	{string}	attributeName	attributeName
		* @param	{any}	attributeValue	attributeValue
		* @return	{void}	
		*/
		setJss : function(el, attributeName , attributeValue) {
			var data=getOwnJssData(el,true);
			data[attributeName]=attributeValue;
		},

		/** 
		* �Ƴ�Ԫ�ص�inline��jss
		* @method	removeJss
		* @param	{element}	el	Ԫ��
		* @param	{string}	attributeName	attributeName
		* @return	{boolean}	�Ƿ����remove����
		*/
		removeJss : function(el, attributeName) {
			var data=getOwnJssData(el);
			if(data && (attributeName in data)){
				delete data[attributeName];
				return true;
			}
			return false;
		}
	};

	QW.Jss=Jss;
	QW.JssTargetH=JssTargetH;
})();