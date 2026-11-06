<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  foreach (['closure_kind','response_note','before_data','after_data'] as $column) {
   if (!Schema::hasColumn('report_revision_requests',$column)) Schema::table('report_revision_requests',function(Blueprint $table) use($column) {
    if ($column==='closure_kind') $table->string($column,30)->nullable();
    elseif ($column==='response_note') $table->text($column)->nullable();
    else $table->json($column)->nullable();
   });
  }
  if (!Schema::hasTable('report_audit_events'))
  Schema::create('report_audit_events', function (Blueprint $table) {
   $table->id();
   $table->unsignedBigInteger('lost_found_item_id')->index();
   $table->unsignedBigInteger('actor_id')->nullable();
   $table->string('actor_name');
   $table->string('event', 40);
   $table->text('detail')->nullable();
   $table->json('snapshot')->nullable();
   $table->timestamp('occurred_at');
  });
 }
 public function down(): void {
  if (!app()->environment('testing')) throw new \RuntimeException('Rollback audit produksi dinonaktifkan; pulihkan file tanpa menghapus riwayat.');
  Schema::dropIfExists('report_audit_events');
  Schema::table('report_revision_requests', fn(Blueprint $table)=>$table->dropColumn(['closure_kind','response_note','before_data','after_data']));
 }
};
