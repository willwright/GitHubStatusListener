<?php


namespace MeCodeNinja\GitHubWebhooks\Listeners;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use MeCodeNinja\GitHubWebhooks\Check\CheckAbstract;
use MeCodeNinja\GitHubWebhooks\Events\PullRequest;
use MeCodeNinja\GitHubWebhooks\GitHub\Config;
use MeCodeNinja\GitHubWebhooks\GitHub\Status;
use MeCodeNinja\GitHubWebhooks\Repository\Repository;

class PullRequestListener
{
    /** @var Client  */
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
     * @param Client $client
     */
    public function __construct(
        Config $config,
        Client $client
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

        $repositoriesArr = $this->_config->getRepositoriesByName($json->repository->full_name);
        /**
         * @var string $key
         * @var Repository $repository
         */
        foreach ($repositoriesArr as $key => $repository) {
            /**
             * Get the User token so that we can interact with the repo
             */
            $this->_token = $repository->getToken();
            if (empty($this->_token)) {
                return;
            }

            $this->_oStatus = new Status($this->_client, $this->_token);

            /**
             * @var string $key
             * @var CheckAbstract $checkObj */
            foreach ($repository->getChecks() as $checkObj) {
                if (!is_object($checkObj)) {
                    Log::warning("\MeCodeNinja\GitHubWebhooks\Listeners\PullRequestListener::handle got null object. SKIPPING");
                    return;
                }

                if ($checkObj->doCheck($this->_content)) {
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
                    $this->_oStatus->create();
                } catch (GuzzleException $guzzleException) {
                    Log::error("Unable to create Status in GitHub for: " . $checkObj->getContext());
                    report($guzzleException);
                }
            }
        }

        return;
    }
}