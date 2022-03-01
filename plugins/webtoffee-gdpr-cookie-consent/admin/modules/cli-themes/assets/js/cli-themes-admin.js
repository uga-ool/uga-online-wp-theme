(function( $ ) {
	'use strict';
	$(function() {
		var CLI_themes=
		{
			hexDigits:new Array
			        ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"),
			propertyBxTmr:null,
			current_elm:null,
			shortcode_arr:['[cookie_button]','[cookie_link]','[cookie_reject]','[cookie_settings]','[cookie_close]','[cookie_accept_all]'],
			Set:function()
			{				
				this.bindCustomize();
				this.propertyBoxProp();
				this.initiCookieBar();
				//save the theme
				this.saveTheme();
				//button and heading text change event
				this.attachHdBtnTxtChange();
				this.bindEsc();
			},
			initiCookieBar : function ()
			{	

				var barElm = jQuery('.wt-cli-cookie-bar');
				barElm.each(function(){
					var currElm = jQuery(this);
					var templateElm = currElm.find('.wt-cli-template');
					var templateID = '';
					if(templateElm.length > 0)
					{	
						var templateClasses = templateElm.attr('class');
						var templateClasses = templateClasses.split(" ");
						var matchingID = templateClasses.filter(function (value) {

							return value.match(/\bcli-style/);
							
						});
						templateID = matchingID[0];
						currElm.attr('data-template-id',templateID);
					}
				});
				
			},
			propertyBoxProp:function()
			{
				//draggable
				cli_theme_dragElement(document.getElementById("cli_themesidebox"),$(window).width());
				
				//property box functions
				$('.cli_themesidebox_head_resize').click(function(){
					var elm=$('.cli_themesidebox');
					var elm_con=elm.find('.cli_themesidebox_content');
					if($(this).html()=='-')
					{
						elm_con.hide();
						elm.stop(true,true).animate({'height':50},200);
						$(this).html('+');
					}else
					{
						elm_con.show();
						elm.stop(true,true).animate({'height':elm.attr('data-height')+'px'},200);
						$(this).html('-');
					}
				});
				$('.cli_themesidebox_head_close, [name="cli_theme_prop_apply"]').click(function(){
					$('.cli_themesidebox').hide();					
				});
				this.propertyBoxEvents();
			},
			propertyBoxEvents:function()
			{
				//adding color picker and onchange

				$('.cli_theme-color-field').each(function(){
					$(this).wpColorPicker({
						clear: function(event) {

						},
						change:function(event,ui)
						{
							var rgb=ui.color.toRgb();
							var elm=$(event.target);
							var prnt=elm.parents('.cli_theme_form_group');
							$('.wp-picker-clear').remove();
							if(prnt.find('.wp-picker-input-wrap').find('.cli_theme_alpha').length==0)
							{
								$('<input type="number" class="cli_theme_alpha" style="" step=".1" min="0" max="1">').insertAfter(prnt.find('.wp-picker-input-wrap').find('.cli_theme-color-field'));
							}
							prnt.find('.wp-picker-input-wrap').find('.cli_theme_alpha').val(ui.color._alpha);
							var c_alpha=ui.color._alpha;
							prnt.find('.wp-picker-input-wrap').find('.cli_theme_alpha').unbind('change').on('change',function(e){
								var rgba='rgba('+rgb['r']+','+rgb['g']+','+rgb['b']+','+$(this).val()+')';
								elm.val(rgba);
								CLI_themes.attachOnchange(elm,rgba);
							});
							var rgba='rgba('+rgb['r']+','+rgb['g']+','+rgb['b']+','+c_alpha+')';
							CLI_themes.attachOnchange(elm,rgba);
						}
					});
				});

				$('.cli_theme_num_input').on('change',function(){
					CLI_themes.attachOnchange($(this),$(this).val());
				});

				$('.cli_theme_txt_input').on('keyup',function(){
					var elm=$(this);
					var vl=elm.val();
					var prvew_elm=$('.cli_theme_customizecontent');
					var hid_elm=$('.cli_theme_hidden_vals');
					var data_id=CLI_themes.current_elm.attr('data-cli-id'); 
					if(data_id=='cli_theme_hd')
					{
						hid_elm.find('[name="cli_theme_hid_heading"]').val(vl);
						$('[name="bar_heading_text_field"]').val(vl);						
						CLI_themes.addBannerHeader(prvew_elm); //adding heading text if available
					}else
					{
						var name_arr=data_id.split('_'); //sample: cli_theme_button_1
						hid_elm.find('[name="cli_theme_hid_button_'+name_arr[3]+'"]').val(vl);
						$('[name="button_'+name_arr[3]+'_text_field"]').val(vl);
					}
					$('.cli_theme_customizecontent').find('[data-cli-id="'+data_id+'"]').html(vl);
				});

				$('.cli_theme_check_input').on('click',function(){
					var elm=$(this);
					var vl=elm.is(':checked') ? elm.attr('data-on') : elm.attr('data-off')
					CLI_themes.attachOnchange(elm,vl);
				});

				$('.cli_theme_css_txt').on('change',function(){
					CLI_themes.applyElmStyle(CLI_themes.current_elm);
				});
			},
			bindCustomize:function()
			{
				this.showThemes();

				$('[name="cli_theme_radio"]').click(function(){
					CLI_themes.showPopupBtn($(this));
				});

				$('.cli_themebox_main').find('.cli_theme_bar').click(function(){
					$(this).parents('.cli_themebox_main').find('[name="cli_theme_radio"]').click();
				});

				var cur_theme_cont=$('.cli_current_theme');
				CLI_themes.addCustomizeHtml(null,cur_theme_cont);
				var subtab_hash='theme-'+cur_theme_cont.attr('data-cli-bartype');
				$('.cli_themes_popup .cli_sub_tab').find('li[data-target="'+subtab_hash+'"]').click();
				$('.cli_theme_customizewrnmsg').hide();
				this.hidePopupBtn();
				$('[name="cli_theme_radio"]').prop('checked',false);

				$('[name="cli_theme_customize"]').click(function(){
					var checked_elm=$('[name="cli_theme_radio"]:checked');
					if(checked_elm.length>0)
					{
						CLI_themes.addCustomizeHtml(checked_elm);
						$('.cli_themes_popup, .cli_themes_popup_overlay').hide();
					}else{
						alert('Please choose a template.');
					}					
				});
			},
			setCheckBoxPos:function()
			{
				$('.cli_themebox_main').each(function(){
					var h=$(this).find('.cli_theme_bar').outerHeight()-10;
					if(h>0){
					$(this).find('.cli_themeboxbar_sub_left').css({'padding-top':(h/2)+'px'});
					
					}
				});
			},
			themeTab:function()
			{
				$('.cli_themes_popup .cli_sub_tab li').unbind('click').click(function(){
					CLI_themes.themeTabShow($(this));
				});
				/*
				$('.cli_themes_popup .cli_sub_tab').each(function(){
					var elm=$(this).children('li').eq(0);
					elm.click();
				});
				*/
			},
			themeTabShow:function(elm)
			{
				var trgt=elm.attr('data-target');
				var ppelm=elm.parents('.cli_themes_popup');
				var prnt=ppelm.find('.cli_sub_tab');
				var lielm=prnt.find('li[data-target="'+trgt+'"]')
				

				prnt.find('li a').css({'color':'#0073aa','cursor':'pointer'});					
				lielm.find('a').css({'color':'#333','cursor':'default'});
				
				prnt.find('li').css({'background':'none'});
				lielm.css({'background':'#fff'});

				ppelm.find('.cli_sub_tab_content').hide();
				ppelm.find('.cli_sub_tab_content[data-id="'+trgt+'"]').show();
				this.setCheckBoxPos();
			},
			showThemes:function()
			{
				$('.cli_theme_show_themes').click(function(){
					CLI_themes.resizePopup();
					$('.cli_themes_popup, .cli_themes_popup_overlay').show();
					CLI_themes.attachPopupResize();
					CLI_themes.themeTab();
					CLI_themes.showCurrentTab();
					CLI_themes.attachPopClose();
				});
			},
			showCurrentTab:function()
			{
				if($('[name="cli_theme_radio"]:checked').length>0)
				{
					var checked_elm=$('[name="cli_theme_radio"]:checked');
					var subtab_hash=checked_elm.parents('.cli_sub_tab_content').attr('data-id');
					$('.cli_themes_popup .cli_sub_tab').find('li[data-target="'+subtab_hash+'"]').click();
				}else
				{
					var cur_theme_cont=$('.cli_current_theme');
					var subtab_hash='theme-'+cur_theme_cont.attr('data-cli-bartype');
					$('.cli_themes_popup .cli_sub_tab').find('li[data-target="'+subtab_hash+'"]').click();
				}
			},
			bindEsc:function()
			{
				$(document).on('keyup',function(e){
					if(e.keyCode===27 && $('.cli_themes_popup').is(':visible'))
					{
						$('.cli_theme_popup_close').click();
					}
				});
			},
			attachPopClose:function()
			{
				$('.cli_theme_popup_close, .cli_theme_popup_cancel').unbind('click').click(function(){
					$('[name="cli_theme_radio"]').prop('checked',false);
					CLI_themes.hidePopupBtn();
					$('.cli_themes_popup, .cli_themes_popup_overlay').hide();
				});
			},
			showPopupBtn:function(elm)
			{
				$('[name="cli_theme_customize"]').prop('disabled',false);
				var theme_id=elm.val().toString().replace('theme_','');
				$('[name="cli_theme_live_preview"]').attr('href',cli_theme_vars.home_url+'?cli_live_theme_preview='+theme_id).css({'opacity':'1','cursor':'pointer'});
			},
			hidePopupBtn:function()
			{
				$('[name="cli_theme_customize"]').prop('disabled',true);
				$('[name="cli_theme_live_preview"]').attr('href','Javascript:void(0);').css({'opacity':'.5','cursor':'default'});
			},
			resizePopup:function()
			{
				var popup_elm=$('.cli_themes_popup');
				var wv=$(window).width()-30;
				var wh=$(window).height()-30;
				if(wv<1000)
				{
					$('.cli_themes_popup_left').hide();
					$('.cli_themes_popup_head .cli_sub_tab').show();
					$('.cli_themes_popup_head').css({'height':'83px'});
					$('.cli_themes_popup_right').css({'width':'100%'});
				}else
				{
					$('.cli_themes_popup_left').show();
					$('.cli_themes_popup_head .cli_sub_tab').hide();
					$('.cli_themes_popup_head').css({'height':'60px'})
					$('.cli_themes_popup_right').css({'width':'85%'});
				}
				var hh=parseInt($('.cli_themes_popup_head').height());
				var fh=parseInt($('.cli_themes_popup_footer').height());
				var cw=wh-(hh+fh+62);					
				$('.cli_themes_popup, .cli_themes_popup_left, .cli_themes_popup_right').css({'height':wh+'px'});
				$('.cli_themes_popup_right .cli_sub_tab_content, .cli_themeboxbar').css({'height':cw+'px'});
				popup_elm.css({'width':wv});				
			},
			attachPopupResize:function()
			{
				clearTimeout(cli_theme_resizeTmr);
				var cli_theme_resizeTmr=null;
				$(window).resize(function() {
					clearTimeout(cli_theme_resizeTmr);
					cli_theme_resizeTmr=setTimeout(function()
					{
						CLI_themes.resizePopup();
					},500);
				});	
			},
			attachHdBtnTxtChange:function()
			{
				var prvew_elm=$('.cli_theme_customizecontent');
				var hid_elm=$('.cli_theme_hidden_vals');
				$('[name="bar_heading_text_field"]').on('keyup',function(){
					hid_elm.find('[name="cli_theme_hid_heading"]').val($(this).val());
					CLI_themes.addBannerHeader(prvew_elm);
				});
				for(var e=1; e<=7; e++)
				{	
					if( e == 6 ) {
						continue;
					}
					$('[name="button_'+e+'_text_field"]').on('keyup',function(){
						var name_arr=$(this).attr('name').split('_');
						var vl=$(this).val();
						hid_elm.find('[name="cli_theme_hid_button_'+name_arr[1]+'"]').val(vl);
						prvew_elm.find('.cli_theme_button_'+name_arr[1]).html(vl);
					});
				}				
			},
			saveTheme:function() 
			{
				$('[name="cli_activate_theme"]').click(function(){
					
					var frm=$('.cli_theme_customizecontent');
					var hid_elm=$('.cli_theme_hidden_vals');
					//setting nonce and ajax action
					var data_obj = {
			            action:'cli_theme',
			            security:cli_theme_vars.nonces.cli_theme,
			            cli_theme_action:'save_theme',
			        };

					//fecthing button styles
					for(var e=1; e<=7; e++)
					{	
						if( e == 6 ) {
							continue;
						}
						var btn_name='button_'+e;
						var btn_elm=frm.find('.cli_theme_'+btn_name);
						if(btn_elm.length>0)
						{
							var st=btn_elm.attr('style');
							//add bg color as last element
							st+=';background-color:'+btn_elm.css('background-color')+';';
							data_obj[btn_name+'_style']=st;
						}else
						{
							data_obj[btn_name+'_style']='';
						}
						data_obj[btn_name+'_txt']=hid_elm.find('[name="cli_theme_hid_'+btn_name+'"]').val();
					}
					var bar_elm=frm.find('.cli_theme_bar');
					var bar_st=bar_elm.attr('style');
					//add bg color as last element
					bar_st+=';background-color:'+bar_elm.css('background-color')+';';
					var bar_hd_st=frm.find('.cli_theme_hd').attr('style');

					data_obj['bar_style']=bar_st;
					data_obj['bar_hd_style']=bar_hd_st;
					data_obj['bar_hd_txt']=hid_elm.find('[name="cli_theme_hid_heading"]').val();
					data_obj['bar_type']=bar_elm.attr('data-cli-bartype');
					data_obj['bar_txt']=$('.cli_theme_box_txt').val();

					$('.cli_theme_customizebutton button').prop('disabled',true).css('opacity','.5');
					$('.cli_theme_customizebutton .spinner').css('visibility','visible');
					$.ajax({
						url: cli_theme_vars.ajax_url,
			            data: data_obj,
			            dataType:'json',
			            type: 'POST',
			            success: function (data) 
			            {
			                $('.cli_theme_customizebutton button').prop('disabled',false).css('opacity',1);
							$('.cli_theme_customizebutton .spinner').css('visibility','hidden');
			                if(data.response===true)
			                {
			                	cli_notify_msg.success(cli_theme_vars.labels.success);
			                	setTimeout(function(){
									window.location.reload(true);
								},1000);
			                }else
			                {
			                	cli_notify_msg.error(cli_theme_vars.labels.error);
			                }
			            },
			            error:function()
			            {
			            	$('.cli_theme_customizebutton button').prop('disabled',false).css('opacity',1);
							$('.cli_theme_customizebutton .spinner').css('visibility','hidden');
			            	cli_notify_msg.error(cli_theme_vars.labels.error);
			            }
					});
				});
			},
			attachOnchange:function(elm,vl)
			{
				var css_prop=elm.attr('data-type');
				var css_unit=elm.attr('data-unit');
				var data_id=CLI_themes.current_elm.attr('data-cli-id');
				$('.cli_theme_customizecontent').find('[data-cli-id="'+data_id+'"]').css(css_prop,vl+css_unit);
				CLI_themes.showElmStyle(CLI_themes.current_elm);
			},
			renderContentOnTxtUpdate:function()
			{
				$('.cli_theme_box_txt, [name="notify_message_field"]').unbind('keyup').on('keyup',function(){
					var shortcode_arr=CLI_themes.shortcode_arr;
					var v=0;
					var vl=$(this).val();
					$('.cli_theme_box_txt, [name="notify_message_field"]').val(vl);
					var html=CLI_themes.nl2br($(this).val());
					var prnt=$('.cli_theme_customizecontent .cli_theme_buttons');
					var bar_type=$('.cli_theme_customizecontent .cli_theme_bar').attr('data-cli-bartype');
					var bar_hd_html=$('<div>').append(prnt.find('.cli_theme_hd').clone()).html();
					html=bar_hd_html+html;
					if(html.indexOf('[cookie_close]')!=-1)
					{
						html=html.replace('[cookie_close]','');
						html='[cookie_close]'+html;
					}					
					for(var e=0; e<shortcode_arr.length; e++)
					{
						v++;
						var shortcode=shortcode_arr[e];
						var btn_html=$('<div>').append(prnt.find('.cli_theme_button_'+v).clone()).html();
						html=html.replace(shortcode,btn_html);
					}
					var prvew_elm=$('.cli_theme_customizecontent');					
					prvew_elm.find('.cli_theme_bar').html(html);
					CLI_themes.addBannerHeader(prvew_elm);
					CLI_themes.bindShowProp();
				});				
			},
			addCustomizeHtml:function(elm,cntnt_elm)
			{
				if(cntnt_elm)
				{
					
				}else
				{
					var prnt=elm.parents('.cli_themebox_main');
					var cntnt_elm=prnt.find('.cli_themebox_sub_right');
				}
				this.updateThemeTxtWithUserTxt(cntnt_elm);
				this.bindShowProp();				
			},
			strip:function(html)
			{
				var tmp = document.createElement("DIV");
				tmp.innerHTML = html;
				return tmp.textContent || tmp.innerText || "";
			},
			updateThemeTxtWithUserTxt:function(theme_elm)
			{
				var tmpElm=$('<div />');
				var bar_html=theme_elm.html();
				tmpElm.html(bar_html);
				var bar_content=tmpElm.find('.cli_theme_bar_txt').html();
				var theme_txt=this.getTxtWithoutShortcode(bar_content);
				
				var usr_content=$('[name="notify_message_field"]').val();
				var usr_txt=this.getTxtWithoutShortcode(usr_content);
				usr_txt = this.strip(usr_txt);
				theme_txt = this.strip(theme_txt);
				bar_html=bar_html.replace(theme_txt,usr_txt);
				var bar_txt=bar_content.replace(theme_txt,usr_txt);

				var prvew_elm=$('.cli_theme_customizecontent');
				prvew_elm.html(bar_html);
				
				//update button and heading text with current text
				var hid_elm=$('.cli_theme_hidden_vals');
				for(var e=1; e<=7; e++)
				{	
					if( e == 6 ) {
						continue;
					} 
					var btn_name='button_'+e;
					var vl=hid_elm.find('[name="cli_theme_hid_'+btn_name+'"]').val();
					prvew_elm.find('.cli_theme_'+btn_name).html(vl);
				}

				CLI_themes.addBannerHeader(prvew_elm); //adding heading text if available
				$('.cli_theme_customizemsg, .cli_theme_customizebutton').show();
				$('.cli_theme_box_txt').val(bar_txt);
			},
			getTxtWithoutShortcode:function(txt)
			{
				var shortcode_arr=this.shortcode_arr
				txt=txt.replace(/\[cookie_button\]/g,'');
				txt=txt.replace(/\[cookie_reject\]/g,'');
				txt=txt.replace(/\[cookie_settings\]/g,'');
				txt=txt.replace(/\[cookie_close\]/g,'');
				txt=txt.replace(/\[cookie_link\]/g,'');
				txt=txt.replace(/\[wt_cli_category_widget\]/g,'');
				txt=txt.replace(/\[cookie_accept_all\]/g,'');
				return $.trim(txt);
			},
			addBannerHeader:function(prvew_elm)
			{
				var hdval=$.trim($('.cli_theme_hidden_vals').find('[name="cli_theme_hid_heading"]').val());
				if(hdval!='')
				{
					prvew_elm.find('.cli_theme_hd').css('display','block').html(hdval);
				}else
				{
					prvew_elm.find('.cli_theme_hd').css('display','none');
				}
			},
			bindShowProp:function()
			{
				$('.cli_theme_customizecontent').find('[class^=cli_theme_]').unbind('click').click(function(e){
					e.stopPropagation();
					CLI_themes.current_elm=$(this);
					$('.cli_theme_alpha').remove();
					$('.cli_theme_property_namehd').text(' ('+$(this).attr('title')+')');
					CLI_themes.showElmProps(CLI_themes.current_elm);
					CLI_themes.showPropertyBox(CLI_themes.current_elm);
					CLI_themes.showElmStyle(CLI_themes.current_elm);
				});
			},
			getCSSProp:function(elm,prop)
			{
				var stle=elm.attr('style');
				var stle_arr=stle.split(';');
				var stprop_arr='';
				for(var w=0; w<stle_arr.length; w++)
				{
					stprop_arr=stle_arr[w].split(':');
					if($.trim(stprop_arr[0])==prop)
					{
						return trim(stprop_arr[1]);
					}
				}
				return '';
			},
			showElmProps:function(elm)
			{
				var cli_id=elm.attr('data-cli-id');
				if(cli_id!='cli_theme_bar' && cli_id!='cli_theme_button_5')
				{
					$('.cli_themesidebox .cli_theme_item_text').show().find('[name="cli_theme_text"]').val(elm.text());
					$('.cli_themesidebox').css({'height':'561px'});
				}else
				{
					$('.cli_themesidebox .cli_theme_item_text').hide();
					$('.cli_themesidebox').css({'height':'510px'});
				}

				var font_size=parseInt(elm.css('font-size'));				
				$('[name="cli_theme_fontsize"]').val(font_size);

				var bg_c=elm.css('background-color');
				$('[name="cli_theme_bgc"]').val(bg_c);
				$('[name="cli_theme_bgc"]').iris('color',bg_c);

				var tc=elm.css('color');
				$('[name="cli_theme_tc"]').val(tc);
				$('[name="cli_theme_tc"]').iris('color',tc);

				var bc=elm.css('border-left-color');
				$('[name="cli_theme_bc"]').val(bc);
				$('[name="cli_theme_bc"]').iris('color',bc);

				var bw=parseInt(elm.css('border-left-width'));
				$('[name="cli_theme_bw"]').val(bw);

				var cr=parseInt(elm.css('border-top-left-radius'));
				$('[name="cli_theme_cr"]').val(cr);
				
				var fw=parseInt(elm.css('font-weight'));
				$('[name="cli_theme_fw"]').val(fw);

				if(elm.css('font-style')=='italic')
				{
					$('#cli_theme_italic_chk').prop('checked',true);
				}else
				{
					$('#cli_theme_italic_chk').prop('checked',false);
				}
				var text_decoration=elm.css('text-decoration');
				if(text_decoration.search('underline')!=-1)
				{
					$('#cli_theme_underline_chk').prop('checked',true);
				}else
				{
					$('#cli_theme_underline_chk').prop('checked',false);
				}
			},
			showElmStyle:function(elm)
			{
				var css=elm.attr('style');
				css=css.replace(/\;/g, '; \n');
				$('[name="cli_theme_custm_css"]').val(css);
			},
			applyElmStyle:function(elm)
			{
				var css=$('[name="cli_theme_custm_css"]').val();
				css=css.replace(/\; \n/g, ';');
				var data_id=elm.attr('data-cli-id');
				$('.cli_theme_customizecontent').find('[data-cli-id="'+data_id+'"]').attr("style",css);
				//elm.attr('style',css);
				setTimeout(function(){
					CLI_themes.showElmProps(elm);
				},1000);				
			},
			showPropertyBox:function(elm)
			{
				clearInterval(CLI_themes.propertyBxTmr);
				var lt=elm.offset().left;
				var themebx=$('.cli_theme_customizecontent .cli_theme_bar');
				var sidebx_elm=$('.cli_themesidebox');
				//var tp=themebx.offset().top+themebx.height();
				var tp=elm.offset().top+elm.height();
				sidebx_elm.attr('data-height',sidebx_elm.height());
				if(!sidebx_elm.is(':visible'))
				{
					if(elm.attr('data-cli-bartype')!='banner')
					{
						tp=tp-150;
					}
					sidebx_elm.css({'top':tp}).fadeIn();
				}
				
				//open sidebox if it was in minified
				var elm_con=sidebx_elm.find('.cli_themesidebox_content');
				elm_con.show();
				sidebx_elm.stop(true,true).animate({'height':sidebx_elm.attr('data-height')+'px'},200);
				$('.cli_themesidebox_head_resize').html('-');

				//closing the property box when the element is removed/hided.				
				CLI_themes.propertyBxTmr=setInterval(function(){
					if(elm.length==0 || elm.is(':visible')==false)
					{
						sidebx_elm.hide();
						clearInterval(CLI_themes.propertyBxTmr);
					}
				},300);
			},
			nl2br:function(str, is_xhtml) {
			    if (typeof str === 'undefined' || str === null) {
			        return '';
			    }
			    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
			    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
			},
			rgb2hex:function(rgb)
			{
			 	if (  rgb.search("rgb") == -1 ) {
			          return rgb;
			     } else {
			          rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);
			          function hex(x) {
			               return ("0" + parseInt(x).toString(16)).slice(-2);
			          }
			          return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]); 
			     }
			},
			hex:function(x) {
			  return isNaN(x) ? "00" : this.hexDigits[(x - x % 16) / 16] + this.hexDigits[x % 16];
			}
		}
		CLI_themes.Set();

	});
})( jQuery );

