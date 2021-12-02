<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use App\Models\Reseller;

class CreateTableResellers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upline_id')->default(0);
            $table->json('uplines')->default(new Expression('(JSON_ARRAY())'));
            $table->tinyInteger('level')->comment('0:referrer,1:master agent,2:agent,3:reseller');
            $table->string('name')->unique()->index();
            $table->string('username')->unique()->index();
            $table->string('phone', 20);
            $table->decimal('credit', 14, 4)->default(0);
            $table->decimal('coin', 14, 4)->default(0);
            $table->string('currency', 6);
            $table->json('payin')->default(new Expression('(JSON_OBJECT())'));
            $table->json('payout')->default(new Expression('(JSON_OBJECT())'));
            $table->unsignedInteger('downline_slot')->default(0);
            $table->tinyInteger('status')->default(0)->comment('0:inactive,1:active,2:disabled');
            $table->tinyInteger('online')->default(0)->comment('0:offline,1:online');
            $table->string('password', 60);
            $table->timestamps();
        });

        Schema::create('reseller_bank_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained();
            $table->foreignId('payment_channel_id')->constrained();
            $table->json('attributes')->default(new Expression('(JSON_OBJECT())'));
            $table->unsignedTinyInteger('status')->default(1)->comment('0:Inactive,1:Active,2:Disabled');
            $table->timestamps();
        });

        Schema::create('reseller_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained();
            $table->unsignedBigInteger('audit_admin_id')->default(0);
            $table->string('order_id', 60)->unique();
            $table->unsignedTinyInteger('transaction_type');
            $table->unsignedTinyInteger('type')->comment('0:Credit,1:Coin');
            $table->decimal('amount', 14, 4);
            $table->unsignedTinyInteger('status')
                ->comment('0:Pending,1:Approved,2:Rejected');
            $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
            $table->timestamps();
        });

        Schema::create('reseller_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reseller_id')->constrained();
            $table->unsignedBigInteger('reseller_bank_card_id')->default(0);
            $table->unsignedBigInteger('audit_admin_id')->default(0);
            $table->string('order_id', 60)->unique();
            $table->unsignedTinyInteger('transaction_type');
            $table->unsignedTinyInteger('type')->comment('0:Credit,1:Coin');
            $table->decimal('amount', 14, 4);
            $table->unsignedTinyInteger('status')
                ->comment('0:Pending,1::Approved,2:Rejected');
            $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
            $table->timestamps();
        });
        Schema::create('reseller_sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id');
            $table->unsignedBigInteger('model_id')->default(0);
            $table->string('model_name', 20)->default('');
            $table->string('platform', 10);
            $table->string('address', 30);
            $table->string('body', 1024);
            $table->unsignedTinyInteger('status')->default(0)->comment('0:Pending,1:Match,2:UnMatch');
            $table->timestamp('sent_at');
            $table->timestamp('received_at');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reseller_sms');
        Schema::dropIfExists('reseller_withdrawals');
        Schema::dropIfExists('reseller_deposits');
        Schema::dropIfExists('reseller_bank_cards');
        Schema::dropIfExists('resellers');
    }
}
