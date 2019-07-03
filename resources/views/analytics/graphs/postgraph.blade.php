<div id="posts_graph" style="height: 290px; float: left; width: 100%;"></div>


<script>
var post_x_label = nps_graph_data.length == 2?'day':'month';

$(document).ready(function(){
Morris.Line({
  element: 'posts_graph',
  data: post_graph_data,
  xkey: 'month',
  ykeys: ['value'],
  labels: [''],
  xLabels: post_x_label,
  xLabelFormat: function(x) {
    var month = graph_months[x.getMonth()];
    return month;
  },
  yLabelFormat: function(x) { 
    if(x % 1 === 0){
      return x;  
    }
    return '';
  },
  dateFormat: function(x) {
    var month = graph_months[new Date(x).getMonth()];
    return month;
  },
  grid:false,
  smooth:false,
  //resize:true,
  goals:[0],
  events:[post_graph_data[0].month],
  eventLineColors:['black'],
  goalLineColors:['black'],
  hideHover:'auto',
  hoverCallback: function (index, options, content, row) {
    var dt = new Date( post_graph_data[index].month );
    var month = graph_months[dt.getMonth()];
    return '<div style="padding: 8px 14px; background-color: #fff;box-shadow: 0 2px 4px 0 rgba(0,0,0,0.16); border-radius: 2px; display: inline-block;"><span style="font-size: 24px;font-weight: 600;text-align: center;line-height: 24px;color: #0D47A1;">'+post_graph_data[index].value+'</span><span style="color: #9e9e9e;    display: block;    font-size: 13px;    line-height: 13px;    margin-top: 2px;    text-align: center;    width: 100%;">'+month+'</span></div>';
  }
});

var post_month_arr = Array();
$($('#posts_graph tspan').get().reverse()).each(function() {
 if(post_month_arr.indexOf($(this).context.textContent)>=0) {
   // alert($(this).html())
     $(this).remove();
 } else {
   post_month_arr.push($(this).context.textContent);
 }
});
});
</script>