<script id="user-share-record" type="text/x-handlebars-template">
	<tr class="row-data {{data.id}}">
		<td class="first_name">{{data.first_name}}</td>
		<td class="last_name">{{data.last_name}}</td>
		<td>{{data.email}}</td>
		<td class="share_name">{{data.share_name}}</td>
		<td>{{#if data.company_name}} {{data.company_name}} {{else}} - {{/if}}</td>
		<td class="user_type">{{#ifCond data.user_type_id '==' user_type.user }}Regular {{else}} Admin {{/ifCond}}</td>
		<td>{{data.share_status}}</td>
		<td class="user_share_status">
		{{#ifCond data.user_status '>' 0}}
			{{#userStatusByInvitationCode invitation_code}} {{/userStatusByInvitationCode}}
		{{else}}
			Deleted
		{{/ifCond}}
		</td>
		<td>
			<div class="dropdown hover-dropdown">
			   <a class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">
			   <span></span>
			   </a>
			   <ul class="dropdown-menu">
			      <li style="display: {{#ifCond data.user_type_id '==' user_type.admin }} none {{/ifCond}};"><a data-toggle="modal" data-target="#promote_user_by_admin" data-uid="{{data.id}}" class="populate_user_details" data-user-id="{{data.user_id}}" data-space-id="{{data.space_id}}">Promote to admin</a></li>
			      {{#ifCond invitation_code '>=' 0}}
			      <li><a href="#" class="delete-link remove_user" data-toggle="modal" data-target="#removeuserpopup" data-uid="{{data.id}}" data-user-id="{{data.user_id}}" data-space-id="{{data.space_id}}">
			      		{{#ifCond invitation_code '==' 0}}
			      			Remove Invite
			      		{{else}}
			      			Remove user
			      		{{/ifCond}}
			  	  </a></li>
			      {{/ifCond}}
			   </ul>
			</div>
		</td>
	</tr>
</script>