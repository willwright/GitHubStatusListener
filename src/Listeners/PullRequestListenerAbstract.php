<?php

namespace MeCodeNinja\GitHubWebhooks\Listeners;

use MeCodeNinja\GitHubWebhooks\Events\PullRequest;
use MeCodeNinja\GitHubWebhooks\GitHub\Helper;
use MeCodeNinja\GitHubWebhooks\GitHub\Status;
use GuzzleHttp\Exception\GuzzleException;

abstract class PullRequestListenerAbstract implements StatusListenerInterface
{
    /** @var Helper */
    private $_githubHelper;

    /** @var Status */
    private $_oStatus;

    /** @var string */
    protected $_content;

    /** @var string */
    protected $_token;

    /** @var \GuzzleHttp\Client  */
    protected $_client;

    /** @var string */
    protected $_nodeId;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        Helper $helper,
        \GuzzleHttp\Client $client
    )
    {
        $this->_githubHelper = $helper;
        $this->_client = $client;
    }

    /**
     * This method will be called automatically from the framework
     *
     * @param PullRequest $event
     */
    public function handle(PullRequest $event) {
        $this->_content = $event->_request->getContent();
        if (empty($this->_content)) {
            return;
        }

        $json = json_decode($this->_content);

        $this->_nodeId = $json->pull_request->node_id;

        /**
         * Get the User token so that we can interact with the repo
         */
        $this->_token = $this->_githubHelper->getUserToken($json->repository->full_name);

        if (empty($this->_token)) {
            return;
        }

        $this->_oStatus = new Status($this->_client, $this->_token);

        if ($this->doCheck()) {
            $this->_oStatus->setState('success');
        } else {
            $this->_oStatus->setState('error');
        }

        /**
         * Create the Status Object which will create a new Status in GitHub
         */
        $this->_oStatus->setOwner($json->repository->owner->login);
        $this->_oStatus->setRepo($json->repository->name);
        $this->_oStatus->setSha($json->pull_request->head->sha);
        $this->_oStatus->setContext('mecode.ninja - ' . $this->getContext());
        $this->_oStatus->setDescription($this->getDescription());

        try {
            $this->_oStatus->create();
        } catch (GuzzleException $guzzleException) {
            //@TODO: What to do wih this?
        }

        return;
    }
}