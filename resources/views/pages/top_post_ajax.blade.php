@if( !sizeOfCustom($toppost) )
  @if($month != date('n') && $year <= date('Y'))
    <div class="greyout box action">
      <p>No Posts to Show</p>
    </div>
  @else
     @include('layouts.sidebar_top_post_box')
  @endif
@endif

@php $sr_no=1; @endphp
@foreach($toppost as $tpostdata)                               
  @if(!isset($req_data['view_share']))
    <a href="{{url('/clientshare')}}/{{$tpostdata['post_details'][0]['space_id']}}/{{$tpostdata['post_details'][0]['id']}}" class="top-list">
  @endif
  
  <div class="box">
    <h4 class="title">{{$tpostdata['post_details'][0]['user']['first_name']}} {{$tpostdata['post_details'][0]['user']['last_name']}}</h4>
        @if( strlen($tpostdata['post_details'][0]['post_subject']) > 55)
          <p class="invite-btn">{{substr($tpostdata['post_details'][0]['post_subject'], 0, 55).'...'}}</p>
        @else
          <p class="invite-btn">{{$tpostdata['post_details'][0]['post_subject']}}</p>
          <span class="time"><?= date('F d, H:i ',strtotime($tpostdata['post_details'][0]['created_at']))?> </span>
        @endif
  </div>
    @if(!isset($req_data['view_share']))
      </a> 
    @else
      </div>
    @endif
    @php $sr_no++ @endphp
@endforeach