<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @deprecated Use LedgerEntry. Kept as a compatibility alias. */
class VoucherEntry extends LedgerEntry
{
    protected $table = 'ledger_entries';
}
