$('.community-unlocked-column, .community-locked-column').addClass('hidden');
var category_flag = false;
var tour_guide = {
    'executive': {
            element: ".executive_col_tile",
            title: "Add an Executive Summary to the Client Share.",
            content: "Use this to share key information about your relationship. Consider uploading a welcome video of an account summary document to add detail to the Executive Summary.",
            backdrop: true,
            backdropPadding: 0,
            placement: "bottom",
            template: "<div class='popover tour'><div class='arrow'></div> <h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='tourlink' data-role='next'>GOT IT</button></div></div>"
        },
    'category': {
            element: "#tour4",
            title: "Categories",
            content: "All posts that are added to the Client Share are given a category to help organise them. Members of the Client Share can choose to view posts according to their category.",
            backdrop: true,
            backdropPadding: 0,
            placement: "bottom",
            template: "<div class='popover tour'><div class='arrow'></div> <h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='tourlink' data-role='next'>GOT IT</button></div></div>"
        },
    'post': {
            element: "#tour2",
            content: "Post something to the Client Share so members can see your content when they join. You can add text, links, documents or media. When members join the Client Share, anybody can restrict who can see their posts and set an alert to tell other members that content has been added.",
            backdrop: true,
            backdropPadding: 0,
            placement: "bottom",
            template: "<div class='popover tour'><div class='arrow'></div> <h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='tourlink' data-role='next'>GOT IT</button></div></div>"
        }, 
    'invite': {
            element: "#tour3",
            title: "Invite Members",
            content: "When you are ready, you can invite members to join you on the Client Share.",
            backdrop: true,
            backdropPadding: 0,
            placement: "bottom",
            template: "<div class='popover tour'><div class='arrow'></div> <h3 class='popover-title'></h3><div class='popover-content'></div><div class='popover-navigation'><button class='tourlink endtour' data-role='end'>FINISH TOUR</button></div></div>"
        }
};

var user_wise_tour = {
    'user': [
        'category',
        'post',
        'invite'
    ],
    'admin': [
        'executive',
        'category',
        'post',
        'invite',
    ]
};

function runTour(){
    var tour_steps = Array();
    
    $.each(user_wise_tour[logged_in_user_role], function(){
        tour_steps.push(tour_guide[this]);
    });

    $(function () {
        var wid = $(window).width();
        var tour = new Tour({
            storage: false,
            onShown: function (tour) {
                $('.categories-wrap').attr('style', 'background: #fff none repeat scroll 0 0;border-radius: 6px;padding: 20px;');
                $('.tour-backdrop').after('<div class="sss tour-backdrop" style="background: transparent none repeat scroll 0% 0%; z-index: 1102;"></div>');                
            },
            onHidden: function (tour) {
                $('.categories-wrap').attr('style', '');
                $("div.sss").remove();
            }
        });

        tour.addSteps(tour_steps);

        if (!(navigator.userAgent.toLowerCase().indexOf('mobile') >= 0)) {
            tour.init(); // Initialize the tour        
            tour.start(); // Start the tour
        }
    });
}

var triggerPendingOnboardingSteps = once(function() {
    if($('.wc-step-uncomplete').length){
        $('.wc-step-uncomplete').eq(0).parent().find('p').trigger('click');

    }
});

function validateCategoryData(current_step) {
    $(current_step).find('input.category_value').each(function(){
        if(!$(this).val().trim().length){
            $(this).addClass('has-error');
            $(this).siblings('.category_error').html('This field is required');
        } else {
            $(this).removeClass('has-error');
            $(this).siblings('.category_error').html('');
        }
    });
}

