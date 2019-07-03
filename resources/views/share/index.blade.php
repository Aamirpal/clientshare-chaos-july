@extends('layouts.super_admin')
@section('content')
<div class="add_clientshare_section">
   <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 add_clientshare_heading_section">
      <h4><a href="javascript:void(0)" id="addclientshare">ADD CLIENT SHARE</a></h4>
   </div>
      <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 add_clientshare_table_section">
         <div class="table-responsive create-share-admin">
            <table class="table">
               <tbody>
                  <form action="clientshare" method="post" enctype="multipart/form-data" class="add_space_form">
                     {{ csrf_field() }}
                     <tr id="clientsharerow">
                        <td class="add_info_link {{ $errors->first('seller.seller_name')?'has-error':'' }}">
                           <input readonly=readonly class="form-control addinputpopup" type="text" name="seller[seller_name]" id="seller" value="{{ old('seller.seller_name')??'' }}" placeholder="Seller Name" >
                           <span class="error-msg text-left">
                           {{$errors->first('seller.seller_name')}}
                           </span>
                           <input type="file" id="file1" class="file1" name="seller[seller_logo]" style="display: none;">
                        </td>
                        <td class="add_info_link {{ $errors->first('buyer.buyer_name')?'has-error':'' }}">
                           <input readonly=readonly class="form-control addinputpopup" type="text" name="buyer[buyer_name]" id="buyer" value="{{ old('buyer.buyer_name')??'' }}" placeholder="Buyer Name" >
                           <span class="error-msg text-left">
                           {{$errors->first('buyer.buyer_name')}}
                           </span>
                           <input type="file" id="file2" class="file2" name="buyer[buyer_logo]" style="display: none;">
                        </td>
                        <td class="add_info_link {{ $errors->first('share.share_name')?'has-error':'' }}">
                           <input readonly=readonly class="form-control addinputpopup" type="text" id="client_share_name" name="share[share_name]" value="{{ old('share.share_name')??'' }}" placeholder="Client Share Name">
                           <span class="error-msg text-left">
                           {{$errors->first('share.share_name')}}
                           </span>
                        </td>
                        <td class="add_info_link {{ $errors->first('user.email')?'has-error':'' }}">
                           <input readonly=readonly class="form-control addinputpopup" type="text" id="admin_email" name="user[email]" value="{{ old('user.email')??'' }}" placeholder="Admin Email">
                           <span class="error-msg text-left">
                           {{$errors->first('user.email')}}
                           </span>
                        </td>
                        <td class="add_info_link {{ $errors->first('user.first_name')?'has-error':'' }}"> <input readonly=readonly class="form-control addinputpopup" type="text" id="admin_first_name" name="user[first_name]" value="{{ old('user.first_name')??'' }}" placeholder="Admin First name">
                           <span class="error-msg text-left">
                           {{$errors->first('user.first_name')}}
                           </span>
                        </td>
                        <td class="add_info_link {{ $errors->first('user.last_name')?'has-error':'' }}"><input readonly=readonly class="form-control addinputpopup" type="text" id="admin_last_name" name="user[last_name]" value="{{ old('user.last_name')??'' }}" placeholder="Admin Last Name">
                           <span class="error-msg text-left">
                           {{$errors->first('user.last_name')}}
                           </span>
                        </td>
                        <td style="padding-top: 5px; padding-bottom: 5px; display:none" class="initial_pop_list">
                          <div class="dropdown hover-dropdown open">
                             <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">
                             <span></span>
                             </a>
                             <ul class="dropdown-menu">
                                <li><a class="share_preview_modal_trigger initiate ini_preview" style="display:none; text-transform:none; cursor: pointer;"  data-toggle="modal" data-target="#share_preview_modal">Preview</a></li>
                                <li><a href="#" class="share_preview_modal_trigger clickbuton">Customize</a></li>
                                <li><a style="cursor:pointer;" class="restrict_modal" data-toggle="modal" data-target="#myRestrictModal">Restrict Domain</a></li>
                                <li><a style="cursor:pointer;" class="subcompany_modal" data-toggle="modal" data-target="#mySubCompanyModal">Sub-companies</a></li>


                              </ul>
                            </div>
                        </td>
                        <td style="padding-top: 5px; padding-bottom: 5px; border-left: none;">
                           <button disabled type="button" class="btn btn-default initiate_btn" data-toggle="modal" data-target="#myModal">INITIATE</button>
                        </td>
                     </tr>
                     <input type="hidden" class="sellername" name="sellername" value="">
                     <input type="hidden" class="sellerlogo" name="sellerlogo" value="">
                     <input type="hidden" class="buyername" name="buyername" value="">
                     <input type="hidden" class="buyerlogo" name="buyerlogo" value="">
                     <input type="hidden" class="bannerbackground" name="bannerbackground" value="">
                     <input type="hidden" class="sellertype" name="sellertype" value="">
                     <input type="hidden" class="buyertype" name="buyertype" value="">
                     <input type="hidden" class='domain_restriction' name="share[domain_restriction]" value="1">
                      <input type="hidden" class='sub_company' name="share[sub_companies]" value="0">
                  </form>
               </tbody>
            </table>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 add_clientshare_table_section">
         <div class="table-responsive">
            <table class="table" id="example" class="display" width="100%" cellspacing="0">
              <thead>
                 <tr>
                    <!-- <th width="20%">Company Name</th> -->
                    <th width="11%">Seller Name</th>
                    <th width="11%">Buyer Name</th>
                    <th width="15%">Client Share Name</th>
                    <th width="12%">Admin Email</th>
                    <th width="11%">Admin First Name</th>
                    <th width="11%">Admin Last Name</th>
                    <th width="7%">Created At</th>
                    <th width="7%">Status</th>
                    <th width="3%">Feedback</th>
                    <th width="3%">version</th>
                    <th width="2%"></th>
                 </tr>
              </thead>
              <tfoot>
                 <tr class="tf-search">
                    <!-- <th width="20%">Company Name</th> -->
                    <th width="11%">Seller Name</th>
                    <th width="11%">Buyer Name</th>
                    <th width="15%">Client Share Name</th>
                    <th width="12%">Admin Email</th>
                    <th width="11%">Admin First Name</th>
                    <th width="11%">Admin Last Name</th>
                    <th width="7%">Created At</th>
                    <th width="7%">Status</th>
                    <th width="3%">Feedback</th>
                    <th width="3%">version</th>
                    <th width="2%"></th>
                 </tr>
              </tfoot>
               <tbody>
                  @foreach($data as $key=>$row )
                  <tr rowid="{{ $row['data']['id'] }}" id="adminrow{{ $row['data']['id'] }}" >
                     <input class="buyer_logo" type='hidden' value='{{ wrapUrl(composeUrl($row["data"]["buyer_logo"])) }}' >
                     <input class="seller_logo" type='hidden' value='{{ wrapUrl(composeUrl($row["data"]["seller_logo"])) }}' >
                     <input class="backgroundlogo" type='hidden' value='{{ wrapUrl(composeUrl($row["data"]["background_image"])) }}' >
                     <td class="editable seller_name">{{ $row['data']['seller_name']['company_name'] }}</td>
                     <td class="editable buyer_name">{{ $row['data']['buyer_name']['company_name'] }}</td>
                     <td class="editable share_name">{{ $row['data']['share_name'] }}</td>
                     @if(!empty($row['data']['admin_user']))
                     <td >{{ $row['data']['admin_user']['email'] }}</td>
                     <td >{{ ucfirst($row['data']['admin_user']['first_name']) }}</td>
                     <td >{{ ucfirst($row['data']['admin_user']['last_name']) }}</td>
                     <td ><span style="display:none">{{ $row['data']['created_at'] }}</span>{{ date("d/m/Y", strtotime($row['data']['created_at'])) }}</td>
                     <td>

                      @if(isset($row['data']['spaceuser']['metadata']['user_profile']))
                        @if(isset($row['spaceBuyer']) && !empty($row['spaceBuyer']))
                          <div class="seller-status active">
                            <span class="state-alert "></span>Active
                            <span class="arrow-shadow">Buyers have joined</span>
                          </div>
                        @endif
                        @if(isset($row['spaceBuyer']) && empty($row['spaceBuyer']))
                          <div class="seller-status pending">
                            <span class="state-alert "></span>Pending
                            <span class="arrow-shadow">No Buyers have joined</span>
                          </div>
                        @endif
                      @else
                      <div class="seller-status inactive">
                        <span class="state-alert "></span>Inactive
                        <span class="arrow-shadow">The admin has not joined</span>
                      </div>
                      @endif
                    </td>
                     @else
                     <td></td>
                     <td></td>
                     <td></td>

                     @endif
                     <td ><span style="display:none">{{$row['data']['feedback_status']?Carbon\Carbon::parse($row['data']['feedback_status_to_date'])->format('M-Y'):'NA'}}</span>{{$row['data']['feedback_status']?Carbon\Carbon::parse($row['data']['feedback_status_to_date'])->format('M-Y'):'NA'}}</td>
                    <td><span class="version-name-{{$row['data']['id']}}">{{$row['data']['version_name']}}</span></td>
                     <td>

                        <div class="dropdown hover-dropdown">
                         <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                         <span></span>
                         </a>
                         <ul class="dropdown-menu">
                            <li><a class="share_preview_modal_trigger" style="text-transform:none; cursor: pointer;" compid="{{ $row['data']['company_id'] }}"  data-toggle="modal" data-target="#share_preview_modal">Preview</a></li>

                            <li><a class="edit_id" style="text-transform:none; cursor: pointer;" spaceid="{{ $row['data']['id'] }}" userid="{{ $row['data']['user_id'] }}" compid="{{ $row['data']['company_id'] }}"  data-toggle="modal" data-target="#myModal1">Edit</a></li>
                            <li><a href="#" class="share_preview_modal_trigger clickbutonedit" id="{{ $row['data']['id'] }}">Customize</a></li>

                            @if(!isset($row['data']['spaceuser']['metadata']['user_profile']))
                            <li><a class="resend_invite" style="text-transform:none; cursor: pointer;" spaceid="{{ $row['data']['id'] }}" userid="{{ $row['data']['user_id'] }}" compid="{{ $row['data']['company_id'] }}"  data-toggle="modal" data-target="#myModalResend">Resend Invite</a></li>
                            @endif
                            <li><a class="delete_share" spaceid="{{ $row['data']['id'] }}" style="text-transform:none; cursor: pointer; color: #ff5252;" id="" data-toggle="modal" data-target="#myModalDelete">Delete</a></{{ $row['data']['id'] }}li>
                             <li><a class="user_copy_share" spaceid="{{ $row['data']['id'] }}" style="text-transform:none; cursor: pointer;" id="" data-toggle="modal" data-target="#migrate-user">Migrate user to new share</a></li>
                            <li>
                              <a class="update-version" data-space-id="{{$row['data']['id']}}" data-version="{{+$row['data']['version']}}">@if($row['data']['version']) Revert platform version @else Update platform version @endif</a>
                            </li>
                            <li>
                                <a id="show_manage_share_popup" class="share-br-modal" style="text-transform:none; cursor: pointer;" data-space-id="{{$row['data']['id']}}"  data-is_business_review_enabled="{{$row['data']['is_business_review_enabled']}}">Manage Share</a>
                            </li>
                         </ul>
                        </div>

                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>



    <button type="button" class="btn btn-primary btn-lg modelbutton" data-toggle="modal" data-target="#myModalCrop" style="display: none;"></button>




    <!-- Crop pic popup -->
    <div class="modal fade car_edit_upload" id="myModalCrop" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <img alt="" src="{{ url('/') }}/images/ic_highlight_removegray.svg">
            </button>
            <h4 class="modal-title " id="myModalLabel">Adjust Photo</h4>
            <p>Drag the image to adjust position.</p>
            <div class="alert-danger"></div>
          </div>
          <div class="modal-body">
            <div class="form-submit-loader profile_loader" style="display:none"><span></span></div>
            <div class="image-editor">

              

              <div class="cropit-preview"></div>

              <p class="dimension-note">Dimensions of uploaded banner image should be not less than 1280x128 pixels</p>

              <div class="cropit-footer">
                <div class="row"><div class="col-sm-2 change-photo-wrap"><label class="change-photo">Change photo. <input type="file" class="cropit-image-input"></label><span class="rotate-cw"></span></div>
                <div class="col-sm-6 range_block">

                <div class="range-slider">
                <input type="range" class="cropit-image-zoom-input range-slider__range" min="0" max="1" step="0.01">

                <input type="hidden" class="edit_background" value="" />
                <input type="hidden" name="_token" class="_token" value="{{ csrf_token() }}" /> </div></div>
                <div class="col-sm-4 crop-btns"> <button data-dismiss="modal" aria-hidden="true" class="savecropimage btn btn-primary">Save</button>
                 <button data-dismiss="modal" aria-hidden="true" class="btn btn-light-white" id="cancelpopup">Cancel</button>
                </div></div>
              </div>
            </div>


          </div>
        </div>
      </div>
    </div>
    <!-- Crop pic popup -->

    <!-- Show img URL -->
    <!-- <img src="" id="getimg"/> -->
    <!-- Show img URL -->

