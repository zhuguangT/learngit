/*
	Copyright (c) 2009, Baidu Inc. All rights reserved.
	http://www.youa.com
	Download by http://www.codefans.net
	version: $version$ $release$ released
	author: wuliang@baidu.com
*/

/**
 * @class ClassH Ϊfunction�ṩǿ����ԭ�ͼ̳�����
 * @singleton 
 * @namespace QW
 * @helper
 */
(function(){
var mix = QW.ObjectH.mix,
	create = QW.ObjectH.create;

var ClassH = {
	/**
	 * <p>Ϊ���Ͷ�̬����һ��ʵ��������ֱ��new����������instanceof��ֵ</p>
	 * <p><strong>�ڶ���ʽ��new T <=> T.apply(T.getPrototypeObject())</strong></p>
	 * @method createInstance
	 * @static
	 * @prarm {function} cls Ҫ�����������ͣ���������
	 * @return {object} ������͵�һ��ʵ��
	 */
	createInstance : function(cls){
		var p = create(cls.prototype);
		cls.apply(p,[].slice.call(arguments,1));
		return p;
	},

	/**
	 * ������װ�� extend
	 * <p>�Ľ��Ķ���ԭ�ͼ̳У��ӳ�ִ�в������죬���������ʵ���������$super����</p>
	 * @method extend
	 * @static
	 * @param {function} cls ���������ԭʼ����
	 * @param {function} p ������
	 * @return {function} ����������Ϊ�������̳���p������
	 * @throw {Error} ���ܶԼ̳з��ص�������ʹ��extend
	 */
	extend : function(cls,p){
		
		var T = function(){};			//����prototype-chain
		T.prototype = p.prototype;
		
		var cp = cls.prototype;
		
		cls.prototype = new T();
		cls.$super = p; //�ڹ������ڿ���ͨ��arguments.callee.$superִ�и��๹��

		//���ԭʼ���͵�prototype���з�������copy
		mix(cls.prototype, cp, true);

		return cls;
	}
};

QW.ClassH = ClassH;

})();