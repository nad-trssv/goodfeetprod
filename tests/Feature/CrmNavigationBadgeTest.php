<?php

namespace Tests\Feature;

use App\Models\CrmChatStaff;
use App\Models\CrmConversation;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmNavigationBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_parent_and_chat_link_show_the_same_unread_count(): void
    {
        $role=Roles::create(['name'=>'Master']);
        $master=User::factory()->create(['role_id'=>$role->id]);
        CrmChatStaff::create(['user_id'=>$master->id,'is_enabled'=>true,'can_view_history'=>false,'must_answer'=>false]);
        $conversation=CrmConversation::create([
            'access_token_hash'=>hash('sha256',str_repeat('a',64)),
            'visitor_name'=>'Badge visitor',
            'assigned_to_user_id'=>$master->id,
            'last_message_at'=>now(),
        ]);
        $conversation->messages()->create(['sender_type'=>'visitor','body'=>'Unread']);

        $this->actingAs($master)->get(route('crm.chat.index'))
            ->assertOk()
            ->assertSee('id="sidebar-crm-group-count"',false)
            ->assertSee('id="sidebar-crm-chat-count"',false);
    }
}
