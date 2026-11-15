<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One line in the cash book: money the business spent, either buying stock or on running costs. */
class CashbookEntry extends Model
{
    public const TYPES = ['purchase' => 'Purchase (stock)', 'expense' => 'Expense'];

    public const CATEGORIES = [
        'purchase' => ['Stock / goods for resale', 'Packaging materials', 'Other purchases'],
        'expense' => ['Rent', 'Salaries & wages', 'Rider fees', 'Transport & fuel', 'Utilities (light, water)', 'Phone & data', 'Marketing & ads', 'Repairs & maintenance', 'Taxes & licences', 'Bank & MoMo charges', 'Other expenses'],
    ];

    public const METHODS = ['cash' => 'Cash', 'momo' => 'Mobile Money', 'bank' => 'Bank'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['entry_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Every category name, for validation and the filter. */
    public static function allCategories(): array
    {
        return array_merge(...array_values(self::CATEGORIES));
    }
}
