<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('order_pdf.title') }}</title>
</head>
<body style="font-family: DejaVu Sans, Arial, sans-serif; font-size: 14px;">
<div style="width:680px;margin:auto;">

    <table style="width:100%;border-collapse:collapse;border:1px solid #DDD;margin-bottom:20px;">
        <tr>
            <td colspan="2" style="background:#efefef;padding:7px;font-weight:bold;">
                {{ __('order_pdf.title') }}
            </td>
        </tr>
        <tr>
            <td style="padding:7px; border:1px solid #DDD;">
                <b>{{ __('order_pdf.date') }}:</b> {{ $product->created_at?->format('d.m.Y H:i') }} <br>

                @if(!empty($product->model?->name))
                    <b>{{ __('order_pdf.product') }}:</b> {{ $product->model?->name }} <br>
                @endif

                @if(!empty($product->price))
                    <b>{{ __('order_pdf.price') }}:</b> {{ money($product->price) }}<br>
                @endif
            </td>

            <td style="padding:7px; border:1px solid #DDD;">
                @if(!empty($product->user?->name))
                    <b>{{ __('order_pdf.full_name') }}:</b> {{ $product->user->name }} <br>
                @endif

                @if(!empty($product->user?->mobile))
                    <b>{{ __('order_pdf.phone') }}:</b> {{ $product->user->mobile }} <br>
                @endif

                @if(!empty($product->user?->id_number))
                    <b>{{ __('order_pdf.id_number') }}:</b> {{ $product->user->id_number }} <br>
                @endif
            </td>
        </tr>
    </table>

    @if(!empty($product->accessoryOrders))
        @foreach($product->accessoryOrders as $accessoryOrder)
            @if($accessoryOrder->items->isNotEmpty())
                <table style="width:100%;border-collapse:collapse;border:1px solid #DDD;margin-bottom:20px;">
                    <thead>
                    <tr>
                        <th style="padding:7px;background:#EFEFEF;border:1px solid #DDD;">#</th>
                        <th style="padding:7px;background:#EFEFEF;border:1px solid #DDD;">{{ __('order_pdf.name') }}</th>
                        <th style="padding:7px;background:#EFEFEF;border:1px solid #DDD;">{{ __('order_pdf.quantity') }}</th>
                        <th style="padding:7px;background:#EFEFEF;border:1px solid #DDD;">{{ __('order_pdf.price') }}</th>
                        <th style="padding:7px;background:#EFEFEF;border:1px solid #DDD;">{{ __('order_pdf.total') }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php $totalAmount = 0; @endphp

                    @foreach($accessoryOrder->items as $i => $item)
                        @php
                            $name = $item->accessory->name ?? __('order_pdf.accessory');
                            $qty = (float) $item->quantity;
                            $price = (float) $item->price;
                            $total = $item->total_price
                                ? (float) $item->total_price
                                : $qty * $price;

                            $totalAmount += $total;
                        @endphp

                        <tr>
                            <td style="padding:7px;border:1px solid #DDD;">{{ $i + 1 }}</td>
                            <td style="padding:7px;border:1px solid #DDD;">{{ $name }}</td>
                            <td style="padding:7px;border:1px solid #DDD;text-align:right;">{{ $qty }}</td>
                            <td style="padding:7px;border:1px solid #DDD;text-align:right;">{{ money($price) }}</td>
                            <td style="padding:7px;border:1px solid #DDD;text-align:right;">{{ money($total) }}</td>
                        </tr>
                    @endforeach
                    </tbody>

                    <tfoot>
                    <tr>
                        <td colspan="4" style="padding:7px;text-align:right;font-weight:bold;border:1px solid #DDD;">
                            {{ __('order_pdf.grand_total') }}:
                        </td>
                        <td style="padding:7px;text-align:right;border:1px solid #DDD;font-weight:bold;">
                            {{ money($totalAmount) }}
                        </td>
                    </tr>
                    </tfoot>
                </table>
            @endif
        @endforeach
    @endif

</div>
</body>
</html>