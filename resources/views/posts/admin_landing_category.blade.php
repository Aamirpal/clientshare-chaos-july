<div class="categories-wrap" id="tour4">
      <ul class="categories">
         <h3 class="title filter-title">Filter content by category</h3>
         @if($data->category_tags!='')
         @foreach($data->category_tags as $key =>$category)
         @if($data->category_tags!='')
         <li><a href="#!" key="{{$key}}" space_id="{{$space_id}}" class="chip disable filter_post_category"><span>{{$category}}</span></a></li>
         @endif
         @endforeach
         @endif
         <span class="more-categories">
         <a class="btn btn-primary more" href="#">+<span></span> more</a>
         <a class="btn btn-primary less" href="#">Show less</a>
         @if(strtolower($space_user[0]['user_role']['user_type_name']) == 'admin')
            @if(env('DISABLE_CATEGORY', true))
               <a class="edit-cat" href="#!" data-toggle="modal" data-target="#modal_category" onclick="return edit_category(event, this);" spaceid="{{$space_id}}"><img src="{{ url('/',[],$ssl) }}/images/ic_edit.svg" alt=""></a>
            @endif
         @endif
         </span>
      </ul>
   </div>
<div class="modal fade custom-tile-popup modal_category" id="modal_category" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content white-popup">
         <div class="modal-header">
            <h2 class="modal-title" id="myModalLabel">Category</h2>
         </div>
         <div class="categories"></div>
      </div>
   </div>
</div>