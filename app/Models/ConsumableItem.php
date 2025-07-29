<?php // This MUST be the very first characters in the file, no spaces or blank lines before it

namespace App\Models; // This MUST be the first statement after <?php (or declare)

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category; // Ensure these are correct
use App\Models\Location; // Ensure these are correct

class ConsumableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'purchase_price',
        'sale_price',
        'category_id',
        'company_name',
        'min_stock_level',
        'location_id',
        'barcode',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'float',
        'sale_price' => 'float',
        'min_stock_level' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}