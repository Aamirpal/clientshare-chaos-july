@foreach ($edit_postdata as $posts)
@foreach ($space_data as $data)
   @php
      $filetype = $posts['post_file_url'];
      $ext = pathinfo($filetype, PATHINFO_EXTENSION);
   @endphp

   @if(!empty($posts['user']['profile_image_url']))
      <img src="{{ $posts['user']['profile_image_url'] }}" class="left img-responsive dp" alt="">
   @endif

   @if(empty($posts['user']['profile_image_url']))
      <img src="{{env('APP_URL')}}/images/dummy-avatar-img.svg" class="left img-responsive dp" alt="">
   @endif 
   <div class="name-wrap">
      <a href="#!" class="title">{{ ucfirst($posts['user']['first_name']) }} {{ ucfirst($posts['user']['last_name']) }}</a>
      <span class="time"><?= date('d-M-y h:i:s',strtotime($posts['updated_at']))?></span>
   </div>

   <!--List options against a post -->
   <div class="dropdown right">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="{{env('APP_URL')}}/images/ic_expand_more.svg" alt="" class="img-responsive" style="margin-top: 4px;"></a>
      <ul class="dropdown-menu">
         <li><a href="" id="edit_post" editpost="{{ $posts['id'] }}">Edit post</a></li>
         <li><a href="" style="color: #EA4335;" id="delete" data-toggle="modal" data-target="#deleteModal{{$posts['id']}}">Delete post</a></li>
         <img src="{{env('APP_URL')}}/images/ic_expand_more.svg" alt="" class="img-responsive" style="margin-top: 4px;">
      </ul>
   </div>
   <!--End options -->

   <!-- Dialog post to delete the post -->
   <div class="modal fade" id="deleteModal{{$posts['id']}}" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
      <div class="modal-dialog modal-sm" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title" id="myModalLabel">Delete post</h4>
            </div>
            <div class="modal-body">
               <p>This will permanently delete the post form the Client Share and all members will loss access to it.</p>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default left" data-dismiss="modal">CANCEL</button>
               <a href="{{env('APP_URL')}}/delete_post/{{ $posts['id'] }}" id="delete" class="btn btn-primary modal_initiate_btn" >DELETE POST</a>
            </div>
         </div>
      </div>
   </div>

   <a href="#!" class="chip disable">
   @foreach ((array)json_decode($posts['metadata']) as $key => $value)
   @php 
      $category_value = $data['category_tags'][$value]; 
   @endphp
   {{ $category_value }}
   @endforeach
   </a>
                  
   <p>{{ $posts['post_description'] }}</p>
   <br>
   @php $myString = $posts['visibility'];
         $myArray = explode(',', $myString);
         $user_visilbe_count = sizeOfCustom($myArray);
   @endphp
   <div class="visible-to-section"><span>— Visible to 
   <a href="#!" data-toggle="modal" data-target="#visiblepopup">
   @php $user_visible_i = 1; @endphp
   @foreach($myArray as $visible_u)
      @if($user_visible_i<=2)
         @foreach( $approve_user as $key1)
            @php
               if(in_array($visible_u, $key1['user']->toArray())){
                  echo ucfirst($key1['user']['first_name']).' '.ucfirst($key1['user']['last_name']);
                  if($user_visible_i<2){
                     echo ',';
                  }
               }
            @endphp
         @endforeach
      @endif       
      @php $user_visible_i++; @endphp
   @endforeach
   @if($user_visilbe_count>3)
   and {{$user_visilbe_count-3}} others
   @endif
   </a>
   </span> <a href="#!" data-toggle="modal" data-target="#visiblepopup{{ $posts['id'] }}"><img src="{{env('APP_URL')}}/images/ic_settings_black.svg" class="img-responsive" alt=""></a></div>
      <div class="modal fade endrose" id="visiblepopup{{ $posts['id']}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
         <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{env('APP_URL')}}/images/ic_highlight_removegray.svg" alt=""></button>
                  <h4 class="modal-title" id="myModalLabel">Visibility</h4>
                  <p>Control who sees the content.</p>
               </div>

               <div class="modal-body">
                  <div class="checkbox fullwidth">
                      <input id="checkbox1" type="checkbox" name="checkbox" value="1" checked="checked"><label for="checkbox1">Toggle All</label>

                  </div>
                  @foreach($myArray as $visible_u1)
                    @foreach( $approve_user as $key2)
                        @if (in_array($visible_u1, $key2['user']->toArray()))
                  <div class="checkbox fullwidth">
                      <input id="checkbox4" type="checkbox" name="checkbox" value="{{ $key2['user']['id']}}" checked="checked"><label for="checkbox4">{{ ucfirst($key2['user']['first_name'])}}{{ ucfirst($key2['user']['last_name'])}}</label>
                  </div>
                       @endif
                    @endforeach
                  @endforeach
               </div>

               <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                  <button type="button" class="btn btn-primary">Save</button>
               </div>
            </div>
         </div>
      </div>

      @if ($ext=='png' || $ext=='gif' || $ext=='jpg' || $ext=='jpeg')

      @php $url = $posts['post_file_url'];
            $path = parse_url($url, PHP_URL_PATH);
            $file_name = basename($path); @endphp
  
      <a data-toggle="modal" data-target="#myModal{{ $posts['id'] }}">

      <div>
         <div style="width:50%;float:left"><img src="{{ $posts['post_file_url'] }}" style="width:200px;height:150px"  viewfile="{{ $posts['id'] }}" id="view_file"></div>
         <div style="width:50%;float:left"><?php echo $file_name; ?></div>
      </div>
   </a>
   <div id="myModal{{ $posts['id'] }}" class="modal fade">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
               <button><a href = "{{ $posts['post_file_url'] }}" download><i class="fa fa-download" aria-hidden="true"></i></a></button>
               <h4 class="modal-title">{{ $file_name }}</h4>
            </div>
            <div class="modal-body">
               <img class="img-responsive" src="{{ $posts['post_file_url'] }}">
               
            </div>
         </div>
      </div>
   </div>

   @endif 
   @if ($ext=='mp4' || $ext=='mp3' || $ext=='avi' || $ext=='mkv')
   <video width="400" controls style="float:left">
      <source src="{{ $posts['post_file_url'] }}" type="video/{{$ext}}">
      Your browser does not support HTML5 video.
   </video>
   @endif
   @if ($ext=='doc' || $ext=='docs' || $ext=='docx')
   <?php $url = $posts['post_file_url'];
      $path = parse_url($url, PHP_URL_PATH); 
      $file_name = basename($path,'.'.$ext); ?>  
   <a data-toggle="modal" data-target="#docModal{{ $posts['id'] }}">
      <div>
         <div style="width:50%;float:left"><img src="http://downloadicons.net/sites/default/files/doc-icon-13462.png" style="width:200px;height:150px"  viewfile="{{ $posts['id'] }}" id="view_file"></div>
         <div style="width:50%;float:left">{{$file_name}}</div>
      </div>
   </a>
   <div class="modal fade" id="docModal{{ $posts['id'] }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
               <h4 class="modal-title" id="myModalLabel">{{$file_name}}</h4>
            </div>
            <div class="modal-body">
               <div style="text-align: center;">
                  <iframe src="http://docs.google.com/gview?url={{ $posts['post_file_url'] }}&embedded=true" 
                     style="width:500px; height:500px;" frameborder="0"></iframe>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>
   @endif 
   @if ($ext=='pdf')
   <?php $url = $posts['post_file_url'];
      $path = parse_url($url, PHP_URL_PATH); 
      $file_name = basename($path,'.'.$ext); ?>  
   <a data-toggle="modal" data-target="#pdfModal{{ $posts['id'] }}">
      <div>
         <div style="width:50%;float:left"><img src="http://cdn.ndtv.com/tech/images/pdf_format_wikipedia.jpg" style="width:200px;height:150px"  viewfile="{{ $posts['id'] }}" id="view_file"></div>
         <div style="width:50%;float:left">{{$file_name}}</div>
      </div>
   </a>
   <div class="modal fade" id="pdfModal{{ $posts['id'] }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
               <h4 class="modal-title" id="myModalLabel">{{$file_name}}</h4>
            </div>
            <div class="modal-body">
               <div style="text-align: center;">
                  <iframe src="http://docs.google.com/gview?url={{ $posts['post_file_url'] }}&embedded=true" 
                     style="width:500px; height:500px;" frameborder="0"></iframe>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>
    
   @endif 
   @if ($ext=='xlsx' || $ext=='xls' || $ext=='csv')
   <?php $url = $posts['post_file_url'];
      $path = parse_url($url, PHP_URL_PATH);
      $file_name = basename($path); ?>
   <a href = "{{ $posts['post_file_url'] }}" download>
      <div>
         <div style="width:50%;float:left"><img src="http://0.tqn.com/d/pcsupport/1/S/b/_/-/-/xls-file.png" style="width:200px;height:150px"  viewfile="{{ $posts['id'] }}" id="view_file"></div>
         <div style="width:50%;float:left">{{$file_name}}</div>
      </div>
   </a>
   @endif
   @if ($ext=='ppt' || $ext=='pptx')
   <?php $url = $posts['post_file_url'];
      $path = parse_url($url, PHP_URL_PATH); 
      $file_name = basename($path,'.'.$ext); ?>  
   <a data-toggle="modal" data-target="#pptModal{{ $posts['id'] }}">
      <div>
         <div style="width:50%;float:left"><img src="http://www.filefacts.net/zh/exticons/173.png" style="width:200px;height:150px"  viewfile="{{ $posts['id'] }}" id="view_file"></div>
         <div style="width:50%;float:left">{{$file_name}}</div>
      </div>
   </a>
   <div class="modal fade" id="pptModal{{ $posts['id'] }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
               <h4 class="modal-title" id="myModalLabel">{{$file_name}}</h4>
            </div>
            <div class="modal-body">
               <div style="text-align: center;">
                  <iframe src="http://docs.google.com/gview?url={{ $posts['post_file_url'] }}&embedded=true" 
                     style="width:500px; height:500px;" frameborder="0"></iframe>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>
   @endif

 <textarea style="display:none" id="edit_post_id_{{$posts['id']}}" olddata="{{ $posts['post_description'] }}"> {{ $posts['post_description'] }} </textarea>

      
@endforeach
@endforeach

  