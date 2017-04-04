/**
 * javascripts for the entry-form-page in the admin-area
 *
 * @date 2012-07-29
 * @version 1.0
 */
 
 

/**
 * Script to insert a smiley in the textarea
 *
 * @param string smiley		the string for the smiley
 */
function insert_smiley(smiley, textarea_id) {
	var textfield = document.getElementById(textarea_id);
	textfield.focus();
	
	
	// -------------------------------- IE ---------------------------------
	
	if(typeof document.selection != 'undefined') {
	
		// insert smiley
		var range = document.selection.createRange();
		var selected_text = range.text;
		range.text = smiley + selected_text;
		
		// set new cursor-position
		range = document.selection.createRange();
		if (selected_text.length != 0) {
			range.moveStart('character', smiley.length + selected_text.length);      
		}
		range.select();
	}
	
	
	
	
	// ------------------------ gecko-based browsers ----------------------

	else if(typeof textfield.selectionStart != 'undefined'){
	
		// insert smiley
		var start = textfield.selectionStart;
		var end = textfield.selectionEnd;
		var selected_text = textfield.value.substring(start, end);
		textfield.value = textfield.value.substr(0, start) + smiley + selected_text + textfield.value.substr(end);
		
		// set new cursor-position
		var pos;
		if (selected_text.length == 0) {
			pos = start + smiley.length;
		} else {
			pos = start + smiley.length + selected_text.length;
		}
		textfield.selectionStart = pos;
		textfield.selectionEnd = pos;
	}
	
	
	
	// ---------------------------- other browsers -------------------------

	else{
		// insert smiley to the end of the string
		textfield.value = textfield.value + " " + smiley;
	}
}

