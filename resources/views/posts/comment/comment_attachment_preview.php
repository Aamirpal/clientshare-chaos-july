<script id="comment_attachment_preview" type="text/x-handlebars-template">
	<ul class="comment-attachment-list">
		{{#each comment_files}}
		<li id="{{this.uid}}" class="comment-attachment">
			<a href="javascript:void(0);">
				{{this.originalName}}
				<span class="comment-attach-delete" data-uid="{{@root.uid}}">
					<img width="8" src="{{ baseurl }}/images/ic_delete_small_grey.svg" alt="">
				</span>
			</a>
		</li>
		{{/each}}
	</ul>
</script>