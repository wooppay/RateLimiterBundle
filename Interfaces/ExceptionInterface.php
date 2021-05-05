<?php


namespace wooppay\RateLimiterBundle\Interfaces;


interface ExceptionInterface
{
    public function getMessage() : ?string;
}
