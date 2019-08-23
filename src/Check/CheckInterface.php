<?php


namespace MeCodeNinja\GitHubWebhooks\Check;


interface CheckInterface
{
    function doCheck($content);
    function getContext();
    function getDescription();
}