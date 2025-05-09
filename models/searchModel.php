<?php

class SearchModel {
    public function search($query) {
        // Replace this block with real API or DB call
        $dummyData = [
            ['title' => 'Climate Change Research', 'description' => 'Explores the impact of climate change.'],
            ['title' => 'AI in Healthcare', 'description' => 'Discusses how AI is used in medical fields.'],
            ['title' => 'Renewable Energy', 'description' => 'Focuses on clean and sustainable energy sources.']
        ];

        // Simulate filtering by keyword
        $results = [];
        foreach ($dummyData as $item) {
            if (stripos($item['title'], $query) !== false || stripos($item['description'], $query) !== false) {
                $results[] = $item;
            }
        }

        return $results;
    }
}
