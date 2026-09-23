<?php

 class CapitalData{

    public function __construct(
        public readonly string $cca2,
        public readonly string $cityName,
        public readonly string $countryName,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly string $currency,
        public readonly ?int $population,
        public readonly string $flagUrl,
    ){

    }
 }