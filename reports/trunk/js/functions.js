
function fill_report_result(url_report)
{
//	alert(url_report);
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
		//console.log(fct_args);
		new Ajax.Request(url_report,
		{
		    method:'post',
		    parameters: { arguments : fct_args
						},
		        onSuccess: function(answer){
				//alert(answer.responseText);
				eval("response = "+answer.responseText);
				var div_to_fill = $('result_report');
				div_to_fill.innerHTML = response.content;
				eval(response.exec_js);
			}
		});
	}
}

function record_data(donnees){
	var path_manage_script = './reports/record_data.php';
    new Ajax.Request(path_manage_script,
	{
		method:'post',
		parameters: {
						data : donnees
					},
		onSuccess: function(response){
			eval("result = "+response.responseText);
			if(result.status == 1){
				window.location.assign("../../apps/maarch_entreprise/tmp/export_reports_maarch.csv");			
			} else {
				console.log(result.response);
			}
		}
	});
}
