<?php


namespace MeCodeNinja\Checks;


interface CheckInterface
{
    function doCheck();
    function getContext();
    function getDescription();
}