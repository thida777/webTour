{{-- One image upload with a preview of the current image. Needs: $name, $label. Optional: $help, $circle (round 90px preview, used for the logo). --}}
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>

    @if (!empty($banner->$name))
        <div class="mb-2">
            @if (!empty($circle))
                <img src="{{ asset('storage/' . $banner->$name) }}" alt="{{ $label }}" class="border" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover;">
            @else
                <img src="{{ asset('storage/' . $banner->$name) }}" alt="{{ $label }}" class="img-thumbnail" style="max-width: 200px;">
            @endif
            <div class="form-text">Current image. Choose a new file only if you want to replace it.</div>
        </div>
    @endif

    <input type="file" id="{{ $name }}" name="{{ $name }}" accept="image/*" class="form-control @error($name) is-invalid @enderror">
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Optional. JPG, PNG, GIF or WebP, maximum 2 MB.@if (!empty($help)) {{ $help }}@endif</div>
</div>
