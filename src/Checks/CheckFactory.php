<?php


namespace MeCodeNinja\GitHubWebhooks\Checks;

use Exception;
use Illuminate\Support\Facades\Log;

class CheckFactory
{
    /**
     * @param $checkType
     * @param $content
     * @param $token
     * @param array $config
     * @return CheckAbstract|null
     */
    public static function create($checkType, $content, $token, $config = []) {
        $me = new CheckFactory();
        $className = $me->getClassByKey($checkType);
        if (empty($className)) {
            Log::error("CheckFactory failed trying to load: $className");
            Log::error("Key: $checkType");
        }

        if (!class_exists($className)) {
            Log::warning("ClassFactory got class which does not exist: $className");
            return null;
        }
        /** @var CheckAbstract $checkObj */
        $checkObj = new $className();
        $checkObj->setContent($content);
        $checkObj->setToken($token);
        $checkObj->setConfig($config);

        return $checkObj;
    }

    /**
     * @param $key
     * @return \Illuminate\Config\Repository|mixed
     */
    private function getClassByKey($key) {
        return config('githubwebhooks.checks.'.$key);
    }
}