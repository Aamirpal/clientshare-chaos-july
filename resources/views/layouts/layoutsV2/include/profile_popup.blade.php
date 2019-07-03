<div class="modal fade"  @if($check_user_is_new) data-backdrop="static" data-keyboard="false" @endif id="user_profile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="custom-loader" style="display:none"><span></span></div>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Your profile</h5>
                @if(!$check_user_is_new)
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
                </button>
                @endif
            </div>
            <div class="modal-body">
                <form id="update_profile_form"  method="post" action="{{ url('/update-share-user',[],env('HTTPS_ENABLE', true)) }} " enctype="multipart/form-data" class="profile_update_form">
                    {{csrf_field()}}
                    <div class="profile-edit-wrap">
                        <div class="profile-image-wrap">
                            <div class="popup-profile-picture"><span style="background: url('{{$profile_image}}') no-repeat #748AA1 center center / cover;" alt="Profile_pic_empty"></span></div>
                            <input type='file' name='file' class="file-input" onchange="uploadProfilePicture(this);" id="show_profile_pic" />
                            <input name="linkedin_image" value="@if(isset($account_data->linkedin)){{@$account_data->linkedin->user->pictureUrls->values[0]}} @endif" type="hidden">
                            <div class="uploaded-picture-div">
                                <span class="uploaded-profile-pic" id="show_changed_profile_pic"></span>
                            </div>
                            <div class="upload-icon-div">
                                <span class="upload-pic-icon">
                                    <img src="{{asset('images/v2-images/camera.svg')}}" alt="Camera" class="camera-icon" />
                                </span>
                            </div>
                        </div>
                        <div class="profile-name-wrap">
                            <h2 class="user-name">
                                <span id="user_full_name"></span>
                                <span class="edit-name-icon"><img src="{{asset('images/v2-images/edit-icon.svg')}}" alt="Camera" class="edit-icon" /></span>
                            </h2>
                            <input type="hidden" id="first_name_prev">
                            <input type="hidden" id="last_name_prev">
                            <div class="edit-user-name" style="display: none;">
                                <div class="form-group edit-first-name">
                                    <input type="text" class="form-control" placeholder="First Name" name="user[first_name]" id="user_first_name" autocomplete="off">
                                    <span class="first-name-error error-msg"></span>
                                    @if ($errors->has('user.first_name'))
                                    <span class="error-msg text-left">
                                        {{ $errors->first('user.first_name') }}
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group edit-last-name">
                                    <input type="text" class="form-control" placeholder="Last Name" name="user[last_name]" id="user_last_name" autocomplete="off">
                                    <span class="last-name-error error-msg"></span>
                                    @if ($errors->has('user.last_name'))
                                    <span class="error-msg text-left">
                                        {{ $errors->first('user.last_name') }}
                                    </span>
                                    @endif
                                </div>
                                <span class="cancel-user-name"><img src="{{asset('images/v2-images/close-icon.svg')}}" alt="close"></span>
                            </div>
                            <div class="form-group">
                                <label>Your job title (required)</label>
                                <input id="user_job_title" name="job_title" type="text" value="" class="form-control" placeholder="Product Manager" autocomplete="off">
                                <span class="job-title-error error-msg"></span>
                            </div>

                            <div class="company-subcompany-wrap">
                                <div class="form-group company-div">
                                    <label>@if($session_data['sub_companies'] == '1') Community (required) @else Company (required) @endif</label>
                                    @if($check_user_is_new)
                                    <input type="hidden" value="{{$buyer_info['company_name']}}" buyer-id="{{$buyer_info['id']}}"
                                           sub-comp-active="{{$session_data['sub_companies']}}" class="buyer_info_hidden">
                                    <div class="company-dropdown">
                                        <select id='company_admin' class="form-control">
                                            <option value="" >Choose company name</option>
                                            @foreach($buyer_seller as $buyer_seller_name)
                                            <option value="{{$buyer_seller_name['id']}}"  @if (old('company') == $buyer_seller_name['id']) selected="selected" @endif>{{$buyer_seller_name['company_name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input id="user_company" name="company_name" type="hidden" class="form-control" placeholder="Sefas" autocomplete="off">
                                    @else
                                    <input id="user_company" name="company_name" type="text" readonly="readonly" class="form-control" placeholder="Sefas" autocomplete="off">
                                    @endif
                                    <input id="user_company_id" name="company" type="hidden" class="form-control" placeholder="Sefas">
                                    <input id="sub_companies"  type="hidden" value="{{$session_data['sub_companies']}}">
                                    <input id="check_user_is_new" name="check_user_is_new"  type="hidden" value="{{$check_user_is_new}}">
                                    <span class="company-error error-msg"></span>
                                </div>
                                <div class="form-group sub-company-div hide">
                                    <label>Company</label>
                                    <input id="user_sub_company" type="text" class="form-control sub-company-input" placeholder="Sefas" autocomplete="off">
                                    <div id="sub_comapany_suggesstion"></div>
                                    <span class="sub-company-error error-msg"></span>
                                </div>
                            </div>
                        </div>
                        <div class='linkedin-hidden-fields'>
                            <input type="hidden" id="linkedin_info" value="{{ (Auth::User()->social_accounts) ?? null}}">
                            <input type="hidden" id="check_buyer_eller" value="{{ checkBuyerSeller(Session::get('space_info')['id'], Auth::User()->id) }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="row">
                            <div class="form-group textarea-group">
                                <label>Bio</label>
                                <textarea id="user_bio" name="bio" type="text" class="form-control" placeholder="Product Manager @ Sefas" onkeyup="charCount(this)" onfocus="textAreaAdjust(this)" maxlength="300"></textarea>
                                <div class="bio-letter-count">
                                    <span id="total_char"></span>/300
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Linkedin profile</label>
                                <input id="user_linkedin" type="text"  name="user[contact][linkedin_url]" class="form-control" placeholder="https://www.linkedin.com/in/clariemacdonald" autocomplete="off">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6 no-pad">
                                    <label>Email</label>
                                    <input  id="user_email" type="email" class="form-control" readonly="readonly" placeholder="clarie@sefas.com" autocomplete="off">
                                </div>
                                <div class="form-group col-md-6 no-pad">
                                    <label>Phone number</label>
                                    <input id="user_phone_number" name="user[contact][contact_number]"  type="text" class="form-control phone-number-validate" maxlength="15" placeholder="44 876543321" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn-group profile-popup-btn-group profile-btn-right hidden-mbl ">
                        <a href="javascript:void(0);" class="linkdin-profile-btn tourlink_yes_linkedin hide" style="display:none"span><img src="{{asset('images/v2-images/linkedin_icon.svg')}}" alt="Linked In" class="camera-icon" /></span>Fill profile with Linkedin</a>
                        <div class="cancel-update-btn">
                            @if(!$check_user_is_new)
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            @endif
                            <button type="submit" class="btn btn-primary update-user-profile">Update</button>
                        </div>
                    </div>
                    <div class="btn-group profile-popup-btn-group profile-btn-bottom hidden-desktop">
                        <div class="mobile-update-btn">
                            <button type="submit" class="btn btn-primary update-user-profile">Done</button>
                        </div>
                    </div>
                    <input id="user_space_id" name="space_id" type="hidden" class="form-control">
                </form>
            </div>
        </div>
    </div>
</div>
