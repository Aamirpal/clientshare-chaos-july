<div class="lazy-loading post-file-filter">
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
<div class="filter_section hidden">
	<h2>Filters</h2>
	<form class="post-file-form">
		<div class="form-group">
		    <label for="">File Name</label>
		    <input name="file_name" type="text" class="form-control" placeholder="Start typing">
		</div>
		<div class="form-group">
		    <label for="">Type</label>
		    <div class="types">
		    	<a class="post-file-type pdf" href="#">PDF</a>
		    	<a class="post-file-type doc" href="#">DOC</a>
		    	<a class="post-file-type video" href="#">VID</a>
		    	<a class="post-file-type image" href="#">IMG</a>
				<a class="post-file-type url" href="#">URL</a>
		    </div>
		</div>
		<div class="form-group">
		    <label for="">Date Added</label>
		    <input name="date_range" type="text" class="form-control post-file-date-filter" id="" placeholder="Anytime">
		</div>
		<div class="form-group">
		    <label for="">Post Subject</label>
		    <input name="post_subject" type="text" class="form-control" id="" placeholder="Start typing">
		</div>
		<div class="form-group">
		    <label for="">Added by</label>
		    <select class="post-added-select" name="users[]" multiple="multiple"></select>
		</div>
		<div class="form-group">
		    <label for="">Category</label>
		    <select class="post-category-select" name="catgories[]" multiple="multiple"></select>
		</div>
		<div class="reset-group reset-post-file-filter">
		    <a href="#">Reset Filter</a>
		</div>
	</form>
</div>
