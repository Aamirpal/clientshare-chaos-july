@extends(session()->get('layout'))
@section('content')
<div class="admin_onboarding">
   <div class=" welcome">
      <div class="container ">
         <h1>Welcome to Client Share</h1>
      </div>
   </div>
   <div class="container">
      <form class="" method="post" enctype="multipart/form-data" action="initial_setup">
         {{ csrf_field() }}
         <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            <div class="panel panel-default">
               <div class="panel-heading" role="tab" id="personalinfoheading">
                  <h4 class="panel-title">
                     <a class="step_number_1" role="button" data-toggle="collapse" data-parent="#accordion" href="#personalinfo" aria-expanded="true" aria-controls="personalinfo">
                     <span class="step_number">1</span>Add your personal information
                     </a>
                  </h4>
               </div>
               <div id="personalinfo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="personalinfoheading">
                  <div class="panel-body">
                     <div class="panel-inner-content col-lg-7 col-md-7 col-sm-12 col-xs-12">
                        <div class="form-group">
                           <label class="small-text">Add your profile picture</label>
                           <div class="profile-image">
                              <input type='hidden' name="user[id]" value="{{ Auth::user()->id }}">
                              <input type='hidden' name="space[id]" value="{{ app('request')->input('_shareToken') }}">
                              <input type='file' onchange="readURL(this);" id="img_show" style="display:none;" name="file"/>
                              <img class="uploaded_img" id="blah" src="#" alt="" style="display:none;" />
                              <span class="fileinput-new" id="show_image_pop"><span>UPLOAD IMAGE</span> </span>
                              <span class="fileinput-exists" id="show_change_image_pop" style="display:none" ><img class="img-responsive" alt="Client Share" src="{{ url('/') }}/images/ic_camera_alt.svg" id="cam_image"></span>
                           </div>
                           @if ($errors->has('file'))
                           <span class="error-msg1 text-left help-block">
                           {{ $errors->first('file') }}
                           </span>
                           @endif
                           <p class="error-msg1 text-left help-block" id="txt3"></p>
                        </div>
                        <div class="form-group ex2">
                           <label class="small-text">Add your Client Share biography
                           <a class="helpicon" data-toggle="popover" data-trigger="hover" title="Client Share biography"  data-placement="right" data-content="This content will be added in soon! Lorem Ipsum This content will be added in soon! Lorem Ipsum "><i class="fa fa-question-circle"></i></a></label>
                           <!--<input value="{{ $data[0]['description'] }}" class="form-control" type="type" placeholder="e.g. What are your responsibilities with your client?" name="space[description]" />-->
                           <textarea placeholder="e.g. What are your responsibilities with your client?" value="{{ $data[0]['description'] }}" class="form-control" name="space_user[description]"></textarea>
                        </div>
                        <div class="btn-group">
                        @if( strtolower($space_user[0]['user_role']['user_type_name']) == 'admin' ) <!-- start if for admin user -->
                          <input class="btn btn-primary" value="Continue" type="button" onclick="step_number_jump('step_number_2')"/>
                          <input class="btn btn-grey" value="Skip" type="button" onclick="step_number_jump('step_number_2')" />
                        @else
                            <input class="btn btn-primary" value="Finish" type="submit"/>
                        @endif <!-- start if for admin user -->
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            @if( $space_user[0]['user_role']['user_type_name'] == 'admin' ) <!-- start if for admin user -->
            <div class="panel panel-default">
               <div class="panel-heading" role="tab" id="setupclientshareheading">
                  <h4 class="panel-title">
                     <a class="collapsed step_number_2" role="button" data-toggle="collapse" data-parent="#accordion" href="#setupclientshare" aria-expanded="false" aria-controls="setupclientshare">
                     <span class="step_number">2</span>Set up your Client Share
                     </a>
                  </h4>
               </div>
               <div id="setupclientshare" class="panel-collapse collapse" role="tabpanel" aria-labelledby="setupclientshareheading">
                  <div class="panel-body">
                     <div class="panel-inner-content col-lg-7 col-md-7 col-sm-12 col-xs-12">
                        <div class="form-group">
                           <label class="small-text">Client Share Name
                           <a class="helpicon" data-toggle="popover" title="Clientshare Name" data-trigger="hover" data-placement="right" data-content="This content will be added in soon! Lorem Ipsum This content will be added in soon! Lorem Ipsum "><i class="fa fa-question-circle"></i></a></label>
                           <input value="{{ $data[0]['share_name'] }}" class="form-control" type="type" placeholder="" name="space[share_name]"/>
                           @if ($errors->has('share'))
                           <span class="error-msg1 text-left help-block">
                           {{ $errors->first('share') }}
                           </span>
                           @endif
                        </div>
                        <div class="form-group">
                           <label class="small-text">Enable your client to give feedback?
                           <a class="helpicon" data-toggle="popover" title="Enable your client to give feedback?" data-trigger="hover" data-placement="right" data-content="This content will be added in soon! Lorem Ipsum This content will be added in soon! Lorem Ipsum "><i class="fa fa-question-circle"></i></a></label>
                           <label class="switch">
                              @if ($data[0]['allow_feedback'] == 'true')
                              <input type="checkbox" checked name="space[allow_feedback]">
                              @else
                              <input type="checkbox" name="space[allow_feedback]">
                              @endif
                              <div class="slider round"></div>
                           </label>
                        </div>
                        <div class="btn-group">
                           <input class="btn btn-primary" value="Continue" type="button" onclick="step_number_jump('step_number_3')"/>
                           <input class="btn btn-grey" value="Skip" type="button" onclick="step_number_jump('step_number_3')"/>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="panel panel-default">
               <div class="panel-heading" role="tab" id="addcategoriesheading">
                  <h4 class="panel-title">
                     <a class="collapsed step_number_3" role="button" data-toggle="collapse" data-parent="#accordion" href="#addcategories" aria-expanded="false" aria-controls="addcategories">
                     <span class="step_number">3</span>Add categories to your Client Share
                     </a>
                  </h4>
               </div>
               <div id="addcategories" class="panel-collapse collapse" role="tabpanel" aria-labelledby="addcategoriesheading">
                  <div class="panel-body">
                     <div class="panel-inner-content col-lg-7 col-md-7 col-sm-12 col-xs-12">
                        <h4>Set your categories so content is organised</h4>
                        <div class="form-group">
                           <label class="small-text">Category 1 (Mandatory)</label>
                           <input class="form-control" type="type" placeholder="" name="categories[category_1]" value="{{$data[0]['category_tags']['category_1']??'General'}}" readonly />
                        </div>
                        <div class="form-group">
                           <label class="small-text">Category 2</label>
                           <input class="form-control" type="type" placeholder="" name="categories[category_2]" value="{{$data[0]['category_tags']['category_2']??'Innovation'}}" />
                        </div>
                        <div class="form-group">
                           <label class="small-text">Category 3</label>
                           <input class="form-control" type="type" placeholder="" name="categories[category_3]" value="{{$data[0]['category_tags']['category_3']??'Thought Leadership'}}" />
                        </div>
                        <div class="form-group">
                           <label class="small-text">Category 4</label>
                           <input class="form-control" type="type" placeholder="" name="categories[category_4]" value="{{$data[0]['category_tags']['category_4']??'Management Information'}}" />
                        </div>
                        <div class="form-group">
                           <label class="small-text">Category 5</label>
                           <input class="form-control" type="type" placeholder="" name="categories[category_5]" value="{{$data[0]['category_tags']['category_5']??'Account Management'}}" />
                        </div>
                        <div class="form-group">
                           <label class="small-text">Category 6</label>
                           <input class="form-control" type="type" placeholder="" name="categories[category_6]" value="{{$data[0]['category_tags']['category_6']??'Case Studies'}}" />
                        </div>
                        <div class="btn-group">
                           <input class="btn btn-primary" value="Finish" type="submit"/>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            @endif
         </div>
      </form>
   </div>
