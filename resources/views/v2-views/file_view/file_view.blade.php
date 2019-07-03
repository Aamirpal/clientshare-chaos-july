<div class="listing">

	<div class="table-responsive">
		<table class="table post-file-view-table">
			<thead>
				<tr class="table-hdrow">
					<th data-column="file_name" class="result-ordering table-cell file-name"> 
					<a href="#">
					File Name
						<img src="{{env('APP_URL')}}/images/v2-images/files-up-down-icon.svg" class="show_puff" style="display: inline;">
					</a>
					</th>

					<th data-column="file_extention" class="result-ordering table-cell file-type">
					<a href="#">
					Type 
						<img src="{{env('APP_URL')}}/images/v2-images/files-up-down-icon.svg" class="show_puff" style="display: inline;">
				  </a></th>

					<th data-column="category" class="result-ordering table-cell category-name">
					<a href="#">
					Category
						<img src="{{env('APP_URL')}}/images/v2-images/files-up-down-icon.svg" class="show_puff" style="display: inline;">
					</a></th>
					
					<th data-column="created_at" class="result-ordering table-cell created-date">
					<a href="#">
					Date Added
						<img src="{{env('APP_URL')}}/images/v2-images/files-up-down-icon.svg" class="show_puff" style="display: inline;">
					</a></th>
					
					<th data-column="post_subject" class="result-ordering table-cell post-subject">
					<a href="#">
					Post Subject
						<img src="{{env('APP_URL')}}/images/v2-images/files-up-down-icon.svg" class="show_puff" style="display: inline;">
					</a></th>
					
					<th data-column="user_name" class="result-ordering table-cell added-by-name">
					<a href="#">
					Added by
						<img src="{{env('APP_URL')}}/images/v2-images/files-up-down-icon.svg" class="show_puff" style="display: inline;">
					</a></th>
				</tr>
			</thead>
			<tbody class="post_file_view_body"></tbody>
		</table>
		<div class="no-result-div" style="display: none">
			<div class="no-result-col">
				<i class="fa fa-search" aria-hidden="true"></i>
			  	<p>No result found.</p>
			</div>
		</div>
	</div>
</div>