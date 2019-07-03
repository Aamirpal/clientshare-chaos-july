@extends('layouts.super_admin_fullwidth')
@section('content')
<div class="mi-overlay-div hidden">
    <img src="{{url('images/loading_bar1.gif')}}" alt="loader" />
    <p>Loading... Please wait</p>
</div>
<link rel="stylesheet" type="text/css" href="{{ url('/css/bootstrap-multiselect_v0_9_15.css') }}">

<div class="management-information-head">
    <div class="management-calendar-col">
        <div class="management-date hidden btn btn-primary form-control"></div>
        <input type="hidden" name="date_value" class="management-date-hidden" value="" />   
    </div>
    <div class="head-multiselect-dropdown head-column-dropdown supplier-dropdown hidden">
        <select class="mi-columns-multiselect" name="column[]" multiple="multiple">
            <option value="o-cont-value-th">Contract Value</option>
            <option value="o-cont-date-th">Contract End Date</option>
            <option value="b-comm-th">Buyer Community</option>
            <option value="s-comm-th">Supplier Community</option>
            <option value="o-comm-th">Overall Community</option>
            <option value="b-csi-th">Buyer CSI Score</option>
            <option value="s-csi-th">Supplier CSI Score</option>
            <option value="o-csi-th">Overall CSI Average</option>
            <option value="b-posts-th">Buyer Posts</option>
            <option value="s-posts-th">Supplier Posts</option>
            <option value="o-posts-th">Overall Posts</option>
            <option value="b-pi-th">Buyer PI</option>
            <option value="s-pi-th">Supplier PI</option>
            <option value="o-pi-th">Overall PI</option>
            <option value="o-nps-th">NPS Score Posts</option>
            <option value="o-pinv-th">Pending Invites</option>
            <option value="o-prog-th">Percentage Complete</option>
        </select>
    </div>

    <div class="management-download-col">
        <div class="dropdown show download-excel">
            <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-spinner fa-spin hidden"></i>
                Download</a>
        </div>
    </div>
</div>

