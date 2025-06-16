<?php

namespace Database\Factories;

use App\Models\Deduction;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeductionFactory extends Factory
{
    protected $model = Deduction::class;

    public function definition(): array
    {
        return [
            'user_id' => null, // يتم تعيينه في الاختبار
            'amount' => $this->faker->randomFloat(2, 20, 200),
            // 'description' => 'خصم تجريبي', // ❌ أزل هذا السطر لأنه يسبب الخطأ
        ];
    }
}
