const elixir = require('laravel-elixir');

require('laravel-elixir-vue');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir.config.assetsPath = 'public';
elixir.config.js.outputFolder = 'public/js/compiled';
elixir.config.css.outputFolder = 'public/css';

elixir(mix => {
    mix.scripts([
        '/bootstrap-suggest.js',
        '/moment.min.js',
        '/bootstrap-multiselect.js',
        '/bootstrap-tour.min.js',
        '/jquery.mentions.js',
        '/custom/file_viewer.js',
        '/custom/user_mention.js',
        '/handle_bar.js',
        '/custom/handlebarjs_helpers.js',
        '/custom/view_template.js',
        '/custom/feed.js',
        '/custom/comment.js',
        '/custom/post_feature.js',
        '/custom/welcome_tour.js'
    ], elixir.config.js.outputFolder+'/feed_page.js');

    mix.styles([
        '/bootstrap.min.css',
        '/bootstrap-select.css',
        '/font-awesome.min.css',
        '/bootstrap-multiselect.css',
        '/bootstrap-tour.min.css',
        '/sweetalert2(6.6.9).min.css',
        '/style.css',
        '/feed.css',
    ], elixir.config.css.outputFolder+'/main_compiled.css');
    
    mix.sass('../../resources/assets/sass/app.scss', 'public/css');
    mix.styles([
        '/style/bootstrap.min.css',
        '/app.css'
    ], elixir.config.css.outputFolder + '/main_compiled_v2.css');
   
   

    mix.scripts([
        '/modular_polyfill_standard.js',
        '/bootstrap.min.js',
        '/custom/circlos.js',
        '/bootstrap-select.js',
        '/autosize.js',
        '/jquery.ns-autogrow.min.js',
        '/bootstrap-tour.min.js',
        '/search.js',
        '/cropit_exif_fix.js',
        '/w3autosize.js',
        '/jquery.fileupload.js',
        '/jquery-ui.min.js',
        '/jquery.cookie.js',
        '/custom/generic.js',
        '/custom/invite.js',
        '/custom/common.js',
        '/custom/logger.js',
        '/custom/file_view.js',
        '/custom/community_member_module.js',
        '/sweetalert2(6.6.9).min.js'
    ], elixir.config.js.outputFolder+'/main_compiled.js');
   
    mix.scripts([
        'public/js/handle_bar.js',
        'public/js/custom/handlebarjs_helpers.js',
        'public/js/search.js',
        'public/js/custom/v2/feedpage.js',
    ], elixir.config.js.outputFolder+'/main_compiled-v2.js');


    mix.version([
        elixir.config.js.outputFolder+'/feed_page.js',
        elixir.config.js.outputFolder+'/main_compiled.js'
    ]);
});
