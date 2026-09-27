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
        $total = count($capitals);
        $count = 0;

        foreach($capitals as $index => $capital) {
            $position = $index + 1;

            WP_CLI::log("[{$position}/{$total}] Processing: {$capital->cityName}");

            $postId = $this->cities->createOrUpdate($capital);

            if($postId === null) {
                WP_CLI::warning("[{$position}/{$total}] Skipping {$capital->cityName}: could not create or update post.");
                continue;
            }

            $this->cities->saveFields($postId, $capital);
            $count++;
        }

        return $count;
    }
}