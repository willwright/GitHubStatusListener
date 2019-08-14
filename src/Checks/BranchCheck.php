<?php

namespace MeCodeNinja\GitHubWebhooks\Listeners;

use Illuminate\Support\Facades\Log;
use MeCodeNinja\Checks\CheckAbstract;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BranchCheck extends CheckAbstract
{
    /** @var string */
    private $_repoPath;

    /**
     * This is specific implementation of check for this specific Listener
     *
     * @return bool
     */
    function doCheck()
    {
        $json = json_decode($this->_content);

        $headRef = $json->pull_request->head->ref;
        $clone_url = $json->repository->clone_url;
        $name = $json->repository->name;

        /**
         * Create the directory where we will clone the repo
         */
        $this->_repoPath = storage_path().'/repos/'. $name;

        if (!is_dir($this->_repoPath)) {
            mkdir($this->_repoPath,0777,true);
        }

        $tokenCloneUrl = $this->getTokenCloneUrl($this->_token, $clone_url);

        $process = new Process("git clone $tokenCloneUrl $this->_repoPath");
        $process->setTimeout(3600);
        try {
            $process->run();
        } catch (ProcessFailedException $processFailedException) {
            Log::error($processFailedException->getMessage());
            Log::error($process->getErrorOutput());
            return false;
        }

        $process = new Process("git fetch");
        $process->setWorkingDirectory($this->_repoPath);
        $process->setTimeout(3600);
        try {
            $process->run();
        } catch (ProcessFailedException $processFailedException) {
            Log::error($processFailedException->getMessage());
            Log::error($process->getErrorOutput());
            return false;
        }

        /**
         * Do the actual check to determine status
         */
        if ($this->hasBranchMerged("origin/$headRef",'origin/develop')
            || $this->isForkedFrom("origin/$headRef",'origin/develop')) {
            return false;
        } else {
            return true;
        }

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

    function getDescription()
    {
        return "Passes if branch does not contain origin/develop";
    }

    /**
     * Is branch a forked from branch b
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    private function isForkedFrom(string $a, string $b) {
        $process = new Process("git merge-base --fork-point $a $b");
        $process->setWorkingDirectory($this->_repoPath);
        $process->setTimeout(3600);
        $process->run();

        return strlen($process->getOutput()) > 0;

    }

    /**
     * Does haystack have needleBranch merged into it
     *
     * @param string $haystack
     * @param string $needleBranch
     * @return bool
     */
    private function hasBranchMerged(string $haystack, string $needleBranch) {
        $process = new Process("git branch -r --merged $haystack");
        $process->setWorkingDirectory($this->_repoPath);
        $process->setTimeout(3600);
        $process->run();

        $mergedBranches = explode(PHP_EOL,$process->getOutput());
        $trimmed = collect($mergedBranches)->map(function($item,$key){
            return trim($item);
        });

        return $trimmed->contains(function($value,$key) use ($needleBranch){
            return ends_with($value,$needleBranch);
        });
    }

    /**
     * Get the URL to clone using Token authentication
     *
     * @param string $token
     * @param string $clone_url
     * @return string
     */
    private function getTokenCloneUrl(string $token, string $clone_url) {
        return str_replace_first('github.com',"$token@github.com",$clone_url);
    }
}
