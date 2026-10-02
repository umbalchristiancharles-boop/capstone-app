<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Product;
use App\Services\DishIngredientInventory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

class DishIngredientInventoryTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->boolean('is_active')->default(true);
            $table->string('unit')->nullable();
            $table->string('pack_unit')->nullable();
            $table->string('per_pack_or_individual')->default('individual');
            $table->decimal('pack_quantity', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('real_stock')->default(0);
            $table->decimal('open_pack_used', 12, 4)->default(0);
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('dish_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dish_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('name');
            $table->string('unit')->nullable();
            $table->decimal('per_serving', 12, 4)->default(0);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('dish_ingredients');
        Schema::dropIfExists('products');
        Schema::dropIfExists('dishes');

        parent::tearDown();
    }

    public function test_grams_convert_to_kilograms_and_recipe_cost_uses_purchase_unit_price(): void
    {
        $this->assertEqualsWithDelta(0.25, DishIngredientInventory::convertQuantity(250, 'g', 'kg'), 0.000001);

        [$servings, $cost] = $this->service()->calculate($this->dishWithIngredient(250), 1);

        $this->assertSame(8, $servings);
        $this->assertEqualsWithDelta(25, $cost, 0.000001);
    }

    public function test_consuming_a_gram_recipe_preserves_fractional_kilogram_usage(): void
    {
        $dish = $this->dishWithIngredient(250);

        $this->assertTrue($this->service()->consume($dish, 1, 5));
        $this->assertSame(1, Product::firstOrFail()->fresh()->stock);
        $this->assertEqualsWithDelta(0.25, (float) Product::firstOrFail()->fresh()->open_pack_used, 0.0001);
    }

    public function test_pack_priced_kilograms_use_pack_quantity_for_availability_and_cost(): void
    {
        [$servings, $cost] = $this->service()->calculate($this->dishWithIngredient(250, [
            'unit' => null,
            'per_pack_or_individual' => 'per_pack',
            'pack_quantity' => 5,
            'pack_unit' => 'kg',
            'cost_price' => 500,
            'stock' => 2,
        ]), 1);

        $this->assertSame(40, $servings);
        $this->assertEqualsWithDelta(25, $cost, 0.000001);
    }

    private function dishWithIngredient(float $grams, array $productOverrides = []): Dish
    {
        $product = Product::create([
            'name' => 'Flour',
            'branch_id' => 1,
            'is_active' => true,
            'unit' => 'kg',
            'cost_price' => 100,
            'stock' => 2,
            'open_pack_used' => 0,
            ...$productOverrides,
        ]);

        $dish = Dish::create(['name' => 'Test dish']);
        $dish->ingredients()->create([
            'product_id' => $product->id,
            'name' => 'Flour',
            'unit' => 'g',
            'per_serving' => $grams,
        ]);

        return $dish->load('ingredients.product');
    }

    private function service(): DishIngredientInventory
    {
        return new DishIngredientInventory();
    }
}