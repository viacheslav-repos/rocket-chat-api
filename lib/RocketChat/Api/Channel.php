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
    public function create($name,$usernames=array())
    {
        $result = $this->post('v1/channels.create', ['name'=>$name, 'members' => $usernames]);

        if ($this->status)
        {
            return $result->channel;
        }

        return null;
    }

    public function publicRooms()
    {
        $result = $this->get('publicRooms');

        if ($this->status)
        {
            return $result;
        }

        return null;
    }

    public function findByName($name)
    {
        $result = $this->publicRooms();
        if ($result)
        {
            foreach($result->rooms as $room)
            {
                if ($room->name == $name)
                {
                    return $room;
                }
            }
        }

        return null;
    }

    /*
     * implement when api is ready
     */
    public function setArchived($id, $state)
    {
        if($state == true) {
            $result = $this->post('room/'. $id .'/archive', []);
        } else {
            $result = $this->post('room/'. $id .'/unarchive', []);
        }

        if ($this->status)
        {
            return $result;
        }

        return null;
    }

    public function createBulk($rooms)
    {
        $result = $this->post('bulk/createRoom', ['rooms' => $rooms]);

        if ($this->status)
        {
            return $result;
        }

        return null;
    }

    public function sendMessage($room, $message)
    {
        $result = $this->post("rooms/{$room}/send", ['msg' => $message]);

        if ($this->status)
        {
            return $result;
        }

        return null;

    }
    
}
