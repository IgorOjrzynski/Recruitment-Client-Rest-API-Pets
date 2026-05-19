@if (session('success'))
    <p><strong>{{ session('success') }}</strong></p>
@endif

@if (session('error'))
    <p><strong>{{ session('error') }}</strong></p>
@endif

@if (session('api'))
    <p><small>{{ session('api') }}</small></p>
@endif

@if ($errors->any())
    <div>
        <p><strong>Błędy walidacji:</strong></p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

