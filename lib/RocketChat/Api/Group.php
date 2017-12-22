<?php

namespace RocketChat\Api;

/**
 * Rocket-chat groups managing
 */
class Group extends AbstractApi
{
    /**
     * Get list of groups
     *
     * @return null|array|object
     */
    public function listGroups()
    {
        return $this->get('groups.list');
    }

    /**
     * Find group by name
     *
     * @param $name
     *
     * @return null|object
     */
    public function findByName($name)
    {
        $result = $this->listGroups();
        if ($result) {
            foreach ($result->groups as $group) {
                if ($group->name == $name) {
                    return $group;
                }
            }
        }

        return null;
    }
}