<!-- Share Pre-veiw modal start -->
<div class="modal fade pro_info_member in dashboard-modal" id="share_preview_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="user_profile">
   <div class="modal-dialog " role="document">
      <div class="modal-header">

        <div class="preview-header">
          <div class="preview-header-button">
            <span class="dashboard-space-pic-wrap">
              <img class="buyer_mini" src="" alt="">
              <img class="space-pic seller_mini" src="" alt="">
            </span>
            <span class="share_name_mini"></span>
          </div>
        </div>

         <span class="success-msg white_box_info" style="display:none">Restricted email access to domains </span>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/')}}/images/ic_highlight_removegray.svg" alt=""></button>
      </div>
      <div class="modal-content white-popup ">
         <div class="modal-body">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 welcome_share_wrap">
               <div class="space-pic-wrap">
                  <span class='buyer'></span>
                  <span class='seller space-pic'></span>
               </div>
               <input type="hidden" value="8daeaf02-d266-11e6-b604-44a842f6cdbf" name="share_id" class="hidden_space_id">
               <div class="user-pop-detail">
                  <h1>
                    <span class="small">Welcome to your</span>
                    <span class="share_name">Client Share</span>
                  </h1>
               </div>
            </div>
         </div>
         <div class="modal-footer">
         </div>
      </div>
      <!-- white popoup -->
   </div>
