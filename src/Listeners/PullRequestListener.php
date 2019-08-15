<?php


namespace MeCodeNinja\GitHubWebhooks\Listeners;


use GuzzleHttp\Exception\GuzzleException;
use MeCodeNinja\GitHubWebhooks\Checks\CheckFactory;
use MeCodeNinja\GitHubWebhooks\Events\PullRequest;
use MeCodeNinja\GitHubWebhooks\GitHub\Config;
use MeCodeNinja\GitHubWebhooks\GitHub\Status;

class PullRequestListener
{
    /** @var \GuzzleHttp\Client  */
    protected $_client;

    /** @var string */
    protected $_token;

    /** @var string */
    protected $_content;

    /** @var string */
    protected $_nodeId;

    /** @var Status */
    private $_oStatus;

    /** @var Config */
    private $_config;

    /**
     * Create the event listener.
     *
     * @param Config $config
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(
        Config $config,
        \GuzzleHttp\Client $client
    )
    {
        $this->_config = $config;
        $this->_client = $client;
    }

    /**
     * This method will be called automatically from the framework
     *
     * @param PullRequest $event
     */
    public function handle(PullRequest $event)
    {
        $this->_content = $event->_request->getContent();
        if (empty($this->_content)) {
            return;
        }

        $json = json_decode($this->_content);

        $this->_nodeId = $json->pull_request->node_id;

        /**
         * Get the User token so that we can interact with the repo
         */
        $this->_token = $this->_config->getUserToken($json->repository->full_name);

        if (empty($this->_token)) {
            return;
        }

        //@TODO: Move the Status logic to the Check Object or to a Status Object or helper

        $this->_oStatus = new Status($this->_client, $this->_token);

        //@TODO: Read config here and loop over Checks using factory to create concrete objects and call doCheck
        $checksArr = $this->_config->getChecks($json->repository->full_name);

        for ($i=0; $i < count($checksArr); $i++) {
            $checkObj = CheckFactory::create($checksArr[$i], $this->_content, $this->_token);

            if ($checkObj->doCheck()) {
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
            $this->_oStatus->setContext('mecode.ninja - ' . $checkObj->getContext());
            $this->_oStatus->setDescription($checkObj->getDescription());

            try {
//            $this->_oStatus->create();
            } catch (GuzzleException $guzzleException) {
                //@TODO: What to do wih this?
            }
        }

        return;
    }
}