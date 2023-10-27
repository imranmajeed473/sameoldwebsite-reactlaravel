<x-main.layout class="container my-5 page-auth page-auth-register">
    <x-slot:title>{{ __('Register') }}</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <div class="card card-container shadow-lg bg-light mt-3">

                <div class="card-header card-header-banner">
                    <h2 class="text-center">{{ __('Register') }}</h2>
                </div>
                <div class="card-body">
                    <div class="p-4">
                        @if ($errors->any())
                        <div class="mb-4">
                            <x-alerts type="danger" :messages="$errors->all(':message')"></x-alerts>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <x-return-url-input :returnUrl="$returnUrl ?? old('return_url')" />

                            <div class="input-group mb-3 has-validation">
                                <span class="input-group-text bg-primary">
                                    <i class="fa-solid fa-at text-white"></i>
                                </span>
                                <input
                                    name="email"
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="{{ __('Email Address') }}"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="input-group mb-3 has-validation">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fa-solid fa-key"></i>
                                </span>

                                <div class="ibc-container showhide-password flex-grow-1">
                                    <input
                                        name="password"
                                        type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="{{ __('Password') }}"
                                        required
                                        autocomplete="password">
                                    <a href="#" class="ibc-button" role="button">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="input-group mb-3 has-validation">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fa-solid fa-globe"></i>
                                </span>

                                <select name="country" class="selectpicker form-control" data-live-search="true" id="country">
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->code }}" @selected(old('country', 'USA') == $country->code)>
                                            {{ __($country->country) }} ({{ $country->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex flex-row justify-content-center">
                                <div class="form-check">
                                    <input
                                        class="form-check-input @error('terms_conditions') is-invalid @enderror"
                                        type="checkbox"
                                        name="terms_conditions"
                                        value="yes"
                                        id="termsConditions"
                                        aria-describedby="invalidTermsConditions"
                                        required>
                                    <label class="form-check-label" for="termsConditions">
                                        I agree to the <a href="#" target="_blank">terms and conditions</a>
                                    </label>
                                    @error('terms_conditions')
                                        <div id="invalidTermsConditions" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>
                            </div>

                            <div class="d-flex flex-column mt-2">
                                <div class="mx-auto mb-3">
                                    <button class="btn btn-primary text-center" type="submit">
                                        {{ __('Register') }}
                                    </button>
                                </div>

                                @if (Route::has('login'))
                                <p class="text-center">
                                    Already have an account?
                                    <a href="{{ route('login') }}" class="text-primary">{{ __('Login') }}</a>
                                </p>
                                @endif

                                @if (Route::has('password.request'))
                                <p class="text-center">
                                    <a href="{{ route('password.request') }}" class="text-primary">{{ __('Forgot your password?') }}</a>
                                </p>
                                @endif
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-main.layout>
