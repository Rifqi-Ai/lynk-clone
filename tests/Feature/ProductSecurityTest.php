<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $creator;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->creator = User::factory()->create(['transaction_fee_pct' => 10]);
    }

    public function test_guest_cannot_create_product(): void
    {
        $response = $this->post('/dashboard/products', [
            'type' => 'digital',
            'title' => 'Test',
            'price' => 10000,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_product(): void
    {
        $this->actingAs($this->creator);

        $response = $this->post('/dashboard/products', [
            'type' => 'digital',
            'title' => 'My Ebook',
            'description' => 'Test description',
            'price' => 50000,
            'publish' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'user_id' => $this->creator->id,
            'title' => 'My Ebook',
            'type' => 'digital',
            'status' => 'published',
            'price' => 50000,
        ]);
    }

    public function test_product_mass_assignment_cannot_inject_metadata(): void
    {
        $this->actingAs($this->creator);

        // Try to inject is_admin via metadata (mass assignment attack)
        $response = $this->post('/dashboard/products', [
            'type' => 'digital',
            'title' => 'Hack Attempt',
            'price' => 100,
            'metadata' => ['is_admin' => true, 'free_paid' => true],
            'user_id' => 999, // try to inject another user_id
            'id' => 'injected-id',
            'status' => 'published',
        ]);

        $product = Product::where('title', 'Hack Attempt')->first();
        $this->assertNotNull($product);
        $this->assertEquals($this->creator->id, $product->user_id, 'user_id must not be mass-assignable');
        $this->assertEquals('draft', $product->status, 'status should default to draft when publish not set in form');
        $this->assertNotEquals('injected-id', $product->id, 'id must be auto-generated');
        $this->assertArrayNotHasKey('is_admin', $product->metadata ?? []);
    }

    public function test_product_cannot_upload_executable_file(): void
    {
        $this->actingAs($this->creator);

        // Try to upload a PHP file disguised as something else
        $phpFile = UploadedFile::fake()->createWithContent('malicious.php', '<?php system($_GET["cmd"]); ?>');

        $response = $this->post('/dashboard/products', [
            'type' => 'digital',
            'title' => 'Malicious Product',
            'price' => 100,
            'file' => $phpFile,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('products', ['title' => 'Malicious Product']);
    }

    public function test_product_cannot_upload_exe_file(): void
    {
        $this->actingAs($this->creator);

        $exeFile = UploadedFile::fake()->create('virus.exe', 1024);

        $response = $this->post('/dashboard/products', [
            'type' => 'digital',
            'title' => 'Virus Product',
            'price' => 100,
            'file' => $exeFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_product_can_upload_safe_file_types(): void
    {
        $this->actingAs($this->creator);

        $pdfFile = UploadedFile::fake()->create('ebook.pdf', 1024, 'application/pdf');

        $response = $this->post('/dashboard/products', [
            'type' => 'digital',
            'title' => 'PDF Ebook',
            'price' => 100,
            'file' => $pdfFile,
            'publish' => 1,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('products', ['title' => 'PDF Ebook']);
    }

    public function test_user_cannot_edit_other_users_product(): void
    {
        $otherUser = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $otherUser->id,
            'type' => 'digital',
            'title' => 'Other User Product',
            'price' => 100,
        ]);

        $this->actingAs($this->creator);

        $response = $this->get("/dashboard/products/{$product->id}/edit");
        $response->assertStatus(403);

        $response = $this->put("/dashboard/products/{$product->id}", [
            'type' => 'digital',
            'title' => 'Hacked Title',
            'price' => 999,
        ]);
        $response->assertStatus(403);

        // Verify the title was NOT changed
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Other User Product',
        ]);
    }

    public function test_user_cannot_delete_other_users_product(): void
    {
        $otherUser = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other User Product',
        ]);

        $this->actingAs($this->creator);

        $response = $this->delete("/dashboard/products/{$product->id}");
        $response->assertStatus(403);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_user_can_edit_own_product(): void
    {
        $product = Product::factory()->create([
            'user_id' => $this->creator->id,
            'type' => 'digital',
            'title' => 'My Product',
            'price' => 100,
            'status' => 'draft',
        ]);

        $this->actingAs($this->creator);

        $response = $this->put("/dashboard/products/{$product->id}", [
            'type' => 'digital',
            'title' => 'Updated Title',
            'price' => 200,
            'publish' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Updated Title',
            'price' => 200,
            'status' => 'published',
        ]);
    }

    public function test_compare_at_price_must_be_greater_than_price(): void
    {
        $this->actingAs($this->creator);

        $response = $this->post('/dashboard/products', [
            'type' => 'digital',
            'title' => 'Bad Discount',
            'price' => 100,
            'compare_at_price' => 50, // lower than price — should fail
        ]);

        $response->assertSessionHasErrors('compare_at_price');
    }

    public function test_price_must_be_numeric(): void
    {
        $this->actingAs($this->creator);

        $response = $this->post('/dashboard/products', [
            'type' => 'digital',
            'title' => 'Bad Price',
            'price' => 'free',
        ]);

        $response->assertSessionHasErrors('price');
    }
}
