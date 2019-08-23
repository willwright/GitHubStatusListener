<?php


namespace MeCodeNinja\GitHubWebhooks\GitHub;


use Illuminate\Support\Facades\Storage;
use MeCodeNinja\GitHubWebhooks\Check\CheckFactory;
use MeCodeNinja\GitHubWebhooks\Repository\Repository;
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
            Storage::disk('local')->put(self::REPO_CONFIG_FILE_NAME,'repositories:
  -
    name: organization/repo
    token: token
    checks:
      BranchCheck:
        branches: [origin/develop]
      PathCheck:
        paths: [/^vendor\//]');
        }

        return;
    }

    /**
     * Get the "checks" sequence from the Config file for the given repo
     *
     * @param string $repoName
     * @return array
     */
    public function getChecks(string $repoName) {
        $contents = Storage::get(self::REPO_CONFIG_FILE_NAME);
        $value = Yaml::parse($contents);

        $collection = collect($value);
        $repoNode = collect($collection->get('repositories'));
        //@TODO: Have this return an array of matched checks
        $repoNode = collect($repoNode->where('name', $repoName)
            ->where('name', '*')
            ->all());

        return collect($repoNode)->get('checks');
    }

    /**
     * Returns an Array of Repositories which match the given input
     *
     * @param string $repoName
     * @return array
     */
    public function getRepositoriesByName(string $repoName) {
        $repositoriesArr = [];
        $contents = Storage::get(self::REPO_CONFIG_FILE_NAME);
        $value = Yaml::parse($contents);

        $collection = collect($value);
        $repositories = collect($collection->get('repositories'));
        $repositories->whereIn('name', [$repoName,'*'])
            ->each(function($item, $key) use (&$repositoriesArr) {
                $repository = new Repository();
                $repository->setName($item['name']);
                $repository->setToken($item['token']);
                foreach ($item['checks'] as $key => $value) {
                    $repository->appendCheck(CheckFactory::create($key, $repository->getToken(), $value));
                }
                $repositoriesArr[] = $repository;
            });

        return $repositoriesArr;
    }
}