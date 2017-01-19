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
        $result = $this->post('channels.create', ['name'=>$name, 'members' => $usernames]);

        if ($this->status)
        {
            return $result->channel;
        }

        return null;
    }

    /**
     * Returns tokens for .
     *
     * @param bool  $username the username of the user
     * @param array $password the password of the user
     *
     * @return user's auth token and userId
     */
    public function close($id)
    {
        $result = $this->post('channels.close',['roomId' => $id]);

        if ($this->status)
        {
            return $result->channel;
        }

        return null;
    }

    public function listRooms()
    {
        $result = (object)[
            'channels' => [],
            'total' => 0,
            'count' => 0,
            'success' =>1,
        ];

        $offset=0;

        do
        {
            $partial = $this->get("channels.list?count=100&offset={$offset}");

            if (!$this->status)
            {
                return null;
            }

            $result->total = (int)$partial->total;
            $result->channels = array_merge($result->channels,$partial->channels);
            $offset+=(int)$partial->count;
        }
        while( count($result->channels)< $result->total);

        return $result;
    }

    public function findByName($name)
    {
        $result = $this->listRooms();
        if ($result)
        {
            foreach($result->channels as $room)
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
            $result = $this->post('channels.archive', ['roomId' => $id]);
        } else {
            $result = $this->post('channels.unarchive', ['roomId' => $id]);
        }

        if ($this->status)
        {
            return $result;
        }

        return null;
    }

    public function sendMessage($id, $message)
    {
        $result = $this->post("chat.postMessage", [ 'roomId' => $id, 'text' => $message]);

        if ($this->status)
        {
            return $result;
        }

        return null;

    }
    
}
