<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Transactions;

use IFRS\Models\Account;
use IFRS\Models\Transaction;

use IFRS\Interfaces\Fetchable;

use IFRS\Traits\Fetching;

use IFRS\Exceptions\MainAccount;
use IFRS\Exceptions\LineItemAccount;
use IFRS\Exceptions\VatCharge;
use IFRS\Interfaces\Assignable;
use IFRS\Traits\Assigning;

class ClientReceipt extends Transaction implements Fetchable, Assignable
{
    use Fetching;
    use Assigning;

    use \Parental\HasParent;

    /**
     * Transaction Number prefix
     *
     * @var string
     */

    const PREFIX = Transaction::RC;

    /**
     * Construct new ClientReceipt
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $attributes['credited'] = true;
        $attributes['transaction_type'] = self::PREFIX;

        parent::__construct($attributes);
    }

    /**
     * Validate ClientReceipt Main Account
     */
    public function save(array $options = []): bool
    {
        if (is_null($this->account) || $this->account->account_type != Account::RECEIVABLE) {
            throw new MainAccount(self::PREFIX, Account::RECEIVABLE);
        }

        return parent::save();
    }

    /**
     * Validate ClientReceipt LineItems
     */
    public function post(): void
    {
        $this->save();

        foreach ($this->getLineItems() as $lineItem) {
            if ($lineItem->account->account_type != Account::BANK) {
                throw new LineItemAccount(self::PREFIX, [Account::BANK]);
            }

            if ($lineItem->vat->rate > 0) {
                throw new VatCharge(self::PREFIX);
            }
        }

        parent::post();
    }
}
