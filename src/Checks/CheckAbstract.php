<?php


namespace MeCodeNinja\GitHubWebhooks\Checks;


abstract class CheckAbstract implements CheckInterface
{
    /** @var string */
    protected $_content;

    /** @var string */
    protected $_token;

    /** @var array */
    protected $_config;

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->_token = $token;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }

}