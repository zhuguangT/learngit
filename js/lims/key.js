$(function () {
  //$('input:text:first').focus();
  var $inp = $('.inputc');
  $inp.bind('keydown', function (e) {
	var key		= e.which;
	var jdian	= $inp.index(this);
	var iptk	= jdian;
	var sss		= $(".inputc:eq(" + jdian + ")").attr('name');
	var q2		= sss.substr(0,2);
	if(q2=='vd' && key==13){
		var k=sss.lastIndexOf("[");
		var w=sss.lastIndexOf("]");
		var kn=sss.substr(0,k);
		var lc=sss.substr(k+1,w-k-1);
		lc++;
		var jname="'"+kn+"["+lc+"]'";
		var vdx=sss.substr(2,k-2);
		var xz='input[name='+jname+']';
		var xname=$(xz).attr('name');
		if(!xname){
			vdx=parseInt(vdx)+1;
			jname="'vd"+vdx+"[0]'";
			xz='input[name='+jname+']';
		}		
			e.preventDefault();
			$(xz).focus();
	}else{
		switch (key){
			case 38: 
				jdian--
				break
			case 40:
			case 13:             
				jdian++
				break              
		}
		if(iptk!=jdian)
		{
			e.preventDefault();
			$(".inputc:eq(" + jdian + ")").focus();
		}
	}
  });
});

