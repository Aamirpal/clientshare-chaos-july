@foreach($postdata as $post)
  @include('posts.post.index', ['post' => $post])
@endforeach