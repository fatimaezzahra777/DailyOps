@php
    $companies = [
        'softart' => [
            'name' => 'SoftArt',
            'logo' => 'images/companies/softart.png',
        ],
        'company_name' => [
            'name' => 'Company Name',
            'logo' => 'images/companies/company-name.png',
        ],
    ];
    $companyFieldName = $companyFieldName ?? 'company';
    $companyPrefix = $companyPrefix ?? 'project';
    $selectedCompany = $selectedCompany ?? null;
@endphp

<fieldset class="md:col-span-2">
    <legend class="mb-3 block text-sm font-medium text-[var(--text-strong)]">Company</legend>
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach ($companies as $companyValue => $company)
            <div>
                <input id="{{ $companyPrefix }}-company-{{ $companyValue }}" name="{{ $companyFieldName }}"
                    type="radio" value="{{ $companyValue }}" class="peer sr-only"
                    @checked ($selectedCompany === $companyValue) required>
                <label for="{{ $companyPrefix }}-company-{{ $companyValue }}" class="project-company-option">
                    <span class="project-company-logo">
                        <img src="{{ asset($company['logo']) }}" alt="{{ $company['name'] }}">
                    </span>
                    <span class="project-company-name">{{ $company['name'] }}</span>
                    <span class="project-company-check material-symbols-rounded" aria-hidden="true">check_circle</span>
                </label>
            </div>
        @endforeach
    </div>
</fieldset>
