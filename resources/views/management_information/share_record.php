<script id="mi-share-record" type="text/x-handlebars-template">
	<tr id="row-{{spaces.space_id}}" class="row-data space-row" data-space="{{spaces.space_id}}">
		<td class="supplier-name supplier-col" style="text-align:left;width: 97px !important; min-width: 97px !important; max-width: 97px !important;">{{spaces.seller_name}}</td>
		<td class="supplier-name buyer-col" style="text-align:left;width: 97px !important; min-width: 97px !important; max-width: 97px !important;">{{spaces.buyer_name}}</td>
		<td class="share-name share-name-col tooltip-hover" style="text-align:left;width: 97px !important; min-width: 97px !important; max-width: 97px !important;">

		<span class="spnDetails">{{spaces.share_name}}</span>
			<span class="spnTooltip admin_names">
				<span>Admin {{admin_names.firstname}}</span>
			</span>

		</td>
		<td data-uiclass="o-cont-value-th" >{{#if spaces.contract_value}} {{math spaces.contract_value '/' contract_value_division}} {{else}} - {{/if}}</td>
		<td data-uiclass="o-cont-date-th" >{{#if spaces.contract_end_date}} {{spaces.contract_end_date}} {{else}} - {{/if}}</td> 
		<td data-uiclass="o-status-th" >{{#if spaces.status}} {{spaces.status}} {{else}} - {{/if}}</td>
		<td class="" data-uiclass="b-comm-th" >
			<table class="full-table value-table">
				<tbody>
					<tr>
						<td class="buyer-community-col
							{{#ifCond community.buyers '>=' community_growth}} light-green-bg {{else}} light-red-bg {{/ifCond}}
							">{{community.buyers}}</td>
						<td class="buyer-commonity-growth-col">
							{{#ifCond community.cal_buyers '>' 0}} + {{/ifCond}}
							{{community.cal_buyers}}
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td data-uiclass="s-comm-th" >
			<table class="full-table value-table">
				<tbody>
					<tr>
						<td class="sellers-community-col 
							{{#ifCond community.sellers '>=' community_growth}} light-green-bg {{else}} light-red-bg {{/ifCond}}
							">{{community.sellers}}</td>
						<td class="sellers-community-growth-col">
							{{#ifCond community.cal_sellers '>' 0}} + {{/ifCond}}
							{{community.cal_sellers}}
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td class="light-blue-bg" data-uiclass="o-comm-th" >
			<table class="full-table value-table">
				<tbody>
					<tr>
						<td class="overall-community-col 
							{{#ifCond community.over_all '>=' overall_growth}} up light-green-bg {{else}} down light-red-bg {{/ifCond}}
							">{{community.over_all}}</td>
						<td class="overall-community-growth-col">
							{{#ifCond community.over_all_performance '>' 0}} + {{/ifCond}}
							{{community.over_all_performance}}
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td data-uiclass="b-csi-th">
			<table class="full-table value-table">
				<tbody>
					<tr>
						<td class="buyer-csi-col 
							{{#ifCond csi.buyer_csi_score_this_month '>=' csi_growth}} light-green-bg {{else}} light-red-bg {{/ifCond}}
							">{{csi.buyer_csi_score_this_month}}</td>
						<td class="buyer-csi-growth-col">
							{{#ifCond csi.buyer_csi_score_change '>' 0}} +{{/ifCond}}
							{{csi.buyer_csi_score_change}}%
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td data-uiclass="s-csi-th">
			<table class="full-table value-table">
				<tbody>
					<tr>
						<td class="seller-csi-col 
							{{#ifCond csi.seller_csi_score_this_month '>=' csi_growth}} light-green-bg {{else}} light-red-bg {{/ifCond}}
							">{{csi.seller_csi_score_this_month}}</td>
						<td class="seller-csi-growth-col">
							{{#ifCond csi.seller_csi_score_change '>' 0}} +{{/ifCond}}
							{{csi.seller_csi_score_change}}%
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td class="light-blue-bg" data-uiclass="o-csi-th">
			<table class="full-table value-table">
				<tbody>
					<tr>
						<td class="overall-csi-col 
							{{#ifCond csi.overall_csi_score '>=' csi_growth}} up light-green-bg {{else}} down light-red-bg {{/ifCond}}
							">{{csi.overall_csi_score}}</td>
						<td class="overall-csi-growth-col">
							{{#ifCond csi.overall_csi_score_change '>' 0}} +{{/ifCond}}
							{{csi.overall_csi_score_change}}%
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td data-uiclass="b-posts-th" >
			<table class="full-table value-table">
				<tbody>
					<tr>
						<td class="buyer-post-col {{#ifCond posts.buyer_posts_total '>=' community_growth}} light-green-bg {{else}} light-red-bg {{/ifCond}}">{{#if posts.buyer_posts_total}} {{posts.buyer_posts_total}} {{else}} 0 {{/if}}</td>
						<td class="buyer-post-growth-col"> {{#if posts.buyer_posts_change}}
						{{#ifCond posts.buyer_posts_change '>=' 1}}+{{/ifCond}}
						{{posts.buyer_posts_change}} {{else}} 0 {{/if}}</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td data-uiclass="s-posts-th" >
			<table class="full-table value-table">
				<tbody>
					<tr>
						<td class="seller-post-col {{#ifCond posts.supplier_posts_total '>=' community_growth}} light-green-bg {{else}} light-red-bg {{/ifCond}}">{{#if posts.supplier_posts_total}} {{posts.supplier_posts_total}} {{else}} 0 {{/if}}</td>
						<td class="seller-post-growth-col"> {{#if posts.supplier_posts_change}}
						{{#ifCond posts.supplier_posts_change '>=' 1}}+{{/ifCond}}
						{{posts.supplier_posts_change}} {{else}} 0 {{/if}}</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td class="light-blue-bg" data-uiclass="o-posts-th">
			<table class="full-table value-table">
				<tbody>
					<tr>
						<td class="overall-post-col {{#ifCond posts.overall_posts_total '>=' community_growth}} up light-green-bg {{else}} down light-red-bg {{/ifCond}}">{{#if posts.overall_posts_total}} {{posts.overall_posts_total}} {{else}} 0 {{/if}}</td>
						<td class="overall-post-growth-col"> {{#if posts.overall_posts_change}}
						{{#ifCond posts.overall_posts_change '>=' 1}}+{{/ifCond}}
						{{posts.overall_posts_change}} {{else}} 0 {{/if}}</td>
					</tr>
				</tbody>
			</table>
		</td>
		<td data-uiclass="b-pi-th">{{post_interactions.buyer_interations}}</td>
		<td data-uiclass="s-pi-th">{{post_interactions.seller_interations}}</td>
		<td data-uiclass="o-pi-th">{{post_interactions.total_interations}}</td>
		<td data-uiclass="o-nps-th">{{#ifCond nps.feedback_status '==' true}} {{nps.total}} {{else ifCond nps.total '>=' 1}} {{nps.total}} {{else}} - {{/ifCond}}</td>
		<td data-uiclass="o-pinv-th">{{pending.total}}</td>
		<td data-uiclass="o-prog-th" class="tooltip-hover">
			<span class="spnDetails">{{progress}}%</span>
			{{#ifCond progress '!=' 100}}
			<span class="spnTooltip">
				{{#if pending_tasks.logo}} {{else}}<span>Logos</span>{{/if}}
				{{#if pending_tasks.banner}} {{else}}<span>Banner</span>{{/if}}
				{{#if pending_tasks.category}} {{else}}<span>Categories</span>{{/if}}
				{{#if pending_tasks.executive_summary}} {{else}}<span>Executive Summary</span>{{/if}}
				{{#if pending_tasks.links}} {{else}}<span>Quick Links</span>{{/if}}
				{{#if pending_tasks.twitter}} {{else}}<span>Twitter</span>{{/if}}
				{{#if pending_tasks.domain}} {{else}}<span>Domain Management</span>{{/if}}
				{{#if pending_tasks.posts}} {{else}}<span>5 posts</span>{{/if}}
			</span>
			{{/ifCond}}
		</td>
		<td class="community-table-col">
			<table class="full-table community-table communication-table">
				<tbody>
					<tr>
						<td>
							
							<span class="status-circle circle-{{calculateRAGColor space_data.mail_log }}"></span>

							<div class="dropdown table-communication hidden">
								<select name="communication_type" class="communication-type selectpicker">
									<option value="community">Community</option>
									<option value="csi">CSI Score</option>
									<option value="posts">Posts</option>
								</select>
							</div>
						</td>
						<td>
							<div class="mi-mail-icon">
								<a href="javascript:void(0)" class="share_preview_modal_trigger preview-mi-email" ></a>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</script>