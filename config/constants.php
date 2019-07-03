<?php

return [
    'APPLICATION' => [
        'description' => 'New content is waiting for you...',
        'display_name' => 'Client Share',
        'marketing_site_url' => 'http://myclientshare.com'
    ],
    'ANALYTIC' => [
        'graph_selection_limit' => 15
    ],
    'AJAX_INTERVAL'=>8000,
    'MEDIA_LINKEDIN'=>'media.licdn.com',
    'INVITATION_STATUS_CANCELLED'=>'Canceled',
    'USER_ID_DEFAULT'=>"00000000-0000-0000-0000-000000000000",
    'DUMMY_UUID' => [
        '00000000-0000-0000-0000-000000000000',
        '00000000-0000-0000-0000-000000000001'
    ],
    'INVITATION_CODE_FOR_REMOVED_USER'=> -1,
    'SUPER_ADMIN'=>'super_admin',
    'ADMIN_ROLE_ID'=>1,
    'SEND_INVITATION'=>'Send Invitation',
    'POST_COMMENT_ROW_LIMIT'=>2,
    'SAVE_EXECUTIVE_SUMMARY'=>'save executive summary',
    'EXECUTIVE_FILES_LIMIT'=>2,
    'EMAIL_DEFAULT_SHARE_LOGO'=>env('APP_URL').'/images/email_default_share_logo.png',
    'COMMUNITY_USERS_COUNT'=>3,
    'COMMUNITY_USERS_AJAX_LIMIT'=>15,
    'TOTAL_POSTS_FETCH_COUNT'=>3,
    'POST_EMPTY_CASE'=>2,
    'POST_NOT_EMPTY_CASE'=>3,
    'TIMEZONE'=>'Europe/London',
    'COMMENT'=>'comment',
    'AUTH_CODE_INPUT_ATTEMPTS'=>6,
    'COMPANY_LOGIN_LOGO'=>env('APP_URL').'/images/login_user_icon.png',
    'GLOBE_IMAGE'=>env('APP_URL').'/images/ic_public.svg',
    'APPROVED_USER'=>7,
    'AUTH_CODE_INPUT_TIME'=>300,
    'BULK_INVITATION_USER_LIMIT' => 2000,
    'VIDEO_COUNT'=>101,
    'BAD_REQUEST' =>400,
    'MANAGEMENT_INFORMATION' => [
        'contract_value_division' => 1000000
    ],
    'MODAL_ID'=>2001,
    'MODEL' => [
        'management_information' => [
            'STATUS_FILTER_LABEL' => [
                's1' => 'Live',
                's2' => 'Live - Non Standard',
                's3' => 'Test',
                's4' => 'Inactive',
                's5' => 'Unassigned'
            ]
        ],
        'executive_file' => 'Media',
        'post_file' => 'PostMedia'
    ],
    'MEDIA_EXTENSIONS'=>[
         'MP4'=>'mp4',
         'MOV'=>'mov',
         'MOVE'=>'MOV',
         'MKV'=>'mkv',
         '3GP'=>'3gp',
         'FLV'=>'flv'
    ],
    'DOCUMENT_EXTENSIONS'=>[
        'PDF'=>'pdf',
        'PPT'=>'ppt',
        'DOCX'=>'docx',
        'PPTX'=>'pptx',
        'DOC'=>'doc',
        'XLS'=>'xls',
        'XLSX'=>'xlsx',
        'CSV'=>'csv'
    ],
    'IMAGE_EXTENSIONS'=>[
        'jpg',
        'jpeg',
        'png',
        'gif',
        'JPG',
        'JPEG',
        'PNG',
        'GIF'
    ],
    'INACTIVE_USER'=>0,
    'COUNT_ONE'=>1,
    'COUNT_TWO'=>2,
    'cookies'=>[
        'life_time' => time() + (86400 * 30)// 30 days
    ],
    'DESCRIPTION_LIMIT'=>375,
    'EMAIL_SETTING'=> [
        'regex'=> [
            'domain' => '^@?((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$'
        ]
    ],
    'email' => [
        'image_domain' => env('APP_URL'),
        'restricted_emails' => '3391bc@myclientshare.com',
        'support_email' => 'support@myclientshare.com',
        'reply_to' => 'info@myclientshare.com',
        'regex' => '/(((http|https|ftp|ftps)\:\/\/)|(www.))[a-zA-Z0-9\-\_\.]+\.[a-zA-Z]{2,3}(\S*)?/i',
        'mention' => '/@\w[a-zA-Z0-9\-\_\+\.]+/im',
        'pending_invites' => [
            'day' => 5
        ],
        'blocked_tags' => [
            'pending-invite-reminder',
            'bulk-users-invitation'
        ],
        'blocked_tags_for_admin' => [
            'user-invitation'
        ],
        'blocked_tags_for_user' =>[
            'weekly-summary',
            'feedback-alert'
        ],
        'invitation_mail_tags' => [
            'bulk-users-invitation','user-invitation','pending-invite-reminder'
        ],
        'post_alert' => [
            'display_attachment' => 2
        ]
    ],
    'extension_wise_image' => [
        'mp4'=> '/images/ic_VIDEO.svg',
        'mov'=> '/images/ic_VIDEO.svg',
        'png'=> '/images/ic_IMAGE.svg',
        'jpg'=> '/images/ic_IMAGE.svg',
        'jpeg'=> '/images/ic_IMAGE.svg',
        'ppt' =>'/images/ic_PWERPOINT.svg',
        'pptx'=> '/images/ic_PWERPOINT.svg',
        'csv' => '/images/ic_EXCEL.svg',
        'xls'=>'/images/ic_EXCEL.svg',
        'xlsx'=> '/images/ic_EXCEL.svg',
        'pdf'=> '/images/ic_PDF.svg',
        'doc'=> '/images/ic_WORD.svg',
        'docx'=> '/images/ic_WORD.svg'
    ],
    'extension_wise_png_image' => [
        'mp4'=> '/images/ic_VIDEO.png',
        'mov'=> '/images/ic_VIDEO.svg',
        'png'=> '/images/ic_IMAGE.png',
        'jpg'=> '/images/ic_IMAGE.png',
        'jpeg'=> '/images/ic_IMAGE.png',
        'ppt' =>'/images/ic_PWERPOINT.png',
        'pptx'=> '/images/ic_PWERPOINT.png',
        'csv' => '/images/ic_EXCEL.png',
        'xls'=>'/images/ic_EXCEL.png',
        'xlsx'=> '/images/ic_EXCEL.png',
        'pdf'=> '/images/ic_PDF.png',
        'doc'=> '/images/ic_WORD.png',
        'docx'=> '/images/ic_WORD.png'
    ],
    'extension_wise_svg_image' => [
        'mp4'=> '/images/ic_video_small.svg',
        'mov'=> '/images/ic_video_small.svg',
        'MOV'=> '/images/ic_video_small.svg',
        'png'=> '/images/ic_image_small.svg',
        'jpg'=> '/images/ic_image_small.svg',
        'jpeg'=> '/images/ic_image_small.svg',
        'ppt' =>'/images/ic_powerpoint_small.svg',
        'pptx'=> '/images/ic_powerpoint_small.svg',
        'csv' => '/images/ic_excel_small.svg',
        'xls'=>'/images/ic_excel_small.svg',
        'xlsx'=> '/images/ic_excel_small.svg',
        'pdf'=> '/images/ic_pdf_small.svg',
        'doc'=> '/images/ic_word_small.svg',
        'docx'=> '/images/ic_word_small.svg'
    ],
    'feedback' => [
        'feedback_opened_till' => env('feedback_opened_till', 14),
        'close_feeback_day' => env('close_feeback_day', 15)
    ],
    'POST'=>[
        'file_view_page' => 20
    ],
    'post_limit'=>3,
    'post_comment_string_limit'=>320,
    'super_admin'=>[
        'login_email' => env('super_admin_login_email', 'clientshare@ucreate.co.in')
    ],
    'GENERIC' => [
        'email_alert' => [
            'user_listing' => 3,
            'post_description'=> 292
        ]
    ],
    'GRAPH'=>[
        'DATA_POINT' => 6
    ],
    'POST_EXTENSION' => [
        'pdf', 'docx', 'ppt', 'pptx', 'mp4', 'doc', 'xls', 'xlsx', 'csv', 'jpeg', 'png', 'jpg','xlsm', 'mov', 'MOV'
    ],
    'PROJECT'=>[
        'name'=>'Client Share'
    ],
    'REQUESTED_FORM' => [
        'field' => [
            'file' => [
                'browsed' => 'browsed'
            ]
        ],
        'status'=>[
            'true'=>'true'
        ]
    ],
    'TWITTER_REQUEST_URL' => 'statuses/home_timeline',
    'EMBEDLY_API_URL' => 'http://api.embedly.com/1/extract',
    'REQUEST_FROM' => [
        'post' => 'post',
        'like' => 'like',
        'setting' => 'setting',
        'feedback' => 'feedback',
        'analytics' => 'analytics',
        'community' => 'community',
        'invite' => 'invite'
    ],
    's3' => [
        'url' => "https://".env('AWS_REGION_PREFIX').env('AWS_REGION').".amazonaws.com/",
        'DOMAIN' => "amazonaws.com",
        'S3_PATH_REGEX' => '((s3-|s3\.)?(.*)\.amazonaws\.com\/'.env('S3_BUCKET_NAME').'\/)'
    ],
    'URL' => [
        'postmark_curl' => 'https://api.postmarkapp.com/messages/outbound/',
        'google_favicon' => 'https://www.google.com/s2/favicons?domain=',
        'youtube_embed' => 'https://www.youtube.com/embed/'
    ],
    'USER'=>[
        'role_tag' =>[
            'seller' => 'seller',
            'buyer' => 'buyer',
        ]
    ],
    'USER_ROLE_ID'=>2,
    'PDF_USERS_LIMIT'=>250,
    'COLUMN_ID'=>'A',
    'COLUMN_WIDTH_SMALL'=>5,
    'COLUMN_WIDTH_LARGE'=>30,
    'NUMBER_COUNT'=>2,
    'COMMUNITY_GROWTH'=>5,
    'CSI_GROWTH'=>50,
    'OVERALL_GROWTH'=>10,
    'LOOP_START_COUNT'=>0,
    'INVITE_EXPORT'=>'invite-export',
    'DEFAULT_SHARE_NAME' =>'Select Share',
    'DEFAULT_COMPANY_NAME' =>'Select community',
    'LABEL_LIVE' =>'Live',
    'LABEL_LIVE_NON_STANDARD' =>'Live - Non Standard',
    'MI_COLUMNS' => [
        'BUYER' => 'Buyer',
        'BUYER_COMMUNITY' => 'Buyer Community',
        'BUYER_CSI_SCORE' => 'Buyer CSI Score',
        'BUYER_POSTS' => 'Buyer Posts',
        'BUYER_POSTS_INTERACTION' => 'Buyer Posts Interaction',
        'CONTRACT_END_DATE' => 'Contract End Date',
        'CONTRACT_VALUE' => 'Contract Value £m',
        'GROWTH' => 'Growth',
        'NPS_SCORE' => 'NPS score',
        'OVERALL_COMMUNITY_SIZE' => 'Overall Community Size',
        'OVERALL_CSI_AVERAGE' => 'Overall CSI Average',
        'OVERALL_POSTS' => 'Overall Posts',
        'OVERALL_POSTS_INTERACTION' => 'Overall Posts Interaction',
        'PENDING_INVITES' => 'Pending Invites',
        'PERCENTAGE_COMPLETE' => 'Percentage complete',
        'STATUS' => 'Status',
        'SUPPLIER_COMMUNITY' => 'Supplier Community',
        'SUPPLIER_CSI_SCORE' => 'Supplier CSI Score',
        'SUPPLIER_POSTS' => 'Supplier Posts',
        'SUPPLIER_POSTS_INTERACTION' => 'Supplier Posts Interaction',
        'SUPPLIER' => 'Supplier',
        'SHARE_NAME' => 'Share'
    ],
    'BANNER_IMAGES' => [
         '/images/banner/adolfo-ruiz-501478-unsplash.jpg',
         '/images/banner/anabel-f-zamora-230570-unsplash.jpg',
         '/images/banner/anders-jilden-219256-unsplash.jpg',
         '/images/banner/aperture-vintage-339839-unsplash.jpg',
         '/images/banner/arisa-chattasa-536833-unsplash.jpg',
         '/images/banner/arno-smit-165176-unsplash.jpg',
         '/images/banner/breno-freitas-277392-unsplash.jpg',
         '/images/banner/devin-avery-542010-unsplash.jpg',
         '/images/banner/fabrizio-conti-666168-unsplash.jpg',
         '/images/banner/jason-leem-143987-unsplash.jpg',
         '/images/banner/joe-tree-242839-unsplash.jpg',
         '/images/banner/jonathan-simcoe-125293-unsplash.jpg',
         '/images/banner/jose-miguel-537825-unsplash.jpg',
         '/images/banner/joshua-rodriguez-466119-unsplash.jpg',
         '/images/banner/josiah-weiss-541633-unsplash.jpg',
         '/images/banner/ricardo-gomez-angel-202862-unsplash.jpg',
         '/images/banner/ricardo-gomez-angel-561569-unsplash.jpg',
         '/images/banner/zoltan-kovacs-285132-unsplash.jpg'
    ],
    'URL_EXIST' => true,
    'CS_PROFILE_IMAGE' => '/images/CS_symbol.png',
    'CS_EMAIL_SPLITTER' => '/images/border-image.png',
    'EMAIL_BANNER_DIMENSION' => [
            'logo_resize_x' => 160,
            'logo_resize_y' => 160,
            'logo_position_x' => 252,
            'logo_position_y' => 0
        ],
    'LINKED_IN_URL' => 'media.licdn.com',
    'MAX_SHARE_SETUP_STEPS' => 10,
    'MI_STATUS' => 3,
    'API' => 'api',
    'COUNT_ZERO' => 0,
    'TASK_0_PROGRESS' => 0,
    'TASK_1_PROGRESS' => 12,
    'TASK_2_PROGRESS' => 25,
    'TASK_3_PROGRESS' => 37,
    'TASK_4_PROGRESS' => 50,
    'TASK_5_PROGRESS' => 62,
    'TASK_6_PROGRESS' => 75,
    'TASK_7_PROGRESS' => 87,
    'TASK_8_PROGRESS' => 100,
    'SHARE_PROFILE_COMPLETION_INDEX' => (100 / 6),
    'STEPS_TO_COMPLETE_SHARE_PROFILE' => 7,
    'HUNDRED_PERCENT' => 100,
    'EMPTY_JSON' => '{"":""}',
    'PROGRESS_BAR' => 'progress_bar',
    'USER_TYPE_ID' => 3,
    'SHARE_STATUS' => 'Unassigned',
    'NAME_INITIALS_BACKGROUND_IMAGE' => 'user_profile_thumbnail/circular_1953586060_1552641454.png',
    'SPACE_ENCODING' => '%20',
    'category_logos'=>[
        'general_updates'=>'images/general.svg',
        'business_reviews'=>'images/business-reviews.svg',
        'management_information'=>'images/mi-reports.svg',
        'company_employee_news'=>'images/proposals-presentations.svg',
        'innovation_added_value'=>'images/innovation-added-value.svg',
        'management_messages'=>'images/contracts.svg',
    ],
    'EVERYONE_IN_SHARE' => 'Everyone',
    'BUSINESS_REVIEWS' => 'Business Reviews',
    'BR_CONDUCTED_VIA' => [
        1 => 'F2F',
        2 => 'CALL',
        3 => 'VIDEO',
        4 => 'DOC',
    ],
    'DOCUMENT_VIEWER' => 'https://view.officeapps.live.com/op/view.aspx?src=',
    'PDF_VIEWER' => env('APP_URL') . "/pdf_viewer/web/viewer.html?file=",
    'USER_ROLE' => [
        'MEMBER' => 2,
        'ADMIN' => 3,
    ]
];