</div>

<!-- Share Pre-veiw modal end -->
<!-- Edit Model -->
<div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"></div>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
   <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Initiate Space?</h4>
         </div>
         <div class="modal-body">
            <p>The admin will receive an email link which they can use to register on this Space.</p>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button" class="btn btn-default modal_initiate_btn" onclick="submit_form('add_space_form')">INITIATE</button>

         </div>
      </div>
   </div>
</div>



 <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#myModalResend" style="display: none;">Resend Invite</button>

  <!-- Modal -->
  <div class="modal fade" id="myModalResend" role="dialog">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close">x</button>
          <h4 class="modal-title">Resend Invite</h4>
        </div>
        <div class="modal-body">
          <p>Do you want to resend invite?</p>
        </div>
        <div class="modal-footer">
        <input type="hidden" class="access_token" value="{{ csrf_token() }}" />
          <input type="hidden" class="admin_resend_user_id" value="">
          <input type="hidden" class="admin_resend_space_id" value="">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
           <button type="button" class="btn btn-primary send_invite_from_james" >Send</button>
        </div>
      </div>
    </div>
  </div>



 <div class="modal fade" id="myRestrictModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
<div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 class="modal-title" id="myModalLabel">Restricted Domains</h4>
         </div>
         <div class="modal-body">
           <p>Would you like to restrict the email domains that can be invited to this Client Share?</p>
           <input class="domain_restriction_trigger" type="checkbox" id="restricted-domains" style="display: none" checked>
           <label class="restricted-domains" for="restricted-domains">Restricted Domains:</label>
           <!-- <p>If the box is ticked then the restricted domains is on (default), if the box is unticked then restricted domains is turned off.
If the reselect Restrict Domain before initiating the share then the ticked/unticked state should load again i.e. changes are kept.</p> -->
         </div>
         <!-- <div class="modal-footer">
            <button type="button" class="btn btn-default domain_restriction_no" data-dismiss="modal">No</button>
            <button type="button" class="btn btn-primary domain_restriction_yes" data-dismiss="modal">Yes</button>
         </div> -->
      </div>
   </div>
   </div>
   <!-- Close Restricted popup-->

<!--modal for deleting sahre-->
      <div class="modal fade" id="myModalDelete" role="dialog">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close-inner" data-dismiss="modal">x</button>
          <h4 class="modal-title">Delete <span class="add_share_name"></span></h4>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this Client Share?</p>
        </div>
        <div class="modal-header">
        <input type="checkbox" name="email_alert_prevent" class="email_alert_prevent" value="true" checked="checked"> User email alert (Uncheck to turn off user email alert) 
        </div>
        <div class="modal-footer">
        <input type="hidden" class="access_token" value="{{ csrf_token() }}" />
          <input type="hidden" class="" value="">
          <input type="hidden" class="" value="">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
           <button type="button" class="btn btn-primary delete-share-yes" >Delete</button>
        </div>
      </div>
    </div>
  </div>
