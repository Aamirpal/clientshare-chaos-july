<!-- image viewer start -->
<div class="modal fade in image-viewer" style="display: none; padding-right: 15px;">
<div class="modal-dialog full-width-doc ">
   <div class="modal-content">
	  <div class="modal-header">
		 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{env('APP_URL')}}/images/ic_highlight_removegray.svg" alt=""></button>
		 <a href="" class="file-download pull-right"><i class="fa fa-download" aria-hidden="true"></i></a>
		 <a href="#!" class="pull-right full_screen_toggle"><i class="fa fa-compress" aria-hidden="true"></i></a>
		 <h4 class="modal-title"></h4>
	  </div>
	  <div class="modal-body">
		 <div class="text-center"><img class="file-source" src="" onload=" $('.modal-loader').hide(); "></div>
	  </div>
   </div>
   <div class="modal-loader" style="background: #ffffff url('{{env('APP_URL')}}/images/loading_bar1.gif') no-repeat center center;"></div>
</div>
</div>
<!-- image viewer end -->

<!-- video viewer start -->
<div class="modal fade in video-viewer" style="display: none; padding-right: 15px;">
<div class="modal-dialog full-width-doc ">
   <div class="modal-content">
	  <div class="modal-header">
		 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{env('APP_URL')}}/images/ic_highlight_removegray.svg" alt=""></button>
		 <a href="" class="file-download pull-right"><i class="fa fa-download" aria-hidden="true"></i></a>
		 <a href="#!" class="pull-right full_screen_toggle"><i class="fa fa-compress" aria-hidden="true"></i></a>
		 <h4 class="modal-title"></h4>
	  </div>
	  <div class="modal-body">
		 <div class="text-center">
			 <video width="400" controls poster="{{env('APP_URL')}}/images/video-poster.jpg" mimeType="video/mp4" class="media-video">
			  <source class="file-source" src="" type="video/mp4">
			  Your browser does not support HTML5 video.
		   </video>
		 </div>
	  </div>
   </div>
   <div class="modal-loader" style="background: #ffffff url('{{env('APP_URL')}}/images/loading_bar1.gif') no-repeat center center;"></div>
</div>
</div>
<!-- video viewer end -->

<!-- docs viewer start -->
<div class="modal fade in docs-viewer" style="display: none; padding-right: 15px;">
<div class="modal-dialog full-width-doc ">
   <div class="modal-content">
	  <div class="modal-header">
		 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{env('APP_URL')}}/images/ic_highlight_removegray.svg" alt=""></button>
		 <a href="" class="file-download pull-right"><i class="fa fa-download" aria-hidden="true"></i></a>
		 <a href="#!" class="pull-right full_screen_toggle"><i class="fa fa-compress" aria-hidden="true"></i></a>
		 <h4 class="modal-title"></h4>
	  </div>
	  <div class="modal-body">
		 <div class="text-center">
			   <iframe class="file-source" src="" frameborder="0" onload=" $('.modal-loader').hide(); "></iframe>
		 </div>
	  </div>
   </div>
   <div class="modal-loader" style="background: #ffffff url('{{env('APP_URL')}}/images/loading_bar1.gif') no-repeat center center;"></div>
</div>
</div>
<!-- docs viewer end -->