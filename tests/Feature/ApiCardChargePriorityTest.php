<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CreditCard;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiCardChargePriorityTest extends TestCase
{
    use DatabaseTransactions;

    protected $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = new Account();
        $this->account->account_id = 'TEST_' . rand(10000, 99999);
        $this->account->f_name = 'Test';
        $this->account->lname = 'User';
        $this->account->phone = '8455550000';
        $this->account->email = 'testuser' . rand(1000, 9999) . '@example.com';
        $this->account->account_type = 'prepaid';
        $this->account->balance = 50.00;
        $this->account->status = 1;
        $this->account->save();
    }

    /**
     * Test 1: Adding a new card sets it to priority 1 and resets older cards to priority 0.
     */
    public function test_add_card_sets_priority_1_and_resets_previous_cards_to_0(): void
    {
        // Setup an existing card with priority 1
        $oldCard = new CreditCard();
        $oldCard->account_id = $this->account->account_id;
        $oldCard->card_number = '4***********4242';
        $oldCard->charge_priority = 1;
        $oldCard->expiry = '2030-12-01';
        $oldCard->cardnox_token = 'token_old_111';
        $oldCard->save();

        // Add a new card via /api/add-card
        $response = $this->postJson('/api/add-card', [
            'account' => $this->account->account_id,
            'card_number' => '5563101651188991',
            'cvc' => '927',
            'expiry_month' => '10',
            'expiry_year' => '31',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'message' => 'Charge Successfully',
        ]);

        // Verify the old card is now priority 0
        $oldCard->refresh();
        $this->assertEquals(0, (int) $oldCard->charge_priority);

        // Verify the newly created card has priority 1
        $newCard = CreditCard::where('account_id', $this->account->account_id)
            ->where('id', '!=', $oldCard->id)
            ->first();

        $this->assertNotNull($newCard);
        $this->assertEquals(1, (int) $newCard->charge_priority);
    }

    /**
     * Test 2: If the first card is declined, it tries the other card and approves if second card works.
     */
    public function test_try_charge2_falls_back_to_second_card_if_first_card_is_declined(): void
    {
        config(['app.CARDKNOX_ENV_LIVE' => true]);

        // Card 1: Primary (priority 1), but with an invalid token so it gets declined
        $declinedCard = new CreditCard();
        $declinedCard->account_id = $this->account->account_id;
        $declinedCard->card_number = '4***********1111';
        $declinedCard->charge_priority = 1;
        $declinedCard->expiry = '2030-12-01';
        $declinedCard->cardnox_token = 'invalid_declined_token_999';
        $declinedCard->save();

        // Card 2: Secondary (priority 0), with a valid sandbox token that approves
        $approvedCard = new CreditCard();
        $approvedCard->account_id = $this->account->account_id;
        $approvedCard->card_number = '5***********8991';
        $approvedCard->charge_priority = 0;
        $approvedCard->expiry = '2031-10-01';
        $approvedCard->cardnox_token = '94352hph47hm7p855m8107q6310h215m'; // valid token
        $approvedCard->save();

        $initialBalance = $this->account->balance;

        $response = $this->postJson('/api/try-charge2', [
            'account' => $this->account->account_id,
            'amount' => 5.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'message' => 'Charge Successfully',
        ]);

        // Account balance should have increased by 5.00
        $this->account->refresh();
        $this->assertEquals($initialBalance + 5.00, (float) $this->account->balance);
    }

    /**
     * Test 3: If all cards are declined, it returns status declined.
     */
    public function test_try_charge2_returns_declined_if_all_cards_fail(): void
    {
        config(['app.CARDKNOX_ENV_LIVE' => true]);

        // Card 1: Primary, invalid token
        $card1 = new CreditCard();
        $card1->account_id = $this->account->account_id;
        $card1->card_number = '4***********1111';
        $card1->charge_priority = 1;
        $card1->expiry = '2030-12-01';
        $card1->cardnox_token = 'invalid_token_1';
        $card1->save();

        // Card 2: Secondary, invalid token
        $card2 = new CreditCard();
        $card2->account_id = $this->account->account_id;
        $card2->card_number = '5***********2222';
        $card2->charge_priority = 0;
        $card2->expiry = '2031-10-01';
        $card2->cardnox_token = 'invalid_token_2';
        $card2->save();

        $initialBalance = $this->account->balance;

        $response = $this->postJson('/api/try-charge2', [
            'account' => $this->account->account_id,
            'amount' => 25.00,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'valid' => false,
            'message' => 'Card Decline',
        ]);

        // Balance must not change
        $this->account->refresh();
        $this->assertEquals($initialBalance, (float) $this->account->balance);
    }
}

