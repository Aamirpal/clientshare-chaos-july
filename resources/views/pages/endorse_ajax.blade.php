<div class="@if(!empty($data))endrose @endif">
<?php
 $data1 = array_reverse($data);
  // echo '<pre>';
  // print_r($data1);exit;

 ?>
@if(!empty($data))
       <span>
       @if($endorsed_by_me=='true')
       	You @if(sizeOfCustom($data)>1) @if(sizeOfCustom($data)==2)
         <span>&</span>
         @else
         ,
         @endif @endif
       		@if(sizeOfCustom($data)>=2) 
                      <!-- skip current usr name-->
                      @php ($f1=1)
                     @foreach($data1 as $re)
                        @if($re['user']['id']!=Auth::user()->id)
                          @if($f1==1)
                          <?php if($re['space_user']['user_status'] == 1) { ?>
                        {{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}
                        <?php } else { ?>
                            <a href="#" data-toggle="modal" data-target="#myEndModal{{$re['user_id']}}" data-id="{{$re['user']['id']}}" onclick="liked_info(this);">{{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}</a>
                           <?php } ?>
                           
                            @php ($f1++) 
                          @endif
                        @endif
                      
                      @endforeach 
          	@endif

       @endif
        @if($endorsed_by_me=='false')
        	@if(sizeOfCustom($data)>=2)


        			@php ($f2=1)
                        @foreach($data1 as $re)
                          @if($re['user']['id']!=Auth::user()->id)
                            @if($f2<=2)
                              <?php if($re['space_user']['user_status'] == 1) { ?>
                        {{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}
                        <?php } else { ?>
                            <a href="#" data-toggle="modal" data-target="#myEndModal{{$re['user_id']}}" data-id="{{$re['user']['id']}}" onclick="liked_info(this);">{{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}</a>
                           <?php } ?>
								@if($f2==1) 	@if(sizeOfCustom($data)==2)
         <span>&</span>
         @else
         ,
         @endif @endif
                              @php ($f2++) 
                            @endif
                          @endif
                         
                        @endforeach


        	@else
        				 @php ($f3=1)
                         @foreach($data1 as $re)
                           @if($re['user']['id']!=Auth::user()->id) 
                          @if($f3==1)
                            <?php if($re['space_user']['user_status'] == 1) { ?>
                        {{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}
                        <?php } else { ?>
                            <a href="#" data-toggle="modal" data-target="#myEndModal{{$re['user_id']}}" data-id="{{$re['user']['id']}}" onclick="liked_info(this);">{{ ucfirst($re['user']['first_name']).' '.ucfirst($re['user']['last_name']) }}</a>
                           <?php } ?>
                            @php ($f3++) 
                          @endif
                        @endif
                       
                      @endforeach 
        	@endif
        	  
       @endif 
       

       <!--  Joe Dempsey --></span>
            <?php 
                   usort($data, function($a, $b) {
                    
                   $a1 = $a['user']['first_name']; //get the name string value
                   $b1 = $b['user']['first_name'];
                  
                   $out = strcasecmp($a1,$b1);
                   if($out == 0){ return 0;} //they are the same string, return 0
                   if($out > 0){ return 1;} // $a1 is lower in the alphabet, return 1
                   if($out < 0){ return -1;} //$a1 is higher in the alphabet, return -1
               }); ?>
        @if(sizeOfCustom($data)>2)
         and <a href="#!" data-toggle="modal" data-target="#endoresedpopup"><span data-trigger="hover" type="button" data-toggle="popover"
          data-placement="bottom" data-html="true" data-content="<?php $liked_user = 1 ?>
          						@foreach($data as $endorse_data)
                         @if($liked_user <= 5)
                        {{ ucfirst($endorse_data['user']['first_name']) }} {{ ucfirst($endorse_data['user']['last_name']) }}
                        <br/>
                        @endif
                        @if($liked_user == 6)
                         {{'and'}} <?php echo $u = sizeOfCustom($data)-5 ?> {{'others'}}
                        @endif
                        <?php $liked_user++ ?> 

                                @endforeach  
          						" id="example_popover" class="example_popover1 endorsed_popup"  endors-poup-post="{{$post_id}}" space-id="{{$spaceid}}">{{sizeOfCustom($data)-2}} others</span></a>
         @endif
      liked this
@endif   
</div>   
<script>
$(document).ready(function(){
	$(".example_popover1").popover();
	});


</script>