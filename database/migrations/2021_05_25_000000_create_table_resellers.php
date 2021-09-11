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
            $table->tinyInteger('level')->comment('0:referrer,1:master agent,2:agent,3:reseller');
            $table->string('name')->unique()->index();
            $table->string('username')->unique()->index();
            $table->string('phone', 20);
            $table->decimal('credit', 14, 4)->default(0);
            $table->decimal('coin', 14, 4)->default(0);
            $table->string('currency', 6);
            $table->decimal('commission_percentage', 5, 4)->default(0);
            $table->unsignedInteger('pending_limit')->default(0);
            $table->unsignedInteger('downline_slot')->default(0);
            $table->tinyInteger('status')->default(0)->comment('0:inactive,1:active,2:disabled');
            $table->string('password', 60);
            $table->timestamps();
        });

        // create default referrer
        Reseller::create([
            'level' => 0,
            'name' => 'company',
            'username' => 'company@gmail.com',
            'phone' => '0978475446',
            'currency' => 'VND',
            'password' => 'P@ssw0rd',
            'status' => Reseller::STATUS['ACTIVE'],
        ]);

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
            $table->foreignId('reseller_bank_card_id')->constrained();
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reseller_withdrawals');
        Schema::dropIfExists('reseller_deposits');
        Schema::dropIfExists('reseller_bank_cards');
        Schema::dropIfExists('resellers');
    }
}
