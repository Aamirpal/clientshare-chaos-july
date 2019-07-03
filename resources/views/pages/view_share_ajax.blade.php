<?php //echo '<pre>'; print_r($share_user[0]);?>
   <div class="modal-dialog modal-md" role="document">
   <form action="updateshare" method="post" enctype="multipart/form-data" class="edit_share_form">
    {{ csrf_field() }}
    <input type="hidden" name="company_seller_id_hidden" value="{{ $share_user[0]['company_seller_id'] }}">
    <input type="hidden" name="company_buyer_id_hidden" value="{{ $share_user[0]['company_buyer_id'] }}">
    <input style="display: none;" name="space_id" value="{{ $share_user[0]['id'] }}"> 
    <input style="display: none;" name="user_id" value="{{ $share_user[0]['user_id'] }}"> 
    <input style="display: none;" name="comp_id" value="{{ $share_user[0]['company_id'] }}"> 
     <div class="form-submit-loader" style="display:none">
         <span></span>
      </div>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/') }}/images/ic_highlight_removegray.svg"/></button>
            <h4 class="modal-title" id="myModalLabel">Edit Share</h4>
         </div>
          <span class="left new_confirm_error"></span>
         <div class="modal-body">
             <div class="edit-share">
                  <div class="form-group">
                     <label for="seller-name">Seller Name</label>
                     <input class="form-control" id="seller-name" type="" name="seller_name" style="" value="{{ $share_user[0]['seller_name']['company_name'] }}">
                  </div>
                  <div class="form-group">
                     <label for="seller-logo">Seller Logo</label>
                     <img src="{{ $share_user[0]['company_seller_logo'] }}" id="s_logo" style="width: 40px; height: 40px;"> 
                     <img id="blah_edit1" alt="" width="40px" height="40px"  style="display:none;" />

                <input id="seller-logo" type="file" style="display: none;" name="seller_logo">

                <input type="hidden" name="seller_logo_url" id="hidden_seller_logo" value="{{ $share_user[0]['company_seller_logo'] }}">

                <input type="hidden" name="seller_logo_status" id="check_seller_logo_status" value=""> 

 

                   <label for="seller-logo"><i class="fa fa-pencil"></i></label>
                  </div>
                  <div class="form-group">
                     <label for="buyer-name">Buyer Name</label>
                     <input class="form-control" id="buyer-name" type="" name="buyer_name" style="" value="{{ $share_user[0]['buyer_name']['company_name'] }}">
                  </div>
                  <div class="form-group">
                     <label for="buyer-logo">Buyer Logo</label>
                     <img src="{{ $share_user[0]['company_buyer_logo'] }}" id="b_logo" style="width: 40px; height: 40px;"> <img id="blah_edit2" alt="" width="40px" height="40px" style="display:none;"/>

                   <input id="buyer-logo" type="file" style="display: none;" name="buyer_logo" ><input style="display: none;" name="buyer_logo_url" value="{{ $share_user[0]['company_buyer_logo'] }}">
                   <input type="hidden" name="buyer_logo_status" id="check_buyer_logo_status" value="">
                   <label for="buyer-logo"><i class="fa fa-pencil"></i></label>
                  </div>
                  <div class="form-group">
                     <label for="clientshare-name">Client Share Name</label>
                     <input class="form-control" id="clientshare-name" type="" name="client_share_name" value="{{ $share_user[0]['share_name'] }}">
                  </div>
                  <div class="form-group">
                     <label for="admin-email">Admin Email</label>
                     <input readonly="readonly" class="form-control" id="admin-email" type="" name="admin_email" style="" value="{{ $share_user[0]['admin_user']['email'] }}">
                  </div>
                  <div class="form-group">
                     <label for="admin-first-name">Admin First Name</label>
                     <input readonly="readonly" class="form-control" id="admin-first-name" type="" name="admin_first_name" style="" value="{{ ucfirst($share_user[0]['admin_user']['first_name']) }}">
                  </div>
                  <div class="form-group">
                     <label for="admin-last-name">Admin Last Name</label>
                     <input readonly="readonly" class="form-control" id="admin-last-name" type="" name="admin_last_name" style="" value="{{ ucfirst($share_user[0]['admin_user']['last_name']) }}">
                  </div>
                  <div class="form-group">
                     <label for="contract-value">Contract Value</label>
                     <input class="form-control" id="contract-value" type="text" name="contract_value" value="{{ $share_user[0]['contract_value'] }}">
                  </div>
                  <div class="form-group">
                     <label for="contract-end-date">Contract End Date</label>
                     <input class="form-control" id="contract-end-date" type="text" name="contract_date" value="@if($share_user[0]['contract_end_date']) {{ date('m/y',strtotime($share_user[0]['contract_end_date'])) }} @endif" readonly="readonly">
                     <input class="form-control" id="contract-end-date-hidden" type="hidden" name="contract_end_date" value="{{ $share_user[0]['contract_end_date'] }}">
                  </div>
                  <div class="form-group">
                     <label for="status">Status</label>
                     <select class="form-control" id="status-value" name="status" title="Select Status">
                        <option value="">Select Status</option>
                        @foreach(config('constants.MODEL.management_information.STATUS_FILTER_LABEL') as $label)
                          <option value="{{$label}}" @if($share_user[0]['status'] == $label) selected="selected" @endif>{{$label}}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="form-group">
                     <label for="domain-restriction">Restricted Domains</label>
                     <input id="checkbox1" type="checkbox" name="space[domain_restriction]" value="1" {{ $share_user[0]['domain_restriction']?'checked':''}}>
                  </div>


                  <div class="form-group">
                     <label for="domain-restriction">Restricted IP</label>
                     <input class="ip-restriction-toggle" type="checkbox" name="space[ip_restriction]" value="1" {{ $share_user[0]['ip_restriction']?'checked':''}}>
                      <div class="ip-address-main-block">

                      @if($share_user[0]['ip_restriction'])
                        @if(sizeOfCustom($share_user[0]['allowed_ip']))
                          @foreach($share_user[0]['allowed_ip'] as $ip)
                            <div class="ip-address-block">
                              <input class="form-control" type="text" name="space[allowed_ip][]" name="" maxlength="15" required="true" value="{{$ip}}">
                              <div class="ip-box-manage">
                                <i class="fa fa-minus remove-ip-address"></i>
                                <i class="fa fa-plus add-ip-address"></i>
                              </div>
                            </div>
                          @endforeach
                        @else
                          @php
                            $show_ip_block = true;
                          @endphp
                        @endif
                      @endif
                      
                      <div class="ip-address-block" style="display: @php echo isset($show_ip_block) ? 'block':'none'; @endphp">
                        <input class="form-control" type="text" name="space[allowed_ip][]" name="" maxlength="15" required="true">
                        <div class="ip-box-manage">
                          <i class="fa fa-minus remove-ip-address"></i>
                          <i class="fa fa-plus add-ip-address"></i>
                        </div>
                      </div>
                      

                      </div>
                      
                  </div>
                   
             </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">CANCEL</button>
            <button type="button" class="btn btn-default modal_initiate_btn" id="edit_save">Save</button>
         </div>
      </div>
      </form>
   </div>
