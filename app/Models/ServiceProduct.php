<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

class ServiceProduct extends Model implements Commentable
{
    use HasComments;

    protected $table = 'products';

    protected $fillable = [
        'sku',
        'order_id',
        'price',
        'sale_price',
        'company_id',
        'retail_price',
        'repair_price',
        'comment',
        'service_comment',
        'images',
        'user_id',
        'condition_id',
        'status_id',
        'category_id',
        'is_consigned',
        'hear_about_id',
        'delivery_id',
        'guarantee_id',
        'model_id',
        'battery_id',
        'color_id',
        'storage_id',
        'seller_id',
        'need_reset',
        'is_repaired',
        'is_payed',
        'show_repair_information',
        'created_at',
    ];

    // Casts
    protected $casts = [
        'images' => 'array',
        'is_consigned' => 'boolean',
        'is_repaired' => 'boolean',
        'show_repair_information' => 'boolean',
        'price' => 'float',
        'sale_price' => 'float',
        'retail_price' => 'float',
        'repair_price' => 'float',
    ];

    public function scopeSold($q)
    {
        return $q->where('status_id', 4);
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(Condition::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function hearAbout(): BelongsTo
    {
        return $this->belongsTo(HearAbout::class, 'hear_about_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function guarantee(): BelongsTo
    {
        return $this->belongsTo(Guarantee::class);
    }

    public function battery(): BelongsTo
    {
        return $this->belongsTo(Battery::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'model_id');
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(Storage::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function accessoryOrders(): HasMany
    {
        return $this->hasMany(AccessoryOrders::class, 'order_id', 'order_id');
    }

    public function information(): BelongsToMany
    {
        return $this->belongsToMany(RepairInformation::class, 'product_repair_information', 'product_id', 'repair_information_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ProductPayment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AccessoryOrderItem::class, 'order_id', 'order_id');
    }

    public function repairHistories(): HasMany
    {
        return $this->hasMany(ServiceRepairHistory::class, 'product_id', 'id')->latest();
    }

    public function consignments()
    {
        return $this->belongsToMany(Consignment::class, 'consignment_product')
            ->withPivot(['qty', 'unit_price', 'line_total'])
            ->withTimestamps();
    }

    public function latestComment(): MorphOne
    {
        return $this->morphOne(Comment::class, 'commentable')->latestOfMany();
    }

    public function firstComment(): MorphOne
    {
        return $this->morphOne(Comment::class, 'commentable')->oldestOfMany();
    }

    public function services()
    {
        return $this->belongsToMany(
            Service::class,
            'service_product',
            'product_id',
            'service_id'
        )
            ->withPivot(['qty', 'unit_price'])
            ->withTimestamps();
    }

    public function getExtendedModel(): string
    {
        return "{$this->model?->name} - {$this->storage?->name} - {$this->battery?->name}";
    }
    
}
