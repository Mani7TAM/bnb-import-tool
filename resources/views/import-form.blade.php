<!DOCTYPE html>
<html>
<head>
    <title>Import Hotel</title>
</head>
<body>
    <h2>Paste Accommodation Listing URL</h2>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    @if($errors->any())
        <ul style="color:red">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('import.handle') }}" method="POST">
        @csrf
        <input type="url" name="url" placeholder="https://..." required style="width: 400px;">
        <button type="submit">Import</button>
    </form>
</body>
</html>