function InvalidMsg(inputbox) {
    if (inputbox.value == '') {
        inputbox.setCustomValidity('Required value !');
    }
    else if(inputbox.validity.rangeOverflow){
		var updated_overflow_text = invalid_html_five_message.rangeOverflow;
		updated_overflow_text = updated_overflow_text.replace("%max_per_item_quantity%", inputbox.max);
		updated_overflow_text = updated_overflow_text.replace("%item_quantity%", inputbox.value);
        inputbox.setCustomValidity(updated_overflow_text);
    }
	else if(inputbox.validity.rangeUnderflow){
		var updated_underflow_text = invalid_html_five_message.rangeUnderflow;
		updated_underflow_text = updated_underflow_text.replace("%min_per_item_quantity%", inputbox.min);
		updated_underflow_text = updated_underflow_text.replace("%item_quantity%", inputbox.value);
        inputbox.setCustomValidity(updated_underflow_text);
    }
    else {
        inputbox.setCustomValidity('');
    }
    return true;
}