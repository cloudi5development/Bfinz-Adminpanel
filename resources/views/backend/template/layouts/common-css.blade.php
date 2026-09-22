{{--
    Admin panel stylesheets — completely separate from the frontend assets.
    The theme is hand-written in public/backend/template/css/style.css; there is
    no CSS framework, so nothing else needs loading here.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">

{{-- Font Awesome — the data table uses <i class="fa-..."> icon tags --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
      referrerpolicy="no-referrer">

{{-- Admin theme stylesheet --}}
<link rel="stylesheet" href="{{ asset('backend/template/css/style.css') }}">
