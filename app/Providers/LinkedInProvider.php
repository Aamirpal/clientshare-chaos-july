<?php
namespace App\Providers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Illuminate\Support\Arr;

class LinkedInProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['w_member_social', 'r_emailaddress', 'r_liteprofile'];
    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';
    /**
     * {@inheritdoc}
     */

    protected $fields = [
        'id', 'first-name', 'last-name', 'formatted-name',
        'email-address', 'headline', 'location', 'industry',
        'public-profile-url', 'picture-url', 'picture-urls::(original)','positions',
    ];
    
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://www.linkedin.com/oauth/v2/authorization', $state);
    }
    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }
    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return parent::getTokenFields($code) + ['grant_type' => 'authorization_code'];
    }
    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $basicProfile = $this->getBasicProfile($token);
        $emailAddress = $this->getEmailAddress($token);
        return array_merge($basicProfile, $emailAddress);
    }
    /**
     * Get the basic profile fields for the user.
     *
     * @param  string  $token
     * @return array
     */

    protected function getPeople($token, $id)
    {
        $fields = implode(',', $this->fields); 
        $url = 'https://api.linkedin.com/v2/me/(id:'.$id.')?projection=(id,firstName,lastName,profileUrl,publicProfileUrl)';
        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ]);
        return (array) json_decode($response->getBody(), true);
    }


    protected function getBasicProfile($token)
    {
        $fields = implode(',', $this->fields); 
        $url = 'https://api.linkedin.com/v2/me';
        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ]);
        return (array) json_decode($response->getBody(), true);
    }
    /**
     * Get the email address for the user.
     *
     * @param  string  $token
     * @return array
     */
    protected function getEmailAddress($token)
    {
        $url = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';
        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
        ]);
        return (array) Arr::get((array) json_decode($response->getBody(), true), 'elements.0.handle~');
    }
    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $preferredLocale = Arr::get($user, 'firstName.preferredLocale.language').'_'.Arr::get($user, 'firstName.preferredLocale.country');
        $firstName = Arr::get($user, 'firstName.localized.'.$preferredLocale);
        $lastName = Arr::get($user, 'lastName.localized.'.$preferredLocale);
        $images = (array) Arr::get($user, 'profilePicture.displayImage~.elements', []);
        $avatar = Arr::first(Arr::where($images, function ($image) {
            return $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] === 100;
        }));
        $originalAvatar = Arr::first(Arr::where($images, function ($image) {
            return $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] === 800;
        }));
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => null,
            'name' => $firstName.' '.$lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => Arr::get($user, 'emailAddress'),
            'avatar' => Arr::get($avatar, 'identifiers.0.identifier'),
            'avatar_original' => Arr::get($originalAvatar, 'identifiers.0.identifier'),
        ]);
    }
}