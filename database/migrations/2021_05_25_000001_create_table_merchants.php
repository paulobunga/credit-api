<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMerchants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique()->index();
            $table->string('username')->unique()->index();
            $table->string('phone', 20);
            $table->string('api_key', 30);
            $table->string('callback_url');
            $table->boolean('status')->default(false)->comment('F:Disabled,T:Enabled');
            $table->string('password', 60);
            $table->timestamps();
        });

        Schema::create('merchant_white_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->json('api')->default(new Expression('(JSON_ARRAY())'));
            $table->json('backend')->default(new Expression('(JSON_ARRAY())'));
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('merchant_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('currency', 6);
            $table->decimal('credit', 14, 4)->default(0);
            $table->decimal('transaction_fee', 5, 4)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['merchant_id', 'currency']);
        });

        Schema::create('merchant_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->constrained();
            $table->foreignId('reseller_bank_card_id')
                ->constrained();
            $table->string('method', 10);
            $table->string('order_id', 60)->unique();
            $table->string('merchant_order_id', 60);
            $table->string('player_id', 40)->default(0);
            $table->decimal('amount', 14, 4);
            $table->string('currency', 6);
            $table->unsignedTinyInteger('status')
                ->default(0)
                ->comment('0:Created,1:Pending,2:Approved,3:Rejected,4:Enforced,5:Canceled');
            $table->unsignedTinyInteger('callback_status')
                ->default(0)
                ->comment('0:Created,1:Pending,2:Finish,3:Failed');
            $table->unsignedTinyInteger('attempts')
                ->default(0);
            $table->string('callback_url');
            $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
            $table->timestamps();
            $table->timestamp('notified_at')->nullable();
            $table->unique(
                ['merchant_id', 'merchant_order_id', 'currency'],
                'merchant_deposits_merchant_order_id_unique'
            );
        });

        Schema::create('merchant_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->constrained();
            $table->foreignId('reseller_id')
                ->constrained();
            $table->foreignId('payment_channel_id')
                ->constrained();
            $table->string('order_id', 60)->unique();
            $table->string('merchant_order_id', 60);
            $table->string('player_id', 40)->default(0);
            $table->json('attributes')->default(new Expression('(JSON_ARRAY())'));
            $table->decimal('amount', 14, 4);
            $table->string('currency', 6);
            $table->unsignedTinyInteger('status')
                ->default(0)
                ->comment('0:Created,1:Pending,2:Approved,3:Rejected,4:Enforced,5:Canceled');
            $table->unsignedTinyInteger('callback_status')
                ->default(0)
                ->comment('0:Created,1:Pending,2:Finish,3:Failed');
            $table->unsignedTinyInteger('attempts')
                ->default(0);
            $table->string('callback_url');
            $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
            $table->timestamps();
            $table->timestamp('notified_at')->nullable();
            $table->unique(
                ['merchant_id', 'merchant_order_id', 'currency'],
                'merchant_withdrawals_merchant_order_id_unique'
            );
        });

        Schema::create('merchant_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained();
            $table->string('order_id', 60)->unique();
            $table->decimal('amount', 14, 4);
            $table->string('currency', 6);
            $table->unsignedTinyInteger('status')
                ->default(0)
                ->comment('0:Pending,1::Approved,2:Rejected');
            $table->json('extra')->default(new Expression('(JSON_OBJECT())'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_settlements');
        Schema::dropIfExists('merchant_withdrawals');
        Schema::dropIfExists('merchant_deposits');
        Schema::dropIfExists('merchant_white_lists');
        Schema::dropIfExists('merchant_credits');
        Schema::dropIfExists('merchants');
    }
}