function saveCategoryRequest(categories) {
    var data;
    var category_list = [];
    $('.edit-categories .tour-category-list').each(function(){
        var category_value = $(this).find('.category_value').val();
        category_list.push(category_value);
    });
    var category_list = category_list.sort(); 

    var category_list_duplicate = [];
    for (var i = 0; i < category_list.length - 1; i++) {
        if (category_list[i + 1] == category_list[i]) {
            category_list_duplicate.push(category_list[i]);
        }
    }
    if(category_list_duplicate.length > 0)
    {
        $('.category_error_duplicacy').fadeIn( 400 ).html('Duplicate category exist.').delay(3000).fadeOut( 400 );
        return false;
    }

    data = {
        'space_id': session_space_id,
        'categories': categories
    };

    $.ajax({
        type: 'POST',
        data: data,
        url: baseurl+'/save_categories',
        beforeSend: function(){ $('#welcome_tour .form-submit-loader').removeClass('hidden')},
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        error: function(xhr, status, error) { errorOnPage(xhr, status, error); },
        complete: function(){ 
            addProgressBarsection();
            $('#welcome_tour .form-submit-loader').addClass('hidden');
            $('.category_step').delay(10000).removeClass('wc-step-uncomplete');
            category_flag = true;
        }
    });
    
    return true;
}

function saveCategory(current_step) {
    if(!current_step.find('.edit-categories').length)
        return true;

    validateCategoryData(current_step);
    if($(current_step).find('.has-error').length)
        return false;

    var categories = $('form.category_form').serialize();
    return saveCategoryRequest(categories);
}

function checkCategory(current_step) {
    if(!current_step.find('.edit-categories').length)
        return true;

    $(current_step).find('input.category_value').each(function(){
        if(!$(this).val().trim().length){
            $(this).addClass('has-error');
            $(this).siblings('.category_error').html('This field is required');
        } else {
            $(this).removeClass('has-error');
            $(this).siblings('.category_error').html('');
        }
    });
    return $(current_step).find('.has-error').length?false:true;
}

function addTwitterFeedInput(){
    var add_feed_btn = $('#onboarding_twitter_handles .add-handles');
    var input_count = add_feed_btn.closest('div.twitter-handle-wrap').find('div.twitter-handle').length;
    if(input_count >=3){
        $(add_feed_btn).hide(); 
        return false;
    }    
    var cloned_div = add_feed_btn.closest('.twitter-handle-wrap').find('.twitter-handle:last').clone();
    var twitter_handle_input = $(cloned_div).find('input[name="twitter_handles[]"]');
    $(twitter_handle_input).val('');
    $(cloned_div).find('p.error').remove();
    $(twitter_handle_input).attr('id', 'twitter_handle_' + (input_count));
    $(cloned_div).find('span.link-input-icon p').html((input_count + 1));
    $('#onboarding_twitter_handles .twitter-handle-wrap-column').append(cloned_div);
    var new_input_count = add_feed_btn.closest('div.twitter-handle-wrap').find('div.twitter-handle').length;
    if(new_input_count >=3){
       add_feed_btn.hide(); 
    }
}

function toggleAddTwitterFeedButton(){
    var add_feed_btn = $('a.add-twitter-feed.add-handles');
    var twitter_handle_input = $('.welcome_twitter_feed_modal .modal-body .twitter-handle-wrap').find('input.twitter-feed-input');
    if(twitter_handle_input.length >= 3)
        $(add_feed_btn).hide();
    else
        $(add_feed_btn).show();
}

function updateTourStep(step){
    if(!step)
        return false;

    $.ajax({
        type: 'GET',
        url: baseurl+'/update_tour_step/'+session_space_id+'/'+(parseInt(step)+1),
        error: function(xhr, status, error) { errorOnPage(xhr, status, error); }
    });
}

function runOnBoardingTour(force_trigger) {
    force_trigger = force_trigger ? force_trigger : false;

    if(share_setup_steps <= 10 || force_trigger)
        $('.tour-trigger').trigger('click');
    else if(isMobileDevice() && share_setup_steps <= 10)
        runTour();
}

