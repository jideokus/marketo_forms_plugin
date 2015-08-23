var fieldCount =0;

function tsn_mkto_add_field(field){
	
	var field_name_value = "";
	var field_label_value = "";
	var field_type_value = "";
	var field_options_value = "";
	var field_validaton_value = "";
	var field_required_value = "required";
	if(field){
		field_name_value = field.field_name;
		field_label_value = field.field_label;
		field_type_value = field.field_type;
		field_options_value = field.field_options;
		field_validaton_value = field.field_validation;
		field_required_value = field.field_required;
	}
	var field_name = 'form_field[' + fieldCount +'][field_name]';
	var label_name = 'form_field[' + fieldCount +'][field_label]';
	var type_name = 'form_field[' + fieldCount +'][field_type]';
	var options_name = 'form_field[' + fieldCount +'][field_options]';
	var validation_name = 'form_field[' + fieldCount +'][field_validation]';
	var required_name = 'form_field[' + fieldCount +'][field_required]';
	var newField = '<div><h3>Field<table></h3>';
	var newField=newField+ '<tr><td><strong><label>Marketo Field Name</strong></label></td>';
	var selected = "";
	newField+='<td>'+
					'<select name="'+field_name+'">';
					jQuery.each(tsn_mkto_all_fields,function(value,title){
						if(value==field_name_value){
							selected="selected";
						}else{
							selected="";
						}
						newField+='<option value="'+value+'" ' + selected+'>'+title+'</option>';
					});
						
	newField+='</select></td></tr>';
	newField=newField+ '<tr><td><strong><label>Field Label</strong></label></td><td><input name="' + label_name + '" type="text" '+
					'value="'+ field_label_value + '" required/></td></tr>';
	newField=newField+ '<tr><td><strong><label>Type</strong></label></td><td><select name="'+type_name+'">';
	newField=newField+  '<option value="text" ';
							if(field_type_value =="text")
								newField+= "selected";
	newField=newField+ '>Text</option>';
	newField=newField+  '<option value = "text-area" ';
							if(field_type_value =="text-area")
								newField+= "selected";
	newField=newField+ '>Text Area</option>';
		newField=newField+  '<option value = "dropdown" ';
							if(field_type_value =="dropdown")
								newField+= "selected";
	newField=newField+ '>Dropdown</option>';
	
	newField=newField+  '<option value = "radio" ';
							if(field_type_value =="radio")
								newField+= "selected";
	newField=newField+ '>Radio Button</option>';
	newField=newField+  '<option value = "checkbox" ';
							if(field_type_value =="checkbox")
								newField+= "selected";
	newField=newField+ '>Checkbox</option>';
	newField=newField+  '</select></td></tr>';
	newField=newField+  '<tr><td><label><strong>Options</strong></label></td><td><textarea name="' + options_name + '">' + field_options_value + '</textarea></td></tr>';
	
	newField=newField+ '<tr><td><strong><label>Validation Rule</strong></label></td><td><select name="'+validation_name+'">';
	newField=newField+  '<option value="text" ';
							if(field_validaton_value =="text")
								newField+= "selected";
	newField=newField+ '>Text</option>';
	newField=newField+  '<option value = "email" ';
							if(field_validaton_value =="email")
								newField+= "selected";
	newField=newField+ '>Email</option>';
	newField=newField+  '<option value = "number" ';
							if(field_validaton_value =="number")
								newField+= "selected";
	newField=newField+ '>Number</option>';
	
	newField=newField+ '<tr><td><strong><label>Required</strong></label></td><td><input name="' + required_name +'"'+'value="required" type="checkbox"';
							if(field_required_value=="required")
									newField+=" checked ";
			newField=newField+'/></td></tr>';
	
	newField=newField+  '</table></div>';
	var remove_button = '<button type="button" class="button-primary" style="background:#ff0000;border:1px solid #ed1c24;margin-bottom:10px;">Remove</button>';
	jQuery("#tsn-mkto-form-fields").append(newField);
	var newFieldDiv = jQuery("#marketo-form-fields").children().last();
	newFieldDiv.append(remove_button);
	newFieldDiv.children().last().click(function(){
		jQuery(this).parent().remove();
		fieldCount--;
	});
	fieldCount++;
}

function validate_form_gen_fields(){

}