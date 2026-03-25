<?php

namespace App\Services;

use Illuminate\Support\Str;

class GrocerySortingService
{
    /**
     * Standard Swedish Store Layout Heuristic (Aisle Order)
     */
    protected array $aisleOrder = [
        'produce'   => 1,
        'bakery'    => 2,
        'deli'      => 3,
        'dairy'     => 4,
        'pantry'    => 5,
        'frozen'    => 6,
        'snacks'    => 7,
        'hygiene'   => 8,
        'other'     => 99,
    ];

    /**
     * Local keyword map for instant categorization.
     * Swedish + English keywords, lowercase.
     */
    protected array $keywordMap = [
        'produce' => [
            'äpple', 'banan', 'tomat', 'gurka', 'lök', 'potatis', 'morötter', 'morot',
            'sallad', 'paprika', 'avokado', 'citron', 'lime', 'apelsin', 'päron',
            'vindruvor', 'jordgubbar', 'blåbär', 'hallon', 'mango', 'ananas',
            'melon', 'svamp', 'champinjon', 'broccoli', 'spenat', 'grönkål',
            'frukt', 'grönsak', 'grönt', 'zucchini', 'selleri', 'rödbetor',
            'apple', 'banana', 'tomato', 'cucumber', 'onion', 'potato', 'carrot',
            'lettuce', 'pepper', 'avocado', 'lemon', 'orange', 'pear', 'grapes',
            'strawberry', 'blueberry', 'pineapple', 'mushroom',
            'broccoli', 'spinach', 'kale', 'fruit', 'vegetable',
        ],
        'bakery' => [
            'bröd', 'limpa', 'fralla', 'bulle', 'kaka', 'knäckebröd', 'tortilla',
            'bread', 'roll', 'bun', 'cake', 'crispbread', 'croissant', 'bagel',
        ],
        'deli' => [
            'kött', 'fläsk', 'nöt', 'kyckling', 'fisk', 'lax', 'torsk', 'räkor',
            'korv', 'bacon', 'skinka', 'köttfärs', 'biff', 'entrecote', 'fläskfilé',
            'meat', 'pork', 'beef', 'chicken', 'fish', 'salmon', 'cod', 'shrimp',
            'sausage', 'ham', 'mince', 'steak',
        ],
        'dairy' => [
            'mjölk', 'mellanmjölk', 'lättmjölk', 'ost', 'smör', 'grädde', 'yoghurt',
            'ägg', 'cream cheese', 'kvarg', 'fil', 'créme fraiche', 'kesella',
            'milk', 'cheese', 'butter', 'cream', 'yogurt', 'egg', 'eggs',
        ],
        'pantry' => [
            'pasta', 'ris', 'mjöl', 'socker', 'salt', 'peppar', 'olja', 'vinäger',
            'soja', 'ketchup', 'senap', 'krydda', 'kaffe', 'te', 'müsli', 'flingor',
            'konserv', 'bönor', 'linser', 'nötter', 'mandlar',
            'rice', 'flour', 'sugar', 'oil', 'vinegar', 'soy', 'mustard',
            'spice', 'coffee', 'tea', 'cereal', 'canned', 'beans', 'lentils', 'nuts',
        ],
        'frozen' => [
            'fryst', 'glass', 'frysta', 'pizza',
            'frozen', 'ice cream',
        ],
        'snacks' => [
            'godis', 'chips', 'choklad', 'läsk', 'öl', 'vin', 'saft', 'juice',
            'popcorn', 'kex', 'tuggummi', 'coca cola', 'pepsi', 'fanta',
            'candy', 'chocolate', 'soda', 'beer', 'wine', 'cookies',
            'crackers', 'snack',
        ],
        'hygiene' => [
            'toalettpapper', 'tvål', 'schampo', 'tandkräm', 'deodorant', 'diskmedel',
            'tvättmedel', 'hushållspapper', 'blöjor', 'tamponger', 'bindor',
            'rengöring', 'sköljmedel',
            'toilet paper', 'soap', 'shampoo', 'toothpaste', 'detergent',
            'diapers', 'paper towel', 'cleaning',
        ],
    ];

    /**
     * Get a sorting score for a product based on its name
     */
    public function getSortScore(string $productName): int
    {
        $category = $this->getCategoryForProduct($productName);
        return $this->aisleOrder[$category] ?? 99;
    }

    /**
     * Determine category using local keyword matching (instant)
     */
    public function getCategoryForProduct(string $name): string
    {
        if (empty(trim($name))) return 'other';

        $lower = Str::lower(trim($name));

        foreach ($this->keywordMap as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($lower, $keyword)) {
                    return $category;
                }
            }
        }

        return 'other';
    }
}