<!--end modal for deleting sahre-->

 <div class="modal fade migrate-user-popup" id="migrate-user" role="dialog">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-body">
                  <div class="heading_wrap">
                     <h4 class="title">SCRIPT to map existing users to new share</h4>
                  </div>
                  <div class="tab-inner-content"> 
                   <span class="user-copy-error"></span>  
                   <span class="user-copy-success"></span>         
                     <form class="user-invitation-form user-copy-form" id="user-invitation-form" action="migrate_user" method="POST" enctype="multipart/form-data">

                       <div class="flash-message alert alert-success" role="alert" style="display: none">
                          <strong>Holy guacamole!</strong> You should check in on some of those fields below.
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>

                        {{csrf_field()}}
                        

                        <div class="col-xs-12 input-group description-area">
                          <select class="multiselect-shares shares" name="new_share_name">
                            <option>Select Share</option>
                            @foreach($data as $share)
                              <option value="{{$share['data']['id']}}">{{$share['data']['share_name']}}</option>
                            @endforeach
                          </select>
                        </div>
                        

                        <div class="col-xs-12 input-group description-area">
                           <select class="multiselect-shares communities" name="company_name">
                             <option>Select community</option>
                           </select>
                        </div>
                        

                        <div class="col-lg-8 col-md-8 col-sm-10 col-xs-12 input-group description-area">
                           <input type="file" class='invitation-file' name="user_list" value="Upload CSV">
                           <div class="bulk-invitation-progress-info"></div>
                        </div>

                        <div class="description-area user-invite-admin">
                          <p>To map existing users to a new share, please upload a CSV file with the following 3 columns: first_name, last_name and email.</p>
                        </div>

                        <div class="exisitin-user-submit description-area user-invite-admin">
                          <input type="hidden" class='old_share_id' name='old_share_id' value="">
                          <input type="button" value="Submit" class="btn btn-primary right copy-user-trigger" href="javascript:void();">
                          <input data-dismiss="modal" type="button" value="Cancel" class="btn btn-primary right copy-user-cancel" href="javascript:void();">
                        </div>
                     </form>
                  </div>
               </div>
            </div>
        </div>
     </div>

<!-- Popup start -->
<div style="display:none">
   <div class="add_info_popup">
      <h4>Company name</h4>
      <div>
         <input type="text" class="form-control popup_input" placeholder="Company name" onclick="" onkeypress="return save_popup_input(event, this)" id="false" value="">
         <div class="logo_api1"></div>
         <div class="logo_api2"></div>
      </div>
      <label for="file1">
        <span style="display:none" class="btn btn-primary show-logo1 white-button">Upload Logo</span>
      </label>
      <label for="file2">
        <span style="display:none" class="btn btn-primary show-logo2 white-button">Upload Logo</span>
      </label>
      <div class="close-preview">
         <!-- <span class="corssimag1"><img src="{{ url('/') }}/images/ic_highlight_removegray.svg"></span> -->
         <!--  <span class="crossimg1"></span>
            <span class="crossimg2"></span> -->
         <img id="blah" alt="" width="100%" height="100%" /><img src="{{ url('/') }}/images/ic_highlight_removedarkgray.svg" class="cross-img" id="blah_cross" alt="" />
         <img id="blah1" alt="" width="100%" height="100%" /><img src="{{ url('/') }}/images/ic_highlight_removedarkgray.svg" class="cross-img" id="blah1_cross" alt="" />
      </div>
      <div class="btn-section">
         <button class="btn btn-default" data-dismiss="modal" type="button" onclick="$('.add_info_popup_temp').remove();">Cancel</button>
         <button class="btn btn-default" type="button" onclick="set_input_value(this,true)">Save</button>
      </div>
   </div>
</div>
<!-- Popup end  -->

