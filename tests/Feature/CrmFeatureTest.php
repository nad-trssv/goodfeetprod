<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\CrmChatStaff;
use App\Models\CrmConversation;
use App\Models\CrmTag;
use App\Models\Customer;
use App\Models\Roles;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrmFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_complete_customer_crm_profile_and_segments(): void
    {
        [$admin, $master, , $customer, $service] = $this->records();
        $tag = CrmTag::create(['name'=>'VIP','slug'=>'vip','color'=>'#8b5cf6']);

        $this->actingAs($admin)->put(route('crm.customers.update', $customer), [
            'important_warnings'=>'Allergy warning', 'contraindications'=>'No heat',
            'preferred_user_id'=>$master->id, 'preferred_service_ids'=>[$service->id], 'tag_ids'=>[$tag->id],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customer_crm_profiles', ['customer_id'=>$customer->id,'preferred_user_id'=>$master->id,'important_warnings'=>'Allergy warning']);
        $this->assertDatabaseHas('customer_crm_tag', ['customer_id'=>$customer->id,'crm_tag_id'=>$tag->id]);
        $this->assertDatabaseHas('customer_preferred_services', ['customer_id'=>$customer->id,'service_id'=>$service->id]);
        $this->actingAs($admin)->get(route('crm.customers.index',['tag_id'=>$tag->id]))->assertOk()->assertSee($customer->full_name);
        $this->actingAs($admin)->get(route('crm.customers.show',$customer))->assertOk()->assertSee('Allergy warning')->assertSee('No heat')->assertSee('VIP');
    }

    public function test_master_customer_scope_is_enforced_for_every_crm_mutation(): void
    {
        [, $master, $otherMaster, $customer, $service] = $this->records();
        $foreign = Customer::create(['first_name'=>'Foreign','last_name'=>'Customer','email'=>'foreign@example.test','phone'=>'+37250000009','locale'=>'en']);
        $this->appointment($foreign,$otherMaster,$service);

        $this->actingAs($master)->post(route('crm.customers.notes.store',$foreign),['body'=>'Must not save'])->assertForbidden();
        $this->actingAs($master)->get(route('crm.customers.show',$foreign))->assertForbidden();
        $this->actingAs($master)->post(route('crm.customers.notes.store',$customer),['body'=>'Own customer note','is_pinned'=>1])->assertRedirect();
        $this->assertDatabaseHas('customer_crm_notes',['customer_id'=>$customer->id,'body'=>'Own customer note']);
    }

    public function test_customer_documents_are_private_validated_and_scope_protected(): void
    {
        Storage::fake('local');
        [$admin, $master, $otherMaster, $customer, $service] = $this->records();
        $document = UploadedFile::fake()->image('before-treatment.jpg',800,600)->size(500);

        $this->actingAs($admin)->post(route('crm.customers.documents.store',$customer),['category'=>'photo','file'=>$document])->assertRedirect()->assertSessionHasNoErrors();
        $stored = $customer->documents()->firstOrFail();
        Storage::disk('local')->assertExists($stored->path);
        $this->actingAs($master)->get(route('crm.documents.download',$stored))->assertOk();

        $foreign = Customer::create(['first_name'=>'Private','last_name'=>'File','email'=>'private-file@example.test','phone'=>'+37250000010','locale'=>'en']);
        $this->appointment($foreign,$otherMaster,$service);
        $stored->update(['customer_id'=>$foreign->id]);
        $this->actingAs($master)->get(route('crm.documents.download',$stored->fresh()))->assertForbidden();
    }

    public function test_public_chat_uses_secret_token_not_public_uuid_and_notifies_required_staff(): void
    {
        [$admin] = $this->records();
        $this->enableChat();
        CrmChatStaff::create(['user_id'=>$admin->id,'is_enabled'=>true,'can_view_history'=>true,'must_answer'=>true]);

        $response = $this->postJson(route('chat.store'), ['name'=>'Chat Visitor','email'=>'chat@example.test','message'=>'Need an appointment'])
            ->assertCreated()->assertJsonStructure(['uuid','token']);
        $conversation = CrmConversation::where('public_uuid',$response->json('uuid'))->firstOrFail();
        $this->assertNotSame($response->json('token'), $conversation->access_token_hash);
        $this->assertSame(hash('sha256',$response->json('token')), $conversation->access_token_hash);
        $this->assertDatabaseHas('notifications',['notifiable_id'=>$admin->id]);

        $this->postJson(route('chat.show',$conversation))->assertUnprocessable();
        $this->postJson(route('chat.show',$conversation),['token'=>str_repeat('x',64)])->assertForbidden();
        $this->postJson(route('chat.show',$conversation),['token'=>$response->json('token')])->assertOk()->assertJsonCount(1,'messages');
    }

    public function test_transferring_chat_grants_only_that_conversation_to_restricted_employee(): void
    {
        [$admin, $master] = $this->records();
        $this->enableChat();
        CrmChatStaff::create(['user_id'=>$master->id,'is_enabled'=>true,'can_view_history'=>false,'must_answer'=>false]);
        $created = $this->postJson(route('chat.store'),['name'=>'Visitor','phone'=>'+37250000111','message'=>'Hello'])->json();
        $conversation = CrmConversation::where('public_uuid',$created['uuid'])->firstOrFail();

        $this->actingAs($master)->get(route('crm.chat.show',$conversation))->assertForbidden();
        $this->actingAs($admin)->postJson(route('crm.chat.transfer',$conversation),['user_id'=>$master->id])->assertOk();
        $this->actingAs($master)->get(route('crm.chat.show',$conversation))->assertOk();
        $this->actingAs($master)->postJson(route('crm.chat.reply',$conversation),['message'=>'I will help you'])->assertCreated();
        $this->assertDatabaseHas('crm_messages',['conversation_id'=>$conversation->id,'sender_type'=>'staff','sender_id'=>$master->id,'body'=>'I will help you']);
    }

    public function test_only_full_scope_administrator_can_change_crm_settings(): void
    {
        [$admin, $master] = $this->records();
        $payload = $this->settingsPayload($admin,$master);
        $this->actingAs($master)->put(route('crm.settings.update'),$payload)->assertRedirect();
        $this->actingAs($admin)->put(route('crm.settings.update'),$payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('crm_chat_staff',['user_id'=>$master->id,'must_answer'=>true,'can_view_history'=>false]);
    }

    private function records(): array
    {
        $adminRole=Roles::firstOrCreate(['slug'=>'super-admin'],['name'=>'Super Admin','appointment_scope'=>'all','is_service_provider'=>true]);
        $masterRole=Roles::firstOrCreate(['slug'=>'master'],['name'=>'Master','appointment_scope'=>'own','is_service_provider'=>true]);
        $admin=User::factory()->create(['username'=>'@crm_admin','role_id'=>$adminRole->id]);
        $master=User::factory()->create(['name'=>'CRM Master','username'=>'@crm_master','role_id'=>$masterRole->id]);
        $other=User::factory()->create(['name'=>'Other Master','username'=>'@other_master','role_id'=>$masterRole->id]);
        $service=Services::create(['name'=>'CRM Service','eventColor'=>'#123456','price'=>50,'duration_minutes'=>60,'status'=>true,'is_deleted'=>false]);
        $customer=Customer::create(['first_name'=>'CRM','last_name'=>'Customer','email'=>'crm@example.test','phone'=>'+37250000001','locale'=>'en']);
        $this->appointment($customer,$master,$service);
        return [$admin,$master,$other,$customer,$service];
    }

    private function appointment(Customer $customer, User $master, Services $service): Appointments
    {
        return Appointments::create(['customer_id'=>$customer->id,'status'=>'completed','user_id'=>$master->id,'service_id'=>$service->id,'client_name'=>$customer->first_name,'client_lastname'=>$customer->last_name,'client_email'=>$customer->email,'client_phone'=>$customer->phone,'price'=>50,'appointment_start'=>'2030-01-10 09:00:00','appointment_end'=>'2030-01-10 10:00:00']);
    }

    private function enableChat(): void
    {
        SiteSettings::where('key','crm_chat_enabled')->update(['payload'=>'true']);
    }

    private function settingsPayload(User $admin, User $master): array
    {
        $schedule=[]; foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day) $schedule[$day]=['enabled'=>true,'start'=>'09:00','end'=>'18:00'];
        return ['enabled'=>true,'title'=>'Support','welcome_message'=>'Hello','offline_message'=>'We are away','timezone'=>'Europe/Tallinn','schedule'=>$schedule,'staff'=>[['user_id'=>$admin->id,'is_enabled'=>true,'can_view_history'=>true,'must_answer'=>true],['user_id'=>$master->id,'is_enabled'=>true,'can_view_history'=>false,'must_answer'=>true]]];
    }
}
