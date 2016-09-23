<?php

namespace RocketChat\Api;

/**
 * Listing users, creating, editing.
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Users
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class User extends AbstractApi
{

    /**
     * Returns tokens for .
     *
     * @param bool  $username the username of the user
     * @param array $password the password of the user
     *
     * @return user's auth token and userId
     */
    public function login($username, $password)
    {
        return $this->post('login', ['user'=>$username, 'password'=>$password]);
    }
}
