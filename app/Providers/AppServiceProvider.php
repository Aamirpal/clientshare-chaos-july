<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;

use App\{Post, Comment };
use App\Helpers\Logger;
use App\Jobs\MixpanelLog;

class AppServiceProvider extends ServiceProvider
{    
    public function boot(UrlGenerator $url)
    {
        if(!env('HTTPS_ENABLE') && env('APP_ENV', 'not_local') != 'local' ){
            $url->forceScheme('https');
        }

        //Extend Socialite for linkein field
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'linkedin',
            function ($app) use ($socialite) {
                $config = $app['config']['services.linkedin'];
                return $socialite->buildProvider(LinkedInProvider::class, $config);
            }
        );
        
        Comment::created(function ($comment) {
            $this->customLog($comment->user_id, Post::findOrFail($comment->post_id)['space_id'], Logger::MIXPANEL_TAG['comment_added']);
        });

        Post::created(function ($post) {
            $this->customLog($post->user_id, $post->space_id, Logger::MIXPANEL_TAG['post_created']);
        });
    }

    public function customLog($user_id, $space_id, $event_tag){
        $log_data = [
          'user_id' => $user_id,
          'event' => $event_tag,
          'space_id'=> $space_id
        ];
        dispatch(new MixpanelLog($log_data));
        return;
    }

    public function register() {
        $this->app->bind(
            'Acme\Repository\UserInterface',
            'Acme\Repository\Eloquent\User'
        );
        $this->app->bind(
            'Acme\Repository\SpaceInterface',
            'Acme\Repository\Eloquent\Space'
        );

        $this->app->bind(
            'App\Repositories\SpaceUser\SpaceUserInterface',
            'App\Repositories\SpaceUser\SpaceUser'
        );
        $this->app->bind(
            'App\Repositories\Notification\NotificationInterface',
            'App\Repositories\Notification\NotificationRepository'
        );    
        $this->app->bind(
            'App\Repositories\Post\PostInterface',
            'App\Repositories\Post\PostRepository'
        );
        $this->app->bind(
            'App\Repositories\SpaceCategory\SpaceCategoryInterface',
            'App\Repositories\SpaceCategory\SpaceCategoryRepository'
        );
        $this->app->bind(
            'App\Repositories\User\UserInterface',
            'App\Repositories\User\UserRepository'
        );
        $this->app->bind(
            'App\Repositories\Space\SpaceInterface',
            'App\Repositories\Space\SpaceRepository'
        );
        $this->app->bind(
            'App\Repositories\Group\GroupInterface',
            'App\Repositories\Group\GroupRepository'
        );
        $this->app->bind(
            'App\Repositories\GroupUser\GroupUserInterface',
            'App\Repositories\GroupUser\GroupUserRepository'
        );
        $this->app->bind(
            'App\Repositories\BusinessReview\BusinessReviewInterface', 'App\Repositories\BusinessReview\BusinessReviewRepository'
        );
        $this->app->bind(
            'App\Repositories\Attendee\AttendeeInterface', 'App\Repositories\Attendee\AttendeeRepository'
        );

        $this->app->bind(
            'App\Repositories\PostMedia\PostMediaInterface', 'App\Repositories\PostMedia\PostMediaRepository'
        );

        $this->app->bind(
            'App\Repositories\RemoveCloudFile\RemoveCloudFileInterface', 'App\Repositories\RemoveCloudFile\RemoveCloudFileRepository'
        );

        $this->app->bind(
            'App\Repositories\EndorsePost\EndorsePostInterface', 'App\Repositories\EndorsePost\EndorsePostRepository');

        $this->app->bind(
            'App\Repositories\BusinessReviewMedia\BusinessReviewMediaInterface', 'App\Repositories\BusinessReviewMedia\BusinessReviewMediaRepository'
        );

        $this->app->bind('App\Repositories\Comment\CommentInterface', 'App\Repositories\Comment\CommentRepository');

        $this->app->bind('App\Repositories\PostView\PostViewInterface', 'App\Repositories\PostView\PostViewRepository');

        $this->app->bind('App\Repositories\CommentAttachment\CommentAttachmentInterface', 'App\Repositories\CommentAttachment\CommentAttachmentRepository');
    }

}
