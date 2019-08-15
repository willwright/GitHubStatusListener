<?php

namespace MeCodeNinja\GitHubWebhooks\Checks;

use GuzzleHttp\Exception\GuzzleException;

class PathCheck extends CheckAbstract
{
    const GRAPHQL_ENDPOINT = 'https://api.github.com/graphql';

    /** @var array  */
    private $_files = [];

    /** @var  */
    private $_client;

    /** @var string */
    private $_token;

    /** @var string */
    private $_nodeId;

    /**
     * This is specific implementation of check for this specific Listener
     *
     * @return bool
     */
    function doCheck()
    {
        $after = null;

        do {
            try {
                $response = $this->filesQuery($this->_client, $this->_token, $this->_nodeId, $after);
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

        //@TODO can the check be extracted into some kind of object for "checking" stuff?
        //Loop over the paths looking for matches
        $resultsArr = preg_grep('/^vendor\//', $this->_files);
        if (count($resultsArr) > 0) {
            //Status Failed
            return false;
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
            $reflect = new \ReflectionClass($this);
        } catch(\ReflectionException $reflectionException) {
            return get_class($this);
        }
        return $reflect->getShortName();
    }

    /**
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
    private function filesQuery(\GuzzleHttp\Client $client, $token, $nodeID, $after = null) {
        $afterCursor = null;
        if (!empty($after)) {
            $afterCursor = "after: $after";
        }

        $payload = new \stdClass();
        $payload->query = sprintf('query { node(id:"%s") { ... on PullRequest { id, files(first:5 %s) { totalCount, edges {node { path },cursor } pageInfo { endCursor, hasNextPage} } } } }', $nodeID, $afterCursor);

        $response = $client->request('POST', self::GRAPHQL_ENDPOINT,[
            'headers' => [
                'Authorization' => "token $token"
            ],
            'body' => \GuzzleHttp\json_encode($payload)
        ]);

        return $response;
    }
}
