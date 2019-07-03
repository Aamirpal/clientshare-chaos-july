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
  <div class="modal-dialog">
    <div class="modal-content white-popup">
      <div class="form-submit-loader share-update-loader hidden">
         <span></span>
      </div>  

      <!-- Welcome text Code Start  -->
      <div data-step="1" class="step-one welcome-cs-popup company-logo full-width {{$session_data['share_setup_steps']<=1?'':'hidden'}}">
        <div class="modal-body">
          <div class="user-welcome-col">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width"></div>
            <div class="user-welcome-text text-center">
              <h2>Hello, {{$user['first_name']}}! <span class="full-width">Welcome to {{$onboarding_data['share_name']}} Client Share</span></h2>
              <p>Client Share is an easy to use platform that improves <br />the relationship between buyers & suppliers.</p>
            </div>

            <div class="user-welcome-step-row full-width">
              <div class="user-welcome-step-col">
                <div class="step-container">
                  <span class="step-icon">
                    <img src="{{ url('/images/step01.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 1" />
                  </span>
                </div>
                <p>Personalise <br />your feed</p>
              </div>
               <div class="user-welcome-step-col">
                <div class="step-container">
                  <span class="step-icon">
                    <img src="{{ url('/images/step02.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 2" />
                  </span>
                </div>
                <p>Add company<br />categories</p>
              </div>
               <div class="user-welcome-step-col">
                <div class="step-container">
                  <span class="step-icon">
                    <img src="{{ url('/images/step03.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 3" />
                  </span>
                </div>
                <p>Create your <br />first posts</p>
              </div>
               <div class="user-welcome-step-col">
                <div class="step-container">
                  <span class="step-icon">
                    <img src="{{ url('/images/step04.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 4" />
                  </span>
                </div>
                <p>Invite your <br />community</p>
              </div>
            </div>

            <div class="user-welcome-footer text-center full-width">
              <a class="btn btn-primary tour-next-step" href="#">Let’s get started</a>
            </div>

          </div>
        </div>
      </div>
      <!-- Welcome text Code End  -->



      <!-- Invite Admin Code Start -->
      <div data-step="2" class="welcome-cs-popup company-logo wc-twitter-col full-width manage_invite_feed_modal welcome_invite_feed_modal {{$session_data['share_setup_steps']==2?'':'hidden'}}" id="manage_invite_feed_modal">
        <div class="modal-body">
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width">
              <span class="mobile-welcome-page-count pull-left"><span class="mobile-welcome-count-strat">7</span> of 10</span>
            </div>

            <div class="user-welcome-text text-center">
              <h2>Invite an admin to help set up your Share</h2>
              <p>There are 8 steps to complete before you can add your community to your share. Delegate your <br /> set up tasks by adding another admin.</p>
            </div>

            <div class="welcome-tour-categorie-col full-width">
              <form method="post" action="{{ env('APP_URL') }}/save_invite_admin" enctype="multipart/form-data" class="invite_admin_form" id="onboarding_invite_admin">
              {!! csrf_field() !!}
                <div class="edit-categories edit-admin-invite text-center">
                    
                  <div class="wc-domain-col full-width">
                    <div class="link-columns twitter-handle-wrap">
                      <div class="logo-upload-column text-center company-category-content">
                        <p>Admins have additional permissions to manage the community <br /> and edit or delete share content.</p>
                      </div>

                      <div class="admin-invite-result-box full-width text-left hidden">
                      </div>

                      <div class="admin-invite-box full-width">
                        <div class="twitter-input-col">
                          <input class="form-control twitter-feed-input" name="admin_invite[]" placeholder="Email address" type="text">
                        </div>
                        <div class="twitter-input-col">
                          <input class="form-control twitter-feed-input" name="admin_invite[]" placeholder="Email address" type="text">
                        </div>
                        <div class="twitter-input-col">
                          <input class="form-control twitter-feed-input" name="admin_invite[]" placeholder="Email address" type="text">
                        </div>
                      </div>  
                      <input type="hidden" class="admin_invite_subject" name="subject" value="{{ Auth::user()->first_name }} has invited you to be admin of the {{ $session_data['share_name'] }}">  
                      <input type="hidden" class="admin_invite_body" name="body" value="I'm inviting you to join the {{ $session_data['share_name'] }} as an administrator.">        

                      <div class="full-width text-left">
                        <a href="javascript:void(0)" class="add-admin-invite-link">
                          <span class="category-add-icon handle-add-icon">
                            <img src="{{ url('/images/ic_add.svg',[],env('HTTPS_ENABLE', true)) }}">
                          </span>
                          Add more admin users
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="user-welcome-footer text-center full-width">
                  <a class="btn btn-primary transparent-btn tour-next-step btn-invite-back" href="javascript:void();">Skip</a>
                  <button type="button" class="btn btn-primary wlcm-next-btn btn-invite-handle" data-space="">Invite</button>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
      <!-- Invite Admin Code End -->


      <!-- Company Logo Code Start -->
      <div data-step="3" class="welcome-cs-popup company-logo full-width {{$session_data['share_setup_steps']==3?'':'hidden'}}">
        <div class="modal-body">
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width">
              <span class="mobile-welcome-page-count pull-left"><span class="mobile-welcome-count-strat">2</span> of 10</span>
             </div>

            <div class="user-welcome-text text-center">
              <h2>Company logos</h2>
              <p>Personalising your Share with company logos is really important. <br />Logos appear in your Client Share banner and help your community to quickly identify a share. </p>
            </div>

            <div class="logo-upload-column text-center">
              <div class="upload-logo-view">
                <span class="seller-logo-preview" style="background-image: url('{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                <span class="buyer-logo-preview space-pic" style="background-image: url('{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
              </div>
              <p>Twitter Logos work best. You can upload your logos from your device or provide us with the company specific Twitter handles and we can do the hard work for you.</p>
            </div>

            <form id="update_welcome_share_logo" class="text-center" action="#" enctype="multipart/form-data">
            {{csrf_field()}}
              <div class="share-twitter-handle">

                <div class="upload-logo-name seller full-width">
                  <p>Your company</p>
                  <div class="twitter-input-col">
                    <input class="form-control seller_twitter_name twitter-feed-input" name="seller_twitter_name" placeholder="@twitterhandle" type="text">
                      <span class="twitter-close remove-handle hidden">
                        <img src="{{ url('/images/ic_delete_hover.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                  </div>
                  <span>OR</span>
                  <div class="share-logo-upload-device">
                    <span class="mobile-upload-icon hidden-sm hidden-md hidden-lg">
                      <img src="{{ url('/images/ic_file_upload.svg',[],env('HTTPS_ENABLE', true)) }}">
                    </span>
                    <a href="javascript:void(0)" id="upload_seller_logo" class="upload-logo title">Upload from device</a>
                    <div class="email-error image-error hidden">Only images are allowed</div>
                    <div class="uploaded-logo-name hidden">
                      <span class="title"></span>
                      <span class="remove-logo">
                        <img src="{{ url('/images/ic_delete_small_blue.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                    </div>
                      <input id="seller-logo" type="file" name="seller_logo" accept="image/*" class="hidden">
                      <input id="seller-twitter-logo" type="hidden" name="seller_twitter_logo" class="twitter-logo-url">
                      <input type="hidden" name="seller_logo_url" id="hidden_seller_logo" value="{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}">
                  </div>
                </div>
                <div class="upload-logo-name buyer">
                  <p>Customer company</p>
                  <div class="twitter-input-col">
                      <input class="form-control buyer_twitter_name twitter-feed-input" name="buyer_twitter_name" placeholder="@twitterhandle" type="text">
                      <span class="twitter-close remove-handle hidden">
                        <img src="{{ url('/images/ic_delete_hover.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                  </div>
                  <span>OR</span>
                  <div class="share-logo-upload-device">
                    <span class="mobile-upload-icon hidden-sm hidden-md hidden-lg">
                      <img src="{{ url('/images/ic_file_upload.svg',[],env('HTTPS_ENABLE', true)) }}">
                    </span>
                    <a href="javascript:void(0)" id="upload_buyer_logo" class="upload-logo title">Upload from device</a>
                    <div class="email-error image-error hidden">Only images are allowed</div>
                    <div class="uploaded-logo-name hidden">
                      <span class="title"></span>
                        <span class="remove-logo">
                          <img src="{{ url('/images/ic_delete_small_blue.svg', [], env('HTTPS_ENABLE', true)) }}">
                        </span>
                    </div>
                    <input id="buyer-logo" type="file" name="buyer_logo" accept="image/*" class="hidden" >
                    <input id="buyer-twitter-logo" type="hidden" name="buyer_twitter_logo" class="twitter-logo-url">
                    <input type="hidden" id="hidden_buyer_logo" name="buyer_logo_url" value="{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}">
                  </div>
                </div>
              </div>
              <div class="user-welcome-footer text-center full-width">
                <input type="hidden" id="hidden_share_logo" name="share_banner_url" value="@if(!empty(wrapUrl($session_data['background_image']))){{ wrapUrl($session_data['background_image']) }}@endif">
                <input type="hidden" name="space_id" value="{{ Session::get('space_info')['id'] }}">
                <input type="hidden" name="seller_name" value="{{ Session::get('space_info')['SellerName']['company_name']}}">
                <input type="hidden" name="buyer_name" value="{{ Session::get('space_info')['BuyerName']['company_name']}}">
                <a class="btn btn-primary transparent-btn tour-previous-step tour-back-invite-admin" href="javascript:void();">Back</a>
                <a class="btn btn-primary wlcm-next-btn onboarding_company_logo" href="javascript:void();">Next</a>
              </div>
            </form>

            <div class="welcome-tour-bottom-slider full-width text-center hidden-xs">
              <ul>
                <li class="active"><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
      <!-- Company Logo Code End -->


      <!--Upload banner Code Start -->
      <div data-step="4" class="welcome-cs-popup company-logo full-width {{$session_data['share_setup_steps']==4?'':'hidden'}}">
        <div class="modal-body">
        <form id="update_welcome_share_banner" class="text-center" action="#" enctype="multipart/form-data">
            {{csrf_field()}}
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width">
              <span class="mobile-welcome-page-count pull-left"><span class="mobile-welcome-count-strat">3</span> of 10</span>
              <!-- <span class="mobile-expand-icon pull-right">
                <img src="{{ url('/images/minimise.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 1" />
              </span> -->
            </div>

            <div class="user-welcome-text text-center">
              <h2>Banner</h2>
              <p>Personalise your share. Banners appear across the top of each Client Share and help your customer to quickly identify each Share.</p>
            </div>
           
            <div class="welcome-upload-banner-row">
              <div class="edit-share-banner-col share-banner-preview" style="background-image: url('{{(!empty(wrapUrl($session_data['background_image'])))? wrapUrl($session_data['background_image']) : env('APP_URL').'/images/bgIimg.jpg'}}');">
                <div class="edit-share-banner-content">
                  <div class="col-xs-12 col-sm-8">
                    <div class="space-pic-wrap">
                       <span class="seller-logo-preview" style="background-image: url('{{ $session_data['company_seller_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                       <span class="buyer-logo-preview space-pic" style="background-image: url('{{ $session_data['company_buyer_logo']??url('/images/login_user_icon.png',[],env('HTTPS_ENABLE', true)) }}');"></span>
                    </div>
                    <div class="share-name-show text-left">
                      <span class="welcome-tour-preview hidden-xs">Welcome to your</span>
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

              <div class="welcome-share-banner-section full-width">
                <div class="select-share-banner-container full-width">
                  <div class="share-logo-upload-device pull-left upload-logo-name banner">
                    <div class="uploaded-logo-name hidden">
                      <span class="title"></span>
                      <span class="remove-logo">
                        <img src="{{ url('/images/ic_delete_small_blue.svg', [], env('HTTPS_ENABLE', true)) }}">
                      </span>
                    </div>
                  </div>
                  <div class="welcome-share-banner-row full-width">
                    <div class="select-share-banner-row full-width">
                      <div class="select-share-banner-col full-width">
                      @foreach(Config::get('constants.BANNER_IMAGES') as $banner)
                          <div class="select-share-banner-part share-banner-images" banner_url="{{ url($banner,[],env('HTTPS_ENABLE', true)) }}" style="background-image: url('{{ url($banner,[],env('HTTPS_ENABLE', true)) }}');"></div>
                      @endforeach
                      </div>
                    </div>
                  </div>
                  <div class="upload-col-icon full-width upload-logo-name banner text-left welcome-banner-image-error">
                    <span class="upload-icon">
                      <img src="{{ url('/images/ic_file_upload.svg', [], env('HTTPS_ENABLE', true)) }}">
                    </span>
                    <a href="#!" id="upload_banner" class="upload-logo title">Upload your own</a>
                    <div class="email-error image-error hidden">Only images are allowed</div>
                    <div class="email-error image-error banner-image-error text-left"></div>
                    <div class="uploaded-logo-name hidden">
                      <span class="title"></span>
                      <span class="remove-logo"><img src="{{ url('/images/ic_delete_small_blue.svg', [], env('HTTPS_ENABLE', true)) }}"></span>
                    </div>
                    <input id="share-banner" type="file" name="share_banner" accept="image/*" class="hidden">
                    <input type="hidden" id="hidden_share_logo" name="share_banner_url" value="@if(!empty(wrapUrl($session_data['background_image']))){{ wrapUrl($session_data['background_image']) }}@endif">
                    <input type="hidden" name="banner_image" id="banner-image" />
                    <input type="hidden" name="space_id" value="{{ Session::get('space_info')['id'] }}">
                    <input type="hidden" name="seller_name" value="{{ Session::get('space_info')['SellerName']['company_name']}}">
                    <input type="hidden" name="buyer_name" value="{{ Session::get('space_info')['BuyerName']['company_name']}}">
                  </div>
                </div>
              </div>
            </div>

            <div class="user-welcome-footer text-center full-width">
              <a class="btn btn-primary transparent-btn tour-previous-step" href="javascript:void();">Back</a>
              <a class="btn btn-primary wlcm-next-btn onboarding_company_logo" href="javascript:void();">Next</a>
            </div>
            </form>

            <div class="welcome-tour-bottom-slider full-width text-center hidden-xs">
              <ul>
                <li><a href="#"></a></li>
                <li class="active"><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
      <!--Upload banner Code End -->


      <!-- Company Categories Code Start -->
      <div data-step="5" class="welcome-cs-popup company-logo full-width {{$session_data['share_setup_steps']==5?'':'hidden'}}">
        <div class="modal-body">
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width">
              <span class="mobile-welcome-page-count pull-left"><span class="mobile-welcome-count-strat">4</span> of 10</span>
              <!-- <span class="mobile-expand-icon pull-right">
                <img src="{{ url('/images/minimise.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 1" />
              </span> -->
            </div>

            <div class="user-welcome-text text-center">
              <h2>Company Categories</h2>
              <p>Create categories for your Client Share. Users will be able to add and find content in these specific categories, making finding content simpler.</p>
            </div>

            <div class="logo-upload-column text-center company-category-content">
              <p>We’ve found the most popular categories to be the ones below, but you can customise these categories and add your own too.</p>
            </div>

            <div class="welcome-tour-categorie-col full-width">
              <form action="" method="post" name="" class="full-width category_form">
                <div class="edit-categories">
                    <ul class="category_edit_list">
                      @php 
                        $counter = 0;
                      @endphp

                      @foreach($onboarding_data['category_tags'] as $category_key => $category)
                      <li class="tour-category-list @if($counter > 5) tour-category-list-new-add @endif">
                        <input name="{{$category_key}}" class="form-control box category_value" type="text" value="{{$category}}" {{$loop->first?'readonly':''}} maxlength="25">
                        <span class="letter-count count_cat hidden"><span class="category-count">0</span>/25</span>
                        <span class="category_error"></span>
                        @if($counter > 5)
                        <span class="wc-categories-delete">
                            <img src="{{ url('/images/ic_deleteBlue.svg', [] , env('HTTPS_ENABLE', true)) }}">
                        </span>
                        @endif
                      </li>
                      @php
                        $counter++;
                      @endphp
                      @endforeach
                    </ul>
                    <div class="full-width"><span class="category_error_duplicacy"></span></div>
                    <div class="full-width">
                      <a href="#!" class="add-category-link add-tour-category">
                        <span class="category-add-icon">
                          <img src="{{ url('/images/ic_add.svg',[],env('HTTPS_ENABLE', true)) }}">
                        </span>
                        Add your own
                      </a>
                    </div>
                  </div>
                <div class="user-welcome-footer text-center full-width">
                  <a class="btn btn-primary transparent-btn tour-previous-step" href="javascript:void();">Back</a>
                  <a class="btn btn-primary wlcm-next-btn tour-next-step" href="javascript:void();">Next</a>
                </div>
              </form>
            </div>

            <div class="welcome-tour-bottom-slider full-width text-center hidden-xs">
              <ul>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li class="active"><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
      <!-- Company Categories Code End -->


      <!-- Executive Summary Code Start -->
      <div data-step="6" class="welcome-cs-popup company-logo full-width {{$session_data['share_setup_steps']==6?'':'hidden'}}">
        <div class="modal-body">
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width">
              <span class="mobile-welcome-page-count pull-left"><span class="mobile-welcome-count-strat">5</span> of 10</span>
              <!-- <span class="mobile-expand-icon pull-right">
                <img src="{{ url('/images/minimise.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 1" />
              </span> -->
            </div>

            <div class="user-welcome-text text-center">
              <h2>Executive Summary</h2>
              <p>An executive summary is a paragraph, telling your community about your business relationship. Here, you should also share a key document and a welcome video that summarises your business relationship with your client in seconds.</p>
            </div>

            <div class="welcome-executive-col full-width">
              <div class="logo-upload-column text-left company-category-content">
                <h6>Our Example:</h6>
                <p>This Client Share will be used for managing our relationship with Example & Co. We will be sharing MI, best practice, cost savings, our roadmap and building a community. We’ve been working with you for x2 years and look forward to growing our business relationship with you.</p>
              </div>

             <form class="executive_summary_save" method="POST" enctype="multipart/form-data" action="#" id="welcome_executive_summary_save">
             {{ csrf_field() }}
                <div class="welcome-executive-input-col full-width">
                  <h3>Your Executive Summary:</h3>
                  <div class="relative full-width">
                    <div class="executive-textarea-col full-width">
                      <textarea class="form-control summary_box" maxlength="300" placeholder="Start typing" name="space[executive_summary]" type="text" onkeyup="countExecutiveCharExec(this)" autofocus="">{{ $data->executive_summary }}</textarea>
                      <span class="letter-count">
                         <span class="executive_character_number" val="{{$length}}">{{$length}}</span>
                         /300
                      </span>
                    </div>
                    @if ($errors->has('errorexecutive'))
                     <span class="error-msg text-left">
                     {{ $errors->first('errorexecutive') }}
                     </span>
                     @endif
                     <input type='hidden' name="user[id]" value="{{ Auth::user()->id }}">
                     <input type='hidden' name="space[id]" value="{{ $data->id }}">
                     <input type='hidden' name="onboarding_data" value="1">
                     <div id="upload_video_name" class="pdf_list_file"></div>
                     <div id="upload_pdf_name" class="pdf_list_file"></div>
                  </div>
                </div>

                <div class="fileupload fileupload-new full-width" data-provides="fileupload">
                <div class="upload-preview-wrap">
                   <div class="selected_files">
                      <input type="hidden" value="" class="post-media-data">
                      <input type="hidden" class="already_uploaded_pdf_file" value="">
                      <div class="pdf_list_file remove_executive_file" style="display: none;">
                         <span class="link-input-icon">
                            <img src="{{ url('/images/ic_link.svg',[],env('HTTPS_ENABLE', true)) }}">
                         </span>
                         <span></span>
                         <a href="#!">
                            <img src="{{ url('/images/ic_highlight_remove.svg',[],env('HTTPS_ENABLE', true)) }}" alt='' id="" class="delete_summary_files">
                         </a>
                      </div>
                      <input type="hidden" class="saved_summary_pdf_del" name="saved_summary_pdf_del" value="">
                      <input type="hidden" class="already_uploaded_video_file" value="">
                      <div class="pdf_list_file remove_executive_file" style="display: none;">
                         <span class="link-input-icon">
                            <img src="{{ url('/images/ic_link.svg',[],env('HTTPS_ENABLE', true)) }}">
                         </span>
                         <span></span>
                         <a href="#!">
                            <img src="{{ url('/images/ic_highlight_remove.svg',[],env('HTTPS_ENABLE', true)) }}" alt='' id="" class="delete_summary_files">
                         </a>
                      </div>
                      <input type="hidden" class="saved_summary_video_del" name="saved_summary_video_del" value="">
                   </div>
                   <div id="upload_video_name"></div>
                   <span class="fileupload-preview"></span>
                   <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none; margin-left: 10px; opacity: 1; color: #0d47a1;">x</a>
                </div>
                <span class="invite-btn btn-file full-width" >
                   <div class="upload_doc_col" >
                      <span>
                         <img src="{{ url('/images/ic_file_upload_icon.svg',[],env('HTTPS_ENABLE', true)) }}">
                      </span>
                      <span class="fileupload-new" onclick="upload_executive_file();">Upload key document or video</span>
                   </div>
                   <span class="fileupload-exists">Upload key document or video</span>
                   <span class="file_upload_error"></span>
                   <input type="hidden" class="upload_video_hidden" value="">
                   <input type="hidden" class="already_uploaded_file" id="already_uploaded_file" value="">
                   <input type="hidden" class="upload_pdf_hidden" value="">
                </span>
             </div>

                <div class="user-welcome-footer text-center full-width">
                  <a class="btn btn-primary transparent-btn tour-previous-step" href="javascript:void();">Back</a>
                  <button class="btn-quick-links wlcm-next-btn btn btn-primary onboarding_save_executive_btn" id="onboarding_save_executive_btn" type="button">Next</button>
                </div>
                <div style="display: none;">        
                   <input type="text" class="executive_aws_files_data" name="aws_files_data">
                   <input type="text" class="deleted_executive_aws_files_data" name="deleted_aws_files_data">
                   <input type="text" class="delete_summary_files_inp" name="delete_summary_files_inp">         
                </div>
              </form>
            </div>            

            <div class="welcome-tour-bottom-slider full-width text-center hidden-xs">
              <ul>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li class="active"><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
      <!-- Executive Summary Code End -->


      <!-- Quick Links Code Start -->
      <div data-step="7" class="welcome-cs-popup company-logo wc-quick-col full-width {{$session_data['share_setup_steps']==7?'':'hidden'}}">
        <div class="modal-body">
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width">
              <span class="mobile-welcome-page-count pull-left"><span class="mobile-welcome-count-strat">6</span> of 10</span>
              <!-- <span class="mobile-expand-icon pull-right">
                <img src="{{ url('/images/minimise.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 1" />
              </span> -->
            </div>

            <div class="user-welcome-text text-center">
              <h2>Quick Links</h2>
              <p>This is where you share external links and tools you use to manage your relationship with your customer.</p>
            </div>

            <div class="logo-upload-column text-center company-category-content">
              <p>Helpdesks, Intranets, Shared Portals, blogs, whatever you like!</p>
            </div>

            <div class="welcome-tour-categorie-col full-width">
               <form method="post" action="{{ url('/save_quick_links',[],env('HTTPS_ENABLE', true)) }}" enctype="multipart/form-data" class="quick_links_form" id="quick_links_form">
               {!! csrf_field() !!}
                <div class="edit-categories">
                  <input type="hidden" name="user_id" id="links_user_id" autocomplete="off" value="{{ Auth::user()->id }}">
                  <div class="link-columns full-width">
                  <div class="col-md-6 quick-link-col-form">
                     <span class="link-input-icon"><img src="{{ url('/images/ic_link.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                     <input type="text" class="form-control hyperlink hyperlink_0" placeholder="Hyperlink" aria-describedby="basic-addon1" name="hyperlink[]" autocomplete="off">
                  </div>
                  <div class="col-md-6 quick-link-col-form">
                    <span class="link-input-icon hidden-sm hidden-md hidden-lg">
                      <img src="{{ url('/images/text.svg',[],env('HTTPS_ENABLE', true)) }}">
                    </span>
                     <input type="text" class="form-control link_name link_name_0" placeholder="Text to display" aria-describedby="basic-addon1" name="link_name[]" autocomplete="off" maxlength="35">
                  </div>

                  <div class="col-md-6 quick-link-col-form">
                    <span class="link-input-icon"><img src="{{ url('/images/ic_link.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                    <input type="text" class="form-control hyperlink hyperlink_1" placeholder="Hyperlink" aria-describedby="basic-addon1" name="hyperlink[]" autocomplete="off">
                  </div>
                  <div class="col-md-6 quick-link-col-form">
                    <span class="link-input-icon hidden-sm hidden-md hidden-lg">
                      <img src="{{ url('/images/text.svg',[],env('HTTPS_ENABLE', true)) }}">
                    </span>
                    <input type="text" class="form-control link_name link_name_1" placeholder="Text to display" aria-describedby="basic-addon1" name="link_name[]" autocomplete="off" maxlength="35">
                  </div>

                  <div class="col-md-6 quick-link-col-form hidden-xs">
                     <span class="link-input-icon"><img src="{{ url('/images/ic_link.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                     <input type="text" class="form-control hyperlink hyperlink_2" placeholder="Hyperlink" aria-describedby="basic-addon1" name="hyperlink[]" autocomplete="off">
                  </div>
                  <div class="col-md-6 quick-link-col-form hidden-xs">
                     <input type="text" class="form-control link_name link_name_2" placeholder="Text to display" aria-describedby="basic-addon1" name="link_name[]" autocomplete="off" maxlength="35">
                  </div>

                  <div class="col-md-6 quick-link-col-form hidden-xs">
                     <span class="link-input-icon"><img src="{{ url('/images/ic_link.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                     <input type="text" class="form-control hyperlink hyperlink_3" placeholder="Hyperlink" aria-describedby="basic-addon1" name="hyperlink[]" autocomplete="off">
                  </div>
                  <div class="col-md-6 quick-link-col-form hidden-xs">
                     <input type="text" class="form-control link_name link_name_3" placeholder="Text to display" aria-describedby="basic-addon1" name="link_name[]" autocomplete="off" maxlength="35">
                  </div>
                 </div>
                  <span class="quick_links_error"></span>
                  </div>
                <div class="user-welcome-footer text-center full-width">
                  <a class="btn btn-primary transparent-btn tour-previous-step" href="javascript:void();">Back</a>
                  <button type="button" class="btn btn-primary wlcm-next-btn btn-quick-links btn-quick-links-button" onclick="sendOnboardingQuickLinks(this)" user_id="{{ Auth::user()->id }}">Next</button>
                </div>
              </form>
            </div>

            <div class="welcome-tour-bottom-slider full-width text-center hidden-xs">
              <ul>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li class="active"><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
      <!-- Quick Links Code End -->


      <!-- Twitter Code Code Start -->
      <div data-step="8" class="welcome-cs-popup company-logo wc-twitter-col full-width {{$session_data['share_setup_steps']==8?'':'hidden'}} manage_twitter_feed_modal welcome_twitter_feed_modal" id="manage_twitter_feed_modal">
        <div class="modal-body">
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width">
              <span class="mobile-welcome-page-count pull-left"><span class="mobile-welcome-count-strat">7</span> of 10</span>
              <!-- <span class="mobile-expand-icon pull-right">
                <img src="{{ url('/images/minimise.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 1" />
              </span> -->
            </div>

            <div class="user-welcome-text text-center">
              <h2>Twitter</h2>
              <p>Make it easier for both parties in your relationship to keep up to date with<br /> your company news on Twitter.</p>
            </div>

            <div class="welcome-tour-categorie-col full-width">
              <form method="post" action="{{ env('APP_URL') }}/save_twitter_feed" enctype="multipart/form-data" class="twitter_feed_form" id="onboarding_twitter_handles">
              {!! csrf_field() !!}
                <div class="edit-categories text-center">
                    
                  <div class="wc-domain-col full-width">
                    <div class="link-columns twitter-handle-wrap">
                      <div class="logo-upload-column text-left company-category-content">
                        <p>Add your preferred feeds simply by adding the Twitter handle below. Don’t worry, you can change these at any time. You can have a maximum of 3.</p>
                      </div>
                      <div class="twitter-handle-wrap-column">
                      <input name="space_id" id="twitter_feed_space_id" autocomplete="off" value="{{$data->id}}" type="hidden">
                      @if(!empty($twitter_handles))
                          @foreach($twitter_handles as $handle_index => $handle_value)
                              <div class="col-md-12 twitter-handle tour-twitter-list">
                                 <span class="link-input-icon"><p>{{$handle_index + 1}}</p></span>
                                 <div class="twitter-input-col">
                                     <input type="text" name="twitter_handles[]" value="{{$handle_value}}" id="twitter_handle_{{$handle_index}}" class="form-control twitter-feed-input" placeholder="@twitterhandle" autocomplete="off" >
                                     <span class="twitter-close remove-handle"><img src="{{ url('/images/ic_delete_hover.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                                 </div>
                              </div>
                         @endforeach
                      @else
                          <div class="col-md-12 twitter-handle tour-twitter-list">
                             <span class="link-input-icon"><p>1</p></span>
                             <div class="twitter-input-col">
                              <input type="text" name="twitter_handles[]" value="" id="twitter_handle_0" class="form-control twitter-feed-input" placeholder="@twitterhandle" autocomplete="off">
                              <span class="twitter-close remove-handle"><img src="{{ url('/images/ic_delete_hover.svg',[],env('HTTPS_ENABLE', true)) }}"></span>
                              </div>
                          </div>
                      @endif
                      </div>

                      <div class="full-width text-left">
                        <a href="javascript:void(0)" class="add-category-link add-twitter-feed add-handles" @php if(!empty($twitter_handles) && sizeOfCustom($twitter_handles) >= 3) echo 'style ="display:none"' @endphp >
                            <span class="category-add-icon handle-add-icon">
                              <img src="{{ url('/images/ic_add.svg',[],env('HTTPS_ENABLE', true)) }}">
                            </span>Add feed
                        </a>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="user-welcome-footer text-center full-width">
                  <a class="btn btn-primary transparent-btn tour-previous-step" href="javascript:void();">Back</a>
                  <button type="button" class="btn btn-primary wlcm-next-btn btn-twitter-handle btn-quick-links" data-space="">Next</button>
                </div>
              </form>
            </div>

            <div class="welcome-tour-bottom-slider full-width text-center hidden-xs">
              <ul>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li class="active"><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
      <!-- Twitter Code End -->


      <!-- Domain Management Code Code Start -->
      <div data-step="9" class="welcome-cs-popup company-logo wc-twitter-col full-width {{$session_data['share_setup_steps']==9?'':'hidden'}}">
        <div class="modal-body">
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width">
              <span class="mobile-welcome-page-count pull-left"><span class="mobile-welcome-count-strat">8</span> of 10</span>
              <!-- <span class="mobile-expand-icon pull-right">
                <img src="{{ url('/images/minimise.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 1" />
              </span> -->
            </div>

            <div class="user-welcome-text text-center">
              <h2>Domain Management</h2>
              <p>Restrict your community members to a list of approved email domains, meaning invites are locked down to specific companies. Alternatively, your Share can be open to all domains. If you are sharing sensitive content then you should restrict domains; it’s easy to add new domains.</p>
            </div>

            <div class="welcome-tour-domain-col full-width">
              <form action="" method="post" name="" class="full-width save-domain">
                <div class="text-center">
                    
                  <div class="wc-domain-col full-width">
                    <div class="link-columns twitter-handle-wrap">
                    
                      <div class="slider-toggle-label text-left full-width">
                        <div class="slider-toggle-text">
                          <h5>Restrict domain access to this share</h5>
                        </div>
                        <label class="switch pull-right">
                          <input class="restrict-domain-check" type="checkbox" {{($onboarding_data['domain_restriction'] == 1)?'checked':''}}>
                          <span class="slider round"></span>
                        </label>
                      </div>

                      <div class="slider-toggle-off-content text-center full-width @if($session_data['domain_restriction']) hidden @endif">
                        <p>Your share will not be restricted to any domains.</p>
                      </div>
                      
                      <div class="slider-toggle-on-content full-width @if(!$session_data['domain_restriction']) hidden @endif">
                      <div class="logo-upload-column text-left company-category-content">
                        <p>Which domains should your share be resticted to?</p>
                      </div>

                      @if(isset($onboarding_data['metadata']['rule']) && sizeOfCustom($onboarding_data['metadata']['rule']))
                        
                        @foreach($onboarding_data['metadata']['rule'] as $rule_index => $rule)
                          <div class="col-md-12 tour-domain-list  twitter-handle full-width">
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
                        @while ($default_domain_list<=4)
                          @if($default_domain_list == 1)
                          <div class="col-md-12 tour-domain-list  twitter-handle full-width">
                            <span class="link-input-icon"><p>{{$default_domain_list}}</p></span>
                            <div class="text-left">
                              <input name="rule[]" type="text" class="form-control full-width" value="{{explode('@',Auth::user()->email)[1]}}" placeholder="@mycompany.com" autocomplete="off" readonly>
                            </div>
                          </div>
                          @else
                          <div class="col-md-12 tour-domain-list  twitter-handle full-width">
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
                            <img src="{{ url('/images/ic_add.svg',[],env('HTTPS_ENABLE', true)) }}">
                          </span>
                          Add more domains
                        </a>
                      </div>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="user-welcome-footer text-center full-width">
                  <a class="btn btn-primary transparent-btn tour-previous-step" href="javascript:void();">Back</a>
                  <a class="btn btn-primary wlcm-next-btn tour-next-step" href="javascript:void();">Next</a>
                </div>
              </form>
            </div>

            <div class="welcome-tour-bottom-slider full-width text-center hidden-xs">
              <ul>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li class="active"><a href="#"></a></li>
                <li><a href="#"></a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
      <!-- Domain Management Code End -->


      <!-- Nearly Code Start -->
      <div data-step="10" class="welcome-cs-popup company-logo wc-twitter-col full-width {{$session_data['share_setup_steps']==10?'':'hidden'}}">
        <div class="modal-body">
          <div class="user-welcome-col user-welcome-logo-col full-width">

            <div class="mobile-expand-column hidden-sm hidden-md hidden-lg full-width">
              <span class="mobile-welcome-page-count pull-left"><span class="mobile-welcome-count-strat">9</span> of 10</span>
              <!-- <span class="mobile-expand-icon pull-right">
                <img src="{{ url('/images/minimise.svg',[],env('HTTPS_ENABLE', true)) }}" alt="Step 1" />
              </span> -->
            </div>

            <div class="user-welcome-text text-center">
              <h2>You're nearly done...</h2>
              <p>One more task to complete! You can’t invite your community to an empty Share!<br /> Add at least five posts before sending out any invitations.<br /> Get some inspiration from the post examples below.</p>
            </div>

            <div class="welcome-tour-categorie-col full-width">
              <form action="" method="post" name="" class="full-width">
                <div class="edit-categories text-center">
                  <div class="wc-post-slider">
                    <div class="text-center">

                      <div id="myCarousel" class="carousel slide hidden-xs" data-ride="carousel">
                        <!-- Wrapper for slides -->
                        <div class="carousel-inner">
                          <div class="item active">
                            <span class="wc-slide-img" style="background-image:url('{{ url('/images/e01.svg',[],env('HTTPS_ENABLE', true)) }}')"></span>
                          </div>
                          <div class="item">
                            <span class="wc-slide-img" style="background-image:url('{{ url('/images/e02.svg',[],env('HTTPS_ENABLE', true)) }}')"></span>
                          </div>
                          <div class="item">
                            <span class="wc-slide-img" style="background-image:url('{{ url('/images/e03.svg',[],env('HTTPS_ENABLE', true)) }}')"></span>
                          </div>
                          <div class="item">
                            <span class="wc-slide-img" style="background-image:url('{{ url('/images/e04.svg',[],env('HTTPS_ENABLE', true)) }}')"></span>
                          </div>
                          <div class="item">
                            <span class="wc-slide-img" style="background-image:url('{{ url('/images/e05.svg',[],env('HTTPS_ENABLE', true)) }}')"></span>
                          </div>
                        </div>
                        <!-- Left and right controls -->
                        <a class="left carousel-control" href="#myCarousel" data-slide="prev">
                          <span class="glyphicon glyphicon-chevron-left">
                            <img  class="img-responsive" src="{{ url('/images/ic_left.svg',[],env('HTTPS_ENABLE', true)) }}" alt="">
                          </span>
                          <span class="sr-only">Previous</span>
                        </a>
                        <a class="right carousel-control" href="#myCarousel" data-slide="next">
                          <span class="glyphicon glyphicon-chevron-right">
                            <img  class="img-responsive" src="{{ url('/images/ic_right.svg',[],env('HTTPS_ENABLE', true)) }}" alt="">
                          </span>
                          <span class="sr-only">Next</span>
                        </a>
                      </div>



                      <div id="myCarouselmbl" class="carousel slide hidden-sm hidden-md hidden-lg" data-ride="carousel">
                        <!-- Wrapper for slides -->
                        <div class="carousel-inner">
                          <div class="item active">
                            <span class="wc-slide-img" style="background-image:url('{{ url('/images/E1M.png',[],env('HTTPS_ENABLE', true)) }}')"></span>
                          </div>
                          <div class="item">
                            <span class="wc-slide-img" style="background-image:url('{{ url('/images/E2M.png',[],env('HTTPS_ENABLE', true)) }}')"></span>
                          </div>
                          <div class="item">
                            <span class="wc-slide-img" style="background-image:url('{{ url('/images/E3M.png',[],env('HTTPS_ENABLE', true)) }}')"></span>
                          </div>
                          <div class="item">
                            <span class="wc-slide-img" style="background-image:url('{{ url('/images/E4M.png',[],env('HTTPS_ENABLE', true)) }}')"></span>
                          </div>
                        </div>
                        <!-- Left and right controls -->
                        <a class="left carousel-control" href="#myCarouselmbl" data-slide="prev">
                          <span class="glyphicon glyphicon-chevron-left">
                            <img  class="img-responsive" src="{{ url('/images/ic_left.svg',[],env('HTTPS_ENABLE', true)) }}" alt="">
                          </span>
                          <span class="sr-only">Previous</span>
                        </a>
                        <a class="right carousel-control" href="#myCarouselmbl" data-slide="next">
                          <span class="glyphicon glyphicon-chevron-right">
                            <img  class="img-responsive" src="{{ url('/images/ic_right.svg',[],env('HTTPS_ENABLE', true)) }}" alt="">
                          </span>
                          <span class="sr-only">Next</span>
                        </a>
                      </div>


                    </div>
                  </div>
                </div>
                <div class="user-welcome-footer text-center full-width">
                  <a class="btn btn-primary transparent-btn tour-previous-step" href="javascript:void();">Back</a>
                  <a class="btn btn-primary wlcm-next-btn add_post_trigger" href="javascript:void();">ADD A POST</a>
                </div>
              </form>
            </div>

            <div class="welcome-tour-bottom-slider full-width text-center hidden-xs">
              <ul>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
                <li class="active"><a href="#"></a></li>
              </ul>
            </div>

          </div>
        </div>
      </div>
      <!-- Nearly Code End -->
    </div>
  </div>
</div>
<script>
  var share_setup_steps = <?php echo $session_data['share_setup_steps']; ?>;
  $('#myCarousel').carousel({
    interval: 0
});
</script>