<div class="management-information-table">
    <div class="table-responsive">
        <table class="table table-main table-header" cellpadding="0" cellspacing="0">
            <thead class="header">
                <tr>
                    <th class="supplier-load buyer-suppler-th not-visible" style="width:97px">Supplier</th>
                    <th class="head-multiselect-dropdown supplier-dropdown hidden buyer-suppler-th" style="width: 97px !important;min-width: 97px !important; max-width: 97px !important;">
                        <select class="mi-supplier-multiselect" name="users[]" multiple="multiple">
                            @foreach($spaces['sellers'] as $seller)
                            <option value="{{$seller['id']}}">{{$seller['company_name']}}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="supplier-load buyer-suppler-th not-visible" style="width:97px">Buyer</th>
                    <th class="head-multiselect-dropdown supplier-dropdown hidden buyer-suppler-th" style="width: 97px !important;min-width: 97px !important; max-width: 97px !important;">
                        <select class="mi-buyer-multiselect" name="users[]" multiple="multiple">
                            @foreach($spaces['buyers'] as $buyer)
                            <option value="{{$buyer['id']}}">{{$buyer['company_name']}}</option>
                            @endforeach
                        </select>
                    </th>


                    <th class="head-multiselect-dropdown supplier-dropdown hidden share-name-th" style="width: 97px !important;min-width: 97px !important; max-width: 97px !important;">
                        <select class="mi-share-multiselect" name="users[]" multiple="multiple">
                            @foreach($spaces_list as $space)
                            <option value="{{$space['id']}}">{{$space['share_name']}}</option>
                            @endforeach
                        </select>
                    </th>
                    

                    <th class="contract-value-th close-on-hover" data-uiclass="o-cont-value-th" style="width:100px">Contract Value £m
                        <span class="remove-column default_hide"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="contract-date-th close-on-hover" data-uiclass="o-cont-date-th" style="width:100px">Contract End Date
                        <span class="remove-column default_hide"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th> 
                    <th data-uiclass="o-status-th" class="space-status-th supplier-load not-visible" style="width: 70px">Status</th>
                    <th data-uiclass="o-status-th" class="close-on-hover space-status-th head-multiselect-dropdown rag-dropdown status-dropdown hidden" style="width: 70px !important;min-width: 70px !important;">
                        <select class="mi-status-multiselect" multiple="multiple" name="status_filter[]">
                            @foreach(config('constants.MODEL.management_information.STATUS_FILTER_LABEL') as $label)
                                <option value="{{$label}}" @if($label == config('constants.LABEL_LIVE') || $label == config('constants.LABEL_LIVE_NON_STANDARD')) selected="selected" @endif>{{$label}}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="community-th close-on-hover" data-uiclass="b-comm-th" style="width:105px">Buyer Community
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="community-th close-on-hover" data-uiclass="s-comm-th" style="width:105px">Supplier Community
                        <span class="remove-column close-on-hover"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="community-th close-on-hover" data-uiclass="o-comm-th" style="width:105px">Overall Community Size
                        <span class="remove-column default_hide"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="csi-th close-on-hover" data-uiclass="b-csi-th" style="width:105px">Buyer CSI Score
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="csi-th close-on-hover" data-uiclass="s-csi-th" style="width:105px">Supplier CSI Score
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="csi-th close-on-hover" data-uiclass="o-csi-th" style="width:105px">Overall CSI Average
                        <span class="remove-column default_hide"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="posts-th close-on-hover" data-uiclass="b-posts-th"  style="width:105px">Buyer Posts
                        <span class="remove-column default_hide"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="posts-th close-on-hover" data-uiclass="s-posts-th"  style="width:105px">Supplier Posts
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="posts-th close-on-hover" data-uiclass="o-posts-th"  style="width:105px">Overall Posts
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="pi-th close-on-hover" data-uiclass="b-pi-th" style="width:50px">Buyer PI
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="pi-th close-on-hover" style="width:50px" data-uiclass="s-pi-th">Supplier PI
                        <span class="remove-column default_hide"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="pi-th close-on-hover" style="width:50px" data-uiclass="o-pi-th">Overall PI
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="nps-th close-on-hover" data-uiclass="o-nps-th" style="width:50px;">NPS Score
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="pinv-th close-on-hover" data-uiclass="o-pinv-th" style="width:50px;">Pending Invites
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="prog-th close-on-hover" data-uiclass="o-prog-th" style="width:50px;">Percentage Complete
                        <span class="remove-column"><img src="{{url('images/ic_remove.png')}}" alt="Remove Column" /></span>
                    </th>
                    <th class="o-rag-th supplier-load rag-th not-visible" style="width: 33px">Rag</th>
                    <th class="o-rag-th rag-th head-multiselect-dropdown rag-dropdown comms-dropdown hidden" style="width: 100px !important;min-width: 100px !important;">
                        <select class="mi-rag-multiselect" multiple="multiple" name="RAG_filter[]">
                            <option class="green-status" value="Last 7 days">Last 7 days</option>
                            <option class="yellow-status" value="Last 8 to 13 days">Last 8 to 13 days</option>
                            <option class="red-status" value="Over 14 days">Over 14 days</option>
                        </select>
                    </th>
                </tr>
                <tr class="sorting">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td data-uiclass="o-cont-value-th">
                        <a href="javascript:void(0)" class="contract-value" sort="contract_value" sort_order="desc"><span></span></a>
                    </td>
                    <td data-uiclass="o-cont-date-th">
                        <a href="javascript:void(0)" class="contract-date" sort="contract_end_date" sort_order="desc"><span></span></a>
                    </td> 
                    <td data-uiclass="o-status-th">
                        <a href="javascript:void(0)" class="space-status" sort="status" sort_order="desc"><span></span></a>
                    </td>
                    <td data-uiclass="b-comm-th">
                        <table class="full-table value-table top-table">
                            <tbody>
                                <tr>
                                    <td><a href="javascript:void(0)" class="community" sort="community_buyer_total" sort_order="desc"><span></span></a></td>
                                    <td><a href="javascript:void(0)" class="community" sort="community_buyer_filter" sort_order="desc"><span></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td data-uiclass="s-comm-th">
                        <table class="full-table value-table top-table">
                            <tbody>
                                <tr>
                                    <td><a href="javascript:void(0)" class="community" sort="community_supplier_total" sort_order="desc"><span></span></a></td>
                                    <td><a href="javascript:void(0)" class="community" sort="community_supplier_filter" sort_order="desc"><span></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td data-uiclass="o-comm-th">
                        <table class="full-table value-table top-table">
                            <tbody>
                                <tr>
                                    <td><a href="javascript:void(0)" class="community" sort="community_overall_total" sort_order="desc"><span></span></a></td>
                                    <td><a href="javascript:void(0)" class="community" sort="community_overall_filter" sort_order="desc"><span></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td data-uiclass="b-csi-th">
                        <table class="full-table value-table top-table">
                            <tbody>
                                <tr>
                                    <td><a href="javascript:void(0)" class="csi" sort="buyer_csi_score_this_month" sort_order="desc"><span></span></a></td>
                                    <td><a href="javascript:void(0)" class="csi" sort="buyer_csi_score_change" sort_order="desc"><span></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td data-uiclass="s-csi-th">
                        <table class="full-table value-table top-table">
                            <tbody>
                                <tr>
                                    <td><a href="javascript:void(0)" class="csi" sort="seller_csi_score_this_month" sort_order="desc"><span></span></a></td>
                                    <td><a href="javascript:void(0)" class="csi" sort="seller_csi_score_change" sort_order="desc"><span></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td data-uiclass="o-csi-th">
                        <table class="full-table value-table top-table">
                            <tbody>
                                <tr>
                                    <td><a href="javascript:void(0)" class="csi" sort="overall_csi_score" sort_order="desc"><span></span></a></td>
                                    <td><a href="javascript:void(0)" class="csi" sort="overall_csi_score_change" sort_order="desc"><span></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td data-uiclass="b-posts-th">
                        <table class="full-table value-table top-table">
                            <tbody>
                                <tr>
                                    <td><a href="javascript:void(0)" class="post-list" sort="buyer_posts_total" sort_order="desc"><span></span></a></td>
                                    <td><a href="javascript:void(0)" class="post-list" sort="buyer_posts_change" sort_order="desc"><span></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td data-uiclass="s-posts-th">
                        <table class="full-table value-table top-table">
                            <tbody>
                                <tr>
                                    <td><a href="javascript:void(0)" class="post-list" sort="supplier_posts_total" sort_order="desc"><span></span></a></td>
                                    <td><a href="javascript:void(0)" class="post-list" sort="supplier_posts_change" sort_order="desc"><span></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td data-uiclass="o-posts-th">
                        <table class="full-table value-table top-table">
                            <tbody>
                                <tr>
                                    <td><a href="javascript:void(0)" class="post-list" sort="overall_posts_total" sort_order="desc"><span></span></a></td>
                                    <td><a href="javascript:void(0)" class="post-list" sort="overall_posts_change" sort_order="desc"><span></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td data-uiclass="b-pi-th">
                        <a href="javascript:void(0)" class="post-interactions" sort="buyer_post_interactions" sort_order="desc"><span></span></a>
                    </td>
                    <td data-uiclass="s-pi-th">
                        <a href="javascript:void(0)" class="post-interactions" sort="seller_post_interactions" sort_order="desc"><span></span></a>
                    </td>
                    <td data-uiclass="o-pi-th">
                        <a href="javascript:void(0)" class="post-interactions" sort="post_interactions" sort_order="desc"><span></span></a>
                    </td>
                    <td data-uiclass="o-nps-th">
                        <a href="javascript:void(0)" class="nps" sort="nps" sort_order="desc"><span></span></a>
                    </td>
                    <td data-uiclass="o-pinv-th">
                        <a href="javascript:void(0)" class="pending_invites" sort="pending" sort_order="desc"><span></span></a>
                    </td>
                    <td data-uiclass="o-prog-th">
                        <a href="javascript:void(0)" class="progress_bar" sort="progress_bar" sort_order="desc"><span></span></a>
                    </td>
                    <td data-uiclass="o-prog-th" class="border-none"></td>
                </tr>
            </thead>
            <tbody class="mi-data-grid">
                @include('management_information.share_record')
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade performance-email mi-modal" id="mi_email_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="miEmailModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content white-popup">
         <form method="POST" action="{{ url('/email',[], env('HTTPS_ENABLE', true)) }}" enctype="multipart/form-data" id="edit_email">
            {!! csrf_field() !!}
             <div class="modal-body">
                <h4 id="miEmailModalLabel">Performance Email</h4>
                    <div class="form-group row">
                        <div class="performance-mail-top">
                            <input type="email" name="email_to" class="form-control" placeholder="To" id="email_to" required>
                            <span class="invalid-mail hidden">Please enter a valid email address</span>
                        </div>
                        <div class="performance-mail-other">
                            <span class="add-cc toggle-cc"><a href="javascript:void(0)">Cc</a></span>
                            <span class="add-cc toggle-bcc"><a href="javascript:void(0)">Bcc</a></span>
                        </div>
                    </div>
                    <div class="form-group row hidden email-cc">
                        <div class="performance-mail-top">
                            <input type="email" name="email_cc" class="form-control" placeholder="Cc">
                            <span class="invalid-mail hidden">Please enter a valid email address</span>
                        </div>
                    </div>
                    <div class="form-group row hidden email-bcc">
                        <div class="performance-mail-top">
                            <input type="email" name="email_bcc" class="form-control" placeholder="Bcc"  id="email_bcc">
                            <span class="invalid-mail hidden">Please enter a valid email address</span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="text" name="email_subject" class="form-control" placeholder="Default Subject" id="email_subject" required>
                        <span class="empty-input hidden">Please add subject</span>
                    </div>
                    <div class="form-group message-area">
                        <textarea name="email_body" class="form-control rounded-0" rows="8" placeholder="Default text" id="email_body"></textarea>
                        <span class="empty-input hidden">Please add message</span>
                    </div>
                    <div class="performance-tiles-col">
                        <div class="col-sm-3 custom-performance-col customer-tile">
                            <div class="performance-tiles-box">
                                <h5>Customer</h5>
                                <div class="performance-count-col">
                                    <span class="left-count-blue">60</span>
                                    <span class="left-count-blue text-dark"><span class="performance-up">+</span> 3</span>
                                    <div class="text-right performance-time">
                                        <p>This month</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 custom-performance-col seller-tile">
                            <div class="performance-tiles-box">
                                <h5>Supplier</h5>
                                <div class="performance-count-col">
                                    <span class="left-count-blue">20</span>
                                    <span class="left-count-blue text-dark"><span class="performance-down">-</span> 6</span>
                                    <div class="text-right performance-time">
                                        <p>This month</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 custom-performance-col posts-tile">
                            <div class="performance-tiles-box">
                                <h5>Posts</h5>
                                <div class="performance-count-col">
                                    <span class="left-count-blue">60</span>
                                    <span class="left-count-blue text-dark"><span class="performance-up">+</span> 12</span>
                                    <div class="text-right performance-time">
                                        <p>This month</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 custom-performance-col csi-tile">
                            <div class="performance-tiles-box">
                                <h5>CSI</h5>
                                <div class="performance-count-col">
                                    <span class="left-count-blue">157</span>
                                    <span class="left-count-blue text-dark"><span class="performance-up">+</span> 10%</span>
                                    <div class="text-right performance-time">
                                        <p>This month</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="performance-bottom-left pull-left">
                        <div class="performance-dropdown-box">
                            <div class="fix-title">
                                <span>Communication:</span>
                            </div>
                            <div class="dropdown pull-left">
                                <select name="communication_type" class="communication-type selectpicker" >
                                    <option value="community">Community</option>
                                    <option value="csi">CSI Score</option>
                                    <option value="posts">Posts</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="space_id">
                    <input type="hidden" name="community_buyers" id="community_buyers">
                    <input type="hidden" name="community_buyers_change" id="community_buyers_change">
                    <input type="hidden" name="community_sellers" id="community_sellers">
                    <input type="hidden" name="community_sellers_change" id="community_sellers_change">
                    <input type="hidden" name="total_posts" id="total_posts">
                    <input type="hidden" name="month_posts" id="month_posts">
                    <input type="hidden" name="csi_score" id="csi_score">
                    <input type="hidden" name="csi_score_change" id="csi_score_change">
                    <div class="performance-bottom-right pull-right">
                        <button type="button" class="btn btn-primary pull-right send-email">Send</button>
                        <button type="button" class="close btn btn-primary" data-dismiss="modal" aria-label="Close">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade performance-email success-popup" id="mi_success_popup" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="miEmailModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content white-popup">
            <div class="modal-body">
                <div class="success-popup-box">
                    <div class="mail-success-icon">
                        <img src="{{url('images/messagesent.svg')}}" alt="Success Mail" />
                    </div>
                    <div class="mail-success-text">
                        <p>Your message has been <br/>sent successfully.</p>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    baseurl = "<?php echo e(env('APP_URL')); ?>";
