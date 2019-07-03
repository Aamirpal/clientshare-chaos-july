<div class="modal fade custom-tile-popup edit-share-banner-popup" id="share_logo_edit" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog">
    <div class="modal-content white-popup">
      <div class="form-submit-loader share-update-loader hidden">
         <span></span>
      </div>
      <div class="modal-header">
        <h2 class="modal-title hidden-xs">Company logos and banner</h2>
        <h2 class="modal-title hidden-sm hidden-md hidden-lg">Company logos</h2>
      </div>
      <form id="edit_share_header" class="edit-share-header" action="share_header" enctype="multipart/form-data">
        {{csrf_field()}}
        <div class="modal-body">
          <div class="edit-share-banner-col share-banner-preview hidden-xs" style="background-image: url('{{(!empty(wrapUrl($session_data['background_image'])))? wrapUrl($session_data['background_image']) : env('APP_URL').'/images/bgIimg.jpg'}}');">
            <div class="edit-share-banner-content">
              <div class="col-xs-12 col-sm-8">
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

              <div class="col-xs-12 col-sm-4">
                <div class="edit-share-banner-preview">
                  <a href="#"><h3>PREVIEW</h3></a>
                </div>
              </div>
            </div>
          </div>

          <div class="border-col hidden-xs">
            <span class="border-line"></span>
          </div>
    
          <div class="share-upload-logo-container">
            <div class="share-upload-logo-row">
              <div class="upload-logo-col">
                <div class="upload-logo-view">
                  <span class="seller-logo-preview" style="background-image: url('{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                  <span class="buyer-logo-preview space-pic" style="background-image: url('{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                </div>
                <div class="share-twitter-handle">

                  <div class="upload-logo-name seller">
                    <p>Share name</p>
                    <div class="twitter-input-col share-edit-input-column">
                      <input class="form-control" name="share_name" id="share-name" value="{{ $session_data['share_name'] }}" placeholder="Share name" type="text">
                      <div class="email-error image-error share-name-error text-left"></div>
                      <span class="share-edit-input-clientshare">Client Share</span>
                    </div>
                  </div>

                  <div class="upload-logo-name seller">
                    <p>Logo - Your company</p>
                    <div class="twitter-input-col">
                      <input class="form-control seller_twitter_name twitter-feed-input" name="seller_twitter_name" placeholder="@twitterhandle" type="text">
                      <span class="twitter-close remove-handle hidden"><img src="{{ url('/images/ic_delete_hover.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                    </div>
                    <span>OR</span>
                    <div class="share-logo-upload-device">
                      <a href="javascript:void(0)" id="upload_seller_logo" class="upload-logo title">Upload from device</a>
                      <div class="email-error image-error hidden">Only images are allowed</div>
                      <div class="uploaded-logo-name hidden">
                        <span class="title"></span>
                        <span class="remove-logo"><img src="{{env('APP_URL')}}/images/ic_delete_small_blue.svg"></span>
                      </div>
                      <input id="seller-logo" type="file" name="seller_logo" accept="image/*" class="hidden">
                      <input id="seller-twitter-logo" type="hidden" name="seller_twitter_logo" class="twitter-logo-url">
                      <input type="hidden" name="seller_logo_url" id="hidden_seller_logo" value="{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}">
                    </div>
                  </div>
                  <div class="upload-logo-name buyer">
                    <p>Logo - Partner company</p>
                    <div class="twitter-input-col">
                      <input class="form-control buyer_twitter_name twitter-feed-input" name="buyer_twitter_name" placeholder="@twitterhandle" type="text">
                      <span class="twitter-close remove-handle hidden"><img src="{{ url('/images/ic_delete_hover.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                    </div>
                    <span class="hidden-xs">OR</span>
                    <div class="share-logo-upload-device">
                      <a href="javascript:void(0)" id="upload_buyer_logo" class="upload-logo title">Upload from device</a>
                      <div class="email-error image-error hidden">Only images are allowed</div>
                      <div class="uploaded-logo-name hidden">
                        <span class="title"></span>
                        <span class="remove-logo"><img src="{{env('APP_URL')}}/images/ic_delete_small_blue.svg"></span>
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
          <div class="border-col hidden-xs border-col-bottom">
            <span class="border-line"></span>
          </div>

          <div class="select-share-banner-container full-width hidden-xs">
            <div class="share-logo-upload-device pull-left upload-logo-name banner">
              <a href="#!" id="upload_banner" class="upload-logo title">Upload your own</a>
              <div class="email-error image-error hidden">Only images are allowed</div>
              <div class="email-error image-error banner-image-error text-left"></div>
              <div class="uploaded-logo-name hidden">
                <span class="title"></span>
                <span class="remove-logo"><img src="{{env('APP_URL')}}/images/ic_delete_small_blue.svg"></span>
              </div>
              <input id="share-banner" type="file" name="share_banner" accept="image/*" class="hidden">
              <input type="hidden" id="hidden_share_logo" name="share_banner_url" value="@if(!empty(wrapUrl($session_data['background_image']))){{ wrapUrl($session_data['background_image']) }}@endif">
              <input type="hidden" name="banner_image" id="edit-banner-image" />
            </div>
            <div class="select-share-banner-row full-width">
              <div class="select-share-banner-col full-width">
              @foreach(Config::get('constants.BANNER_IMAGES') as $banner)
                  <div class="select-share-banner-part share-banner-images" banner_url="{{ url($banner,[],env('HTTPS_ENABLE', true)) }}" style="background-image: url('{{ url($banner,[],env('HTTPS_ENABLE', true)) }}');"></div>
              @endforeach
              </div>
            </div>
          </div>
        </div>

        <div class="btn-section text-right full-width">
          <input type="hidden" name="space_id" value="{{ Session::get('space_info')['id'] }}">
          <input type="hidden" name="seller_name" value="{{ Session::get('space_info')['SellerName']['company_name']}}">
          <input type="hidden" name="buyer_name" value="{{ Session::get('space_info')['BuyerName']['company_name']}}">
          <button class="btn-quick-links btn btn-primary pull-right save-share-header" type="button">Save</button>
          <button class="close btn btn-default share-header-cancel btn-quick-links" data-dismiss="modal" type="button">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>