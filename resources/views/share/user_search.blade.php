@extends('layouts.super_admin_fullwidth')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ url('/css/bootstrap-multiselect_v0_9_15.css') }}">

	<section class="main-content">
    	<div class="container">
    		<div class="row">
				<div class="col-xs-12">
			  		<div class="edit-mail-form text-center">
			            <form class="edit-mail-form-box user-share-search" id="user-share-search" action="space-search-by-user" method="POST">
                           	<div class="col-xs-12 text-center">
                           		<span class="search-error error-message top-error"></span>
                           	</div>
					    	<div class="form-group row">
					        	<div class="col-sm-6">
					            	<input class="form-control user_first_name" name="first_name" placeholder="First Name" type="text">
					            	<span class="first-name-error error-message"></span>
					            </div>
					            <div class="col-sm-6">
					            	<input class="form-control user_last_name" name="last_name" placeholder="Last Name" type="text">
					            	<span class="last-name-error error-message"></span>
					            </div>
					        </div>

					        <div class="form-group row">
					        	<div class="col-xs-12 text-center">
					        		<p>OR</p>
					        	</div>
					        </div>

					        <div class="form-group row">
					        	<div class="col-sm-12">
					            	<input class="form-control user_email" name="email" placeholder="Email" type="mail">
					            	<span class="email-error error-message"></span>
					            </div>
					        </div>

					        <div class="full-width col-xs-12">   
			          			<div class="form-group row form-search-col text-right">
			            			<button type="button" id="user-search-button" class="btn btn-primary search-button">Search</button>
			          			</div>
			       			</div>
			      		</form>
			    	</div>
			    	<span class="no-row-result error-message"></span>
			  	</div>

			  	<div class="col-xs-12">
			  		<div class="user-share-table">
			  			<div class="table-responsive">
			  				<table class="table table-main table-striped table-bordered">
			  					<thead>
			  						<tr>
			  							<th>First name</th>
			  							<th>Last name</th>
			  							<th>Email address</th>
			  							<th>Share name</th>
										<th>Community</th>
			  							<th>User status</th>
			  							<th>Share status</th>
			  							<th>User status</th>
			  							<th></th>
			  						</tr>
			  					</thead>
			  					<tbody class="user-data-grid">
					                @include('share.user_share_table')
					            </tbody>
			  				</table>
			  			</div>
			  		</div>
			  	</div>

			</div>
        </div>
	</section>
<div class="mi-overlay-div">
    <img src="{{url('images/loading_bar1.gif')}}" alt="loader" />
    <p>Loading... Please wait</p>
</div>
<script>
    baseurl = "<?php echo e(env('APP_URL')); ?>";
</script>
<script rel="text/javascript" src="{{ url('js/custom/user_search.js?q='.env('CACHE_COUNTER', rand()),[],env('HTTPS_ENABLE', true)) }}"></script>
<script rel="text/javascript" src="{{ url('js/handle_bar.js?q='.env('CACHE_COUNTER', rand()),[],env('HTTPS_ENABLE', true)) }}"></script>
<script rel="text/javascript" src="{{ url('js/custom/handlebarjs_helpers.js?q='.env('CACHE_COUNTER', rand()),[],env('HTTPS_ENABLE', true)) }}"></script>
<script rel="text/javascript" src="{{ url('js/custom/common.js?q='.env('CACHE_COUNTER'),[],env('HTTPS_ENABLE', true)) }}"></script>
 <script rel="text/javascript" src="{{url('js/bootstrap-multiselect_V0_9_15.js?q='.env('CACHE_COUNTER'),[],env('HTTPS_ENABLE', true))}}"></script>

 <div class="modal fade" id="promote_user_by_admin" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-sm" role="document">
       <div class="modal-content promot-user">
		  <div class="form-submit-loader" style="display: none;"><span></span></div>
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Promote to administrator</h4>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to promote <span class="username"></span> to admin in the <span class="share_name"></span> Share?</p>
			<input type="hidden" class='space_id'> 
			<input type="hidden" class='share_name'> 
			<input type="hidden" class='user_id'> 
			<input type="hidden" class='uid'>
          </div>
          <div class="modal-footer">
             <button type="button" class="promote_user btn btn-primary">Promote</button>
             <button type="button" class="btn btn-default left" data-dismiss="modal">Cancel</button>
          </div>
       </div>
    </div>
 </div>

 <div class="modal fade" id="removeuserpopup" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-sm" role="document">
       <div class="modal-content">
       	  <div class="form-submit-loader" style="display: none;"><span></span></div>
          <div class="modal-header">
             <h4 class="modal-title" id="myModalLabel">Remove <span class="username"></span>?</h4>
          </div>
          <div class="modal-body">
          	<input type="hidden" class='space_id'>
			<input type="hidden" class='user_id'> 
			<input type="hidden" class='uid'>
			
             <p>Are you sure you want to remove <span class="username"></span> from <span class="share_name"></span> Client Share.</p>
          </div>
          <div class="modal-footer">
             <button type="button" class="remove_user btn btn-primary modal_initiate_btn">Remove User</button>
             <button type="button" class="btn btn-default left" data-dismiss="modal">Cancel</button>
          </div>
       </div>
    </div>
 </div>
@endsection