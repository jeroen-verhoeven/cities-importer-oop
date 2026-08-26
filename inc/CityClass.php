<?php

class City {
    private ?string $cca2 = null;
    private ?int $postId = null;

    public ?string $cityName = null;
    public ?string $countryName = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?string $currency = null;
    public ?int $population = null;
    public ?string $flagUrl = null;

    public function __construct(string $cca2, int $postId){
        $this->cca2 = $cca2;
        $this->postId = $postId;
    }

    public function getCca2() : string {
        return $this->cca2;
    }

    public function getPostId(): int {
        return $this->postId;
    }
}