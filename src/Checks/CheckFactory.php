<?php


namespace MeCodeNinja\GitHubWebhooks\Checks;

class CheckFactory
{
    /**
     * @param $checkType
     * @param $content
     * @param $token
     * @return CheckAbstract|null
     */
    public static function create($checkType, $content, $token) {
        $namespace = '\\MeCodeNinja\\GitHubWebhooks\\Checks\\';
        $className = $namespace . $checkType;
        try {
            /** @var CheckAbstract $checkObj */
            $checkObj = new $className();
            $checkObj->setContent($content);
            $checkObj->setToken($token);

            return $checkObj;
        } catch (\ReflectionException $reflectionException) {

        }

        return null;
    }
}