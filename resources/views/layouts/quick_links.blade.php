<div class="modal fade custom-tile-popup" id="quick_links_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content white-popup">
         <div class="modal-header">
            <h2 class="modal-title" id="myModalLabel">Quick Links</h2>
            <p>Add useful links for your users to access from their homepage.</p>
         </div>
         <form method="post" action="{{ url('/save_quick_links',[],env('HTTPS_ENABLE', true)) }}" enctype="multipart/form-data" class="quick_links_form" id="quick_links_form">
            {!! csrf_field() !!}
         <div class="modal-body">
            <input type="hidden" name="user_id" id="links_user_id" autocomplete="off" value="{{ Auth::user()->id }}">
            <div class="link-columns full-width">
            <div class="col-md-6 quick-link-col-form">
               <span class="link-input-icon"><img src="{{ url('/',[],$ssl) }}/images/ic_link.svg"></span>
               <input type="text" class="form-control hyperlink hyperlink_0" placeholder="Hyperlink" aria-describedby="basic-addon1" name="hyperlink[]" autocomplete="off">
            </div>
            <div class="col-md-6 quick-link-col-form">
               <input type="text" class="form-control link_name link_name_0" placeholder="Text to display" aria-describedby="basic-addon1" name="link_name[]" autocomplete="off" maxlength="35">
            </div>

            <div class="col-md-6 quick-link-col-form">
               <span class="link-input-icon"><img src="{{ url('/',[],$ssl) }}/images/ic_link.svg"></span>
               <input type="text" class="form-control hyperlink hyperlink_1" placeholder="Hyperlink" aria-describedby="basic-addon1" name="hyperlink[]" autocomplete="off">
            </div>
            <div class="col-md-6 quick-link-col-form">
               <input type="text" class="form-control link_name link_name_1" placeholder="Text to display" aria-describedby="basic-addon1" name="link_name[]" autocomplete="off" maxlength="35">
            </div>

            <div class="col-md-6 quick-link-col-form">
               <span class="link-input-icon"><img src="{{ url('/',[],$ssl) }}/images/ic_link.svg"></span>
               <input type="text" class="form-control hyperlink hyperlink_2" placeholder="Hyperlink" aria-describedby="basic-addon1" name="hyperlink[]" autocomplete="off">
            </div>
            <div class="col-md-6 quick-link-col-form">
               <input type="text" class="form-control link_name link_name_2" placeholder="Text to display" aria-describedby="basic-addon1" name="link_name[]" autocomplete="off" maxlength="35">
            </div>

            <div class="col-md-6 quick-link-col-form">
               <span class="link-input-icon"><img src="{{ url('/',[],$ssl) }}/images/ic_link.svg"></span>
               <input type="text" class="form-control hyperlink hyperlink_3" placeholder="Hyperlink" aria-describedby="basic-addon1" name="hyperlink[]" autocomplete="off">
            </div>
            <div class="col-md-6 quick-link-col-form">
               <input type="text" class="form-control link_name link_name_3" placeholder="Text to display" aria-describedby="basic-addon1" name="link_name[]" autocomplete="off" maxlength="35">
            </div>
           </div>
            <span class="quick_links_error"></span>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-primary btn-quick-links btn-quick-links-button pull-right" onclick="sendQuickLinks(this)" user_id="{{ Auth::user()->id }}">save</button>
            <button type="button" class="close btn btn-primary btn-quick-links" data-dismiss="modal" aria-label="Close">Cancel</button>
         </div>
         </form>
      </div>
         <!-- white popoup -->
   </div>
</div>