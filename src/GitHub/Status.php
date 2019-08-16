<?php
namespace MeCodeNinja\GitHubWebhooks\GitHub;

use GuzzleHttp\Exception\GuzzleException;

class Status implements StatusInterface
{
    /** @var string */
    private $_state;

    /** @var string */
    private $_target_url;

    /** @var string */
    private $_description;

    /** @var string */
    private $_context;

    /** @var \GuzzleHttp\Client  */
    private $_client;

    /** @var string */
    private $_owner;

    /** @var string */
    private $_repo;

    /** @var string */
    private $_sha;

    /** @var string */
    private $_token;

    public function __construct(\GuzzleHttp\Client $client, $token)
    {
        $this->_client = $client;
        $this->_token = $token;
    }

    /**
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->_owner = $owner;
    }

    /**
     * @param string $repo
     */
    public function setRepo($repo)
    {
        $this->_repo = $repo;
    }

    /**
     * @param string $sha
     */
    public function setSha($sha)
    {
        $this->_sha = $sha;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->_state = $state;
    }

    /**
     * @param string $target_url
     */
    public function setTargetUrl($target_url)
    {
        $this->_target_url = $target_url;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * @param string $context
     */
    public function setContext($context)
    {
        $this->_context = $context;
    }

    /**
     * Create the Status within GitHub
     *
     * @throws GuzzleException
     */
    public function create()
    {
        /**
         * Build the API request to GitHub
         */
        $statusesEndPoint = sprintf("https://api.github.com/repos/%s/%s/statuses/%s",
            $this->_owner,
            $this->_repo,
            $this->_sha);

        $payload = new \stdClass();
        $payload->state = $this->_state;
        $payload->target_url = $this->_target_url;
        $payload->description = $this->_description;
        $payload->context = $this->_context;

        /**
         * Send the request to GitHub
         */
        $this->_client->request('POST', $statusesEndPoint,[
            'headers' => [
                'Authorization' => "token $this->_token"
            ],
            'body' => \GuzzleHttp\json_encode($payload)
        ]);
    }
}