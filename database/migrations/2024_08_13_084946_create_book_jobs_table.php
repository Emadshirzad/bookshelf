<?php

use App\Models\Book;
use App\Models\book_user;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('book_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(User::class, 'to_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignIdFor(Book::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(book_user::class, 'type')->constrained('book_users')->onDelete('cascade');
            $table->integer('price');
            $table->integer('sold_price');
            $table->enum('status', ['delivered', 'wating_deliver', 'is_waiting']);
            $table->string('end_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_jobs');
    }
};
