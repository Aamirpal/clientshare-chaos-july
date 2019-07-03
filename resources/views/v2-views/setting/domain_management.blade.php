<div class="heading-wrap">
    <h2 class="title">Domain management</h2>
    <button class="btn btn-secondary add_domain_row" type="button">
    <span><img src="{{asset('images/v2-images/add_small_icon.svg')}}" alt="Add Domain" /></span>  
    Add email domain</button>
</div>
<div class="heading-wrap-mobile">
    <h2 class="title">Domain management</h2>
</div>
<div class="tab-inner-content domain-management-inner-content">
    <span class="info-text"><img src="{{asset('images/v2-images/info-icon.svg')}}" alt="Email Invitation Information" /> Email invitation’s cannot be sent to email addresses outside the approved domain(s).</span>
    <span style="display:none" class="success-msg white_box_info">Restricted email access to domains: </span>
    <form id="domain_management_form"  class="domain_management_form set_email_rule" action="/domain_update" method="post" autocomplete="off">
        <div class="form_field_section">
        <div class="input-field-wrap">
        <div class="input-group">
            <span class="approved-small-text">Approved email domains</span>
        </div>
        @if( isset($domain_management['metadata']['rule']))
        @foreach($domain_management['metadata']['rule'] as $rule)
        <div class="input-group domain-input-grp" data-id="{{base64_encode($rule['value'])}}">
          <div class="input-inner-wrap">
            <span class="input-group-addon" id="basic-addon1">@</span>
            <input type="text" class="form-control domain_name_inp" placeholder="IBM.com" name="rule" autocomplete="off" value="{{ $rule['value'] }}" disabled spellcheck="false">
            <div class="dropdown show more-options-dropdown">
                <a href="#" class="dropdown-toggle" data-domain="{{$rule['value']}}" data-toggle="dropdown" role="button" id="dropdownMenuLink" aria-haspopup="true" aria-expanded="false">
                more options
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                <li class="domain_inp_edit"><a href="#!"><img src="{{asset('images/v2-images/edit-icon.svg')}}" alt="Edit Domain" /> Edit domain</a></li>
                <li class="domain_delete_inp"><a href="#" class="delete-link"><img src="{{asset('images/v2-images/delete-icon-red.svg')}}" alt="Delete Domain" /> Delete domain</a></li>
                </ul>
                <div class="btn-group dropdown-save-btn domain-action-btn"  style="display:none">
                    <button type="button" class="btn btn-secondary cancel-domain-editing" >Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="set_email_rule_in_setting('domain_management_form');">Save</button>
                </div>

            </div>
        </div>
        </div>
        @endforeach
        @endif
        </div>
        <div class="link-wrap input-group">
            <a class="link add_domain_row" href="#!"><img src="{{asset('images/v2-images/add_small_icon.svg')}}" alt="Add Domain" /> Add email domain</a>
        </div>
    </div>
    <input type="hidden" class="spaceid" value="{{Session::get('space_info')['id']}}" userid="{{Auth::user()->id}}">
    </form>
</div>