function saveDomainRequest(form_class, current_step) {
    var form_to_submit = $("."+form_class);
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    var settings = {
        "crossDomain": true,
        "url": baseurl+"/clientshare/"+session_space_id,
        "method": "put",
        "headers": {
            "cache-control": "no-cache",
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        "data": {
            "metadata": {
                "rule": $(form_to_submit).serializeArray()
            },
            "domain_restriction": true,
            "onboarding_domain_flag" : true,
            "_method": "put",
        }
    };

    $('#welcome_tour .form-submit-loader').removeClass('hidden');
    $.ajax(settings).done(function(response) {
        $.each(response.message, function(index, value) {
            index = index.split(".");
            input = $(form_to_submit).find('input[name="rule[]"]').eq(index[0]);
            input.parent().find('.error-msg').remove();
            input.after('<span class="error-msg text-left">' + value + '</span>');
            input.addClass("has-error");
        });
        if(response.code != 401)        
            updateNextStep(current_step);
            addProgressBarsection();
        
        $('#welcome_tour .form-submit-loader').addClass('hidden');
    });
}

function retrictDomin(current_step, check){
    $.ajax({
        type: 'POST',
        data: {
            'data':{
                'domain_restriction': check
            },
            'space_id': session_space_id
        },
        url: baseurl+'/restict_domain',
        beforeSend: function(){ $('#welcome_tour .form-submit-loader').removeClass('hidden')},
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(response) { 
            updateNextStep(current_step);
            addProgressBarsection();
        },
        error: function(xhr, status, error) { errorOnPage(xhr, status, error); },
        complete: function(){ $('#welcome_tour .form-submit-loader').addClass('hidden')}
    });
}

function saveDomain(current_step){
    if(!current_step.find('form.save-domain').length)
        return true;
    if(!$('.restrict-domain-check').prop("checked"))
        retrictDomin(current_step, false);
    else
        saveDomainRequest('save-domain', current_step);
}

function updateNextStep(current_step) {
    current_step.addClass('hidden');
    current_step.next($('.welcome-cs-popup')).removeClass('hidden');
    $('.banner-image-error').text('');
    $('#banner-image, #edit-banner-image').val('');
    updateTourStep(current_step.attr('data-step'));
}

function addCategory(element){
    category_layout = $(element).closest('.welcome-tour-categorie-col').find('.tour-category-list');
    category_layout_clone = category_layout.eq(2).clone();
    category_layout_clone.find('input').val('');
    category_layout_clone.find('input').attr('name', 'category_'+random());
    category_layout_clone.find('.category-count').html(0);
    category_layout_clone.addClass('tour-category-list-new-add');
    category_layout_clone.append('<span class="wc-categories-delete"><img src="'+baseurl+'/images/ic_deleteBlue.svg"></span>');
    $('.tour-category-list').eq(category_layout.length-1).after(category_layout_clone);
}

function displayPanel() {
    pending_steps = $('.wc-step-done.wc-step-uncomplete');
    
    if(!pending_steps.length) 
        return false;
    
    $('.finish-setup-alert').find('span').html(pending_steps.length);
    $('.finish-setup-alert').parent().show();
    triggerPendingOnboardingSteps();
}

function addProgressBarsection(refrence){
    if(!refrence) {
        refrence = 'default';
    }
    $.ajax({
        type: 'GET',
        url: baseurl+'/get_share_profile_status?space_id='+session_space_id,
        success: function (response) {
            if(response.data.space_admin) {
                $('.btn-invite-back').remove();
            }
            if(response.data.progress == parseInt(100) || response.data.space_users) {
                $('#save_post_btn_new').text('Post');
                $('.community-locked-column').remove();
                $('.community-unlocked-column').removeClass('hidden');
                $('.user-profile-status-col, .finish-setup-alert').remove();
                return false;
            }
            if(response.result){
                var parser = new DOMParser;
                var source = $("#progress-bar-section").html();
                var template = Handlebars.compile(source);
                response.baseurl = baseurl;
                if((response.data.category && response.data.category_flag) || category_flag || share_setup_steps >= 5) {
                    response.data.category = true;
                } else {
                    response.data.category = false;
                }
                
                var html = template(response);
                if($(window).width() >= 767){ 
                    $('.user-profile-status-show').html(html);    
                } else {
                    $('.user-profile-status-show-mobile').html(html); 
                }

                if(response.data.space_admin_data.length > 0) {
                    $('.admin-invite-result-box').removeClass('hidden');
                    appendSpaceUserInInviteAdminScreen(response.data.space_admin_data);
                }
                $('.community-locked-column').removeClass('hidden');
                $('.community-unlocked-column').addClass('hidden');
                $(".cdev").circlos();
                if(refrence == 'add_post' && response.data.posts_count < 5) {
                    $('.add_post_trigger').trigger('click');
                }
                if(response.data.posts_count == 4) {
                   $('#save_post_btn_new').text('Finish');
                }
                if(response.data.posts_count == 5) {
                   $('#save_post_btn_new').text('Post');
                }
                displayPanel();
            }
        },
        error: function(xhr, status, error) { errorOnPage(xhr, status, error); }
    });
}

function showOnboardingPopup(step){
    $('div.welcome-cs-popup').addClass('hidden');
    if(step<10) {
        $('div.welcome-cs-popup[data-step="'+step+'"]').removeClass('hidden');
        runOnBoardingTour(true);
    } else {
        $('.add_post_trigger').trigger('click');
    }
}

function sendOnboardingQuickLinks(btn){ 
     var link_count = 0;
     var reference = $(btn).closest('.quick_links_form');
     var pattern = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
     var quick_links_error = reference.find('.quick_links_error');
     quick_links_error.text('');
     var hyperlink = reference.find('.hyperlink[name="hyperlink[]"]');
     var link_name = reference.find('.link_name[name="link_name[]"]');
     for (i=0; i<hyperlink.length; i++){
            if(hyperlink[i].value.trim() != '' && link_name[i].value.trim() != ''){
                   link_count++;
            }
            if(hyperlink[i].value.trim() != '' && link_name[i].value.trim() == ''){
                   quick_links_error.text('Please enter link name.');
                   return false;
            }
            if(hyperlink[i].value.trim() == '' && link_name[i].value.trim() != ''){
                   quick_links_error.text('Please add hyperlink.');
                   return false;
            }
            if(hyperlink[i].value.trim() != '' && !pattern.test(hyperlink[i].value.trim())){
                   quick_links_error.text('Please add correct hyperlink.');
                   return false;
            }
     }
     //if(link_count >= 2){ 
        var twitter_handle_last = $('#twitter_handle_2').val();
        if((typeof twitter_handle_last != 'undefined' && twitter_handle_last.length < 2) || twitter_handle_last == '')
          $('#twitter_handle_2').closest('.twitter-input-col').find('.remove-handle').trigger('click');

        reference.find('.btn-quick-links-button').attr('disabled',true);
        reference.submit(); 
     /*}else{
        quick_links_error.text('You need to add a minimum of 2 quick links to your client share.');
        return false;
     }*/
}

function validateAdminInviteEmail(ele) {
    $('.admin-invite-error').remove();
    $('.admin-invite-box .twitter-feed-input').removeClass('has-error');
    var filter = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    var email_flag = validate_flag = false;
    $('.admin-invite-box .twitter-input-col input').each(function(index, value) {
        var email = $(this).val();
        if(email.trim()) {
            email_flag = true;
        }
        if (email.trim() != '' && !filter.test(email.trim())) {
            $(this).addClass('has-error');
            $(this).parent().append('<span class="admin-invite-error">Please enter a correct email format.</span>');
            validate_flag = true;
            setTimeout(function(){
              $('.edit-admin-invite').find('.admin-invite-error').remove();
              $('.admin-invite-box .twitter-feed-input').removeClass('has-error');
            }, 4000);
        }
    });
    if(!email_flag) {
        if ($('.admin-invite-result-box').html().trim() != "") {
            current_step = $(ele).closest('.welcome-cs-popup');
            updateNextStep(current_step);
            return false;
        }
        $('.edit-admin-invite').append('<span class="admin-invite-error">Please enter at least one email address.</span>');
        setTimeout(function(){
              $('.edit-admin-invite').find('.admin-invite-error').remove();
            }, 3000);
        return false;
    }
    if(validate_flag) {
        return false;
    }
    return true;
}

function appendSpaceUserInInviteAdminScreen(admin_data) {
    var html = '';
    $.each(admin_data, function(i, item) {
        html += '<div class="onboarding-invite-status">';
        html += '<p>'+admin_data[i].user.email+'</p>';
        if(admin_data[i].metadata.invitation_code == 0) {
            html += '<p class="invite-result">Pending</p></div>'; 
        }
         if(admin_data[i].metadata.invitation_code == 1 
            && typeof admin_data[i].metadata.user_profile != 'undefined'
            && admin_data[i].metadata.user_profil != '') {
             html += '<p class="invite-result">Accepted</p></div>'; 
        }
    })
    $('.admin-invite-result-box').html(html);
    $('.admin-invite-box .twitter-input-col').not(':first').remove();
}

$(document).ready(function () {
    addProgressBarsection();
    toggleAddTwitterFeedButton();

    $('.category-count').each(function(){
        val = $(this).closest('.tour-category-list').find('.category_value').val();
        $(this).html(val.length);
    });
});

$(document).on('click', '.finish-setup-alert a', function(){
    pending_steps = $('.wc-step-done.wc-step-uncomplete');
    step = pending_steps.eq(0).attr('data-step');
    showOnboardingPopup(step);
});

$(document).on('click', '.admin-progress-icon a', function(){
    $('div.welcome-cs-popup').addClass('hidden');
    step = $(this).attr('data-step');
    if(typeof step == 'undefined' || !step) {
        return false;
    }
    showOnboardingPopup(step);
});

$(document).on('click', '.wc-step-col p', function(){
    $('div.welcome-cs-popup').addClass('hidden');
    step = $(this).parent().find('.wc-step-done').attr('data-step');
    if(typeof step == 'undefined' || !step) {
        return false;
    }
    showOnboardingPopup(step);
});

$(document).on('click', '.endtour', function () {
    if (show_feedback) $('#feedback-popup').modal('show');
    $.ajax({
        type: "GET",
        url: baseurl + '/update_showtour',
        success: function (response) {},
        error: function (xhr, status, error) {}
    });
});

$(document).on('click', '.add-admin-invite-link', function(event){ 
    var html = '<div class="twitter-input-col"><input class="form-control twitter-feed-input" name="admin_invite[]" placeholder="Email address" type="text"></div>';
    $('.admin-invite-box').append(html);
});

$(document).on('click', '.btn-invite-handle', function(event){
    current_step = $(this).closest('.welcome-cs-popup');
    var validate_data = validateAdminInviteEmail(this);
    if(!validate_data) {
        return false;
    }
    email_array = [];
    var duplicate_email_flag = true;
    $('.admin-invite-box .twitter-input-col input').each(function(index, value) {
        var email = $(this).val();
        if($.inArray(email, email_array) !== -1) {
            $(this).addClass('has-error');
            $(this).parent().append('<span class="admin-invite-error">Email already mentioned in input.</span>');
            setTimeout(function(){
                $('.edit-admin-invite').find('.admin-invite-error').remove();
                $('.admin-invite-box .twitter-feed-input').removeClass('has-error');
            }, 4000);
            duplicate_email_flag = false;
        }
        if(email.trim() != '') { 
            email_array[index] = email;
        }
    });
    if(!duplicate_email_flag) {
        return false;
    }
    var mail = [];
    mail[0] = $('.admin_invite_body').val();
    var subject = $('.admin_invite_subject').val();
    var settings = {
        "async": true,
        "crossDomain": true,
        "url": baseurl+"/invite_admin_user",
        "method": "post",
        "headers": {
            "cache-control": "no-cache",
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        "data": {
            "share_id": session_space_id,
            "admin_invite": email_array,
            "user": {
                 "first_name": '',
                 "last_name": '',
                 "user_type_id": 2,
                 'onboarding' : true,
                 "subject": subject
            },
        "mail": {
             "body": mail
            }
        }
    }
    $('#welcome_tour .form-submit-loader').removeClass('hidden')
    $.ajax(settings).done(function(response) {
        $('#welcome_tour .form-submit-loader').addClass('hidden');
        if(typeof response.code != 'undefined' && response.code == 400) {
            $(".admin-invite-box div:nth-child("+response.key+")").find('input').addClass('has-error');
            $(".admin-invite-box div:nth-child("+response.key+")").append('<span class="admin-invite-error">'+response.message+'</span>');
            setTimeout(function(){
                $('.edit-admin-invite').find('.admin-invite-error').remove();
                $(".admin-invite-box div:nth-child("+response.key+")").find('input').removeClass('has-error');
            }, 5000);
        } else if(response.code == 200) {
            if(response.space_admin.length > 0) {
                $('.admin-invite-result-box').removeClass('hidden');
                appendSpaceUserInInviteAdminScreen(response.space_admin);
            }
            $('.admin-invite-box .twitter-feed-input').val('');
            updateNextStep(current_step);
        }
    });
});

$(document).on('click', '.tour-next-step', function(event){
    current_step = $(this).closest('.welcome-cs-popup');
    
    if(!saveCategory(current_step, event))
        return false;

    if(!saveDomain(current_step, event))
        return false;

    updateNextStep(current_step);
});

$(document).on('click', '.tour-previous-step', function(){
    $('.tour-domain-list span.error-msg').remove();
    $('.tour-domain-list input').removeClass('has-error');
    current_step = $(this).closest('.welcome-cs-popup');
    current_step.addClass('hidden');
    current_step.prev($('.welcome-cs-popup')).removeClass('hidden');
});

$(document).on('click', '.add-domain', function(){
    domain_layout = $(this).closest('.welcome-tour-domain-col').find('.tour-domain-list');
    domain_layout_clone = domain_layout.eq(0).clone();
    domain_layout_clone.find('input').val('');
    domain_layout_clone.find('input').removeClass('has-error');
    domain_layout_clone.find('input').removeAttr('readonly');
    domain_layout_clone.find('input').attr('name', 'rule[]');
    domain_layout_clone.append('<span class="wc-domain-delete"><img src="'+baseurl+'/images/ic_deleteBlue.svg"></span>');
    domain_layout_clone.find('.link-input-icon p').html(domain_layout.length+1);
    domain_layout_clone.find('.error-msg').remove();
    $('.tour-domain-list').eq(domain_layout.length-1).after(domain_layout_clone);
});

$(document).on('click', '.add-tour-category', function(){
    addCategory(this);

    if($('.tour-category-list').length%2 != 0)
        addCategory(this);
});

$(document).on('click', '.wc-categories-delete', function(){
    $(this).closest('.tour-category-list').remove();
});

$(document).on('click', '.wc-domain-delete', function(){
    $(this).closest('.tour-domain-list').remove();
    $('.tour-domain-list').each(function(index){
        $(this).find('.link-input-icon p').html(index+1)
    });
});

$(document).on('click', '.add_post_trigger', function(){
    current_step = $(this).closest('.welcome-cs-popup');
    updateTourStep(current_step.attr('data-step'));
    current_step.addClass('hidden');
    current_step.prev($('.welcome-cs-popup')).removeClass('hidden');
    $('#welcome_tour').modal('hide');
    $('.add_post_form .post_subject').trigger('click');

});

$(document).on('click', '#onboarding_twitter_handles .add-handles', function(ele){
    addTwitterFeedInput(ele);
});

$(document).on('click', '.btn-twitter-handle', function () {
    $('.tour-domain-list span.error-msg').remove();
    $('.tour-domain-list input').removeClass('has-error');
    $('.tour-twitter-list').each(function(index){
        $(this).find('.twitter-input-col input').attr('id','twitter_handle_'+index);
    });
    $('#onboarding_twitter_handles').submit();
});

$(document).on('click', 'form#update_welcome_share_logo .onboarding_company_logo', function(){
    $('.twitter-input-col input').removeClass('has-error');
    $('.twitter-error').remove();
    var seller_twitter_name = $('.seller_twitter_name').val().trim();
    var buyer_twitter_name = $('.buyer_twitter_name').val().trim();
    if(seller_twitter_name)
        $('#onboarding_twitter_handles #twitter_handle_0').val(seller_twitter_name);
    if(!seller_twitter_name && buyer_twitter_name)
        $('#onboarding_twitter_handles #twitter_handle_0').val(buyer_twitter_name);
      
});

$(document).on('click', 'form#update_welcome_share_banner .onboarding_company_logo', function(){
    var seller_twitter_name = $('.seller_twitter_name').val().trim();
    var buyer_twitter_name = $('.buyer_twitter_name').val().trim();
    var twitter_handle_last = $('#twitter_handle_2').val();
    if(seller_twitter_name && buyer_twitter_name)
    {
        $('#onboarding_twitter_handles .add-handles').trigger('click');
        $('#onboarding_twitter_handles #twitter_handle_1').val(buyer_twitter_name);
        if($('#twitter_handle_1').val() != '' && typeof twitter_handle_last != 'undefined' && twitter_handle_last.length < 2)
            $('#twitter_handle_2').val('');
    }
    if((typeof twitter_handle_last != 'undefined' && twitter_handle_last.length < 2) || twitter_handle_last == '')
        $('#twitter_handle_2').parent().find('.remove-handle').trigger('click');
        
});

$(document).on('submit','#onboarding_twitter_handles',function(e){
       var validate = validateInputs('welcome_twitter_feed_modal');
       if(!validate)
        return false;

       $('.welcome_twitter_feed_modal .twitter-feed-input').removeClass('has-error');
       $('.btn-twitter-handles').attr('disabled', true);
       e.preventDefault();
       var form =$(this);
       var form_data = new FormData(form[0]);
       $.ajax({
              type: 'post',
              url: baseurl+'/save_twitter_feed?space_id='+session_space_id,
              data:  form_data,
              async: true,
              success: function (response) {
                 if(response.result){ 
                      $('.btn-twitter-handles').attr('disabled', false);
                      current_step = form.closest('.welcome-cs-popup');
                      updateTourStep(current_step.attr('data-step'));
                      current_step.addClass('hidden');
                      current_step.next($('.welcome-cs-popup')).removeClass('hidden');
                      getTwitterFeeds();
                      addProgressBarsection();
                 } else {
                    $('.welcome_twitter_feed_modal #twitter_handle_'+response.key).addClass('has-error');
                    $('.welcome_twitter_feed_modal #twitter_handle_'+response.key).closest('div.twitter-handle')
                    .append($('<p class="error twitter_links_error">'+response.error+'</p>'));
                 } 
              },
              error: function (xhr, status, error) {
                logErrorOnPage(xhr, status, error, 'onboarding_twitter_handles');
              },
              cache: false,
              contentType: false,
              processData: false
            });
        return false;
});

$(document).on('click', '.slider.round', function() {
    $('.slider-toggle-on-content').toggleClass('hidden');
    $('.slider-toggle-off-content').toggleClass('hidden');
});

$(document).on('hidden.bs.modal', '.modal', function () {
    $('.modal:visible').length && $(document.body).addClass('modal-open');
});