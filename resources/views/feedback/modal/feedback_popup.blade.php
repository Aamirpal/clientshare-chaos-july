<!--  FEEDBACK POPUP -->
<div id="feedback-popup" class="modal fade feedback-popup" data-backdrop="static" data-keyboard="false" role="dialog">
   <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <div class="modal-header">
         <h4 class="modal-title"> {{Carbon\Carbon::now()->subMonth(3)->format('F Y')}} - {{Carbon\Carbon::now()->subMonth(1)->format('F Y')}} </h4>
      </div>
      <div class="modal-content">
         <form method="post" action="{{ url('/saveFeedback',[],env('HTTPS_ENABLE', true)) }}" enctype="multipart/form-data" class="feedback_form">
            {!! csrf_field() !!}
            <input type="hidden" name="space_id" value="{{$feedback_data['space']['id']}}">
            <input type="hidden" name="user_id" value="{{$feedback_data['user_id']}}">
            <input type="hidden" name="home" value="home">
            <div class="modal-body">
               <h1>Your opinion is important.</h1>
               <h2>We'd like to hear it.</h2>
               <p class="feedback-title">How likely are you to recommend {{$feedback_data['space']['sellerName']['company_name']}} to a friend or colleague?<span style="color: #0d47a1;"> *</span><a class="helpicon" data-toggle="popover" data-trigger="hover" title=""  data-placement="right" data-content="NPS is an industry standard customer loyalty metric. It is calculated based on one question with a ranking from 1-10. Your overall score ranges from –100 to +100; a positive NPS is deemed to be good, whilst a NPS of +50 is excellent."><i class="fa fa-question-circle"></i></a></p>
               <div class="rating-wrap">
                  <span class="likely">Not at all likely</span>                  
                  @for($i=0; $i<=10; $i++)
                     <div class="radio">
                        <input id="r{{$i}}" type="radio" name="rating" value="{{$i}}" class="rating" >
                        <label for="r{{$i}}">{{$i}}</label>
                     </div>
                  @endfor
                  <span>Extremely likely</span>
               </div>
               <p class="feedback-title">Tell us one thing we can do better</p>
               <textarea rows="1" style="min-height:35px" class="form-control suggesResize"  placeholder="Your answer" name="suggestion" onkeyup="getSuggeCount(this.value)" maxlength="500"></textarea>
               <span class=" text-left suggCount" style="text-align: left;color:#0d47a1; ">500</span>
               <p class="feedback-title">General comment</p>
               <textarea rows="1" class="form-control genCommResize" placeholder="Your answer" name="comments" style="min-height:35px" onkeyup="getCommCount(this.value)" maxlength="500"></textarea>
               <span class=" text-left commCount" style="text-align: left;color:#0d47a1; ">500</span>
            </div>
            <div class="modal-footer">
               <p class="blue-span">* required fields</p>
               <button class="btn btn-primary subButton" type="submit">Submit</button>
               <button type="button" id="thanku-feedbackpopup" class="btn btn-primary subButton" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#thanku-feedback" style="display:none;">
               </button>
            </div>
         </form>
      </div>
   </div>
</div>
<!-- END FEEDBACK POPUP -->