! function(e) {
        var t = function(t, n) {
            this.$element = e(t), this.type = this.$element.data("uploadtype") || (this.$element.find(".thumbnail").length > 0 ? "image" : "file"), this.$input = this.$element.find(":file");
            if (this.$input.length === 0) return;
            this.name = this.$input.attr("name") || n.name, this.$hidden = this.$element.find('input[type=hidden][name="' + this.name + '"]'), this.$hidden.length === 0 && (this.$hidden = e('<input type="hidden" />'), this.$element.prepend(this.$hidden)), this.$preview = this.$element.find(".fileupload-preview");
            var r = this.$preview.css("height");
            this.$preview.css("display") != "inline" && r != "0px" && r != "none" && this.$preview.css("line-height", r), this.original = {
               exists: this.$element.hasClass("fileupload-exists"),
               preview: this.$preview.html(),
               hiddenVal: this.$hidden.val()
           }, this.$remove = this.$element.find('[data-dismiss="fileupload"]'), this.$element.find('[data-trigger="fileupload"]').on("click.fileupload", e.proxy(this.trigger, this)), this.listen()
        };
        t.prototype = {
            listen: function() {
                this.$input.on("change.fileupload", e.proxy(this.change, this)), e(this.$input[0].form).on("reset.fileupload", e.proxy(this.reset, this)), this.$remove && this.$remove.on("click.fileupload", e.proxy(this.clear, this))
            },
            change: function(e, t) {
                if (t === "clear") return;
                var n = e.target.files !== undefined ? e.target.files[0] : e.target.value ? {
                    name: e.target.value.replace(/^.+\\/, "")
                } : null;
                if (!n) {
                    this.clear();
                    return
                }
                this.$hidden.val(""), this.$hidden.attr("name", ""), this.$input.attr("name", this.name);
                if (this.type === "image" && this.$preview.length > 0 && (typeof n.type != "undefined" ? n.type.match("image.*") : n.name.match(/\.(gif|png|jpe?g)$/i)) && typeof FileReader != "undefined") {
                    var r = new FileReader,
                        i = this.$preview,
                        s = this.$element;
                    r.onload = function(e) {
                        i.html('<img src="' + e.target.result + '" ' + (i.css("max-height") != "none" ? 'style="max-height: ' + i.css("max-height") + ';"' : "") + " />"), s.addClass("fileupload-exists").removeClass("fileupload-new")
                    }, r.readAsDataURL(n)
                } else this.$preview.text(n.name), this.$element.addClass("fileupload-exists").removeClass("fileupload-new")
            },
            clear: function(e) {
                this.$hidden.val(""), this.$hidden.attr("name", this.name), this.$input.attr("name", "");
                if (navigator.userAgent.match(/msie/i)) {
                    var t = this.$input.clone(!0);
                    this.$input.after(t), this.$input.remove(), this.$input = t
                } 
                else 
                    this.$input.val("");

                this.$preview.html(""), this.$element.addClass("fileupload-new").removeClass("fileupload-exists"), e && (this.$input.trigger("change", ["clear"]), e.preventDefault())
            },
            reset: function(e) {
               this.clear(), this.$hidden.val(this.original.hiddenVal), this.$preview.html(this.original.preview), this.original.exists ? this.$element.addClass("fileupload-exists").removeClass("fileupload-new") : this.$element.addClass("fileupload-new").removeClass("fileupload-exists")
            },
            trigger: function(e) {
               this.$input.trigger("click"), e.preventDefault()
            }
        }, e.fn.fileupload = function(n) {
            return this.each(function() {
                var r = e(this),
                    i = r.data("fileupload");
                i || r.data("fileupload", i = new t(this, n)), typeof n == "string" && i[n]()
            })
        }, e.fn.fileupload.Constructor = t, e(document).on("click.fileupload.data-api", '[data-provides="fileupload"]', function(t) {
            var n = e(this);
            if (n.data("fileupload")) return;
            n.fileupload(n.data());
            var r = e(t.target).closest('[data-dismiss="fileupload"],[data-trigger="fileupload"]');
            r.length > 0 && (r.trigger("click.fileupload"), t.preventDefault())
        })
    }(window.jQuery)

