<?php


namespace MeCodeNinja\GitHubWebhooks\GitHub;


use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Config
 * @package MeCodeNinja\GitHubWebhooks\GitHub
 */
class Config
{
    const REPO_CONFIG_FILE_NAME = 'githubwebhooks-config.yaml';

    /**
     * Get the stored User Token
     *
     * @param string $repoName
     * @return mixed|null
     */
    public function getUserToken(string $repoName) {
        $contents = Storage::get(self::REPO_CONFIG_FILE_NAME);
        $value = Yaml::parse($contents);

        $collection = collect($value);
        $repoCollection = collect($collection->get('repositories'));
        $repo = collect($repoCollection->where('name', $repoName)->first());

        return $repo->get('token');
    }

    /**
     * Check to see if the Config file exists for this Package
     * If the Config file does not already exist create it with the stub content
     *
     * @return void
     */
    public function createRepoConfigFile() {
        if (!Storage::disk('local')->exists(self::REPO_CONFIG_FILE_NAME)) {
            Storage::disk('local')->put(self::REPO_CONFIG_FILE_NAME,'repository:
  -
    #name: organization/repo
    #token: mytoken');
        }

        return;
    }

    /**
     * Get the "checks" sequence from the Config file
     *
     * @param string $repoName
     * @return array
     */
    public function getChecks(string $repoName) {
        $contents = Storage::get(self::REPO_CONFIG_FILE_NAME);
        $value = Yaml::parse($contents);

        $collection = collect($value);
        $repoNode = collect($collection->get('repositories'));
        $repoNode = collect($repoNode->where('name', $repoName)->first());

        return collect($repoNode)->get('checks');
    }
}