<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('report_revision_requests', function(Blueprint $t) {
 $t->id(); $t->foreignId('lost_found_item_id')->constrained('lost_found_items')->cascadeOnDelete();
 $t->unsignedBigInteger('requested_by'); $t->string('requester_name'); $t->text('note');
 $t->timestamp('closed_at')->nullable(); $t->unsignedBigInteger('closed_by')->nullable();
 $t->string('closer_name')->nullable(); $t->text('closure_note')->nullable();
 $t->timestamps(); $t->index(['lost_found_item_id','closed_at']);
 }); }
 public function down(): void { Schema::dropIfExists('report_revision_requests'); }
};
