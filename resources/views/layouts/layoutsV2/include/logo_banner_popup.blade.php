<div class="modal fade custom-tile-popup edit-share-banner-popup" id="share_logo_edit" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content white-popup">
      <div class="form-submit-loader share-update-loader hidden">
         <span></span>
      </div>
      <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Personalise your share</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
            </button>
         </div> 
      <form id="edit_share_header" class="edit-share-header" action="share_header" enctype="multipart/form-data">
        {{csrf_field()}}
        <div class="edit-share-wrap"> 
        <div class="edit-share-banner-wrap"> 
          <div class="edit-share-banner-col share-banner-preview hidden-xs" style="background-image: url('{{(!empty(wrapUrl($session_data['background_image'])))? wrapUrl($session_data['background_image']) : env('APP_URL').'/images/bgIimg.jpg'}}');">
              <div class="edit-share-banner-content">
                <div class="space-pic-wrap">
                  <span class="seller-logo-preview" style="background-image: url('{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                  <span class="buyer-logo-preview space-pic" style="background-image: url('{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                </div>

                <div class="share-name-show">
                  <h3>
                    <span class="s_name"><a href="#">{{ $session_data['share_name'] }}</a></span> 
                    <span class="fix-name"><a href="#">Client Share</a> </span> 
                  </h3>
                </div>
            </div>
            </div> 
            </div> 
    
          <div class="modal-body">
          <div class="share-upload-logo-container">
            <div class="share-upload-logo-row">
              <div class="upload-logo-col">
                <div class="share-twitter-handle">
                  <h4>Edit name and logos</h4>
                  <div class="upload-logo-name seller form-group">
                    <label>Share name</label>
                    <div class="twitter-input-col share-edit-input-column">
                      <input class="form-control" name="share_name" id="share-name" value="{{ $session_data['share_name'] }}" placeholder="Share name" type="text">
                      <div class="email-error image-error share-name-error text-left"></div>
                    </div>
                  </div>

                  <div class="upload-logo-name seller form-group">
                    <label>Logo - Your company</label>
                    <div class="upload-logo-inner-wrap">
                    <div class="twitter-input-col">
                      <input class="form-control seller_twitter_name twitter-feed-input" name="seller_twitter_name" placeholder="@twitterhandle" type="text">
                      <span class="twitter-close remove-handle hidden"><img src="{{ url('/images/v2-images/close_bg_icon.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                    </div>
                    <span class="upload-logo-or-div">or</span>
                    <div class="share-logo-upload-device">
                      <a href="javascript:void(0)" id="upload_seller_logo" class="upload-logo title" data-html="true" data-toggle="tooltip" data-placement="top" title="<div class='custom-tooltip'>To ensure your logo looks great, use a square image</div>">
                      <span class="mobile-upload-icon hidden-sm hidden-md hidden-lg">
                        <img src="{{ url('/images/v2-images/upload-icon-green.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                      Upload from device</a>
                      <div class="email-error image-error hidden">Only images are allowed</div>
                      <div class="uploaded-logo-name hidden">
                        <span class="title"></span>
                        <span class="remove-logo"><img src="{{env('APP_URL')}}/images/v2-images/close_bg_icon.svg"></span>
                      </div>
                      <input id="seller-logo" type="file" name="seller_logo" accept="image/*" class="hidden">
                      <input id="seller-twitter-logo" type="hidden" name="seller_twitter_logo" class="twitter-logo-url">
                      <input type="hidden" name="seller_logo_url" id="hidden_seller_logo" value="{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}">
                    </div>
                    </div>
                  </div>
                  <div class="upload-logo-name buyer form-group">
                    <label>Logo - Partner company</label>
                    <div class="upload-logo-inner-wrap">
                    <div class="twitter-input-col">
                      <input class="form-control buyer_twitter_name twitter-feed-input" name="buyer_twitter_name" placeholder="@twitterhandle" type="text">
                      <span class="twitter-close remove-handle hidden"><img src="{{ url('/images/v2-images/close_bg_icon.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                    </div>
                    <span class="upload-logo-or-div">or</span>
                    <div class="share-logo-upload-device">
                      <a href="javascript:void(0)" id="upload_buyer_logo" class="upload-logo title" data-html="true" data-toggle="tooltip" data-placement="top" title="<div class='custom-tooltip'>To ensure your logo looks great, use a square image</div>">
                      <span class="mobile-upload-icon hidden-sm hidden-md hidden-lg">
                        <img src="{{ url('/images/v2-images/upload-icon-green.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                      Upload from device</a>
                      <div class="email-error image-error hidden">Only images are allowed</div>
                      <div class="uploaded-logo-name hidden">
                        <span class="title"></span>
                        <span class="remove-logo"><img src="{{env('APP_URL')}}/images/v2-images/close_bg_icon.svg"></span>
                      </div>
                      <input id="buyer-logo" type="file" name="buyer_logo" accept="image/*" class="hidden" >
                      <input id="buyer-twitter-logo" type="hidden" name="buyer_twitter_logo" class="twitter-logo-url">
                      <input type="hidden" id="hidden_buyer_logo" name="buyer_logo_url" value="{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}">
                    </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="select-share-banner-container full-width hidden-xs">
          <h4>Select a banner or add your own </h4>
            <div class="share-logo-upload-device pull-left upload-logo-name banner">
              <a href="#!" id="upload_banner" class="upload-logo upload-banner title" data-html="true" data-toggle="tooltip" data-placement="top" title="<div class='custom-tooltip'>The optimised size for your banner is 1280 x 128</div>">
              <span class="upload-icon">
                <img src="{{ url('/images/v2-images/upload-icon-green.svg',[],env('HTTPS_ENABLE', true)) }}">
              </span>
              Upload your own
            </a>
              <div class="email-error image-error hidden">Only images are allowed</div>
              <div class="email-error image-error banner-image-error text-left"></div>
              <div class="uploaded-logo-name hidden">
                <span class="title"></span>
                <span class="remove-logo"><img src="{{env('APP_URL')}}/images/v2-images/close_bg_icon.svg"></span>
              </div>
              <input id="share-banner" type="file" name="share_banner" accept="image/*" class="hidden">
              <input type="hidden" id="hidden_share_logo" name="share_banner_url" value="@if(!empty(wrapUrl($session_data['background_image']))){{ wrapUrl($session_data['background_image']) }}@endif">
              <input type="hidden" name="banner_image" id="edit-banner-image" />
            </div>
            <div class="edit-share-banner-row custom-scrollbar">
            <div class="select-share-banner-row full-width">
              <div class="select-share-banner-col full-width">
              @foreach(Config::get('constants.BANNER_IMAGES') as $banner)
                  <div class="select-share-banner-part share-banner-images" banner_url="{{ url($banner,[],env('HTTPS_ENABLE', true)) }}" style="background-image: url('{{ url($banner,[],env('HTTPS_ENABLE', true)) }}');"></div>
              @endforeach
              </div>
            </div>
            </div>
          </div>
          <div class="btn-section btn-group full-width">
          <input type="hidden" name="space_id" value="{{ Session::get('space_info')['id'] }}">
          <input type="hidden" name="seller_name" value="{{ Session::get('space_info')['SellerName']['company_name']}}">
          <input type="hidden" name="buyer_name" value="{{ Session::get('space_info')['BuyerName']['company_name']}}">
          <button class="btn btn-secondary share-header-cancel btn-quick-links" data-dismiss="modal" type="button">Cancel</button>
          <button class="btn-quick-links btn btn-primary pull-right save-share-header" type="button">Save</button>
        </div>
        </div>

       
        </div>
      </form>
    </div>
  </div>
</div>