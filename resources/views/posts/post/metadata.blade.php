<div class="post-header-col full-width">
   <div class="post-left-col">
      <a href="#">
      <span class="post-admin-dp" style="background-image: url({{$post['user']['profile_image_url']}});"></span>
      </a>
      <div class="name-wrap">
         <span class="post-admin-name"><a href="#">{{$post['user']['fullname']}}</a></span>
         <span class="post-admin-name">{{$post['created_at']}}</span>
      </div>
   </div>
   <div class="post-right-col">
      <div class="dropdown pull-right">
         <a href="#" class="dropdown-toggle edit-post-cog" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
         <img src="{{env('APP_URL')}}/images/ic_settings-grey.svg" alt="" class="img-responsive">
         </a>
         <ul class="dropdown-menu edit-post-dropdown">
            <li>
               <a href="javascript:void(0)" class="minimize-post">
               <span class="dropdown-post-icon">
               <img src="{{env('APP_URL')}}/images/ic_unfold_less.svg" alt="" class="img-responsive"></span>
               Minimise post
               </a>
            </li>
            <li>
               <a href="javascript:void(0)" class="pin-post-disable">
               <span class="dropdown-post-icon">
               <img src="{{env('APP_URL')}}/images/ic_pin.svg" alt="" class="img-responsive"></span>
               Pin post
               </a>
            </li>
            <li>
               <a href="javascript:void(0)" class="editpost_data">
               <span class="dropdown-post-icon">
               <img src="{{env('APP_URL')}}/images/ic_edit_black.svg" alt="" class="img-responsive">
               </span>
               Edit post
               </a>
            </li>
            <li>
               <a href="javascript:void(0)" data-toggle="modal" data-target="">
               <span class="dropdown-post-icon">
               <img src="{{env('APP_URL')}}/images/ic_delete_red.svg" alt="" class="img-responsive"></span>
               Delete post
               </a>
            </li>
            <img src="{{env('APP_URL')}}/images/ic_settings-grey.svg" alt="" class="img-responsive">
         </ul>
      </div>
      <div class="category-chip-wrap pull-left">
         <a href="javascript:void(0)" class="chip disable">
         <input value="category_1" type="hidden">General
         </a>            
      </div>
   </div>
</div>