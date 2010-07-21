//
function convertSize()
{
	if(!isNaN($('size_limit').value))
	{
		if($('size_format').value == "MB")
		{
			$('size_limit').value = $('size_limit_hidden').value / (1000 * 1000);
			$('actual_size').value = $('actual_size_hidden').value / (1000 * 1000);
		}
		if($('size_format').value == "GB")
		{
			$('size_limit').value = $('size_limit_hidden').value / (1000 * 1000 * 1000);
			$('actual_size').value = $('actual_size_hidden').value / (1000 * 1000 * 1000);
		}
		if($('size_format').value == "TB")
		{
			$('size_limit').value = $('size_limit_hidden').value / (1000 * 1000 * 1000 * 1000);
			$('actual_size').value = $('actual_size_hidden').value / (1000 * 1000 * 1000 * 1000);
		}
	}
	else
	{
		window.alert('WRONG FORMAT');
	}
}

function saveSizeInBytes()
{
	if(!isNaN($('size_limit').value))
	{
		//$('size_limit_hidden').value = $('size_limit').value;
		if($('size_format').value == "MB")
		{
			$('size_limit_hidden').value = $('size_limit').value * (1000 * 1000);
		}
		if($('size_format').value == "GB")
		{
			$('size_limit_hidden').value = $('size_limit').value * (1000 * 1000 * 1000);
		}
		if($('size_format').value == "TB")
		{
			$('size_limit_hidden').value = $('size_limit').value * (1000 * 1000 * 1000 * 1000);
		}
	}
	else
	{
		window.alert('WRONG FORMAT');
	}
}