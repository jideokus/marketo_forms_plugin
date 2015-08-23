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
								{type:'textbox',name:'cpn_id',label: 'Marketo Campaign ID (Separated by Commas)'},
								{type:'textbox',name:'heading',label: 'Form heading'},
								{type:'listbox',name:'with_labels',label: 'Show form labels?', values:[{'text':'No','value':'no'},{'text':'Yes','value':'yes'}]},
								{type:'textbox',name:'post_reg_url',label: 'Post form fill URL'},

								
							],
							onsubmit: function(e) {
								ed.insertContent("[tsn_mkto_form id=\"" + e.data.form_id+ "\" cpnid=\""+e.data.cpn_id+"\" post_reg=\"" + e.data.post_reg_url +"\" show_labels=\"" + e.data.with_labels + "\" heading=\""+e.data.heading+"\"]");
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