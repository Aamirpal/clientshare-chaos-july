<script id="post_template" type="text/x-handlebars-template">
	<div class="col-xs-12 col-lg-12 pull-right feed-col-right post-block post {{id}} post-wrap {{#if postsettings.minimize}} minimize {{/if}} {{#if pin_status}} pined {{/if}}" id="post_{{id}}">
		<div class="post-feed-section">
		<input type="hidden" name="post_id" id="{{id}}">
	   	<div class="post-show-box full-width {{#if pin_status}} top-section {{/if}}">
			<!-- metadata section -->
			<div class="post-header-col full-width">
			   <div class="post-left-col">
			      <a href="javascript:void();">
			      <span class="post-admin-dp" style="background-image: url({{#if user.profile_image_url}} {{ user.profile_image_url }} {{else}} {{ baseurl }}/images/dummy-avatar-img.svg {{/if}});"></span>
			      </a>
			      <div class="name-wrap">
			         <span class="post-admin-name"><a data-id='{{ user.id }}' onclick='liked_info(this);'>{{user.fullname}}</a></span>
			         <span class="post-admin-name time">{{#dateFormat created_at.date 'MMMM DD, HH:mm'}}{{/dateFormat}} {{#ifCond this.created_at.date '!=' this.updated_at.date}} (edited){{/ifCond}}
			         <span class="visible_tooltip show-user{{ id }} earth-wrap" data-trigger="hover" type="button" data-toggle="popover" data-placement="bottom" data-html="true" title="" data-content="{{#checkPostVisibility visibility}} Everyone {{else}} {{#getPostUsers visibility}} {{/getPostUsers}} {{/checkPostVisibility}}" data-original-title="Who can see this post?" >
                        <span class="dropdown visible-dropdown-view">
                          <span class="dropdown-toggle " type="button" data-toggle="dropdown">
                          <span class="earth {{#checkPostVisibility visibility}}  {{else}} lock {{/checkPostVisibility}} v_image{{ id }}"></span>
                          <span class="arrow"></span>
                          </span>
                          {{#checkUserIsPostOwnerOrAdmin is_logged_in_user_admin logged_user.id user_id}}
                          <ul class="visible-setting dropdown-menu" >
                             <li class="title">Who can see this?</li>
                             <li class="{{#checkPostVisibility visibility}} active {{/checkPostVisibility}} now_active_public{{ id }} ">
                                <a class="s-everyone" space-id="{{space_id}}" postid="{{id}}" visibletousers="{{visibility}}">
                                   <span class="slect-everyone"><img src="{{ baseurl }}/images/ic_public_copy.svg" alt=""> Everyone</span>
                                   <p>All members in this Client<br/>Share</p>
                                </a>
                             </li>
                             <li class="{{#checkPostVisibility visibility}} {{else}} active {{/checkPostVisibility}}  now_active_private{{ id }}">
                                <a data-toggle="modal" data-target="#visibility_setting_modal" class="visibility_setting add_scroll" setting-id="{{id}}" space-id="{{space_id}}">
                                   <span class="rstrct-user rst-user{{id}}"><img src="{{ baseurl }}/images/ic_https.svg" alt=""> Restricted</span>
                                   <span class="blue-span rest-memb{{id}}" {{#checkPostVisibility visibility}} style="display:none" {{/checkPostVisibility}} >View Restricted Members</span>
                                   <p>Limited to certain members of<br>this Client Share</p>
                                </a>
                             </li>
                          </ul>
                          {{/checkUserIsPostOwnerOrAdmin}}
                      </span>
                  </span>
                  </span>
			      </div>

			      <div class="mobile-feed-column hidden-lg hidden-sm hidden-md">
				      <div class="dropdown pull-right">
				         <a href="javascript:void();" class="dropdown-toggle edit-post-cog" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
				         <img src="{{baseurl}}/images/ic_settings-grey.svg" alt="" class="img-responsive">
				         </a>
				         <ul class="dropdown-menu edit-post-dropdown">
				         	{{#if single_post_view}}{{else}}
				            <li>
				               <a href="javascript:void(0)" class="minimize-post" attr-id="{{id}}" attr-uid="{{space_id}}">
				               <span class="dropdown-post-icon">
				               <img src="{{baseurl}}/images/ic_unfold_less.svg" alt="" class="img-responsive"></span>
				               Minimise post
				               </a>
				            </li>
				            {{/if}}
				            {{#if is_logged_in_user_admin}}
	                        {{#unless single_post_view}}
				            <li>
				               {{#if pin_status}}
					               <a href="{{ baseurl }}/pinpost/{{id}}/0/{{space_id}}" class="pin-post-disable unpin_post">
						               <span class="dropdown-post-icon">
						               <img src="{{baseurl}}/images/ic_pin.svg" alt="" class="img-responsive"></span>
						               Unpin post
					               </a>
	                           {{else}}
						            <a href="{{ baseurl }}/pinpost/{{id}}/1/{{space_id}}" class="pin-post-disable pin_post">
						               <span class="dropdown-post-icon">
						               <img src="{{baseurl}}/images/ic_pin.svg" alt="" class="img-responsive"></span>
						               Pin post
					               </a>
				               {{/if}}
				            </li>
			               	{{/unless}}
				            {{/if}}
				            {{#if is_logged_in_user_admin}}
				            <li>
				               <a href="javascript:void(0)" class="{{#if single_post_view}} edit-single-post-data {{else}} {{#if single_post_view}} edit-single-post-data {{else}} editpost_data {{/if}} {{/if}}" id="edit_post_remove" editpost="{{ id }}" postby="{{ user_id }}">
				               <span class="dropdown-post-icon">
				               <img src="{{baseurl}}/images/ic_edit_black.svg" alt="" class="img-responsive">
				               </span>
				               Edit post
				               </a>
				            </li>
				            
				            <li>
				               <a href="javascript:void(0)" class="delete_post" id="delete" data-toggle="modal" data-target="#delete_post_modal" post_id="{{id}}" postby="{{ user_id }}">
				               <span class="dropdown-post-icon">
				               <img src="{{baseurl}}/images/ic_delete_red.svg" alt="" class="img-responsive"></span>
				               Delete post
				               </a>
				            </li>
				            {{else}}
				            {{#compare user.id logged_user.id}}
				            <li>
				               <a href="javascript:void(0)" class="{{#if single_post_view}} edit-single-post-data {{else}} editpost_data {{/if}}" id="edit_post_remove" editpost="{{ id }}" postby="{{ user_id }}">
				               <span class="dropdown-post-icon">
				               <img src="{{baseurl}}/images/ic_edit_black.svg" alt="" class="img-responsive">
				               </span>
				               Edit post
				               </a>
				            </li>
				            
				            <li>
				               <a href="javascript:void(0)" class="delete_post" id="delete" data-toggle="modal" data-target="#delete_post_modal" post_id="{{id}}" postby="{{ user_id }}">
				               <span class="dropdown-post-icon">
				               <img src="{{baseurl}}/images/ic_delete_red.svg" alt="" class="img-responsive"></span>
				               Delete post
				               </a>
				            </li>
				            {{/compare}}
				            {{/if}}
				            <li><a href="{{ baseurl }}/clientshare/{{space_id}}/{{id}}" data-href="{{ baseurl }}/clientshare/{{space_id}}/{{id}}" class="copy-post-link" attr-id="{{id}}">
				               <span class="dropdown-post-icon">
				                 <img src="{{ baseurl }}/images/ic_external_copy.svg" alt="" class="img-responsive"></span>
				                 Copy post link
				               </a>
				            </li>
				            <img src="/images/ic_settings-grey.svg" alt="" class="img-responsive">
				         </ul>
				      </div>
				      </div>

			   </div>
			   <div class="post-right-col">
			      <div class="category-chip-wrap">
			         <a href="javascript:void(0)" class="chip disable">
			         <input type="hidden" value="{{meta_array.category}}" />
			         {{#indexInfo space_category meta_array.category}}{{/indexInfo}}
			         </a>

			         <div class="dropdown pull-left {{#isMobileDevice}} hidden-xs {{/isMobileDevice}}">
			         <a href="javascript:void();" class="dropdown-toggle edit-post-cog" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
			         <img src="{{baseurl}}/images/ic_settings-grey.svg" alt="" class="img-responsive">
			         </a>
			         <ul class="dropdown-menu edit-post-dropdown">
			         	{{#if single_post_view}}{{else}}
			            <li>
			               <a href="javascript:void(0)" class="minimize-post" attr-id="{{id}}" attr-uid="{{space_id}}">
			               <span class="dropdown-post-icon">
			               <img src="{{baseurl}}/images/ic_unfold_less.svg" alt="" class="img-responsive"></span>
			               Minimise post
			               </a>
			            </li>
			            {{/if}}
			            {{#if is_logged_in_user_admin}}
                        {{#unless single_post_view}}
			            <li>
			               {{#if pin_status}}
				               <a href="{{ baseurl }}/pinpost/{{id}}/0/{{space_id}}" class="pin-post-disable unpin_post">
					               <span class="dropdown-post-icon">
					               <img src="{{baseurl}}/images/ic_pin.svg" alt="" class="img-responsive"></span>
					               Unpin post
				               </a>
                           {{else}}
					            <a href="{{ baseurl }}/pinpost/{{id}}/1/{{space_id}}" class="pin-post-disable pin_post">
					               <span class="dropdown-post-icon">
					               <img src="{{baseurl}}/images/ic_pin.svg" alt="" class="img-responsive"></span>
					               Pin post
				               </a>
			               {{/if}}
			            </li>
				        {{/unless}}
			            {{/if}}
			            {{#if is_logged_in_user_admin}}
			            <li>
			               <a href="javascript:void(0)" class="{{#if single_post_view}} edit-single-post-data {{else}} editpost_data {{/if}}" id="edit_post_remove" editpost="{{ id }}" postby="{{ user_id }}">
			               <span class="dropdown-post-icon">
			               <img src="{{baseurl}}/images/ic_edit_black.svg" alt="" class="img-responsive">
			               </span>
			               Edit post
			               </a>
			            </li>
			            
			            <li>
			               <a href="javascript:void(0)" class="delete_post" id="delete" data-toggle="modal" data-target="#delete_post_modal" post_id="{{id}}" postby="{{ user_id }}">
			               <span class="dropdown-post-icon">
			               <img src="{{baseurl}}/images/ic_delete_red.svg" alt="" class="img-responsive"></span>
			               Delete post
			               </a>
			            </li>
			            {{else}}
			            {{#compare user.id logged_user.id}}
			            <li>
			               <a href="javascript:void(0)" class="{{#if single_post_view}} edit-single-post-data {{else}} editpost_data {{/if}}" id="edit_post_remove" editpost="{{ id }}" postby="{{ user_id }}">
			               <span class="dropdown-post-icon">
			               <img src="{{baseurl}}/images/ic_edit_black.svg" alt="" class="img-responsive">
			               </span>
			               Edit post
			               </a>
			            </li>
			            
			            <li>
			               <a href="javascript:void(0)" class="delete_post" id="delete" data-toggle="modal" data-target="#delete_post_modal" post_id="{{id}}" postby="{{ user_id }}">
			               <span class="dropdown-post-icon">
			               <img src="{{baseurl}}/images/ic_delete_red.svg" alt="" class="img-responsive"></span>
			               Delete post
			               </a>
			            </li>
			            {{/compare}}
			            {{/if}}
			            <li><a href="{{ baseurl }}/clientshare/{{space_id}}/{{id}}" data-href="{{ baseurl }}/clientshare/{{space_id}}/{{id}}" class="copy-post-link" attr-id="{{id}}">
			               <span class="dropdown-post-icon">
			                 <img src="{{ baseurl }}/images/ic_external_copy.svg" alt="" class="img-responsive"></span>
			                 Copy post link
			               </a>
			            </li>
			            <img src="/images/ic_settings-grey.svg" alt="" class="img-responsive">
			         </ul>
			      </div>

			         <span class="single-post-close"> 
			         	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><img src="{{ baseurl }}/images/ic_delete_round.svg" alt=""></button>
			         </span>
			      </div>
			      {{#if pin_status}}
		            <div class="pinned-post"><span class="pinned-post-icon"><img src="{{ baseurl }}/images/ic_pin_blue.svg" alt="" class="img-responsive"></span><span>Pinned post</span></div>
		          {{/if}}
			   </div>
			</div>
			<input type="hidden" class="post_visible_user" value="{{visibility}}"> 
			<!-- post-description section -->
			<div class="post-description full-width">
				<h4 class="m-0">{{post_subject}}</h4>
				<div class="full_description hidden">
				  <p>{{#addLink post_description 0}}{{/addLink}}</p>
				  <span class="show_less_content blue-span" id="{{id}}">Show less</span>
				</div>
				<div class="trim_description">
				    {{#postDescriptionTextCheck post_description 300}}
			     	 <p>{{#addLink post_description 300}}{{/addLink}}... </p><span class="show_extra_content blue-span" id="{{id}}">Show more</span>
			     	 {{else}}
			     	 <p>{{#addLink post_description 0}}{{/addLink}}</p>
				    {{/postDescriptionTextCheck}}
				</div>
			</div>
			 <a href="javascript:void(0)" attr-id="{{id}}"  attr-uid="{{space_id}}" class="minimize-collapse"><img src="{{baseurl}}/images/ic_unfold_more.svg">Click here to expand post</a>
             <br>
             <div class="modal fade endrose add_scroll visibility-edit-modal" id="visiblepopup{{ id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
             </div>
	   	</div>


	   		<!-- images section -->
			<div class="expand_view_content">
			{{#if images}}
			<div class="update-attachment full-width">
				{{#each images}}
					{{#compare @index 0}}
			    	<div class="findmedia attachment-wrap {{#isSingleImage @root.images}}{{/isSingleImage}}">
			    		<input type="hidden" name="file_name" value="{{escape this.metadata.originalName}}">
			        	<a class="attach-link full-width" href="javascript:void(0)" onclick="viewPostAttachment('{{this.post_file_url}}', '{{this.metadata.mimeType}}', '{{escape this.metadata.originalName}}', '{{@root.id}}', '{{this.metadata.size}}', {{toJson this}})">
			            <img class="img-responsive {{this.id}}" src="{{#feedSignedUrl this.post_file_url this.id id}}{{/feedSignedUrl}}" alt="image"  media-id="{{this.id}}" viewfile="{{ this.post_id }}"/>
			        	</a>
			      	</div>
			      	{{/compare}}
			   {{/each}}
			</div>
			{{/if}}
			

			<!-- url-preview section -->
			{{#if meta_array.get_url_data}}
			<div class="link-view-section">
				<div class="link-previrew-col full-width inner-block">
				    {{#compareValue meta_array.get_url_data.metatags.twitter:player}}
					 <div class="video_desc_block description-block">
		                <div>
		                   <a class="post_emb_link" target="_blank" href="{{meta_array.get_url_data.full_url}}" title="Information Technology in Education"><img src="https://www.google.com/s2/favicons?domain={{meta_array.get_url_data.metatags.twitter:player}}">{{meta_array.get_url_data.title}}</a>
		                   <p>{{#limitText meta_array.get_url_data.description 150}}{{/limitText}}</p>
		                </div>
		             </div>
		            {{#if meta_array.get_url_data.metatags.twitter:player}}
						{{#compareValue meta_array.get_url_data.metatags.twitter:player}}
							<iframe class="youtube_iframe" id='{{id}}_youtube_iframe' data-video-src={{meta_array.get_url_data.url}} allowfullscreen="allowfullscreen" width="100%" height="480" src="{{meta_array.get_url_data.metatags.twitter:player}}/?enablejsapi=1"></iframe>
						{{/compareValue}}
					{{/if}}
		             {{else}}
		             <div class="link-thumbnail">
					    <span style="background-image: url('{{meta_array.get_url_data.favicon}}');"></span>
					 </div>
					 <div class="link-preview-description">
					    <a href="{{meta_array.get_url_data.full_url}}" target="_blank">
					      <span class="link-title">{{meta_array.get_url_data.title}}</span>
					    </a>
					    <p>{{#limitText meta_array.get_url_data.description 150}}{{/limitText}}</p>
					 </div>
					{{/compareValue}}
				</div>
			</div>
			{{/if}}
			<!-- attachments section -->
			
			{{#ifCond documents.length '==' 1 }}
				{{#ifCond documents.0.metadata.mimeType '==' 'video/mp4'}}
				<div class="media_video_section">
				<a class="findmedia attach-link full-width" href="javascript:void(0)">

					<video controls poster="" class="bkg"  width="100%" class="media-video" mimeType="video/mp4" viewfile="{{ id }}" media-id="{{documents.0.id}}">
                      <source class="{{documents.0.id}}" src="{{#feedSignedUrl documents.0.post_file_url documents.0.id id}}{{/feedSignedUrl}}" type="video/mp4">
                      Your browser does not support HTML5 video.
                   </video>
                   <input type="hidden" name="file_orignal_name" value="{{escape documents.0.metadata.originalName}}">
               	</a>
               	</div>
               	{{removePreviewedDocument documents documents}}
				{{/ifCond}}	
			{{/ifCond}}

			{{removePreviewedDocument documents images}}
			{{#if documents}}
			<div class="file-attachment-col full-width">
			   <h4 class="m-0">Attachments</h4>
			   	{{#each documents}}
			   		<a class="{{#ifCond @index '>=' 2 }} hidden hiddenable{{/ifCond}} findmedia attach-link full-width" href="javascript:void(0)" onclick="viewPostAttachment('{{this.post_file_url}}', '{{this.metadata.mimeType}}', '{{escape this.metadata.originalName}}', '{{@root.id}}', '{{this.metadata.size}}', {{toJson this}} )">
				      <img class="" src="{{#extention_icon}}{{#extention}}{{escape this.metadata.originalName}}{{/extention}}{{/extention_icon}}" media-id="{{this.id}}" viewfile="{{ this.post_id }}"/>
				      <span class="attachment-text">{{this.metadata.originalName}}</span>
				   	</a>
			   	{{/each}}
			   	{{#ifCond documents.length '>' 2}}
			   		<span class="more-attachments full-width"><a href="javascript:void(0)">	+{{minus documents.length 2}} more attachments</a></span>
			   		<span class="hidden more-attachments full-width"><a href="javascript:void(0)">View fewer attachments</a></span>
			   {{/ifCond}}
			</div>
			{{/if}}

			<!-- likes_views -->
			<div class="like-detail full-width {{id}}">
			   <div class="bottom-section left">
			    <div class="like-detail-section">
			      <a href="javascript:void(0)" class="endrose disable">
			      	{{#if endorse_by_me}}<img class="dendorse" id="{{id}}" src="{{baseurl}}/images/ic_thumb_up.svg">
			      	{{else}}<img class="endorse" id="{{id}}" src="{{baseurl}}/images/ic_thumb_up_grey.svg">{{/if}}
			      </a>
			      <div class="endrose-wrap">
			         <div class="endrose">
			      	{{#if endorse}}
			            <span>
			            	{{#endorse endorse_by_me endorse_by_others}}{{/endorse}}
			        	</span> found this useful
			   	  	{{/if}}
			         </div>
			      </div>
			     </div>
			   {{#if post_media_log.length}}
			   <div class="view-right pull-right">
			      <a href="javascript:void(0)" data-toggle="modal">
			         <button type="button" class="get_view_user btn viewpostsuser{{id}}" data-toggle="popover" data-trigger="hover" data-placement="bottom" title="" data-html="true" data-content="" data-original-title="Who has viewed this content">
			         <img src="{{baseurl}}/images/ic_visibility.svg" data-html="true" space-id={{space_id}} getViewId="{{id}}">
			         <span class="view_eye_content"> {{post_media_log.length}} views  </span>
			      </button>
			      </a>
			   </div>
			   {{/if}}
			   </div>
			</div>
			</div>
		
		<!-- comments section -->
		<div class="post-comment-part full-width expand_view_content">
	      <div class="comment-wrap box full-width">
	      	{{#if comments_count }}
	      		{{#count comments_count 2}}
	      			<a class="viewmore-comm view-more-comments">View {{#math comments_count '-' 2 }} {{/math}} more comments</a>
	      			<a class="viewmore-comm view-less-comments hidden">View fewer comments</a>
	      		{{/count}}
	      	{{/if}}
	      	{{#each comments}}
	      	<div class="{{#commentLimit @index @root.comments_count 3 }}hidden{{/commentLimit}} user-comment-post" id="{{id}}">
	            <div class="user-comment-left">
	               <a href="javascript:void();">
	               <span class="user-comment" style="background-image: url('{{#isProfileImageExist this.user.profile_image_url}} {{/isProfileImageExist}}');"></span>
	               </a>
	            </div>
	            <div class="user-comment-detail">
	               <span class="user-comment-name"><a data-id='{{ this.user.id }}' onclick='liked_info(this);'>{{this.user.fullname}}</a></span>
	               <span class="user-comment-description">
	                  <p>{{#addLink this.comment 0}}{{/addLink}}</p>
	               </span>
	               <div class="full-width">
		               <span class="user-comment-time">{{#dateFormat this.created_at.date 'MMMM DD, HH:mm'}}{{/dateFormat}} {{#ifCond this.created_at.date '!=' this.updated_at.date}} (edited) {{/ifCond}}</span>
		               {{#if this.attachments}}<span class="attachment-count">{{this.attachments.length}} Attachments</span>{{/if}}
	               </div>
	               <div class="upload-file-view">
	               		<ul>
	               			{{#each this.attachments}}
	               				<li><a href="javascript:void(0);" onclick="viewPostAttachment('{{this.file_url}}', '{{this.metadata.mimeType}}', '{{escape this.metadata.originalName}}', '{{@root.id}}', '{{this.metadata.size}}', {{toJson this}} )">{{this.file_name}}</a></li>
	               			{{/each}}
	               		</ul>
	               </div>
	            </div>
	            
            {{#checkUserIsPostOwnerOrAdmin @root.is_logged_in_user_admin @root.logged_user.id user_id}}
	            <div class="dropdown hover-dropdown white-background edit-comment">
               <a href="javascript:void();" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
               <span></span>
               </a>
               	<ul class="dropdown-menu">
               		{{#ifCond this.user.id '==' @root.logged_user.id}}
	                	<li class="domain_inp_edit">
	                    	<a href="javascript:void(0)" data-comment-restriction="{{@root.feature_restriction.post.add_comment_attachment}}" onclick="return edit_comment('{{id}}', this);">Edit comment</a>
	                	</li>
                	{{/ifCond}}
                	{{#if @root.is_logged_in_user_admin}}
	                    <li class="domain_inp_delete">
	                    	<a href="javascript:void(0)" onclick="return delete_comment('{{@root.id}}', '{{id}}', '{{@root.space_id}}');">Delete comment</a>
	                  	</li>
                  	{{else}}
                  		{{#ifCond this.user.id '==' @root.logged_user.id}}
	                  		<li class="domain_inp_delete">
			                	<a href="javascript:void(0)" onclick="return delete_comment('{{@root.id}}', '{{id}}', '{{@root.space_id}}');">Delete comment</a>
			              	</li>
                  		{{/ifCond}}
                  	{{/if}}
               </ul>
            </div>
            {{/checkUserIsPostOwnerOrAdmin}}
	         </div>
	         {{/each}}
	         <div class="pined-user-text-box">
			   <span class="pro_pic_wrap dp" style="background-image: url('{{#if logged_user.profile_image_url}} {{ logged_user.profile_image_url }} {{else}} {{ baseurl }}/images/dummy-avatar-img.svg {{/if}}');" ></span>
			   <div class="form-group dp-input comment-add-section" data-postid="{{@root.id}}">
			      <div contenteditable="true" class="form-control no-border comment-area" id="comment_input_area{{id}}" data-placeholder="Add a comment or tag someone using @..." areaid="{{id}}" data-postId="{{@root.id}}" style="white-space: pre-line;" wrap="hard"></div>
			      
			      {{#unless feature_restriction.post.add_comment_attachment }}
			      <div class="comment-attach-col" style="display:none;">
			      	<input type="submit" value="File Attachment" class="comment_attachment comment_attachment_trigger" data-spaceid="{{space_id}}" data-postid="{{id}}" data-userid="{{logged_user.id}}" style="float:right;">
			      </div>
			      {{/unless}}

			      <input id="comment_btn_{{id}}" type="submit" value="Send" name="sendmessage" class="send_comment invite-btn" spaceid="{{space_id}}" datapostid="{{id}}" datauserid="{{logged_user.id}}" style="float:right; display:none">

			      <div class="attachment-box-row full-width" style="display:none">
				    <div class="feed-post-attachment-box"></div>
			    </div>
			    <div class="comment_attachment_progress full-width {{id}}"></div>
			   </div>
			</div>
	      </div>
	   </div>
	</div>
	</div>
	<div class="" id="post_edit_{{ id }}">
    </div>
</script>
