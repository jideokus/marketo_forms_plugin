(function() {
     /* Register the buttons */

     tinymce.create('tinymce.plugins.TSNMarketoForms', {
          init : function(ed, url) {
               /**
               * Inserts shortcode content
               */
               ed.addButton( '-tsn-mkto-form', {
                    title : 'Insert Marketo Form',
                    image : marketo_form_plugin_url+'images/button-icon.png',
                    onclick : function() {
                         ed.windowManager.open({
							title: 'Configure a Marketo form',
							body: [
								{type: 'listbox', name: 'form_id', label: 'Select a form',values:marketo_forms},
								{type:'textbox',name:'cpn_id',label: 'Marketo Campaign ID'},
								{type:'textbox',name:'heading',label: 'Form heading'},
								{type:'listbox',name:'popup',label: 'With popup?', values:[{'text':'No','value':'no'},{'text':'Yes','value':'yes'}]},
								{type:'textbox',name:'btn_text',label: 'Enter the button_text[For popup only]'},
								{type:'textbox',name:'lead_text',label: 'Enter the leading text [For popup and Long CTA style only]'},
								
								{type:'listbox',name:'style',label: 'Select the style [For popup only]', values:[{'text':'Small Red','value':'small_red'},{'text':'Long CTA','value':'long_cta'}]},
								
							],
							onsubmit: function(e) {
								
								var popup_meta = '';
								if(e.data.popup=='yes'){
									popup_meta+=" lead_text=\""+e.data.lead_text+"\"";
									popup_meta+=" btn_text=\""+e.data.btn_text+"\"";
									popup_meta+=" style=\""+e.data.style+"\"";
								}
								ed.insertContent("[mkto_form id=\"" + e.data.form_id+ "\" cpnid=\""+e.data.cpn_id+"\" heading=\""+e.data.heading+"\" popup=\"" + e.data.popup+"\""+popup_meta +"]");
							}
						});
                    }
               });
               
          },
          createControl : function(n, cm) {
               return null;
          },
     });
     
     tinymce.PluginManager.add( 'tsn_mkto_button_script', tinymce.plugins.TSNMarketoForms );
})();