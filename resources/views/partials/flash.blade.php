@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
@endif
