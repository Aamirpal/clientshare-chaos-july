<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="{{ url('css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ url('css/bootstrap-tour.min.css') }}">
        <link rel="stylesheet" href="{{ url('css/font-awesome.css') }}">
        <link rel="stylesheet" href="{{ url('css/bootstrap-select.min.css') }}">
        <link rel="stylesheet" href="{{ url('css/style.css?q='.env('CACHE_COUNTER', '500')) }}">
        <script src="{{ url('js/jquery.min.js') }}"></script>

        <script rel="text/javascript" src="{{ url('js/bootstrap.min.js') }}"></script>
        <script rel="text/javascript" src="{{ url('js/bootstrap-select.js') }}"></script>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
        <link rel="icon" href="{{ url('favicon.ico') }}" type="image/x-icon"/>
        <link rel="shortcut icon" href="{{ url('favicon.ico') }}" type="image/x-icon"/>
        <link rel="icon" href="{{ env('APP_URL') }}/images/CSProfileImg.png" sizes="32x32" />
        <title>ClientShare</title>
        <script>
            baseurl = "{{ url('/') }}";
        </script>
    </head>
    <body class="feed analytics-page temp block-word">
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <a class="navbar-brand nav-btn" href="{{ url('/dashboard') }}">
                        <img class="img-responsive" alt="" src="{{ url('/',[], env('HTTPS_ENABLE', true)) }}/images/ic_clientShare.svg">
                        <span>Client Share</span>
                    </a>
                    <ul class="nav navbar-nav navbar-right onboarding-right-nav pull-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle nav-btn" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <img src="{{ url('/',[], env('HTTPS_ENABLE', true)) }}/images/ic_settings.svg" alt="">
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ url('/profile') }}">Profile</a>
                                </li>
                                <li><a href="{{ url('/settings') }}">Settings</a>
                                </li>
                                <li><a href="{{ url('/logout') }}">Signout</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">        
                </div>
                <!-- /.navbar-collapse -->
            </div>
            <!-- /.container-fluid -->
        </nav>
        <section class="main-content">
            <div class="analytics-breadcrumb">
                <ol class="breadcrumb container">
                    <li><a href="{{ url('/dashboard') }}">Admin</a></li>
                    <li  class="active"><a href="#">Settings</a></li>
                </ol>
            </div>
            <!-- analytics-breadcrumb -->
            <div class="col-lg-10 col-md-12 col-md-12 col-md-12 mid-content settings_page_content analytics_page">
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 settings_tabs_wrap">
                    <div class="box">
                        <ul class="nav nav-tabs year-tab" role="tablist">
                            <li class="active month-class" getmonthnum="03" role="presentation"><a href="#">Blocked words</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 settings_content_wrap">
                    <div class="box">
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="blockword-management-tab">
                                <div class="heading_wrap">
                                    <h4 class="title">Blocked Words</h4>
                                </div>
                                @if (sizeOfCustom($errors)) 
                                <ul>
                                    @foreach($errors->all() as $error) 
                                    <li>{{ $error }}</li>
                                    @endforeach 
                                </ul>
                                @endif 
                                <div class="tab-inner-content">
                                    <p>Add blocked words below. Blocked words are not allowed in posts on Client Share.</p>
                                    <div class="alert alert-info text-center blockwordsave" style="display:none;"></div>
                                    <form class="domain_management_form set_email_rule" method="post" name="block_form">
                                        {{ csrf_field() }}
                                        <div class="form_field_section">
                                            @php $blocked_word_count = sizeOfCustom($blocked_words)  @endphp
                                            @php $pending = 5-$blocked_word_count @endphp
                                            @if($blocked_word_count == 0 || $blocked_word_count <= 5 )
                                            @if($blocked_word_count == 0)
                                            <?php for ($x = 0; $x <= 4; $x++) { ?>
                                                <div class="input-group blockword-input-grp" style="display: block;">
                                                    <input class="form-control block_word_inp block_up active_save" placeholder="Add word here..." name="block_words[]" block-no="{{$x}}" value="" type="text"> 
                                                    <span class="error-msg error-body text-left block-w-error block-w{{$x}}" style="text-align:left;display:none">field is required.</span>
                                                    <div class="dropdown hover-dropdown">
                                                        <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                            <span></span>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li class="blockword_inp_edit"><a href="#!">Edit word</a></li>
                                                            <li class="del_word"><a href="#!" class="delete-link">Delete word</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <?php } ?>  
                                            @else
                                            @php $a = 3000 @endphp
                                            @foreach($blocked_words as $rule)
                                            <div class="input-group blockword-input-grp blk_grp{{$rule->id}}"  style="display: block;"  >
                                                <input class="form-control  block_word_inp" disabled placeholder="Add word here..." block-no="{{$a}}" name="block_words[]" value="{{$rule->block_words}}" type="text">
                                                <span class="error-msg error-body text-left block-w-error block-w{{$a}}" style="text-align:left;display:none">field is required.</span>
                                                <div class="dropdown hover-dropdown">
                                                    <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                        <span></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="blockword_inp_edit"><a href="#!">Edit word</a></li>
                                                        <li class="del_word" id="wid_{{$rule->id}}" wordid="{{$rule->id}}"><a href="#!" class="delete-link" >Delete word</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <?php $a++ ?>
                                            @endforeach 
                                            <?php for ($x = 0; $x < $pending; $x++) { ?>
                                                <div class="input-group blockword-input-grp" style="display: block;">
                                                    <input class="form-control block_word_inp block_up" placeholder="Add word here..." name="block_words[]" block-no="{{$x}}" value="" type="text"> 
                                                    <span class="error-msg error-body text-left block-w-error block-w{{$x}}" style="text-align:left;display:none">field is required.</span>
                                                    <div class="dropdown hover-dropdown">
                                                        <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                            <span></span>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li class="blockword_inp_edit"><a href="#!">Edit word</a></li>
                                                            <li class="del_word"><a href="#!" class="delete-link">Delete word</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            @endif
                                            @else
                                            @if(isset($blocked_words))
                                            @foreach($blocked_words as $rule)                    
                                            <div class="input-group blockword-input-grp blk_grp{{$rule->id}}"  style="display: block;"  >
                                                <input class="form-control  block_word_inp" disabled placeholder="Add word here..." name="block_words[]" value="{{$rule->block_words}}" type="text">
                                                <div class="dropdown hover-dropdown">
                                                    <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                        <span></span>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="blockword_inp_edit"><a href="#!">Edit word</a></li>
                                                        <li class="del_word" id="wid_{{$rule->id}}" wordid="{{$rule->id}}"><a href="#!" class="delete-link" >Delete word</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            @endforeach
                                            @endif
                                            @endif  
                                            <div class="link-wrap input-group">
                                                <a class="link add_blockword_row" href="#!">Add new</a>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary save-last" disabled>Save</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- tab-content -->
                </div>
                <!-- analytics-column -->
            </div>
            <div class="input-group blockword-input-grp add_blockword_skull" style="display:none">
                <span class="input-group-addon" id="basic-addon1"></span>
                <input type="text" class="form-control " placeholder="Add word here..." name="block_words[]" value=""> 
                <span class="error-msg error-body text-left block-w" style="text-align:left;display:none">field is required.</span>
                <div class="dropdown hover-dropdown">
                    <a href="#" class="dropdown-toggle  dots" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <span></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="domain_inp_edit"><a href="#!">Edit word</a></li>

                    </ul>
                </div>
            </div>
            <script type="text/javascript">
                $('.add_blockword_row').on('click', function () {
                    var count = $('input.block_word_inp').length;
                    if (parseInt(count + 1) > 5) {
                        $('.set_email_rule').removeAttr('action');
                    }
                    domain_skull = $('.add_blockword_skull').clone();
                    domain_skull.show();
                    domain_skull.removeClass('add_blockword_skull');
                    domain_skull.find('input').addClass('block_word_inp');
                    domain_skull.find('input').attr('block-no', parseInt(count + 1));
                    domain_skull.find('.block-w').hide().addClass('block-w-error');
                    domain_skull.find('.block-w').addClass('block-w' + parseInt(count + 1));

                    var ele = $('.form_field_section').find('.blockword-input-grp').length - 1;
                    if (ele < 0) {
                        $('.form_field_section').find('.input-group').eq(0).after(domain_skull);
                    } else {
                        $('.form_field_section').find('.blockword-input-grp').eq(ele).after(domain_skull);
                    }
                    domain_skull.find('.block_word_inp').focus();
                    $(".save-last").attr('disabled', false);
                });
                /* domain_inp_edit start */
                $(document).on("click", ".blockword_inp_edit", function () {
                    $(this).parent().parent().parent().find('.block_word_inp').attr('disabled', false);
                    $(this).parent().parent().parent().find('.block_word_inp').focus();

                    val_temp = $(this).parent().parent().parent().find('.block_word_inp').val();
                    $(this).parent().parent().parent().find('.block_word_inp').val('');
                    $(this).parent().parent().parent().find('.block_word_inp').val(val_temp)
                    $(this).parent().parent().parent().find('.block_word_inp').addClass("edited");
                    $(".save-last").attr('disabled', false);

                });
                /* domain_inp_edit end */

                $(document).on('click', '.del_word', function (e) {
                    var wid = $(this).attr('wordid');
                    if (wid == undefined) {
                        return false;
                    } else {
                        $.ajax({
                            type: "GET",
                            dataType: "html",
                            url: baseurl + '/deleteword/' + wid,
                            success: function (response) {
                                $('#wid_' + wid).hide();
                                $('.blk_grp' + wid).hide();
                                $('div.blockwordsave').text(response).show();
                                setTimeout(function () {
                                    $('.blockwordsave').fadeOut();
                                }, 2000);
                                location.reload(true);

                            }, error: function (xhr, status, error) {
                                alert(error);
                            }
                        });
                    }

                });

                $(".save-last").click(function () {
                    var check_form = $('.set_email_rule').attr('action');
                    $('.block-w-error').hide();
                    if (typeof check_form !== typeof undefined && check_form !== false) {
                        //$('.block-w-error').hide();  
                        /*For unique Field*/
                        var arr = [];
                        $("input.block_word_inp").each(function () {
                            var value = $(this).val();
                            if (value != '') {
                                if (arr.indexOf(value) == -1)
                                    arr.push(value);
                                else
                                    var num = $(this).attr('block-no');
                                $('.block-w' + num).show();
                                $('.block-w' + num).html('duplicate found');
                            }
                        });
                        /*For Required Field*/
                        $('.block_up').each(function () {
                            var empty = $(this).parent().find(".block_word_inp").filter(function () {
                                return this.value === "";
                            });
                            if (empty.length) {
                                var num = $(this).attr('block-no');
                                $('.block-w' + num).show();
                            }
                        });

                        var check_err = $('.block-w-error:visible').length;
                        if (check_err == 0) {
                            var data = [];
                            $(".domain_management_form").find(':input').each(function () {
                                var name = $(this).attr('name');
                                var val = $(this).val();
                                if (typeof name !== typeof undefined && name !== false && typeof val !== typeof undefined) {
                                    //checkboxes needs to be checked:
                                    if (!$(this).is("input[type=checkbox]") || $(this).prop('checked'))
                                        data += (data == "" ? "" : "&") + encodeURIComponent(name) + "=" + encodeURIComponent(val);
                                }
                            });
                            $.ajax({
                                headers: {
                                    "cache-control": "no-cache",
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                type: "POST",
                                url: baseurl + '/add_words',
                                data: data,
                                dataType: "text",
                                async: false,
                                success: function (response) {
                                    $('div.blockwordsave').text(response).show();
                                    setTimeout(function () {
                                        $('.blockwordsave').fadeOut();
                                    }, 2000);
                                    location.reload(true);
                                },
                                error: function (xhr, status, error) {
                                }
      //                                
                            });

                        }
                    } else {

                        /*For Required Field*/
                        $('.block_word_inp').each(function () {
                            var empty = $(this).parent().find(".block_word_inp").filter(function () {
                                return this.value === "";
                            });
                            if (empty.length) {
                                var num = $(this).attr('block-no');
                                $('.block-w' + num).show();
                            }
                        });

                        /*For unique Field*/
                        var arr = [];
                        $("input.block_word_inp").each(function () {
                            var value = $(this).val();
                            if (value != '') {
                                if (arr.indexOf(value) == -1)
                                    arr.push(value);
                                else
                                    var num = $(this).attr('block-no');
                                $('.block-w' + num).show();
                                $('.block-w' + num).html('duplicate found');
                            }
                        });
                        var check_err = $('.block-w-error:visible').length;
                        if (check_err == 0) {
                            var data = [];
                            $(".domain_management_form").find(':input').each(function () {
                                var name = $(this).attr('name');
                                var val = $(this).val();
                                if (typeof name !== typeof undefined && name !== false && typeof val !== typeof undefined) {
                                    //checkboxes needs to be checked:
                                    if (!$(this).is("input[type=checkbox]") || $(this).prop('checked'))
                                        data += (data == "" ? "" : "&") + encodeURIComponent(name) + "=" + encodeURIComponent(val);
                                }
                            });
                            $.ajax({
                                headers: {
                                    "cache-control": "no-cache",
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                type: "POST",
                                url: baseurl + '/add_words',
                                data: data,
                                dataType: "text",
                                async: false,
                                success: function (response) {
                                    $('div.blockwordsave').text(response).show();
                                    setTimeout(function () {
                                        $('.blockwordsave').fadeOut();
                                    }, 2000);
                                    location.reload(true);
                                }
                            });
                        }
                    }
                });

                $(document).ready(function () {
                    var input = $(".block_up").length;
                    if (parseInt(input) <= 5) {
                        $('.set_email_rule').attr('action', baseurl + '/add_words');
                    }

                });

                $(document).ready(function () {
                    $(".active_save").change(function () {
                        $(".save-last").attr('disabled', false);
                    });

                });


            </script>
