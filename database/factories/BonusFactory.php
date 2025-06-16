<?php

namespace Database\Factories;

use App\Models\Bonus;
use Illuminate\Database\Eloquent\Factories\Factory;

class BonusFactory extends Factory
{
    protected $model = Bonus::class;

    public function definition(): array
    {
        return [
            'user_id' => null, // يتم تعيينه يدوياً في الاختبارات
            'amount' => $this->faker->randomFloat(2, 50, 500),
            // 'description' => 'مكافأة تجريبية', // تم التعليق لتجنب الخطأ في حال عدم وجود العمود
        ];
    }
}