<!-- Sub Company popup-->
   <div class="modal fade" id="mySubCompanyModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
           <div class="modal-header">
              <button type="button" class="close-inner sub_companies_cross" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" class="close-btn-sub-comp ">×</span></button>
              <h4 class="modal-title" id="myModalLabel">Sub-companies</h4>
           </div>
           <div class="modal-body">

             <input class="sub_company_trigger" type="checkbox" id="sub-company" style="" >
             <label class="sub-company" for="sub-company">Enable sub-companies on this Client Share
             </label>
             <input class="sub_company_checkbox_temp" type="hidden" value="" >
           </div>
           <div class="modal-footer">
              <button type="button" class="btn btn-default sub_companies_no" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary sub_companies_yes" data-dismiss="modal">Save</button>
           </div>
        </div>
     </div>
     </div>
     <!-- Close Restricted popup-->
     <div class="modal fade" id="manage_share_popup" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
         <div class="modal-dialog modal-sm" role="document">
             <div class="modal-content">
                 <div class="modal-header">
                     <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                     <h4 class="modal-title" id="myModalLabel">Manage Share</h4>
                 </div>
                 <div class="modal-body">
                     <form class="form-group manage-flex manage-share-popup-form">
                       <div class="rename-categories-wrap">
                         <label>Rename Categories</label>
                         <div class="category-list-input"></div>
                         <input type="hidden" class="manage-share-popup-space-id" name="space_id" value="">
                        </div>  
                         <div class="show-review-div">
                          <label for="business_review">Show Business Reviews</label>
                          <input id="business_review" type="checkbox" name="business_review" value="1" checked="">
                         </div>
                      </form>
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                     <button type="button" space_id=''  id="business_review_visibility" class="btn btn-primary" >Save</button>
                 </div>
             </div>
         </div>
     </div>

     <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{ url('css/sweetalert2(6.6.9).min.css?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}">


<style>
tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
    }
    /*tfoot {
    display: table-header-group !important;
}*/
#example_filter {
  display: none;
}
</style>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function(){
    $('.seller-status').tooltip();

});
</script>
<script>
   $(document).ready(function(){

    /* */
    $('.domain_restriction_trigger').on('change', function(){
      // alert($(this).is(":checked"));
      $('input[class=domain_restriction]').val( $(this).is(":checked") );
    });

    $('#example tfoot th').not(":eq(9),:eq(10)").each( function () {
      var title = $('#example thead th').eq( $(this).index() ).text();
      $(this).html( '<input type="text" placeholder="Search" />' );
    });

    // DataTable
    var table = $('#example').DataTable({
      "order": [[ 6, "desc" ]],
     "bPaginate": false,
    "bLengthChange": false,
    "bFilter": true,
    "bInfo": false,
    "bAutoWidth": false,
    responsive: true,
   'aoColumnDefs': [{
        'bSortable': false,
        'aTargets': [-1,-2], /* 1st one, start by the right */
        "sType": "date-uk"
    }]
});
$('#example tfoot tr').insertAfter($('#example thead tr'));
    // Apply the search
    table.columns().eq( 0 ).each( function ( colIdx ) {
        if (colIdx == 9 || colIdx == 10) return; //Do not add event handlers for these columns

        $( 'input', table.column( colIdx ).footer() ).on( 'keyup change', function () {
            table
                .column( colIdx )
                .search( this.value )
                .draw();
        } );
    } );

$(document).on('click','th[aria-controls="example"]',function(){
  $("#clientsharerow").hide();
});
      /* Check preview while intiating */
      // $(document).on('change', '.addinputpopup', function() {
      $('.addinputpopup').change(function() {
       //alert( $(this).val() );
      });

      /* Trigger Share preview start */
      $(document).on('click', '.share_preview_modal_trigger', function() {
        if( $(this).hasClass('initiate') ){

          $('#share_preview_modal .buyer_mini').attr('src', $('.sellerlogo').val() );
          $('#share_preview_modal .seller_mini').attr('src', $('.buyerlogo').val() );
          $('#share_preview_modal .share_name_mini').html( $('input[name="seller[seller_name]"]').val()+" & "+$('input[name="buyer[buyer_name]"]').val() );


          $('#share_preview_modal .buyer').css('background-image', 'url("'+$('.sellerlogo').val()+'")');
          $('#share_preview_modal .seller').css('background-image', 'url("'+$('.buyerlogo').val()+'")');
           $('#share_preview_modal .white-popup').css('background-image', 'url("")');

          $('#share_preview_modal').find('.share_name').html( $('input[name="share[share_name]"]').val()+" Client Share" );

        } else {
          parent_ele = $(this).parent().parent().parent().parent().parent();
          $('#share_preview_modal .buyer_mini').attr('src', parent_ele.find('.seller_logo').val());
          $('#share_preview_modal .seller_mini').attr('src', parent_ele.find('.buyer_logo').val());


          $('#share_preview_modal .share_name_mini').html( parent_ele.find('.seller_name').html()+" & "+parent_ele.find('.buyer_name').html() );

          $('#share_preview_modal .buyer').css('background-image', 'url("'+parent_ele.find('.seller_logo').val()+'")');
          $('#share_preview_modal .seller').css('background-image', 'url("'+parent_ele.find('.buyer_logo').val()+'")');
          $('#share_preview_modal .white-popup').css('background-image', 'url("'+parent_ele.find('.backgroundlogo').val()+'")');
         /* $('#share_preview_modal .white-popup').css('background-image', 'url("'+$('.bannerbackground').val()+'")');*/

          $('#share_preview_modal').find('.share_name').html( parent_ele.find('.share_name').html()+" Client Share" );
        }
      });

      // alert($("#myModal").is(":visible"));
      $("#myModal").on('shown.bs.modal', function () {
         $(".modal_initiate_btn").focus();
      });




      if({{ sizeOfCustom($errors) }}){
         $("#clientsharerow").show();
      }

      $("#addclientshare").click(function(){
          $("#clientsharerow").fadeToggle( "fast", "linear" );
          $('input[name="seller[seller_name]"]').trigger('focus');
          //$('#blah').addClass('show-img');
          $('#blah').attr('src', $('.sellerlogo').val());
          $('.close-preview').show();
          $('#seller').val($('.sellername').val());
      });

      /* Display popup on input field */
       $(".addinputpopup").on('click, focus', function(){
        $('.logo_api1').html('');
        $('.logo_api2').html('');
        if($(".logo_api1").html()!=''){
          $(".logo_api1").html('');
        }
      //alert('data');
         $('.add_info_popup_temp').remove();
         $(this).parent().append( $('.add_info_popup').parent().html() ).show('slow');
         new_popup = $(this).parent().find('.add_info_popup');
         new_popup.addClass('add_info_popup_temp');
         new_popup.find('input').attr('id',$(this).attr('id'));
         var logo_check = $(this).attr('id');

         new_popup.find('h4').html($(this).attr('placeholder'));
         new_popup.find('input').attr('placeholder',$(this).attr('placeholder'));
         new_popup.find('input').val($(this).val());

         new_popup.find('input').focus();

         if( logo_check == 'seller') {
            $('.show-logo1').show();
            $('.show-logo2').hide();
            $('.close-preview').show();
            //$('#blah').addClass('show-img');
            $('#blah').attr('src', $('.sellerlogo').val());
            if($('.sellerlogo').val()!='') {
              $('#blah_cross').attr('src', "{{ url('/') }}/images/ic_highlight_removedarkgray.svg");
              $('#blah_cross').addClass('show-img');
              $('#blah').addClass('show-img');
             }
            $(".logo_api1").html('');


         } else if(logo_check == 'buyer') {
            $('.show-logo1').hide();
            $('.show-logo2').show();

            $('#blah1').attr('src', $('.buyerlogo').val());
            if($('.buyerlogo').val()!=''){
              $('#blah1_cross').attr('src', "{{ url('/') }}/images/ic_highlight_removedarkgray.svg");
              $('#blah1_cross').addClass('show-img');
              $('#blah1').addClass('show-img');
             }             //$('.crossimg2').show();
            $(".logo_api2").html('');
            //$('.close-preview').removeAtt('style');
            $('.close-preview').attr('style', 'display: block !important');

         }
         else{
          $('.show-logo1').hide();
          $('.show-logo2').hide();
          //$('.logo_api1').hide();
          //$('.logo_api2').hide();
         }

      });
   });
   var xhr = null;
   /* Set value to respective input box from popup */
   function set_input_value(popup_save_btn, remove) {
      ele = $(popup_save_btn).parent().parent().parent();
      ele.removeClass('has-error');
      $('.error-msg').text('');
      $(".initiate_btn").removeAttr("disabled");
      if(ele.find('.addinputpopup').attr('name') == 'user[last_name]'){
        $(".initiate_btn").removeAttr("disabled");
        $('.initial_pop_list').show();
      }
      ele.find('.addinputpopup').val($(popup_save_btn).parent().parent().find('input').val());
      if(remove){ $('.add_info_popup_temp').remove(); }
      if( xhr != null ) {
        xhr.abort();
        xhr = null;
       }
   }

   /*Submit form*/
   function submit_form(form_class){
      $('.modal_initiate_btn').attr('disabled', true);
      $('.'+form_class).submit();
   }
  var clearbit_id = "";
  var delayTimer;
   /* Save data on press enter on input*/
   function save_popup_input(event, ele) {
      //Js clearbit api function start
      clearTimeout(delayTimer);
      clearbit_id = $(ele).attr('id');
      var not_allowed_for_search = ['admin_email','admin_first_name','admin_last_name','client_share_name'];

      delayTimer = setTimeout(function() {
        var key = event.which;
       if(not_allowed_for_search.indexOf(clearbit_id)<0 && key != 13){
          clearbit_value = $(ele).val() + String.fromCharCode(event.which);
          $(".logo_api2").html('');
          $(".logo_api1").html('');

          xhr = $.ajax({
           type: "GET",
           url: './clearbitapi?clearbit_value='+clearbit_value+'&type='+clearbit_id,
           beforeSend : function(){
            if(clearbit_id=='seller'){
                $(".logo_api1").show();
                $(".logo_api1").html('loading...');
            }else{
                 $(".logo_api2").show();
                 $(".logo_api2").html('loading...');
            }
            if( xhr != null ) {
            xhr.abort();
            xhr = null;
            }
          },
          success: function (response) {
          $(".logo_api1").hide();
          $(".logo_api2").hide();
          if(clearbit_id=='seller'){
            //alert(1);
            $('.logo_api1').show();
            $(".logo_api1").html(response);
          }
          if(clearbit_id=='buyer'){
            //alert(2);
            $('.logo_api2').show();
            $(".logo_api2").html(response);
          }
          },error: function (message) { }
          });
       }
       else{
          $(".logo_api1").html('');$(".logo_api2").html('');
       }
        }, 500);
      //clearbit api function end
      var key = event.which;
      if(key == 13) {
        if( xhr != null ) {
                xhr.abort();
                xhr = null;
        }
         //$('.logo_api1').hide();
         //$('.logo_api2').hide();
         set_input_value(ele, false);
         td = $(".add_info_popup_temp").parent();
         if($(td).next().find(".addinputpopup").length){
            $(td).next().find(".addinputpopup").trigger("focus");

         } else {
            $('.add_info_popup_temp').remove();
            if(!$('input[name="seller[seller_name]"]').val() || !$('input[name="buyer[buyer_name]"]').val() || !$('input[name="share[share_name]"]').val()){
              $(".initiate_btn").attr('disabled', true);
              return false;
            }
            $(".ini_preview")[0].click();
            $(".initiate_btn").removeAttr("disabled");
            $('.initial_pop_list').show();
            // $(".initiate_btn").trigger("click");
         }
      }

      if($('input[name="seller[seller_name]"]').val().length &&
        $('input[name="buyer[buyer_name]"]').val().length &&
        $('input[name="share[share_name]"]').val().length){
        $('.ini_preview').show();
      }
   }

   $('#myModal1').on('show.bs.modal', function() {
        $('#contract-end-date').daterangepicker({
          singleDatePicker: true,
          minDate: new Date(),
          autoUpdateInput: false,
          drops:'up',
          locale: {
            format: 'MM/YY'
          }
       }, function(start) {
          $('#contract-end-date').val(start.format('MM/YY'));
          $('#contract-end-date-hidden').val(start.format('MM/DD/YY'));
      });

        $('#contract-end-date-hidden').daterangepicker({
          singleDatePicker: true,
          minDate: new Date(),
          autoUpdateInput: false,
          locale: {
            format: 'MM/DD/YY'
          }
       });

      $('#contract-end-date').on('apply.daterangepicker', function(ev, picker) {
          var picker = $(ev.target).data('daterangepicker');
          $('#contract-end-date-hidden').val(picker.startDate.format('MM/DD/YY'));
      });
   });

  

      $(document).on('click',"#edit_save", function(e){
        var regex = new RegExp(/^[+-]?\d+(\.\d+)?$/);
        var seller = $("#seller-name").val();
        var buyer = $("#buyer-name").val();
        var contract_value = $("#contract-value").val();
        var contract_end_date = $("#contract-end-date").val();
        var error_css = { "color": "#ff5252",
                  "font-size": "12px",
                  "letter-spacing": "0",
                  "line-height": "12px",
                  "margin-bottom": "9px",
                  "margin-top": "9px" };
        if($.trim(seller) == $.trim(buyer)){
             $('.new_confirm_error').css(error_css).html('Both seller/buyer names can not be same');
             return false;
        }else if(contract_value.trim() != '' && !regex.test(contract_value)){
             $('.new_confirm_error').css(error_css).html('Contract value can not be non numeric');
             return false;
        }

      $('.edit_share_form').find('.form-submit-loader').show();       
      $('.edit_share_form').submit();
       e.preventDefault();
      });

      $(document).on('change',"#seller-logo", function(e){
      //hidden_seller_logo
      $('#s_logo').hide();
      readURL3(this,'blah_edit1');
      $('#check_seller_logo_status').val('browsed');


      });

      $(document).on('change',"#buyer-logo", function(e){
      //hidden_seller_logo
      $('#b_logo').hide();
      readURL3(this,'blah_edit2');
      $('#check_buyer_logo_status').val('browsed');


      });
      function readURL3(input,id) {
        if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
            $('#'+id).show()
            $('#'+id).attr('src', e.target.result);
          }
          reader.readAsDataURL(input.files[0]);
        }
      }
