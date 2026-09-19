{{-- Shared form fields for the create and edit pages. $package is empty on create. --}}

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" id="name" name="name" value="{{ old('name', $package->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="location" class="form-label">Location</label>
    <input type="text" id="location" name="location" value="{{ old('location', $package->location ?? '') }}" class="form-control @error('location') is-invalid @enderror">
    @error('location')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="price" class="form-label">Price (USD)</label>
        <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $package->price ?? '') }}" class="form-control @error('price') is-invalid @enderror">
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="duration" class="form-label">Duration</label>
        <input type="text" id="duration" name="duration" value="{{ old('duration', $package->duration ?? '') }}" placeholder="e.g. 3 days 2 nights" class="form-control @error('duration') is-invalid @enderror">
        @error('duration')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $package->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="image" class="form-label">Image</label>

    @if (!empty($package->image))
        <div class="mb-2">
            <img src="{{ asset('storage/' . $package->image) }}" alt="{{ $package->name }}" class="img-thumbnail" style="max-width: 200px;">
            <div class="form-text">Current image. Choose a new file only if you want to replace it.</div>
        </div>
    @endif

    <input type="file" id="image" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
    @error('image')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Optional. JPG, PNG, GIF or WebP, maximum 2 MB.</div>
</div>

<div class="mb-4">
    {{-- The hidden 0 is sent when the box is unchecked, so "inactive" is saved correctly. --}}
    <input type="hidden" name="status" value="0">
    <div class="form-check">
        <input type="checkbox" id="status" name="status" value="1" class="form-check-input @error('status') is-invalid @enderror" {{ old('status', $package->status ?? true) ? 'checked' : '' }}>
        <label for="status" class="form-check-label">Active (show on the public website)</label>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
