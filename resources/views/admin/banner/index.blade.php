@extends('layouts.app')

@section('title', 'Banner Settings - Eocambo Tours')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                        <h1 class="h3 mb-0">Banner Settings</h1>
                        <div class="d-flex gap-2">
                            <a href="/" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">View Home Page</a>
                            <a href="/admin/dashboard" class="btn btn-outline-secondary btn-sm">&larr; Dashboard</a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">Please fix the errors below and save again.</div>
                    @endif

                    <form action="/admin/banner" method="POST" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')

                        {{-- 1. Branding --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">1. Branding</div>
                            <div class="card-body">
                                @include('admin.banner._text', ['name' => 'brand_name', 'label' => 'Brand name', 'help' => 'The name shown next to the logo in the navbar and footer. The last word is shown in gold. Leave empty to use "Eocambo Tours".'])
                                @include('admin.banner._image', ['name' => 'logo', 'label' => 'Logo image', 'circle' => true, 'help' => 'The logo is shown as a circle in the navbar and footer (not in the banner). If no logo is uploaded, only the brand name is shown.'])
                            </div>
                        </div>

                        {{-- 2. Headline --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">2. Headline</div>
                            <div class="card-body">
                                @include('admin.banner._text', ['name' => 'small_title', 'label' => 'Small title', 'help' => "Example: It's Time To"])
                                @include('admin.banner._text', ['name' => 'main_title', 'label' => 'Main title', 'help' => 'Example: TRAVEL'])
                                @include('admin.banner._text', ['name' => 'highlight_text', 'label' => 'Highlight text', 'help' => 'Shown in yellow. Example: EXPLORE'])
                                @include('admin.banner._text', ['name' => 'tagline', 'label' => 'Tagline', 'help' => 'Example: Cambodia With Us!'])
                            </div>
                        </div>

                        {{-- 3. Book Now button --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">3. Book Now Button</div>
                            <div class="card-body">
                                @include('admin.banner._text', ['name' => 'button_text', 'label' => 'Button text', 'help' => 'Example: BOOK NOW'])
                                @include('admin.banner._text', ['name' => 'button_url', 'label' => 'Button URL', 'help' => 'Use /packages for the package page, or a full address starting with https://'])
                            </div>
                        </div>

                        {{-- 4. Contact --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">4. Contact</div>
                            <div class="card-body">
                                @include('admin.banner._text', ['name' => 'phone', 'label' => 'Phone number'])
                                @include('admin.banner._text', ['name' => 'website', 'label' => 'Website', 'help' => 'Full address, for example https://www.example.com'])
                            </div>
                        </div>

                        {{-- 5. Banner images --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">5. Banner Images</div>
                            <div class="card-body">
                                @include('admin.banner._image', ['name' => 'image_1', 'label' => 'Image 1'])
                                @include('admin.banner._image', ['name' => 'image_2', 'label' => 'Image 2'])
                                @include('admin.banner._image', ['name' => 'image_3', 'label' => 'Image 3'])
                            </div>
                        </div>

                        {{-- 6. Tour includes --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">6. Tour Includes</div>
                            <div class="card-body">
                                @include('admin.banner._text', ['name' => 'include_1', 'label' => 'Include item 1'])
                                @include('admin.banner._text', ['name' => 'include_2', 'label' => 'Include item 2'])
                                @include('admin.banner._text', ['name' => 'include_3', 'label' => 'Include item 3'])
                                @include('admin.banner._text', ['name' => 'include_4', 'label' => 'Include item 4', 'help' => 'Leave an item empty to hide it.'])
                            </div>
                        </div>

                        {{-- 7. Special offer --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">7. Special Offer</div>
                            <div class="card-body">
                                @include('admin.banner._text', ['name' => 'offer_title', 'label' => 'Offer title'])
                                @include('admin.banner._text', ['name' => 'discount', 'label' => 'Discount percentage', 'type' => 'number', 'help' => 'A number from 0 to 100.'])
                                @include('admin.banner._text', ['name' => 'offer_suffix', 'label' => 'Offer suffix', 'help' => 'Example: OFF'])

                                {{-- The hidden 0 is sent when the box is unchecked. --}}
                                <input type="hidden" name="show_offer" value="0">
                                <div class="form-check">
                                    <input type="checkbox" id="show_offer" name="show_offer" value="1" class="form-check-input" {{ old('show_offer', $banner->show_offer) ? 'checked' : '' }}>
                                    <label for="show_offer" class="form-check-label">Show the special offer on the banner</label>
                                </div>
                            </div>
                        </div>

                        {{-- 8. Social media --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">8. Social Media</div>
                            <div class="card-body">
                                @include('admin.banner._text', ['name' => 'facebook_url', 'label' => 'Facebook URL', 'help' => 'Full address, for example https://www.facebook.com/yourpage'])
                                @include('admin.banner._text', ['name' => 'instagram_url', 'label' => 'Instagram URL'])
                                @include('admin.banner._text', ['name' => 'youtube_url', 'label' => 'YouTube URL'])
                                @include('admin.banner._text', ['name' => 'x_url', 'label' => 'X URL', 'help' => 'Only icons that have a link are shown on the banner.'])
                            </div>
                        </div>

                        {{-- 9. Banner visibility --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-bold">9. Banner Visibility</div>
                            <div class="card-body">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                                    <label for="is_active" class="form-check-label">Show the banner on the Home page</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <a href="/admin/dashboard" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
