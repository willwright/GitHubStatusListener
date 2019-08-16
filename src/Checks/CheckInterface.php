<?php


namespace MeCodeNinja\GitHubWebhooks\Checks;


interface CheckInterface
{
    function doCheck();
    function getContext();
    function getDescription();
}