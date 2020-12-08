<?php


namespace wooppay\RateLimiterBundle\Interfaces;


interface StorageInterface
{
	public function save(string $ip, string $route) : void;

	public function getCount(string $ip, string $route, int $period) : int;
}