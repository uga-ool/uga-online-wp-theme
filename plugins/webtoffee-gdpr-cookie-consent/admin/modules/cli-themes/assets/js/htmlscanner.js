/**
 * HTML scanner
 *
 * @link       http://cookielawinfo.com/
 * @since      2.1.8
 *
 * @package    Cookie_Law_Info
 */
var htmlScanner={
	only_visible:true,
	btn_data:[],
	bar_data:[],
	for_btn:['a','button','input[type="submit"]','input[type="cancel"]'],
	btn_prop:[
	'background-color',
	'color',
	'text-transform',
	'text-decoration',
	'font-weight',
	'font-size',
	'font-style',
	'font-family',
	'border-top-style','border-top-color','border-top-width',
	'border-right-style','border-right-color','border-right-width',
	'border-bottom-style','border-bottom-color','border-bottom-width',
	'border-left-style','border-left-color','border-left-width',
	'box-shadow',
	'border-top-left-radius',
	'border-top-right-radius',
	'border-bottom-left-radius',
	'border-bottom-right-radius',
	'text-shadow',
	'padding-left',
	'padding-right',
	'padding-top',
	'padding-bottom',
	'display',
	'float',
	'font-variant'
	],
	for_bar:['div','span','p','section','article','main','body','header','nav','aside','ul','li','footer'],
	bar_prop:['background-color','color','font-size','font-family','font-style','text-transform','font-weight','text-shadow','line-height','text-align','border-radius','border','box-shadow','padding-left','padding-right','text-transform'],
	Set:function()
	{
		var visible_chk=this.only_visible ? ':visible' : '';
		for(i=0; i<this.for_btn.length; i++)
		{
			this.scanBtnElm(jQuery(this.for_btn[i]+visible_chk));
		}
		for(i=0; i<this.for_bar.length; i++)
		{
			this.scanBarElm(jQuery(this.for_bar[i]+visible_chk));
		}

		var accpt_btn=jQuery('.cli-accept-button');
		console.log(accpt_btn.css('display'));

		this.saveThemeData();
		console.log(htmlScanner.btn_data);
	},
	saveThemeData:function()
	{
		jQuery.ajax({
			url:cli_htmlscanner.ajax_url,
			//dataType:'json',
		    type: 'POST',
			data:{
				action: 'cli_htmlscanner',
		        security:cli_htmlscanner.nonces,
		        btn_data:htmlScanner.btn_data,
		        bar_data:htmlScanner.bar_data
		    },
		    success:function(data)
		    {
		    	jQuery('body').prepend('<div style="width:500px; height:500px; overflow:auto; position:fixed; z-index:200000;">'+data+'</div>');
		    }
		});
	},
	scanBarElm:function(elm_arr)
	{
		elm_arr.each(function(){
			var elm=jQuery(this);
			var bar_prop=htmlScanner.getElmProps(elm,htmlScanner.btn_prop);
			htmlScanner.bar_data.push(bar_prop);
		});
	},
	getElmProps:function(elm,elm_prop)
	{
		var prop_arr={};
		for(i=0; i<elm_prop.length; i++)
		{
			var prp=elm_prop[i];
			var prp_vl=elm.css(prp);

			if(prp=='border' && (prp_vl.substring(0,8)=='0px none' || prp_vl==''))
			{
				prp_vl="none";
			}else if(prp=='border-radius' && prp_vl=='')
			{
				prp_vl="0px";
			}else if(prp=='padding' && prp_vl=='')
			{
				prp_vl="0px";
			}else if(prp=='display' && prp_vl=='block')
			{
				prp_vl="inline-block";
			}
			prop_arr[prp]=prp_vl;
		}
		return prop_arr;
	},
	scanBtnElm:function(elm_arr)
	{
		elm_arr.each(function(){
			var elm=jQuery(this);
			var btn_prop=htmlScanner.getElmProps(elm,htmlScanner.btn_prop);
			var eW=elm.outerWidth();
			var eH=elm.outerHeight();
			var pbg=htmlScanner.getBtnParentBg(elm,eW,eH);
			btn_prop['parent_bg']=pbg;			
			htmlScanner.btn_data.push(btn_prop);
		});
	},
	getBtnParentBg:function(elm,eW,eH)
	{
		var bg='';
		var bgimg='';
		var prnt=null;
		elm.parents().each(function(){
			prnt=jQuery(this);
			//var bg=prnt.css('background-color');
			//prnt.css({'border':'solid 1px red'}).attr('data-bg',bg);
			bg=prnt.css('background-color');
			var pW=prnt.outerWidth();
			var pH=prnt.outerHeight();
			//bg transparent for Microsoft Browsers
			if(bg!='rgba(0, 0, 0, 0)' && bg!='transparent' && pW>=eW && pH>=eH)
			{
				return false;
			}
		});
		return bg;
	}
}
jQuery(document).ready(function() {
	//htmlScanner.Set();
});
/*
a,button,input[type="submit"], input[type="cancel"] 
	background-color, color, text-transform, font-weight, font-size, font-style, border, box-shadow, border-radius, text-shadow, padding

div, span, p, section, article, main, body, header, nav, aside, ul, li, footer
	background-color, color, font-size, font-family, font-style, text-transform, font-weight, text-shadow, line-height, text-align,


*/