<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_crm_profiles', function (Blueprint $table) {
            $table->foreignId('customer_id')->primary()->constrained('customers')->cascadeOnDelete();
            $table->text('contraindications')->nullable();
            $table->text('important_warnings')->nullable();
            $table->foreignId('preferred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('crm_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('slug', 100)->unique();
            $table->string('color', 7)->default('#64748b');
            $table->timestamps();
        });
        Schema::create('customer_crm_tag', function (Blueprint $table) {
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('crm_tag_id')->constrained('crm_tags')->cascadeOnDelete();
            $table->primary(['customer_id', 'crm_tag_id']);
        });

        Schema::create('customer_crm_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->index(['customer_id', 'is_pinned', 'created_at'], 'crm_notes_customer_order');
        });

        Schema::create('customer_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type', 80);
            $table->boolean('is_granted');
            $table->dateTime('captured_at');
            $table->dateTime('withdrawn_at')->nullable();
            $table->string('source', 80)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_id', 'type', 'captured_at']);
        });

        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_uuid')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 60)->default('other');
            $table->string('original_name');
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size');
            $table->timestamps();
            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('customer_preferred_services', function (Blueprint $table) {
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->primary(['customer_id', 'service_id']);
        });

        Schema::create('crm_chat_staff', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('can_view_history')->default(false);
            $table->boolean('must_answer')->default(false);
            $table->timestamps();
        });

        Schema::create('crm_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_uuid')->unique();
            $table->char('access_token_hash', 64);
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('visitor_name', 190)->nullable();
            $table->string('visitor_email', 190)->nullable();
            $table->string('visitor_phone', 32)->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('last_message_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'last_message_at']);
            $table->index(['assigned_to_user_id', 'status']);
        });

        Schema::create('crm_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('crm_conversations')->cascadeOnDelete();
            $table->string('sender_type', 20);
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->text('body');
            $table->timestamps();
            $table->index(['conversation_id', 'id']);
        });

        Schema::create('crm_conversation_reads', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained('crm_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('last_read_message_id')->nullable();
            $table->timestamps();
            $table->primary(['conversation_id', 'user_id']);
        });

        $now = now();
        foreach ([
            'crm.view' => ['crm', 'view'], 'crm.update' => ['crm', 'update'],
            'crm.documents' => ['crm', 'documents'], 'crm.chat.view' => ['crm_chat', 'view'],
            'crm.chat.reply' => ['crm_chat', 'reply'], 'crm.settings' => ['crm', 'settings'],
        ] as $key => [$group, $action]) {
            DB::table('permissions')->updateOrInsert(['key' => $key], [
                'group' => $group, 'action' => $action, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach (['super-admin', 'master', 'receptionist'] as $slug) {
            $roleId = DB::table('roles')->where('slug', $slug)->value('id');
            if (! $roleId) continue;
            $keys = $slug === 'super-admin'
                ? ['crm.view','crm.update','crm.documents','crm.chat.view','crm.chat.reply','crm.settings']
                : ['crm.view','crm.update','crm.documents','crm.chat.view','crm.chat.reply'];
            foreach (DB::table('permissions')->whereIn('key', $keys)->pluck('id') as $permissionId) {
                DB::table('permission_role')->insertOrIgnore(['role_id'=>$roleId,'permission_id'=>$permissionId]);
            }
        }

        foreach ([
            'enabled' => false, 'title' => 'Online chat',
            'welcome_message' => 'Hello! How can we help you?',
            'offline_message' => 'We are currently offline. Leave a message and we will reply during working hours.',
            'timezone' => config('app.timezone'),
            'schedule' => collect(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'])
                ->mapWithKeys(fn ($day) => [$day => ['enabled'=>!in_array($day,['saturday','sunday'],true),'start'=>'09:00','end'=>'18:00']])->all(),
        ] as $key => $value) {
            DB::table('site_settings')->updateOrInsert(['key'=>'crm_chat_'.$key], [
                'group'=>'crm_chat','payload'=>json_encode($value, JSON_UNESCAPED_UNICODE),'created_at'=>$now,'updated_at'=>$now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_conversation_reads');
        Schema::dropIfExists('crm_messages');
        Schema::dropIfExists('crm_conversations');
        Schema::dropIfExists('crm_chat_staff');
        Schema::dropIfExists('customer_preferred_services');
        Schema::dropIfExists('customer_documents');
        Schema::dropIfExists('customer_consents');
        Schema::dropIfExists('customer_crm_notes');
        Schema::dropIfExists('customer_crm_tag');
        Schema::dropIfExists('crm_tags');
        Schema::dropIfExists('customer_crm_profiles');
        DB::table('permissions')->whereIn('key', ['crm.view','crm.update','crm.documents','crm.chat.view','crm.chat.reply','crm.settings'])->delete();
        DB::table('site_settings')->where('group', 'crm_chat')->delete();
    }
};
