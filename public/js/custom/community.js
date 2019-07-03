function show_community_user_in_modal(user_block) {
    var bg_image = user_block.find('.community-user-memmber').css('background-image');
    var user_name = user_block.find('.community-member-name').text();
    var user_title = user_block.find('.community-member-designation').text();
    var user_bio = user_block.find('.community-user-des > p.full_bio').text();
    var user_phone = user_block.parent().find('.community-user-phone > a').attr('href');
    if (typeof user_phone !== 'undefined') {
	user_phone = user_phone.substring(4);
    }
    var user_email = user_block.parent().find('.community-user-mail > a').attr('href');
    if (typeof user_email !== 'undefined') {
	user_email = user_email.substring(7);
    }
    var linkedin_url = user_block.parent().find('.community-user-linkdin > a').attr('href');
    var company_name = user_block.find('.community-member-company').text();
    
    $('.community-member-detail').find('.modal_image_section').html('<span style=\'background-image:' + bg_image + '\'></span>');
    $('.community-member-detail').find('.member_info').find('h4').html(user_name);
    $('.community-member-detail').find('.member_info').find('h5').html(user_title);
    
    $('.community-member-detail').find('.member_info').find('h6').eq(0).html(company_name);
    $('.community-member-detail').find('.member_info').find('p').html(user_bio);
    
    var linkedin_element = $('.community-member-detail').find('.linkedin-link');
    var email_element = $('.community-member-detail').find('.email-link');
    var phone_element = $('.community-member-detail').find('.call-link');

    if (typeof linkedin_url !== 'undefined') {
	linkedin_element.html('<a target="_blank" href="' + linkedin_url + '">' + linkedin_url + '</a>');
	linkedin_element.show();
    } else {
	linkedin_element.hide();
    }
    if (typeof user_email !== 'undefined') {
	email_element.html('<a href="mailto:' + user_email + '">' + user_email + '</a>');
	email_element.show();
    } else {
	email_element.hide();
    }
    if (typeof user_phone !== 'undefined') {
	phone_element.html('<a href="tel:' + user_phone + '">' + user_phone + '</a>');
	phone_element.show();
    } else {
	phone_element.hide();
    }
    $('#member_info_modal').modal('show');

}

$(document).on('click', '.community-user-detail', function () {
    show_community_user_in_modal($(this));
});
$("#member_info_modal").on('hide.bs.modal', function () {
    $('.community-member-detail').find('.modal_image_section').html('');
    $('.community-member-detail').find('.member_info').find('h4').html('');
    $('.community-member-detail').find('.member_info').find('h5').html('');
    $('.community-member-detail').find('.member_info').find('h6').eq(0).html('');
    $('.community-member-detail').find('.member_info').find('p').html('');
    
    $('.community-member-detail').find('.linkedin-link').html('');
    $('.community-member-detail').find('.email-link').html('');
    $('.community-member-detail').find('.call-link').html('');
    $('.community-member-detail').find('.linkedin-link').hide();
    $('.community-member-detail').find('.email-link').hide();
    $('.community-member-detail').find('.call-link').hide();
});

function load_json_community_users() {
    var offset = $('.community_member_block').length;
    var search = $("#search_auto").val();
    var check_company_id = $("#search_company_id").val();
    var company_id = check_company_id ? check_company_id : ''; 
    $.ajax({
	type: "GET",
	url: baseurl + '/community_members?space_id=' + current_space_id + '&offset=' + offset + '&company_id=' + company_id + '&search=' + search,
	success: function (data) {
	    if(!data) {
		community_users_ajax = true;
	    } else {
		community_users_ajax = false;
	    }
	    $('.community_member_block').parent().append(data);
	},
	error: function (xhr, status, error) {
	    alert('Could not load more users');
	}
    });
}