</script>
<script rel="text/javascript" src="{{ url('js/handle_bar.js?q='.env('CACHE_COUNTER', rand()),[],env('HTTPS_ENABLE', true)) }}"></script>
<script rel="text/javascript" src="{{ url('js/custom/handlebarjs_helpers.js?q='.env('CACHE_COUNTER', rand()),[],env('HTTPS_ENABLE', true)) }}"></script>
<script rel="text/javascript" src="{{ url('js/custom/common.js?q='.env('CACHE_COUNTER'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script rel="text/javascript" src="{{ url('js/custom/management_information.js?q='.env('CACHE_COUNTER'),[],env('HTTPS_ENABLE', true)) }}"></script>
<script rel="text/javascript" src="{{ url('js/moment.min.js?q='.env('CACHE_COUNTER'),[],env('HTTPS_ENABLE', true)) }}"></script>
 <script rel="text/javascript" src="{{url('js/daterangepicker.js',[],env('HTTPS_ENABLE', true))}}"></script>
 <script rel="text/javascript" src="{{url('js/bootstrap-multiselect_V0_9_15.js?q='.env('CACHE_COUNTER'),[],env('HTTPS_ENABLE', true))}}"></script>
 <script rel="text/javascript" src="{{url('js/jquery.stickytableheaders.js?q='.env('CACHE_COUNTER'),[],env('HTTPS_ENABLE', true))}}"></script>
@endsection