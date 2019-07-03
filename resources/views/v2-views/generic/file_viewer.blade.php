<!-- image viewer start -->
<div class="modal fade in image-viewer file-view-popup" style="display: none;">
<div class="modal-dialog full-width-doc modal-dialog-centered">
   <div class="modal-content">
	  <div class="modal-header">
		  <h4 class="modal-title"></h4>
		  <div class="file-view-right-header">
			 <a href="" class="file-download file-view-right-block">Download <img src="{{env('APP_URL')}}/images/v2-images/download.svg" alt=""></a>
			 <a href="#!" class="full_screen_toggle file-view-right-block"><span>Minimise</span> <img src="{{env('APP_URL')}}/images/v2-images/collapse_light_Icon.svg" alt=""></a>
		  </div>
		  <button type="button" class="close file-view-right-block" data-dismiss="modal" aria-label="Close"></button>
	  </div>
	  <div class="modal-body">
	  	  <div class="file-view-download-mobile">
			 <a href="" class="file-download file-view-right-block">Download <img src="{{env('APP_URL')}}/images/v2-images/download.svg" alt=""></a>
		  </div>
		 <div class="text-center"><img class="file-source" src="" onload=" $('.modal-loader').hide(); "></div>  
		  <div class="modal-loader" style="background: #ffffff url('{{env('APP_URL')}}/images/v2-images/loader.svg') no-repeat center center;"></div>
	  </div>
   </div>
</div>
</div>
<!-- image viewer end -->

<!-- video viewer start -->
<div class="modal fade in video-viewer file-view-popup" style="display: none;">
<div class="modal-dialog full-width-doc modal-dialog-centered">
   <div class="modal-content">
	  <div class="modal-header">
		 <h4 class="modal-title"></h4>
		 <div class="file-view-right-header">
			 <a href="" class="file-download file-view-right-block">Download <img src="{{env('APP_URL')}}/images/v2-images/download.svg" alt=""></a>
			 <a href="#!" class="full_screen_toggle file-view-right-block"><span>Minimise</span> <img src="{{env('APP_URL')}}/images/v2-images/collapse_light_Icon.svg" alt=""></a>
		 </div>
		 <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
	  </div>
	  <div class="modal-body">
	  <div class="file-view-download-mobile">
			 <a href="" class="file-download file-view-right-block">Download <img src="{{env('APP_URL')}}/images/v2-images/download.svg" alt=""></a>
		  </div>
		 <div class="text-center">
			 <video width="400" controls poster="{{env('APP_URL')}}/images/video-poster.jpg" mimeType="video/mp4" class="media-video">
			  <source class="file-source" src="" type="video/mp4">
			  Your browser does not support HTML5 video.
		   </video>
		 </div>
		 <div class="modal-loader" style="background: #ffffff url('{{env('APP_URL')}}/images/v2-images/loader.svg') no-repeat center center;"></div>
	  </div>
   </div>
</div>
</div>
<!-- video viewer end -->

<!-- docs viewer start -->
<div class="modal fade in docs-viewer file-view-popup" style="display: none;">
<div class="modal-dialog full-width-doc modal-dialog-centered">
   <div class="modal-content">
	  <div class="modal-header">
		 <h4 class="modal-title"></h4>
		 <div class="file-view-right-header">
			 <a href="" class="file-download file-view-right-block">Download <img src="{{env('APP_URL')}}/images/v2-images/download.svg" alt=""></a>
			 <a href="#!" class="full_screen_toggle file-view-right-block"><span>Minimise</span> <img src="{{env('APP_URL')}}/images/v2-images/collapse_light_Icon.svg" alt=""></a>
		 </div>
		 <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
	  </div>
	  <div class="modal-body">
	  <div class="file-view-download-mobile">
			 <a href="" class="file-download file-view-right-block">Download <img src="{{env('APP_URL')}}/images/v2-images/download.svg" alt=""></a>
		  </div>
		 <div class="text-center">
			   <iframe class="file-source" src="" frameborder="0" onload=" $('.modal-loader').hide(); "></iframe>
		 </div>
		 <div class="modal-loader" style="background: #ffffff url('{{env('APP_URL')}}/images/v2-images/loader.svg') no-repeat center center;"></div>
	  </div>
   </div>
</div>
</div>
<!-- docs viewer end -->