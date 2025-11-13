@extends('layouts.app')

@section('title', 'Masuk | G-TECH')

@section('content')
<section class="min-h-[70vh] flex items-center justify-center py-12">
  <div class="w-full max-w-md rounded-[36px] bg-white shadow-[0_25px_65px_rgba(15,23,42,0.15)] border border-slate-100">
    <div class="px-10 pt-10 pb-6 text-center border-b border-slate-100">
      <p class="text-2xl font-bold tracking-[0.4em] text-slate-900">G-TECH</p>
      <p class="mt-3 text-xs uppercase tracking-[0.35em] text-slate-400">Log in</p>
    </div>

    <div class="px-10 py-8 space-y-6">
      @if(session('status'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
          {{ session('status') }}
        </div>
      @endif

      @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          <p class="font-semibold mb-2 uppercase text-[11px] tracking-[0.3em]">Gagal Masuk</p>
          <ul class="list-disc space-y-1 pl-4">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('login.attempt') }}" method="POST" class="space-y-6">
        @csrf
        <div class="space-y-2">
          <label for="email" class="block text-xs font-semibold tracking-[0.25em] text-slate-500">EMAIL</label>
          <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            required
            class="w-full border-none border-b border-slate-300 px-0 pb-2 text-sm uppercase tracking-wide text-slate-900 focus:border-slate-900 focus:ring-0"
            placeholder="example@gtech.com">
          @error('email')
            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="space-y-2">
          <label for="password" class="block text-xs font-semibold tracking-[0.25em] text-slate-500">PASSWORD</label>
          <input
            type="password"
            id="password"
            name="password"
            required
            class="w-full border-none border-b border-slate-300 px-0 pb-2 text-sm uppercase tracking-wide text-slate-900 focus:border-slate-900 focus:ring-0"
            placeholder="********">
          @error('password')
            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="flex items-center justify-between text-xs uppercase tracking-[0.25em] text-slate-500">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="remember" value="1"
                   class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                   {{ old('remember') ? 'checked' : '' }}>
            <span>Remember</span>
          </label>
          <a href="#" class="text-slate-400 hover:text-slate-900 transition">Forgot?</a>
        </div>

        <button
          type="submit"
          class="w-full rounded-2xl bg-slate-900 py-3 text-sm font-semibold uppercase tracking-[0.35em] text-white shadow-[0_12px_24px_rgba(15,23,42,0.35)] transition hover:opacity-90">
          Masuk
        </button>
      </form>

      <p class="text-center text-xs text-slate-500 tracking-[0.2em] uppercase">
        Belum punya akun?
        <a href="#" class="text-slate-900 font-semibold hover:underline">Daftar sekarang</a>
      </p>
    </div>
  </div>
</section>
@endsection
