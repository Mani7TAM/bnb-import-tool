<!DOCTYPE html>
<html>
<head>
    <title>Review Listing</title>
</head>
<body>
    <h2>Review Listing</h2>

    <p><strong>Name:</strong> {{ $name }}</p>
    <p><strong>Description:</strong> {{ $description }}</p>
    <p><strong>Address:</strong> {{ $address }}</p>
<p><strong>Coordinates:</strong> {{ is_array($coordinates) ? implode(', ', $coordinates) : $coordinates }}</p>    <p><strong>Rating:</strong> {{ $rating }}</p>
    <p><strong>Review Count:</strong> {{ $reviewCount }}</p>

    <h3>Room Types & Prices</h3>
    <ul>
        @foreach($roomTypes as $room)
            <li>{{ $room['type'] ?? '' }} - {{ $room['price'] ?? '' }}</li>
        @endforeach
    </ul>

    <h3>Images</h3>
    @foreach($images as $img)
        <img src="{{ asset($img) }}" alt="Hotel Image" style="max-width:150px; margin:5px;">
    @endforeach

    <form action="{{ route('import.save') }}" method="POST">
        @csrf
        <input type="hidden" name="name" value="{{ $name }}">
        <input type="hidden" name="description" value="{{ $description }}">
        <input type="hidden" name="address" value="{{ $address }}">
<input type="hidden" name="coordinates" value='@json($coordinates)'>
        <input type="hidden" name="rating" value="{{ $rating }}">
        <input type="hidden" name="reviewCount" value="{{ $reviewCount }}">
        <input type="hidden" name="roomTypes" value='@json($roomTypes)'>
        <input type="hidden" name="images" value='@json($images)'>
        <button type="submit">Save Listing</button>
    </form>
</body>
</html>