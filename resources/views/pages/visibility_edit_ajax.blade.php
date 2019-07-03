
<?php
//echo $visible_to_all;
//$postid
//$logeduser

$myArray = explode(',', $usercount);
$user_visilbe_count = sizeOfCustom($myArray);
$user_visible_i = 1; 
if('All'==$visible_to_all){ ?>
                   
       <span href="#!" data-toggle="modal" data-target="#endoresedpopup1"><span data-trigger="hover" type="button" data-toggle="popover"
        data-placement="bottom" data-html="true" 
        data-content=" <?php $use = 1;?>@foreach( $all_users as $key) 
        				 
        				 @if($use <= 5)
                        {{ ucfirst($key['first_name'])}} {{ ucfirst($key['last_name'])}}
                        <br/>
                        @endif
                         @if($use == 6)
                         {{'and'}} <?php echo $u = sizeOfCustom($all_users)-5 ?> {{'others'}}
                        @endif
                        <?php $use++ ?>	

                         	         
                  @endforeach" id="example_popover" class="visible_tooltip example_popover2" endors-poup-post="">
                  <span data-toggle="modal" data-target="#visiblepopup{{$postid}}" class="visibility_setting_more add_scroll" setting-id="{{$postid}}" space-id="{{$spaceid}}">
                  			Everyone
					</span> 

                  </span></span>
<?php  }else{  ?>

		@foreach($all_users as $visible_u)	
			@if($user_visible_i<=2)
				
				@if (strpos($visible_users[0]['visibility'], $visible_u['id']) !== false) 
		    		 <span id="see_community" href="#" userid="{{$visible_u['id']}}" spaceid="{{$spaceid}}" >
		    		{{ucfirst($visible_u['first_name'])}} {{ucfirst($visible_u['last_name']) }}</span> 
		    		@if($user_visilbe_count>1 && $user_visible_i==1)
		    		 ,
		    		@endif
		    	@endif
		    @endif
		    <?php $user_visible_i++; ?>
		@endforeach
		@if($user_visilbe_count>2)
			<span href="#!" data-toggle="modal" data-target="#endoresedpopup1"><span data-trigger="hover" type="button" data-toggle="popover"
        data-placement="bottom" data-html="true" <?php $usre = 1;?>
        data-content="@foreach( $all_users as $key) 
        						 @if($usre <= 5)
                        		{{ ucfirst($key['first_name'])}} {{ ucfirst($key['last_name'])}}
                        		<br/>
                        		@endif
                        		@if($usre == 6)
                         		{{'and'}} <?php echo $u1 = sizeOfCustom($all_users)-5 ?> {{'others'}}
                        		@endif
                        		<?php $usre++ ?>

        				       
                        		                         	         
                  @endforeach" id="example_popover" class="visible_tooltip example_popover2" endors-poup-post="">
			<span data-toggle="modal" data-target="#visiblepopup{{$postid}}" class="visible_tooltip visibility_setting_more add_scroll" setting-id="{{$postid}}" space-id="{{$spaceid}}">
                 and {{$user_visilbe_count-2}} others
            </span>

             </span></span>
		@endif
<?php } ?>


<script>
$(document).ready(function(){
	$(".example_popover2").popover();
	});
</script>