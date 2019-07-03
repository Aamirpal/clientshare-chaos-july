<div class="feed-tile pull-left executive_col_tile">
     <div class="summary-wrap" id="tour1" style={{$style}} >
      <h4 class="title">Executive summary  </h4>
      <span class="tile-descriptions full-width tile-descriptions-wrap">
         <span class="executive-center-insider">
            <p class="executive_show_less"></p>
            <p class="executive_show_more"></p>
         </span>
      </span>
      @if( !isset($data->executive_summary) && strtolower($space_user[0]['user_role']['user_type_name']) == 'admin')
      <a href="javescript:void();" data-toggle="modal" data-target="#executive_modal" class="add_executive_button">ADD EXECUTIVE SUMMARY</a>
      @endif
   </div>

   <div class="executive-summary-preview">
      @if(isset($data->executive_summary) && $data->executive_summary)
      <span class="tile-heading pull-left">Executive Summary</span>
         @if( strtolower($space_user[0]['user_role']['user_type_name']) == 'admin')
         <span class="pull-right edit-icon"><!-- <a id="inc_text"> -->
         <a href="javescript:void();" data-toggle="modal" data-target="#executive_modal">
         <img class="exe_pencil" src="{{ url('/',[],$ssl) }}/images/ic_edit.svg" aria-hidden="true"></a>
         </span>
         @endif
      <span class="tile-description full-width">
         <span class="executive-center-inside">
            <p class="executive_show_less">{{ ucfirst(substr($data->executive_summary, 0, 186)) }}
               @if(strlen($data->executive_summary) > 186)… <a href="javascript:void();">Show more</a>@endif</p>
            <p class="executive_show_more">{{ ucfirst($data->executive_summary) }} <a href="javascript:void();">Show less</a></p>
         </span>
      </span>
      @endif
      <div class="summary-links">
         <?php $modal_differ_id = Config::get('constants.MODAL_ID');?>
            @for($count = 0; $count < 2; $count++)
            <div style="display: none" class="executive-link-col full-width pdf_list_file executive-file">   
               <a class="executive-file-modal">
               <input type='hidden' name="url_src" value="">
               <img src="{{ url('/',[],$ssl) }}/images/ic_link.svg"><span><span class='loader' style='display: none'>...Loading</span></span></a>

            </div>
            <div class="modal fade" id="media_preview_modal_{{$modal_differ_id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
               <div class="modal-dialog full-width-doc">
                  <div class="modal-content">
                     <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/',[],$ssl) }}/images/ic_highlight_removegray.svg" alt=""></button>
                        <a class="media-link" href = ""><i class="fa fa-download pull-right" aria-hidden="true"></i></a>
                        <a href="javascript:void(0)" class="pull-right full_screen_toggle"><i class="fa fa-compress" aria-hidden="true"></i>
                        <input type="hidden" name="url_src" value="">
                        </a>
                        <h4 class="modal-title" id="myModalLabel"></h4>
                     </div>

                        <div class="modal-body">
                           <!-- media content here -->
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                     </div>
                  </div>
                  <div class="modal-loader" style="background: #ffffff url('{{ url('/',[],$ssl) }}/images/loading_bar1.gif') no-repeat center center;"></div>
               </div>
            </div>       
            <?php $modal_differ_id++; ?>
         @endfor


         <!--<XLS CODE>-->         
      </div>
</div>  
</div>
@php
 $allowed_extentions_doc = [Config::get('constants.DOCUMENT_EXTENSIONS.PDF'), Config::get('constants.DOCUMENT_EXTENSIONS.PPT'), Config::get('constants.DOCUMENT_EXTENSIONS.DOCX'), Config::get('constants.DOCUMENT_EXTENSIONS.PPTX'), Config::get('constants.DOCUMENT_EXTENSIONS.DOC'), Config::get('constants.DOCUMENT_EXTENSIONS.XLS'), Config::get('constants.DOCUMENT_EXTENSIONS.XLSX'), Config::get('constants.DOCUMENT_EXTENSIONS.CSV')];

 $video_extentions = [Config::get('constants.MEDIA_EXTENSIONS.MP4'), Config::get('constants.MEDIA_EXTENSIONS.MKV'), Config::get('constants.MEDIA_EXTENSIONS.3GP'), Config::get('constants.MEDIA_EXTENSIONS.FLV'), Config::get('constants.MEDIA_EXTENSIONS.MOV'), Config::get('constants.MEDIA_EXTENSIONS.MOVE')];
 
 $media_icons = Config::get('constants.extension_wise_svg_image');
@endphp
<script type="text/javascript">
 var doc_extension = $.parseJSON('<?php echo json_encode($allowed_extentions_doc); ?>');
 var video_extension = $.parseJSON('<?php echo json_encode($video_extentions); ?>');
 var media_icons = $.parseJSON('<?php echo json_encode($media_icons); ?>');
</script>
<script rel="text/javascript" src="{{ url('js/custom/executive_summary.js?q='.env('CACHE_COUNTER', rand()),[],$ssl) }}"></script>