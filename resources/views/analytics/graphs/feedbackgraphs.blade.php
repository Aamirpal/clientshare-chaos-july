<div id="feedback_line_graph" style="height:290px; float: left; width: 100%;"></div>
<script>
    
var nps_x_label = feedback_graph_data.length == 2?'day':'month';

$(document).ready(function(){
Morris.Line({
  element: 'feedback_line_graph',
  data: feedback_graph_data,
  xkey: 'month',
  ykeys: ['value'],
  labels: [''],
  xLabels: nps_x_label,
  xLabelFormat: function(x) { 
    var month = graph_months[x.getMonth()];
    return month;
   },
  yLabelFormat: function(x) { // <--- x.getMonth() returns valid index
    if(x % 1 === 0){
      return x;  
    }
    return 0;
  },
  dateFormat: function(x) {
    var month = graph_months[new Date(x).getMonth()];
    return month;
  },
  eventLineColors:['black'],
  goalLineColors:['black'],
  grid:false,
  goals:[0],
  events:[feedback_graph_data[0].month],
  hideHover:'auto',
  hoverCallback: function (index, options, content, row) {
    var dt = new Date( feedback_graph_data[index].month );
    var month = graph_months[dt.getMonth()];
    var val = feedback_graph_data[index].msg?(feedback_graph_data[index].msg):(feedback_graph_data[index].value)
    return '<div style="padding: 8px 14px; background-color: #fff;box-shadow: 0 2px 4px 0 rgba(0,0,0,0.16); border-radius: 2px; display: inline-block;"><span style="font-size: 24px;font-weight: 600;text-align: center;line-height: 24px;color: #0D47A1;">'+val+'</span><span style="color: #9e9e9e;    display: block;    font-size: 13px;    line-height: 13px;    margin-top: 2px;    text-align: center;    width: 100%;">'+month+'</span></div>';
  }
});

var nps_month_arr = Array();
$($('#feedback_line_graph tspan').get().reverse()).each(function() {
if($(this).context.textContent != "undefined"){
 if(nps_month_arr.indexOf($(this).context.textContent)>=0) {
     $(this).remove();
 } else {
   nps_month_arr.push($(this).context.textContent);
 }
 }
});

});
</script>