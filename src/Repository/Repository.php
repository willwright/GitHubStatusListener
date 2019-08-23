<?php

namespace MeCodeNinja\GitHubWebhooks\Repository;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    private $_name;

    private $_token;

    private $_checks = [];

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->_token = $token;
    }

    /**
     * @return array
     */
    public function getChecks()
    {
        return $this->_checks;
    }

    /**
     * @param array $checks
     */
    public function setChecks($checks)
    {
        $this->_checks = $checks;
    }

    /**
     * @param $check
     */
    public function appendCheck($check) {
        $this->_checks[] = $check;
    }

}