(function(){
var mix=QW.ObjectH.mix;

var CustEventTarget=QW.CustEventTarget=function(){
	this.__custListeners={};
};

var methodized = QW.HelperH.methodize(QW.CustEventTargetH,null, {on:'operator',un:'operator'}); //��Helper�������prototype������ͬʱ�޸�on/un�ķ���ֵ
mix(CustEventTarget.prototype, methodized);

QW.CustEvent.createEvents = CustEventTarget.createEvents = function(target,types){
	QW.CustEventTargetH.createEvents(target, types);
	return mix(target,CustEventTarget.prototype);//���ض������on��
};
})();