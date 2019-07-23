<?php

namespace Alvario\OAuth2;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class AlvarioUser implements ResourceOwnerInterface
{
    protected $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->response['id'];
    }

    public function getUsername()
    {
        return $this->response['username'];
    }

    public function getFirstName()
    {
        return $this->response['first_name'];
    }

    public function getLastName()
    {
        return $this->response['last_name'];
    }

    public function getEmail()
    {
        return $this->response['email'];
    }

    public function getPermissions()
    {
        return $this->response['permissions'];
    }

    public function getScopes()
    {
        return $this->response['scopes'];
    }

    public function getRoles()
    {
        return $this->response['roles'];
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