</script>
<!-- For Edit -->
<script type="text/javascript">
   $(".edit_id").on('click', function(e){
     var space_id = $(this).attr('spaceid');
     var user_id = $(this).attr('userid');
     var company_id = $(this).attr('compid');
     $.ajax({
        type: "GET",
        async: false,
        url: './view_edit_share?space_id='+space_id+'&user_id='+user_id+'&company_id='+company_id,
        success: function (response) {
           $('#myModal1').html(response);
        }
      });
   });

   $('#save_edit_share').on('click', function(e){
    $('.edit_share_form').submit();
    e.preventDefault();
    return false;
   });




</script>
<script type="text/javascript">

   function readURL(input,id1,id2,logo,type,api_cls) {
     if (input.files && input.files[0]) {
       var reader = new FileReader();
       reader.onload = function (e) {
       $('#'+id1).attr('src', e.target.result);
       $('#'+id1).addClass('show-img');
       $('#'+id2).show();
       $('#'+id2).attr('src', "{{ url('/') }}/images/ic_highlight_removedarkgray.svg");
       $('#'+id2).addClass('show-img');
       $('.'+logo).val(e.target.result);
       $('.'+type).val('browse');
       $('.'+api_cls).parent().find('input').focus();

       }
       reader.readAsDataURL(input.files[0]);
     }
   }
   $("#file1").change(function () {
      /* restrict extention */
      block_ext = ['png', 'jpg', 'jpeg'];
      ext = $(this).val().split('.')[1].toLowerCase();
      if(block_ext.indexOf(ext) < 0){
        $(this).val('');
        $('#blah').attr('src','');
        $('.close-preview').hide();
        alert('Please upload image with "'+block_ext.toString()+'" extention.');
      }
      if(block_ext.indexOf(ext) >= 0){
        $('.close-preview').show();
      }
      /* restrict extention block-end */
     readURL(this,'blah','blah_cross','sellerlogo','sellertype','logo_api1');
   });
   $("#file2").change(function () {
    /* restrict extention */
      block_ext = ['png', 'jpg', 'jpeg'];
      ext = $(this).val().split('.')[1].toLowerCase();
      if(block_ext.indexOf(ext) < 0){
        $(this).val('');
        $('#blah1').attr('src','');
        $('.close-preview').hide();
        alert('Please upload image with "'+block_ext.toString()+'" extention.');
      }
      if(block_ext.indexOf(ext) >= 0){
        $('.close-preview').show();
      }
      /* restrict extention block-end */
     readURL(this,'blah1','blah1_cross','buyerlogo','buyertype','logo_api2');
   });

