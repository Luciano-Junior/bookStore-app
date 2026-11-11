<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a book payload.
     */
    private function bookData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Clean Code',
            'isbn' => '9780132350884',
            'value' => 120.00,
        ], $overrides);
    }

    /**
     * User can list books.
     */
    public function test_user_can_list_books(): void
    {
        $user = User::factory()->create();

        Book::create($this->bookData());

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/books');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Clean Code');
    }

    /**
     * User can retrieve a specific book.
     */
    public function test_user_can_retrieve_a_book(): void
    {
        $user = User::factory()->create();

        $book = Book::create($this->bookData());

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/books/{$book->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.name', 'Clean Code');
    }

    /**
     * User can create a book.
     */
    public function test_user_can_create_a_book(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', $this->bookData());

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Clean Code');

        $this->assertDatabaseHas('books', [
            'name' => 'Clean Code',
            'isbn' => '9780132350884',
        ]);
    }

    /**
     * Book name is required.
     */
    public function test_book_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', [
                'isbn' => '9780132350884',
                'value' => 120.00,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * ISBN must contain numbers only.
     */
    public function test_isbn_must_contain_numbers_only(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', $this->bookData([
                'isbn' => 'ABC123XYZ',
            ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);
    }

    /**
     * ISBN must be unique when provided.
     */
    public function test_isbn_must_be_unique(): void
    {
        $user = User::factory()->create();

        Book::create($this->bookData());

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', $this->bookData([
                'name' => 'Another Book',
            ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);
    }

    /**
     * Books can be created without ISBN.
     */
    public function test_book_can_be_created_without_isbn(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/books', $this->bookData([
                'isbn' => null,
            ]));

        $response->assertCreated();

        $this->assertDatabaseHas('books', [
            'name' => 'Clean Code',
            'isbn' => null,
        ]);
    }

    /**
     * User can update a book.
     */
    public function test_user_can_update_a_book(): void
    {
        $user = User::factory()->create();

        $book = Book::create($this->bookData());

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/books/{$book->id}", [
                'name' => 'Clean Code Updated',
                'isbn' => '9780132350884',
                'value' => 150.00,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Clean Code Updated');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'name' => 'Clean Code Updated',
        ]);
    }

    /**
     * User cannot reuse another book's ISBN.
     */
    public function test_user_cannot_update_book_with_an_existing_isbn(): void
    {
        $user = User::factory()->create();

        $firstBook = Book::create($this->bookData());

        $secondBook = Book::create($this->bookData([
            'name' => 'Design Patterns',
            'isbn' => '9780201633610',
        ]));

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/books/{$secondBook->id}", [
                'name' => 'Design Patterns',
                'isbn' => $firstBook->isbn,
                'value' => 150.00,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);
    }

    /**
     * User can delete a book.
     */
    public function test_user_can_delete_a_book(): void
    {
        $user = User::factory()->create();

        $book = Book::create($this->bookData());

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/books/{$book->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    /**
     * Unauthenticated users cannot access books.
     */
    public function test_guest_cannot_access_books(): void
    {
        $response = $this->getJson('/api/books');

        $response->assertUnauthorized();
    }

    /**
     * Return 404 when book does not exist.
     */
    public function test_nonexistent_book_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/books/99999');

        $response->assertNotFound();
    }
}