function getSuggeCount(texVal){
    var texlen = texVal.length;
    $('.subButton').attr("disabled", false);
    $('.subButton').removeClass('disabled');
    $(".suggCount").text(texlen+'/500');  
}

function getCommCount(texVal){
    var texlen = texVal.length;
    $('.subButton').attr("disabled", false);
    $('.subButton').removeClass('disabled'); 
    $(".commCount").text(texlen+'/500');  
}

function sendPdfLink(){
    console.log(feedback_pdf_link);
    $.ajax({
        type: "GET",
        url: feedback_pdf_link +'?send_email=true',
        success: function( response ) {
            //
        }
    });
}



$(document).ready(function(){
    $(".feedback_form").on('submit', function(){
        if(!$('.rating').is(':checked')) {     
            $('.rating-wrap').parent().find('.error-msg').remove();
            $('.rating-wrap').after('<span class="error-msg error-body text-left rating-error" style="text-align: left;">Rating is mandatory</span>');
            error = 1;
        }else{
            error = 0;
        }
        if( error ){
            return false;
        } else {      
            return true;
        }
    });

    $(function(){
        $('[data-toggle="popover"]').popover()
    });

    $(document).on("click", ".next_year", function() { 
        var cur_year = $(".curnt_year").html();   
        var real_year =  new Date().getFullYear();    
        var  year = parseInt(cur_year) + 1 ;         
         
        if(parseInt(year) <= parseInt(real_year)){    
            $(".curnt_year").html(year);
        }
    });

    /*Get Last Year*/   
    $(document).on("click", ".last_year", function() {    
        var cur_year = $(".curnt_year").html();   //alert(cur_year);
        var real_year =  '2016';   
        var year = parseInt(cur_year) - 1 ;  
        if(parseInt(year) >= parseInt(real_year)){ 
            $(".curnt_year").html(year);
        }
    });

    /* Remove download link of feedback pdf */
    if (!(navigator.userAgent.match(/(iPod|iPhone|iPad)/))) {
        $('.download_feedback_pdf').show();
    }
    
    /* Remove download link of feedback pdf */
    var mouse_is_outside_navi = false;

    $('#a_nav').hover(function(){
        mouse_is_outside_navi=true; 
    }, function(){ 
        mouse_is_outside_navi=false; 
    });

    $("body").mouseup(function(){
        if( (!mouse_is_outside_navi) && $('#bs-example-navbar-collapse-2').hasClass('in')) {
            $("#bs-example-navbar-collapse-2").removeClass("in");           
        }
    });

    $('.subButton').addClass('disabled'); 
    $('.subButton').attr("disabled", true);

    $(document).on("click", ".rating", function() {   
        var rating = $(this).val();    
        if(parseInt(rating) >= 0 ){    
            $('.subButton').removeClass('disabled');
            $('.subButton').attr("disabled", false);    
            if($('.rating-error').length > 0) {     
                $('.rating-error').hide();    
            }   
        }   
    }); 

    $('#myModal0').on('shown.bs.modal', function() {
       $('#myInput').focus()
    });

    $(".suggesResize, .genCommResize, .commentResize").on('click change, focus', function() {
        autosize(document.querySelectorAll('textarea.suggesResize'));    
        autosize(document.querySelectorAll('textarea.genCommResize'));    
        autosize(document.querySelectorAll('textarea.commentResize'));    
    });

    $(document).on("click", ".down-month, .curnt_year", function() { 
        if($('.year-tab').hasClass('open-month')){
            $('.year-tab').removeClass('open-month');
        } else {
            $('.year-tab').addClass('open-month');
        }
    });

    $("#pdf_email_popup").on("hidden.bs.modal",function(){
        sendPdfLink();
    });

});