</script>
<!-- js select logo start-->
<script>
   $(document).ready(function(){
      $(document).on('click','.ffff',function(){
         var logo = $(this).attr('logo');
         var name = $(this).attr('name');
         var type = $(this).attr('type');
         if(type=='seller'){
            $('#blah').attr('src', logo);
            $('#blah').addClass('show-img');
            $('.close-preview').show();
            $('#seller').val(name);
            $('.sellerlogo').val(logo);
            $('.sellertype').val('api');
            $(".logo_api1").html('');
            $('.logo_api1').parent().find('input').val(name);
            $('.logo_api1').parent().find('input').focus();
            //if($('.sellerlogo').val()!=''){
             $('#blah_cross').show();
             $('#blah_cross').addClass('show-img');
             $('#blah_cross').attr('src', "{{ url('/') }}/images/ic_highlight_removedarkgray.svg");
            //}
         }
         if(type=='buyer'){
            $('#blah1').attr('src', logo);
            $('#blah1').addClass('show-img');
            $('.close-preview').show();
            $('#buyer').val(name);
            $('.buyerlogo').val(logo);
            $('.buyertype').val('api');
            $(".logo_api2").html('');
            $('.logo_api2').parent().find('input').focus();
            $('.logo_api2').parent().find('input').val(name);
            //if($('.buyerlogo').val()!=''){
              $('#blah1_cross').show();
              $('#blah1_cross').addClass('show-img');
              $('#blah1_cross').attr('src', "{{ url('/') }}/images/ic_highlight_removedarkgray.svg");

            //}
         }
      });
   });
   $('.crossimg2').hide();
   $('.crossimg1').hide();
   $(document).ready(function(){
   $(document).on('click','#blah_cross',function(){
      $(this).hide();

      $('#blah').attr('src', '');
      $('#blah').removeClass('show-img');
      $('#blah_cross').attr('src', '');
      $('#blah_cross').removeClass('show-img');
      $('.sellerlogo').val('');
      $('.logo_api1').parent().find('input').focus();
   });
   $(document).on('click','#blah1_cross',function(){
      $(this).hide();
      $('#blah1').attr('src', '');
      $('#blah1').removeClass('show-img');
      $('#blah1_cross').attr('src', '');
      $('#blah1_cross').removeClass('show-img');
      $('.buyerlogo').val('');
      $('.logo_api2').parent().find('input').focus();
   });

   $(document).on('click','.resend_invite',function(){
    var userid = $(this).attr("userid");
    var spaceid = $(this).attr("spaceid");
    $(".admin_resend_user_id").val(userid);
    $(".admin_resend_space_id").val(spaceid);

   });

     $(document).on('click','.send_invite_from_james',function(){
     var space_id = $(".admin_resend_space_id").val();
     var user_id = $(".admin_resend_user_id").val();
     var accesstoken = $('.access_token').val();
     $("#myModalResend .close-inner").trigger('click');
              $.ajax({
              type: "POST",
              dataType: "json",
              type: "GET",
              url: baseurl+'/resend_invite_from_james?spaceid='+space_id+'&user_id='+user_id,
              /*data: {"spaceid":space_id,"user_id":user_id,"accesstoken":accesstoken},
              url: baseurl+'/resend_invite_from_james',*/
              success: function (response) {  },
               complete: function(response) { location.reload(true); },
              error: function (xhr, status, error) {  }
              });
   });

   });

