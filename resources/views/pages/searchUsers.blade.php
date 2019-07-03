	<?php
	   $ssl = false;
	   if(env('APP_ENV')!='local')
	   $ssl = true;
   	?>
	<li class="header">Search results</li>
	
	@if(sizeOfCustom($result) > 0 )	 	
		@foreach($result as $res )
			@if(isset($res->post_subject))
			<?php 			
			
				if(!empty($res->data)){
					$data = json_decode($res->data);
					$filename = $data->originalName;				
				}else{
					$filename = '';
					
				}

			?>
				@if(strpos(strtolower($res->post_description),strtolower($keywords)) > -1)
					<?php 
						$position = strpos(strtolower($res->post_description),strtolower($keywords));
						if($position > 10){
						$position = $position - 10;	
						$replaceVal = substr($res->post_description,$position,40); 
						}else{
							$replaceVal = substr($res->post_description,0,40); 
						}
					?>
				@elseif($filename != '' && strpos(strtolower($filename),strtolower($keywords)) > -1)		
					<?php $replaceVal = $filename; ?>
				@else	
					<?php $replaceVal = substr($res->post_description,0,40); ?>
				@endif	
				
			  <li class="">
			       <a href="{{url('clientshare',array($res->space_id,$res->id))}}">
						
			       		@if(!empty($res->userprofileimage))					       		    				
			       			<?php	$userProfile = wrapUrl(composeUrl($res->userprofileimage)); ?>
			       		@else
			       			<?php	$userProfile = url('/',[],$ssl)."/images/dummy-avatar-img.svg"; ?>
			       		@endif
			       		<span class="dp pro_pic_wrap" style="background: url('{{$userProfile}}');"></span>
			           <span class="notify-detail"><strong><?php 
			           		if(strpos(strtolower($res->post_subject),strtolower($keywords)) > -1){
					           	$pos = strpos(strtolower($res->post_subject),strtolower($keywords));
					           	if($pos > 10){
								$pos = $pos - 10;
								echo substr($res->post_subject,$pos,20);	
					           	}else{
					           	echo substr($res->post_subject,0,20);
					           		
					           	} 
				           }else{	 
				           		echo substr($res->post_subject,0,20); 
				           } 
			          ?></strong>
			           </br>{{$replaceVal}}</span>
			        </a>
			   </li>
			@else  
				 
				<?php if(!empty($res->profile_image_url)){
		               $imageUrl = filePathJsonToUrl($res->profile_image_url); 
		            }else{   
		                $imageUrl = url('/',[],$ssl)."/images/dummy-avatar-img.svg"; 
		            }
		          ?>
		        
				<li class="">
			       <a href="#!" data-toggle="modal" data-target="#myCommuModal{{$res->user_id}}"  onclick="adddiv()">
		              
		               <span class="dp pro_pic_wrap" style="background: url('{{$imageUrl}}');"></span>
		          
		               <span class="notify-detail"><strong>{{ucfirst($res->first_name)}} {{ucfirst($res->last_name)}}</strong></br>{{$res->email}}</span>
			        </a>
			  	</li>					
				 <div class="modal fade community-member-detail" id="myCommuModal{{$res->user_id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	                  <div class="modal-dialog" role="document">
	                     <div class="modal-content">
	                        <div class="modal-header">
	                           <button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ url('/',[],$ssl) }}/images/ic_highlight_removegray.svg" alt="" /></button>
	                        </div>
	                        <div class="modal-body">
	                           <div class="modal_image_section">
	                              @if(!isset($res->profile_image_url))
	                              <span style="background-image:url('{{ url('/',[],$ssl) }}/images/default-user-image.png')">
	                              </span>
	                              @else
	                              <span style="background-image:url('{{$res->profile_image_url}}')">
	                              </span>
	                              @endif
	                           </div>
	                           <div class="modal_content_section community_member_info">
	                              <div class="member_info">
	                                 <h4>{{ucfirst($res->first_name)}}@if(isset($res->last_name)) {{ucfirst($res->last_name)}} @endif</h4>
	                                 <h5>@if(!empty($res->job_title)) {{ucfirst($res->job_title)}}@endif</h5>
	                                 <p>@if(!empty($res->bio)) {{ucfirst($res->bio)}}@endif</p>
	                                 <div class="contact-info">
	                                    <h6>Contact information</h6>
	                                    @if(!empty($res->linkedin))
	                                    <span class="linkedin-link"><a target="_blank" href="{{$res->linkedin}}">{{$res->linkedin}}</a></span>
	                                    @endif
	                                    <span class="email-link"><a href="mailto:{{$res->email}}">{{$res->email}}</a></span>
	                                    @if(!empty($res->contactNumber))
	                                    <span class="call-link">{{$res->contactNumber}}</span>
	                                    @endif
	                                 </div>
	                              </div>
	                           </div>
	                        </div>
	                     </div>
	                  </div>
	               </div>	              
             @endif    
				
		@endforeach  
		
		<?php $counter=  sizeOfCustom($result); ?> 
		@if($totalcount > $count)
          <a href="#" onclick="load('{{$spaceId}}','{{$userId}}','{{$counter}}',event)">Show more results</a>
		@endif	
	@else
		<li  class="header"><span class="notify-detail">No results found</span> </li>		
	@endif   

	