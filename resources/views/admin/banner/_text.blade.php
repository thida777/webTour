{{-- One text/number input. Needs: $name, $label. Optional: $type, $help. $banner comes from the parent view. --}}
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type ?? 'text' }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $banner->$name) }}"
        @if (($type ?? 'text') === 'number') step="0.01" min="0" max="100" @endif
        class="form-control @error($name) is-invalid @enderror">
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @if (!empty($help))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
