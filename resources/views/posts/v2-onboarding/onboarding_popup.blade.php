<?php
  $length = 0;
  if (!empty($data->executive_summary))
    $length = strlen($data->executive_summary);
    $twitter_handles = getTwitterHandlersArray(Session::get('space_info')['twitter_handles']);
?>
@php
  $session_data = spaceSessionData($space_id);
@endphp
<div class="modal fade custom-tile-popup edit-share-banner-popup" data-keyboard="false" data-controls-modal="your_div_id" id="welcome_tour" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content white-popup">
      <div class="form-submit-loader share-update-loader hidden">
         <span></span>
      </div>

      <!-- Invite Admin Code Start -->
      <div data-step="1" class="welcome-cs-popup company-logo wc-twitter-col full-width manage_invite_feed_modal welcome_invite_feed_modal {{($session_data['share_profile_progress']<=1 )?'':'hidden'}}" id="manage_invite_feed_modal">
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Invite Admin</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
            </button>
         </div> 
          <div class="user-welcome-col user-welcome-logo-col full-width">
            <div class="user-welcome-text">
              <p>Spread the load – invite other members of your team to become administrators of your share. They can help you complete the 6 step onboarding process. </p>
            </div>

           <div class="modal-body">
            <div class="welcome-tour-categorie-col full-width">
              <form method="post" action="{{ env('APP_URL') }}/save_invite_admin" enctype="multipart/form-data" class="invite_admin_form" id="onboarding_invite_admin">
              {!! csrf_field() !!}
                <div class="edit-categories edit-admin-invite">
                    
                  <div class="wc-domain-col full-width">
                    <div class="link-columns twitter-handle-wrap">
                      <div class="logo-upload-column company-category-content">
                      <p class="info-text"><img src="{{asset('images/v2-images/info-icon.svg')}}" alt="Email Invitation Information" />Admins have additional permissions to manage the community and edit or delete share content.</p>
                      </div>


                      <div class="add-admin-wrap">
                      <label>Would you like to add another admin?</label>
                      <div class="custom-radiobtn-group">
                          <label for="radio-add-admin-yes">Yes                   
                              <input type="radio" name="radio_add_admin" value="1" class="radio-invite-admin" id="radio-add-admin-yes">
                              <span class="custom-radiobtn"></span>
                          </label>
                      </div>
                      <div class="custom-radiobtn-group">
                          <label for="radio-add-admin-no">No
                              <input type="radio" name="radio_add_admin" value="0" class="radio-invite-admin" id="radio-add-admin-no">
                              <span class="custom-radiobtn"></span>
                          </label>
                      </div>
                      <div class="validate-invite-admin"></div>
                      </div>
                        <div class="invite-admin-block">
                            <div class="admin-invite-result-box full-width text-left hidden">
                            </div>
                            <div class="admin-invite-box full-width">
                                <div class="twitter-input-col form-group">
                                    <label>Email</label>
                                    <input class="form-control twitter-feed-input" name="admin_invite[]" placeholder="Email address" type="text">
                                </div>
                            </div>
                            <div class="full-width add-admin-users-link">
                                <a href="javascript:void(0)" class="add-admin-invite-link">
                                    <span class="category-add-icon handle-add-icon">
                                        <img src="{{ url('/images/v2-images/add_small_icon.svg',[],env('HTTPS_ENABLE', true)) }}">
                                    </span>
                                    Add more admin users
                                </a>
                            </div>
                        </div>
                        <input type="hidden" class="admin_invite_subject" name="subject" value="{{ Auth::user()->first_name }} has invited you to be admin of the {{ $session_data['share_name'] }}">  
                        <input type="hidden" class="admin_invite_body" name="body" value="I'm inviting you to join the {{ $session_data['share_name'] }} as an administrator.">        


                    </div>
                  </div>
                </div>
                <div class="user-welcome-footer full-width btn-group">
                  <a class="btn btn-secondary transparent-btn tour-next-step btn-invite-back" href="javascript:void(0);">Skip</a>
                  <button type="button" class="btn btn-primary wlcm-next-btn btn-invite-handle" data-space="">Invite</button>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
      <!-- Invite Admin Code End -->


      <!-- Company Logo Code Start -->
      <div data-step="2" class="welcome-cs-popup company-logo full-width {{$session_data['share_profile_progress']==2?'':'hidden'}}">
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Company Logos</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
            </button>
         </div>
          <div class="user-welcome-col user-welcome-logo-col full-width">
            <div class="user-welcome-text">
              <p>Brand your Share! Add your company logos to customise your share. This will help you and your customer quickly identify which Share they are in.</p>
            </div>
            <div class="modal-body">
            <div class="logo-upload-column">
            <p class="info-text"><img src="{{asset('images/v2-images/info-icon.svg')}}" alt="Email Invitation Information" />Twitter Logos work best. You can upload your logos from your device or provide us with the company specific Twitter handles and we can do the hard work for you.</p>
              <div class="upload-logo-view">
                <span class="logo-count">1</span>
                <span class="seller-logo-preview" style="background-image: url('{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                <span class="buyer-logo-preview space-pic" style="background-image: url('{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                <span class="logo-count">2</span>
              </div>
            </div>

                <form id="update_welcome_share_logo" action="#" enctype="multipart/form-data">
                    {{csrf_field()}}
              <div class="share-twitter-handle">

                <div class="upload-logo-name seller full-width form-group">
                  <label>Your company</label>
                  <div class="upload-logo-inner-wrap">
                  <div class="twitter-input-col">
                    <input class="form-control seller_twitter_name twitter-feed-input" name="seller_twitter_name" placeholder="@twitterhandle" type="text">
                      <span class="twitter-close remove-handle hidden">
                        <img src="{{ url('/images/v2-images/close_bg_icon.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                  </div>
                  <span class="upload-logo-or-div">or</span>
                  <div class="share-logo-upload-device">
                    <a href="javascript:void(0)" id="upload_seller_logo" class="upload-logo title" data-html="true" data-toggle="tooltip" data-placement="top" title="<div class='custom-tooltip'>To ensure your logo looks great, use a square image</div>">
                      <span class="mobile-upload-icon hidden-sm hidden-md hidden-lg">
                        <img src="{{ url('/images/v2-images/upload-icon-green.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                      Upload from device
                    </a>
                    <div class="email-error image-error hidden">Only images are allowed</div>
                    <div class="uploaded-logo-name hidden">
                      <span class="title"></span>
                      <span class="remove-logo">
                        <img src="{{ url('/images/v2-images/close_bg_icon.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                    </div>
                      <input id="seller-logo" type="file" name="seller_logo" accept="image/*" class="hidden">
                      <input id="seller-twitter-logo" type="hidden" name="seller_twitter_logo" class="twitter-logo-url">
                      <input type="hidden" name="seller_logo_url" id="hidden_seller_logo" value="{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}">
                  </div>
                  </div>
                </div>
                <div class="upload-logo-name buyer form-group">
                  <label>Customer company</label>
                  <div class="upload-logo-inner-wrap">
                  <div class="twitter-input-col">
                      <input class="form-control buyer_twitter_name twitter-feed-input" name="buyer_twitter_name" placeholder="@twitterhandle" type="text">
                      <span class="twitter-close remove-handle hidden">
                        <img src="{{ url('/images/v2-images/close_bg_icon.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                  </div>
                  <span class="upload-logo-or-div">or</span>
                  <div class="share-logo-upload-device">
                    <a href="javascript:void(0)" id="upload_buyer_logo" class="upload-logo title" data-html="true" data-toggle="tooltip" data-placement="top" title="<div class='custom-tooltip'>To ensure your logo looks great, use a square image</div>">
                      <span class="mobile-upload-icon hidden-sm hidden-md hidden-lg">
                        <img src="{{ url('/images/v2-images/upload-icon-green.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                      Upload from device
                    </a>
                    <div class="email-error image-error hidden">Only images are allowed</div>
                    <div class="uploaded-logo-name hidden">
                      <span class="title"></span>
                        <span class="remove-logo">
                          <img src="{{ url('/images/v2-images/close_bg_icon.svg', [], env('HTTPS_ENABLE', true)) }}">
                        </span>
                    </div>
                    <input id="buyer-logo" type="file" name="buyer_logo" accept="image/*" class="hidden" >
                    <input id="buyer-twitter-logo" type="hidden" name="buyer_twitter_logo" class="twitter-logo-url">
                    <input type="hidden" id="hidden_buyer_logo" name="buyer_logo_url" value="{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}">
                  </div>
                  </div>
                </div>
              </div>
              <div class="user-welcome-footer text-center full-width">
                <input type="hidden" id="hidden_share_logo" name="share_banner_url" value="@if(!empty(wrapUrl($session_data['background_image']))){{ wrapUrl($session_data['background_image']) }}@endif">
                <input type="hidden" name="space_id" value="{{ Session::get('space_info')['id'] }}">
                <input type="hidden" name="seller_name" value="{{ Session::get('space_info')['SellerName']['company_name']}}">
                <input type="hidden" name="buyer_name" value="{{ Session::get('space_info')['BuyerName']['company_name']}}">
                <div class="btn-group">
                <a class="btn btn-secondary transparent-btn tour-previous-step tour-back-invite-admin" href="javascript:void(0);">Back</a>
                <a class="btn btn-primary wlcm-next-btn onboarding_company_logo" href="javascript:void(0);">Next</a>
                </div>
              </div>
            </form>
            </div>
          </div>
      </div>
      <!-- Company Logo Code End -->


      <!--Upload banner Code Start -->
      <div data-step="3" class="welcome-cs-popup company-logo full-width {{$session_data['share_profile_progress']==3?'':'hidden'}}">
          <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">Banner</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
          </button>
        </div>
        <form id="update_welcome_share_banner" action="#" enctype="multipart/form-data">
            {{csrf_field()}}
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="user-welcome-text">
              <p>Select a banner image or upload your own! Banners help personalise each share to give your customers a unique experience!</p>
            </div>
           
          <div class="modal-body">
            <div class="welcome-upload-banner-row">
              <div class="edit-share-banner-col share-banner-preview" style="background-image: url('{{(!empty(wrapUrl($session_data['background_image'])))? wrapUrl($session_data['background_image']) : env('APP_URL').'/images/bgIimg.jpg'}}');">
                <div class="edit-share-banner-content">
                    <div class="space-pic-wrap">
                       <span class="seller-logo-preview" style="background-image: url('{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                       <span class="buyer-logo-preview space-pic" style="background-image: url('{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                    </div>
                    <div class="edit-share-banner-preview">
                      <a href="#"><img src="{{ url('/images/v2-images/eye-icon-white.svg', [], env('HTTPS_ENABLE', true)) }}"> preview</a>
                    </div>
                </div>
              </div>

              <div class="welcome-share-banner-section full-width">
                <div class="select-share-banner-container full-width">
                  <div class="share-logo-upload-device pull-left upload-logo-name banner">
                    <div class="uploaded-logo-name hidden">
                      <span class="title"></span>
                      <span class="remove-logo">
                        <img src="{{ url('/images/v2-images/close_bg_icon.svg', [], env('HTTPS_ENABLE', true)) }}">
                      </span>
                    </div>
                  </div>
                  <div class="upload-col-icon full-width upload-logo-name banner text-left welcome-banner-image-error">
                    <a href="#!" id="upload_banner" class="upload-logo upload-banner title" data-html="true" data-toggle="tooltip" data-placement="top" title="<div class='custom-tooltip'>The optimised size for your banner is 1280 x 128</div>">
                    <span class="upload-icon">
                      <img src="{{ url('/images/v2-images/upload-icon-green.svg',[],env('HTTPS_ENABLE', true)) }}">
                    </span>Upload your own</a>
                    <div class="email-error image-error hidden">Only images are allowed</div>
                    <div class="email-error image-error banner-image-error text-left"></div>
                    <div class="uploaded-logo-name hidden">
                      <span class="title"></span>
                      <span class="remove-logo"><img src="{{ url('/images/v2-images/close_bg_icon.svg', [], env('HTTPS_ENABLE', true)) }}"></span>
                    </div>
                    <input id="share-banner" type="file" name="share_banner" accept="image/*" class="hidden">
                    <input type="hidden" id="hidden_share_logo" name="share_banner_url" value="@if(!empty(wrapUrl($session_data['background_image']))){{ wrapUrl($session_data['background_image']) }}@endif">
                    <input type="hidden" name="banner_image" id="banner-image" />
                    <input type="hidden" name="space_id" value="{{ Session::get('space_info')['id'] }}">
                    <input type="hidden" name="seller_name" value="{{ Session::get('space_info')['SellerName']['company_name']}}">
                    <input type="hidden" name="buyer_name" value="{{ Session::get('space_info')['BuyerName']['company_name']}}">
                  </div>
                  <div class="welcome-share-banner-row full-width custom-scrollbar">
                    <div class="select-share-banner-row full-width">
                      <div class="select-share-banner-col full-width">
                      @foreach(Config::get('constants.BANNER_IMAGES') as $banner)
                          <div class="select-share-banner-part share-banner-images" banner_url="{{ url($banner,[],env('HTTPS_ENABLE', true)) }}" style="background-image: url('{{ url($banner,[],env('HTTPS_ENABLE', true)) }}');"></div>
                      @endforeach
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="user-welcome-footer full-width btn-group">
              <a class="btn btn-secondary transparent-btn tour-previous-step" href="javascript:void(0);">Back</a>
              <a class="btn btn-primary wlcm-next-btn onboarding_company_logo" href="javascript:void(0);">Next</a>
            </div>
            </div>
        </form>
        </div>
      </div>

      <!--             Twitter Code Code Start -->
      <div data-step="4" class="welcome-cs-popup company-logo wc-twitter-col full-width {{$session_data['share_profile_progress']==4?'':'hidden'}} manage_twitter_feed_modal welcome_twitter_feed_modal" id="manage_twitter_feed_modal">
      <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">Twitter</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
          </button>
        </div>
          <div class="user-welcome-col user-welcome-logo-col full-width">
            <div class="user-welcome-text">
              <p>Keep up to date! Adding twitter feeds let you and your customer seamlessly view your social media content!</p>
            </div>

            <div class="modal-body">
            <div class="welcome-tour-categorie-col full-width">
              <form method="post" action="{{ env('APP_URL') }}/save_twitter_feed" enctype="multipart/form-data" class="twitter_feed_form" id="onboarding_twitter_handles">
              {!! csrf_field() !!}
                <div class="edit-categories">
                    
                  <div class="wc-domain-col full-width">
                    <div class="link-columns twitter-handle-wrap">
                      <div class="logo-upload-column text-left company-category-content">
                      <p class="info-text"><img width="17" src="{{asset('images/v2-images/info-icon.svg')}}" alt="Email Invitation Information" />Simply add you and your customers twitter handles! These are easily changed and you can have up to 3.</p>
                      </div>
                      <div class="twitter-handle-wrap-column">
                      <input name="space_id" id="twitter_feed_space_id" autocomplete="off" value="{{$data->id}}" type="hidden">
                      @if(!empty($twitter_handles))
                          @foreach($twitter_handles as $handle_index => $handle_value)
                              <div class="twitter-handle tour-twitter-list form-group">
                                 <span class="link-input-icon"><p>{{$handle_index + 1}}</p></span>
                                 <div class="twitter-input-col">
                                     <input type="text" name="twitter_handles[]" value="{{$handle_value}}" id="twitter_handle_{{$handle_index}}" class="form-control twitter-feed-input" placeholder="@twitterhandle" autocomplete="off" >
                                     <span class="twitter-close remove-handle"><img src="{{ url('/images/v2-images/close_bg_icon.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                                 </div>
                              </div>
                         @endforeach
                      @else
                          <div class="twitter-handle tour-twitter-list form-group">
                             <span class="link-input-icon"><p>1</p></span>
                             <div class="twitter-input-col">
                              <input type="text" name="twitter_handles[]" value="" id="twitter_handle_0" class="form-control twitter-feed-input" placeholder="@twitterhandle" autocomplete="off">
                              <span class="twitter-close remove-handle"><img src="{{ url('/images/v2-images/close_bg_icon.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                              </div>
                          </div>
                      @endif
                      </div>

                      <div class="full-width text-left">
                        <a href="javascript:void(0)" class="add-category-link add-twitter-feed add-handles" @php if(!empty($twitter_handles) && sizeOfCustom($twitter_handles) >= 3) echo 'style ="display:none"' @endphp >
                            <span class="category-add-icon handle-add-icon">
                              <img src="{{ url('/images/v2-images/add_small_icon.svg',[],env('HTTPS_ENABLE', true)) }}">
                            </span>Add feed
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="user-welcome-footer full-width onboarding-twitter-footer">
                  <div class="prefer-add-twitter custom-checkbox-group">
                  <label for="prefer_twitter"> We'd prefer not to add a twitter
                  <input id="prefer_twitter" class="prefer-twitter" type="checkbox" value="">
                  <span class="custom-checkmark"></span>
                  </label>
                  </div>
                  <div class="btn-group ">
                    <a class="btn btn-secondary transparent-btn tour-previous-step" href="javascript:void(0);">Back</a>
                    <button type="button" class="btn btn-primary wlcm-next-btn btn-twitter-handle btn-quick-links" data-space="">Next</button>
                  </div>
                </div>
              </form>
            </div>
            </div>
        </div>
      </div>
      <!--             Twitter Code End -->


<!--             Domain Management Code Code Start -->
<div data-step="5" class="welcome-cs-popup company-logo wc-twitter-col full-width {{$session_data['share_profile_progress']==5?'':'hidden'}}">
<div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">Domain Management</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
          </button>
        </div>
          <div class="user-welcome-col user-welcome-logo-col full-width">
            <div class="user-welcome-text">
              <p>Add both you and your customers email domains (e.g. @myclientshare.com). This restricts access to the platform to users with only approved email domains! You can add as many as you need!</p>
            </div>
            <div class="modal-body">
            <div class="welcome-tour-domain-col full-width">
              <form action="" method="post" name="" class="full-width save-domain">
                    
                  <div class="wc-domain-col full-width">
                    <div class="logo-upload-column text-left company-category-content">
                      <p class="info-text"><img src="{{asset('images/v2-images/info-icon.svg')}}" alt="Email Invitation Information" />You can also choose to have open domains, meaning any user, who is invited, can join your share.</p>
                    </div>
                    <div class="link-columns twitter-handle-wrap">
                    <div class="restrict-domain-wrap">
                      <label>Do you want to restrict domain access to this share?</label>
                      <div class="custom-radiobtn-group">
                          <label for="radio-restrict-domain-yes">Yes                   
                              <input type="radio" name="restrict_domain" @if($session_data['domain_restriction']) checked="checked" @endif   value="1" class="radio-invite-admin restrict-domain" id="radio-restrict-domain-yes">
                              <span class="custom-radiobtn"></span>
                          </label>
                      </div>
                      <div class="custom-radiobtn-group">
                          <label for="radio-restrict-domain-no">No
                              <input type="radio" name="restrict_domain" @if(!$session_data['domain_restriction']) checked="checked" @endif  value="0" class="radio-invite-admin restrict-domain" id="radio-restrict-domain-no">
                              <span class="custom-radiobtn"></span>
                          </label>
                      </div>
                      <div id="restrict_domain-radio-error" class="error-msg"></div>
                      </div>

                      <div class="slider-toggle-off-content full-width @if($session_data['domain_restriction']) hidden @endif">
                          <p>Your share will not be restricted to any domains.</p>
                      </div>
                      
                      <div class="slider-toggle-on-content add-restrict-domain-wrap full-width @if(!$session_data['domain_restriction']) hidden @endif">
                      <div class="logo-upload-column company-category-content">
                        <p>Which domains should your share be resticted to?</p>
                      </div>

                      @if(isset($onboarding_data['metadata']['rule']) && sizeOfCustom($onboarding_data['metadata']['rule']))
                        
                        @foreach($onboarding_data['metadata']['rule'] as $rule_index => $rule)
                          <div class="tour-domain-list twitter-handle full-width form-group">
                              <span class="link-input-icon"><p>{{$rule_index+1}}</p></span>
                              <div class="text-left">
                              <input name="rule[]" type="text" class="form-control full-width" placeholder="@mycompany.com" autocomplete="off" @if($rule_index == 0) readonly @endif value="{{$rule['value']}}">
                            </div>
                          </div>
                        @endforeach

                      @else

                        @php 
                          $default_domain_list=1;
                        @endphp
                        @while ($default_domain_list<=1)
                          @if($default_domain_list == 1)
                          <div class="tour-domain-list twitter-handle full-width form-group">
                              <span class="link-input-icon"><p>{{$default_domain_list}}</p></span> 
                              <div class="text-left">
                              <input name="rule[]" type="text" class="form-control full-width" value="{{explode('@',Auth::user()->email)[1]}}" placeholder="@mycompany.com" autocomplete="off" readonly>
                            </div>
                          </div>
                          @else
                          <div class="tour-domain-list twitter-handle full-width form-group">
                              <span class="link-input-icon"><p>{{$default_domain_list}}</p></span>
                              <div class="text-left">
                              <input name="rule[]" type="text" class="form-control full-width" placeholder="@mycompany.com" autocomplete="off">
                            </div>
                          </div>
                          @endif
                          @php
                            $default_domain_list++
                          @endphp
                        @endwhile
                      @endif

                      <div class="full-width text-left">
                        <a href="#!" class="add-domain">
                          <span class="category-add-icon">
                          <img src="{{ url('/images/v2-images/add_small_icon.svg',[],env('HTTPS_ENABLE', true)) }}">
                          </span>
                          Add more domains
                        </a>
                      </div>
                      </div>
                    </div>
                  </div>

                <div class="user-welcome-footer btn-group full-width">
                  <a class="btn btn-secondary transparent-btn tour-previous-step" href="javascript:void(0);">Back</a>
                  <a class="btn btn-primary wlcm-next-btn tour-next-step" href="javascript:void(0);">Next</a>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
<!--  Domain Management Code End-->


      <!-- Nearly Code Start -->
      <div data-step="6" class="welcome-cs-popup company-logo wc-twitter-col full-width {{$session_data['share_profile_progress']==6?'':'hidden'}}">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">Add Posts</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
          </button>
        </div>
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="user-welcome-text">
              <p>Nearly there! Before you can invite your customers to your new Share you need to add some content. (We don’t want them seeing a blank Share do we..) Once you have added 5 posts, you will be able to invite your customer!</p>
            </div>

            <div class="modal-body">
            <div class="welcome-tour-categorie-col full-width">
            <div class="logo-upload-column text-left company-category-content">
              <p class="info-text"><img src="{{asset('images/v2-images/info-icon.svg')}}" alt="Email Invitation Information" />We recommend that add 2 or 3 operational posts as well as perhaps a piece of good news or thought leadership. 
Welcome videos are very engaging for your customer!</p>
            </div>
            
                <div class="user-welcome-footer btn-group full-width">
                  <a class="btn btn-secondary transparent-btn tour-previous-step" href="javascript:void(0);">Back</a>
                  <a class="btn btn-primary wlcm-next-btn add_post_trigger" href="javascript:void(0);">ADD A POST</a>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
      <!-- Nearly Code End -->
    </div>
  </div>
</div>
<script type="text/javascript">
  var share_setup_steps = <?php echo $session_data['share_profile_progress']; ?>;
</script>