<!DOCTYPE html>
<html>
<head>
    <title>Hotel Scraper</title>
</head>
<body>
    <h1>Paste Hotel Listing URL</h1>
    <form method="POST" action="{{ route('scrape') }}">
        @csrf
        <input type="text" name="url" placeholder="Enter URL" style="width:400px" required>
        <button type="submit">Scrape</button>
    </form>

    @isset($data)
        <hr>
        <h2>{{ $data['name'] }}</h2>
        <p><strong>Address:</strong> {{ $data['address'] }}</p>
        <p><strong>Description:</strong> {{ $data['description'] }}</p>
        <h3>Images:</h3>
        @foreach($data['images'] as $img)
            <img src="{{ $img }}" width="200" style="margin:10px;">
        @endforeach
    @endisset
</body>
</html>