function cli_theme_dragElement(elmnt,Ww) 
{
	  var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
	  if (document.getElementById(elmnt.id + "header")) {
	    // if present, the header is where you move the DIV from:
	    document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
	  } else {
	    // otherwise, move the DIV from anywhere inside the DIV:
	    elmnt.onmousedown = dragMouseDown;
	  }

	  function dragMouseDown(e) {
	    e = e || window.event;
	    e.preventDefault();
	    // get the mouse cursor position at startup:
	    pos3 = e.clientX;
	    pos4 = e.clientY;
	    document.onmouseup = closeDragElement;
	    // call a function whenever the cursor moves:
	    document.onmousemove = elementDrag;
	  }

	  function elementDrag(e) {
	    e = e || window.event;
	    e.preventDefault();
	    // calculate the new cursor position:
	    pos1 = pos3 - e.clientX;
	    pos2 = pos4 - e.clientY;
	    pos3 = e.clientX;
	    pos4 = e.clientY;
	    var tp=elmnt.offsetTop - pos2;
	    var lt=elmnt.offsetLeft - pos1;
	    if(tp<40)
	    {
	    	tp=40;
	    }
	    if(lt<10)
	    {
	    	lt=elmnt.style.left;
	    }else if(lt>(Ww-640))
	    {
	    	lt=(Ww-640);
	    }

	    // set the element's new position:
	    elmnt.style.top = tp + "px";
	    elmnt.style.left = lt + "px";
	  }

	  function closeDragElement() {
	    // stop moving when mouse button is released:
	    document.onmouseup = null;
	    document.onmousemove = null;
	  }
}