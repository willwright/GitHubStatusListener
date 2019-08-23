<?php

namespace MeCodeNinja\GitHubWebhooks\Check;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionException;
use stdClass;

class PathCheck extends CheckAbstract
{
    const GRAPHQL_ENDPOINT = 'https://api.github.com/graphql';

    /** @var array  */
    private $_files = [];

    /** @var Client */
    private $_client;

    /** @var string */
    private $_nodeId;

    const PULL_REQUEST_BATCH_SIZE = 100;

    /**
     * This is specific implementation of check for this specific Listener
     *
     * @return bool
     */
    function doCheck($content)
    {
        $this->_client = new Client();
        $after = null;

        $json = json_decode($content);
        $this->_nodeId = $json->pull_request->node_id;

        do {
            try {
                //Query GitHub for files in the PullRequest
                $response = $this->filesQuery($this->_token, $this->_nodeId, $after);
            } catch (GuzzleException $guzzleException) {
                //@TODO: Do something with this
                return false;
            }

            $responseJSON = json_decode($response->getBody());

            if (property_exists($responseJSON,"errors")) {
                //@TODO: Do something with this
                return false;
            }

            //Gather files paths into local array
            foreach($responseJSON->data->node->files->edges as $edge) {
                $this->_files[] = $edge->node->path;
            }

            //Set the endCursor for the next iteration of this loop
            $after =  $responseJSON->data->node->files->pageInfo->endCursor;
        } while($responseJSON->data->node->files->pageInfo->hasNextPage);

        if (!key_exists('paths', $this->_config)) {
            Log::info("No paths configured for PathCheck return PASS");
            return true;
        }

        //Loop over the paths looking for matches
        for ($i=0; $i < count($this->_config['paths']); $i++) {
            $path = $this->_config['paths'][$i];
            try {
                $resultsArr = preg_grep($path, $this->_files);
            } catch (\ErrorException $errorException) {
                report($errorException);
                $resultsArr = [];
            }

            if (count($resultsArr) > 0) {
                //Status Failed
                return false;
            }
        }

        //Status Passed
        return true;
    }

    /**
     * Return the context to keep the status checks separate in GitHub
     *
     * Use Reflection method because it's much faster than string splitting
     * @return string
     */
    function getContext()
    {
        try {
            $reflect = new ReflectionClass($this);
        } catch(ReflectionException $reflectionException) {
            return get_class($this);
        }
        return $reflect->getShortName();
    }

    /**
     * Return the Description to be used in the Status object for this particular Check
     *
     * @return string
     */
    function getDescription()
    {
        return "Passes if no modifications made to /vendor";
    }

    /**
     * Run the GraphQL Query which returns a list of files in the given PR
     *
     * @param \GuzzleHttp\Client $client
     * @param string $token
     * @param string $nodeID
     * @param null $after
     * @return int|mixed|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    private function filesQuery($token, $nodeID, $after = null) {
        $afterCursor = null;
        if (!empty($after)) {
            $afterCursor = "after: $after";
        }

        $payload = new stdClass();
        $payload->query = sprintf('query { node(id:"%s") { ... on PullRequest { id, files(first:%u %s) { totalCount, edges {node { path },cursor } pageInfo { endCursor, hasNextPage} } } } }', $nodeID, self::PULL_REQUEST_BATCH_SIZE,$afterCursor);

        $response = $this->_client->request('POST', self::GRAPHQL_ENDPOINT,[
            'headers' => [
                'Authorization' => "token $token"
            ],
            'body' => \GuzzleHttp\json_encode($payload)
        ]);

        return $response;
    }
}