</div>
<!--
   <form class="form-horizontal" method="post" enctype="multipart/form-data" action="{{ url('/addprofile') }}">

     {{ csrf_field() }}

     <label>Upload Photo</label>

     <input class="" style="color: #000;" id="file" placeholder="Date" name="file" type="file"> <br><br>

   <label>Add Bio</label>
     <textarea style="color: #000;" class="form-control" rows="3" name="bio" id="textArea"></textarea><br><br>

   <label>Client Share Name</label>
     <input style="color: #000;" class="form-control" id="inputEmail" placeholder="" name="share_name" type="text" value="<?php //echo $data[0]['share_name']; ?>"><br><br>


   <label>Recive Feedback</label>
     <input type="radio" name="feedback" value="yes"> Yes
     <input type="radio" name="feedback" value="no"> No <br><br>


   <input type="submit" value="Continue">  &nbsp;&nbsp;&nbsp;<a href="{{ url('/dashboard') }}">Skip</a>

   -->
<!-- >>>>>>> 75d2678b9c6c4b269ac206c4f76ddd4a8cdd6047 -->
<script>
   $(document).ready(function(){
    $('[data-toggle="popover"]').popover()
    $('.ex2 textarea').autogrow({vertical: true, horizontal: false});
   });


   function readURL(input) {
              if (input.files && input.files[0]) {
         var fileinput = document.getElementById('img_show');
        if (!fileinput)
          return "";
        var filename = fileinput.value;
        if (filename.length == 0)
          return "";
        var dot = filename.lastIndexOf(".");
        if (dot == -1)
        return "";
        var extension = filename.substr(dot, filename.length);
        //alert(extension);
        var file_ext = extension.toLowerCase();
        var allowed_extensions = [".jpg", ".png", ".bmp", ".gif", ".jpeg"];
        var a = allowed_extensions.indexOf(file_ext);
        if(a < 0)
        {

         document.getElementById('txt3').innerHTML = 'Please Select Image';
          //alert('Please Select Image');
          return false;
        }
        else
        {


              var reader = new FileReader();


                  reader.onload = function (e) {
   // Only For Image Validation
                   // var image = new Image();
                   // //Set the Base64 string return from FileReader as source.
                   // image.src = e.target.result;
                   // image.onload = function () {
                   //     //Determine the Height and Width.
                   //     var height = this.height;
                   //     var width = this.width;

                   //     if(height < 500 && height < 500)
                   //     {
                   //       document.getElementById('txt3').innerHTML = 'Invalid file resolution, upload atleast 500*500';
                   //         $("#show_image_pop").show();
                   //         $("#blah").hide();
                   //        //alert('Please Select Image');
                   //         return false;
                   //     }

                   //   }

                      $('#blah')
                          .attr('src', e.target.result)
                          .width(200)
                          .height(auto);
                  };






           reader.readAsDataURL(input.files[0]);
          $("#show_image_pop").hide();
          $("#show_change_image_pop").show();
          $("#cam_image").hide();

          $("#blah").show();
           document.getElementById('txt3').innerHTML = '';
        }
             }
          }

   $( "#show_image_pop" ).on( "click", function() {
    $( "#img_show" ).trigger( "click" );
   });
   $( "#show_change_image_pop" ).on( "click", function() {
    $( "#img_show" ).trigger( "click" );
   });

   /**/
   function step_number_jump( step_number_class ){
    $("."+step_number_class).trigger("click");
   }

</script>
@endsection
