//
function convertSize()
{
	if(!isNaN($('size_limit_number').value))
	{
		if($('size_format').value == "MB")
		{
			$('size_limit_number').value = $('size_limit_hidden').value / (1000 * 1000);
			$('actual_size_number').value = $('actual_size_hidden').value / (1000 * 1000);
		}
		if($('size_format').value == "GB")
		{
			$('size_limit_number').value = $('size_limit_hidden').value / (1000 * 1000 * 1000);
			$('actual_size_number').value = $('actual_size_hidden').value / (1000 * 1000 * 1000);
		}
		if($('size_format').value == "TB")
		{
			$('size_limit_number').value = $('size_limit_hidden').value / (1000 * 1000 * 1000 * 1000);
			$('actual_size_number').value = $('actual_size_hidden').value / (1000 * 1000 * 1000 * 1000);
		}
	}
	else
	{
		window.alert('WRONG FORMAT');
	}
}

function saveSizeInBytes()
{
	if(!isNaN($('size_limit_number').value))
	{
		//$('size_limit_hidden').value = $('size_limit_number').value;
		if($('size_format').value == "MB")
		{
			$('size_limit_hidden').value = $('size_limit_number').value * (1000 * 1000);
		}
		if($('size_format').value == "GB")
		{
			$('size_limit_hidden').value = $('size_limit_number').value * (1000 * 1000 * 1000);
		}
		if($('size_format').value == "TB")
		{
			$('size_limit_hidden').value = $('size_limit_number').value * (1000 * 1000 * 1000 * 1000);
		}
	}
	else
	{
		window.alert('WRONG FORMAT');
	}	
}

function hide_index(mode_hide, display_val) {
	var displayVal = $(display_val);
	if(mode_hide == true) {
		if(displayVal) {
			Element.setStyle(displayVal, {display : 'none'});
		}
	} else {
		if(displayVal) {
			Element.setStyle(displayVal, {display : 'block'});
		}
	}
}
