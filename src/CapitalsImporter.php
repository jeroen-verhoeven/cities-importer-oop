<?php

class CapitalsImporter{
    public function __construct(
        private CapitalProvider $provider,
        private CityRepository $cities,
    ) {
    }

    /**
     * @return int Number of cities successfully created or updated.
     */
    public function run(): int {
        $capitals = $this->provider->fetchCapitals();
        $count = 0;

        foreach($capitals as $capital) {
            $postId = $this->cities->createOrUpdate($capital);

            if($postId === null) {
                continue;
            }

            $this->cities->saveFields($postId, $capital);
            $count++;
        }

        return $count;
    }
}