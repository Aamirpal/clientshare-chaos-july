<div class="heading_wrap">
      <h4 class="title">User management</h4>
      <button class="invite-btn" type="button" data-toggle="modal" data-target="#myModalInvite">INVITE COLLEAGUES</button>
   </div>
   <div class="tab-inner-content">
      <div class="form_field_section">
         <div class="tablerow tablehdrow">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell"><span class="approved-small-text">Members</span></div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell"><span class="approved-small-text">Email address</span></div>
            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell"><span class="approved-small-text">Company</span></div>
            <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 tablecell"><span class="approved-small-text"></span></div>
         </div>
         @foreach( $space_users as $key => $space_user )
         
         @if($space_user['user']['id'] != Auth::user()->id)
         <div class="tablerow user{{$space_user['user']['id']}}">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell name_cell"><span class="approved-small-text">Members</span>
               @if(!empty(composeUrl($space_user['user']['profile_image'])))
               <span style="background-image: url('{{ wrapUrl(composeUrl($space_user['user']['profile_image'])) }} ');" class="dp pro_pic_wrap"></span>
               @endif
               @if(empty($space_user['user']['profile_image_url']))
               <span style="background-image: url(' {{  url('/images/dummy-avatar-img.svg',[],env('HTTPS_ENABLE', true)) }}');" class="dp pro_pic_wrap"></span>
               @endif
               <span class="mem_name user_nme{{$space_user['user']['id']}}">{{ ucfirst($space_user['user']['first_name'])}} {{ ucfirst($space_user['user']['last_name'])}}  @if($space_user['user_role_id'] == Config::get('constants.USER_ROLE_ID')) (admin)  @endif</span>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4 tablecell user-mgm-email">
               <span class="approved-small-text">Email address</span>
               <span>{{ $space_user['user']['email']}}</span>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 tablecell">
               <span class="approved-small-text">Company</span>
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
                     <option value="{{ $spacInfoValueSetting->toArray()['seller_name']['id'] }}" @if($space_user['metadata']['user_profile']['company'] == $spacInfoValueSetting->toArray()['seller_name']['id']) style="display:none;" selected="selected" disabled="disabled" @endif >{{ $spacInfoValueSetting->toArray()['seller_name']['company_name'] }} </option>
                     <option value="{{ $spacInfoValueSetting->toArray()['buyer_name']['id'] }}" @if($space_user['metadata']['user_profile']['company'] == $spacInfoValueSetting->toArray()['buyer_name']['id']) style="display:none;" selected="selected" disabled="disabled" @endif>{{ $spacInfoValueSetting->toArray()['buyer_name']['company_name'] }}</option>
                     </select>
                  </div>
                  @endif
               </span>
            </div>
            <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1 tablecell">
               <div class="dropdown hover-dropdown">
                  <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">
                  <span></span>
                  </a>
                  <ul class="dropdown-menu @if($space_user['user_role_id'] == Config::get('constants.USER_ROLE_ID')) del_comment  @endif">
                     <li class="cancel_invi_trigger"><a href="javascript:void(0)" class="edit_compapny" userid="{{ $space_user['user']['id']}}" data-toggle="modal-" data-target="#editcompany" onclick="func_edit_company(this)">Edit company</a></li>
                     <!--JS changes 10-01-2017 end-->
                     @if($space_user['user_role_id'] != Config::get('constants.USER_ROLE_ID'))
                     <li class="resend_trigger"><a href="#" class="promote_user" userid="{{ $space_user['user']['id']}}" spaceid="{{Session::get('space_info')['id']}}" data-toggle="modal" data-target="#promoteuser">Promote to admin</a></li>
                     @endif
                     <?php //} ?>
                     <li class="cancel_invi_trigger"><a href="#" class="delete-link remove_user" userid="{{ $space_user['user']['id']}}" data-toggle="modal" data-target="#removeuserpopup">Remove user</a></li>
                  </ul>
               </div>
            </div>
         </div>
         @endif
         @endforeach
         <!--JS changes 10-01-2017 start-->
         <div class="modal fade" id="editcompany" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
            <div class="modal-dialog modal-sm" role="document">
               <div class="modal-content">
                  <div class="modal-header">
                     <h4 class="modal-title modal_title" id="myModalLabel">Change Company</h4>
                  </div>
                  <div class="modal-body">
                     <p>
                        <span class="modal_text"></span>
                        <input type="hidden" name="hidden_company_id" class="hidden_company_id" value="">
                        <input type="hidden" name="hidden_user_id" class="hidden_user_id" value="">
                     </p>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default left cancel-edit-box" data-dismiss="modal">Cancel</button>
                     <a href="javascript:void(0)" id="delete_user--" inactive="" class="btn btn-primary modal_initiate_btn" onclick="submit_company_form(this)" >Confirm</a>
                  </div>
               </div>
               <!-- </form>  -->
            </div>
            <!--JS changes 10-01-2017 start-->
         </div>
         <div class="modal fade" id="removeuserpopup" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
            <div class="modal-dialog modal-sm" role="document">
               <div class="modal-content">
                  <div class="modal-header">
                     <h4 class="modal-title" id="myModalLabel">Remove <span class="u_name"></span>?</h4>
                  </div>
                  <div class="modal-body">
                     <p>Are you sure you want to remove <span class="u_name"></span> from this Client Share?</p>
                     <p>Posts by <span class="u_name"></span> will remain in this Client Share, but <span class="u_name"></span> will no longer have access to this Client Share.</p>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default left" data-dismiss="modal">Cancel</button>
                     <a href="" id="delete_user" inactive="" class="btn btn-primary modal_initiate_btn " >Remove User</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal fade" id="promoteuser" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
            <div class="modal-dialog modal-sm" role="document">
               <div class="modal-content promot-user">
                  <div class="modal-header">
                     <h4 class="modal-title" id="myModalLabel">Promote to administrator</h4>
                  </div>
                  <div class="modal-body">
                     <p>Are you sure you want to give administrator permission to <span class="u_name"></span></p>
                  </div>
                  <div class="modal-footer">
                     <a href="" id="promote_user" inactive="" class="btn btn-primary modal_initiate_btn " >Promote to Administrator</a>
                     <button type="button" class="btn btn-default left" data-dismiss="modal">Cancel</button>
                  </div>
               </div>
            </div>
         </div>
         <input type="hidden" class="spaceid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
         <div class="tablerow lastrow">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 tablecell">
               <a href="#" data-toggle="modal" class="modelinvite" data-target="#myModalInvite">Invite colleagues</a>
            </div>
         </div>
      </div>
      <!-- Share users pagination start  -->
      <div class="pagination-wrap">
      {{  $space_users->links() }}
      </div>
      <!-- Share users pagination end  -->
   </div>
@php
   $space_user = $space_user_data;
@endphp
@include('layouts.invite_colleague', ['data' => $space_data])