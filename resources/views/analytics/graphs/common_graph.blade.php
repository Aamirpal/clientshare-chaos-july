<div id="{{$view_data['graph_div_id']}}" class='common-graph-layout'>
</div>
<div class="map-nav">
  <ul class="{{$view_data['graph_legends_class']}}">
  </ul>
</div>
<script>
	var view_data = {!!json_encode($view_data)!!};
	var graph_data_point_check = {{config('constants.GRAPH.DATA_POINT')}};
	drawAnalyticsGraph();
</script>