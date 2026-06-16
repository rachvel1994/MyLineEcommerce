<h2>დღიური გაყიდვების რეპორტი</h2>
<p>თარიღი: {{ $date }}</p>

<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <thead>
    <tr>
        <th>#</th>
        <th>პროდუქტი</th>
        <th>ფასი</th>
        <th>თვითღირებულება</th>
    </tr>
    </thead>
    <tbody>
    @foreach($products as $i => $product)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>
                {{ $product->model->name ?? '' }}
                {{ $product->storage->name ?? '' }}
            </td>
            <td>{{ number_format($product->sale_price, 2) }}</td>
            <td>{{ number_format($product->price, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<br>

<h3>ჯამები:</h3>
<ul>
    <li>სულ გაყიდვები: {{ $totalRevenue }}</li>
    <li>სულ თვითღირებულება: {{ $totalCost }}</li>
    <li>მოგება: {{ $profit }}</li>
</ul>
