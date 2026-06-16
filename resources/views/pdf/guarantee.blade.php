@php
  use App\Models\AccessoryOrders;

  $fullTitle = $product->getExtendedModel();
  $showInfo = (bool) ($product->show_repair_information ?? false);
  $infoText = $showInfo ? $product->information->pluck('name')->join(', ') : null;
  $needReset = (bool) ($product->need_reset ?? false);
  $issueDate = optional($product->created_at)->format('d.m.Y') ?? now()->format('d.m.Y');

  $accessories = collect();

  if (!empty($product->order_id)) {
      $accessories = AccessoryOrders::query()
          ->with('items.accessory')
          ->where('order_id', $product->order_id)
          ->get()
          ->pluck('items')
          ->flatten(1);
  }
@endphp

        <!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <title>{{ __('warranty.title') }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="//cdn.web-fonts.ge/fonts/bpg-arial/css/bpg-arial.min.css" rel="stylesheet">

  <style>
    body {
      font-family: "BPG Arial", sans-serif;
      margin: 30px;
      background-color: #f7f7f7;
      font-size: 13px;
      color: #222;
    }

    .terms {
      margin-top: 2px;
      background: #fff;
      padding: 3px 15px;
      border-radius: 10px;
      box-shadow: 0 0 4px rgba(0, 0, 0, 0.05);
      white-space: normal;
      line-height: 1.2;
    }

    .terms h4 {
      color: #f7931e;
      font-size: 14px;
      margin: 10px 0 6px;
    }

    .note, .check-note {
      margin-top: 10px;
      padding: 10px 14px;
      font-size: 13px;
      border-radius: 6px;
      line-height: 1.4;
    }

    .note {
      background: #fff4e6;
      border-left: 4px solid #f7931e;
    }

    .check-note {
      background: #e7fbe7;
      border-left: 4px solid #5cb85c;
    }

    .check-icon {
      color: green;
      font-size: 15px;
      font-weight: bold;
    }

    .footer-bar {
      margin-top: 20px;
      background: #f7931e;
      color: #fff;
      padding: 12px;
      text-align: center;
      font-size: 15px;
      border-radius: 5px;
      font-weight: bold;
    }
  </style>
</head>

<body>

<table style="width: 100%; margin-bottom: 10px;">
  <tr>
    <td style="width: 50%; vertical-align: top;">
      <img src="https://myline.ge/logo.png" alt="MYLINE Logo" style="width: 250px;">
    </td>
    <td style="width: 50%; text-align: right; vertical-align: top;">
            <span style="color: #f7931e; font-weight: bold; font-size: 14px;">
                {{ __('warranty.issue_date') }}: {{ $issueDate }}
            </span>
    </td>
  </tr>
</table>

<table style="width:100%; border-spacing:10px;">
  <tr>
    <td style="width:50%; vertical-align:top; background:#fafafa; border:1px solid #eee; border-radius:8px; padding:10px;">
      <h3 style="color:#f7931e; font-size:14px;">{{ __('warranty.customer_information') }}</h3>

      <div><strong>{{ __('warranty.buyer') }}:</strong> {{ $product->user?->name ?? 'N/A' }}</div>
      <div><strong>{{ __('warranty.phone') }}:</strong> {{ $product->user?->mobile ?? 'N/A' }}</div>
      <div><strong>{{ __('warranty.id_number') }}:</strong> {{ $product->user?->id_number ?? 'N/A' }}</div>
      <div><strong>{{ __('warranty.price') }}:</strong> {{ number_format($product->sale_price ?? 0, 2) }} {{ __('warranty.currency') }}</div>
    </td>

    <td style="width:50%; vertical-align:top; background:#fafafa; border:1px solid #eee; border-radius:8px; padding:10px;">
      <h3 style="color:#f7931e; font-size:14px;">{{ __('warranty.product_information') }}</h3>

      @if(filled($product->order_id))
        <div><strong>{{ __('warranty.order_id') }}:</strong> {{ $product->order_id }}</div>
      @endif

      @if(filled($fullTitle))
        <div><strong>{{ __('warranty.product') }}:</strong> {{ $fullTitle }}</div>
      @endif

      @if(filled($product->guarantee?->name))
        <div><strong>{{ __('warranty.guarantee') }}:</strong> {{ $product->guarantee?->name }}</div>
      @endif

      @if(filled($product->sku))
        <div><strong>{{ __('warranty.code') }}:</strong> {{ $product->sku }}</div>
      @endif

      @if($showInfo && filled($infoText))
        <div style="margin-top:6px;">
          <strong>{{ __('warranty.information') }}:</strong> {{ $infoText }}
        </div>
      @endif

      @if($accessories->isNotEmpty())
        <div style="margin-top:10px;">
          <strong>{{ __('warranty.accessories') }}:</strong>

          <div style="margin-top:6px;">
            @foreach($accessories as $item)
              @php
                $name = $item->accessory->name ?? '---';
                $price = (float) $item->price;
              @endphp

              <div style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px dashed #eee;">
                <span style="font-weight:bold;">{{ $name }}:</span>

                <span>
                                    @if($price == 0)
                    {{ __('warranty.gift') }}
                  @else
                    {{ number_format($price, 2) }} {{ __('warranty.currency') }}
                  @endif
                                </span>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </td>
  </tr>
</table>

<div class="terms">
  <h4>{{ __('warranty.replacement_title') }}</h4>
  {!! __('warranty.replacement_text') !!}

  <h4>{{ __('warranty.service_terms_title') }}</h4>
  {!! __('warranty.service_terms_text') !!}

  <h4>{{ __('warranty.warranty_cancel_title') }}</h4>
  {!! __('warranty.warranty_cancel_text') !!}
</div>

<div class="note">
  {{ __('warranty.note') }}
</div>

@if($needReset)
  <div class="check-note">
    <span class="check-icon">&#10003;</span>
    {{ __('warranty.reset_note') }}
  </div>
@endif

<table style="width: 100%; margin-top: 30px;">
  <tr>
    <td style="width: 65%; font-size: 13px;">
      {{ __('warranty.acceptance_text') }}
    </td>
    <td style="width: 25%; text-align: center; vertical-align: bottom;">
      <div style="border-top: 2px solid #000; padding-top: 6px; font-weight: bold;">
        {{ __('warranty.signature') }}
      </div>
    </td>
  </tr>
</table>

<div class="footer-bar">
  {!! __('warranty.footer') !!}
</div>

</body>
</html>
