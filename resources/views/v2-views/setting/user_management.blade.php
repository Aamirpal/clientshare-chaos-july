<div class="heading-wrap">
      <h2 class="title">User management</h2>
      <button class="btn btn-secondary invite-btn" type="button" data-toggle="modal" data-target="#myModalInvite"><span><img src="{{asset('images/v2-images/add_small_icon.svg')}}" alt="Add Domain" /></span>Invite Colleagues</button>
   </div>
   <div class="heading-wrap-mobile">
    <h2 class="title">User management</h2>
</div>
   <div class="tab-inner-content user-management-inner-content">
      <div class="form_field_section">
      <div class="user-listing-wrap">
         <div class="tablerow tablehdrow">
            <div class="tablecell member-wrap"><span class="approved-small-text">Members</span></div>
            <div class="tablecell email-wrap"><span class="approved-small-text">Email address</span></div>
            <div class="tablecell company-wrap"><span class="approved-small-text">Company</span></div>
            <div class="tablecell more-options-cell"><span class="approved-small-text"></span></div>
         </div>
         @foreach( $space_users as $key => $space_user )
         
         @if($space_user['user']['id'] != Auth::user()->id)
         <div class="tablerow user{{$space_user['user']['id']}} user-data-row">
         <div class="user-inner-row">

            <div class="member-pic-mobile">
               @if(!empty(composeUrl($space_user['user']['profile_image'])))
               <span style="background-image: url('{{ wrapUrl(composeUrl($space_user['user']['profile_image'])) }} ');" class="dp pro_pic_wrap"></span>
               @endif
               @if(empty($space_user['user']['profile_image_url']))
               <span style="background-image: url(' {{ url('/images/v2-images/user-placeholder.svg',[],env('HTTPS_ENABLE', true)) }}');" class="dp pro_pic_wrap"></span>
               @endif
               @if($space_user['user_role_id'] == Config::get('constants.USER_ROLE_ID')) <span class="admin-text-mobile">A</span> @endif
            </div>
            <div class="member-info-mobile">
            <div class="tablecell name_cell member-wrap">
            <div class="member-info">
               @if(!empty(composeUrl($space_user['user']['profile_image'])))
               <span style="background-image: url('{{ wrapUrl(composeUrl($space_user['user']['profile_image'])) }} ');" class="dp pro_pic_wrap"></span>
               @endif
               @if(empty($space_user['user']['profile_image_url']))
               <span style="background-image: url(' {{ url('/images/v2-images/user-placeholder.svg',[],env('HTTPS_ENABLE', true)) }}');" class="dp pro_pic_wrap"></span>
               @endif
               <span class="mem_name user_nme{{$space_user['user']['id']}}">{{ ucfirst($space_user['user']['first_name'])}} {{ ucfirst($space_user['user']['last_name'])}}  @if($space_user['user_role_id'] == Config::get('constants.USER_ROLE_ID')) <span class="admin-text"> (admin)   </span> @endif</span>
            </div>
            </div>
            <div class="tablecell user-mgm-email email-wrap">
               <span>{{ $space_user['user']['email']}}</span>
            </div>
            <div class="tablecell company-wrap">
               <div class="company-edit">
               <span class="companyName">
               @php $spacInfoValueSetting = Session::get('space_info'); @endphp
               @if(isset($spacInfoValueSetting) && isset($space_user['metadata']['user_profile']))
               @if($space_user['metadata']['user_profile']['company'] == $spacInfoValueSetting->toArray()['seller_name']['id'])
               {{ $spacInfoValueSetting->toArray()['seller_name']['company_name'] }}
               @else    
               @if(!empty($space_user['sub_comp']) && isset($space_user['sub_comp']['company_name']))
               {{$space_user['sub_comp']['company_name']}}
               @else
               @if($space_user['metadata']['user_profile']['company'] == $spacInfoValueSetting->toArray()['buyer_name']['id'])
               {{ $spacInfoValueSetting->toArray()['buyer_name']['company_name'] }}
               @endif  
               @endif                        
               @endif
               @endif
               </span>
               <span class="companyNameEdit" style="display:none;">
                  @php $spacInfoValueSetting = Session::get('space_info'); @endphp
                  @if(isset($spacInfoValueSetting) && isset($space_user['metadata']['user_profile']))
                  <div class="user-management-select">
                     <select  company-id="{{$space_user['metadata']['user_profile']['company']}}" user-id="{{$space_user['user']['id']}}" onchange="change_company(this)" class="select_company_n selectpicker company_admin form-control">
                     <option value="{{ $spacInfoValueSetting->toArray()['seller_name']['id'] }}" @if($space_user['metadata']['user_profile']['company'] == $spacInfoValueSetting->toArray()['seller_name']['id'])  selected="selected"  @endif >{{ $spacInfoValueSetting->toArray()['seller_name']['company_name'] }} </option>
                     <option value="{{ $spacInfoValueSetting->toArray()['buyer_name']['id'] }}" @if($space_user['metadata']['user_profile']['company'] == $spacInfoValueSetting->toArray()['buyer_name']['id'])  selected="selected"  @endif>{{ $spacInfoValueSetting->toArray()['buyer_name']['company_name'] }}</option>
                     </select>
                  </div>
                  @endif
               </span>
               </div>
            </div>


            <div class="tablecell more-options-cell">
               <div class="more-options-wrap">
               <div class="dropdown show more-options-dropdown">
                  <a href="#" class="dropdown-toggle dots" data-toggle="dropdown" role="button" id="dropdownMenuLink" aria-haspopup="true" aria-expanded="true">
                  <span class="more-options-text">more options</span>
                  </a>
                  <ul class="dropdown-menu @if($space_user['user_role_id'] == Config::get('constants.USER_ROLE_ID')) del_comment  @endif" aria-labelledby="dropdownMenuLink">
                     <li class="cancel_invi_trigger edit-company"><a href="javascript:void(0)" class="edit_compapny" userid="{{ $space_user['user']['id']}}" data-toggle="modal-" data-target="#editcompany" onclick="func_edit_company(this)"><img src="{{asset('images/v2-images/edit-icon.svg')}}" alt="Edit company" /> Edit company</a></li>
                     <!--JS changes 10-01-2017 end-->
                     @if($space_user['user_role_id'] != Config::get('constants.USER_ROLE_ID'))
                     <li class="resend_trigger promote-user"><a href="#" class="promote_user" userid="{{ $space_user['user']['id']}}" spaceid="{{Session::get('space_info')['id']}}" data-toggle="modal" data-target="#promoteuser"><img src="{{asset('images/v2-images/promote-icon.svg')}}" alt="Promote to admin" /> Promote to admin</a></li>
                     @endif
                     <?php //} ?>
                     <li class="cancel_invi_trigger remove-user"><a href="#" class="remove_user" userid="{{ $space_user['user']['id']}}" data-toggle="modal" data-target="#removeuserpopup"><img src="{{asset('images/v2-images/delete-icon-red.svg')}}" alt="Delete User" /> Remove user</a></li>
                  </ul>
               </div>
            </div>
               <div class="btn-group save-user-company" style="display:none">
                  <button class="btn btn-primary" onClick="update_user_company();">Save</button>
               </div>
               </div>

            </div>


            <div class="more-options-mobile ">
               <div class="more-options-wrap">
               <div class="dropdown show more-options-dropdown">
                  <a href="#" class="dropdown-toggle dots" data-toggle="dropdown" role="button" id="dropdownMenuLink" aria-haspopup="true" aria-expanded="true">
                  <span class="more-options-text">more options</span>
                  </a>
                  <ul class="dropdown-menu @if($space_user['user_role_id'] == Config::get('constants.USER_ROLE_ID')) del_comment  @endif" aria-labelledby="dropdownMenuLink">
                     <li class="cancel_invi_trigger edit-company"><a href="javascript:void(0)" class="edit_compapny" userid="{{ $space_user['user']['id']}}" data-toggle="modal-" data-target="#editcompany" onclick="func_edit_company(this)"><img src="{{asset('images/v2-images/edit-icon.svg')}}" alt="Edit company" /> Edit company</a></li>
                     <!--JS changes 10-01-2017 end-->
                     @if($space_user['user_role_id'] != Config::get('constants.USER_ROLE_ID'))
                     <li class="resend_trigger promote-user"><a href="#" class="promote_user" userid="{{ $space_user['user']['id']}}" spaceid="{{Session::get('space_info')['id']}}" data-toggle="modal" data-target="#promoteuser"><img src="{{asset('images/v2-images/promote-icon.svg')}}" alt="Promote to admin" /> Promote to admin</a></li>
                     @endif
                     <?php //} ?>
                     <li class="cancel_invi_trigger remove-user"><a href="#" class="remove_user" userid="{{ $space_user['user']['id']}}" data-toggle="modal" data-target="#removeuserpopup"><img src="{{asset('images/v2-images/delete-icon-red.svg')}}" alt="Delete User" /> Remove user</a></li>
                  </ul>
               </div>
            </div>
               <div class="btn-group save-user-company" style="display:none">
                  <button class="btn btn-primary" onClick="update_user_company();">Save</button>
               </div>
               </div>


            </div>
            
         </div>
         @endif
         @endforeach
         <!--JS changes 10-01-2017 start-->
         <div class="modal fade sm-popup edit-company-popup" id="editcompany" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
               <div class="modal-content">
                  <div class="modal-header">
                     <h4 class="modal-title modal_title" id="myModalLabel">Change Company</h4>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
                     </button>
                  </div>
                  <div class="modal-body">
                     <p>
                        <span class="modal_text"></span>
                        <input type="hidden" name="hidden_company_id" class="hidden_company_id" value="">
                        <input type="hidden" name="hidden_user_id" class="hidden_user_id" value="">
                     </p>
                  <div class="btn-group">
                     <button type="button" class="btn btn-secondary left cancel-edit-box" data-dismiss="modal">Cancel</button>
                     <a href="javascript:void(0)" id="delete_user--" inactive="" class="btn btn-primary modal_initiate_btn" onclick="submit_company_form(this)" >Confirm</a>
                  </div>
                  </div>
               </div>
               <!-- </form>  -->
            </div>
            <!--JS changes 10-01-2017 start-->
         </div>
         <div class="modal fade sm-popup delete-user-popup" id="removeuserpopup" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
               <div class="modal-content">
                  <div class="modal-header">
                     <h5 class="modal-title" id="myModalLabel">Remove user</h5>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
                     </button>
                  </div>
                  <div class="modal-body">
                     <p>Are you sure you want to remove this user?</p>
                     <div class="btn-group delete-domain-btn-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <a href="" id="delete_user" inactive="" class="btn btn-primary modal_initiate_btn " ><img src="{{asset('images/v2-images/delete-icon-white.svg')}}" alt="Delete Domain" /> Remove</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal fade sm-popup promote-admin-popup" id="promoteuser" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
               <div class="modal-content promot-user">
                  <div class="modal-header">
                     <h4 class="modal-title" id="myModalLabel">Promote to administrator</h4>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
                     </button>
                  </div>
                  <div class="modal-body">
                     <p>Are you sure you want to give administrator permission to <span class="u_name"></span></p>
                     <div class="btn-group delete-domain-btn-group">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <a href="" id="promote_user" inactive="" class="btn btn-primary modal_initiate_btn " >Promote to Administrator</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         </div>
         <div class="invite-button-wrap">
         <input type="hidden" class="spaceid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
         <div class="tablerow lastrow">
               <a href="#" data-toggle="modal" class="modelinvite" data-target="#myModalInvite"><img src="{{asset('images/v2-images/add_small_icon.svg')}}" alt="Invite colleagues" />Invite colleagues</a>
         </div>
         </div>
      </div>
      <!-- Share users pagination start  -->
      <div class="pagination-wrap">
      {{  $space_users->links() }}
      <input type="hidden" class="user-management-current-page" value="{{$space_users->currentPage()}}">  
      </div>
      <!-- Share users pagination end  -->
   </div>
@php
   $space_user = $space_user_data;
@endphp
@include('v2-views/setting/invite_colleague', ['data' => $space_data])