function ajax_search_community_users(search) {
    var search = $("#search_auto").val();
    var check_company_id = $("#search_company_id").val();
    var company_id = check_company_id ? check_company_id : ''; 
    $.ajax({
        type: "GET",
        url: baseurl + '/community_members?space_id=' + current_space_id + '&company_id=' + company_id + '&search=' + search,
        success: function (data) {
            $('.ajax_search_community_member_blocks').html(data);
            var search_community_users_list = '';
            var member_name = '';
            var member_profile_image = '';
            var member_designation = '';
            $('.ajax_search_community_member_blocks .community-user-detail').each(function (index, element) {
                member_name = $(this).find('.community-member-name');
                member_profile_image = $(this).find('.community-user-memmber').css('background-image');
                member_designation = $(this).find('.community-member-designation').html();
                
                search_community_users_list += '<li><a href="#" class="ajax-user" data-id="' + member_name.data('id') + '">\n\
                <span class="dp pro_pic_wrap" style="background-image:' + member_profile_image.replace(/"/g, '\'') +'"></span>\n\
                <span class="notify-detail"><strong>' + member_name.html() + '</strong><br>' + member_designation +'</span>\n\
                </a></li>';
            });

            $('#community_search_results_list').html(search_community_users_list);
            if(search_community_users_list !== '') {
                $('.search_form').addClass('open');
            }
        }
    });

}

$('.mail_body').autosize()
   $(document).ready(function(){
    $(".search_form").hide();
    $("#search_auto").show();
        $("form").validate();
        $(document).on('submit', '.search_form', function () {
          $("#search_auto").rules("add", {
            required: true,
            messages: {
                required: "Search field can not be empty"
            }
        });
        if (!$("form").valid()) {
            return false;
        }
        });
        $("#myModalInvite").on('hide.bs.modal', function(){
          location.reload();
        });
        $("input[name=first_name]").on('change, keyup paste input',function() {
              $(".mailbody").find('span').eq(0).show();
              $(".mailbody").find('span').eq(1).html(' '+$(this).val()+',' );
         });
         $(".mailbody").on('focus', function() {
           $('.mail_body').get(0).selectionStart = $('.mail_body').html().length;
           $('.mail_body').get(0).selectionEnd = $('.mail_body').html().length;
           $('.mail_body').focus();
         });
        });
   $(".invite-btn").click(function() {
   $(".mail_body").height(140);
});
   
   
var community_users_ajax = false;
$(window).bind('scroll', function () {
    if ($(window).scrollTop() >= $('.community_inner_content').offset().top + $('.community_inner_content').outerHeight() - window.innerHeight) {
	if(!community_users_ajax) {
	    community_users_ajax = true;
	    load_json_community_users();
	}
    }
});

$(".search_icon_blue").on("click", function (e) {
    e.preventDefault();
    var search_form = $(".search_form");
    var search_auto_value = $("#search_auto").val()
    if (search_form.is(':visible')) {
        (search_auto_value !== '') ? search_form.submit() : search_form.hide();
        return;
    }
    search_form.show();
     $("#search_auto").focus();
   });

$(document).on("click", ".ajax-user", function (e) {
    e.preventDefault();
    var ajax_user_id = $(this).data('id');
    $('.ajax_search_community_member_blocks .community-user-detail[data-id=' + ajax_user_id + ']').trigger('click');
});

$('#search_auto').keyup(function (e) {
    // Escape key
    if (e.keyCode === 27) {
        $(".search_form").hide();
        return;
    }
    if ((e.keyCode > 64 && e.keyCode < 91) || (e.keyCode > 96 && e.keyCode < 123))
        ajax_search_community_users($(this).val());
});


$(document).keyup(function (e) {
    if (e.keyCode === 191) {
        if ( $('input:focus').length === 0 && $('select:focus').length === 0 && $('textarea:focus').length === 0) {
            $(".search_form").show();
            $("#search_auto").focus();
        }
    }
});

$(document).ready(function () {
    if($("#search_auto").val() !== '') {
        $(".search_form").show();
    }
});


   $('#myModal0').on('shown.bs.modal', function () {
   $('#myInput').focus()
   });

   !function(e){var t=function(t,n){this.$element=e(t),this.type=this.$element.data("uploadtype")||(this.$element.find(".thumbnail").length>0?"image":"file"),this.$input=this.$element.find(":file");if(this.$input.length===0)return;this.name=this.$input.attr("name")||n.name,this.$hidden=this.$element.find('input[type=hidden][name="'+this.name+'"]'),this.$hidden.length===0&&(this.$hidden=e('<input type="hidden" />'),this.$element.prepend(this.$hidden)),this.$preview=this.$element.find(".fileupload-preview");var r=this.$preview.css("height");this.$preview.css("display")!="inline"&&r!="0px"&&r!="none"&&this.$preview.css("line-height",r),this.original={exists:this.$element.hasClass("fileupload-exists"),preview:this.$preview.html(),hiddenVal:this.$hidden.val()},this.$remove=this.$element.find('[data-dismiss="fileupload"]'),this.$element.find('[data-trigger="fileupload"]').on("click.fileupload",e.proxy(this.trigger,this)),this.listen()};t.prototype={listen:function(){this.$input.on("change.fileupload",e.proxy(this.change,this)),e(this.$input[0].form).on("reset.fileupload",e.proxy(this.reset,this)),this.$remove&&this.$remove.on("click.fileupload",e.proxy(this.clear,this))},change:function(e,t){if(t==="clear")return;var n=e.target.files!==undefined?e.target.files[0]:e.target.value?{name:e.target.value.replace(/^.+\\/,"")}:null;if(!n){this.clear();return}this.$hidden.val(""),this.$hidden.attr("name",""),this.$input.attr("name",this.name);if(this.type==="image"&&this.$preview.length>0&&(typeof n.type!="undefined"?n.type.match("image.*"):n.name.match(/\.(gif|png|jpe?g)$/i))&&typeof FileReader!="undefined"){var r=new FileReader,i=this.$preview,s=this.$element;r.onload=function(e){i.html('<img src="'+e.target.result+'" '+(i.css("max-height")!="none"?'style="max-height: '+i.css("max-height")+';"':"")+" />"),s.addClass("fileupload-exists").removeClass("fileupload-new")},r.readAsDataURL(n)}else this.$preview.text(n.name),this.$element.addClass("fileupload-exists").removeClass("fileupload-new")},clear:function(e){this.$hidden.val(""),this.$hidden.attr("name",this.name),this.$input.attr("name","");if(navigator.userAgent.match(/msie/i)){var t=this.$input.clone(!0);this.$input.after(t),this.$input.remove(),this.$input=t}else this.$input.val("");this.$preview.html(""),this.$element.addClass("fileupload-new").removeClass("fileupload-exists"),e&&(this.$input.trigger("change",["clear"]),e.preventDefault())},reset:function(e){this.clear(),this.$hidden.val(this.original.hiddenVal),this.$preview.html(this.original.preview),this.original.exists?this.$element.addClass("fileupload-exists").removeClass("fileupload-new"):this.$element.addClass("fileupload-new").removeClass("fileupload-exists")},trigger:function(e){this.$input.trigger("click"),e.preventDefault()}},e.fn.fileupload=function(n){return this.each(function(){var r=e(this),i=r.data("fileupload");i||r.data("fileupload",i=new t(this,n)),typeof n=="string"&&i[n]()})},e.fn.fileupload.Constructor=t,e(document).on("click.fileupload.data-api",'[data-provides="fileupload"]',function(t){var n=e(this);if(n.data("fileupload"))return;n.fileupload(n.data());var r=e(t.target).closest('[data-dismiss="fileupload"],[data-trigger="fileupload"]');r.length>0&&(r.trigger("click.fileupload"),t.preventDefault())})}(window.jQuery)