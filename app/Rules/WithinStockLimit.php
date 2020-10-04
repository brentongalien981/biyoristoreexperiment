<?php

namespace App\Rules;

use App\Product;
use Illuminate\Contracts\Validation\Rule;

class WithinStockLimit implements Rule
{
    private $product = null;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $value <= $this->product->quantity;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'That :attribute exceeds our stock limit';
    }
}
