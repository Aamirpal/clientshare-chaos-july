@inject('post_controller', 'App\Http\Controllers\PostController')
<?php
   $ssl = false;
   if(env('APP_ENV')!='local')
   $ssl = true;
$profile_img = Auth::user()->profile_image_url;
$vis_count = 1;
   

    foreach ($postdata as $posts):
      //echo '<pre>';
   
      $filetype = $posts['post_file_url'];
      $ext = pathinfo($filetype, PATHINFO_EXTENSION);
      $ext = strtolower($ext);
      ?>
   <div class="post-wrap" id="post_{{ $posts['id'] }}">
      <div class="post ">
         <div class="top-section box" id="postid">
            <div class="top-section change_post_{{ $posts['id'] }}">
              <div class = "top-section-wrap">
               @if(!empty($posts['user']['profile_image_url']))
               <?php if($posts['user']['space_user'][0]['user_status']==1) { ?>
                    <span style="background-image: url('{{ $posts['user']['profile_image_url'] }}');" class="pro_pic_wrap dp"></span>
                     <?php } else { ?>
                      <a href="#!" class="title @if($posts['user']['space_user'][0]['user_status'] == 1 ) inactive_name @endif" @if($posts['user']['space_user'][0]['user_status'] == 0 ) data-id="{{$posts['user']['id']}}" onclick="liked_info(this);" @endif><span style="background-image: url('{{ $posts['user']['profile_image_url'] }}');" class="pro_pic_wrap dp"></span></a>
                     <?php } ?>
               
               @endif
               @if(empty($posts['user']['profile_image_url']))
               <?php if($posts['user']['space_user'][0]['user_status']==1) { ?>
                     <span style="background-image: url('{{ url('/',[],$ssl)}}/images/dummy-avatar-img.svg');" class="pro_pic_wrap dp"></span>
                     <?php } else { ?>
                      <a href="#!" class="title @if($posts['user']['space_user'][0]['user_status'] == 1 ) inactive_name @endif" @if($posts['user']['space_user'][0]['user_status'] == 0 ) data-id="{{$posts['user']['id']}}" onclick="liked_info(this);" @endif><span style="background-image: url('{{ url('/',[],$ssl)}}/images/dummy-avatar-img.svg');" class="pro_pic_wrap dp"></span></a>
                     <?php } ?>
              
               @endif
               <div class="name-wrap">
                <?php if($posts['user']['space_user'][0]['user_status']==1) { ?>
                     {{ ucfirst($posts['user']['first_name']) }} {{ ucfirst($posts['user']['last_name']) }} @if($posts['user']['space_user'][0]['user_status'] == 1 ) (Inactive) @endif
                     <?php } else { ?>
                      <a href="#!" class="title @if($posts['user']['space_user'][0]['user_status'] == 1 ) inactive_name @endif" @if($posts['user']['space_user'][0]['user_status'] == 0 ) data-id="{{$posts['user']['id']}}" onclick="liked_info(this);" @endif>{{ ucfirst($posts['user']['first_name']) }} {{ ucfirst($posts['user']['last_name']) }} @if($posts['user']['space_user'][0]['user_status'] == 1 ) (Inactive) @endif</a>
                     <?php } ?>
                 
                  <span class="time"><?= date('F d, H:i ',strtotime($posts['updated_at']))?></span>
               </div>
               <!-- EDIT POST DROPDOWN LINK START-->
               <?php //@if(Auth::user()->id == $posts['user_id'] ) ?>
               @if(Session::get('space_info')->toArray()['space_user'][0]['user_type_id']=='2' || Auth::user()->id == $posts['user_id'])
               <div class="dropdown right">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="{{ url('/',[],$ssl) }}/images/ic_expand_more.svg" alt="" class="img-responsive" style="margin-top: 4px;"></a>
                  <ul class="dropdown-menu edit-post-dropdown">
                     <li><a href="" id="edit_post-removethis" class="editpost_data" editpost="{{ $posts['id'] }}" postby="{{ $posts['user_id'] }}" activeuser="{{ Auth::user()->id }}" >Edit post</a></li>
                     <li><a href="" style="color: #EA4335;" id="delete" data-toggle="modal" data-target="#deleteModal{{$posts['id']}}" postby="{{ $posts['user_id'] }}" activeuser="{{ Auth::user()->id }}">Delete post</a></li>
                     <img src="{{ url('/',[],$ssl) }}/images/ic_expand_more.svg" alt="" class="img-responsive" style="margin-top: 4px;">
                  </ul>
               </div>
               @endif
               <?php //@endif ?>
               <!-- EDIT POST DROPDOWN LINK START ENDS-->
               <div class="modal fade" id="deleteModal{{$posts['id']}}" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
                  <div class="modal-dialog modal-sm" role="document">
                     <div class="modal-content">
                        <div class="modal-header">
                           <button type="button" class="close-inner" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                           <h4 class="modal-title" id="myModalLabel">Delete post</h4>
                        </div>
                        <div class="modal-body">
                           <p>Do you want to permanently delete this post?</p>
                        </div>
                        <div class="modal-footer">
                           <button type="button" class="btn btn-default left" data-dismiss="modal">CANCEL</button>
                           <a href="{{ url('/',[],$ssl) }}/delete_post/{{ $posts['id'] }}" id="delete" class="btn btn-primary modal_initiate_btn" >DELETE POST</a>
                        </div>
                     </div>
                  </div>
               </div>
               <a href="#!" class="chip  @if(isset($_REQUEST['tokencategory']) && $_REQUEST['tokencategory'] !='') @else disable @endif">
               <input type="hidden" value="{{json_decode($posts['metadata'],true)['category']}}" />
               @foreach ((array)json_decode($posts['metadata'],true)['category'] as $key => $value)
               {{ $data->category_tags[$value] }}
               @endforeach
               </a>
               <?php $myString = $posts['visibility'];
                  $myArray = explode(',', $myString);
                  $user_visilbe_count = sizeOfCustom($myArray);
                  $count_approve_user = sizeOfCustom($approve_user);
                  
                   ?>
               <div class="visible-to-section">
                  <span>
                     — Visible to
                     <a href="#!" data-toggle="modal" data-target="#visiblepopup" class="replace_ajax_{{$posts['id']}} add_scroll">
                        <input type="hidden" class="post_visible_user" value="{{$myString}}">
                        <?php $user_visible_i = 1;
                           if(in_array('All',$myArray)){ ?>
                        <span href="#!" data-toggle="modal" data-target="#endoresedpopup1"><span data-trigger="hover" type="button" data-toggle="popover"
                           data-placement="bottom" data-html="true"
                           data-content="<?php $use = 1;?>
                           @foreach( $approve_user as $key)
                           @if($use <= 5)
                           {{ ucfirst($key['user']['first_name'])}} {{ ucfirst($key['user']['last_name'])}}
                           <br/>
                           @endif
                           @if($use > 5)
                           {{'and'}} <?php echo $u = sizeOfCustom($approve_user)-5 ?> {{'others'}}
                           <?php break;?>
                           @endif
                           <?php $use++ ?>
                           @endforeach
                           " id="example_popover" class="visible_tooltip" endors-poup-post="">
                        <span data-toggle="modal" data-target="#visiblepopup{{$posts['id']}}" class="visibility_setting_more add_scroll" setting-id="{{$posts['id']}}" space-id="{{$spaceid}}">
                        Everyone
                        </span>
                        </span>
                        </span>
                        <?php  } else{     ?>
                        @foreach($myArray as $visible_u => $ind )
                        @if( isset($approve_user_ref[$ind]) )
                        <span id="see_community" href="#" userid="{{ $ind }}" spaceid="{{$spaceid}}" >
                        <?php  echo  $approve_user_ref[$ind] ?></span>
                        <?php
                           if($visible_u == 1) break;
                           if($user_visilbe_count>1 && $user_visible_i==1){
                             echo ',';
                           }?>
                        @endif
                        @if(isset($key1['metadata']['user_profile']['bio']))
                        @php
                          $full_bio = $key1['metadata']['user_profile']['bio'];
                        @endphp
                        @if(strlen($key1['metadata']['user_profile']['bio'])>30)
                        @php
                        $biography = substr($key1['metadata']['user_profile']['bio'], 0, 30)."...";
                        @endphp
                        @else
                        @php
                        $biography = $key1['metadata']['user_profile']['bio']
                        @endphp
                        @endif
                        @else
                        @php
                          $full_bio = "";
                          $biography = "";
                        @endphp
                        @endif
                        @if(isset($key1['user']['contact']))
                        @if(isset($key1['user']['contact']['linkedin_url']))
                        @php
                          $linkedin = $key1['user']['contact']['linkedin_url'];
                        @endphp
                        @else
                        @php
                          $linkedin = "";
                        @endphp
                        @endif
                        @if(isset($key1['user']['contact']['contact_number']))
                        @php
                          $contact = $key1['user']['contact']['contact_number'];
                        @endphp
                        @else
                        @php
                          $contact = "";
                        @endphp
                        @endif
                        @else
                        @php
                          $linkedin = "";
                          $contact = "";
                        @endphp
                        @endif
                        <?php
                           if(!empty($linkedin))
                           {
                             $parsed = parse_url($linkedin);
                             if (empty($parsed['scheme']))
                             {
                               $linkedin = 'https://' . ltrim($linkedin, '/');
                             }
                           }
                           ?>
                        <!---Modal-->
                        @endforeach
                        @if( sizeOfCustom($myArray) > 2)
                        <span href="#!" data-toggle="modal" data-target="#endoresedpopup1">
                        <span data-trigger="hover" type="button" data-toggle="popover"
                           data-placement="bottom" data-html="true"
                           data-content="<?php $usr = 1;?>
                           @foreach( $approve_user as $key)
                           @if(in_array($key['user']['id'],$myArray))
                           @if($usr <= 5)
                           {{ ucfirst($key['user']['first_name'])}} {{ ucfirst($key['user']['last_name'])}}
                           <br/>
                           @endif
                           @if($usr > 5)
                           {{'and'}} <?php echo $u = sizeOfCustom($approve_user)-5 ?> {{'others'}}
                           <?php break;?>
                           @endif
                           <?php $usr++ ?>
                           @endif
                           @endforeach
                           " id="example_popover" class="visible_tooltip visibility_setting_more" setting-id="{{$posts['id']}}" space-id="{{$spaceid}}"  endors-poup-post="">
                        <span data-toggle="modal" data-target="#visiblepopup{{ $posts['id'] }}" >
                        and {{$user_visilbe_count-2}} others
                        </span>
                        </span>
                        </span>
                        @endif
                        <?php }  ?>
                     </a>
                  </span>
                  <a href="#!" data-toggle="modal" data-target="#visiblepopup{{ $posts['id'] }}"><img src="{{ url('/',[],$ssl)}}/images/ic_settings_black.svg" class="img-responsive visibility_setting add_scroll" setting-id="{{$posts['id']}}" space-id="{{$spaceid}}" alt="" ></a>
               </div>
               <h3 class="post-subject">{{$posts['post_subject']}}</h3>
               @if(isset(json_decode($posts['metadata'],true)['get_url_data']))
               @php
               $get_url_data = json_decode($posts['metadata'],true);
               @endphp
               @php
               $full_url = $get_url_data['get_url_data']['full_url']??"#";
               @endphp
               @php
               $domain = $get_url_data['get_url_data']['domain']??"";
               $domain = $domain?'https://www.'.$domain:'';
               @endphp
               <p class="posted-description-bx" style="display:none;">
                  <?php
                     $find1=array('`((?:https?|ftp)://\S+[[:alnum:]]/?)`si','`((?<!//)(www\.\S+[[:alnum:]]/?))`si');
                     $replace1=array('<a class="post_emb_link" href="$1" target="_blank">$1</a>', '<a class="post_emb_link" href="https://$1" target="_blank">$1</a>');
                     echo nl2br(preg_replace($find1,$replace1,$posts['post_description']));
                     $post_description_after_process = nl2br(preg_replace($find1,$replace1,$posts['post_description']));
                     
                     ?>
               </p>
               <?php
                  if(strlen($post_description_after_process) < 375 )
                         { ?>
                       <p class="posted-description-bx"><?php echo $post_description_after_process;?> </p>
                        <?php } else { ?>
                         <div class="show_less{{$posts['id']}} post-desc">
                          <?php $dist = substr($post_description_after_process, 0, 375);
                               $openpos = strripos($dist, "<a");
                               $closepos = strripos($dist, "</a>");
                               if($openpos > $closepos)
                               {
                                 $dist = substr($post_description_after_process, 0, $openpos-1);
                               }
                               else
                               {
                                 $dist = substr($post_description_after_process, 0, 375);
                               } ?>
                         <p class="posted-description-bx"><?php echo $dist."...";?><span class="show_extra blue-span" top-id="{{$posts['id']}}">&nbsp;&nbsp;&nbsp;Show More</span></p>
                         </div>
                         <div class="show_more{{$posts['id']}} post-desc" style="display:none;">
                          <p class="posted-description-bx"><?php echo $post_description_after_process;?><span class="not_show blue-span" top-id="{{$posts['id']}}">&nbsp;&nbsp;&nbsp;Show Less</span></p>
                         </div>

                          <?php }
                         ?>
               <!-- Url embed start -->
               <div class="outer-block">
                  @if( !isset($get_url_data['get_url_data']['metatags']['twitter:player']) )
                  <div class="inner-block url_link_posted">
                     <div class="thumbnail-block">
                        @if( isset($get_url_data['get_url_data']['thumbnail_img']) && $get_url_data['get_url_data']['thumbnail_img'] )
                        <img src="{{ $get_url_data['get_url_data']['favicon']??"" }}" class="url-favicon thumbnail-img" onerror="this.src='{{$domain}}/favicon.ico';">
                        @else
                        <img src="{{ $get_url_data['get_url_data']['favicon']??"" }}" class="url-favicon" onerror="this.src='{{$domain}}/favicon.ico';">
                        @endif
                     </div>
                     @else
                     <div class="inner-block">
                        @endif
                        <div class="video_desc_block">
                           <!--<h5><img src="{{ 'http://'.$get_url_data['get_url_data']['favicon']??'' }}" alt="demo_01"  /> {{ $get_url_data['get_url_data']['domain']??"" }}</h5>-->
                           <a target='_blank' href="{{ $full_url }}" title="{{ $get_url_data['get_url_data']['title']??"" }}">{{ $get_url_data['get_url_data']['title']??"" }}</a>
                           <p>{{ $get_url_data['get_url_data']['description']??"" }}</p>
                           <!-- <img src="../thumb.png" class="thumb"  /> -->
                        </div>
                        @if( isset($get_url_data['get_url_data']['metatags']['twitter:player']) ) <!-- if content is video -->
                        <iframe allowfullscreen="allowfullscreen" width="420" height="480" src="{{$get_url_data['get_url_data']['metatags']['twitter:player']}}"></iframe>
                        @endif
                     </div>
                  </div>
                  <!-- Url embed end -->
                  @else
                  <p style="display:none;">
                     <?php
                        $find1=array('`((?:https?|ftp)://\S+[[:alnum:]]/?)`si','`((?<!//)(www\.\S+[[:alnum:]]/?))`si');
                        $replace1=array('<a class="post_emb_link" href="$1" target="_blank">$1</a><br>', '<a class="post_emb_link" href="https://$1" target="_blank">$1</a><br>');
                        echo nl2br(preg_replace($find1,$replace1,$posts['post_description']));
                        $post_description_after_process = nl2br(preg_replace($find1,$replace1,$posts['post_description']));
                         ?>
                  </p>
                  <?php
                  if(strlen($post_description_after_process) < 375 )
                         { ?>
                        <?php echo $post_description_after_process;?>
                        <?php } else { ?>
                          <div class="show_less{{$posts['id']}} post-desc">
                         <?php $dist = substr($post_description_after_process, 0, 375);
                               $openpos = strripos($dist, "<a");
                               $closepos = strripos($dist, "</a>");
                               if($openpos > $closepos)
                               {
                                 $dist = substr($post_description_after_process, 0, $openpos-1);
                               }
                               else
                               {
                                 $dist = substr($post_description_after_process, 0, 375);
                               }?>
                        <?php echo $dist."...";?><span class="show_extra blue-span" top-id="{{$posts['id']}}">&nbsp;&nbsp;&nbsp;Show more</span>
                         </div>
                         <div class="show_more{{$posts['id']}} post-desc" style="display:none;">
                          <?php echo $post_description_after_process;?><span class="not_show blue-span" top-id="{{$posts['id']}}">&nbsp;&nbsp;&nbsp;Show less</span>
                         </div>

                          <?php }
                         ?>
                  @endif
                  <br>
                  <div class="modal fade endrose add_scroll" id="visiblepopup{{$posts['id']}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                  </div>
                  <div class="modal fade add_scroll" id="visiblepopup_more_{{$posts['id']}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                  </div> </div>
                  @if ( sizeOfCustom($posts['post_media'])>0 )
                  <div class="attachment-wrap">
                  <div style="width: 100%; float: left; color: rgb(0, 0, 0); margin-bottom: 10px; cursor: pointer; font-weight: 600; margin-top: 17px;" >
                     <span class="left">Attachments</span>
                  </div>
                  @endif
                  <?php
                     // echo "<pre>"; print_r($posts); exit;
                       if(!empty($posts['post_media']))
                       { ?>
                  <?php
                     foreach ($posts['post_media'] as $postmed)
                     {
                     
                       $filetype = $postmed['post_file_url'];
                       $postmed['post_file_url'] = str_replace("https", "http", $postmed['post_file_url']);
                       $ext = pathinfo($filetype, PATHINFO_EXTENSION);
                       $ext = strtolower($ext);
                       ?>
                  <input type="hidden" class="view_file_spaceid" value="{{$data->id}}">
                  @if ($ext=='png' || $ext=='gif' || $ext=='jpg' || $ext=='jpeg')
                  <?php $url = $postmed['post_file_url'];
                     $path = parse_url($url, PHP_URL_PATH);
                     $file_name = explode('.', $postmed['metadata']['originalName']);
                     array_pop($file_name);
                     $file_name = implode('.', $file_name);
                     ?>
                  <!-- <img src="{{ $posts['post_file_url'] }}" class="img-responsive body-img" alt=""> -->
                  <a data-toggle="modal" data-target="#myModal{{ $postmed['id'] }}" class="findmedia">
                     <input type='hidden' name="url_src" value="{{ $postmed['post_file_url'] }}">
                     <div>
                        @if ( sizeOfCustom($posts['post_media'])>1 )
                        <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top:  10px;cursor:pointer;" >
                           <img style="margin-right: 20px;" src="{{ url('/',[],$ssl) }}/images/ic_IMAGE.svg" viewfile="{{ $postmed['post_id'] }}" id="view_file" media-id="{{$postmed['id']}}"><font><?php echo $file_name; ?></font>
                        </div>
                        @else
                        <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top:  10px; cursor:pointer;" >
                           <img src="{{ $post_controller->getAwsValidUrl( $postmed['post_file_url'] ) }}" viewfile="{{ $postmed['post_id'] }}" id="view_file" class="img-responsive" style="height: auto;" media-id="{{$postmed['id']}}">
                        </div>
                        @endif
                     </div>
                  </a>
                  <div id="myModal{{ $postmed['id'] }}" class="modal fade">
                     <div class="modal-dialog ">
                        <div class="modal-content">
                           <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/',[],$ssl) }}/images/ic_highlight_removegray.svg" alt=""></button>
                              <a href = "{{ url('/',[],$ssl).'/get_media/'.$postmed['id'] }}" class="for_download pull-right"><i class="fa fa-download" aria-hidden="true"></i></a>
                              <a href="#!" class="pull-right full_screen_toggle"><i class="fa fa-expand" aria-hidden="true"></i></a>
                              <h4 class="modal-title">{{ $file_name }}</h4>
                           </div>
                           <div class="modal-body">
                              <img src="{{ $post_controller->getAwsValidUrl( $postmed['post_file_url'] ) }}" onload=" $('.modal-loader').hide(); ">
                           </div>
                        </div>
                        <div class="modal-loader" style="background: #ffffff url('{{ url('/',[],$ssl) }}/images/loading_bar1.gif') no-repeat center center;"></div>
                     </div>
                  </div>
                  @endif
                  @if ($ext=='mp4' || $ext=='mp3' || $ext=='avi' || $ext=='mkv' || $ext=='3gp')
                  
                  <?php $url = $postmed['post_file_url'];
                     $path = parse_url($url, PHP_URL_PATH);
                     $file_name = explode('.', $postmed['metadata']['originalName']);
                     array_pop($file_name);
                     $file_name = ucfirst(implode('', $file_name));
                     ?>
                  <a data-toggle="modal" data-target="#myModal{{ $postmed['id'] }}" class="findmedia">
                     <input type='hidden' name="url_src" value="{{ $postmed['post_file_url'] }}">
                     <input type='hidden' name="file_orignal_name" value="{{ $postmed['metadata']['originalName'] }}">
                     <div>
                        @if ( sizeOfCustom($posts['post_media'])>1 )
                        <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top: 10px; cursor:pointer;">
                           <img id="view_file" style="margin-right: 20px;" src="{{ url('/',[],$ssl) }}/images/ic_VIDEO.svg" ><font><?php echo $file_name; ?></font>
                        </div>
                        @else
                        <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top: 10px;cursor:pointer;">
                           <video controls poster="{{ url('/',[],$ssl) }}/images/video-poster.jpg" class="bkg"  width="100%" style="min-width:200px; min-height:100px;" mimeType="video/mp4" viewfile="{{ $postmed['post_id'] }}" id="view_file1" media-id="{{$postmed['id']}}">
                              <source src="{{ $post_controller->getAwsValidUrl( $postmed['post_file_url'] ) }}" type="video/mp4">
                              Your browser does not support HTML5 video.
                           </video>
                        </div>
                        @endif
                     </div>
                  </a>
                  <div id="myModal{{ $postmed['id'] }}" class="modal fade">
                     <div class="modal-dialog ">
                        <div class="modal-content">
                           <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/',[],$ssl) }}/images/ic_highlight_removegray.svg" alt=""></button>
                              <a href = "{{ url('/',[],$ssl).'/get_media/'.$postmed['id'] }}" class="for_download pull-right"><i class="fa fa-download" aria-hidden="true"></i></a>
                              <a href="#!" class="pull-right full_screen_toggle"><i class="fa fa-expand" aria-hidden="true"></i></a>
                              <h4 class="modal-title">{{ $file_name }}</h4>
                           </div>
                           <div class="modal-body">
                              <video width="400" controls poster="{{ url('/',[],$ssl) }}/images/video-poster.jpg" mimeType="video/mp4" style="min-width:200px; min-height:100px;">
                                 <source src="{{ $post_controller->getAwsValidUrl( $postmed['post_file_url'] ) }}" type="video/mp4">
                                 Your browser does not support HTML5 video.
                              </video>
                           </div>
                        </div>
                        <div class="modal-loader" style="background: #ffffff url('{{ url('/',[],$ssl) }}/images/loading_bar1.gif') no-repeat center center;"></div>
                     </div>
                  </div>
                  @endif
                  @if ($ext=='doc' || $ext=='docs' || $ext=='docx')
                  <?php $url = $postmed['post_file_url'];
                     $path = parse_url($url, PHP_URL_PATH);
                     $file_name = explode('.', $postmed['metadata']['originalName']);
                     array_pop($file_name);
                     $file_name = ucfirst(implode('', $file_name));
                     ?>
                  @if( $postmed['metadata']['size'] != '' && ($postmed['metadata']['size']/1024)/1024 > 9 ) <!-- Check file size start -->
                    <a href = "{{ url('/',[],$ssl).'/get_media/'.$postmed['id'] }}" class="findmedia">
                       <input type='hidden' name="url_src" value="{{ $postmed['post_file_url'] }}">
                       <div>
                          <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top:  10px; cursor:pointer;">
                            <img style="margin-right: 20px;" src="{{ url('/',[],$ssl) }}/images/ic_WORD.svg"  viewfile="{{ $postmed['post_id'] }}" id="view_file" media-id="{{$postmed['id']}}"><font><?php echo $file_name; ?></font>
                          </div>
                       </div>
                    </a>
                  @else
                  <a data-toggle="modal" data-target="#docModal{{ $postmed['id'] }}" class="findmedia">
                     <input type='hidden' name="url_src" value="{{ $postmed['post_file_url'] }}">
                     <div>
                        <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top:  10px; cursor:pointer;">
                           <img style="margin-right: 20px;" src="{{ url('/',[],$ssl) }}/images/ic_WORD.svg"  viewfile="{{ $postmed['post_id'] }}" id="view_file" media-id="{{$postmed['id']}}"><font><?php echo $file_name; ?></font>
                         </div>
                     </div>
                  </a>
                  <div class="modal fade" id="docModal{{ $postmed['id'] }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                     <div class="modal-dialog">
                        <div class="modal-content">
                           <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/',[],$ssl) }}/images/ic_highlight_removegray.svg" alt=""></button>
                              <a href = "{{ url('/',[],$ssl).'/get_media/'.$postmed['id'] }}" class="for_download pull-right"><i class="fa fa-download" aria-hidden="true"></i></a>
                              <a href="#!" class="pull-right full_screen_toggle"><i class="fa fa-expand" aria-hidden="true"></i></a>
                              <h4 class="modal-title" id="myModalLabel">{{$file_name}}</h4>
                           </div>
                           <div class="modal-body">
                              <div style="text-align: center;">
                                 <iframe src="" style="width:500px; height:500px;" frameborder="0"></iframe>
                              </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                           </div>
                        </div>
                        <div class="modal-loader" style="background: #ffffff url('{{ url('/',[],$ssl) }}/images/loading_bar1.gif') no-repeat center center;"></div>
                     </div>
                  </div>
                  @endif <!-- Check file size end -->

                  @endif
                  @if ($ext=='pdf')
                  <?php $url = $postmed['post_file_url'];
                     $path = parse_url($url, PHP_URL_PATH);
                     $file_name = explode('.', $postmed['metadata']['originalName']);
                     array_pop($file_name);
                     $file_name = ucfirst(implode('', $file_name));
                     ?>

                  
                  <a data-toggle="modal" data-target="#pdfModal{{ $postmed['id'] }}" class="findmedia">
                     <input type='hidden' name="url_src" value="{{ $postmed['post_file_url'] }}">
                     <div>
                        <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top:  10px; cursor:pointer;">
                           <img style="margin-right: 20px;" src="{{ url('/',[],$ssl) }}/images/ic_PDF.svg" viewfile="{{ $postmed['post_id'] }}" id="view_file" media-id="{{$postmed['id']}}"><font>{{$file_name}}</font><!--<a style="margin-right: 20px;" class="right" href="{{ url('/',[],$ssl) }}/delete_postmedia/{{ $postmed['id'] }}">
                              <img src='{{ url('/',[],$ssl) }}/images/ic_highlight_remove.svg'>
                              </a>-->
                        </div>
                     </div>
                  </a>
                  <div class="modal fade" id="pdfModal{{ $postmed['id'] }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                     <div class="modal-dialog ">
                        <div class="modal-content">
                           <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/',[],$ssl) }}/images/ic_highlight_removegray.svg" alt=""></button>
                              <a href = "{{ url('/',[],$ssl).'/get_media/'.$postmed['id'] }}" class="for_download pull-right"><i class="fa fa-download" aria-hidden="true"></i></a>
                              <a href="#!" class="pull-right full_screen_toggle"><i class="fa fa-expand" aria-hidden="true"></i></a>
                              <h4 class="modal-title" id="myModalLabel">{{$file_name}}</h4>
                           </div>
                           <div class="modal-body">
                              <div style="text-align: center;">
                                 <iframe src="" style="width:500px; height:500px;" frameborder="0"></iframe>
                              </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                           </div>
                        </div>
                        <div class="modal-loader" style="background: #ffffff url('{{ url('/',[],$ssl) }}/images/loading_bar1.gif') no-repeat center center;"></div>
                     </div>
                  </div>
                  <!--  <iframe src="http://docs.google.com/gview?url={{ $posts['post_file_url'] }}&embedded=true"
                     style="width:500px; height:200px;" frameborder="0"></iframe> -->
                  @endif
                  @if ($ext=='xlsx' || $ext=='xls' || $ext=='csv')
                  <?php $url = $postmed['post_file_url'];
                     $path = parse_url($url, PHP_URL_PATH);
                     $file_name = explode('.', $postmed['metadata']['originalName']);
                     array_pop($file_name);
                     $file_name = ucfirst(implode('', $file_name));
                     ?>
                  <a href = "{{ url('/',[],$ssl).'/get_media/'.$postmed['id'] }}" class="findmedia">
                     <input type='hidden' name="url_src" value="{{ $postmed['post_file_url'] }}">
                     <div>
                        <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top:  10px; cursor:pointer;">
                           <img style="margin-right: 20px;" src="{{ url('/',[],$ssl) }}/images/ic_EXCELsmall.svg"  viewfile="{{ $postmed['post_id'] }}" id="view_file" media-id="{{$postmed['id']}}"><font><?php echo $file_name; ?></font>                        </div>
                     </div>
                  </a>
                  @endif
                  @if ($ext=='ppt' || $ext=='pptx')
                  <?php $url = $postmed['post_file_url'];
                     $path = parse_url($url, PHP_URL_PATH);
                     $file_name = explode('.', $postmed['metadata']['originalName']);
                     array_pop($file_name);
                     $file_name = ucfirst(implode('', $file_name));
                     ?>
                  @if( $postmed['metadata']['size'] != '' && ($postmed['metadata']['size']/1024)/1024 > 9 ) <!-- Check file size start -->
                    <a href = "{{ url('/',[],$ssl).'/get_media/'.$postmed['id'] }}" class="findmedia">
                       <input type='hidden' name="url_src" value="{{ $postmed['post_file_url'] }}">
                       <div>
                          <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top:  10px; cursor:pointer;">
                            <img style="margin-right: 20px;" src="{{ url('/',[],$ssl) }}/images/ic_PWERPOINT.svg"  viewfile="{{ $postmed['post_id'] }}" id="view_file" media-id="{{$postmed['id']}}"><font><?php echo $file_name; ?></font>
                          </div>
                       </div>
                    </a>
                  @else
                  <a data-toggle="modal" data-target="#pptModal{{ $postmed['id'] }}" class="findmedia">
                     <input type='hidden' name="url_src" value="{{ $postmed['post_file_url'] }}">
                     <div>
                        <div style="width: 100%;float: left;color: #212121;margin-bottom: 10px; margin-top: 10px; cursor:pointer;">
                           <img style="margin-right: 20px;" src="{{ url('/',[],$ssl) }}/images/ic_PWERPOINT.svg"  viewfile="{{ $postmed['post_id'] }}" id="view_file" media-id="{{$postmed['id']}}"><font><?php echo $file_name; ?></font>
                         </div>
                     </div>
                  </a>                  
                  <div class="modal fade" id="pptModal{{ $postmed['id'] }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                     <div class="modal-dialog ">
                        <div class="modal-content">
                           <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/',[],$ssl) }}/images/ic_highlight_removegray.svg" alt=""></button>
                              <a href = "{{ url('/',[],$ssl).'/get_media/'.$postmed['id'] }}" class="for_download pull-right"><i class="fa fa-download" aria-hidden="true"></i></a>
                              <a href="#!" class="pull-right full_screen_toggle"><i class="fa fa-expand" aria-hidden="true"></i></a>
                              <h4 class="modal-title" id="myModalLabel"><?php echo $file_name; ?></h4>
                           </div>
                           <div class="modal-body ">
                              <div style="text-align: center;">
                                 <iframe src=""
                                    style="width:500px; height:500px;" frameborder="0"></iframe>
                              </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                           </div>
                        </div>
                        <div class="modal-loader" style="background: #ffffff url('{{ url('/',[],$ssl) }}/images/loading_bar1.gif') no-repeat center center;"></div>
                     </div>
                  </div>
                  @endif <!-- Check file size end -->
                  @endif
                  <?php
                     } ?>
                   </div>  
                     <?php }
                     
                     ?>
                  <textarea style="display:none" id="edit_post_id_{{$posts['id']}}" olddata="{{ $posts['post_description'] }}"> {{ $posts['post_description'] }} </textarea>
               </div>
              
               @php ($endorsed_by_me_img = 'false')
               @php ($post_endorse1 = $posts['endorse'])
               @if(!empty($post_endorse1))
               @foreach($post_endorse1 as $endorse1)
               @if($endorse1['user_id']==Auth::user()->id)
               @php ($endorsed_by_me_img = 'true')
               @endif
               @endforeach
               @endif
               <div class="bottom-section left">
                  
                  @if($endorsed_by_me_img=='true')
                  <a href="javascript:void(0)" class="endrose disable addendorse up_{{$posts['id']}}" add-endorse-id="{{$posts['id']}}" add-endorse-userid="{{ Auth::user()->id }}" img-attr="up" url="{{ url('/',[],$ssl) }}" space-id="{{$spaceid}}">
                  <img src="{{ url('/',[],$ssl) }}/images/ic_thumb_up.svg" alt="" class=""  style="display:inline-block;">
                  <span class="endrose-text{{$posts['id']}}"></span>
                  </a>
                  @endif
                  @if($endorsed_by_me_img=='false')
                  <a href="javascript:void(0)" class="endrose disable addendorse down_{{$posts['id']}}" add-endorse-id="{{$posts['id']}}" add-endorse-userid="{{ Auth::user()->id }}" img-attr="down" url="{{ url('/',[],$ssl) }}" space-id="{{$spaceid}}">
                  <img src="{{ url('/',[],$ssl) }}/images/ic_thumb_up_grey.svg" alt="" class="grey" >
                  <span class="endrose-text{{$posts['id']}}"></span>
                  </a>
                  @endif
                  <!--ENDORSE POST START-->
            <div class="endorsediv_{{$posts['id']}} endrose-wrap">
               <div class="@if(!empty($posts['endorse']))endrose @endif">
                  @php ($endorse_array=array() )
                  @php ($endorsed_by_me = 'false')
                  @php ($post_endorse = $posts['endorse'])
                  @if(!empty($post_endorse))
                  <?php $data1 = array_reverse($post_endorse);
                     ?>
                  @foreach($post_endorse as $endorse)
                  @php ($endorse_array[] = $endorse)
                  @if($endorse['user_id']==Auth::user()->id)
                  @php ($endorsed_by_me = 'true')
                  @endif
                  @endforeach
                  <span>
                     @if($endorsed_by_me=='true')
                     You @if(sizeOfCustom($post_endorse)>1)
                     @if(sizeOfCustom($post_endorse)==2)
                     <span>&</span>
                     @else
                     ,
                     @endif @endif
                     @if(sizeOfCustom($post_endorse)>=2)
                     <!-- skip current usr name-->
                     @php ($f1=1)
                     @foreach($data1 as $re)
                     @if($re['user']['id']!=Auth::user()->id)
                     @if($f1==1)
                     <?php if($re['space_user']['user_status'] == 1) { ?>
                        {{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}
                        <?php } else { ?>
                            <a href="#" data-toggle="modal" data-target="#myEndModal-{{$re['user_id']}}" data-id="{{$re['user']['id']}}" onclick="liked_info(this);">{{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}</a>
                           <?php } ?>
                     
                     @php ($f1++)
                     @endif
                     @endif
                     
                     @endforeach
                     @endif
                     @endif
                     @if($endorsed_by_me=='false')
                     @if(sizeOfCustom($post_endorse)>=2)
                     @php ($f2=1)
                     @foreach($data1 as $re)
                     @if($re['user']['id']!=Auth::user()->id)
                     <!--  <pre>{{ print_r($re) }}<pre/> -->
                     @if($f2 <= 2)
                      <?php if($re['space_user']['user_status'] == 1) { ?>
                        {{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}
                        <?php } else { ?>
                            <a href="#" data-toggle="modal" data-target="#myEndModal-{{$re['user_id']}}" data-id="{{$re['user']['id']}}" onclick="liked_info(this);">{{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}</a>
                           <?php } ?>
                     @if($f2==1)
                     @if(sizeOfCustom($post_endorse)==2)
                     <span>&</span>
                     @else
                     ,
                     @endif
                     @endif
                     @php ($f2++)
                     @endif
                     @endif
                     
                     @endforeach
                     @else
                     @php ($f3=1)
                     @foreach($data1 as $re)
                     @if($re['user']['id']!=Auth::user()->id)
                     @if($f3==1)
                      <?php if($re['space_user']['user_status'] == 1) { ?>
                        {{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}
                        <?php } else { ?>
                            <a href="#" data-toggle="modal" data-target="#myEndModal-{{$re['user_id']}}" data-id="{{$re['user']['id']}}" onclick="liked_info(this);">{{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}</a>
                           <?php } ?>
                     @php ($f3++)
                     @endif
                     @endif
                     
                     @endforeach
                     @endif
                     @endif
                  </span>
                  <?php
                     usort($endorse_array, function($a, $b) {
                     
                     $a1 = $a['user']['first_name']; //get the name string value
                     $b1 = $b['user']['first_name'];
                     
                     $out = strcasecmp($a1,$b1);
                     if($out == 0){ return 0;} //they are the same string, return 0
                     if($out > 0){ return 1;} // $a1 is lower in the alphabet, return 1
                     if($out < 0){ return -1;} //$a1 is higher in the alphabet, return -1
                     }); ?>
                  @if(sizeOfCustom($post_endorse)>2)
                  and <a href="#!" data-toggle="modal" data-target="#endoresedpopup"><span data-trigger="hover" type="button" data-toggle="popover"
                     data-placement="bottom" data-html="true" <?php $liked_user = 1 ?>
                     data-content="@foreach($endorse_array as $endorse_data)
                     @if($liked_user <= 5)
                     {{ ucfirst($endorse_data['user']['first_name']) }} {{ ucfirst($endorse_data['user']['last_name']) }}
                     <br/>
                     @endif
                     @if($liked_user > 5)
                     {{'and'}} <?php echo $u = sizeOfCustom($endorse_array)-5 ?> {{'others'}}
                     <?php break;?>
                     @endif
                     <?php $liked_user++ ?>
                     @endforeach" id="example_popover" class="endorsed_popup" endors-poup-post="{{$posts['id']}}" space-id="{{$spaceid}}" >{{sizeOfCustom($post_endorse)-2}}  others</span></a>
                  @endif liked this
                  @endif
               </div>
            </div>
            <!--ENDORSE POST END-->
                  <!--<a href="#!" data-toggle="modal" data-target="#visiblepopup">-->
                  <button type="button" class="get_view_user btn right viewpostsuser{{$posts['id']}}" data-toggle="popover" data-trigger="hover" data-placement="bottom" title="Who has viewed this post" data-html="true"
                     data-content="">@if(!empty($posts['post_media']))
                  <img src="{{ url('/',[],$ssl) }}/images/ic_visibility.svg" data-html="true"
                     alt="" id="" getViewId="{{$posts['post_media'][0]['post_id']}}" >@endif
                  </button>
                  <!--</a>-->
               </div>
               <!-- bottom-section -->
               <!-- bottom-section -->
            </div>
            <!-- top section -->
         </div>
         <div class="comment-wrap box">
            
            <!-- post comments start-->
            <div class="user-comments comments{{$posts['id']}}">
               @if(!empty($posts['comments']))
               <?php $post_comments=$posts['comments']; ?>
               @if(sizeOfCustom($post_comments)>'2')
               <a class="viewmore-comm view-more-comments" spaceid="{{$spaceid}}" datapostid="{{$posts['id']}}" datauserid="{{Auth::user()->id}}" commentlimit="">View {{ sizeOfCustom($post_comments)-2 }} more comments</a>
               <a class="viewmore-comm view-less-comments hidden" spaceid="{{$spaceid}}" datapostid="{{$posts['id']}}" datauserid="{{Auth::user()->id}}" commentlimit="">View fewer comments</a>
               @endif
               @endif
               @if(!empty($posts['comments']))
               <?php $comment_show = '0';
                  if(sizeOfCustom($post_comments)>2) {
                    $show_last_tree_comments = sizeOfCustom($post_comments)-2;
                  }else{ $show_last_tree_comments=0;}
                  ?>
               @foreach(array_slice($post_comments,$show_last_tree_comments) as $data_comment)
               @if($comment_show < 2)
               <div class="member-wrap single_comment" id="comment_wrap_{{ $data_comment['user']['id'] }}">
                  @if(!empty($data_comment['user']['profile_image_url']))
                   <?php if($data_comment['spaceuser'][0]['user_status']==1) { ?>
                      <span class="pro_pic_wrap dp" style="background-image: url('{{ $data_comment['user']['profile_image_url'] }}');" ></span>
                     <?php } else { ?>
                     <a href="#!"  onclick="liked_info(this);"  data-id="{{$data_comment['user']['id']}}" class="title @if($data_comment['spaceuser'][0]['user_status'] == 1 ) inactive_name @endif  "><span class="pro_pic_wrap dp" style="background-image: url('{{ $data_comment['user']['profile_image_url'] }}');" ></span></a>
                     <?php } ?>
                  
                  @endif
                  @if(empty($data_comment['user']['profile_image_url']))
                   <?php if($data_comment['spaceuser'][0]['user_status']==1) { ?>
                     <span class="pro_pic_wrap dp" style="background-image: url('{{ url('/',[],$ssl)}}/images/dummy-avatar-img.svg');" ></span>
                     <?php } else { ?>
                     <a href="#!"  onclick="liked_info(this);"  data-id="{{$data_comment['user']['id']}}" class="title @if($data_comment['spaceuser'][0]['user_status'] == 1 ) inactive_name @endif  "><span class="pro_pic_wrap dp" style="background-image: url('{{ url('/',[],$ssl)}}/images/dummy-avatar-img.svg');" ></span></a>
                     <?php } ?>
                  
                  @endif
                  <div class="name-wrap single-cmt-wrap user-comment-detail" id="cmt_inr_wrap_{{$data_comment['id']}}">
                   <?php if($data_comment['spaceuser'][0]['user_status']==1) { ?>
                      {{ ucfirst($data_comment['user']['first_name']) }} {{ ucfirst($data_comment['user']['last_name']) }} @if($data_comment['spaceuser'][0]['user_status'] == 1 ) (Inactive) @endif
                     <?php } else { ?>
                     <a href="#!"  onclick="liked_info(this);"  data-id="{{$data_comment['user']['id']}}" class="title @if($data_comment['spaceuser'][0]['user_status'] == 1 ) inactive_name @endif  ">  {{ ucfirst($data_comment['user']['first_name']) }} {{ ucfirst($data_comment['user']['last_name']) }} @if($data_comment['spaceuser'][0]['user_status'] == 1 ) (Inactive) @endif </a>
                     <?php } 
                     $find_link = array('`((?:https?|ftp)://\S+[[:alnum:]]/?)`si','`((?<!//)(www\.\S+[[:alnum:]]/?))`si');
                     $replace_link = array('<a href="$1" target="_blank">$1</a>','<a href="http://$1" target="_blank">$1</a>');
    
                     $data_comment['comment'] = preg_replace($find_link,$replace_link, ($data_comment['comment']));?>
                     <span style="display:none;">
                        {!! nl2br($data_comment['comment']) !!}
                     </span>
                     <?php
                      if(strlen($data_comment['comment']) < 180 )
                         { ?>
                         <div class="show_less_comment{{$data_comment['id']}} post-desc @if($data_comment['spaceuser'][0]['user_status'] == 1 ) user-inactive-comment @endif"><?php echo nl2br($data_comment['comment']);?></div>
                      <?php } else { ?>
                        <div class="show_less_comment{{$data_comment['id']}} post-desc @if($data_comment['spaceuser'][0]['user_status'] == 1 ) user-inactive-comment @endif">
                           <?php echo nl2br(substr($data_comment['comment'], 0, 180)."...");?></a><span class="show_extra_comment blue-span" top-id="{{$data_comment['id']}}">&nbsp;&nbsp;&nbsp;Show more</span>
                        </div>
                        <div class="show_more_comment{{$data_comment['id']}} post-desc @if($data_comment['spaceuser'][0]['user_status'] == 1 ) user-inactive-comment @endif" style="display:none;"><?php echo nl2br($data_comment['comment']);?><span class="not_show_comment blue-span" top-id="{{$data_comment['id']}}">&nbsp;&nbsp;&nbsp;Show less</span>
                         </div>
                     <?php }
                          ?>
                     <span class="time">
                        <!-- September 19, 15:58 -->
                        {{date('F d, H:i', strtotime($data_comment['created_at'])) }}
                        @if($data_comment['created_at']!=$data_comment['updated_at'])
                        <span class="comment_edited">(edited)</span>
                        @endif
                     </span>
                  </div>
                  @if(Session::get('space_info')->toArray()['space_user'][0]['user_type_id']=='2' || Auth::user()->id == $data_comment['user_id'])
                  <div class="dropdown hover-dropdown white-background edit-comment">
                     <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                     <span></span>
                     </a>
                     <ul class="dropdown-menu @if(Auth::user()->id != $data_comment['user_id']) del_comment @endif">
                        @if(Auth::user()->id == $data_comment['user_id'])
                        <li class="domain_inp_edit">
                           <a href="javascript:void(0)" id="{{$posts['id']}}" commentid="{{ $data_comment['id'] }}" spaceid="{{$spaceid}}" onclick="return edit_comment('{{ $data_comment['id'] }}', this);">Edit comment</a>
                        </li>
                        @endif
                        <li class="domain_inp_delete">
                           <a href="javascript:void(0)" id="{{$posts['id']}}" commentid="{{ $data_comment['id'] }}" spaceid="{{$spaceid}}" onclick="return delete_comment('{{$posts['id']}}', '{{$data_comment['id']}}', '{{$spaceid}}');">Delete comment</a>
                        </li>
                     </ul>
                  </div>
                  @endif
               </div>
               @endif
               <?php $comment_show ++;    ?>
               @endforeach
               @endif
               <!-- post comments end-->
            </div>
            <span class="pro_pic_wrap dp" style="background-image: url('{{ $profile_img }}');" ></span>
            <div class="form-group dp-input comment-add-section" data-postid="{{$posts['id']}}">
               <!-- <input type="text" class="form-control no-border" id="comment_input_area{{$posts['id']}}" placeholder="Write a comment..."> -->
               <textarea class="form-control no-border comment-area" id="comment_input_area{{$posts['id']}}" placeholder="Write a comment..." areaid="{{$posts['id']}}" style="white-space: pre-line;" wrap="hard"></textarea>
               <div class="comment-attach-col" style="display:none;">
                <input type="submit" value="File Attachment" class="comment_attachment comment_attachment_trigger" data-spaceid="{{$spaceid}}" data-postid="{{$posts['id']}}" data-userid="{{Auth::user()->id}}" style="float:right;">
              </div>
               <input id="comment_btn_{{$posts['id']}}" type="submit" value="Send" name="sendmessage" class="send_comment invite-btn" spaceid="{{$spaceid}}" datapostid="{{$posts['id']}}" datauserid="{{Auth::user()->id}}" style="float:right; display:none">
               <div class="attachment-box-row full-width" style="display:none">
                <div class="feed-post-attachment-box"></div>
               </div>
               <div class="comment_attachment_progress full-width {{$posts['id']}}"></div>
            </div>
         </div>
         <!-- comment-wrap -->
         <!-- comment-wrap -->
         <!-- comment-wrap -->
      </div>
      <!--jscode -->
      <div class="post-wrap" id="post_edit_{{$posts['id'] }}">
      </div>
      <!-- -->
      <?php endforeach;
         $exact_post_count = $vis_count-1;
         ?>
         <script type="text/javascript">
         $('.show_extra').click(function(){
         var id = $(this).attr("top-id");
      $('.show_less'+id).hide();
      $('.show_more'+id).show();
     });
     $('.not_show').click(function(){
      var id = $(this).attr("top-id");
      $('.show_less'+id).show();
      $('.show_more'+id).hide();
     });
     $('.show_extra_comment').click(function(){
         var id = $(this).attr("top-id");
      $('.show_less_comment'+id).hide();
      $('.show_more_comment'+id).show();
     });
     $('.not_show_comment').click(function(){
      var id = $(this).attr("top-id");
      $('.show_less_comment'+id).show();
      $('.show_more_comment'+id).hide();
     });
         </script>
         