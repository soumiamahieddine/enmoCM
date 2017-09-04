
function fill_report_result(url_report)
{
	if(url_report)
	{
		var fct_args  = '';
		if(url_report.indexOf('?') != -1)
		{
			var tmp = url_report.slice(url_report.indexOf('?')+1 );
			var args = tmp.split('&');
			var tmp2;
			for(var i=0; i < args.length; i++)
			{
				tmp2 = args[i].split('=');
				fct_args += tmp2[0]+'#'+tmp2[1]+'$$';
			}
		}
		/*
		{
			url : url_report,
		    type : 'POST',
		    data: { arguments : fct_args
						},
		        success: function(answer){
				//alert(answer.responseText);
				eval("response = "+answer);
				var div_to_fill = $j('#result_report');
				div_to_fill.html(response.content);
				eval(response.exec_js);
			}
		});*/

		$j.ajax(
			{
				url: url_report,
				type: 'POST',
				data: {
					arguments : fct_args,
				},
				success: function(answer){
					//console.log(answer);
					eval("response = "+answer);
					$j('#result_report').html(response.content);
					eval(response.exec_js);
				},

				error : function(answer)
				{
					alert(error);
				}

			}
		)
	}
}

function record_data(url)
{
	//var path_manage_script = url;
    /*new Ajax.Request(path_manage_script,
	{
		method:'post',
		onSuccess: function(response){
			eval("result = "+response.responseText);
			if(result.status == 1){
				window.location.assign("index.php?page=export&display=true&origin=graph");		
			} else {
				console.log(result.response);
			}
		}
	});*/
	$j.ajax({
		url: url,
		type: 'POST',
		data: {
			data : donnees
		},
		success: function(answer){
			eval("result = "+answer);
			if(result.status ==1){
				window.location.assign("index.php?page=export&display=true&origin=graph");
			}
			else{
				console.log(result.response);
			}
		}
	})
}
