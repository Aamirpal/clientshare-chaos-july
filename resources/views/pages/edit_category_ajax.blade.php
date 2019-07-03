<?php
     $ssl = false;
   if(env('APP_ENV')!='local')
     $ssl = true;
    ?>
<form action="{{ url('/save_editcategory_ajax',[],$ssl) }}" method="post" name="edit_post_form" id="edit_post_category_form">
{{csrf_field()}}
      <input type="hidden" name="spaceid" value="{{$space_id}}" class="spaceid">
      <div class="edit-categories ">
        <p class="small-text">Add or edit a category</p>
        <ul class="category_edit_list">          
          @foreach( $categories[0]['category_tags'] as $key => $val)
            @if ($loop->first)
              <li class=""><input name="{{$key}}" class="form-control box category_value" type="text" placeholder="Start typing..." value="{{$val}}" maxlength="25" readonly>
                <span class="letter-count count_cat"></span>
                </li>
            @else
              <li class=""><input name="{{$key}}" class="form-control box category_value" type="text" placeholder="Start typing..." value="{{$val}}" maxlength="25">
                <span class="letter-count count_cat"></span>
                <div class="category-field-delete">
                  <img data-toggle="modal" data-target="#category_delete_popup" class="delete" src="{{ url('/',[],$ssl) }}/images/ic_delete_blue.svg">
                </div>
              </li>
            @endif
          @endforeach
        </ul>
        <a href="#!" class="add-category-link add_category"><span class="category-add-icon"><img src="{{ url('/',[],$ssl) }}/images/ic_add.svg"></span>Add a new category</a>
        <span class="category_error"></span>
        <div class="text-right">
          <input type="hidden" name="delete_category" value="">
          <button type="submit" class="btn btn-primary pull-right btn-quick-links">save</button>
          <button type="button" class="btn btn-default btn-quick-links" data-dismiss="modal">cancel</button>
        </div>
      </div><!-- edit-categories -->
</form>
