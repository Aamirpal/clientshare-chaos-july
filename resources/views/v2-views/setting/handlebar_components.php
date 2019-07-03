<script id="power_bi_reports_list" type="text/x-handlebars-template">
<div class="tablerow tablerow-detail">
	<div class="tablecell number-wrap"><span>{{index}}</span></div>
	<div class="tablecell report-name-wrap"><span>{{report_name}}</span></div>
	<div class="tablecell report-type-wrap"><span>{{report_type}}</span></div>
	<div class="tablecell createdon-wrap"><span>{{created_at}}</span></div>
	<div class="tablecell action-wrap"><span class="remove_report remove-report" data-report-id={{id}}>Remove</span></div>
</div>
</script>