</script>
<!-- //select logo end -->
<script>
baseurl = "{{ env('APP_URL') }}";
      $(function() {
        $('.image-editor').cropit({
          imageBackground: true,
          imageBackgroundBorderWidth: 15,
          quality: 1,
          originalSize: true,
          imageState: {
            src: 'https://lorempixel.com/500/400/',
          },
        });

        $('.rotate-cw').click(function() {
          $('.image-editor').cropit('rotateCW');
        });
        $('.rotate-ccw').click(function() {
          $('.image-editor').cropit('rotateCCW');
        });
 $(document).on('click','.savecropimage',function(){
            $('.profile_loader').show();
             var spaceid = $('.edit_background').val();
             if(spaceid != "")
             {
              var exported = $('.image-editor').cropit('export');
              var token = $('._token').val();
              $.ajax({
              type: "POST",
              dataType: "json",
              data: {"spaceid":spaceid,"image":exported,"_token":token},
              url: baseurl+'/save_background_image',
              success: function (response) {
                location.reload(); },
              error: function (xhr, status, error) {
              console.log(error);alert(error);  }
              });
             }
             else
             {
             var exported = $('.image-editor').cropit('export');
             $('.bannerbackground').val(exported);
             }
        });

      });
    </script>
    <script>
    $('.clickbuton').click(function(){
       $('.cropit-image-input').trigger('click');
    });
    $('.clickbutonedit').click(function(){
      var space = $(this).attr("id");
      $('.edit_background').val(space);
       $('.cropit-image-input').trigger('click');
    });
    $('.cropit-image-input').change(function(){
      var imageval =  $(this).val();
      var fileUpload = $(".cropit-image-input")[0];
      var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(.jpg|.png|.gif)$");


                var reader = new FileReader();
                //Read the contents of Image File.
                reader.readAsDataURL(fileUpload.files[0]);
                reader.onload = function (e) {
                    //Initiate the JavaScript Image object.
                    var image = new Image();
                    //Set the Base64 string return from FileReader as source.
                    image.src = e.target.result;
                    image.onload = function () {
                        //Determine the Height and Width.
                        var height = this.height;
                        var width = this.width;
                        if (height < 128 || width < 1280) {
                          $(".alert-danger").html("Dimensions of uploaded banner image should be not less than 1280x128 pixels");
                          e.preventDefault();
                            //alert("Height and Width must not less than  1280px*128px.");
                            //$('.close').trigger('click');
                        }
                        else
                        {
                           $(".alert-danger").hide();
                        }
                    };
                }
      if($('#myModalCrop').is(':visible'))
      {
            return false;
      }
      if(imageval != "")
      {
        setTimeout(function(){
          $('.modelbutton').trigger('click');
        },500);

        //$(this).val('');
      }
    });
    $('#myModalCrop').on('hide.bs.modal', function () {
    $('.cropit-image-input').removeClass('hidetrigger');
    });


    $('#myRestrictModal').on('shown.bs.modal', function () {
      $('.restrict_modal').focus()
    });

     /*Only For sub company tick*/
       $('.sub_company_trigger').on('change', function(){
          //alert($(this).is(":checked"));
         //$('.sub_company_checkbox_temp').val( $(this).is(":checked") );
        });


      $('#mySubCompanyModal').on('shown.bs.modal', function () {
        $('.subcompany_modal').focus()
      });

       /*Sub comapny for no*/
       $(document).on('click', '.sub_companies_no', function() {
           var check_status = $('.sub_company_checkbox_temp').val();

           if(check_status == 'true'){
              $( "#sub-company").prop('checked', check_status);
           } else {
              $( "#sub-company").prop('checked', false);
              $('input[class=sub_company]').val(0);
           }
       });

       /*Sub comapny for cross*/
       $(document).on('click', '.sub_companies_cross', function() {
           var check_status = $('.sub_company_checkbox_temp').val();

           if(check_status == 'true'){
              $( "#sub-company").prop('checked', check_status);
           } else {
              $( "#sub-company").prop('checked', false);
              $('input[class=sub_company]').val(0);
           }
       });

       /*Sub comapny for yes*/
      $(document).on('click', '.sub_companies_yes', function() {
        $('.sub_company_checkbox_temp').val($('.sub_company_trigger').is(":checked"));
        $('input[class=sub_company]').val($('.sub_company_trigger').is(":checked"));
      });

      /*Delete Share on James Dashboard*/
    $(document).on('click', '.delete_share', function() {
      var share_name_value = $(this).closest('tr').children('td.share_name').text();
      var space_id = $(this).attr('spaceid');
      $('.delete-share-yes').attr('spaceid',space_id);
      $( ".add_share_name" ).text(share_name_value);
     });

      /*Confirm Delete Share*/
    $(document).on('click', '.delete-share-yes', function() {
       var space_id = $(this).attr('spaceid');
       var user_email_alert = $('.email_alert_prevent').val();
       $("#myModalDelete .close-inner").trigger('click');
                  $.ajax({
                      type: "POST",
                      headers: {
                          "cache-control": "no-cache",
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      url: baseurl+'/delete_space',
                      data: {sapce_id:space_id,user_email_alert:user_email_alert},
                      dataType:"text",
                      async: false,
                      success: function(response) {
                         location.reload(true);
                         //return false;
                     }
                  });
    });
  </script>

  <script>
    $(document).ready(function(){
        var update_version = $('.update-version');
        update_version.on('click',function(e){
          e.preventDefault();
          $this = $(this)
          data = {
            'space_id' : $this.attr('data-space-id'),
            'current_version' : $this.attr('data-version')
          }
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': "{{csrf_token()}}"
            }
          });
          $.ajax({
            type: 'POST',
            dataType: 'json',
            async : false,
            data:data,
            url: baseurl + '/update-share-version',
            success: function (response) {
                console.log(response);
                if(response.status){
                  console.log('s',$this)
                  if($this.attr('data-version') == 0){
                    $this.attr('data-version',1);
                    $this.text('Revert platform version');
                    $('.version-name-'+$this.attr('data-space-id')).text('New')
                    return true;
                  }else if($this.attr('data-version') == 1){
                    $this.attr('data-version',0);
                    $this.text('update platform version');
                    $('.version-name-'+$this.attr('data-space-id')).text('Old')
                    return true;
                  }
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr, status, error);
            }
          });
        })
    })
  </script>
  
  <script rel="text/javascript" src="{{ url('js/custom/share_create.js?q='.env('CACHE_COUNTER', '500'),[],env('HTTPS_ENABLE', true)) }}"></script>

  <script rel="text/javascript" src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
@endsection
