<?php


namespace MeCodeNinja\GitHubWebhooks\Check;


interface CheckInterface
{
    function doCheck();
    function getContext();
    function getDescription();
}