@include('layouts.common-header')
	<script rel="text/javascript" src="https://cdn.jsdelivr.net/npm/powerbi-client@2.6.5/dist/powerbi.min.js"></script>
	<label>Ref: <a target="_blank" href="https://microsoft.github.io/PowerBI-JavaScript/demo/v2-demo/index.html">https://microsoft.github.io/PowerBI-JavaScript/demo/v2-demo/index.html</a></label>
	<div id="reportContainer"></div>

	<script>

	    function triggerReport(){
	    	var accessToken = "{{env('accessToken')}}";
		    var embedUrl = "{{env('embedUrl')}}";
		    var embedReportId = "{{env('embedReportId')}}";
		    var models = window['powerbi-client'].models;
		    var config = {
		        type: 'report',
		        tokenType: models.TokenType.Embed,
		        accessToken: accessToken,
		        embedUrl: embedUrl,
		        id: embedReportId,
		        permissions: models.Permissions.All,
		        settings: {
		            filterPaneEnabled: true,
		            navContentPaneEnabled: true
		        }
		    };
		    var reportContainer = $('#reportContainer')[0];
		    var report = powerbi.embed(reportContainer, config);
	    }
	    triggerReport();

	</script>
