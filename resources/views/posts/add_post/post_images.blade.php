@php
	$image_count = anyImage($mail_data['post_media']);
	$video_count = anyVideo($mail_data['post_media']);
@endphp

@if($image_count)
	<!-- Large image start -->
	<tr>
		<td height="16"></td>
	</tr>
	<tr>
	  <td colspan="2"  cellspacing="0" cellpadding="0">
	  	<table width="452"  cellspacing="0" cellpadding="0">
	  		@php $index = 1; @endphp
	  		@foreach($mail_data['post_media'] as $media)
	  			@if( !is_numeric(stripos($media['metadata']['mimeType'], 'image')) ) @continue @endif
                @if($index == 1 || $index == 3 ) <tr> @endif
	  			<td align="center" @if($image_count>1) style="background-color:#fff;margin-bottom:3px width="223" height="153" @endif>

	  				@php
	  					$file_path = $media['metadata']['url'];
	  				@endphp
					@if($image_count>1)
						@php
							$image_dimentions = generateImageThumbnail($file_path, 223, 158).' "style=vertical-align:bottom';
						@endphp
					@else 
						@php
							$image_dimentions = generateImageThumbnail($file_path, 424,318);
						@endphp
					@endif

	  				<a style="padding-top: 8px;float: left;width: 100%;" href="{{$mail_data['post_page']}}">
	  					<img {{$image_dimentions}} src="{{composeEmailURL($media['metadata']['url'])}}" alt="image"/>
	  				</a>
	  			</td>
                @if($index == 2) </tr> @endif
                @php $index++; @endphp
                @if ($index == 5) @break @endif
	   		@endforeach
	  	</table>
	  </td>
	</tr>
	<!-- Large image end -->
@elseif($video_count)
	<tr>
		<td height="16"></td>
	</tr>
	<tr>
	  <td colspan="2"  cellspacing="0" cellpadding="0">
	  	<table width="452"  cellspacing="0" cellpadding="0" style="width: 452px;">
	  			<td align="center">
	  				<a style="padding-top: 8px;float: left;width: 100%;" href="{{$mail_data['post_page']}}">
	  					<img style="vertical-align:bottom" src="{{$mail_data['video_ss']}}" alt="image"/>
	  				</a>
	  			</td>
	  	</table>
	  </td>
	</tr>
@endif