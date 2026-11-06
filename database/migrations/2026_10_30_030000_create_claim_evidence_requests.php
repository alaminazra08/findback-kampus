<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('claim_evidence_requests', function(Blueprint $t) {
 $t->id(); $t->foreignId('item_claim_id')->constrained('item_claims')->cascadeOnDelete();
 $t->unsignedBigInteger('requested_by'); $t->string('requester_name'); $t->text('note');
 $t->string('closure_type')->nullable(); $t->timestamp('closed_at')->nullable(); $t->unsignedBigInteger('closed_by')->nullable();
 $t->string('closer_name')->nullable(); $t->text('closure_note')->nullable();
 $t->timestamps(); $t->index(['item_claim_id','closed_at']);
 }); }
 public function down(): void { Schema::dropIfExists('claim_evidence_requests'); }
};
