<div class="listing">
	<div class="lazy-loading post-file-table">
      <div class="user-img">
        <p class="pic"></p>
        <p class="name"></p>
        <p class="pull-right one"></p>
        <p class="pull-right two"></p>
        <br>
        <p class="name2"></p>
        <p class="name3"></p>
      </div>
      <div class="content">
        <p class="one"></p><br>
        <p class="two"></p><br>
        <p class="one"></p><br>
        <p class="three"></p>
      </div>
    </div>

	<div class="table-responsive">
		<table class="table table-striped post-file-view-table">
			<thead>
				<tr>
					<th style="width:20%;" data-column="file_name" class="result-ordering">File Name 
						<a href="#">
							<i class="fa fa-long-arrow-up " aria-hidden="true"></i>
							<i class="fa fa-long-arrow-down " aria-hidden="true"></i>
						</a>
					</th>

					<th  style="width:9%;" data-column="file_extention" class="result-ordering">Type <a><i class="fa fa-long-arrow-up " aria-hidden="true"></i>
						<i class="fa fa-long-arrow-down " aria-hidden="true"></i></a></th>

					<th style="width:19%;" data-column="category" class="result-ordering">Category<a><i class="fa fa-long-arrow-up " aria-hidden="true"></i>
						<i class="fa fa-long-arrow-down " aria-hidden="true"></i></a></th>
					
					<th style="width:12%;" data-column="created_at" class="result-ordering">Date Added<a><i class="fa fa-long-arrow-up " aria-hidden="true"></i>
						<i class="fa fa-long-arrow-down " aria-hidden="true"></i></a></th>
					
					<th style="width:25%;" data-column="post_subject" class="result-ordering">Post Subject<a><i class="fa fa-long-arrow-up " aria-hidden="true"></i>
						<i class="fa fa-long-arrow-down " aria-hidden="true"></i></a></th>
					<th style="width:14%;" data-column="user_name" class="result-ordering">Added by<a><i class="fa fa-long-arrow-up " aria-hidden="true"></i>
						<i class="fa fa-long-arrow-down " aria-hidden="true"></i></a></th>
				</tr>
			</thead>
			<tbody class="post_file_view_body"></tbody>
		</table>
		<div id="load_more" class="load_more" style="float: left; width: 100%; text-align: center;">
		  <img src="{{env('APP_URL')}}/images/puff.svg" class="show_puff" style="display: inline;">
		</div>
		<div class="no-result-div" style="display: none">
			<div class="no-result-col">
				<i class="fa fa-search" aria-hidden="true"></i>
			  	<p>No result found.</p>
			</div>
		</div>
	</div>
</div>