@props(['headers' => []])

<div class="table-wrapper">
    <table class="vakt-table">
        <thead>
            <tr>
                @foreach($headers as $header)
                <th>{{ is_array($header) ? $header['label'] : $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
