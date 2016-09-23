<?php

namespace RocketChat\Api;

/**
 * Listing users, creating, editing.
 *
 * @author Fogarasi Ferenc <ffogarasi at gmail dot com>
 * Website: http://github.com/ffogarasi/rocket-chat-api
 */
class Channel extends AbstractApi
{

    /**
     * Returns tokens for .
     *
     * @param bool  $username the username of the user
     * @param array $password the password of the user
     *
     * @return user's auth token and userId
     */
    public function create($name)
    {
        $result = $this->post('v1/channels.create', ['name'=>$name]);

        if ($this->status)
        {
            return $result->channel;
        }

        return null;
    }
}
