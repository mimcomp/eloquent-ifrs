<?php
/**
 * Laravel IFRS Accounting
 *
 * @author Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license MIT
 */
namespace Ekmungai\IFRS\Traits;

use Ekmungai\IFRS\Models\Account;

use Ekmungai\IFRS\Exceptions\LineItemAccount;
use Ekmungai\IFRS\Exceptions\MainAccount;

trait Buying
{
    /**
     * Validate Buying Transaction Main Account.
     */
    public function save(): void
    {
        if (is_null($this->getAccount()) or $this->getAccount()->account_type != Account::PAYABLE) {
            throw new MainAccount(self::PREFIX, Account::PAYABLE);
        }

        $this->transaction->save();
    }

    /**
     * Validate Buying Transaction LineItems.
     */
    public function post(): void
    {
        $this->save();

        $purchasable = [
            Account::OPERATING_EXPENSE,
            Account::DIRECT_EXPENSE,
            Account::OVERHEAD_EXPENSE,
            Account::OTHER_EXPENSE,
            Account::NON_CURRENT_ASSET,
            Account::CURRENT_ASSET,
            Account::INVENTORY
        ];

        foreach ($this->getLineItems() as $lineItem) {
            if (!in_array($lineItem->account->account_type, $purchasable)) {
                throw new LineItemAccount(self::PREFIX, $purchasable);
            }
        }

        $this->transaction->post();
    }
}