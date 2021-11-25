<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\Reseller;
use App\Models\Transaction;
use App\Models\ResellerSms;
use App\Models\MerchantDeposit;
use App\Models\ResellerBankCard;
use App\Models\MerchantWithdrawal;
use App\Models\MerchantSettlement;
use App\Models\ResellerWithdrawal;

class MerchantDepositSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Merchant::all() as $merchant) {
            foreach ($merchant->credits as $credit) {
                $reseller_bank_card = ResellerBankCard::whereHas('reseller', function ($q) use ($credit) {
                    $q->where('currency', $credit->currency);
                })->inRandomOrder()->first();
                $d = MerchantDeposit::create([
                    'merchant_order_id' => $merchant->id == 1 ? '9798223690986' : $this->faker->isbn13,
                    'method' => Arr::random($reseller_bank_card->paymentChannel->payment_methods),
                    'amount' => 1000,
                    'currency' => $credit->currency,
                    'callback_url' => app('api.url')->version(env('API_VERSION'))->route('api.demos.callback'),
                    'merchant_id' => $merchant->id,
                    'reseller_bank_card_id' => $reseller_bank_card->id,
                    'status' => MerchantDeposit::STATUS['APPROVED'],
                ]);
                if ($d->paymentChannel->isSupportSMS()) {
                    ResellerSms::create([
                        'reseller_id' => $reseller_bank_card->reseller_id,
                        'model_id' => $d->id,
                        'model_name' => 'merchant.deposit',
                        'platform' => 'Android',
                        'address' => $d->paymentChannel->payin->sms_addresses[0],
                        'body' => __('sms.' . $d->paymentChannel->name, ['amount' => number_format($d->amount, 2)]),
                        'status' => ResellerSms::STATUS['MATCH'],
                        'sent_at' => Carbon::now(),
                        'received_at' => Carbon::now(),
                    ]);
                }
                $withdrawal = MerchantWithdrawal::create([
                    'merchant_order_id' => $merchant->id == 1 ? '9798223690986' : $this->faker->isbn13,
                    'merchant_id' => $merchant->id,
                    'reseller_id' => $reseller_bank_card->reseller->id,
                    'payment_channel_id' => $reseller_bank_card->paymentChannel->id,
                    'attributes' => collect($reseller_bank_card->paymentChannel->attributes)->flatMap(function ($v) {
                        switch ($v) {
                            case 'account_name':
                                return ['account_name' => $this->faker->name];
                            case 'account_number':
                                return ['account_number' => $this->faker->bankAccountNumber];
                            case 'bank_name':
                                return ['bank_name' => $this->faker->name . ' bank'];
                            case 'upi_id':
                                return ['upi_id' => Str::random(15) . '@upi'];
                            case 'ifsc_code':
                                return ['ifsc_code' => $this->faker->unique()->numerify('###########')];
                            default:
                                return [$v => Str::random(10)];
                        }
                    }),
                    'amount' => 100,
                    'currency' => $credit->currency,
                    'status' => MerchantWithdrawal::STATUS['PENDING'],
                    'callback_url' => app('api.url')->version(env('API_VERSION'))->route('api.demos.callback'),
                ]);
                $withdrawal->update([
                    'status' => MerchantWithdrawal::STATUS['FINISHED']
                ]);
                $withdrawal->update([
                    'status' => MerchantWithdrawal::STATUS['APPROVED']
                ]);
                MerchantSettlement::create([
                    'merchant_id' => $merchant->id,
                    'amount' => 100,
                    'currency' => $credit->currency,
                    'status' => MerchantSettlement::STATUS['APPROVED'],
                ]);
            }
        }
        foreach (Reseller::with('bankCards')->where('level', Reseller::LEVEL['RESELLER'])->get() as $reseller) {
            if ($reseller->coin < 1) {
                continue;
            }
            ResellerWithdrawal::create([
                'reseller_id' => $reseller->id,
                'reseller_bank_card_id' => $reseller->bankCards->random()->id,
                'audit_admin_id' => 1,
                'type' => ResellerWithdrawal::TYPE['COIN'],
                'transaction_type' => Transaction::TYPE['RESELLER_WITHDRAW_COIN'],
                'amount' => 1,
                'status' => ResellerWithdrawal::STATUS['APPROVED'],
                'extra' => [
                    'payment_type' => 'BY BANK TRANSFER',
                    'reason' => 'Withdraw',
                    'remark' => 'Test',
                    'memo' => 'success',
                    'creator' => $reseller->id
                ]
            ]);
        }
    }
}
